<?php

namespace App\Http\Controllers;

use App\Models\Espacio;
use App\Models\User;
use App\Models\Reserva;
use App\Models\Solicitante;
use App\Models\VetoSalaEstudio;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\VetosExport;

class SalaEstudioController extends Controller
{
    /**
     * Registrar acceso de un alumno a una sala de estudio
     */
    public function registrarAcceso(Request $request)
    {
        try {
            $request->validate([
                'id_espacio' => 'required|string',
                'run' => 'required|string'
            ]);

            $idEspacio = $request->id_espacio;
            $run = $request->run;

            // Limpiar el RUN (quitar guión y dígito verificador si existen)
            $runLimpio = preg_replace('/[^0-9]/', '', $run);

            // Verificar si el usuario está vetado
            $veto = VetoSalaEstudio::vetoActivo($run);
            if (!$veto) {
                $veto = VetoSalaEstudio::vetoActivo($runLimpio);
            }

            if ($veto) {
                return response()->json([
                    'success' => false,
                    'message' => '🚫 Acceso Denegado: Usuario vetado',
                    'vetado' => true,
                    'veto' => [
                        'motivo' => $veto->observacion,
                        'fecha_veto' => $veto->fecha_veto->format('d/m/Y H:i'),
                        'tipo' => $veto->tipo_veto
                    ]
                ], 403);
            }

            // Buscar el usuario en la tabla users
            $usuario = User::where('run', $run)
                ->orWhere('run', $runLimpio)
                ->first();

            // Si no existe como usuario, intentar crearlo desde solicitantes
            if (!$usuario) {
                // Intentar crear desde solicitantes
                $solicitante = Solicitante::on('tenant')->where('run_solicitante', $run)
                    ->orWhere('run_solicitante', $runLimpio)
                    ->first();

                if ($solicitante) {
                    // Crear usuario desde solicitante
                    try {
                        $usuario = new User();
                        $usuario->run = $solicitante->run_solicitante;
                        $usuario->name = $solicitante->nombre;
                        $usuario->email = $solicitante->correo;
                        $usuario->password = Hash::make($solicitante->run_solicitante);
                        $usuario->celular = $solicitante->telefono;
                        $usuario->save();

                        Log::info('Usuario creado desde solicitante para sala de estudio', [
                            'run' => $solicitante->run_solicitante,
                            'nombre' => $solicitante->nombre
                        ]);
                    } catch (\Exception $e) {
                        Log::error('Error al crear usuario desde solicitante', [
                            'error' => $e->getMessage(),
                            'run' => $solicitante->run_solicitante
                        ]);
                        
                        return response()->json([
                            'success' => false,
                            'mensaje' => 'Error al crear usuario: ' . $e->getMessage()
                        ], 500);
                    }
                } else {
                    // No existe en ninguna tabla
                    return response()->json([
                        'success' => false,
                        'mensaje' => 'Usuario no encontrado en el sistema'
                    ], 404);
                }
            }

            // Verificar que el espacio existe y es sala de estudio
            $espacio = Espacio::where('id_espacio', $idEspacio)->first();

            if (!$espacio) {
                return response()->json([
                    'success' => false,
                    'mensaje' => 'Espacio no encontrado'
                ], 404);
            }

            if (strtolower($espacio->tipo_espacio) !== 'sala de estudio') {
                return response()->json([
                    'success' => false,
                    'mensaje' => 'El espacio no es una sala de estudio'
                ], 400);
            }

            // Verificar si el alumno ya está registrado hoy (salida)
            $reservaExistente = Reserva::where('id_espacio', $idEspacio)
                ->where('run_solicitante', $usuario->run)
                ->where('estado', 'activa')
                ->whereDate('fecha_reserva', Carbon::today())
                ->first();

            if ($reservaExistente) {
                // Si ya está registrado, finalizar su reserva (salida)
                $reservaExistente->estado = 'finalizada';
                $reservaExistente->hora_salida = Carbon::now()->format('H:i:s');
                $reservaExistente->save();

                // Verificar si hay más reservas activas en este espacio
                $reservasActivas = Reserva::where('id_espacio', $idEspacio)
                    ->where('estado', 'activa')
                    ->whereDate('fecha_reserva', Carbon::today())
                    ->count();

                // Si no hay más reservas activas, marcar el espacio como disponible
                if ($reservasActivas === 0 && $espacio->estado === 'Ocupado') {
                    $espacio->estado = 'Disponible';
                    $espacio->save();
                }

                Log::info('Salida registrada en sala de estudio', [
                    'espacio' => $idEspacio,
                    'usuario' => $usuario->run,
                    'nombre' => $usuario->name,
                    'hora_entrada' => $reservaExistente->hora,
                    'hora_salida' => Carbon::now()->format('H:i:s')
                ]);

                return response()->json([
                    'success' => true,
                    'accion' => 'salida',
                    'mensaje' => 'Salida registrada exitosamente',
                    'nombre' => $usuario->name,
                    'run' => $usuario->run,
                    'hora_entrada' => Carbon::parse($reservaExistente->hora)->format('H:i'),
                    'hora_salida' => Carbon::now()->format('H:i')
                ]);
            }

            // Si llegamos aquí, es una entrada nueva - verificar capacidad disponible
            $reservasActivas = Reserva::where('id_espacio', $idEspacio)
                ->where('estado', 'activa')
                ->whereDate('fecha_reserva', Carbon::today())
                ->count();

            if ($espacio->capacidad_maxima && $reservasActivas >= $espacio->capacidad_maxima) {
                return response()->json([
                    'success' => false,
                    'mensaje' => 'Capacidad máxima alcanzada'
                ], 400);
            }

            // Crear la reserva/registro de acceso (entrada)
            $reserva = new Reserva();
            $reserva->id_reserva = Reserva::generarIdUnico();
            $reserva->id_espacio = $idEspacio;
            $reserva->run_solicitante = $usuario->run;
            $reserva->fecha_reserva = Carbon::now();
            $reserva->hora = Carbon::now()->format('H:i:s');
            $reserva->hora_salida = Carbon::now()->addHours(2)->format('H:i:s'); // 2 horas por defecto
            $reserva->estado = 'activa';
            $reserva->tipo_reserva = 'espontanea'; // Tipo de reserva para salas de estudio
            $reserva->observaciones = 'Sala de Estudio'; // Identificar como sala de estudio
            $reserva->save();

            // Actualizar estado del espacio si es necesario
            if ($espacio->estado !== 'Ocupado') {
                $espacio->estado = 'Ocupado';
                $espacio->save();
            }

            Log::info('Acceso registrado en sala de estudio', [
                'espacio' => $idEspacio,
                'usuario' => $usuario->run,
                'nombre' => $usuario->name
            ]);

            return response()->json([
                'success' => true,
                'accion' => 'entrada',
                'mensaje' => 'Acceso registrado exitosamente',
                'nombre' => $usuario->name,
                'run' => $usuario->run,
                'hora_registro' => Carbon::now()->format('H:i')
            ]);

        } catch (\Exception $e) {
            Log::error('Error al registrar acceso a sala de estudio', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'mensaje' => 'Error al registrar acceso: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener lista de alumnos registrados en una sala de estudio
     */
    public function obtenerAlumnosRegistrados($idEspacio)
    {
        try {
            // Obtener reservas activas del día de hoy
            $reservas = Reserva::where('id_espacio', $idEspacio)
                ->where('estado', 'activa')
                ->whereDate('fecha_reserva', Carbon::today())
                ->orderBy('hora', 'desc')
                ->get();

            $alumnos = $reservas->map(function ($reserva) {
                // Intentar obtener nombre del solicitante o del usuario
                $nombre = 'Usuario desconocido';
                
                if ($reserva->run_solicitante) {
                    // Primero buscar en la tabla solicitantes
                    if ($reserva->solicitante) {
                        $nombre = $reserva->solicitante->nombre;
                    } else {
                        // Si no está en solicitantes, buscar en users
                        $usuario = User::where('run', $reserva->run_solicitante)->first();
                        if ($usuario) {
                            $nombre = $usuario->name;
                        }
                    }
                }

                return [
                    'run' => $reserva->run_solicitante,
                    'nombre' => $nombre,
                    'hora_registro' => Carbon::parse($reserva->hora)->format('H:i')
                ];
            });

            return response()->json([
                'success' => true,
                'alumnos' => $alumnos
            ]);

        } catch (\Exception $e) {
            Log::error('Error al obtener alumnos registrados en sala de estudio', [
                'espacio' => $idEspacio,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'mensaje' => 'Error al obtener alumnos registrados',
                'alumnos' => []
            ], 500);
        }
    }

    /**
     * Aplicar veto individual
     */
    public function vetarIndividual(Request $request)
    {
        try {
            $request->validate([
                'run' => 'required|string',
                'observacion' => 'required|string',
                'id_reserva' => 'nullable|string'
            ]);

            // Verificar si ya existe un veto activo
            if (VetoSalaEstudio::estaVetado($request->run)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Este usuario ya tiene un veto activo'
                ], 400);
            }

            $veto = VetoSalaEstudio::create([
                'run_vetado' => $request->run,
                'tipo_veto' => 'individual',
                'id_reserva_origen' => $request->id_reserva,
                'observacion' => $request->observacion,
                'estado' => 'activo',
                'vetado_por' => Auth::user()->name ?? 'Sistema',
                'fecha_veto' => now()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Veto individual aplicado correctamente',
                'veto' => $veto
            ]);

        } catch (\Exception $e) {
            Log::error('Error al aplicar veto individual', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al aplicar el veto'
            ], 500);
        }
    }

    /**
     * Aplicar veto grupal (a todos los miembros de una reserva/grupo)
     */
    public function vetarGrupo(Request $request)
    {
        try {
            $request->validate([
                'id_reserva' => 'required|string',
                'observacion' => 'required|string'
            ]);

            // Obtener la reserva origen para obtener datos del grupo
            $reservaOrigen = Reserva::where('id_reserva', $request->id_reserva)->firstOrFail();
            
            // Obtener todas las reservas del mismo grupo
            $reservasGrupo = Reserva::where('id_espacio', $reservaOrigen->id_espacio)
                ->where('fecha_reserva', $reservaOrigen->fecha_reserva)
                ->whereBetween('hora', [
                    Carbon::parse($reservaOrigen->hora)->subMinutes(30)->format('H:i:s'),
                    Carbon::parse($reservaOrigen->hora)->addMinutes(30)->format('H:i:s')
                ])
                ->where('tipo_reserva', 'espontanea')
                ->where('observaciones', 'Sala de Estudio')
                ->get();

            $vetados = [];
            $yaVetados = [];

            foreach ($reservasGrupo as $reserva) {
                if ($reserva->run_solicitante) {
                    // Verificar si ya está vetado
                    if (VetoSalaEstudio::estaVetado($reserva->run_solicitante)) {
                        $yaVetados[] = $reserva->run_solicitante;
                        continue;
                    }

                    $veto = VetoSalaEstudio::create([
                        'run_vetado' => $reserva->run_solicitante,
                        'tipo_veto' => 'grupal',
                        'id_reserva_origen' => $request->id_reserva,
                        'observacion' => $request->observacion,
                        'estado' => 'activo',
                        'vetado_por' => Auth::user()->name ?? 'Sistema',
                        'fecha_veto' => now()
                    ]);

                    $vetados[] = $reserva->run_solicitante;
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Veto grupal aplicado correctamente',
                'vetados' => count($vetados),
                'ya_vetados' => count($yaVetados),
                'detalles' => [
                    'nuevos_vetos' => $vetados,
                    'ya_vetados' => $yaVetados
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error al aplicar veto grupal', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al aplicar el veto grupal'
            ], 500);
        }
    }

    /**
     * Liberar veto
     */
    public function liberarVeto(Request $request, $id)
    {
        try {
            $veto = VetoSalaEstudio::findOrFail($id);

            if ($veto->estado !== 'activo') {
                return response()->json([
                    'success' => false,
                    'message' => 'Este veto ya fue liberado'
                ], 400);
            }

            $veto->update([
                'estado' => 'liberado',
                'liberado_por' => Auth::user()->name ?? 'Sistema',
                'fecha_liberacion' => now()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Veto liberado correctamente',
                'veto' => $veto
            ]);

        } catch (\Exception $e) {
            Log::error('Error al liberar veto', [
                'id' => $id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al liberar el veto'
            ], 500);
        }
    }

    /**
     * Actualizar observación de un veto
     */
    public function actualizarVeto(Request $request, $id)
    {
        try {
            $request->validate([
                'observacion' => 'required|string'
            ]);

            $veto = VetoSalaEstudio::findOrFail($id);

            $veto->update([
                'observacion' => $request->observacion
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Observación actualizada correctamente',
                'veto' => $veto
            ]);

        } catch (\Exception $e) {
            Log::error('Error al actualizar veto', [
                'id' => $id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar el veto'
            ], 500);
        }
    }

    /**
     * Obtener lista de vetados
     */
    public function listarVetados(Request $request)
    {
        try {
            $query = VetoSalaEstudio::select([
                'id',
                'run_vetado',
                'tipo_veto',
                'estado',
                'observacion',
                'fecha_veto',
                'vetado_por',
                'fecha_liberacion',
                'liberado_por'
            ])
            ->with(['solicitante:run_solicitante,nombre']); // Solo cargar campos necesarios

            // Filtrar por estado
            if ($request->has('estado') && $request->estado) {
                $query->where('estado', $request->estado);
            } elseif (!$request->has('estado')) {
                $query->where('estado', 'activo'); // Por defecto solo mostrar activos
            }

            // Filtrar por tipo
            if ($request->has('tipo_veto') && $request->tipo_veto) {
                $query->where('tipo_veto', $request->tipo_veto);
            }

            $vetos = $query->orderBy('fecha_veto', 'desc')
                          ->limit(500) // Limitar resultados para evitar sobrecarga
                          ->get();

            return response()->json([
                'success' => true,
                'vetos' => $vetos
            ]);

        } catch (\Exception $e) {
            Log::error('Error al listar vetados', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al obtener la lista de vetados',
                'vetos' => []
            ], 500);
        }
    }

    /**
     * Exportar vetos a Excel
     */
    public function exportarVetos(Request $request)
    {
        try {
            $estado = $request->get('estado', '');
            
            $fileName = 'usuarios-vetados-' . ($estado ?: 'todos') . '-' . date('Y-m-d') . '.xlsx';
            
            return Excel::download(new VetosExport($estado), $fileName);

        } catch (\Exception $e) {
            Log::error('Error al exportar vetados', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al exportar la lista de vetados'
            ], 500);
        }
    }
}
