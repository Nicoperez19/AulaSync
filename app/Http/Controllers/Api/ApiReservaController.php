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
use App\Traits\RunNormalizer;

class ApiReservaController extends Controller
{
    use RunNormalizer;

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
                'run' => 'required',
                'espacio_id' => 'required|exists:tenant.espacios,id_espacio'
            ]);

            // Normalizar RUN
            $runNormalizado = $this->normalizeRun($request->run);


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
                ->where('h.run_profesor', $runNormalizado)

                ->where('m.dia', $diaActual)
                ->where(function ($query) use ($horaActual, $horaActualStr) {
                    $tiempoMas20 = $horaActual->copy()->addMinutes(20)->toTimeString();
                    $tiempoMas40 = $horaActual->copy()->addMinutes(40)->toTimeString();
                    
                    $query->whereRaw("m.hora_inicio <= CASE WHEN m.hora_inicio LIKE '08:10%' THEN ? ELSE ? END", [$tiempoMas40, $tiempoMas20])
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
                $profesor = Profesor::where('run_profesor', $runNormalizado)->first();

                $asignaturaLibre = $profesor ? $profesor->asignaturas()->first() : null;

                // Buscar el módulo actual según la hora
                $tiempoMas20 = $horaActual->copy()->addMinutes(20)->toTimeString();
                $tiempoMas40 = $horaActual->copy()->addMinutes(40)->toTimeString();

                $moduloActual = Modulo::where('dia', $diaActual)
                    ->whereRaw("hora_inicio <= CASE WHEN hora_inicio LIKE '08:10%' THEN ? ELSE ? END", [$tiempoMas40, $tiempoMas20])
                    ->where('hora_termino', '>=', $horaActualStr)
                    ->first();

                // Si no hay módulo actual, usar horarios por defecto
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
                $reserva->run_profesor = $runNormalizado;

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


            // Validar los datos de entrada
            $request->validate([
                'run' => 'required',
                'espacio_id' => 'required'
            ]);

            // Normalizar RUN
            $runNormalizado = $this->normalizeRun($request->run);


            DB::connection('tenant')->beginTransaction();

            // Buscar la reserva activa para el espacio sin restricción de fecha
            $reserva = Reserva::where('id_espacio', $request->espacio_id)
                ->where('estado', 'activa')
                ->first();



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
                ->whereNotNull('observaciones')
                ->where('observaciones', 'LIKE', '%finalizó automáticamente por excederse en el tiempo%')
                ->where(function ($query) use ($runNormalizado) {
                    $query->where('run_profesor', $runNormalizado)
                        ->orWhere('run_solicitante', $runNormalizado);
                })

                ->orderBy('updated_at', 'desc')
                ->first();

            if ($reservaAutoFinalizada) {
                // El profesor está devolviendo la llave después de que la reserva fue auto-finalizada
                $observacionActual = $reservaAutoFinalizada->observaciones ?? '';
                $nuevaObservacion = "\nProfesor finalizó la clase más tarde y devolvió llave de acceso a las " . Carbon::now()->format('H:i:s') . '.';
                $reservaAutoFinalizada->observaciones = $observacionActual . $nuevaObservacion;
                $reservaAutoFinalizada->save();

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
                'user_id' => 'required',
                'espacio_id' => 'required|exists:tenant.espacios,id_espacio',
                'modulos' => 'required|array|min:1',
                'modulos.*' => 'required|string'
            ]);

            // Normalizar RUN
            $runNormalizado = $this->normalizeRun($request->user_id);

            // Verificar que el usuario es profesor
            $usuario = User::where('run', $runNormalizado)

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

            // Calcular número de módulo de inicio y fin
            $numModuloInicio = 1;
            $numModuloFin = 1;
            if ($modulos->isNotEmpty()) {
                $numModuloInicio = \App\Helpers\ModulosHelper::getNumeroModulo($modulos->first()->id_modulo);
                $numModuloFin = \App\Helpers\ModulosHelper::getNumeroModulo($modulos->last()->id_modulo);
            }

            $reserva = Reserva::create([
                'id_reserva' => Reserva::generarIdUnico(),
                'hora' => $horaInicio,
                'hora_salida' => $horaTermino,
                'fecha_reserva' => $fechaReserva,
                'id_espacio' => $request->espacio_id,
                'run_profesor' => $runNormalizado,
                'tipo_reserva' => 'espontanea',
                'modulo_inicio' => $numModuloInicio,
                'modulo_fin' => $numModuloFin,
                'modulos' => count($idsModulos),
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

    // Método duplicado - removido para evitar confusión
    // Se usa HorarioController::devolverLlaves en su lugar
    /*
    public function devolverLlaves(Request $request)
    {
        try {
            $request->validate([
                'run' => 'required',
                'id_espacio' => 'required|exists:tenant.espacios,id_espacio'
            ]);

            $espacio = Espacio::where('id_espacio', $request->id_espacio)->first();

            if (!$espacio) {
                return response()->json([
                    'success' => false,
                    'message' => 'Espacio no encontrado'
                ], 404);
            }

            // Verificar si el espacio está ocupado
            if ($espacio->estado !== 'Ocupado') {
                return response()->json([
                    'success' => false,
                    'message' => 'El espacio no está ocupado'
                ], 400);
            }

            // Buscar la reserva activa para este espacio y usuario (profesor o solicitante)
            $reservaActiva = Reserva::where('id_espacio', $request->id_espacio)
                ->where('estado', 'activa')
                ->where('fecha_reserva', Carbon::today())
                ->where(function($query) use ($request) {
                    $query->where('run_profesor', $request->run)
                          ->orWhere('run_solicitante', $request->run);
                })
                ->first();

            if (!$reservaActiva) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se encontró una reserva activa para este usuario y espacio'
                ], 404);
            }

            // Obtener información del usuario (profesor o solicitante)
            $usuario = null;
            $nombreUsuario = '';

            if ($reservaActiva->run_profesor) {
                $usuario = Profesor::where('run_profesor', $reservaActiva->run_profesor)->first();
                $nombreUsuario = $usuario ? $usuario->name : 'Profesor no encontrado';
            } elseif ($reservaActiva->run_solicitante) {
                $solicitante = Solicitante::on('tenant')->where('run_solicitante', $reservaActiva->run_solicitante)->first();
                $nombreUsuario = $solicitante ? $solicitante->nombre : 'Solicitante no encontrado';
            }

            // Actualizar la reserva
            $reservaActiva->update([
                'estado' => 'finalizada',
                'hora_salida' => Carbon::now()->format('H:i:s')
            ]);

            // Cambiar el estado del espacio a disponible
            $espacio->update(['estado' => 'Disponible']);

            return response()->json([
                'success' => true,
                'message' => 'Devolución completada',
                'data' => [
                    'usuario' => $nombreUsuario,
                    'espacio' => $espacio->nombre_espacio,
                    'hora_devolucion' => Carbon::now()->format('H:i:s')
                ]
            ]);

        } catch (\Exception $e) {
            \Log::error('Error en devolución de llaves: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al procesar la devolución: ' . $e->getMessage()
            ], 500);
        }
    }
    */

    // Método migrado desde App\Http\Controllers\ReservaController
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
                ->where('hora_salida', '>=', Carbon::now()->format('H:i:s'))
                ->with(['user', 'espacio'])
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

    /**
     * Liberar espacio bloqueado + Registrar nuevo uso
     * 
     * Permite que un profesor con clase programada libere un espacio
     * que está ocupado por un profesor anterior que no devolvió la llave.
     * 
     * Flujo:
     * 1. Validar que el profesor tiene clase programada en ese espacio/horario
     * 2. Finalizar la reserva anterior con anotación
     * 3. Crear nueva reserva para el profesor actual
     * 4. Cambiar estado del espacio a "Ocupado"
     * 5. Registrar todo en logs y observaciones para auditoría
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function liberarYRegistrarUso(Request $request)
    {
        try {
            // Validar datos de entrada
            $request->validate([
                'run' => 'required',
                'espacio_id' => 'required|exists:tenant.espacios,id_espacio'
            ]);

            $runNormalizado = $this->normalizeRun($request->run);
            $horaActual = Carbon::now();
            $diaActual = strtolower($horaActual->locale('es')->isoFormat('dddd'));
            $horaActualStr = $horaActual->format('H:i:s');

            // 1️⃣ VALIDACIÓN: Verificar que hay clase programada en este espacio/horario
            // Esto asegura que el profesor que llega tiene horario en ese espacio
            $tieneClaseProgramada = DB::connection('tenant')->table('planificacion_asignaturas as pa')
                ->join('modulos as m', 'pa.id_modulo', '=', 'm.id_modulo')
                ->join('asignaturas as a', 'pa.id_asignatura', '=', 'a.id_asignatura')
                ->where('pa.id_espacio', $request->espacio_id)
                ->where('m.dia', $diaActual)
                ->where(function ($query) use ($horaActualStr, $horaActual) {
                    $tiempoMas20 = $horaActual->copy()->addMinutes(20)->toTimeString();
                    $tiempoMas40 = $horaActual->copy()->addMinutes(40)->toTimeString();
                    
                    $query->whereRaw("m.hora_inicio <= CASE WHEN m.hora_inicio LIKE '08:10%' THEN ? ELSE ? END", [$tiempoMas40, $tiempoMas20])
                          ->where('m.hora_termino', '>=', $horaActualStr);
                })
                ->select('pa.id_asignatura', 'a.nombre_asignatura', 'm.hora_inicio', 'm.hora_termino', 'pa.id_modulo')
                ->first();

            if (!$tieneClaseProgramada) {
                return response()->json([
                    'success' => false,
                    'message' => 'No tiene clase programada en este espacio a esta hora. No puede liberar la sala.',
                    'error_code' => 'no_scheduled_class'
                ], 403);
            }

            DB::connection('tenant')->beginTransaction();

            try {
                $espacio = Espacio::findOrFail($request->espacio_id);

                // 2️⃣ BUSCAR Y FINALIZAR RESERVA ANTERIOR
                $reservaAnterior = Reserva::where('id_espacio', $request->espacio_id)
                    ->where('estado', 'activa')
                    ->first();

                $reservaAnteriorFinalizadaId = null;
                $profesorAnterior = null;

                if ($reservaAnterior) {
                    $reservaAnteriorFinalizadaId = $reservaAnterior->id_reserva;
                    $profesorAnterior = $reservaAnterior->profesor ? $reservaAnterior->profesor->name : 'Desconocido';

                    // Anotar la causa de la finalización
                    $motivoLiberacion = "LIBERADA: Profesor {$profesorAnterior} no devolvió la llave. " .
                        "Liberada por profesor con clase programada ({$tieneClaseProgramada->nombre_asignatura}) " .
                        "el " . $horaActual->format('d/m/Y H:i:s');

                    $reservaAnterior->estado = 'finalizada';
                    $reservaAnterior->hora_salida = $horaActualStr;
                    $reservaAnterior->observaciones = ($reservaAnterior->observaciones ?? '') . " | {$motivoLiberacion}";
                    $reservaAnterior->save();

                    \Log::warning('🔑 LIBERACIÓN DE LLAVE', [
                        'reserva_liberada' => $reservaAnteriorFinalizadaId,
                        'profesor_anterior' => $profesorAnterior,
                        'profesor_actual' => $runNormalizado,
                        'espacio_id' => $request->espacio_id,
                        'espacio_nombre' => $espacio->nombre_espacio,
                        'motivo' => 'Llave no devuelta',
                        'timestamp' => $horaActual->format('Y-m-d H:i:s')
                    ]);
                }

                // 3️⃣ CREAR NUEVA RESERVA PARA PROFESOR ACTUAL
                $nuevaReserva = new Reserva();
                $nuevaReserva->id_reserva = Reserva::generarIdUnico();
                $nuevaReserva->run_profesor = $runNormalizado;
                $nuevaReserva->id_espacio = $request->espacio_id;
                $nuevaReserva->id_asignatura = $tieneClaseProgramada->id_asignatura ?? null;

                // Encontrar bloques consecutivos de planificación
                $numModuloActual = 1;
                $moduloFin = 1;
                $cantidadModulos = 1;
                $horaSalidaBloque = $tieneClaseProgramada->hora_termino ?? null;

                if ($tieneClaseProgramada && !empty($tieneClaseProgramada->id_modulo)) {
                    $periodo = \App\Helpers\SemesterHelper::getCurrentPeriod();
                    $planificacionesMismoBloque = \App\Models\Planificacion_Asignatura::with('modulo')
                        ->where('id_espacio', $request->espacio_id)
                        ->where('id_asignatura', $tieneClaseProgramada->id_asignatura)
                        ->whereHas('horario', function ($q) use ($periodo) {
                            $q->where('periodo', $periodo);
                        })
                        ->whereHas('modulo', function ($q) use ($diaActual) {
                            $q->where('dia', $diaActual);
                        })
                        ->get();

                    $numModuloActual = \App\Helpers\ModulosHelper::getNumeroModulo($tieneClaseProgramada->id_modulo);
                    $moduloFin = $numModuloActual;
                    
                    $modulosAsociados = [];
                    foreach ($planificacionesMismoBloque as $planItem) {
                        if ($planItem->modulo) {
                            $num = \App\Helpers\ModulosHelper::getNumeroModulo($planItem->id_modulo);
                            $modulosAsociados[$num] = $planItem->modulo;
                        }
                    }
                    
                    while (isset($modulosAsociados[$moduloFin + 1])) {
                        $moduloFin++;
                        $cantidadModulos++;
                    }
                    
                    $moduloFinalObj = $modulosAsociados[$moduloFin] ?? null;
                    if ($moduloFinalObj) {
                        $horaSalidaBloque = $moduloFinalObj->hora_termino;
                    }
                }

                $nuevaReserva->fecha_reserva = $horaActual->format('Y-m-d');
                $nuevaReserva->hora = $horaActualStr;
                $nuevaReserva->tipo_reserva = 'clase';
                $nuevaReserva->estado = 'activa';
                $nuevaReserva->modulo_inicio = $numModuloActual;
                $nuevaReserva->modulo_fin = $moduloFin;
                $nuevaReserva->hora_salida = $horaSalidaBloque;
                $nuevaReserva->modulos = $cantidadModulos;
                $nuevaReserva->observaciones = "REGISTRADA CON LIBERACIÓN: El espacio fue liberado de la reserva anterior " .
                    "por no devolución de llave. Profesor tiene clase programada: {$tieneClaseProgramada->nombre_asignatura}";
                $nuevaReserva->created_at = $horaActual;
                $nuevaReserva->updated_at = $horaActual;
                $nuevaReserva->save();

                // 4️⃣ ACTUALIZAR ESTADO DEL ESPACIO
                $espacio->estado = 'Ocupado';
                $espacio->save();

                // 5️⃣ LIMPIAR REGISTROS DE CLASES NO REALIZADAS (si aplica)
                if (method_exists(\App\Models\ClaseNoRealizada::class, 'limpiarRegistrosIncorrectos')) {
                    \App\Models\ClaseNoRealizada::limpiarRegistrosIncorrectos(
                        $request->espacio_id,
                        $horaActual->format('Y-m-d'),
                        $horaActual->format('H:i:s'),
                        $runNormalizado
                    );
                }

                DB::connection('tenant')->commit();

                \Log::info('✅ USO REGISTRADO CON LIBERACIÓN', [
                    'reserva_nueva' => $nuevaReserva->id_reserva,
                    'reserva_liberada' => $reservaAnteriorFinalizadaId,
                    'profesor' => $runNormalizado,
                    'espacio_id' => $request->espacio_id,
                    'asignatura' => $tieneClaseProgramada->nombre_asignatura,
                    'timestamp' => $horaActual->format('Y-m-d H:i:s')
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Sala liberada y uso registrado correctamente',
                    'data' => [
                        'accion' => 'liberar_y_registrar',
                        'reserva_nueva' => [
                            'id' => $nuevaReserva->id_reserva,
                            'profesor' => $runNormalizado,
                            'espacio' => $espacio->nombre_espacio,
                            'asignatura' => $tieneClaseProgramada->nombre_asignatura,
                            'hora_inicio' => $horaActualStr,
                            'hora_termino' => $tieneClaseProgramada->hora_termino
                        ],
                        'reserva_liberada' => $reservaAnteriorFinalizadaId ? [
                            'id' => $reservaAnteriorFinalizadaId,
                            'profesor' => $profesorAnterior,
                            'motivo' => 'Llave no devuelta'
                        ] : null,
                        'auditoria' => [
                            'timestamp' => $horaActual->format('Y-m-d H:i:s'),
                            'razon' => 'Profesor anterior no devolvió llave'
                        ]
                    ]
                ], 201);

            } catch (\Exception $e) {
                DB::connection('tenant')->rollBack();
                \Log::error('❌ Error al liberar y registrar uso: ' . $e->getMessage());
                throw $e;
            }

        } catch (\Exception $e) {
            \Log::error('Error en liberarYRegistrarUso: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al liberar y registrar el uso del espacio: ' . $e->getMessage()
            ], 500);
        }
    }
}
