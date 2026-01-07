<?php

namespace App\Http\Controllers;

use App\Models\Reserva;
use App\Models\Espacio;
use App\Models\Usuario;
use App\Models\Profesor;
use App\Models\Solicitante;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class AdminPanelController extends Controller
{
    /**
     * Buscar usuario por RUN en profesores y solicitantes
     */
    public function buscarUsuario($run)
    {
        try {
            $usuario = null;

            // Buscar en profesores
            $profesor = Profesor::where('run', $run)->first();
            if ($profesor) {
                $usuario = [
                    'nombre' => $profesor->nombre,
                    'run' => $profesor->run,
                    'correo' => $profesor->correo,
                    'telefono' => $profesor->telefono ?? '',
                    'tipo_usuario' => 'profesor'
                ];
            } else {
                // Buscar en solicitantes
                $solicitante = Solicitante::where('run', $run)->first();
                if ($solicitante) {
                    $usuario = [
                        'nombre' => $solicitante->nombre,
                        'run' => $solicitante->run,
                        'correo' => $solicitante->correo,
                        'telefono' => $solicitante->telefono ?? '',
                        'tipo_usuario' => 'solicitante'
                    ];
                }
            }

            if ($usuario) {
                return response()->json([
                    'success' => true,
                    'usuario' => $usuario
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'mensaje' => 'Usuario no encontrado'
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Error al buscar usuario: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'mensaje' => 'Error en la búsqueda'
            ]);
        }
    }

    /**
     * Obtener espacios disponibles
     */
    public function getEspaciosDisponibles()
    {
        try {
            $espacios = Espacio::select('codigo', 'nombre', 'piso', 'capacidad')
                ->orderBy('codigo')
                ->get();

            return response()->json([
                'success' => true,
                'espacios' => $espacios
            ]);
        } catch (\Exception $e) {
            Log::error('Error al obtener espacios: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'mensaje' => 'Error al cargar espacios'
            ]);
        }
    }

    /**
     * Crear una nueva reserva
     */
    public function crearReserva(Request $request)
    {
        try {
            DB::beginTransaction();

            $data = $request->all();

            // Validar datos básicos
            $request->validate([
                'nombre' => 'required|string|max:255',
                'run' => 'required|string|max:20',
                'correo' => 'required|email|max:255',
                'tipo' => 'required|in:profesor,solicitante',
                'espacio' => 'required|string|exists:espacios,codigo',
                'fecha' => 'required|date|after_or_equal:today',
                'modulo_inicial' => 'required|integer|min:1|max:16',
                'modulo_final' => 'required|integer|min:1|max:16',
            ]);

            // Verificar que el módulo inicial no sea mayor al final
            if ($data['modulo_inicial'] > $data['modulo_final']) {
                return response()->json([
                    'success' => false,
                    'mensaje' => 'El módulo inicial no puede ser mayor al módulo final'
                ]);
            }

            // Verificar disponibilidad del espacio
            $espacioOcupado = Reserva::where('codigo_espacio', $data['espacio'])
                ->where('fecha', $data['fecha'])
                ->where('estado', 'activa')
                ->where(function($query) use ($data) {
                    $query->whereBetween('modulo_inicial', [$data['modulo_inicial'], $data['modulo_final']])
                          ->orWhereBetween('modulo_final', [$data['modulo_inicial'], $data['modulo_final']])
                          ->orWhere(function($q) use ($data) {
                              $q->where('modulo_inicial', '<=', $data['modulo_inicial'])
                                ->where('modulo_final', '>=', $data['modulo_final']);
                          });
                })
                ->exists();

            if ($espacioOcupado) {
                return response()->json([
                    'success' => false,
                    'mensaje' => 'El espacio ya está ocupado en ese horario'
                ]);
            }

            // Buscar o crear usuario
            $usuarioId = null;
            if ($data['tipo'] === 'profesor') {
                $profesor = Profesor::where('run', $data['run'])->first();
                if (!$profesor) {
                    $profesor = Profesor::create([
                        'nombre' => $data['nombre'],
                        'run' => $data['run'],
                        'correo' => $data['correo'],
                        'telefono' => $data['telefono'] ?? null,
                    ]);
                }
                $usuarioId = $profesor->id;
            } else {
                $solicitante = Solicitante::where('run', $data['run'])->first();
                if (!$solicitante) {
                    $solicitante = Solicitante::create([
                        'nombre' => $data['nombre'],
                        'run' => $data['run'],
                        'correo' => $data['correo'],
                        'telefono' => $data['telefono'] ?? null,
                        'tipo_solicitante' => 'externo',
                    ]);
                }
                $usuarioId = $solicitante->id;
            }

            // Crear la reserva
            $reserva = Reserva::create([
                'run_profesor' => $data['tipo'] === 'profesor' ? $data['run'] : null,
                'run_solicitante' => $data['tipo'] === 'solicitante' ? $data['run'] : null,
                'codigo_espacio' => $data['espacio'],
                'fecha' => $data['fecha'],
                'modulo_inicial' => $data['modulo_inicial'],
                'modulo_final' => $data['modulo_final'],
                'estado' => 'activa',
                'observaciones' => $data['observaciones'] ?? null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Si la reserva es para hoy y estamos en el horario, ocupar el espacio
            $fechaReserva = Carbon::parse($data['fecha']);
            $hoy = Carbon::today();
            
            if ($fechaReserva->isSameDay($hoy)) {
                // Verificar si estamos en el horario de la reserva
                $horaActual = Carbon::now()->hour;
                $moduloActual = $this->obtenerModuloActual();
                
                if ($moduloActual >= $data['modulo_inicial'] && $moduloActual <= $data['modulo_final']) {
                    // Ocupar el espacio
                    Espacio::where('codigo', $data['espacio'])
                        ->update(['estado' => 'Ocupado']);
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'mensaje' => 'Reserva creada exitosamente',
                'reserva_id' => $reserva->id
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al crear reserva: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'mensaje' => 'Error al crear la reserva: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Obtener todas las reservas para administración
     */
    public function getReservas(Request $request)
    {
        try {
            $query = DB::connection('tenant')->table('reservas as r')
                ->leftJoin('profesores as p', 'r.run_profesor', '=', 'p.run')
                ->leftJoin('solicitantes as s', 'r.run_solicitante', '=', 's.run')
                ->select(
                    'r.id',
                    'r.codigo_espacio',
                    'r.fecha',
                    'r.modulo_inicial',
                    'r.modulo_final',
                    'r.estado',
                    'r.observaciones',
                    'r.created_at',
                    DB::raw('COALESCE(p.nombre, s.nombre) as nombre_responsable'),
                    DB::raw('COALESCE(r.run_profesor, r.run_solicitante) as run_responsable')
                )
                ->orderBy('r.fecha', 'desc')
                ->orderBy('r.modulo_inicial', 'asc');

            // Aplicar filtros si existen
            if ($request->has('estado') && $request->estado) {
                $query->where('r.estado', $request->estado);
            }

            if ($request->has('fecha') && $request->fecha) {
                $query->where('r.fecha', $request->fecha);
            }

            $reservas = $query->get();

            return response()->json([
                'success' => true,
                'reservas' => $reservas
            ]);
        } catch (\Exception $e) {
            Log::error('Error al obtener reservas: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'mensaje' => 'Error al cargar reservas'
            ]);
        }
    }

    /**
     * Cambiar estado de una reserva
     */
    public function cambiarEstadoReserva(Request $request, $reservaId)
    {
        try {
            $request->validate([
                'estado' => 'required|in:activa,finalizada'
            ]);

            $reserva = Reserva::findOrFail($reservaId);
            $estadoAnterior = $reserva->estado;
            
            $reserva->estado = $request->estado;
            $reserva->save();

            // Si se finaliza una reserva activa para hoy, liberar el espacio
            if ($request->estado === 'finalizada' && $estadoAnterior === 'activa') {
                $fechaReserva = Carbon::parse($reserva->fecha);
                $hoy = Carbon::today();
                
                if ($fechaReserva->isSameDay($hoy)) {
                    // Verificar si no hay otras reservas activas para este espacio hoy
                    $otrasReservasActivas = Reserva::where('codigo_espacio', $reserva->codigo_espacio)
                        ->where('fecha', $reserva->fecha)
                        ->where('estado', 'activa')
                        ->where('id', '!=', $reservaId)
                        ->count();

                    if ($otrasReservasActivas === 0) {
                        Espacio::where('codigo', $reserva->codigo_espacio)
                            ->update(['estado' => 'Disponible']);
                    }
                }
            }
            // Si se activa una reserva para hoy y estamos en el horario, ocupar el espacio
            elseif ($request->estado === 'activa' && $estadoAnterior === 'finalizada') {
                $fechaReserva = Carbon::parse($reserva->fecha);
                $hoy = Carbon::today();
                
                if ($fechaReserva->isSameDay($hoy)) {
                    $moduloActual = $this->obtenerModuloActual();
                    
                    if ($moduloActual >= $reserva->modulo_inicial && $moduloActual <= $reserva->modulo_final) {
                        Espacio::where('codigo', $reserva->codigo_espacio)
                            ->update(['estado' => 'Ocupado']);
                    }
                }
            }

            return response()->json([
                'success' => true,
                'mensaje' => 'Estado de reserva actualizado correctamente'
            ]);

        } catch (\Exception $e) {
            Log::error('Error al cambiar estado de reserva: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'mensaje' => 'Error al cambiar estado de reserva'
            ]);
        }
    }

    /**
     * Obtener todos los espacios para administración
     */
    public function getEspacios(Request $request)
    {
        try {
            $query = Espacio::select('codigo', 'nombre', 'tipo', 'piso', 'capacidad', 'estado')
                ->orderBy('codigo');

            // Aplicar filtros si existen
            if ($request->has('estado') && $request->estado) {
                $query->where('estado', $request->estado);
            }

            if ($request->has('piso') && $request->piso) {
                $query->where('piso', $request->piso);
            }

            $espacios = $query->get();

            return response()->json([
                'success' => true,
                'espacios' => $espacios
            ]);
        } catch (\Exception $e) {
            Log::error('Error al obtener espacios: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'mensaje' => 'Error al cargar espacios'
            ]);
        }
    }

    /**
     * Cambiar estado de un espacio
     */
    public function cambiarEstadoEspacio(Request $request, $codigoEspacio)
    {
        try {
            $request->validate([
                'estado' => 'required|in:Disponible,Ocupado,Mantenimiento'
            ]);

            $espacio = Espacio::where('codigo', $codigoEspacio)->firstOrFail();
            $espacio->estado = $request->estado;
            $espacio->save();

            // Si se libera un espacio ocupado, finalizar las reservas activas de hoy
            if ($request->estado === 'Disponible') {
                $hoy = Carbon::today()->toDateString();
                
                Reserva::where('codigo_espacio', $codigoEspacio)
                    ->where('fecha', $hoy)
                    ->where('estado', 'activa')
                    ->update(['estado' => 'finalizada']);
            }

            return response()->json([
                'success' => true,
                'mensaje' => 'Estado del espacio actualizado correctamente'
            ]);

        } catch (\Exception $e) {
            Log::error('Error al cambiar estado de espacio: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'mensaje' => 'Error al cambiar estado del espacio'
            ]);
        }
    }

    /**
     * Vaciar todas las reservas activas
     */
    public function vaciarReservas()
    {
        try {
            DB::beginTransaction();

            // Obtener reservas activas de hoy
            $hoy = Carbon::today()->toDateString();
            
            $reservasActivas = Reserva::where('estado', 'activa')
                ->where('fecha', $hoy)
                ->get();

            $reservasFinalizadas = 0;
            $espaciosLiberados = [];

            foreach ($reservasActivas as $reserva) {
                $reserva->estado = 'finalizada';
                $reserva->save();
                $reservasFinalizadas++;

                // Agregar espacio a la lista de espacios a liberar
                if (!in_array($reserva->codigo_espacio, $espaciosLiberados)) {
                    $espaciosLiberados[] = $reserva->codigo_espacio;
                }
            }

            // Liberar todos los espacios que tenían reservas activas
            if (count($espaciosLiberados) > 0) {
                Espacio::whereIn('codigo', $espaciosLiberados)
                    ->update(['estado' => 'Disponible']);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'mensaje' => 'Operación completada exitosamente',
                'reservas_finalizadas' => $reservasFinalizadas,
                'espacios_liberados' => count($espaciosLiberados)
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al vaciar reservas: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'mensaje' => 'Error al vaciar las reservas'
            ]);
        }
    }

    /**
     * Obtener el módulo actual basado en la hora
     */
    private function obtenerModuloActual()
    {
        // Esta función debería obtener el módulo actual basado en los horarios
        // Por ahora retornamos un valor por defecto
        $hora = Carbon::now()->hour;
        
        // Horarios aproximados (ajustar según sea necesario)
        if ($hora >= 8 && $hora < 9) return 1;
        if ($hora >= 9 && $hora < 10) return 2;
        if ($hora >= 10 && $hora < 11) return 3;
        if ($hora >= 11 && $hora < 12) return 4;
        if ($hora >= 12 && $hora < 13) return 5;
        if ($hora >= 13 && $hora < 14) return 6;
        if ($hora >= 14 && $hora < 15) return 7;
        if ($hora >= 15 && $hora < 16) return 8;
        if ($hora >= 16 && $hora < 17) return 9;
        if ($hora >= 17 && $hora < 18) return 10;
        if ($hora >= 18 && $hora < 19) return 11;
        if ($hora >= 19 && $hora < 20) return 12;
        if ($hora >= 20 && $hora < 21) return 13;
        if ($hora >= 21 && $hora < 22) return 14;
        if ($hora >= 22 && $hora < 23) return 15;
        if ($hora >= 23 || $hora < 8) return 16;
        
        return 1; // Por defecto
    }
}
