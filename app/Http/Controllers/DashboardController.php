<?php

namespace App\Http\Controllers;

use App\Helpers\ModulosHelper;
use App\Helpers\SemesterHelper;
use App\Models\Espacio;
use App\Models\Planificacion_Asignatura;
use App\Models\PlanificacionProfesorColaborador;
use App\Models\Reserva;
use App\Models\ClaseNoRealizada;
use App\Models\DiaFeriado;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Traits\RunNormalizer;

class DashboardController extends Controller
{
    use RunNormalizer;

    public function index(Request $request)
    {
        return view('layouts.dashboard');
    }

    public function horariosActualAjax(Request $request)
    {
        $diaActual = strtolower(now()->locale('es')->isoFormat('dddd'));
        $horaAhora = date('H:i:s');
        $moduloActualNum = null;
        $moduloActualHorario = null;

        $horariosModulos = ModulosHelper::getHorariosModulos();
        $diaNormalizado = ModulosHelper::normalizarDia($diaActual);

        if (isset($horariosModulos[$diaNormalizado])) {
            foreach ($horariosModulos[$diaNormalizado] as $num => $horario) {
                if ($horaAhora >= $horario['inicio'] && $horaAhora < $horario['fin']) {
                    $moduloActualNum = $num;
                    $moduloActualHorario = $horario;
                    break;
                }
            }
        }

        // Determinar el período actual y fecha de hoy
        $periodo = SemesterHelper::getCurrentPeriod();
        $fechaHoy = now()->format('Y-m-d');

        // Obtener los usuarios asignados por espacio para el módulo actual
        $prefijosDias = ['DO', 'LU', 'MA', 'MI', 'JU', 'VI', 'SA'];
        $diasArray = ['domingo', 'lunes', 'martes', 'miércoles', 'jueves', 'viernes', 'sábado'];
        $indexDia = array_search($diaActual, $diasArray);
        $prefijo = $indexDia !== false ? $prefijosDias[$indexDia] : 'LU';
        $idModulo = $prefijo.'.'.$moduloActualNum;

        $asignaciones = Planificacion_Asignatura::with(['espacio.piso', 'asignatura', 'horario.profesor'])
            ->where('id_modulo', $idModulo)
            ->whereHas('horario', function ($q) use ($periodo) {
                $q->where('periodo', $periodo);
            })
            ->get();

        $asignacionesColaboradores = PlanificacionProfesorColaborador::with(['espacio.piso', 'profesorColaborador.profesor', 'profesorColaborador.asignatura'])
            ->where('id_modulo', $idModulo)
            ->whereHas('profesorColaborador', function ($q) use ($fechaHoy) {
                $q->where('estado', 'activo')
                  ->where('fecha_inicio', '<=', $fechaHoy)
                  ->where('fecha_termino', '>=', $fechaHoy);
            })
            ->get();

        // Obtener todas las reservas activas de hoy por espacio para optimizar consultas
        $reservasActivasHoy = Reserva::where('fecha_reserva', $fechaHoy)
            ->where('estado', 'activa')
            ->whereNull('hora_salida')
            ->get()
            ->groupBy('id_espacio');

        $asignacionesMapeadas = collect();

        foreach ($asignaciones as $asig) {
            if (!$asig->espacio) {
                continue;
            }

            $runProfesor = $asig->horario->run_profesor ?? $asig->asignatura->run_profesor ?? null;
            $runProfesorNorm = $runProfesor ? $this->normalizeRun($runProfesor) : null;

            $reservasEspacio = $reservasActivasHoy->get($asig->espacio->id_espacio, collect());
            $profesorPresente = false;
            if ($runProfesorNorm) {
                $profesorPresente = $reservasEspacio->contains(function ($reserva) use ($runProfesorNorm) {
                    $reservaRunNorm = $reserva->run_profesor ? $this->normalizeRun($reserva->run_profesor) : null;
                    return $reservaRunNorm === $runProfesorNorm;
                });
            }

            $asignacionesMapeadas->push((object) [
                'espacio' => $asig->espacio,
                'nombre_asignatura' => $asig->asignatura->nombre_asignatura ?? '-',
                'profesor_name' => $asig->horario->profesor->name ?? $asig->asignatura->profesor->name ?? '-',
                'profesor_email' => $asig->horario->profesor->email ?? $asig->asignatura->profesor->email ?? '-',
                'profesor_presente' => $profesorPresente,
            ]);
        }

        foreach ($asignacionesColaboradores as $asig) {
            if (!$asig->espacio) {
                continue;
            }

            $runProfesor = $asig->profesorColaborador->run_profesor_colaborador ?? null;
            $runProfesorNorm = $runProfesor ? $this->normalizeRun($runProfesor) : null;

            $reservasEspacio = $reservasActivasHoy->get($asig->espacio->id_espacio, collect());
            $profesorPresente = false;
            if ($runProfesorNorm) {
                $profesorPresente = $reservasEspacio->contains(function ($reserva) use ($runProfesorNorm) {
                    $reservaRunNorm = $reserva->run_profesor ? $this->normalizeRun($reserva->run_profesor) : null;
                    return $reservaRunNorm === $runProfesorNorm;
                });
            }

            $asignacionesMapeadas->push((object) [
                'espacio' => $asig->espacio,
                'nombre_asignatura' => $asig->profesorColaborador->nombre_asignatura ?? '-',
                'profesor_name' => $asig->profesorColaborador->profesor->name ?? '-',
                'profesor_email' => $asig->profesorColaborador->profesor->email ?? '-',
                'profesor_presente' => $profesorPresente,
            ]);
        }

        // Ordenar por número de piso (sumando offset de 100 para admitir subterráneos) y luego por código de espacio
        $asignacionesOrdenadas = $asignacionesMapeadas->sortBy(function ($item) {
            $piso = (int) ($item->espacio->piso->numero_piso ?? 99) + 100;
            $nombre = $item->espacio->id_espacio ?? '';

            return sprintf('%03d-%s', $piso, $nombre);
        })->values();

        return view('partials.dashboard.horarios_modulo_actual', [
            'diaActual' => $diaActual,
            'moduloActualNum' => $moduloActualNum,
            'moduloActualHorario' => $moduloActualHorario,
            'asignaciones' => $asignacionesOrdenadas,
        ])->render();
    }

    public function ocupacionDatosAjax(Request $request)
    {
        $tipoFilter = strtolower($request->query('tipo', 'todos'));

        $espaciosQuery = Espacio::query();

        if ($tipoFilter === 'laboratorios') {
            $espaciosQuery->where(function ($q) {
                $q->where('tipo_espacio', 'like', '%laboratorio%')
                  ->orWhere('tipo_espacio', 'like', '%lab%');
            });
        } elseif ($tipoFilter === 'salas_clases' || $tipoFilter === 'aula' || $tipoFilter === 'clases') {
            $espaciosQuery->where(function ($q) {
                $q->where('tipo_espacio', 'like', '%clase%')
                  ->orWhere('tipo_espacio', 'like', '%aula%')
                  ->orWhere('tipo_espacio', 'like', '%taller%');
            });
        } elseif ($tipoFilter === 'salas_estudio' || $tipoFilter === 'estudio') {
            $espaciosQuery->where(function ($q) {
                $q->where('tipo_espacio', 'like', '%estudio%')
                  ->orWhere('tipo_espacio', 'like', '%biblioteca%');
            });
        }

        $espaciosIds = $espaciosQuery->pluck('id_espacio');
        $totalEspacios = $espaciosIds->count();

        // Días y fechas para la semana actual (Lunes a Sábado)
        $diasDisponibles = ['lunes', 'martes', 'miercoles', 'jueves', 'viernes', 'sabado'];
        $lunesDate = now()->startOfWeek();

        $fechasSemana = [
            'lunes' => $lunesDate->format('Y-m-d'),
            'martes' => $lunesDate->copy()->addDay(1)->format('Y-m-d'),
            'miercoles' => $lunesDate->copy()->addDay(2)->format('Y-m-d'),
            'jueves' => $lunesDate->copy()->addDay(3)->format('Y-m-d'),
            'viernes' => $lunesDate->copy()->addDay(4)->format('Y-m-d'),
            'sabado' => $lunesDate->copy()->addDay(5)->format('Y-m-d'),
        ];

        $fechaInicioSemana = $fechasSemana['lunes'];
        $fechaFinSemana = $fechasSemana['sabado'];

        // Pre-cargar planificaciones de clases programadas para el período académico actual
        $periodoActual = SemesterHelper::getCurrentPeriod();
        $planificacionesSemana = Planificacion_Asignatura::whereIn('id_espacio', $espaciosIds)
            ->whereHas('horario', function ($q) use ($periodoActual) {
                $q->where('periodo', $periodoActual);
            })
            ->get(['id_espacio', 'id_modulo'])
            ->groupBy('id_modulo');

        // Mapeo de días a prefijos de módulo
        $prefijosDias = [
            'lunes' => 'LU',
            'martes' => 'MA',
            'miercoles' => 'MI',
            'jueves' => 'JU',
            'viernes' => 'VI',
            'sabado' => 'SA',
        ];

        // Pre-cargar reservas de uso real en la semana (activas/finalizadas con asistentes)
        $reservasSemana = Reserva::whereBetween('fecha_reserva', [$fechaInicioSemana, $fechaFinSemana])
            ->whereIn('id_espacio', $espaciosIds)
            ->whereIn('estado', ['activa', 'finalizada'])
            ->where(function ($q) {
                $q->whereNull('hubo_asistentes')
                  ->orWhere('hubo_asistentes', true);
            })
            ->get()
            ->groupBy(function ($reserva) {
                $fecha = $reserva->fecha_reserva;
                return $fecha instanceof \Carbon\Carbon 
                    ? $fecha->format('Y-m-d') 
                    : \Carbon\Carbon::parse($fecha)->format('Y-m-d');
            });

        // Determinar día y módulo actual en vivo
        $diaActual = strtolower(now()->locale('es')->isoFormat('dddd'));
        $diaHoyNormalizado = ModulosHelper::normalizarDia($diaActual);
        $horaAhora = date('H:i:s');
        $moduloActualNum = null;

        $horariosModulos = ModulosHelper::getHorariosModulos();

        if (isset($horariosModulos[$diaHoyNormalizado])) {
            foreach ($horariosModulos[$diaHoyNormalizado] as $num => $horario) {
                if ($horaAhora >= $horario['inicio'] && $horaAhora < $horario['fin']) {
                    $moduloActualNum = $num;
                    break;
                }
            }
        }

        // Calcular ocupación (Programación de Clases + Reservas Espontáneas / Espacios Totales) por día y módulo
        $ocupacion = [];

        foreach ($diasDisponibles as $dia) {
            $fechaDelDia = $fechasSemana[$dia];
            $ocupacion[$dia] = [];
            $maxModulos = ($dia === 'sabado') ? 6 : 15;
            $prefijo = $prefijosDias[$dia] ?? 'LU';

            $reservasDia = $reservasSemana->get($fechaDelDia, collect());

            for ($moduloNum = 1; $moduloNum <= 15; ++$moduloNum) {
                if ($moduloNum > $maxModulos) {
                    $ocupacion[$dia][$moduloNum] = null; // Módulo no disponible (N/A)
                    continue;
                }

                $idModuloStr = $prefijo . '.' . $moduloNum;
                $espaciosUsados = [];

                // 1. Obtener espacios ocupados según la PROGRAMACIÓN DE CLASES
                if (isset($planificacionesSemana[$idModuloStr])) {
                    $espaciosProgramados = $planificacionesSemana[$idModuloStr]->pluck('id_espacio')->toArray();
                    $espaciosUsados = array_merge($espaciosUsados, $espaciosProgramados);
                }

                // 2. Sumar espacios ocupados por RESERVAS ESPONTÁNEAS/EVENTOS en este día y módulo
                $horarioModulo = ModulosHelper::getHorarioModulo($dia, $moduloNum);
                foreach ($reservasDia as $reserva) {
                    $overlap = false;
                    $modInicio = $reserva->modulo_inicio ? (int) $reserva->modulo_inicio : null;
                    $modFin = $reserva->modulo_fin ? (int) $reserva->modulo_fin : null;

                    if ($modInicio !== null && $modFin !== null) {
                        if ($moduloNum >= $modInicio && $moduloNum <= $modFin) {
                            $overlap = true;
                        }
                    } else {
                        if ($horarioModulo && $reserva->hora) {
                            $resInicio = $reserva->hora;
                            $resFin = $reserva->hora_salida ?: \Carbon\Carbon::parse($reserva->hora)->addHour()->format('H:i:s');
                            $modInicioHorario = $horarioModulo['inicio'];
                            $modFinHorario = $horarioModulo['fin'];

                            $overlapStart = max($resInicio, $modInicioHorario);
                            $overlapEnd = min($resFin, $modFinHorario);
                            if ($overlapStart < $overlapEnd) {
                                $overlap = true;
                            }
                        }
                    }

                    if ($overlap) {
                        $espaciosUsados[] = $reserva->id_espacio;
                    }
                }

                // 3. Para el módulo actual en vivo, incluir espacios con estado 'Ocupado'
                if ($dia === $diaHoyNormalizado && $moduloNum === $moduloActualNum) {
                    $espaciosEstadoOcupado = Espacio::whereIn('id_espacio', $espaciosIds)
                        ->where('estado', 'Ocupado')
                        ->pluck('id_espacio')
                        ->toArray();
                    $espaciosUsados = array_merge($espaciosUsados, $espaciosEstadoOcupado);
                }

                // Calcular total único de espacios ocupados (programación + reservas sin duplicados)
                $totalUsados = count(array_unique($espaciosUsados));

                // Porcentaje de ocupación = (Espacios Usados / Espacios Totales) * 100
                $porcentaje = $totalEspacios > 0 ? round(($totalUsados / $totalEspacios) * 100) : 0;
                $ocupacion[$dia][$moduloNum] = min(100, max(0, $porcentaje));
            }
        }

        return view('partials.dashboard.ocupacion_grid', [
            'ocupacion' => $ocupacion,
            'tipoFilter' => $tipoFilter,
        ])->render();
    }

    /**
     * Endpoint AJAX para el Estado de Clases (Gráfico 2D y Métricas de Asistencia por Fechas)
     * Basado en la planificación académica real (Planificacion_Asignatura) y asistencia verificada.
     */
    public function statusClasesAjax(Request $request)
    {
        $rango = $request->query('rango', 'semana');
        $fechaInicioStr = $request->query('fecha_inicio');
        $fechaFinStr = $request->query('fecha_fin');

        if (!$fechaInicioStr || !$fechaFinStr) {
            if ($rango === 'hoy') {
                $fechaInicio = now()->startOfDay();
                $fechaFin = now()->endOfDay();
            } elseif ($rango === 'mes') {
                $fechaInicio = now()->startOfMonth();
                $fechaFin = now()->endOfMonth();
            } else { // 'semana' por defecto
                $fechaInicio = now()->startOfWeek();
                $fechaFin = now()->endOfWeek();
            }
        } else {
            $fechaInicio = Carbon::parse($fechaInicioStr)->startOfDay();
            $fechaFin = Carbon::parse($fechaFinStr)->endOfDay();
        }

        $fechaInicioYmd = $fechaInicio->format('Y-m-d');
        $fechaFinYmd = $fechaFin->format('Y-m-d');

        $ahora = Carbon::now();
        $diasSemana = ['domingo', 'lunes', 'martes', 'miercoles', 'jueves', 'viernes', 'sabado'];

        // 1. Pre-cargar feriados en el rango
        $feriadosEnRango = DiaFeriado::activos()
            ->enRango($fechaInicioYmd, $fechaFinYmd)
            ->get();
        $fechasFeriado = [];
        foreach ($feriadosEnRango as $feriado) {
            $cursor = Carbon::parse($feriado->fecha_inicio)->startOfDay();
            $fin = Carbon::parse($feriado->fecha_fin)->startOfDay();
            while ($cursor <= $fin) {
                $fechasFeriado[$cursor->format('Y-m-d')] = true;
                $cursor->addDay();
            }
        }

        // 2. Pre-cargar clases no realizadas en mapa por módulo individual
        $clasesNoRealizadas = ClaseNoRealizada::whereBetween('fecha_clase', [$fechaInicioYmd, $fechaFinYmd])->get();
        $clasesNoRealizadasCache = [];
        foreach ($clasesNoRealizadas as $cnr) {
            $fecha = Carbon::parse($cnr->fecha_clase)->format('Y-m-d');
            $modulos = explode(',', $cnr->id_modulo);
            foreach ($modulos as $mod) {
                $modTrim = trim($mod);
                $clasesNoRealizadasCache["{$fecha}_{$cnr->id_espacio}_{$modTrim}"] = $cnr;
                $clasesNoRealizadasCache["{$fecha}_{$cnr->id_asignatura}_{$modTrim}"] = $cnr;
            }
        }

        // 3. Pre-cargar reservas efectivas de clase en el rango
        $reservasCache = Reserva::whereBetween('fecha_reserva', [$fechaInicioYmd, $fechaFinYmd])
            ->whereIn('estado', ['activa', 'finalizada'])
            ->where(function ($q) {
                $q->whereNull('hubo_asistentes')
                  ->orWhere('hubo_asistentes', true);
            })
            ->where(function ($q) {
                $q->whereNotNull('run_profesor')
                  ->orWhereNotNull('run_solicitante');
            })
            ->get()
            ->groupBy(function ($reserva) {
                return Carbon::parse($reserva->fecha_reserva)->format('Y-m-d') . '_' . $reserva->id_espacio;
            });

        // 4. Pre-cargar planificaciones vigentes
        $periodo = SemesterHelper::getCurrentPeriod();
        $planificaciones = Planificacion_Asignatura::with([
                'modulo:id_modulo,dia,hora_inicio,hora_termino',
                'horario:id_horario,run_profesor,periodo',
                'asignatura:id_asignatura,nombre_asignatura,codigo_asignatura,run_profesor'
            ])
            ->whereHas('modulo')
            ->whereHas('horario')
            ->when($periodo, function ($q) use ($periodo) {
                $q->whereHas('horario', function ($hq) use ($periodo) {
                    $hq->where('periodo', $periodo);
                });
            })
            ->get();

        // 5. Generar lista de días válidos (lunes a sábado) dentro del rango
        $fechasAEvaluar = [];
        $currentDate = $fechaInicio->copy();
        while ($currentDate <= $fechaFin) {
            if ($currentDate->dayOfWeek >= 1 && $currentDate->dayOfWeek <= 6) {
                $fechasAEvaluar[] = $currentDate->copy();
            }
            $currentDate->addDay();
        }

        $realizadas = 0;
        $recuperadas = 0;
        $justificadas = 0;
        $noRegistradas = 0;
        $futurasPendientes = 0;

        foreach ($fechasAEvaluar as $fechaObj) {
            $fechaYmd = $fechaObj->format('Y-m-d');
            $diaNombre = $diasSemana[$fechaObj->dayOfWeek];

            // Si es día feriado, no cuenta como clase fallida
            if (isset($fechasFeriado[$fechaYmd])) {
                continue;
            }

            // Filtrar planificaciones del día
            $planificacionesDelDia = $planificaciones->filter(function ($plan) use ($diaNombre) {
                return ModulosHelper::normalizarDia($plan->modulo->dia) === $diaNombre;
            });

            // Agrupar por Espacio y Asignatura (1 clase = 1 bloque docente consecutivo)
            $clasesAgrupadas = $planificacionesDelDia->groupBy(function ($plan) {
                return $plan->id_espacio . '_' . $plan->id_asignatura;
            });

            foreach ($clasesAgrupadas as $grupoKey => $modulosClase) {
                $primerModulo = $modulosClase->sortBy(fn($p) => $p->modulo->hora_inicio)->first();
                $ultimoModulo = $modulosClase->sortBy(fn($p) => $p->modulo->hora_termino)->last();

                if (!$primerModulo || !$primerModulo->modulo || !$ultimoModulo || !$ultimoModulo->modulo) {
                    continue;
                }

                $horaInicioClase = Carbon::parse($primerModulo->modulo->hora_inicio);
                $horaFinClase    = Carbon::parse($ultimoModulo->modulo->hora_termino);
                $fechaHoraFinClase = $fechaObj->copy()->setTimeFromTimeString($horaFinClase->format('H:i:s'));

                // 1. Verificar si esta clase está oficialmente registrada en ClaseNoRealizada
                $registroCNR = null;
                foreach ($modulosClase as $mItem) {
                    $keyEspacio = "{$fechaYmd}_{$mItem->id_espacio}_{$mItem->id_modulo}";
                    $keyAsig    = "{$fechaYmd}_{$mItem->id_asignatura}_{$mItem->id_modulo}";
                    if (isset($clasesNoRealizadasCache[$keyEspacio])) {
                        $registroCNR = $clasesNoRealizadasCache[$keyEspacio];
                        break;
                    }
                    if (isset($clasesNoRealizadasCache[$keyAsig])) {
                        $registroCNR = $clasesNoRealizadasCache[$keyAsig];
                        break;
                    }
                }

                if ($registroCNR) {
                    if ($registroCNR->estado === 'recuperada') {
                        $recuperadas++;
                    } elseif ($registroCNR->estado === 'justificado') {
                        $justificadas++;
                    } else {
                        $noRegistradas++;
                    }
                    continue;
                }

                // 2. Verificar si hubo reserva / escaneo en el espacio para este bloque
                $claveReserva = "{$fechaYmd}_{$primerModulo->id_espacio}";
                $reservaEncontrada = null;

                if (isset($reservasCache[$claveReserva])) {
                    $reservasDelDia = $reservasCache[$claveReserva];

                    // Coincidencia por asignatura
                    foreach ($reservasDelDia as $r) {
                        if ($r->id_asignatura == $primerModulo->id_asignatura) {
                            $reservaEncontrada = $r;
                            break;
                        }
                    }

                    // Coincidencia por horario
                    if (!$reservaEncontrada) {
                        $minutosMargen = ModulosHelper::getMargenIngresoMinutos($primerModulo->id_modulo);
                        $margenInicio = $horaInicioClase->copy()->subMinutes($minutosMargen);

                        foreach ($reservasDelDia as $r) {
                            $horaAcceso = Carbon::parse($r->hora);
                            if ($horaAcceso >= $margenInicio && $horaAcceso <= $horaFinClase) {
                                $reservaEncontrada = $r;
                                break;
                            }
                        }
                    }
                }

                if ($reservaEncontrada) {
                    $realizadas++;
                } else {
                    // Si aún no termina el horario de la clase, queda pendiente
                    if ($fechaHoraFinClase >= $ahora) {
                        $futurasPendientes++;
                    } else {
                        // Concluyó y no tiene inasistencia oficial registrada en Control de Clases
                        $realizadas++;
                    }
                }
            }
        }

        $totalImpartidas = $realizadas + $recuperadas;
        $totalClasesEvaluadas = $totalImpartidas + $noRegistradas + $justificadas;

        $pctImpartidas = $totalClasesEvaluadas > 0 ? round(($totalImpartidas / $totalClasesEvaluadas) * 100, 1) : 0;
        $pctRealizadas = $totalClasesEvaluadas > 0 ? round(($realizadas / $totalClasesEvaluadas) * 100, 1) : 0;
        $pctRecuperadas = $totalClasesEvaluadas > 0 ? round(($recuperadas / $totalClasesEvaluadas) * 100, 1) : 0;
        $pctNoRegistradas = $totalClasesEvaluadas > 0 ? round(($noRegistradas / $totalClasesEvaluadas) * 100, 1) : 0;

        $data = [
            'rango' => $rango,
            'fecha_inicio' => $fechaInicioYmd,
            'fecha_fin' => $fechaFinYmd,
            'total_clases' => $totalClasesEvaluadas,
            'total_impartidas' => $totalImpartidas,
            'realizadas' => $realizadas,
            'recuperadas' => $recuperadas,
            'no_registradas' => $noRegistradas,
            'justificadas' => $justificadas,
            'futuras_pendientes' => $futurasPendientes,
            'pct_impartidas' => $pctImpartidas,
            'pct_realizadas' => $pctRealizadas,
            'pct_recuperadas' => $pctRecuperadas,
            'pct_no_registradas' => $pctNoRegistradas,
        ];

        if ($request->wantsJson() || $request->has('json')) {
            return response()->json($data);
        }

        return view('partials.dashboard.status_clases_chart', $data)->render();
    }
}
