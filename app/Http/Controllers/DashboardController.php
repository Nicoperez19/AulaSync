<?php

namespace App\Http\Controllers;

use App\Helpers\ModulosHelper;
use App\Helpers\SemesterHelper;
use App\Models\Espacio;
use App\Models\Planificacion_Asignatura;
use App\Models\PlanificacionProfesorColaborador;
use App\Models\Reserva;
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

        // Calcular ocupación real (Espacios Usados / Espacios Totales) por día y módulo
        $ocupacion = [];

        foreach ($diasDisponibles as $dia) {
            $fechaDelDia = $fechasSemana[$dia];
            $ocupacion[$dia] = [];
            $maxModulos = ($dia === 'sabado') ? 6 : 15;

            $reservasDia = $reservasSemana->get($fechaDelDia, collect());

            for ($moduloNum = 1; $moduloNum <= 15; ++$moduloNum) {
                if ($moduloNum > $maxModulos) {
                    $ocupacion[$dia][$moduloNum] = null; // Módulo no disponible (N/A)
                    continue;
                }

                $horarioModulo = ModulosHelper::getHorarioModulo($dia, $moduloNum);
                $espaciosUsados = [];

                // Revisar reservas en este día que coincidan con el módulo
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

                // Para el módulo actual en vivo, incluir también los espacios marcados como 'Ocupado'
                if ($dia === $diaHoyNormalizado && $moduloNum === $moduloActualNum) {
                    $espaciosEstadoOcupado = Espacio::whereIn('id_espacio', $espaciosIds)
                        ->where('estado', 'Ocupado')
                        ->pluck('id_espacio')
                        ->toArray();
                    $espaciosUsados = array_merge($espaciosUsados, $espaciosEstadoOcupado);
                }

                $totalUsados = count(array_unique($espaciosUsados));

                // Porcentaje de uso real = (Espacios Usados / Espacios Totales) * 100
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
     */
    public function statusClasesAjax(Request $request)
    {
        $rango = $request->query('rango', 'semana');
        $fechaInicio = $request->query('fecha_inicio');
        $fechaFin = $request->query('fecha_fin');

        if (!$fechaInicio || !$fechaFin) {
            if ($rango === 'hoy') {
                $fechaInicio = now()->format('Y-m-d');
                $fechaFin = now()->format('Y-m-d');
            } elseif ($rango === 'mes') {
                $fechaInicio = now()->startOfMonth()->format('Y-m-d');
                $fechaFin = now()->endOfMonth()->format('Y-m-d');
            } else { // 'semana' por defecto
                $fechaInicio = now()->startOfWeek()->format('Y-m-d');
                $fechaFin = now()->endOfWeek()->format('Y-m-d');
            }
        }

        // 1. Clases Realizadas (Reservas activas/finalizadas normales)
        $realizadas = Reserva::whereBetween('fecha_reserva', [$fechaInicio, $fechaFin])
            ->whereIn('estado', ['activa', 'finalizada'])
            ->where(function ($q) {
                $q->whereNull('hubo_asistentes')
                  ->orWhere('hubo_asistentes', true);
            })
            ->where('tipo_reserva', 'not like', '%recupera%')
            ->count();

        // 2. Clases Recuperadas (Reservas tipo recuperación o clases no realizadas recuperadas)
        $recuperadasReservas = Reserva::whereBetween('fecha_reserva', [$fechaInicio, $fechaFin])
            ->whereIn('estado', ['activa', 'finalizada'])
            ->where('tipo_reserva', 'like', '%recupera%')
            ->count();

        $recuperadasRegistros = \App\Models\ClaseNoRealizada::whereBetween('fecha_clase', [$fechaInicio, $fechaFin])
            ->where('estado', 'recuperada')
            ->count();

        $recuperadas = $recuperadasReservas + $recuperadasRegistros;

        // 3. Clases No Registradas / No Realizadas
        $noRealizadasClases = \App\Models\ClaseNoRealizada::whereBetween('fecha_clase', [$fechaInicio, $fechaFin])
            ->whereIn('estado', ['no_realizada', 'justificado'])
            ->count();

        $reservasSinAsistencia = Reserva::whereBetween('fecha_reserva', [$fechaInicio, $fechaFin])
            ->where('hubo_asistentes', false)
            ->count();

        $noRegistradas = max($noRealizadasClases, $reservasSinAsistencia);

        $totalImpartidas = $realizadas + $recuperadas;
        $totalClases = $totalImpartidas + $noRegistradas;

        $pctImpartidas = $totalClases > 0 ? round(($totalImpartidas / $totalClases) * 100, 1) : 0;
        $pctRealizadas = $totalClases > 0 ? round(($realizadas / $totalClases) * 100, 1) : 0;
        $pctRecuperadas = $totalClases > 0 ? round(($recuperadas / $totalClases) * 100, 1) : 0;
        $pctNoRegistradas = $totalClases > 0 ? round(($noRegistradas / $totalClases) * 100, 1) : 0;

        $data = [
            'rango' => $rango,
            'fecha_inicio' => $fechaInicio,
            'fecha_fin' => $fechaFin,
            'total_clases' => $totalClases,
            'total_impartidas' => $totalImpartidas,
            'realizadas' => $realizadas,
            'recuperadas' => $recuperadas,
            'no_registradas' => $noRegistradas,
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
