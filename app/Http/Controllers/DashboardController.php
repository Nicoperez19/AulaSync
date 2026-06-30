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
        $tipoFilter = $request->query('tipo', 'todos');

        $espaciosQuery = Espacio::query();

        if ($tipoFilter === 'laboratorios') {
            $espaciosQuery->where('tipo_espacio', 'like', '%laboratorio%');
        } elseif ($tipoFilter === 'aula') {
            $espaciosQuery->where(function ($q) {
                $q->where('tipo_espacio', 'like', '%clase%')
                  ->orWhere('tipo_espacio', 'like', '%aula%');
            });
        } elseif ($tipoFilter === 'salas_estudio') {
            $espaciosQuery->where('tipo_espacio', 'like', '%estudio%');
        }

        $espaciosIds = $espaciosQuery->pluck('id_espacio');
        $totalEspacios = $espaciosIds->count();

        // 2. Definir días y sus fechas para la semana actual
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

        $periodo = SemesterHelper::getCurrentPeriod();
        $fechaInicioSemana = $fechasSemana['lunes'];
        $fechaFinSemana = $fechasSemana['sabado'];

        // Pre-cargar planificaciones de la semana (clase regular)
        $planificacionesSemana = Planificacion_Asignatura::whereIn('id_espacio', $espaciosIds)
            ->whereHas('horario', function ($q) use ($periodo) {
                $q->where('periodo', $periodo);
            })
            ->get()
            ->groupBy('id_modulo');

        // Pre-cargar planificaciones de profesores colaboradores activas en la semana
        $colaboradoresSemana = PlanificacionProfesorColaborador::with('profesorColaborador')
            ->whereIn('id_espacio', $espaciosIds)
            ->whereHas('profesorColaborador', function ($q) use ($fechaInicioSemana, $fechaFinSemana) {
                $q->where('estado', 'activo')
                  ->where('fecha_inicio', '<=', $fechaFinSemana)
                  ->where('fecha_termino', '>=', $fechaInicioSemana);
            })
            ->get();

        // Pre-cargar reservas activas/asistidas en la semana (incluyendo espontáneas)
        $reservasSemana = Reserva::whereBetween('fecha_reserva', [$fechaInicioSemana, $fechaFinSemana])
            ->whereIn('id_espacio', $espaciosIds)
            ->where('estado', '!=', 'cancelada')
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

        // 3. Calcular la ocupación por día y módulo
        $ocupacion = [];

        foreach ($diasDisponibles as $dia) {
            $fechaDelDia = $fechasSemana[$dia];
            $ocupacion[$dia] = [];

            $maxModulos = ($dia === 'sabado') ? 5 : 15;

            $prefijoDia = [
                'lunes' => 'LU',
                'martes' => 'MA',
                'miercoles' => 'MI',
                'jueves' => 'JU',
                'viernes' => 'VI',
                'sabado' => 'SA',
            ][$dia];

            for ($moduloNum = 1; $moduloNum <= 15; ++$moduloNum) {
                if ($moduloNum > $maxModulos) {
                    $ocupacion[$dia][$moduloNum] = null; // Módulo no disponible (N/A)
                    continue;
                }

                $idModulo = $prefijoDia . '.' . $moduloNum;

                // 1. Espacios ocupados por planificación regular
                $espaciosPlanificados = $planificacionesSemana->get($idModulo, collect())
                    ->pluck('id_espacio')
                    ->toArray();

                // 2. Espacios ocupados por colaboradores activos en esta fecha
                $espaciosColaboradores = $colaboradoresSemana->filter(function($colab) use ($idModulo, $fechaDelDia) {
                    if (!$colab->profesorColaborador) {
                        return false;
                    }
                    $fInicio = $colab->profesorColaborador->fecha_inicio;
                    $fTermino = $colab->profesorColaborador->fecha_termino;
                    
                    $fInicioStr = $fInicio instanceof \Carbon\Carbon ? $fInicio->format('Y-m-d') : $fInicio;
                    $fTerminoStr = $fTermino instanceof \Carbon\Carbon ? $fTermino->format('Y-m-d') : $fTermino;
                    
                    return $colab->id_modulo === $idModulo &&
                           $fInicioStr <= $fechaDelDia &&
                           $fTerminoStr >= $fechaDelDia;
                })
                ->pluck('id_espacio')
                ->toArray();

                // 3. Espacios ocupados por reservas de este día
                $reservasDia = $reservasSemana->get($fechaDelDia, collect());
                $espaciosReservas = [];

                foreach ($reservasDia as $reserva) {
                    $overlap = false;

                    if ($reserva->modulo_inicio && $reserva->modulo_fin) {
                        if ($moduloNum >= $reserva->modulo_inicio && $moduloNum <= $reserva->modulo_fin) {
                            $overlap = true;
                        }
                    } else {
                        // Reserva directa o espontánea - verificar traslape de horario
                        $horarioModulo = ModulosHelper::getHorarioModulo($dia, $moduloNum);
                        if ($horarioModulo && $reserva->hora) {
                            $resInicio = $reserva->hora;
                            // Si la reserva está activa (hora_salida es null), estimar el término en 1 hora (límite por defecto)
                            $resFin = $reserva->hora_salida ?: \Carbon\Carbon::parse($reserva->hora)->addHour()->format('H:i:s');

                            $modInicio = $horarioModulo['inicio'];
                            $modFin = $horarioModulo['fin'];

                            // Verificar si los rangos se traslapan
                            $overlapStart = max($resInicio, $modInicio);
                            $overlapEnd = min($resFin, $modFin);

                            if ($overlapStart < $overlapEnd) {
                                $overlap = true;
                            }
                        }
                    }

                    if ($overlap) {
                        $espaciosReservas[] = $reserva->id_espacio;
                    }
                }

                // Combinar todos los espacios ocupados de forma única
                $todosOcupados = array_unique(array_merge($espaciosPlanificados, $espaciosColaboradores, $espaciosReservas));
                $totalOcupados = count($todosOcupados);

                // Calcular el porcentaje de ocupación
                $porcentaje = $totalEspacios > 0 ? round(($totalOcupados / $totalEspacios) * 100) : 0;

                $ocupacion[$dia][$moduloNum] = $porcentaje;
            }
        }

        return view('partials.dashboard.ocupacion_grid', [
            'ocupacion' => $ocupacion,
            'tipoFilter' => $tipoFilter,
        ])->render();
    }
}
