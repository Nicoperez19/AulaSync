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
use App\Helpers\SemesterHelper;

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

            // Obtener la hora actual y variantes del día
            $ahora = Carbon::now();
            $diaActual = strtolower($ahora->locale('es')->isoFormat('dddd'));
            $diaLimpio = \App\Helpers\ModulosHelper::normalizarDia($diaActual);
            $diasPosibles = array_unique([$diaActual, $diaLimpio, 'miércoles', 'miercoles', 'sábado', 'sabado']);

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
            $runLimpio = preg_replace('/[^0-9kK]/', '', $run);
            $ahoraMas60 = $ahora->copy()->addMinutes(60)->format('H:i:s');
            $hoyFecha = $ahora->toDateString();
            $periodoActual = \App\Helpers\SemesterHelper::getCurrentPeriod();

            $tieneClaseProgramada = DB::connection('tenant')
                ->table('planificacion_asignaturas as pa')
                ->join('horarios as h', 'pa.id_horario', '=', 'h.id_horario')
                ->join('modulos as m', 'pa.id_modulo', '=', 'm.id_modulo')
                ->where('pa.id_espacio', $espacioId)
                ->where('h.periodo', $periodoActual)
                ->where(function ($q) use ($hoyFecha) {
                    $q->whereNull('pa.fecha_fin_vigencia')
                      ->orWhere('pa.fecha_fin_vigencia', '>=', $hoyFecha);
                })
                ->where(function ($q) use ($run, $runLimpio) {
                    $q->where('h.run_profesor', $run)
                      ->orWhere('h.run_profesor', $runLimpio)
                      ->orWhereRaw("REPLACE(REPLACE(REPLACE(h.run_profesor, '.', ''), '-', ''), ' ', '') = ?", [$runLimpio]);
                })
                ->whereIn('m.dia', $diasPosibles)
                ->where('m.hora_inicio', '<=', $ahoraMas60)
                ->where('m.hora_termino', '>=', $ahora->format('H:i:s'))
                ->exists();

            if (!$tieneClaseProgramada) {
                $tieneClaseProgramada = DB::connection('tenant')
                    ->table('planificaciones_profesores_colaboradores as ppc')
                    ->join('profesores_colaboradores as pc', 'ppc.id_profesor_colaborador', '=', 'pc.id')
                    ->join('modulos as m', 'ppc.id_modulo', '=', 'm.id_modulo')
                    ->where('ppc.id_espacio', $espacioId)
                    ->where(function ($q) use ($hoyFecha) {
                        $q->whereNull('ppc.fecha_fin_vigencia')
                          ->orWhere('ppc.fecha_fin_vigencia', '>=', $hoyFecha);
                    })
                    ->where(function ($q) use ($run, $runLimpio) {
                        $q->where('pc.run_profesor_colaborador', $run)
                          ->orWhere('pc.run_profesor_colaborador', $runLimpio)
                          ->orWhereRaw("REPLACE(REPLACE(REPLACE(pc.run_profesor_colaborador, '.', ''), '-', ''), ' ', '') = ?", [$runLimpio]);
                    })
                    ->where('pc.estado', 'activo')
                    ->where('pc.fecha_inicio', '<=', $hoyFecha)
                    ->where('pc.fecha_termino', '>=', $hoyFecha)
                    ->whereIn('m.dia', $diasPosibles)
                    ->where('m.hora_inicio', '<=', $ahoraMas60)
                    ->where('m.hora_termino', '>=', $ahora->format('H:i:s'))
                    ->exists();
            }

            if (!$tieneClaseProgramada) {
                $tieneClaseProgramada = DB::connection('tenant')
                    ->table('planificacion_asignaturas as pa')
                    ->join('horarios as h', 'pa.id_horario', '=', 'h.id_horario')
                    ->join('modulos as m', 'pa.id_modulo', '=', 'm.id_modulo')
                    ->join('asignaturas as a', 'pa.id_asignatura', '=', 'a.id_asignatura')
                    ->join('profesores_colaboradores as pc', 'a.id_asignatura', '=', 'pc.id_asignatura')
                    ->where('pa.id_espacio', $espacioId)
                    ->where('h.periodo', $periodoActual)
                    ->where(function ($q) use ($hoyFecha) {
                        $q->whereNull('pa.fecha_fin_vigencia')
                          ->orWhere('pa.fecha_fin_vigencia', '>=', $hoyFecha);
                    })
                    ->where(function ($q) use ($run, $runLimpio) {
                        $q->where('pc.run_profesor_colaborador', $run)
                          ->orWhere('pc.run_profesor_colaborador', $runLimpio)
                          ->orWhereRaw("REPLACE(REPLACE(REPLACE(pc.run_profesor_colaborador, '.', ''), '-', ''), ' ', '') = ?", [$runLimpio]);
                    })
                    ->where('pc.estado', 'activo')
                    ->where('pc.fecha_inicio', '<=', $hoyFecha)
                    ->where('pc.fecha_termino', '>=', $hoyFecha)
                    ->whereIn('m.dia', $diasPosibles)
                    ->where('m.hora_inicio', '<=', $ahoraMas60)
                    ->where('m.hora_termino', '>=', $ahora->format('H:i:s'))
                    ->exists();
            }

            if (!$tieneClaseProgramada) {
                $recProg = \App\Models\ClaseNoRealizada::obtenerRecuperacionProgramada(
                    $espacioId,
                    $run,
                    $hoyFecha,
                    $ahora->format('H:i:s'),
                    60
                );
                $tieneClaseProgramada = $recProg !== null;
            }

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


            // Obtener la hora actual y normalizar día (soportando tildes y variantes)
            $horaActual = Carbon::now();
            $diaActual = strtolower($horaActual->locale('es')->isoFormat('dddd'));
            $diaLimpio = \App\Helpers\ModulosHelper::normalizarDia($diaActual);
            $diasPosibles = array_unique([$diaActual, $diaLimpio, 'miércoles', 'miercoles', 'sábado', 'sabado']);
            $horaActualStr = $horaActual->format('H:i:s');
            $runLimpio = preg_replace('/[^0-9kK]/', '', $request->run);

            $espacio = Espacio::findOrFail($request->espacio_id);
            $espaciosEquivalentes = \App\Helpers\EspacioAliasHelper::obtenerEquivalentes($request->espacio_id);

            // 1. Verificar si tiene clase programada (antes del check de ocupado para permitir el cierre forzado)
            // Ventana: permite que el profesor llegue hasta 60 min antes del inicio del módulo
            // y que el módulo aún no haya terminado.
            $horaMaximaInicio = $horaActual->copy()->addMinutes(60)->toTimeString();
            $hoyFecha = $horaActual->toDateString();
            $periodoActual = \App\Helpers\SemesterHelper::getCurrentPeriod();

            $tieneClase = DB::connection('tenant')
                ->table('planificacion_asignaturas as pa')
                ->join('horarios as h', 'pa.id_horario', '=', 'h.id_horario')
                ->join('modulos as m', 'pa.id_modulo', '=', 'm.id_modulo')
                ->join('asignaturas as a', 'pa.id_asignatura', '=', 'a.id_asignatura')
                ->whereIn('pa.id_espacio', $espaciosEquivalentes)
                ->where('h.periodo', $periodoActual)
                ->where(function ($q) use ($hoyFecha) {
                    $q->whereNull('pa.fecha_fin_vigencia')
                      ->orWhere('pa.fecha_fin_vigencia', '>=', $hoyFecha);
                })
                ->where(function ($q) use ($runNormalizado, $request, $runLimpio) {
                    $q->where('h.run_profesor', $runNormalizado)
                      ->orWhere('h.run_profesor', $request->run)
                      ->orWhere('h.run_profesor', $runLimpio)
                      ->orWhereRaw("REPLACE(REPLACE(REPLACE(h.run_profesor, '.', ''), '-', ''), ' ', '') = ?", [$runLimpio]);
                })
                ->whereIn('m.dia', $diasPosibles)
                ->where('m.hora_inicio', '<=', $horaMaximaInicio)
                ->where('m.hora_termino', '>=', $horaActualStr)
                ->orderBy('m.hora_inicio', 'asc')
                ->select('a.id_asignatura', 'a.nombre_asignatura', 'm.id_modulo', 'm.hora_inicio', 'm.hora_termino')
                ->first();

            // 1.b. Si no se encontró en planificación regular, verificar si es Profesor Colaborador en este espacio
            if (!$tieneClase) {
                $tieneClase = DB::connection('tenant')
                    ->table('planificaciones_profesores_colaboradores as ppc')
                    ->join('profesores_colaboradores as pc', 'ppc.id_profesor_colaborador', '=', 'pc.id')
                    ->join('modulos as m', 'ppc.id_modulo', '=', 'm.id_modulo')
                    ->leftJoin('asignaturas as a', 'pc.id_asignatura', '=', 'a.id_asignatura')
                    ->whereIn('ppc.id_espacio', $espaciosEquivalentes)
                    ->where(function ($q) use ($hoyFecha) {
                        $q->whereNull('ppc.fecha_fin_vigencia')
                          ->orWhere('ppc.fecha_fin_vigencia', '>=', $hoyFecha);
                    })
                    ->where(function ($q) use ($runNormalizado, $request, $runLimpio) {
                        $q->where('pc.run_profesor_colaborador', $runNormalizado)
                          ->orWhere('pc.run_profesor_colaborador', $request->run)
                          ->orWhere('pc.run_profesor_colaborador', $runLimpio)
                          ->orWhereRaw("REPLACE(REPLACE(REPLACE(pc.run_profesor_colaborador, '.', ''), '-', ''), ' ', '') = ?", [$runLimpio]);
                    })
                    ->where('pc.estado', 'activo')
                    ->where('pc.fecha_inicio', '<=', $hoyFecha)
                    ->where('pc.fecha_termino', '>=', $hoyFecha)
                    ->whereIn('m.dia', $diasPosibles)
                    ->where('m.hora_inicio', '<=', $horaMaximaInicio)
                    ->where('m.hora_termino', '>=', $horaActualStr)
                    ->orderBy('m.hora_inicio', 'asc')
                    ->select(
                        DB::raw('COALESCE(a.id_asignatura, pc.id_asignatura) as id_asignatura'),
                        DB::raw('COALESCE(a.nombre_asignatura, pc.nombre_asignatura_temporal, "Clase Colaborador") as nombre_asignatura'),
                        'm.id_modulo',
                        'm.hora_inicio',
                        'm.hora_termino'
                    )
                    ->first();
            }

            // 1.c. Si aún no se encuentra, verificar si está registrado como colaborador de la asignatura dictada en este espacio
            if (!$tieneClase) {
                $tieneClase = DB::connection('tenant')
                    ->table('planificacion_asignaturas as pa')
                    ->join('horarios as h', 'pa.id_horario', '=', 'h.id_horario')
                    ->join('modulos as m', 'pa.id_modulo', '=', 'm.id_modulo')
                    ->join('asignaturas as a', 'pa.id_asignatura', '=', 'a.id_asignatura')
                    ->join('profesores_colaboradores as pc', 'a.id_asignatura', '=', 'pc.id_asignatura')
                    ->whereIn('pa.id_espacio', $espaciosEquivalentes)
                    ->where('h.periodo', $periodoActual)
                    ->where(function ($q) use ($hoyFecha) {
                        $q->whereNull('pa.fecha_fin_vigencia')
                          ->orWhere('pa.fecha_fin_vigencia', '>=', $hoyFecha);
                    })
                    ->where(function ($q) use ($runNormalizado, $request, $runLimpio) {
                        $q->where('pc.run_profesor_colaborador', $runNormalizado)
                          ->orWhere('pc.run_profesor_colaborador', $request->run)
                          ->orWhere('pc.run_profesor_colaborador', $runLimpio)
                          ->orWhereRaw("REPLACE(REPLACE(REPLACE(pc.run_profesor_colaborador, '.', ''), '-', ''), ' ', '') = ?", [$runLimpio]);
                    })
                    ->where('pc.estado', 'activo')
                    ->where('pc.fecha_inicio', '<=', $hoyFecha)
                    ->where('pc.fecha_termino', '>=', $hoyFecha)
                    ->whereIn('m.dia', $diasPosibles)
                    ->where('m.hora_inicio', '<=', $horaMaximaInicio)
                    ->where('m.hora_termino', '>=', $horaActualStr)
                    ->orderBy('m.hora_inicio', 'asc')
                    ->select('a.id_asignatura', 'a.nombre_asignatura', 'm.id_modulo', 'm.hora_inicio', 'm.hora_termino')
                    ->first();
            }

            // 1.d. Si aún no se encuentra, verificar si tiene una recuperación de clase reagendada para hoy en este espacio
            $infoRecuperacionApi = null;
            if (!$tieneClase) {
                $infoRecuperacionApi = \App\Models\ClaseNoRealizada::obtenerRecuperacionProgramada(
                    $request->espacio_id,
                    $request->run,
                    $hoyFecha,
                    $horaActualStr,
                    60
                );
                if ($infoRecuperacionApi) {
                    $tieneClase = (object) [
                        'id_asignatura'     => $infoRecuperacionApi['id_asignatura'],
                        'nombre_asignatura' => $infoRecuperacionApi['nombre_asignatura'] ?? 'Recuperación de clase',
                        'id_modulo'         => $infoRecuperacionApi['id_modulo'],
                        'hora_inicio'       => $infoRecuperacionApi['hora_inicio'],
                        'hora_termino'      => $infoRecuperacionApi['hora_termino'],
                        'es_recuperacion'   => true,
                    ];
                }
            }

            $forzado = false;

            // 2. Verificar si el espacio está ocupado
            if ($espacio->estado === 'Ocupado') {

                // --- Caso A: El espacio está ocupado por el mismo docente ---
                $reservaMismoProfesor = Reserva::activasProfesor($runNormalizado, $request->espacio_id)
                    ->orderBy('created_at', 'desc')
                    ->first();

                if ($reservaMismoProfesor) {
                    if ($tieneClase && $reservaMismoProfesor->tipo_reserva === 'espontanea') {
                        // El docente tenía una reserva espontánea y ahora inicia una clase programada.
                        // Finalizamos la espontánea limpiamente antes de crear la nueva.
                        $reservaMismoProfesor->estado      = 'finalizada';
                        $reservaMismoProfesor->hora_salida = $horaActualStr;
                        $reservaMismoProfesor->observaciones = trim(
                            ($reservaMismoProfesor->observaciones ?? '') .
                            "\nFinalizada automáticamente al iniciar clase programada: " . $tieneClase->nombre_asignatura .
                            " (" . $horaActual->format('d/m/Y H:i:s') . ")"
                        );
                        $reservaMismoProfesor->save();

                        \Log::info('Reserva espontánea cerrada al iniciar clase programada del mismo docente', [
                            'reserva_cerrada' => $reservaMismoProfesor->id_reserva,
                            'run_profesor'    => $runNormalizado,
                            'espacio_id'      => $request->espacio_id,
                            'clase'           => $tieneClase->nombre_asignatura,
                        ]);
                        // Continúa para crear la nueva reserva de tipo 'clase'.

                    } elseif ($tieneClase && $reservaMismoProfesor->tipo_reserva === 'clase') {
                        // Ya existe una reserva de clase activa para este docente en este espacio.
                        // No se crea duplicado; se retorna éxito con la información existente.
                        \Log::info('Registro entrada evitado: el docente ya tiene clase activa registrada', [
                            'reserva_existente' => $reservaMismoProfesor->id_reserva,
                            'run_profesor'      => $runNormalizado,
                            'espacio_id'        => $request->espacio_id,
                        ]);
                        return response()->json([
                            'success'        => true,
                            'message'        => 'Ya tiene una clase activa registrada en este espacio.',
                            'espacio_nombre' => $espacio->nombre_espacio,
                            'hora_termino'   => $tieneClase->hora_termino,
                            'asignatura'     => $tieneClase->nombre_asignatura,
                        ]);

                    } else {
                        // El espacio está ocupado por el mismo docente con uso libre y no tiene clase programada.
                        return response()->json([
                            'success' => false,
                            'message' => 'Ya tiene un uso activo registrado en este espacio.'
                        ], 400);
                    }

                } else {
                    // --- Caso B: El espacio está ocupado por otro docente ---
                    if ($tieneClase) {
                        // El docente actual tiene clase programada → liberación forzada del ocupante anterior.
                        $reservaAnterior = Reserva::activasOtrosDocentes($runNormalizado, $request->espacio_id)
                            ->orderBy('created_at', 'desc')
                            ->first();

                        if ($reservaAnterior) {
                            $reservaAnterior->estado      = 'finalizada';
                            $reservaAnterior->hora_salida = $horaActualStr;
                            $mensajeForzado = 'Cierre forzado por Docente ' . $request->run .
                                ' al iniciar clase programada: ' . $tieneClase->nombre_asignatura;
                            $reservaAnterior->observaciones = trim(
                                ($reservaAnterior->observaciones ?? '') . "\n" . $mensajeForzado
                            );
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
            }

            if (!$tieneClase) {
                return response()->json([
                    'success' => false,
                    'message' => 'No tiene una clase programada en este espacio. Las reservas temporales deben ser gestionadas únicamente por una Asistente.'
                ], 403);
            }

            DB::connection('tenant')->beginTransaction();
            try {
                // Calcular módulos consecutivos y hora de salida real del bloque
                $numModuloInicio = null;
                $numModuloFin = null;
                $cantidadModulos = 1;
                $horaSalidaCalculada = $tieneClase->hora_termino ?? null;

                if (!empty($infoRecuperacionApi)) {
                    $numModuloInicio = $infoRecuperacionApi['modulo_inicio'];
                    $numModuloFin = $infoRecuperacionApi['modulo_fin'];
                    $cantidadModulos = $infoRecuperacionApi['cantidad_modulos'];
                    $horaSalidaCalculada = $infoRecuperacionApi['hora_termino'];
                } elseif (!empty($tieneClase->id_modulo)) {
                    $numModuloInicio = \App\Helpers\ModulosHelper::getNumeroModulo($tieneClase->id_modulo);
                    $numModuloFin = $numModuloInicio;

                    $planificacionesMismoBloque = \App\Models\Planificacion_Asignatura::with('modulo')
                        ->whereIn('id_espacio', $espaciosEquivalentes)
                        ->where('id_asignatura', $tieneClase->id_asignatura)
                        ->whereHas('horario', function ($q) use ($periodoActual) {
                            $q->where('periodo', $periodoActual);
                        })
                        ->whereHas('modulo', function ($q) use ($diasPosibles) {
                            $q->whereIn('dia', $diasPosibles);
                        })
                        ->get();

                    $modulosAsociados = [];
                    foreach ($planificacionesMismoBloque as $planItem) {
                        if ($planItem->modulo) {
                            $num = \App\Helpers\ModulosHelper::getNumeroModulo($planItem->id_modulo);
                            $modulosAsociados[$num] = $planItem->modulo;
                        }
                    }

                    while ($numModuloFin !== null && isset($modulosAsociados[$numModuloFin + 1])) {
                        $numModuloFin++;
                        $cantidadModulos++;
                    }

                    if ($numModuloFin !== null && isset($modulosAsociados[$numModuloFin])) {
                        $horaSalidaCalculada = $modulosAsociados[$numModuloFin]->hora_termino;
                    }
                } elseif (isset($moduloNum) && $moduloNum) {
                    $numModuloInicio = $moduloNum;
                    $numModuloFin = $moduloNum;
                }

                // Crear la reserva
                $reserva = new Reserva();
                $reserva->id_reserva = Reserva::generarIdUnico();
                $reserva->run_profesor = $runNormalizado;

                $reserva->id_espacio = $request->espacio_id;
                $reserva->id_asignatura = $tieneClase->id_asignatura ?? null;
                $reserva->fecha_reserva = $horaActual->format('Y-m-d');
                $reserva->hora = $horaActualStr;
                $reserva->tipo_reserva = !empty($infoRecuperacionApi)
                    ? 'recuperacion'
                    : ($tieneClase->nombre_asignatura === 'Uso libre' ? 'espontanea' : 'clase');
                $reserva->estado = 'activa';
                $reserva->modulo_inicio = $numModuloInicio;
                $reserva->modulo_fin = $numModuloFin;
                $reserva->modulos = $cantidadModulos;
                $reserva->hora_salida = $horaSalidaCalculada;
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

                app(\App\Services\ReservaService::class)->enviarCorreoReserva($reserva);

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

            // Buscar la reserva activa del espacio que pertenezca al docente que registra la salida.
            // Se usa el scope activasProfesor() para filtrar por run_profesor o run_solicitante.
            $reserva = Reserva::activasProfesor($runNormalizado, $request->espacio_id)
                ->orderBy('created_at', 'desc')
                ->first();

            // Si no hay reserva activa del propio docente, verificar primero si la reserva fue auto-finalizada
            // previamente (por margen de tiempo o expiración) y el docente está devolviendo la llave tardíamente.
            if (!$reserva) {
                $espaciosEquiv = \App\Helpers\EspacioAliasHelper::obtenerEquivalentes($request->espacio_id);

                $reservaAutoFinalizada = Reserva::whereIn('id_espacio', $espaciosEquiv)
                    ->where('estado', 'finalizada')
                    ->where('fecha_reserva', Carbon::now()->toDateString())
                    ->where(function ($query) use ($runNormalizado) {
                        $query->where('run_profesor', $runNormalizado)
                            ->orWhere('run_solicitante', $runNormalizado)
                            ->orWhereRaw("REPLACE(REPLACE(REPLACE(run_profesor, '.', ''), '-', ''), ' ', '') = ?", [preg_replace('/[^0-9kK]/', '', $runNormalizado)])
                            ->orWhereRaw("REPLACE(REPLACE(REPLACE(run_solicitante, '.', ''), '-', ''), ' ', '') = ?", [preg_replace('/[^0-9kK]/', '', $runNormalizado)]);
                    })
                    ->whereNotNull('observaciones')
                    ->where(function ($q) {
                        $q->where('observaciones', 'LIKE', '%Finalizada automáticamente%')
                          ->orWhere('observaciones', 'LIKE', '%finalizó automáticamente%')
                          ->orWhere('observaciones', 'LIKE', '%Llave no devuelta%')
                          ->orWhere('observaciones', 'LIKE', '%tiempo límite excedido%');
                    })
                    ->orderBy('updated_at', 'desc')
                    ->first();

                if ($reservaAutoFinalizada) {
                    $horaDevolucion = Carbon::now()->format('H:i:s');
                    $observacionActual = $reservaAutoFinalizada->observaciones ?? '';
                    if (!str_contains($observacionActual, 'Devolución de llave registrada') && !str_contains($observacionActual, 'devolvió llave de acceso')) {
                        $nuevaObservacion = "\nDevolución de llave registrada por el docente a las {$horaDevolucion} (devolución tardía tras auto-cierre).";
                        $reservaAutoFinalizada->observaciones = trim($observacionActual . $nuevaObservacion);
                        $reservaAutoFinalizada->save();
                    }

                    // Asegurar que el espacio esté Disponible
                    $espacio = Espacio::find($request->espacio_id);
                    if ($espacio && $espacio->estado !== 'Disponible') {
                        $espacio->estado = 'Disponible';
                        $espacio->save();
                    }

                    try {
                        $tenantId = \App\Models\Tenant::current()?->id ?? 'default';
                        \Illuminate\Support\Facades\Cache::forget("estados_espacios_{$tenantId}");
                        \Illuminate\Support\Facades\Cache::forget('estados_espacios');
                    } catch (\Throwable $e) {}

                    DB::connection('tenant')->commit();

                    return response()->json([
                        'success' => true,
                        'message' => 'Devolución de llave registrada correctamente (devolución tardía tras auto-cierre)',
                        'tardia' => true,
                        'hora_salida' => $horaDevolucion
                    ]);
                }

                $reservaAjena = Reserva::activasOtrosDocentes($runNormalizado, $request->espacio_id)
                    ->first();

                DB::connection('tenant')->rollBack();

                if ($reservaAjena) {
                    \Log::warning('Intento de salida sobre reserva ajena', [
                        'run_solicitante' => $runNormalizado,
                        'run_dueño'       => $reservaAjena->run_profesor ?? $reservaAjena->run_solicitante,
                        'id_reserva'      => $reservaAjena->id_reserva,
                        'id_espacio'      => $request->espacio_id,
                    ]);
                    return response()->json([
                        'success' => false,
                        'message' => 'No tiene una reserva activa en este espacio. El espacio está ocupado por otro usuario.'
                    ], 403);
                }

                return response()->json([
                    'success' => false,
                    'message' => 'No se encontró una reserva activa para este espacio'
                ], 404);
            }

            // Actualizar la reserva activa
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

            try {
                $tenantId = \App\Models\Tenant::current()?->id ?? 'default';
                \Illuminate\Support\Facades\Cache::forget("estados_espacios_{$tenantId}");
                \Illuminate\Support\Facades\Cache::forget('estados_espacios');
            } catch (\Throwable $e) {}

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
        return response()->json([
            'success' => false,
            'message' => 'No se permite tomar reservas temporales desde el sistema de sala de profesores. Por favor, solicite la reserva con una Asistente.'
        ], 403);
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
            $hoyFecha = $horaActual->toDateString();
            $periodoActual = \App\Helpers\SemesterHelper::getCurrentPeriod();

            // 1️⃣ VALIDACIÓN: Verificar que hay clase programada en este espacio/horario del semestre activo
            // Esto asegura que el profesor que llega tiene horario en ese espacio
            $tieneClaseProgramada = DB::connection('tenant')->table('planificacion_asignaturas as pa')
                ->join('horarios as h', 'pa.id_horario', '=', 'h.id_horario')
                ->join('modulos as m', 'pa.id_modulo', '=', 'm.id_modulo')
                ->join('asignaturas as a', 'pa.id_asignatura', '=', 'a.id_asignatura')
                ->where('pa.id_espacio', $request->espacio_id)
                ->where('h.periodo', $periodoActual)
                ->where(function ($q) use ($hoyFecha) {
                    $q->whereNull('pa.fecha_fin_vigencia')
                      ->orWhere('pa.fecha_fin_vigencia', '>=', $hoyFecha);
                })
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

                // 2️⃣ BUSCAR Y FINALIZAR RESERVA ANTERIOR (solo de otro docente)
                // Si el espacio está ocupado por el mismo docente, no se debe liberar — sería un conflicto.
                $reservaMismoProfesor = Reserva::where('id_espacio', $request->espacio_id)
                    ->where('estado', 'activa')
                    ->where('run_profesor', $runNormalizado)
                    ->first();

                if ($reservaMismoProfesor) {
                    DB::connection('tenant')->rollBack();
                    \Log::warning('liberarYRegistrarUso: docente intenta liberar su propia reserva activa', [
                        'run_profesor' => $runNormalizado,
                        'id_reserva'   => $reservaMismoProfesor->id_reserva,
                        'tipo_reserva' => $reservaMismoProfesor->tipo_reserva,
                        'espacio_id'   => $request->espacio_id,
                    ]);
                    return response()->json([
                        'success'    => false,
                        'message'    => 'Usted ya tiene una reserva activa en este espacio. Use la opción de salida para finalizarla primero.',
                        'error_code' => 'own_active_reservation',
                    ], 409);
                }

                $reservaAnterior = Reserva::where('id_espacio', $request->espacio_id)
                    ->where('estado', 'activa')
                    ->where(function ($q) use ($runNormalizado) {
                        $q->where('run_profesor', '!=', $runNormalizado)
                          ->orWhereNull('run_profesor');
                    })
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
                    $espacioLimpioLib = str_replace(['-', ' '], '', $request->espacio_id);
                    $runLimpioLib = preg_replace('/[^0-9kK]/', '', $runNormalizado);
                    $planificacionesMismoBloque = \App\Models\Planificacion_Asignatura::with('modulo')
                        ->where(function ($eq) use ($request, $espacioLimpioLib) {
                            $eq->where('id_espacio', $request->espacio_id)
                               ->orWhereRaw("REPLACE(REPLACE(id_espacio, '-', ''), ' ', '') = ?", [$espacioLimpioLib]);
                        })
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

                app(\App\Services\ReservaService::class)->enviarCorreoReserva($nuevaReserva);

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

    /**
     * ADMIN: Registrar entrada (activar reserva) en nombre de un docente.
     * Solo accesible para usuarios con permiso 'mantenedor de reservas'.
     */
    public function registrarEntradaAdmin(Request $request, $id)
    {
        try {
            $request->validate([
                'hora_inicio_real' => 'nullable|date_format:H:i',
            ]);

            $reserva = Reserva::with(['profesor', 'solicitante', 'espacio'])->findOrFail($id);

            if (!in_array($reserva->estado, ['programada', 'activa'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Solo se puede registrar entrada para reservas programadas o activas.',
                ], 422);
            }

            $admin = auth()->user();
            $horaReal = $request->input('hora_inicio_real')
                ? $request->input('hora_inicio_real') . ':00'
                : Carbon::now()->format('H:i:s');

            $observacionAdmin = "\n[ADMIN] Entrada registrada por {$admin->name} ({$admin->run}) "
                . "el " . Carbon::now()->format('d/m/Y') . " a las " . Carbon::now()->format('H:i:s')
                . ". Hora real de inicio indicada: " . substr($horaReal, 0, 5) . ".";

            $reserva->estado = 'activa';
            $reserva->hora = $horaReal;
            $reserva->observaciones = ($reserva->observaciones ?? '') . $observacionAdmin;
            $reserva->save();

            // Marcar espacio como Ocupado
            if ($reserva->espacio) {
                $reserva->espacio->estado = 'Ocupado';
                $reserva->espacio->save();
            }

            \Log::info('[ADMIN] Entrada registrada', [
                'reserva' => $id,
                'admin' => $admin->run,
                'hora_registrada' => $horaReal,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Entrada registrada correctamente en nombre del docente.',
            ]);

        } catch (\Exception $e) {
            \Log::error('[ADMIN] Error al registrar entrada: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al registrar la entrada: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * ADMIN: Registrar salida (finalizar reserva) en nombre de un docente.
     * Solo accesible para usuarios con permiso 'mantenedor de reservas'.
     */
    public function registrarSalidaAdmin(Request $request, $id)
    {
        try {
            $reserva = Reserva::with(['espacio'])->findOrFail($id);

            if ($reserva->estado !== 'activa') {
                return response()->json([
                    'success' => false,
                    'message' => 'Solo se puede registrar salida para reservas activas.',
                ], 422);
            }

            $admin = auth()->user();
            $horaSalida = Carbon::now()->format('H:i:s');

            $observacionAdmin = "\n[ADMIN] Salida registrada por {$admin->name} ({$admin->run}) "
                . "el " . Carbon::now()->format('d/m/Y') . " a las {$horaSalida}.";

            $reserva->estado = 'finalizada';
            $reserva->hora_salida = $horaSalida;
            $reserva->observaciones = ($reserva->observaciones ?? '') . $observacionAdmin;
            $reserva->save();

            // Liberar espacio
            if ($reserva->espacio) {
                $reserva->espacio->estado = 'Disponible';
                $reserva->espacio->save();
            }

            \Log::info('[ADMIN] Salida registrada', [
                'reserva' => $id,
                'admin' => $admin->run,
                'hora_salida' => $horaSalida,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Salida registrada correctamente en nombre del docente.',
            ]);

        } catch (\Exception $e) {
            \Log::error('[ADMIN] Error al registrar salida: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al registrar la salida: ' . $e->getMessage(),
            ], 500);
        }
    }
}
