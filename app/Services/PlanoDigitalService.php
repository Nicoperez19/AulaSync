<?php

namespace App\Services;

use Illuminate\Http\Request;
use App\Models\Reserva;
use App\Models\Espacio;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Traits\PlanoDigitalHelperTrait;
use App\Models\Asignatura;
use App\Models\RegistroLimpieza;
use App\Models\Incidencia;
use App\Models\Sancion;
use Illuminate\Support\Facades\Log;

class PlanoDigitalService
{
    use PlanoDigitalHelperTrait;

    public function devolverEspacio(Request $request)
    {
        try {
            // Establecer contexto del tenant desde el request
            $this->establecerContextoTenant();

            $request->validate([
                'id_espacio' => 'required|string',
                'run_usuario' => 'required|string',
                'tipo_desocupacion' => 'sometimes|string|in:normal,forzosa',
                'run_administrador' => 'required_if:tipo_desocupacion,forzosa|string'
            ]);

            $idEspacio = $this->normalizeEspacioId($request->input('id_espacio'));
            $runUsuario = $request->input('run_usuario');
            $tipoDesocupacion = $request->input('tipo_desocupacion', 'normal');
            $runAdministrador = $request->input('run_administrador');

            // Buscar el espacio
            $espacio = Espacio::where('id_espacio', $idEspacio)->first();

            if (!$espacio) {
                return response()->json([
                    'success' => false,
                    'mensaje' => 'Espacio no encontrado'
                ], 404);
            }

            // Verificar si el usuario tiene una reserva activa en este espacio
            $reservaActiva = null;

            // Si el run_usuario indica desocupación forzosa por falta de info (empieza con FORCE_)
            if (strpos($runUsuario, 'FORCE_') === 0) {
                // Buscar cualquier reserva activa en este espacio
                $reservaActiva = Reserva::where('id_espacio', $idEspacio)
                    ->where('estado', 'activa')
                    ->first();


            } else {
                // Búsqueda normal por RUN de usuario
                $reservaActiva = Reserva::where(function ($query) use ($runUsuario) {
                    $query
                        ->where('run_profesor', $runUsuario)
                        ->orWhere('run_solicitante', $runUsuario);
                })
                    ->where('id_espacio', $idEspacio)
                    ->where('estado', 'activa')
                    ->first();
            }

            if (!$reservaActiva) {
                \Log::warning("Intento de devolución sin reserva activa - Usuario: {$runUsuario}, Espacio: {$idEspacio}");

                // Verificar si el espacio ya está disponible (puede que ya se haya devuelto)
                if ($espacio->estado === 'Disponible') {
                    return response()->json([
                        'success' => true,
                        'mensaje' => 'El espacio ya está disponible',
                        'espacio' => [
                            'id' => $espacio->id_espacio,
                            'nombre' => $espacio->nombre_espacio,
                            'estado' => $espacio->estado
                        ]
                    ]);
                }

                return response()->json([
                    'success' => false,
                    'mensaje' => 'No tienes una reserva activa en este espacio'
                ], 400);
            }

            // NUEVA LÓGICA: Verificar si es un profesor devolviendo durante el primer módulo de una clase
            $devolucionPrimerModulo = false;
            $infoClase = null;

            if ($reservaActiva->run_profesor) {
                // Obtener hora actual y día
                $horaActual = now()->format('H:i:s');
                $diaActual = strtolower(now()->locale('es')->isoFormat('dddd'));

                // Buscar planificaciones del profesor en este espacio para hoy
                $planificaciones = Planificacion_Asignatura::with(['asignatura', 'modulo'])
                    ->where('id_espacio', $idEspacio)
                    ->whereHas('asignatura', function ($query) use ($reservaActiva) {
                        $query->where('run_profesor', $reservaActiva->run_profesor);
                    })
                    ->whereHas('modulo', function ($query) use ($diaActual) {
                        $query->where('dia', $diaActual);
                    })
                    ->orderBy('id_modulo')
                    ->get();

                if ($planificaciones->count() > 0) {
                    // Agrupar módulos consecutivos
                    $secuenciasModulos = [];
                    $secuenciaActual = [];

                    foreach ($planificaciones as $planificacion) {
                        if (empty($secuenciaActual)) {
                            $secuenciaActual[] = $planificacion;
                        } else {
                            $ultimoModulo = end($secuenciaActual)->modulo;
                            $moduloActual = $planificacion->modulo;

                            // Verificar si son consecutivos
                            if ($ultimoModulo->hora_termino === $moduloActual->hora_inicio) {
                                $secuenciaActual[] = $planificacion;
                            } else {
                                if (!empty($secuenciaActual)) {
                                    $secuenciasModulos[] = $secuenciaActual;
                                }
                                $secuenciaActual = [$planificacion];
                            }
                        }
                    }

                    if (!empty($secuenciaActual)) {
                        $secuenciasModulos[] = $secuenciaActual;
                    }

                    // Buscar si estamos en el primer módulo de una secuencia de múltiples módulos
                    foreach ($secuenciasModulos as $secuencia) {
                        if (count($secuencia) > 1) {  // Solo si tiene más de 1 módulo
                            $primerModulo = $secuencia[0]->modulo;

                            // Verificar si la hora actual está dentro del primer módulo
                            if ($horaActual >= $primerModulo->hora_inicio && $horaActual <= $primerModulo->hora_termino) {
                                $devolucionPrimerModulo = true;
                                $infoClase = [
                                    'asignatura' => $secuencia[0]->asignatura->nombre_asignatura,
                                    'codigo_asignatura' => $secuencia[0]->asignatura->codigo_asignatura,
                                    'total_modulos' => count($secuencia),
                                    'modulo_actual' => 1,
                                    'hora_inicio' => $primerModulo->hora_inicio,
                                    'hora_termino' => $secuencia[count($secuencia) - 1]->modulo->hora_termino
                                ];
                                break;
                            }
                        }
                    }
                }
            }

            // Actualizar la reserva activa del usuario: establecer hora_salida y cambiar estado a finalizada
            if ($reservaActiva) {
                $reservaActiva->hora_salida = now()->format('H:i:s');
                $reservaActiva->estado = 'finalizada';

                // Si es una desocupación forzosa, agregar información adicional
                if ($tipoDesocupacion === 'forzosa') {
                    $reservaActiva->observaciones = ($reservaActiva->observaciones ?? '')
                        . "; DESOCUPACIÓN FORZOSA por administrador RUN: {$runAdministrador} el " . now()->format('Y-m-d H:i:s');
                }

                $reservaActiva->observaciones = trim(($reservaActiva->observaciones ?? '') . "\nFinalizada por el usuario a las " . now()->format('H:i:s'));
                $reservaActiva->save();

                // Enviar correo de confirmación de devolución
                $this->enviarCorreoDevolucion($reservaActiva);
            }

            // Buscar si hay reservas finalizadas automáticamente que el profesor está devolviendo tarde
            $reservaAutoFinalizada = Reserva::where(function ($query) use ($runUsuario) {
                $query
                    ->where('run_profesor', $runUsuario)
                    ->orWhere('run_solicitante', $runUsuario);
            })
                ->where('id_espacio', $idEspacio)
                ->where('estado', 'finalizada')
                ->where('fecha_reserva', now()->toDateString())
                ->whereNotNull('observaciones')
                ->where('observaciones', 'LIKE', '%finalizó automáticamente por excederse en el tiempo%')
                ->orderBy('updated_at', 'desc')
                ->first();

            if ($reservaAutoFinalizada) {
                // El profesor está devolviendo la llave después de que la reserva fue auto-finalizada
                $observacionActual = $reservaAutoFinalizada->observaciones ?? '';
                $nuevaObservacion = "\nProfesor finalizó la clase más tarde y devolvió llave de acceso a las " . now()->format('H:i:s') . '.';
                $reservaAutoFinalizada->observaciones = $observacionActual . $nuevaObservacion;
                $reservaAutoFinalizada->save();


            }

            // Cambiar el estado del espacio a disponible
            $espacio->estado = 'Disponible';
            $espacio->save();

            // Registrar la devolución en un log
            $mensajeLog = $tipoDesocupacion === 'forzosa'
                ? "Espacio {$idEspacio} FORZOSAMENTE devuelto por administrador {$runAdministrador} - Usuario ocupante: {$runUsuario} - Reserva ID: {$reservaActiva->id_reserva}"
                : "Espacio {$idEspacio} devuelto exitosamente por usuario {$runUsuario} - Reserva ID: {$reservaActiva->id_reserva}";



            $mensajeRespuesta = $tipoDesocupacion === 'forzosa'
                ? 'Espacio desocupado forzosamente por el administrador'
                : 'Espacio devuelto exitosamente';

            $respuesta = [
                'success' => true,
                'mensaje' => $mensajeRespuesta,
                'tipo_desocupacion' => $tipoDesocupacion,
                'espacio' => [
                    'id' => $espacio->id_espacio,
                    'nombre' => $espacio->nombre_espacio,
                    'estado' => $espacio->estado
                ]
            ];

            // Si es devolución en primer módulo, agregar información adicional
            if ($devolucionPrimerModulo) {
                $respuesta['devolucion_primer_modulo'] = true;
                $respuesta['info_clase'] = $infoClase;
                $respuesta['id_reserva'] = $reservaActiva->id_reserva;
            }

            // [OPTIMIZACIÓN] Invalidar caché del plano y del espacio individual
            $this->limpiarCacheEstadosEspacios();
            \Illuminate\Support\Facades\Cache::forget("espacio_info_{$idEspacio}");
            \Illuminate\Support\Facades\Cache::forget("espacio_info_{$idEspacio}_time");

            return response()->json($respuesta);
        } catch (\Exception $e) {
            \Log::error('Error al devolver espacio: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'mensaje' => 'Error al procesar la devolución: ' . $e->getMessage()
            ], 500);
        }
    }

    public function registrarAsistenciaClase(Request $request)
    {
        try {
            // Establecer contexto del tenant desde el request
            $this->establecerContextoTenant();

            $request->validate([
                'id_reserva' => 'required|string',
                'hubo_asistentes' => 'required|boolean'
            ]);

            $idReserva = $request->input('id_reserva');
            $huboAsistentes = $request->input('hubo_asistentes');

            // Buscar la reserva
            $reserva = Reserva::find($idReserva);

            if (!$reserva) {
                return response()->json([
                    'success' => false,
                    'mensaje' => 'Reserva no encontrada'
                ], 404);
            }

            // Agregar observación sobre asistencia
            $mensajeAsistencia = $huboAsistentes
                ? '✓ Clase con asistentes confirmado por profesor'
                : '✗ Clase SIN asistentes - Devolución en primer módulo';

            $observacionActual = $reserva->observaciones ?? '';
            $reserva->observaciones = trim($observacionActual . "\n" . $mensajeAsistencia);

            // Guardar en el campo dedicado
            $reserva->hubo_asistentes = $huboAsistentes;
            $reserva->save();

            // NUEVA LÓGICA: Registrar Clase No Realizada si no hubo asistentes
            if (!$huboAsistentes && $reserva->id_asignatura) {
                try {
                    $periodo = SemesterHelper::getCurrentPeriod();

                    // Buscar el módulo actual basado en la reserva
                    $idModulo = null;
                    if ($reserva->modulo_inicio) {
                        // El formato suele ser "DIA.MODULO", ej: "lunes.1"
                        $dia = strtolower(now()->locale('es')->isoFormat('dddd'));

                        // Normalizar nombres de días si es necesario (Carbon vs BD)
                        $diasMapa = [
                            'monday' => 'lunes',
                            'tuesday' => 'martes',
                            'wednesday' => 'miércoles',
                            'thursday' => 'jueves',
                            'friday' => 'viernes',
                            'saturday' => 'sábado',
                            'sunday' => 'domingo'
                        ];
                        $diaEng = strtolower(now()->englishDayOfWeek);
                        $diaNormalizado = $diasMapa[$diaEng] ?? $dia;
                        $codigoDia = $this->obtenerCodigoDia($diaNormalizado);

                        $idModulo = ($codigoDia ?? $diaNormalizado) . '.' . $reserva->modulo_inicio;
                    } else {
                        // Fallback: buscar por hora actual
                        $horaActual = now()->format('H:i:s');
                        $dia = strtolower(now()->locale('es')->isoFormat('dddd'));
                        $modulo = Modulo::where('dia', $dia)
                            ->where('hora_inicio', '<=', $horaActual)
                            ->where('hora_termino', '>=', $horaActual)
                            ->first();
                        $idModulo = $modulo ? $modulo->id_modulo : null;
                    }

                    if ($idModulo) {
                        ClaseNoRealizada::updateOrCreate(
                            [
                                'id_asignatura' => $reserva->id_asignatura,
                                'id_espacio' => $reserva->id_espacio,
                                'id_modulo' => $idModulo,
                                'fecha_clase' => $reserva->fecha_reserva,
                            ],
                            [
                                'run_profesor' => $reserva->run_profesor,
                                'periodo' => $periodo,
                                'motivo' => 'Confirmado por profesor: Clase sin asistentes',
                                'observaciones' => 'El docente devolvió las llaves en el primer módulo e informó que no hubo asistentes.',
                                'estado' => 'no_realizada',
                                'hora_deteccion' => now(),
                            ]
                        );

                    }
                } catch (\Exception $e) {
                    \Log::error("Error al registrar clase no realizada: " . $e->getMessage());
                }
            }

            return response()->json([
                'success' => true,
                'mensaje' => 'Asistencia registrada correctamente',
                'hubo_asistentes' => $huboAsistentes
            ]);
        } catch (\Exception $e) {
            \Log::error('Error al registrar asistencia de clase: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'mensaje' => 'Error al registrar asistencia: ' . $e->getMessage()
            ], 500);
        }
    }

    public function forzarCierreYTomarEspacio(Request $request)
    {
        return DB::transaction(function () use ($request) {
            try {
                $this->establecerContextoTenant();

                $request->validate([
                    'run_usuario' => 'required|string',
                    'id_espacio' => 'required|string',
                    'id_reserva_anterior' => 'required|string'
                ]);

                $runNuevo = $this->normalizeRun($request->input('run_usuario'));
                $idEspacio = $this->normalizeEspacioId($request->input('id_espacio'));
                $idReservaAnterior = $request->input('id_reserva_anterior');

                // 1. Obtener datos de los involucrados
                $docenteNuevo = Profesor::where('run_profesor', $runNuevo)->first();
                if (!$docenteNuevo) {
                    return response()->json(['success' => false, 'mensaje' => 'Docente nuevo no encontrado (' . $runNuevo . ')'], 404);
                }


                $reservaAnterior = Reserva::with(['profesor', 'solicitante'])->find($idReservaAnterior);
                if (!$reservaAnterior || $reservaAnterior->id_espacio !== $idEspacio) {
                    return response()->json(['success' => false, 'mensaje' => 'Reserva anterior no válida'], 400);
                }

                $nombreAnterior = $reservaAnterior->profesor ? $reservaAnterior->profesor->name : ($reservaAnterior->solicitante ? $reservaAnterior->solicitante->nombre : 'Usuario desconocido');
                $runAnterior = $reservaAnterior->run_profesor ?? $reservaAnterior->run_solicitante;

                // 2. Finalizar reserva anterior
                $reservaAnterior->hora_salida = now()->format('H:i:s');
                $reservaAnterior->estado = 'finalizada';
                $reservaAnterior->observaciones = ($reservaAnterior->observaciones ?? '')
                    . "; CIERRE FORZADO por el docente del siguiente módulo: {$docenteNuevo->name} ({$runNuevo}) el " . now()->format('Y-m-d H:i:s');
                $reservaAnterior->save();

                // 3. Crear nueva reserva para el docente actual
                // Primero verificar si tiene planificación o reserva programada
                $horaActual = now();
                $diaActual = strtolower($horaActual->locale('es')->isoFormat('dddd'));
                $horaActualStr = $horaActual->format('H:i:s');

                $planificacion = Planificacion_Asignatura::where('id_espacio', $idEspacio)
                    ->whereHas('asignatura', function ($q) use ($runNuevo) {
                        $q->where('run_profesor', $runNuevo);
                    })
                    ->whereHas('modulo', function ($q) use ($diaActual, $horaActualStr, $horaActual) {
                        $q
                            ->where('dia', $diaActual)
                            ->where('hora_inicio', '<=', $horaActual->copy()->addMinutes(15)->toTimeString())
                            ->where('hora_termino', '>=', $horaActualStr);
                    })
                    ->first();

                $reservaProgramada = Reserva::where('id_espacio', $idEspacio)
                    ->where('run_profesor', $runNuevo)
                    ->where('estado', 'programada')
                    ->where('fecha_reserva', Carbon::today()->toDateString())
                    ->first();

                if ($reservaProgramada) {
                    // Activar la programada
                    $reservaProgramada->estado = 'activa';
                    $reservaProgramada->hora = $horaActualStr;
                    $reservaProgramada->observaciones = ($reservaProgramada->observaciones ?? '')
                        . "; Sesión iniciada forzosamente; el docente anterior ({$nombreAnterior} - {$runAnterior}) no liberó el espacio";
                    $reservaProgramada->save();
                    $nuevaReservaId = $reservaProgramada->id_reserva;
                } else {
                    // Crear nueva reserva (como registrarAsistenciaProfesor)
                    $nuevaReserva = new Reserva();
                    $nuevaReserva->id_reserva = Reserva::generarIdUnico();
                    $nuevaReserva->run_profesor = $runNuevo;
                    $nuevaReserva->id_espacio = $idEspacio;
                    $nuevaReserva->fecha_reserva = Carbon::today()->toDateString();
                    $nuevaReserva->hora = $horaActualStr;
                    $nuevaReserva->estado = 'activa';
                    $nuevaReserva->tipo_reserva = 'clase';

                    if ($planificacion) {
                        $nuevaReserva->id_asignatura = $planificacion->id_asignatura;
                        // Extraer el número de módulo del id_modulo (ej: "LU.5" -> 5)
                        $numModulo = explode('.', $planificacion->id_modulo)[1] ?? null;
                        $nuevaReserva->modulo_inicio = $numModulo;
                        $nuevaReserva->modulo_fin = $numModulo;

                        // Si el objeto modulo está cargado, usar su hora de término
                        if ($planificacion->modulo) {
                            $nuevaReserva->hora_salida = $planificacion->modulo->hora_termino;
                        } else {
                            // Fallback: intentar cargar el módulo o usar el mapeo estándar
                            $moduloObj = Modulo::find($planificacion->id_modulo);
                            if ($moduloObj) {
                                $nuevaReserva->hora_salida = $moduloObj->hora_termino;
                            } else {
                                // Mapeo estándar de horas de término por módulo
                                $horariosFin = [
                                    1 => '09:00:00',
                                    2 => '10:00:00',
                                    3 => '11:00:00',
                                    4 => '12:00:00',
                                    5 => '13:00:00',
                                    6 => '14:00:00',
                                    7 => '15:00:00',
                                    8 => '16:00:00',
                                    9 => '17:00:00',
                                    10 => '18:00:00',
                                    11 => '19:00:00',
                                    12 => '20:00:00',
                                    13 => '21:00:00',
                                    14 => '22:00:00',
                                    15 => '23:00:00'
                                ];
                                if ($numModulo && isset($horariosFin[(int) $numModulo])) {
                                    $nuevaReserva->hora_salida = $horariosFin[(int) $numModulo];
                                }
                            }
                        }
                    }

                    $nuevaReserva->observaciones = "Sesión iniciada forzosamente; el docente anterior ({$nombreAnterior} - {$runAnterior}) no liberó el espacio";
                    $nuevaReserva->save();
                    $nuevaReservaId = $nuevaReserva->id_reserva;
                }

                // 4. Asegurar que el espacio esté como Ocupado
                $espacio = Espacio::where('id_espacio', $idEspacio)->first();
                if ($espacio) {
                    $espacio->estado = 'Ocupado';
                    $espacio->save();
                }



                return response()->json([
                    'success' => true,
                    'mensaje' => 'Cierre forzado realizado con éxito. Su clase ha sido iniciada.',
                    'id_reserva' => $nuevaReservaId
                ]);
            } catch (\Exception $e) {
                \Log::error('Error en forzarCierreYTomarEspacio: ' . $e->getMessage());
                return response()->json([
                    'success' => false,
                    'mensaje' => 'Error al procesar el cierre forzado: ' . $e->getMessage()
                ], 500);
            }
        });
    }

    public function verificarEstadoEspacioYReserva(Request $request)
    {
        try {
            // Establecer contexto del tenant desde el request
            $this->establecerContextoTenant();

            // Registro de diagnóstico: confirmar que la función fue invocada (opcional)

            $request->validate([
                'run' => 'required|string',
                'id_espacio' => 'required|string'
            ]);

            $runUsuario = $this->normalizeRun($request->input('run'));
            $idEspacio = $this->normalizeEspacioId($request->input('id_espacio'));


            // Verificar que el espacio existe (ignorar scopes globales para evitar problemas de filtrado por sede/tenant)
            $espacio = Espacio::withoutGlobalScopes()->where('id_espacio', $idEspacio)->first();
            if (!$espacio) {
                \Log::warning('Espacio no encontrado en verificarEstadoEspacioYReserva', [
                    'id_espacio' => $idEspacio,
                    'database' => \DB::connection('tenant')->getDatabaseName()
                ]);
                return response()->json([
                    'tipo' => 'error',
                    'mensaje' => 'Espacio no encontrado: ' . $idEspacio
                ], 404);
            }

            // Verificar si el usuario tiene una reserva activa en otro espacio (prioridad alta)
            $reservaExistente = Reserva::where(function ($query) use ($runUsuario) {
                $query->whereRaw("REPLACE(REPLACE(REPLACE(run_profesor, '.', ''), '-', ''), ' ', '') = ?", [$runUsuario])
                      ->orWhereRaw("REPLACE(REPLACE(REPLACE(run_solicitante, '.', ''), '-', ''), ' ', '') = ?", [$runUsuario]);
            })
                ->where('id_espacio', '!=', $idEspacio)
                ->where('estado', 'activa')
                ->whereNull('hora_salida')
                ->first();

            if ($reservaExistente) {
                // El usuario ya tiene una reserva activa en otro espacio
                return response()->json([
                    'tipo' => 'reserva_existente',
                    'mensaje' => 'Ya tienes una reserva activa en otro espacio. Debes finalizarla antes de solicitar una nueva.',
                    'espacio_disponible' => false
                ]);
            }

            // Verificar si el usuario tiene una reserva activa en este espacio
            // Usamos una comparación más robusta para el RUN por si existen puntos o guiones en la BD
            $reservaActiva = Reserva::where(function ($query) use ($runUsuario) {
                $query->whereRaw("REPLACE(REPLACE(REPLACE(run_profesor, '.', ''), '-', ''), ' ', '') = ?", [$runUsuario])
                      ->orWhereRaw("REPLACE(REPLACE(REPLACE(run_solicitante, '.', ''), '-', ''), ' ', '') = ?", [$runUsuario]);
            })
                ->where('id_espacio', $idEspacio)
                ->where('estado', 'activa')
                ->first();

            // Verificar si el espacio está disponible
            $espacioDisponible = $espacio->estado === 'Disponible';

            if ($reservaActiva) {
                // El usuario tiene una reserva activa en este espacio

                return response()->json([
                    'tipo' => 'devolucion',
                    'mensaje' => 'Tienes una reserva activa en este espacio. ¿Deseas devolver las llaves?',
                    'reserva' => [
                        'id_reserva' => $reservaActiva->id_reserva,
                        'hora_inicio' => $reservaActiva->hora,
                        'fecha' => $reservaActiva->fecha_reserva,
                        'espacio' => $espacio->nombre_espacio
                    ],
                    'espacio_disponible' => false
                ]);
            }

            // Verificar si el usuario tiene una reserva PROGRAMADA en este espacio
            $reservaProgramada = Reserva::where(function ($query) use ($runUsuario) {
                $query->whereRaw("REPLACE(REPLACE(REPLACE(run_profesor, '.', ''), '-', ''), ' ', '') = ?", [$runUsuario])
                      ->orWhereRaw("REPLACE(REPLACE(REPLACE(run_solicitante, '.', ''), '-', ''), ' ', '') = ?", [$runUsuario]);
            })
                ->where('id_espacio', $idEspacio)
                ->where('estado', 'programada')
                ->where('fecha_reserva', Carbon::today()->toDateString())
                ->first();

            if ($reservaProgramada) {
                // El usuario tiene una reserva programada para este espacio.
                // Verificar si la hora actual está dentro del rango de módulos de la reserva.
                $ahora = Carbon::now();
                $puedeActivar = false;

                if ($reservaProgramada->modulo_inicio && $reservaProgramada->modulo_fin) {
                    // Verificar con la hora actual vs horarios de los módulos
                    $horaActualStr = $ahora->format('H:i:s');
                    $horarioModulos = $this->obtenerMapaHorariosModulos();

                    $horaInicioModulo = $horarioModulos[$reservaProgramada->modulo_inicio]['inicio'] ?? null;
                    $horaFinModulo = $horarioModulos[$reservaProgramada->modulo_fin]['fin'] ?? null;

                    if ($horaInicioModulo && $horaFinModulo) {
                        // Aplicar margen de 15 minutos para activación anticipada
                        $horaInicioConMargen = Carbon::createFromFormat('H:i:s', $horaInicioModulo)->subMinutes(15)->format('H:i:s');
                        $puedeActivar = ($horaActualStr >= $horaInicioConMargen && $horaActualStr <= $horaFinModulo);
                    }
                }

                if ($puedeActivar) {
                    // Activar la reserva: cambiar de programada → activa
                    $reservaProgramada->estado = 'activa';
                    $reservaProgramada->hora = $ahora->format('H:i:s');
                    $reservaProgramada->save();

                    // Marcar espacio como Ocupado
                    $espacio->estado = 'Ocupado';
                    $espacio->save();



                    return response()->json([
                        'tipo' => 'activacion_reserva',
                        'success' => true,
                        'mensaje' => 'Reserva programada activada exitosamente. El espacio ha sido asignado.',
                        'reserva' => [
                            'id_reserva' => $reservaProgramada->id_reserva,
                            'hora_inicio' => $reservaProgramada->hora,
                            'fecha' => $reservaProgramada->fecha_reserva,
                            'espacio' => $espacio->nombre_espacio,
                            'nombre_actividad' => $reservaProgramada->nombre_actividad,
                        ],
                        'espacio_disponible' => false
                    ]);
                } else {
                    // El usuario tiene reserva programada pero NO estamos en la franja horaria


                    return response()->json([
                        'tipo' => 'reserva_fuera_horario',
                        'success' => false,
                        'mensaje' => 'Tienes una reserva programada para este espacio, pero aún no es el horario correspondiente. La reserva se activará cuando llegue el momento.',
                        'reserva' => [
                            'id_reserva' => $reservaProgramada->id_reserva,
                            'fecha' => $reservaProgramada->fecha_reserva,
                            'modulo_inicio' => $reservaProgramada->modulo_inicio,
                            'modulo_fin' => $reservaProgramada->modulo_fin,
                            'nombre_actividad' => $reservaProgramada->nombre_actividad,
                        ],
                        'espacio_disponible' => false
                    ]);
                }
            }

            if ($espacioDisponible) {
                // Antes de permitir nueva reserva, verificar si hay una reserva programada
                // de OTRO usuario que cubre el módulo actual
                $reservaProgramadaOtro = Reserva::with(['profesor', 'solicitante'])
                    ->where('id_espacio', $idEspacio)
                    ->where('estado', 'programada')
                    ->where('fecha_reserva', Carbon::today()->toDateString())
                    ->where(function ($query) use ($runUsuario) {
                        $query->where(function ($q) use ($runUsuario) {
                            $q->whereNotNull('run_profesor')->whereRaw("REPLACE(REPLACE(REPLACE(run_profesor, '.', ''), '-', ''), ' ', '') != ?", [$runUsuario]);
                        })->orWhere(function ($q) use ($runUsuario) {
                            $q->whereNotNull('run_solicitante')->whereRaw("REPLACE(REPLACE(REPLACE(run_solicitante, '.', ''), '-', ''), ' ', '') != ?", [$runUsuario]);
                        });
                    })
                    ->first();

                if ($reservaProgramadaOtro && $reservaProgramadaOtro->modulo_inicio && $reservaProgramadaOtro->modulo_fin) {
                    $horaActualStr = Carbon::now()->format('H:i:s');
                    $horarioModulos = $this->obtenerMapaHorariosModulos();
                    $horaInicioMod = $horarioModulos[$reservaProgramadaOtro->modulo_inicio]['inicio'] ?? null;
                    $horaFinMod = $horarioModulos[$reservaProgramadaOtro->modulo_fin]['fin'] ?? null;

                    if ($horaInicioMod && $horaFinMod && $horaActualStr >= $horaInicioMod && $horaActualStr <= $horaFinMod) {
                        // Hay una reserva programada de otro usuario que cubre el horario actual
                        $nombreOcupante = $reservaProgramadaOtro->profesor->name
                            ?? $reservaProgramadaOtro->solicitante->nombre
                            ?? 'Otro usuario';

                        return response()->json([
                            'tipo' => 'espacio_ocupado',
                            'mensaje' => "El espacio tiene una reserva programada por {$nombreOcupante} en este horario.",
                            'espacio_disponible' => false,
                            'ocupante' => [
                                'tipo' => $reservaProgramadaOtro->run_profesor ? 'profesor' : 'solicitante',
                                'nombre' => $nombreOcupante,
                                'run' => $reservaProgramadaOtro->run_profesor ?? $reservaProgramadaOtro->run_solicitante,
                                'hora_inicio' => $horarioModulos[$reservaProgramadaOtro->modulo_inicio]['inicio'] ?? '-',
                                'fecha' => $reservaProgramadaOtro->fecha_reserva,
                            ]
                        ]);
                    }
                }

                // [NUEVO] Verificar si hay una clase programada (Planificacion_Asignatura) de otro docente
                // que cubra el horario actual o que inicie en los próximos 15 minutos.
                $horaActual = Carbon::now();
                $diaActual = strtolower($horaActual->locale('es')->isoFormat('dddd'));
                $horaActualStr = $horaActual->format('H:i:s');
                $runUsuarioLimpio = str_replace(['.', '-', ' '], '', $runUsuario);

                $claseProgramadaOtro = Planificacion_Asignatura::with(['asignatura.profesor', 'modulo'])
                    ->where('id_espacio', $idEspacio)
                    ->whereHas('asignatura', function ($query) use ($runUsuarioLimpio) {
                        $query->whereRaw("REPLACE(REPLACE(REPLACE(run_profesor, '.', ''), '-', ''), ' ', '') != ?", [$runUsuarioLimpio]);
                    })
                    ->whereHas('modulo', function ($query) use ($diaActual, $horaActualStr) {
                        $query->where('dia', $diaActual)
                              ->where('hora_inicio', '<=', $horaActualStr)
                              ->where('hora_termino', '>', $horaActualStr);
                    })
                    ->first();

                if (!$claseProgramadaOtro) {
                    // Si no hay clase en curso, buscar si hay una clase que empiece en los próximos 15 minutos
                    $horaLimiteAnticipada = $horaActual->copy()->addMinutes(15)->format('H:i:s');
                    $claseProgramadaOtro = Planificacion_Asignatura::with(['asignatura.profesor', 'modulo'])
                        ->where('id_espacio', $idEspacio)
                        ->whereHas('asignatura', function ($query) use ($runUsuarioLimpio) {
                            $query->whereRaw("REPLACE(REPLACE(REPLACE(run_profesor, '.', ''), '-', ''), ' ', '') != ?", [$runUsuarioLimpio]);
                        })
                        ->whereHas('modulo', function ($query) use ($diaActual, $horaActualStr, $horaLimiteAnticipada) {
                            $query->where('dia', $diaActual)
                                  ->where('hora_inicio', '>', $horaActualStr)
                                  ->where('hora_inicio', '<=', $horaLimiteAnticipada);
                        })
                        ->first();
                }

                if ($claseProgramadaOtro) {
                    $nombreDocente = $claseProgramadaOtro->asignatura->profesor->name ?? 'Docente programado';
                    $nombreAsignatura = $claseProgramadaOtro->asignatura->nombre_asignatura ?? 'Clase regular';
                    $horaInicio = $claseProgramadaOtro->modulo->hora_inicio ?? '-';
                    $horaTermino = $claseProgramadaOtro->modulo->hora_termino ?? '-';

                    return response()->json([
                        'tipo' => 'clase_programada_otro_docente',
                        'mensaje' => "Esta sala tiene una clase programada de {$nombreDocente} ({$nombreAsignatura}) en el bloque {$horaInicio} - {$horaTermino}, pero el docente aún no ha asistido.",
                        'espacio_disponible' => false,
                        'clase' => [
                            'docente' => $nombreDocente,
                            'asignatura' => $nombreAsignatura,
                            'hora_inicio' => $horaInicio,
                            'hora_termino' => $horaTermino
                        ]
                    ]);
                }

                // El espacio está disponible para crear una nueva reserva
                return response()->json([
                    'tipo' => 'nueva_reserva',
                    'mensaje' => 'Espacio disponible para reservar',
                    'espacio_disponible' => true
                ]);
            } else {
                // El espacio está ocupado por otro usuario - buscar información de la reserva activa más reciente
                $reservaOcupante = Reserva::with('asignatura')
                    ->where('id_espacio', $idEspacio)
                    ->where('estado', 'activa')
                    ->orderBy('created_at', 'desc')
                    ->first();

                $mensaje = 'El espacio está ocupado por otro usuario';
                $informacionOcupante = null;

                if ($reservaOcupante) {
                    if ($reservaOcupante->run_profesor) {
                        // Es un profesor
                        $profesor = Profesor::where('run_profesor', $reservaOcupante->run_profesor)->first();
                        if ($profesor) {
                            $mensaje = "El espacio está ocupado por el profesor {$profesor->name}";

                            // Obtener información de la asignatura si existe
                            $asignaturaInfo = 'Sin asignatura';
                            if ($reservaOcupante->id_asignatura) {
                                $asignatura = \App\Models\Asignatura::where('id_asignatura', $reservaOcupante->id_asignatura)->first();
                                if ($asignatura) {
                                    $asignaturaInfo = $asignatura->nombre_asignatura;
                                }
                            }

                            $informacionOcupante = [
                                'tipo' => 'profesor',
                                'nombre' => $profesor->name,
                                'run' => $profesor->run_profesor,
                                'email' => $profesor->email,
                                'tipo_profesor' => $profesor->tipo_profesor,
                                'hora_inicio' => $reservaOcupante->hora,
                                'fecha' => $reservaOcupante->fecha_reserva,
                                'asignatura' => $asignaturaInfo
                            ];
                        }
                    } elseif ($reservaOcupante->run_solicitante) {
                        // Es un solicitante
                        $solicitante = Solicitante::on('tenant')->where('run_solicitante', $reservaOcupante->run_solicitante)->first();
                        if ($solicitante) {
                            $mensaje = "El espacio está ocupado por el solicitante {$solicitante->nombre}";
                            $informacionOcupante = [
                                'tipo' => 'solicitante',
                                'nombre' => $solicitante->nombre,
                                'run' => $solicitante->run_solicitante,
                                'hora_inicio' => $reservaOcupante->hora,
                                'fecha' => $reservaOcupante->fecha_reserva
                            ];
                        }
                    }
                }

                // NUEVA LÓGICA: Verificar si el usuario que escanea tiene una clase programada ahora
                // para permitir forzar el cierre si la sala está ocupada por un tercero (o por él mismo en una sesión antigua).
                $puedeForzarCierre = false;
                $idReservaAnterior = $reservaOcupante ? $reservaOcupante->id_reserva : null;

                $horaActual = Carbon::now();
                $diaActual = strtolower($horaActual->locale('es')->isoFormat('dddd'));
                $horaActualStr = $horaActual->format('H:i:s');

                // 1. Verificamos si la reserva ocupante es "stale" (de días anteriores o ya expiró su tiempo programado)
                $esReservaAntigua = false;
                if ($reservaOcupante) {
                    $fechaReservaStr = $reservaOcupante->fecha_reserva instanceof Carbon 
                        ? $reservaOcupante->fecha_reserva->toDateString() 
                        : (is_string($reservaOcupante->fecha_reserva) ? substr($reservaOcupante->fecha_reserva, 0, 10) : null);
                    
                    if ($fechaReservaStr && $fechaReservaStr < Carbon::today()->toDateString()) {
                        $esReservaAntigua = true;
                    }
                }

                // 2. Buscar si el usuario que escanea tiene planificación en este bloque (margen de 15 min)
                $planificacionScanner = Planificacion_Asignatura::whereRaw("REPLACE(REPLACE(id_espacio, '-', ''), ' ', '') = ?", [str_replace(['-', ' '], '', $idEspacio)])
                    ->whereHas('asignatura', function ($q) use ($runUsuario) {
                        $q->whereRaw("REPLACE(REPLACE(REPLACE(run_profesor, '.', ''), '-', ''), ' ', '') = ?", [$runUsuario]);
                    })
                    ->whereHas('modulo', function ($q) use ($diaActual, $horaActualStr, $horaActual) {
                        $q
                            ->where('dia', $diaActual)
                            ->where('hora_inicio', '<=', $horaActual->copy()->addMinutes(15)->toTimeString())
                            ->where('hora_termino', '>=', $horaActualStr);
                    })
                    ->first();

                if ($planificacionScanner || $esReservaAntigua) {
                    $puedeForzarCierre = true;
                } else {
                    // También verificar reservas programadas del scanner (margen de 15 min)
                    $reservaProgramadaScanner = Reserva::whereRaw("REPLACE(REPLACE(id_espacio, '-', ''), ' ', '') = ?", [str_replace(['-', ' '], '', $idEspacio)])
                        ->where(function ($q) use ($runUsuario) {
                            $q->whereRaw("REPLACE(REPLACE(REPLACE(run_profesor, '.', ''), '-', ''), ' ', '') = ?", [$runUsuario])
                              ->orWhereRaw("REPLACE(REPLACE(REPLACE(run_solicitante, '.', ''), '-', ''), ' ', '') = ?", [$runUsuario]);
                        })
                        ->where('estado', 'programada')
                        ->where('fecha_reserva', Carbon::today()->toDateString())
                        ->first();

                    if ($reservaProgramadaScanner) {
                        // Verificar margen de 15 minutos para la reserva programada
                        $horarioModulos = $this->obtenerMapaHorariosModulos();
                        $horaInicioMod = $horarioModulos[$reservaProgramadaScanner->modulo_inicio]['inicio'] ?? null;
                        
                        if ($horaInicioMod) {
                            $horaInicioConMargen = Carbon::createFromFormat('H:i:s', $horaInicioMod)->subMinutes(15)->format('H:i:s');
                            if ($horaActualStr >= $horaInicioConMargen) {
                                $puedeForzarCierre = true;
                            }
                        }
                    }
                }


                return response()->json([
                    'tipo' => 'espacio_ocupado',
                    'mensaje' => $mensaje,
                    'espacio_disponible' => false,
                    'ocupante' => $informacionOcupante,
                    'puede_forzar_cierre' => $puedeForzarCierre,
                    'id_reserva_anterior' => $idReservaAnterior
                ]);
            }
        } catch (\Exception $e) {
            \Log::error('Error al verificar estado del espacio y reserva: ' . $e->getMessage());
            return response()->json([
                'tipo' => 'error',
                'mensaje' => 'Error al verificar estado del espacio y reserva: ' . $e->getMessage()
            ], 500);
        }
    }

    public function registrarAsistenciaSalaEstudio(Request $request)
    {
        try {
            // Asegurar contexto tenant para consultas/escrituras
            $this->establecerContextoTenant();

            $validated = $request->validate([
                'espacio_id' => 'required|string|exists:tenant.espacios,id_espacio',
                'asistentes' => 'required|array|min:1',
                'asistentes.*.run' => 'required|string',
                'asistentes.*.nombre' => 'required|string'
            ]);

            $espacioId = $validated['espacio_id'];
            $asistentes = collect($validated['asistentes'])
                ->map(function ($asistente) {
                    return [
                        'run' => preg_replace('/[^0-9kK]/', '', (string) $asistente['run']),
                        'nombre' => trim((string) $asistente['nombre']),
                    ];
                })
                ->filter(fn($a) => $a['run'] !== '' && $a['nombre'] !== '')
                ->unique('run')
                ->values();

            if ($asistentes->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Debe registrar al menos un asistente válido'
                ], 422);
            }

            // Verificar que el espacio sea una sala de estudio
            $espacio = Espacio::where('id_espacio', $espacioId)->first();

            if (!$espacio) {
                return response()->json([
                    'success' => false,
                    'message' => 'Espacio no encontrado'
                ], 404);
            }

            if (strtolower($espacio->tipo_espacio) !== 'sala de estudio') {
                return response()->json([
                    'success' => false,
                    'message' => 'El espacio no es una sala de estudio'
                ], 400);
            }

            // Verificar capacidad
            if ($espacio->capacidad_maxima && $asistentes->count() > (int) $espacio->capacidad_maxima) {
                return response()->json([
                    'success' => false,
                    'message' => 'La cantidad de asistentes excede la capacidad máxima de la sala'
                ], 400);
            }

            $ahora = Carbon::now();
            $fechaActual = $ahora->toDateString();
            $horaActual = $ahora->format('H:i:s');

            $reserva = DB::transaction(function () use ($espacio, $espacioId, $asistentes, $fechaActual, $horaActual, $ahora) {
                // Evitar múltiples reservas activas simultáneas en la misma sala de estudio
                $reservaActiva = Reserva::where('id_espacio', $espacioId)
                    ->whereDate('fecha_reserva', $fechaActual)
                    ->where('estado', 'activa')
                    ->lockForUpdate()
                    ->first();

                if ($reservaActiva) {
                    // Si vuelve a escanear el responsable, se interpreta como devolución/cierre de sala
                    $runEscaneados = $asistentes->pluck('run')->values();
                    if ($runEscaneados->contains((string) $reservaActiva->run_solicitante)) {
                        $horaSalida = $ahora->format('H:i:s');

                        $reservaActiva->estado = 'finalizada';
                        $reservaActiva->hora_salida = $horaSalida;
                        $reservaActiva->observaciones = trim(($reservaActiva->observaciones ?? '') . ' | Sala de estudio finalizada por responsable');
                        $reservaActiva->save();

                        // Finalizar asistencias en curso de esa reserva
                        Asistencia::where('id_reserva', $reservaActiva->id_reserva)
                            ->where('estado', Asistencia::ESTADO_PRESENTE)
                            ->update([
                                'estado' => Asistencia::ESTADO_FINALIZADO,
                                'hora_salida' => $horaSalida,
                                'updated_at' => now(),
                            ]);

                        // Liberar espacio
                        $espacio->estado = 'Disponible';
                        if (!is_null($espacio->capacidad_maxima)) {
                            $espacio->puestos_disponibles = (int) $espacio->capacidad_maxima;
                        }
                        $espacio->save();

                        return [
                            'accion' => 'devolucion',
                            'reserva' => $reservaActiva,
                            'run_responsable' => (string) $reservaActiva->run_solicitante,
                        ];
                    }

                    throw new \RuntimeException('Ya existe una reserva activa en esta sala de estudio. Solo el responsable puede finalizarla escaneando nuevamente su carnet.');
                }

                // El primer carnet escaneado es el responsable de la reserva
                $responsable = $asistentes->first();

                $solicitanteResponsable = Solicitante::firstOrCreate(
                    ['run_solicitante' => $responsable['run']],
                    [
                        'nombre' => $responsable['nombre'],
                        'correo' => $responsable['run'] . '@temp.com',
                        'telefono' => '000000000',
                        'tipo_solicitante' => 'estudiante',
                        'activo' => true,
                        'fecha_registro' => now(),
                    ]
                );

                // Crear reserva grupal (formato real de la tabla reservas)
                $reservaNueva = new Reserva();
                $reservaNueva->id_reserva = Reserva::generarIdUnico();
                $reservaNueva->id_espacio = $espacioId;
                $reservaNueva->run_solicitante = $solicitanteResponsable->run_solicitante;
                $reservaNueva->fecha_reserva = $fechaActual;
                $reservaNueva->hora = $horaActual;
                $reservaNueva->hora_salida = $ahora->copy()->addHours(2)->format('H:i:s');
                $reservaNueva->estado = 'activa';
                $reservaNueva->tipo_reserva = 'espontanea';
                $reservaNueva->observaciones = sprintf(
                    'Sala de Estudio | Responsable: %s (%s) | Asistentes: %d',
                    $responsable['nombre'],
                    $responsable['run'],
                    $asistentes->count()
                );
                $reservaNueva->save();

                // Registrar asistencia de todos los escaneados (incluye responsable)
                foreach ($asistentes as $asistente) {
                    // Mantener solicitantes mínimos para trazabilidad de RUN/nombre
                    Solicitante::firstOrCreate(
                        ['run_solicitante' => $asistente['run']],
                        [
                            'nombre' => $asistente['nombre'],
                            'correo' => $asistente['run'] . '@temp.com',
                            'telefono' => '000000000',
                            'tipo_solicitante' => 'estudiante',
                            'activo' => true,
                            'fecha_registro' => now(),
                        ]
                    );

                    Asistencia::create([
                        'id_reserva' => $reservaNueva->id_reserva,
                        'id_espacio' => $espacioId,
                        'rut_asistente' => $asistente['run'],
                        'nombre_asistente' => $asistente['nombre'],
                        'hora_llegada' => $horaActual,
                        'tipo_entrada' => Asistencia::TIPO_ESPONTANEA,
                        'estado' => Asistencia::ESTADO_PRESENTE,
                        'observaciones' => $asistente['run'] === $responsable['run']
                            ? 'Responsable de reserva de sala de estudio'
                            : 'Asistente de sala de estudio',
                    ]);
                }

                // Actualizar estado/cupos del espacio
                $espacio->estado = 'Ocupado';
                if (!is_null($espacio->capacidad_maxima)) {
                    $espacio->puestos_disponibles = max(0, (int) $espacio->capacidad_maxima - $asistentes->count());
                }
                $espacio->save();

                return [
                    'accion' => 'registro',
                    'reserva' => $reservaNueva,
                    'run_responsable' => $responsable['run'] ?? null,
                ];
            });

            $accion = $reserva['accion'] ?? 'registro';
            $reservaResultado = $reserva['reserva'];
            $runResponsable = $reserva['run_responsable'] ?? ($asistentes->first()['run'] ?? null);



            return response()->json([
                'success' => true,
                'accion' => $accion,
                'message' => $accion === 'devolucion'
                    ? 'Sala de estudio devuelta exitosamente por el responsable'
                    : 'Asistencia registrada exitosamente',
                'reserva_id' => $reservaResultado->id_reserva,
                'cantidad_asistentes' => $asistentes->count(),
                'run_responsable' => $runResponsable
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Datos inválidos',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Error al registrar asistencia en sala de estudio: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al registrar asistencia: ' . $e->getMessage()
            ], 500);
        }
    }

    public function crearReserva(Request $request)
    {
        return response()->json([
            'success' => false,
            'mensaje' => 'Este endpoint está en desuso y ha sido unificado en los controladores específicos.'
        ], 410);
    }
}
