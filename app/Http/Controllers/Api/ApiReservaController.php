<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Espacio;
use App\Models\Modulo;
use App\Models\Profesor;
use App\Models\Reserva;
use App\Models\Solicitante;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ApiReservaController extends Controller
{
    public function verificarEspacio($userId, $espacioId)
    {
        try {
            $espacio = Espacio::findOrFail($espacioId);
            $usuario = User::where('id', $userId)
                ->whereHas('roles', function ($query) {
                    $query->where('name', 'profesor');
                })
                ->firstOrFail();

            // Obtener la hora actual
            $ahora = Carbon::now();
            $diaActual = strtolower($ahora->locale('es')->isoFormat('dddd'));

            // Verificar si el espacio está ocupado
            $reservaActiva = Reserva::where('id_espacio', $espacioId)
                ->where('fecha_reserva', $ahora->toDateString())
                ->where('hora', '<=', $ahora->format('H:i:s'))
                ->whereNull('hora_salida')
                ->first();

            if ($reservaActiva) {
                return response()->json([
                    'estado' => 'ocupado',
                    'profesor_nombre' => $reservaActiva->profesor->name ?? ($reservaActiva->user->name ?? 'N/A'),
                    'hora_entrada' => $reservaActiva->hora
                ]);
            }

            $run = $usuario->run;

            $tieneClaseProgramada = DB::connection('tenant')
                ->table('planificacion_asignaturas as pa')
                ->join('horarios as h', 'pa.id_horario', '=', 'h.id_horario')
                ->join('modulos as m', 'pa.id_modulo', '=', 'm.id_modulo')
                ->where('pa.id_espacio', $espacioId)
                ->where('h.run_profesor', $run)
                ->where('m.dia', $diaActual)
                ->where('m.hora_inicio', '<=', $ahora->format('H:i:s'))
                ->where('m.hora_termino', '>=', $ahora->format('H:i:s'))
                ->exists();

            return response()->json([
                'estado' => 'disponible',
                'tieneClaseProgramada' => $tieneClaseProgramada
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error al verificar el espacio: ' . $e->getMessage()
            ], 500);
        }
    }

    public function registrarUsoEspacio(Request $request)
    {
        try {
            // Validar datos de entrada
            $request->validate([
                'run' => 'required|exists:tenant.profesors,run_profesor',
                'espacio_id' => 'required|exists:tenant.espacios,id_espacio'
            ]);

            // Obtener la hora actual
            $horaActual = Carbon::now();
            $diaActual = strtolower($horaActual->locale('es')->isoFormat('dddd'));
            $horaActualStr = $horaActual->format('H:i:s');

            $espacio = Espacio::findOrFail($request->espacio_id);

            // 1. Verificar si tiene clase programada (antes del check de ocupado para permitir el cierre forzado)
            $tieneClase = DB::connection('tenant')
                ->table('planificacion_asignaturas as pa')
                ->join('horarios as h', 'pa.id_horario', '=', 'h.id_horario')
                ->join('modulos as m', 'pa.id_modulo', '=', 'm.id_modulo')
                ->join('asignaturas as a', 'pa.id_asignatura', '=', 'a.id_asignatura')
                ->where('pa.id_espacio', $request->espacio_id)
                ->where('h.run_profesor', $request->run)
                ->where('m.dia', $diaActual)
                ->where(function ($query) use ($horaActualStr) {
                    $query
                        ->where('m.hora_inicio', '<=', $horaActualStr)
                        ->where('m.hora_termino', '>=', $horaActualStr);
                })
                ->select('a.id_asignatura', 'a.nombre_asignatura', 'm.hora_inicio', 'm.hora_termino')
                ->first();

            $forzado = false;

            // 2. Verificar si el espacio está ocupado
            if ($espacio->estado === 'Ocupado') {
                // Si el docente actual tiene clase programada, forzamos la liberación
                if ($tieneClase) {
                    $reservaAnterior = Reserva::where('id_espacio', $request->espacio_id)
                        ->where('estado', 'activa')
                        ->orderBy('created_at', 'desc')
                        ->first();

                    if ($reservaAnterior) {
                        $reservaAnterior->estado = 'finalizada';
                        $reservaAnterior->hora_salida = $horaActualStr;
                        $mensajeForzado = 'Cierre forzado por Docente ' . $request->run . ' al iniciar clase programada: ' . $tieneClase->nombre_asignatura;
                        $reservaAnterior->observaciones = trim(($reservaAnterior->observaciones ?? '') . "\n" . $mensajeForzado);
                        $reservaAnterior->save();
                        $forzado = true;
                    }
                } else {
                    return response()->json([
                        'success' => false,
                        'message' => 'El espacio se encuentra ocupado'
                    ], 400);
                }
            }

            if (!$tieneClase) {
                $profesor = Profesor::where('run_profesor', $request->run)->first();
                $asignaturaLibre = $profesor ? $profesor->asignaturas()->first() : null;

                $moduloActual = Modulo::where('dia', $diaActual)
                    ->where('hora_inicio', '<=', $horaActualStr)
                    ->where('hora_termino', '>=', $horaActualStr)
                    ->first();

                $horaInicio = $moduloActual ? $moduloActual->hora_inicio : $horaActualStr;
                $horaTermino = $moduloActual ? $moduloActual->hora_termino : Carbon::parse($horaActualStr)->addMinutes(50)->format('H:i:s');

                $tieneClase = (object) [
                    'id_asignatura' => $asignaturaLibre ? $asignaturaLibre->id_asignatura : null,
                    'nombre_asignatura' => 'Uso libre',
                    'hora_inicio' => $horaInicio,
                    'hora_termino' => $horaTermino
                ];
            }

            DB::connection('tenant')->beginTransaction();
            try {
                // Crear la reserva
                $reserva = new Reserva();
                $reserva->id_reserva = Reserva::generarIdUnico();
                $reserva->run_profesor = $request->run;
                $reserva->id_espacio = $request->espacio_id;
                $reserva->id_asignatura = $tieneClase->id_asignatura ?? null;
                $reserva->fecha_reserva = $horaActual->format('Y-m-d');
                $reserva->hora = $horaActualStr;
                $reserva->tipo_reserva = $tieneClase->nombre_asignatura === 'Uso libre' ? 'espontanea' : 'clase';
                $reserva->estado = 'activa';
                $reserva->hora_salida = null;
                $reserva->created_at = $horaActual;
                $reserva->updated_at = $horaActual;
                $reserva->save();

                $espacio->estado = 'Ocupado';
                $espacio->save();

                \App\Models\ClaseNoRealizada::limpiarRegistrosIncorrectos(
                    $request->espacio_id,
                    $horaActual->format('Y-m-d'),
                    $horaActual->format('H:i:s'),
                    $request->run
                );

                DB::connection('tenant')->commit();

                return response()->json([
                    'success' => true,
                    'message' => $forzado
                        ? 'Debido a que la sala está ocupada y usted tiene una clase programada, se ha procedido a liberar el espacio.'
                        : 'Uso del espacio registrado correctamente',
                    'espacio_nombre' => $espacio->nombre_espacio,
                    'hora_termino' => $tieneClase->hora_termino,
                    'asignatura' => $tieneClase->nombre_asignatura
                ]);
            } catch (\Exception $e) {
                DB::connection('tenant')->rollback();
                throw $e;
            }
        } catch (\Exception $e) {
            \Log::error('Error en registrarUsoEspacio: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al registrar el uso del espacio: ' . $e->getMessage()
            ], 500);
        }
    }

    public function registrarSalidaClase(Request $request)
    {
        try {
            \Log::info('Datos recibidos en registrarSalidaClase:', $request->all());

            // Validar los datos de entrada
            $request->validate([
                'run' => 'required',
                'espacio_id' => 'required'
            ]);

            DB::connection('tenant')->beginTransaction();

            // Buscar la reserva activa para el espacio sin restricción de fecha
            $reserva = Reserva::where('id_espacio', $request->espacio_id)
                ->where('estado', 'activa')
                ->first();

            \Log::info('Reserva encontrada:', ['reserva' => $reserva]);

            if (!$reserva) {
                DB::connection('tenant')->rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'No se encontró una reserva activa para este espacio'
                ], 404);
            }

            // Actualizar la reserva
            $reserva->estado = 'finalizada';
            $reserva->hora_salida = Carbon::now()->format('H:i:s');
            $reserva->updated_at = Carbon::now();
            $reserva->save();

            // Actualizar el estado del espacio
            $espacio = Espacio::find($request->espacio_id);
            if ($espacio) {
                $espacio->estado = 'Disponible';
                $espacio->save();
            }

            // Buscar si hay reservas finalizadas automáticamente que el profesor está devolviendo tarde
            $reservaAutoFinalizada = Reserva::where('id_espacio', $request->espacio_id)
                ->where('estado', 'finalizada')
                ->where('fecha_reserva', Carbon::now()->toDateString())
                ->whereNotNull('observaciones')
                ->where('observaciones', 'LIKE', '%finalizó automáticamente por excederse en el tiempo%')
                ->where(function ($query) use ($request) {
                    $query
                        ->where('run_profesor', $request->run)
                        ->orWhere('run_solicitante', $request->run);
                })
                ->orderBy('updated_at', 'desc')
                ->first();

            if ($reservaAutoFinalizada) {
                // El profesor está devolviendo la llave después de que la reserva fue auto-finalizada
                $observacionActual = $reservaAutoFinalizada->observaciones ?? '';
                $nuevaObservacion = "\nProfesor finalizó la clase más tarde y devolvió llave de acceso a las " . Carbon::now()->format('H:i:s') . '.';
                $reservaAutoFinalizada->observaciones = $observacionActual . $nuevaObservacion;
                $reservaAutoFinalizada->save();

                \Log::info("Reserva auto-finalizada {$reservaAutoFinalizada->id_reserva} actualizada: profesor devolvió llave tarde");
            }

            DB::connection('tenant')->commit();

            return response()->json([
                'success' => true,
                'message' => 'Salida registrada correctamente'
            ]);
        } catch (\Exception $e) {
            DB::connection('tenant')->rollBack();
            \Log::error('Error al registrar salida: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            return response()->json([
                'success' => false,
                'message' => 'Error al registrar la salida: ' . $e->getMessage()
            ], 500);
        }
    }

    public function registrarReservaEspontanea(Request $request)
    {
        try {
            $request->validate([
                'user_id' => 'required|exists:users,run',
                'espacio_id' => 'required|exists:tenant.espacios,id_espacio',
                'modulos' => 'required|array|min:1',
                'modulos.*' => 'required|string'
            ]);

            // Verificar que el usuario es profesor
            $usuario = User::where('run', $request->user_id)
                ->whereHas('roles', function ($query) {
                    $query->where('name', 'profesor');
                })
                ->firstOrFail();

            $fechaReserva = now()->format('Y-m-d');
            $horaActual = now()->format('H:i:s');

            $idsModulos = $request->modulos;
            $modulos = Modulo::whereIn('id_modulo', $idsModulos)->orderBy('hora_inicio')->get();

            $horaInicio = $modulos->first()->hora_inicio;
            $horaTermino = $modulos->last()->hora_termino;

            // Verificar si ya existe una reserva para alguno de esos módulos
            $existeReserva = Reserva::where('id_espacio', $request->espacio_id)
                ->where('fecha_reserva', $fechaReserva)
                ->where(function ($query) use ($horaInicio, $horaTermino) {
                    $query->whereRaw('TIME(hora) BETWEEN ? AND ?', [$horaInicio, $horaTermino]);
                })
                ->exists();

            if ($existeReserva) {
                return response()->json([
                    'success' => false,
                    'message' => 'El espacio no está disponible en el horario seleccionado'
                ], 400);
            }

            $reserva = Reserva::create([
                'id_reserva' => Reserva::generarIdUnico(),
                'hora' => $horaInicio,
                'fecha_reserva' => $fechaReserva,
                'id_espacio' => $request->espacio_id,
                'run_profesor' => $request->user_id,
                'tipo_reserva' => 'espontanea',
                'estado' => 'activa'
            ]);

            return response()->json([
                'success' => true,
                'mensaje' => 'Reserva espontánea registrada correctamente',
                'espacio_nombre' => $reserva->espacio->nombre_espacio ?? ''
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al registrar la reserva: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getEspacioEstado($id)
    {
        try {
            $espacio = Espacio::findOrFail($id);
            return response()->json([
                'success' => true,
                'estado' => $espacio->estado
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener el estado del espacio: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getReservaActiva($id)
    {
        try {
            $espacio = Espacio::where('id_espacio', $id)->first();

            if (!$espacio) {
                return response()->json([
                    'success' => false,
                    'message' => 'Espacio no encontrado'
                ], 404);
            }

            if ($espacio->estado === 'Ocupado') {
                $ultimaReserva = Reserva::where('id_espacio', $id)
                    ->where('estado', 'activa')
                    ->orderBy('created_at', 'desc')
                    ->first();

                if ($ultimaReserva) {
                    $nombreUsuario = '';
                    $emailUsuario = '';

                    if ($ultimaReserva->run_profesor) {
                        $usuario = Profesor::where('run_profesor', $ultimaReserva->run_profesor)->first();
                        $nombreUsuario = $usuario ? $usuario->name : 'Profesor no encontrado';
                        $emailUsuario = $usuario ? $usuario->email : 'Sin información';
                    } elseif ($ultimaReserva->run_solicitante) {
                        $solicitante = Solicitante::on('tenant')->where('run_solicitante', $ultimaReserva->run_solicitante)->first();
                        $nombreUsuario = $solicitante ? $solicitante->nombre : 'Solicitante no encontrado';
                        $emailUsuario = $solicitante ? $solicitante->correo : 'Sin información';
                    }

                    return response()->json([
                        'success' => true,
                        'reserva' => [
                            'id' => $ultimaReserva->id_reserva,
                            'profesor_nombre' => $nombreUsuario,
                            'profesor_email' => $emailUsuario,
                            'hora_inicio' => $ultimaReserva->hora,
                            'hora_termino' => $ultimaReserva->hora_salida,
                            'fecha' => $ultimaReserva->fecha_reserva,
                            'espacio_nombre' => $espacio->nombre_espacio,
                            'tipo_reserva' => 'Ocupación sin reserva',
                            'estado_espacio' => 'Ocupado'
                        ]
                    ]);
                }

                return response()->json([
                    'success' => true,
                    'reserva' => [
                        'id' => null,
                        'profesor_nombre' => 'Sin información',
                        'profesor_email' => 'Sin información',
                        'hora_inicio' => null,
                        'hora_termino' => null,
                        'fecha' => null,
                        'espacio_nombre' => $espacio->nombre_espacio,
                        'tipo_reserva' => 'Ocupación sin reserva',
                        'estado_espacio' => 'Ocupado'
                    ]
                ]);
            }

            // Si no está ocupado, buscamos reservas activas
            $reserva = Reserva::where('id_espacio', $id)
                ->where('fecha_reserva', Carbon::today())
                ->where('hora', '<=', Carbon::now()->format('H:i:s'))
                ->whereNull('hora_salida')
                ->with(['profesor', 'espacio'])
                ->first();

            if (!$reserva) {
                return response()->json([
                    'success' => false,
                    'message' => 'No hay reserva activa para este espacio'
                ]);
            }

            // Obtener información del usuario (profesor o solicitante)
            $nombreUsuario = '';
            $emailUsuario = '';

            if ($reserva->run_profesor) {
                $usuario = Profesor::where('run_profesor', $reserva->run_profesor)->first();
                $nombreUsuario = $usuario ? $usuario->name : 'Profesor no encontrado';
                $emailUsuario = $usuario ? $usuario->email : 'Sin información';
            } elseif ($reserva->run_solicitante) {
                $solicitante = Solicitante::on('tenant')->where('run_solicitante', $reserva->run_solicitante)->first();
                $nombreUsuario = $solicitante ? $solicitante->nombre : 'Solicitante no encontrado';
                $emailUsuario = $solicitante ? $solicitante->correo : 'Sin información';
            }

            return response()->json([
                'success' => true,
                'reserva' => [
                    'id' => $reserva->id_reserva,
                    'profesor_nombre' => $nombreUsuario,
                    'profesor_email' => $emailUsuario,
                    'hora_inicio' => $reserva->hora,
                    'hora_termino' => $reserva->hora_salida,
                    'fecha' => $reserva->fecha_reserva,
                    'espacio_nombre' => $reserva->espacio->nombre_espacio,
                    'tipo_reserva' => $reserva->tipo_reserva,
                    'estado_espacio' => $espacio->estado
                ]
            ]);
        } catch (\Exception $e) {
            \Log::error('Error al obtener reserva activa: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener la reserva activa'
            ], 500);
        }
    }
}
