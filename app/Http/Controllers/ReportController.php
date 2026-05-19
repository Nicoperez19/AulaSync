<?php
namespace App\Http\Controllers;

use App\Exports\AccesosExport;
use App\Helpers\SemesterHelper;
use App\Models\AreaAcademica;
use App\Models\Asignatura;
use App\Models\Espacio;
use App\Models\Piso;
use App\Models\Planificacion_Asignatura;
use App\Models\Reserva;
use App\Services\OccupancyService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Maatwebsite\Excel\Facades\Excel;

class ReportController extends Controller
{
    use \App\Traits\ReportCalculationsTrait;

    protected $occupancyService;

    public function __construct(OccupancyService $occupancyService)
    {
        $this->occupancyService = $occupancyService;
    }

    public function tipoEspacio(Request $request)
    {
        $mes = now()->month;
        $anio = now()->year;

        // KPIs
        $total_tipos = Espacio::distinct('tipo_espacio')->count('tipo_espacio');
        $total_espacios = Espacio::count();
        $espacios_ocupados = Espacio::where('estado', 'Ocupado')->count();

        $total_reservas = Reserva::whereMonth('fecha_reserva', $mes)
            ->whereYear('fecha_reserva', $anio)
            ->count();

        $dias_laborales = collect(range(1, now()->daysInMonth))
            ->map(function ($day) use ($anio, $mes) {
                return Carbon::create($anio, $mes, $day);
            })
            ->filter(function ($date) {
                return $date->isWeekday();
            })
            ->count();

        $modulos_posibles = $total_espacios * $dias_laborales * 15;  // 15 módulos por día
        $modulos_reservados = Reserva::whereMonth('fecha_reserva', $mes)
            ->whereYear('fecha_reserva', $anio)
            ->count();

        // Calcular horas desde planificaciones
        $inicioMes = Carbon::create($anio, $mes, 1)->startOfDay();
        $finMes = Carbon::create($anio, $mes, 1)->endOfMonth()->endOfDay();
        $periodo = SemesterHelper::getCurrentPeriod();

        $horas_planificaciones = 0;
        $planificaciones = Planificacion_Asignatura::with(['modulo'])
            ->whereHas('horario', function ($q) use ($periodo) {
                $q->where('periodo', $periodo);
            })
            ->get();

        // Calcular horas de planificaciones para el mes
        for ($fecha = $inicioMes->copy(); $fecha->lte($finMes); $fecha->addDay()) {
            if (!$fecha->isWeekday() && !$fecha->isSaturday())
                continue;

            $diaSemana = strtolower($fecha->locale('es')->isoFormat('dddd'));
            $planificacionesDia = $planificaciones->filter(function ($plan) use ($diaSemana) {
                return $plan->modulo && strtolower($plan->modulo->dia) === $diaSemana;
            });

            foreach ($planificacionesDia as $plan) {
                if ($plan->modulo && $plan->modulo->hora_inicio && $plan->modulo->hora_termino) {
                    $inicio = Carbon::parse($plan->modulo->hora_inicio);
                    $fin = Carbon::parse($plan->modulo->hora_termino);
                    $horas_planificaciones += $inicio->diffInHours($fin, true);
                }
            }
        }

        // Calcular horas reales desde reservas espontáneas
        $reservas_mes = Reserva::whereMonth('fecha_reserva', $mes)
            ->whereYear('fecha_reserva', $anio)
            ->get();

        $horas_reservas = $reservas_mes->sum(function ($reserva) {
            if ($reserva->hora && $reserva->hora_salida) {
                $inicio = Carbon::parse($reserva->hora);
                $fin = Carbon::parse($reserva->hora_salida);
                return $inicio->diffInHours($fin, true);  // true para incluir decimales
            }
            return 0.83;  // Si no hay hora de salida, asumir 1 módulo de 50 minutos
        });

        // Total de horas utilizadas
        $horas_utilizadas = $horas_planificaciones + $horas_reservas;

        // Calcular horas totales disponibles considerando sábados (5 horas) vs días normales (15 horas)
        $horas_totales_disponibles = 0;
        for ($fecha = $inicioMes->copy(); $fecha->lte($finMes); $fecha->addDay()) {
            if ($fecha->isWeekday() || $fecha->isSaturday()) {
                $horas_totales_disponibles += $total_espacios * $this->occupancyService->horasPorTurno(null, $fecha);
            }
        }
        $promedio_utilizacion = $horas_totales_disponibles > 0
            ? round(($horas_utilizadas / $horas_totales_disponibles) * 100)
            : 0;

        $tipos = Espacio::distinct()->pluck('tipo_espacio');
        $resumen = [];
        $labels_grafico = [];
        $data_grafico = [];
        $data_reservas_grafico = [];

        foreach ($tipos as $tipo) {
            $espacios = Espacio::where('tipo_espacio', $tipo)->pluck('id_espacio');
            $total_espacios_tipo = $espacios->count();

            // 1. Calcular horas desde PLANIFICACIONES para este tipo
            $horas_plan_tipo = 0;
            $planificaciones_tipo = $planificaciones->filter(function ($plan) use ($tipo) {
                return $plan->espacio && $plan->espacio->tipo_espacio === $tipo;
            });

            for ($fecha = $inicioMes->copy(); $fecha->lte($finMes); $fecha->addDay()) {
                if (!$fecha->isWeekday() && !$fecha->isSaturday())
                    continue;

                $diaSemana = strtolower($fecha->locale('es')->isoFormat('dddd'));
                $planificacionesDia = $planificaciones_tipo->filter(function ($plan) use ($diaSemana) {
                    return $plan->modulo && strtolower($plan->modulo->dia) === $diaSemana;
                });

                foreach ($planificacionesDia as $plan) {
                    if ($plan->modulo && $plan->modulo->hora_inicio && $plan->modulo->hora_termino) {
                        $inicio = Carbon::parse($plan->modulo->hora_inicio);
                        $fin = Carbon::parse($plan->modulo->hora_termino);
                        $horas_plan_tipo += $inicio->diffInHours($fin, true);
                    }
                }
            }

            // 2. Calcular horas desde RESERVAS espontáneas para este tipo
            $reservas_tipo = Reserva::whereIn('id_espacio', $espacios)
                ->whereMonth('fecha_reserva', $mes)
                ->whereYear('fecha_reserva', $anio)
                ->get();
            $total_reservas_tipo = $reservas_tipo->count();

            $horas_reservas_tipo = $reservas_tipo->sum(function ($r) {
                if ($r->hora) {
                    if ($r->hora_salida) {
                        return Carbon::parse($r->hora)->diffInHours(Carbon::parse($r->hora_salida), true);
                    } else {
                        // Si no hay hora de salida, calcular desde la hora actual
                        $horaInicio = Carbon::parse($r->hora);
                        $horaActual = Carbon::now();
                        return $horaInicio->diffInHours($horaActual, true);
                    }
                }
                return 0;  // 0 min default si no hay hora inicio (originalmente era 0.83 equivalente a 50 min)
            });

            // Total de horas utilizadas = planificaciones + reservas
            $horas_utilizadas = $horas_plan_tipo + $horas_reservas_tipo;

            // Calcular horas disponibles considerando sábados (5 horas) vs días normales (15 horas)
            $horas_disponibles_tipo = 0;
            for ($fecha = $inicioMes->copy(); $fecha->lte($finMes); $fecha->addDay()) {
                if ($fecha->isWeekday() || $fecha->isSaturday()) {
                    $horas_disponibles_tipo += $total_espacios_tipo * $this->occupancyService->horasPorTurno(null, $fecha);
                }
            }

            // Calcular porcentaje real basado en horas utilizadas vs disponibles
            $promedio = $horas_disponibles_tipo > 0
                ? round(($horas_utilizadas / $horas_disponibles_tipo) * 100)
                : 0;

            $estado = $promedio >= 80 ? 'Óptimo' : ($promedio >= 40 ? 'Medio uso' : 'Bajo uso');
            $resumen[] = [
                'nombre' => $tipo,
                'total_espacios' => $total_espacios_tipo,
                'total_reservas' => $total_reservas_tipo,
                'horas_utilizadas' => round($horas_utilizadas),
                'promedio' => $promedio,
                'estado' => $estado,
            ];
            $labels_grafico[] = $tipo;
            $data_grafico[] = $promedio;
            $data_reservas_grafico[] = $total_reservas_tipo;
        }

        // CALCULAR ESTADÍSTICAS POR TURNO (DIURNO Y VESPERTINO)
        $estadisticasTurnos = [];
        foreach ($tipos as $tipo) {
            $espacios = Espacio::where('tipo_espacio', $tipo)->pluck('id_espacio');
            $total_espacios_tipo = $espacios->count();

            // Para cada turno (diurno y vespertino)
            foreach (['diurno', 'vespertino'] as $turno) {
                // Calcular horas disponibles por turno considerando sábados
                $horas_disponibles_turno = 0;
                for ($fecha = $inicioMes->copy(); $fecha->lte($finMes); $fecha->addDay()) {
                    if ($fecha->isWeekday() || $fecha->isSaturday()) {
                        $horas_disponibles_turno += $total_espacios_tipo * $this->occupancyService->horasPorTurno($turno, $fecha);
                    }
                }

                // Calcular horas desde planificaciones para este turno
                $horas_plan_turno = 0;
                $planificaciones_tipo = $planificaciones->filter(function ($plan) use ($tipo) {
                    return $plan->espacio && $plan->espacio->tipo_espacio === $tipo;
                });

                for ($fecha = $inicioMes->copy(); $fecha->lte($finMes); $fecha->addDay()) {
                    if (!$fecha->isWeekday() && !$fecha->isSaturday())
                        continue;

                    $diaSemana = strtolower($fecha->locale('es')->isoFormat('dddd'));
                    $planificacionesDia = $planificaciones_tipo->filter(function ($plan) use ($diaSemana) {
                        return $plan->modulo && strtolower($plan->modulo->dia) === $diaSemana;
                    });

                    foreach ($planificacionesDia as $plan) {
                        if ($plan->modulo && $plan->modulo->hora_inicio && $plan->modulo->hora_termino) {
                            if ($this->occupancyService->esTurno($plan->modulo->hora_inicio, $turno)) {
                                $inicio = Carbon::parse($plan->modulo->hora_inicio);
                                $fin = Carbon::parse($plan->modulo->hora_termino);
                                $horas_plan_turno += $inicio->diffInHours($fin, true);
                            }
                        }
                    }
                }

                // Calcular horas desde reservas para este turno
                $reservas_tipo_turno = Reserva::whereIn('id_espacio', $espacios)
                    ->whereMonth('fecha_reserva', $mes)
                    ->whereYear('fecha_reserva', $anio)
                    ->get()
                    ->filter(function ($r) use ($turno) {
                        return $r->hora && $this->occupancyService->esTurno($r->hora, $turno);
                    });

                $horas_reservas_turno = $reservas_tipo_turno->sum(function ($r) {
                    if ($r->hora) {
                        if ($r->hora_salida) {
                            return Carbon::parse($r->hora)->diffInHours(Carbon::parse($r->hora_salida), true);
                        } else {
                            // Si no hay hora de salida, calcular desde la hora actual
                            $horaInicio = Carbon::parse($r->hora);
                            $horaActual = Carbon::now();
                            return $horaInicio->diffInHours($horaActual, true);
                        }
                    }
                    return 0;
                });

                // $horas_plan_turno contiene solo las horas de planificaciones para este turno antiguamente era $horas_reservas_turno + $horas_plan_turno
                $horas_utilizadas_turno = $horas_reservas_turno;
                $promedio_turno = $horas_disponibles_turno > 0
                    ? round(($horas_utilizadas_turno / $horas_disponibles_turno) * 100)
                    : 0;

                $estadisticasTurnos[$tipo][$turno] = [
                    'horas_utilizadas' => round($horas_utilizadas_turno),
                    'promedio' => $promedio_turno,
                    'total_reservas' => $reservas_tipo_turno->count()
                ];
            }
        }

        $diasDisponibles = ['lunes', 'martes', 'miercoles', 'jueves', 'viernes', 'sabado'];
        $tiposEspacioDisponibles = $tipos;
        $diaActual = strtolower(now()->locale('es')->isoFormat('dddd'));
        if (!in_array($diaActual, $diasDisponibles))
            $diaActual = 'lunes';

        $ocupacionHorarios = [];
        foreach ($tiposEspacioDisponibles as $tipo) {
            foreach ($diasDisponibles as $dia) {
                for ($moduloNum = 1; $moduloNum <= 15; $moduloNum++) {
                    $totalEspacios = Espacio::where('tipo_espacio', $tipo)->count();
                    if ($totalEspacios === 0) {
                        $ocupacionHorarios[$tipo][$dia][$moduloNum] = 0;
                        continue;
                    }
                    $ocupados = Planificacion_Asignatura::where('id_modulo', $dia . '.' . $moduloNum)
                        ->whereHas('espacio', function ($q) use ($tipo) {
                            $q->where('tipo_espacio', $tipo);
                        })
                        ->count();
                    $ocupacionHorarios[$tipo][$dia][$moduloNum] = round(($ocupados / $totalEspacios) * 100);
                }
            }
        }

        return view('reportes.tipo-espacio', compact(
            'total_tipos',
            'total_espacios',
            'espacios_ocupados',
            'total_reservas',
            'promedio_utilizacion',
            'resumen',
            'labels_grafico',
            'data_grafico',
            'data_reservas_grafico',
            'diasDisponibles',
            'tiposEspacioDisponibles',
            'diaActual',
            'ocupacionHorarios',
            'estadisticasTurnos'
        ));
    }

    public function espacios(Request $request)
    {
        $mes = now()->month;
        $anio = now()->year;

        // Obtener filtros de la request
        $tipoEspacioFiltro = $request->get('tipo_espacio', '');
        $pisoFiltro = $request->get('piso', '');
        $estadoFiltro = $request->get('estado', '');
        $busqueda = $request->get('busqueda', '');

        // Query base optimizada con eager loading
        $espaciosQuery = Espacio::with(['piso.facultad', 'reservas' => function ($query) use ($mes, $anio) {
            $query
                ->whereMonth('fecha_reserva', $mes)
                ->whereYear('fecha_reserva', $anio);
        }]);

        // Aplicar filtros solo si están presentes
        if (!empty($tipoEspacioFiltro)) {
            $espaciosQuery->where('tipo_espacio', $tipoEspacioFiltro);
        }
        if (!empty($pisoFiltro)) {
            $espaciosQuery->whereHas('piso', function ($q) use ($pisoFiltro) {
                $q->where('numero_piso', $pisoFiltro);
            });
        }
        if (!empty($estadoFiltro)) {
            $espaciosQuery->where('estado', $estadoFiltro);
        }
        if (!empty($busqueda)) {
            $espaciosQuery->where('nombre_espacio', 'like', '%' . $busqueda . '%');
        }

        $espacios = $espaciosQuery->get();

        // KPIs optimizados
        $total_espacios = $espacios->count();
        $espacios_ocupados = $espacios->where('estado', 'Ocupado')->count();

        // Calcular estadísticas de reservas de forma más eficiente
        $total_reservas = $espacios->sum(function ($espacio) {
            return $espacio->reservas->count();
        });

        // Calcular promedio de utilización basado en días laborales del mes
        $dias_laborales = $this->calcularDiasLaborales($anio, $mes);
        $horas_totales_disponibles = $total_espacios * $dias_laborales * 15;  // 15 horas por día laboral

        // Calcular horas reales utilizadas
        $reservas_mes = Reserva::whereMonth('fecha_reserva', $mes)
            ->whereYear('fecha_reserva', $anio)
            ->get();

        $horas_utilizadas = $reservas_mes->sum(function ($reserva) {
            if ($reserva->hora && $reserva->hora_salida) {
                $inicio = Carbon::parse($reserva->hora);
                $fin = Carbon::parse($reserva->hora_salida);
                return $inicio->diffInHours($fin, true);  // true para incluir decimales
            } elseif ($reserva->hora) {
                // Si no hay hora de salida, calcular desde la hora actual
                $horaInicio = Carbon::parse($reserva->hora);
                $horaActual = Carbon::now();
                return $horaInicio->diffInHours($horaActual, true);
            }
            return 0;  // 0 horas si no hay hora inicio
        });

        // Calcular promedio de utilización basado en horas reales
        $promedio_utilizacion = $horas_totales_disponibles > 0
            ? round(($horas_utilizadas / $horas_totales_disponibles) * 100)
            : 0;

        // Calcular estadísticas detalladas por espacio
        $resumen = [];
        $labels_grafico = [];
        $data_grafico = [];
        $data_reservas_grafico = [];

        foreach ($espacios as $espacio) {
            $total_reservas_espacio = $espacio->reservas->count();
            $horas_utilizadas = $espacio->reservas->sum(function ($reserva) {
                if ($reserva->hora && $reserva->hora_salida) {
                    $inicio = Carbon::parse($reserva->hora);
                    $fin = Carbon::parse($reserva->hora_salida);
                    return $inicio->diffInHours($fin, true);  // true para incluir decimales
                }
                return 0.83;  // Si no hay hora de salida, asumir 1 módulo de 50 minutos
            });

            // Calcular porcentaje de utilización basado en días con reservas
            $dias_con_reservas = $espacio->reservas->unique('fecha_reserva')->count();
            $porcentaje_utilizacion = $dias_laborales > 0
                ? round(($dias_con_reservas / $dias_laborales) * 100, 1)
                : 0;

            // Determinar estado de utilización
            $estado_utilizacion = $this->determinarEstadoUtilizacion($porcentaje_utilizacion);

            $resumen[] = [
                'id_espacio' => $espacio->id_espacio,
                'nombre' => $espacio->nombre_espacio,
                'tipo_espacio' => $espacio->tipo_espacio,
                'piso' => $espacio->piso ? $espacio->piso->numero_piso : 'N/A',
                'facultad' => $espacio->piso && $espacio->piso->facultad
                    ? $espacio->piso->facultad->nombre_facultad
                    : 'N/A',
                'estado' => $espacio->estado,
                'puestos_disponibles' => $espacio->puestos_disponibles,
                'total_reservas' => $total_reservas_espacio,
                'horas_utilizadas' => $horas_utilizadas,
                'promedio' => $porcentaje_utilizacion,
                'estado_utilizacion' => $estado_utilizacion,
            ];

            $labels_grafico[] = $espacio->nombre_espacio;
            $data_grafico[] = $porcentaje_utilizacion;
            $data_reservas_grafico[] = $total_reservas_espacio;
        }

        // Datos para filtros
        $tiposEspacioDisponibles = Espacio::distinct()->pluck('tipo_espacio')->sort();
        $pisosDisponibles = Piso::whereHas('facultad', function ($q) {
            $q->where('id_facultad', 'IT_TH');
        })->orderBy('numero_piso')->pluck('numero_piso', 'numero_piso');
        $estadosDisponibles = ['Disponible', 'Ocupado', 'Mantenimiento'];

        // Configuración de horarios
        $diasDisponibles = ['lunes', 'martes', 'miercoles', 'jueves', 'viernes'];
        $diaActual = strtolower(now()->locale('es')->isoFormat('dddd'));
        if (!in_array($diaActual, $diasDisponibles))
            $diaActual = 'lunes';

        // Calcular ocupación por horarios de forma más precisa
        $ocupacionHorarios = $this->calcularOcupacionHorarios($espacios, $mes, $anio, $diasDisponibles);

        // Obtener datos del histórico de reservas
        $fechaInicio = $request->get('fecha_inicio', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $fechaFin = $request->get('fecha_fin', Carbon::now()->endOfMonth()->format('Y-m-d'));

        // Obtener reservas del rango de fechas con información completa
        $reservasQuery = Reserva::with(['espacio.piso.facultad', 'profesor', 'solicitante', 'asignatura'])
            ->whereBetween('fecha_reserva', [$fechaInicio, $fechaFin])
            ->whereHas('espacio', function ($q) use ($espacios) {
                $q->whereIn('id_espacio', $espacios->pluck('id_espacio'));
            });

        // Aplicar filtros al histórico
        if (!empty($tipoEspacioFiltro)) {
            $reservasQuery->whereHas('espacio', function ($q) use ($tipoEspacioFiltro) {
                $q->where('tipo_espacio', $tipoEspacioFiltro);
            });
        }
        if (!empty($pisoFiltro)) {
            $reservasQuery->whereHas('espacio.piso', function ($q) use ($pisoFiltro) {
                $q->where('numero_piso', $pisoFiltro);
            });
        }
        if (!empty($estadoFiltro)) {
            $reservasQuery->where('estado', $estadoFiltro);
        }
        if (!empty($busqueda)) {
            $reservasQuery->whereHas('espacio', function ($q) use ($busqueda) {
                $q->where('nombre_espacio', 'like', '%' . $busqueda . '%');
            });
        }

        $reservas = $reservasQuery
            ->orderBy('fecha_reserva', 'desc')
            ->orderBy('hora', 'desc')
            ->get();

        // Preparar datos del histórico
        $historico = [];
        foreach ($reservas as $reserva) {
            $duracionMinutos = 0;
            $horasUtilizadas = 0;

            if ($reserva->hora && $reserva->hora_salida) {
                $inicio = Carbon::parse($reserva->hora);
                $fin = Carbon::parse($reserva->hora_salida);
                $duracionMinutos = $inicio->diffInMinutes($fin);
                $horasUtilizadas = $duracionMinutos / 60;
            }

            $duracionFormateada = $duracionMinutos > 0
                ? (floor($duracionMinutos / 60) > 0 ? floor($duracionMinutos / 60) . 'h ' : '')
                    . ($duracionMinutos % 60) . ' min'
                : '0 min';

            $historico[] = [
                'id_reserva' => $reserva->id_reserva,
                'fecha' => Carbon::parse($reserva->fecha_reserva)->format('d/m/Y'),
                'hora_inicio' => $reserva->hora ? Carbon::parse($reserva->hora)->format('H:i') : 'N/A',
                'hora_fin' => $reserva->hora_salida ? Carbon::parse($reserva->hora_salida)->format('H:i') : 'N/A',
                'espacio' => $reserva->espacio->nombre_espacio . ' (' . $reserva->espacio->id_espacio . ')',
                'tipo_espacio' => $reserva->espacio->tipo_espacio,
                'piso' => $reserva->espacio->piso ? $reserva->espacio->piso->numero_piso : 'N/A',
                'facultad' => $reserva->espacio->piso && $reserva->espacio->piso->facultad
                    ? $reserva->espacio->piso->facultad->nombre_facultad
                    : 'N/A',
                'usuario' => $this->obtenerNombreUsuario($reserva),
                'tipo_usuario' => $this->obtenerTipoUsuario($reserva),
                'asignatura' => $reserva->asignatura ? ($reserva->asignatura->codigo_asignatura . ' - ' . $reserva->asignatura->nombre_asignatura) : 'Sin asignatura',
                'horas_utilizadas' => round($horasUtilizadas, 1),
                'duracion' => $duracionFormateada,
                'estado' => ucfirst($reserva->estado)
            ];
        }

        return view('reportes.espacios', compact(
            'total_espacios',
            'espacios_ocupados',
            'total_reservas',
            'promedio_utilizacion',
            'resumen',
            'labels_grafico',
            'data_grafico',
            'data_reservas_grafico',
            'diasDisponibles',
            'diaActual',
            'ocupacionHorarios',
            'tiposEspacioDisponibles',
            'pisosDisponibles',
            'estadosDisponibles',
            'tipoEspacioFiltro',
            'pisoFiltro',
            'estadoFiltro',
            'busqueda',
            'historico',
            'fechaInicio',
            'fechaFin'
        ));
    }

    /**
     * Calcular días laborales en un mes específico
     */


    /**
     * Determinar estado de utilización basado en porcentaje
     */


    /**
     * Calcular ocupación por horarios por espacio individual
     */


    public function exportEspacios(Request $request, $format)
    {
        // Verificar si es exportación del histórico
        $fechaInicio = $request->get('fecha_inicio');
        $fechaFin = $request->get('fecha_fin');
        $tipoExport = $request->get('tipo_export');

        if ($fechaInicio && $fechaFin && $tipoExport === 'horarios') {
            // Es exportación de horarios
            return $this->exportHorariosEspacios($request, $format);
        } elseif ($fechaInicio && $fechaFin) {
            // Es exportación del histórico
            return $this->exportHistoricoEspacios($request, $format);
        }

        // Exportación del resumen general
        $mes = now()->month;
        $anio = now()->year;

        // Obtener filtros de la request
        $tipoEspacioFiltro = $request->get('tipo_espacio', '');
        $pisoFiltro = $request->get('piso', '');
        $estadoFiltro = $request->get('estado', '');
        $busqueda = $request->get('busqueda', '');

        // Query base optimizada con eager loading de reservas del mes
        $espaciosQuery = Espacio::with(['piso.facultad', 'reservas' => function ($query) use ($mes, $anio) {
            $query
                ->whereMonth('fecha_reserva', $mes)
                ->whereYear('fecha_reserva', $anio);
        }]);

        // Aplicar filtros
        if (!empty($tipoEspacioFiltro)) {
            $espaciosQuery->where('tipo_espacio', $tipoEspacioFiltro);
        }
        if (!empty($pisoFiltro)) {
            $espaciosQuery->whereHas('piso', function ($q) use ($pisoFiltro) {
                $q->where('numero_piso', $pisoFiltro);
            });
        }
        if (!empty($estadoFiltro)) {
            $espaciosQuery->where('estado', $estadoFiltro);
        }
        if (!empty($busqueda)) {
            $espaciosQuery->where('nombre_espacio', 'like', '%' . $busqueda . '%');
        }

        $espacios = $espaciosQuery->get();

        // Días laborales simplificado
        $dias_laborales = collect(range(1, now()->daysInMonth))
            ->map(function ($day) use ($anio, $mes) {
                return Carbon::create($anio, $mes, $day);
            })
            ->filter(function ($date) {
                return $date->isWeekday();
            })
            ->count();

        // Calcular datos para exportación usando la misma lógica que la vista
        $datos = [];
        foreach ($espacios as $espacio) {
            $total_reservas_espacio = $espacio->reservas->count();

            // Calcular horas utilizadas usando Carbon (igual que en la vista)
            $horas_utilizadas = $espacio->reservas->sum(function ($reserva) {
                if ($reserva->hora && $reserva->hora_salida) {
                    $inicio = Carbon::parse($reserva->hora);
                    $fin = Carbon::parse($reserva->hora_salida);
                    return $inicio->diffInHours($fin, true);  // true para valor absoluto
                }
                return 0.83;  // Si no hay hora de salida, asumir 1 módulo de 50 minutos
            });

            // Calcular días con reservas
            $dias_con_reservas = $espacio->reservas->unique('fecha_reserva')->count();

            // Calcular porcentaje de utilización basado en días con reservas
            $promedio = $dias_laborales > 0 ? round(($dias_con_reservas / $dias_laborales) * 100, 1) : 0;
            $estado = $promedio >= 80 ? 'Óptimo' : ($promedio >= 40 ? 'Medio uso' : 'Bajo uso');

            $datos[] = [
                'id_espacio' => $espacio->id_espacio,
                'nombre' => $espacio->nombre_espacio,
                'tipo_espacio' => $espacio->tipo_espacio,
                'piso' => $espacio->piso ? $espacio->piso->numero_piso : 'N/A',
                'facultad' => $espacio->piso && $espacio->piso->facultad ? $espacio->piso->facultad->nombre_facultad : 'N/A',
                'estado' => $espacio->estado,
                'puestos_disponibles' => $espacio->puestos_disponibles ?? 'N/A',
                'total_reservas' => $total_reservas_espacio,
                'horas_utilizadas' => round($horas_utilizadas, 1),
                'promedio_utilizacion' => $promedio,
                'estado_utilizacion' => $estado
            ];
        }

        if ($format === 'excel') {
            return $this->exportarResumenExcel($datos);
        } elseif ($format === 'pdf') {
            return $this->exportarResumenPDF($datos, $tipoEspacioFiltro, $pisoFiltro, $estadoFiltro, $busqueda);
        }

        return redirect()->back()->with('error', 'Formato de exportación no válido');
    }

    public function exportHistoricoEspacios(Request $request, $format)
    {
        try {
            // Obtener filtros de la request
            $fechaInicio = $request->get('fecha_inicio', Carbon::now()->startOfMonth()->format('Y-m-d'));
            $fechaFin = $request->get('fecha_fin', Carbon::now()->endOfMonth()->format('Y-m-d'));
            $piso = $request->get('piso', '');
            $tipoUsuario = $request->get('tipo_usuario', '');
            $tipoEspacioFiltro = $request->get('tipo_espacio_filtro', '');
            $diaFiltro = $request->get('dia_filtro', '');

            // Obtener espacios filtrados
            $espaciosQuery = Espacio::whereHas('piso.facultad', function ($q) {
                $q->where('id_facultad', 'IT_TH');
            });
            if (!empty($piso)) {
                $espaciosQuery->whereHas('piso', function ($q) use ($piso) {
                    $q->where('numero_piso', $piso);
                });
            }
            if (!empty($tipoEspacioFiltro)) {
                $espaciosQuery->where('tipo_espacio', $tipoEspacioFiltro);
            }
            $espacios = $espaciosQuery->get();

            // Obtener reservas filtradas
            $reservasQuery = Reserva::with(['espacio', 'profesor', 'solicitante'])
                ->whereBetween('fecha_reserva', [$fechaInicio, $fechaFin])
                ->where('estado', 'activa')
                ->whereHas('espacio', function ($q) use ($espacios) {
                    $q->whereIn('id_espacio', $espacios->pluck('id_espacio'));
                });

            if (!empty($diaFiltro)) {
                $numeroDia = $this->obtenerNumeroDia($diaFiltro);
                $reservasQuery->whereRaw('DAYOFWEEK(fecha_reserva) = ?', [$numeroDia]);
            }

            if (!empty($tipoUsuario)) {
                if ($tipoUsuario === 'profesor') {
                    $reservasQuery->whereNotNull('run_profesor');
                } elseif ($tipoUsuario === 'solicitante') {
                    $reservasQuery->whereNotNull('run_solicitante');
                }
            }

            $reservas = $reservasQuery
                ->orderBy('fecha_reserva', 'desc')
                ->orderBy('hora', 'desc')
                ->get();

            // Preparar datos para exportación
            $datosExport = [];
            foreach ($reservas as $reserva) {
                $duracionMinutos = 0;
                $horasUtilizadas = 0;

                if ($reserva->hora && $reserva->hora_salida) {
                    $inicio = Carbon::parse($reserva->hora);
                    $fin = Carbon::parse($reserva->hora_salida);
                    $duracionMinutos = $inicio->diffInMinutes($fin);
                    $horasUtilizadas = $duracionMinutos / 60;
                }

                $duracionFormateada = $duracionMinutos > 0
                    ? (floor($duracionMinutos / 60) > 0 ? floor($duracionMinutos / 60) . 'h ' : '')
                        . ($duracionMinutos % 60) . ' min'
                    : '0 min';

                // Determinar si es profesor o solicitante
                $usuario = 'N/A';
                $tipoUsuario = 'N/A';

                if ($reserva->profesor) {
                    $usuario = $reserva->profesor->name ?? 'Profesor no encontrado';
                    $tipoUsuario = 'Profesor';
                } elseif ($reserva->solicitante) {
                    $usuario = $reserva->solicitante->nombre ?? 'Solicitante no encontrado';
                    $tipoUsuario = ucfirst($reserva->solicitante->tipo_solicitante ?? 'Solicitante');
                }

                $datosExport[] = [
                    'fecha' => Carbon::parse($reserva->fecha_reserva)->format('d/m/Y'),
                    'hora_inicio' => $reserva->hora ? Carbon::parse($reserva->hora)->format('H:i') : 'N/A',
                    'hora_fin' => $reserva->hora_salida ? Carbon::parse($reserva->hora_salida)->format('H:i') : 'N/A',
                    'espacio' => $reserva->espacio->nombre_espacio . ' (' . $reserva->espacio->id_espacio . ')',
                    'tipo_espacio' => $reserva->espacio->tipo_espacio,
                    'piso' => $reserva->espacio->piso->numero_piso,
                    'facultad' => $reserva->espacio->piso->facultad->nombre_facultad,
                    'usuario' => $usuario,
                    'tipo_usuario' => $tipoUsuario,
                    'horas_utilizadas' => round($horasUtilizadas, 1),
                    'duracion' => $duracionFormateada,
                    'estado' => ucfirst($reserva->estado)
                ];
            }

            if ($format === 'excel') {
                return $this->exportarHistoricoExcel($datosExport, $fechaInicio, $fechaFin);
            } elseif ($format === 'pdf') {
                return $this->exportarHistoricoPDF($datosExport, $fechaInicio, $fechaFin, $piso, $tipoUsuario, $tipoEspacioFiltro);
            }

            return redirect()->back()->with('error', 'Formato de exportación no válido');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error al exportar: ' . $e->getMessage());
        }
    }



    // 3. Accesos registrados
    public function accesos(Request $request)
    {
        // Obtener datos para filtros primero
        $pisos = $this->obtenerPisosDisponibles();
        $espacios = $this->obtenerEspaciosDisponibles();
        $tiposUsuario = $this->obtenerTiposUsuario();

        // Obtener filtros de la request
        $fechaInicio = $request->get('fecha_inicio', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $fechaFin = $request->get('fecha_fin', Carbon::now()->endOfMonth()->format('Y-m-d'));
        $piso = $request->get('piso', '');  // Valor vacío por defecto
        $tipoUsuario = $request->get('tipo_usuario', '');  // Valor vacío por defecto
        $espacio = $request->get('espacio', '');  // Valor vacío por defecto

        // Obtener accesos registrados (reservas activas)
        $accesos = $this->obtenerAccesosRegistrados($fechaInicio, $fechaFin, $piso, $tipoUsuario, $espacio);

        return view('reportes.accesos', compact(
            'accesos',
            'fechaInicio',
            'fechaFin',
            'piso',
            'tipoUsuario',
            'espacio',
            'pisos',
            'espacios',
            'tiposUsuario'
        ));
    }

    // Método para limpiar filtros
    public function limpiarFiltrosAccesos()
    {
        return redirect()->route('reportes.accesos')->with('success', 'Filtros limpiados correctamente');
    }

    public function exportAccesos($format)
    {
        try {
            // Obtener todos los accesos para exportar
            $accesos = $this->obtenerAccesosRegistrados(
                Carbon::now()->startOfMonth()->format('Y-m-d'),
                Carbon::now()->endOfMonth()->format('Y-m-d')
            );

            if ($accesos->isEmpty()) {
                return redirect()->back()->with('error', 'No hay datos para exportar');
            }

            if ($format === 'excel') {
                return $this->exportarAccesosExcel($accesos);
            } elseif ($format === 'pdf') {
                return $this->exportarAccesosPDF($accesos);
            }

            return redirect()->back()->with('error', 'Formato de exportación no válido');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error al exportar: ' . $e->getMessage());
        }
    }

    public function exportAccesosConFiltros(Request $request, $format)
    {
        try {
            // Obtener filtros de la request
            $fechaInicio = $request->get('fecha_inicio', Carbon::now()->startOfMonth()->format('Y-m-d'));
            $fechaFin = $request->get('fecha_fin', Carbon::now()->endOfMonth()->format('Y-m-d'));
            $piso = $request->get('piso');
            $tipoUsuario = $request->get('tipo_usuario');
            $espacio = $request->get('espacio');

            // Obtener accesos con filtros aplicados
            $accesos = $this->obtenerAccesosRegistrados($fechaInicio, $fechaFin, $piso, $tipoUsuario, $espacio);

            if ($accesos->isEmpty()) {
                return redirect()->back()->with('error', 'No hay datos para exportar con los filtros aplicados');
            }

            if ($format === 'excel') {
                return $this->exportarAccesosExcel($accesos);
            } elseif ($format === 'pdf') {
                return $this->exportarAccesosPDF($accesos);
            }

            return redirect()->back()->with('error', 'Formato de exportación no válido');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error al exportar: ' . $e->getMessage());
        }
    }

    public function getDetallesAcceso($id)
    {
        $reserva = Reserva::with(['user', 'espacio.piso.facultad.sede.universidad'])
            ->where('id_reserva', $id)
            ->first();

        if (!$reserva) {
            return response()->json(['error' => 'Acceso no encontrado'], 404);
        }

        $detalles = [
            'id' => $reserva->id_reserva,
            'usuario' => [
                'nombre' => $reserva->user->name ?? 'Usuario no encontrado',
                'run' => $reserva->user->run ?? 'N/A',
                'email' => $reserva->user->email ?? 'N/A',
                'celular' => $reserva->user->celular ?? 'N/A',
                'tipo_usuario' => $this->determinarTipoUsuario($reserva->user),
                'universidad' => $reserva->user->universidad->nombre_universidad ?? 'N/A',
                'facultad' => $reserva->user->facultad->nombre_facultad ?? 'N/A',
                'carrera' => $reserva->user->carrera->nombre_carrera ?? 'N/A',
            ],
            'espacio' => [
                'nombre' => $reserva->espacio->nombre_espacio ?? 'Espacio no encontrado',
                'tipo' => $reserva->espacio->tipo_espacio ?? 'N/A',
                'capacidad' => $reserva->espacio->capacidad ?? 'N/A',
                'piso' => $reserva->espacio->piso->numero_piso ?? 'N/A',
                'facultad' => $reserva->espacio->piso->facultad->nombre_facultad ?? 'N/A',
                'sede' => $reserva->espacio->piso->facultad->sede->nombre_sede ?? 'N/A',
                'universidad' => $reserva->espacio->piso->facultad->sede->universidad->nombre_universidad ?? 'N/A',
            ],
            'reserva' => [
                'fecha' => Carbon::parse($reserva->fecha_reserva)->format('d/m/Y'),
                'hora_entrada' => $reserva->hora,
                'hora_salida' => $reserva->hora_salida ? Carbon::parse($reserva->hora_salida)->format('H:i:s') : 'En curso',
                'tipo_reserva' => $reserva->tipo_reserva ?? 'Directa',
                'estado' => $reserva->estado,
                'duracion' => $this->calcularDuracion($reserva->hora, $reserva->hora_salida),
            ],
            'incidencias' => $this->obtenerIncidencias($reserva->id_reserva)
        ];

        return response()->json($detalles);
    }

    /** Identificar problemas específicos de una área académica */

    /**
     * Obtener accesos registrados con filtros
     */
    public function obtenerAccesosRegistrados($fechaInicio, $fechaFin, $piso = null, $tipoUsuario = null, $espacio = null)
    {
        // OPTIMIZACIÓN: Seleccionar solo los campos necesarios y limitar resultados
        $query = Reserva::select([
            'id_reserva',
            'run_profesor',
            'run_solicitante',
            'id_espacio',
            'fecha_reserva',
            'hora',
            'hora_salida',
            'tipo_reserva',
            'estado'
        ])
            ->with([
                'profesor:run_profesor,name,email',
                'solicitante:run_solicitante,nombre,correo,tipo_solicitante',
                'espacio:id_espacio,nombre_espacio,piso_id',
                'espacio.piso:id,numero_piso,id_facultad',
                'espacio.piso.facultad:id_facultad,nombre_facultad'
            ])
            ->whereBetween('fecha_reserva', [$fechaInicio, $fechaFin])
            ->whereIn('estado', ['activa', 'finalizada'])
            ->whereNotNull('hora')
            ->orderBy('fecha_reserva', 'desc')
            ->orderBy('hora', 'desc');

        // Filtrar por piso
        if (!empty($piso)) {
            $query->whereHas('espacio.piso', function ($q) use ($piso) {
                $q->where('numero_piso', $piso);
            });
        }

        // Filtrar por tipo de usuario
        if (!empty($tipoUsuario)) {
            if ($tipoUsuario === 'profesor') {
                $query->whereNotNull('run_profesor');
            } elseif ($tipoUsuario === 'solicitante') {
                $query->whereNotNull('run_solicitante');
            } elseif ($tipoUsuario === 'estudiante') {
                $query->whereHas('solicitante', function ($q) {
                    $q->where('tipo_solicitante', 'estudiante');
                });
            } elseif ($tipoUsuario === 'administrativo') {
                $query->whereHas('solicitante', function ($q) {
                    $q->where('tipo_solicitante', 'personal');
                });
            }
        }

        // Filtrar por espacio
        if (!empty($espacio)) {
            $query->whereHas('espacio', function ($q) use ($espacio) {
                $q->where('nombre_espacio', 'like', '%' . $espacio . '%');
            });
        }

        // OPTIMIZACIÓN: Limitar a 500 registros máximo para evitar timeout
        $query->limit(500);

        // OPTIMIZACIÓN: Usar chunk para procesar en lotes pequeños
        $accesos = collect();

        $query->chunk(100, function ($reservas) use (&$accesos) {
            foreach ($reservas as $reserva) {
                // Determinar si es profesor o solicitante
                $esProfesor = !empty($reserva->run_profesor);
                $esSolicitante = !empty($reserva->run_solicitante);

                if ($esProfesor && $reserva->profesor) {
                    $usuario = $reserva->profesor->name;
                    $run = $reserva->profesor->run_profesor;
                    $email = $reserva->profesor->email;
                    $tipoUsuario = 'profesor';
                } elseif ($esSolicitante && $reserva->solicitante) {
                    $usuario = $reserva->solicitante->nombre;
                    $tipoUsuario = ucfirst($reserva->solicitante->tipo_solicitante ?? 'Solicitante');
                    $run = $reserva->solicitante->run_solicitante;
                    $email = $reserva->solicitante->correo;
                } else {
                    $usuario = 'Usuario no identificado';
                    $run = 'N/A';
                    $email = 'N/A';
                    $tipoUsuario = 'desconocido';
                }

                $accesos->push([
                    'id' => $reserva->id_reserva,
                    'usuario' => $usuario ?? 'N/A',
                    'run' => $run ?? 'N/A',
                    'email' => $email ?? 'N/A',
                    'tipo_usuario' => $tipoUsuario,
                    'espacio' => $reserva->espacio->nombre_espacio ?? 'Espacio no encontrado',
                    'id_espacio' => $reserva->espacio->id_espacio ?? '',
                    'piso' => $reserva->espacio->piso->numero_piso ?? 'N/A',
                    'facultad' => $reserva->espacio->piso->facultad->nombre_facultad ?? 'N/A',
                    'fecha' => $reserva->fecha_reserva,
                    'hora_entrada' => $reserva->hora,
                    'hora_salida' => $reserva->hora_salida ? Carbon::parse($reserva->hora_salida)->format('H:i:s') : 'En curso',
                    'tipo_reserva' => $reserva->tipo_reserva ?? 'Directa',
                    'estado' => $reserva->estado,
                    'duracion' => $this->calcularDuracion($reserva->hora, $reserva->hora_salida),
                    'incidencias' => []  // Optimización: evitar consultas adicionales
                ]);
            }
        });

        return $accesos;
    }

    /**
     * Determinar el tipo de usuario basado en los campos del modelo
     */


    /**
     * Calcular duración de la reserva
     */


    /**
     * Obtener incidencias de la reserva
     */


    /**
     * Obtener pisos disponibles (con caché)
     */


    /**
     * Obtener espacios disponibles (con caché)
     */


    /**
     * Obtener tipos de usuario
     */


    /**
     * Exportar accesos a Excel
     */


    /**
     * Exportar accesos a PDF
     */


    /**
     * Obtener tipos de espacio disponibles
     */


    /**
     * Obtener días disponibles
     */



    /**
     * Calcular horarios pico basados en los datos de ocupación
     */


    /**
     * Obtener número de día de la semana para MySQL
     */


    /**
     * Obtener hora de inicio o fin de un módulo
     */


    /**
     * Fuente de verdad única para el mapeo de módulos académicos.
     * Equivalente al ModulosSeeder: 15 módulos, 08:10–23:00.
     */


    /**
     * Obtener módulo por hora
     */


    public function exportHorariosEspacios(Request $request, $format)
    {
        try {
            // Obtener parámetros
            $fechaInicio = $request->get('fecha_inicio');
            $fechaFin = $request->get('fecha_fin');
            $moduloInicio = $request->get('modulo_inicio', 0);
            $moduloFin = $request->get('modulo_fin', 14);
            $busqueda = $request->get('busqueda', '');
            $tipoEspacio = $request->get('tipo_espacio', '');
            $piso = $request->get('piso', '');
            $estado = $request->get('estado', '');

            // Obtener espacios filtrados
            $espaciosQuery = Espacio::with(['piso.facultad']);

            if (!empty($tipoEspacio)) {
                $espaciosQuery->where('tipo_espacio', $tipoEspacio);
            }
            if (!empty($piso)) {
                $espaciosQuery->whereHas('piso', function ($q) use ($piso) {
                    $q->where('numero_piso', $piso);
                });
            }
            if (!empty($estado)) {
                $espaciosQuery->where('estado', $estado);
            }
            if (!empty($busqueda)) {
                $espaciosQuery->where('nombre_espacio', 'like', '%' . $busqueda . '%');
            }

            $espacios = $espaciosQuery->get();

            // Obtener datos de ocupación por horarios
            $ocupacionHorarios = $this->calcularOcupacionHorarios($espacios, Carbon::parse($fechaInicio)->month, Carbon::parse($fechaInicio)->year, ['lunes', 'martes', 'miercoles', 'jueves', 'viernes']);

            // Preparar datos para exportación
            $datosExport = [];
            $modulosDia = [
                0 => '08:10-09:00', 1 => '09:10-10:00', 2 => '10:10-11:00', 3 => '11:10-12:00', 4 => '12:10-13:00',
                5 => '13:10-14:00', 6 => '14:10-15:00', 7 => '15:10-16:00', 8 => '16:10-17:00', 9 => '17:10-18:00',
                10 => '18:10-19:00', 11 => '19:10-20:00', 12 => '20:10-21:00', 13 => '21:10-22:00', 14 => '22:10-23:00'
            ];

            foreach ($espacios as $espacio) {
                $fila = [
                    'espacio' => $espacio->nombre_espacio . ' (' . $espacio->id_espacio . ')',
                    'tipo' => $espacio->tipo_espacio,
                    'piso' => $espacio->piso ? $espacio->piso->numero_piso : 'N/A',
                    'facultad' => $espacio->piso && $espacio->piso->facultad ? $espacio->piso->facultad->nombre_facultad : 'N/A'
                ];

                // Agregar columnas de módulos
                for ($i = $moduloInicio; $i <= $moduloFin; $i++) {
                    $moduloReal = $i + 1;
                    // Obtener ocupación del día específico (lunes por defecto)
                    $ocupacion = 0;
                    if (isset($ocupacionHorarios[$espacio->id_espacio]['lunes'][$moduloReal])) {
                        $ocupacion = $ocupacionHorarios[$espacio->id_espacio]['lunes'][$moduloReal];
                    }
                    $fila['modulo_' . $moduloReal] = $ocupacion . '%';
                }

                $datosExport[] = $fila;
            }

            if ($format === 'excel') {
                return $this->exportarHorariosExcel($datosExport, $fechaInicio, $moduloInicio, $moduloFin, $modulosDia);
            } elseif ($format === 'pdf') {
                return $this->exportarHorariosPDF($datosExport, $fechaInicio, $moduloInicio, $moduloFin, $modulosDia);
            }

            return redirect()->back()->with('error', 'Formato de exportación no válido');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error al exportar horarios: ' . $e->getMessage());
        }
    }





    /**
     * Obtiene el histórico de reservas por tipo de espacio
     */
    public function getHistoricoTipoEspacio(Request $request)
    {
        try {
            $request->validate([
                'fecha_inicio' => 'required|date',
                'fecha_fin' => 'required|date|after_or_equal:fecha_inicio',
                'tipo_espacio' => 'nullable|string',
                'page' => 'nullable|integer|min:1'
            ]);

            $query = Reserva::with(['espacio.piso.facultad', 'solicitante', 'profesor'])
                ->whereBetween('fecha_reserva', [
                    $request->fecha_inicio,
                    $request->fecha_fin
                ]);

            // Filtrar por tipo de espacio si se especifica
            if ($request->filled('tipo_espacio')) {
                $query->whereHas('espacio', function ($q) use ($request) {
                    $q->where('tipo_espacio', $request->tipo_espacio);
                });
            }

            // Obtener datos paginados
            $reservas = $query
                ->orderBy('fecha_reserva', 'desc')
                ->orderBy('hora', 'asc')
                ->paginate(15);

            // Calcular KPIs con estados correctos
            $total = $query->count();
            $finalizadas = $query->where('estado', 'finalizada')->count();
            $activas = $query->where('estado', 'activa')->count();

            // Formatear datos para la respuesta
            $data = $reservas->getCollection()->map(function ($reserva) {
                // Determinar si es profesor o solicitante
                $usuario = 'N/A';
                $tipoUsuario = 'N/A';
                $run = 'N/A';
                $email = 'N/A';

                if ($reserva->profesor) {
                    $usuario = $reserva->profesor->name ?? 'Profesor no encontrado';
                    $tipoUsuario = 'Profesor';
                    $run = $reserva->profesor->run_profesor ?? 'N/A';
                    $email = $reserva->profesor->email ?? 'N/A';
                } elseif ($reserva->solicitante) {
                    $usuario = $reserva->solicitante->nombre ?? 'Solicitante no encontrado';
                    $tipoUsuario = ucfirst($reserva->solicitante->tipo_solicitante ?? 'Solicitante');
                    $run = $reserva->solicitante->run_solicitante ?? 'N/A';
                    $email = $reserva->solicitante->correo ?? 'N/A';
                }

                // Calcular duración
                $duracion = 'N/A';
                if ($reserva->hora && $reserva->hora_salida) {
                    $inicio = Carbon::parse($reserva->hora);
                    $fin = Carbon::parse($reserva->hora_salida);
                    $diff = $inicio->diffInMinutes($fin);

                    if ($diff >= 60) {
                        $horas = floor($diff / 60);
                        $minutos = $diff % 60;
                        $duracion = $minutos > 0 ? "{$horas}h {$minutos}min" : "{$horas}h";
                    } else {
                        $duracion = "{$diff} min";
                    }
                } elseif ($reserva->hora && $reserva->estado === 'activa') {
                    $duracion = 'En curso';
                }

                // Formatear hora de salida
                $horaSalida = 'N/A';
                if ($reserva->hora_salida) {
                    $horaSalida = Carbon::parse($reserva->hora_salida)->format('H:i:s');
                } elseif ($reserva->estado === 'activa') {
                    $horaSalida = 'En curso';
                }

                return [
                    'profesor_solicitante' => $usuario,
                    'run' => $run,
                    'email' => $email,
                    'espacio' => ($reserva->espacio->nombre_espacio ?? 'N/A') . ' (' . ($reserva->espacio->id_espacio ?? 'N/A') . ', Piso ' . ($reserva->espacio->piso->numero_piso ?? 'N/A') . ')',
                    'facultad' => $reserva->espacio->piso->facultad->nombre_facultad ?? 'N/A',
                    'fecha' => Carbon::parse($reserva->fecha_reserva)->format('d/m/Y'),
                    'hora_inicio' => $reserva->hora ? Carbon::parse($reserva->hora)->format('H:i:s') : 'N/A',
                    'hora_termino' => $horaSalida,
                    'duracion' => $duracion,
                    'tipo_usuario' => $tipoUsuario,
                    'estado' => $reserva->estado
                ];
            });

            return response()->json([
                'data' => $data,
                'current_page' => $reservas->currentPage(),
                'last_page' => $reservas->lastPage(),
                'per_page' => $reservas->perPage(),
                'total' => $total,
                'finalizadas' => $finalizadas,
                'activas' => $activas
            ]);
        } catch (\Exception $e) {
            \Log::error('Error en getHistoricoTipoEspacio: ' . $e->getMessage());
            return response()->json([
                'error' => 'Error interno del servidor',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Exportar datos del histórico por tipo de espacio
     */
    public function exportTipoEspacio(Request $request, $format)
    {
        try {
            // Obtener parámetros de filtro
            $fechaInicio = $request->get('fecha_inicio', now()->startOfMonth()->format('Y-m-d'));
            $fechaFin = $request->get('fecha_fin', now()->endOfMonth()->format('Y-m-d'));
            $tipoEspacio = $request->get('tipo_espacio', '');

            // Obtener datos del histórico
            $query = Reserva::with(['espacio.piso.facultad', 'solicitante', 'profesor'])
                ->whereBetween('fecha_reserva', [$fechaInicio, $fechaFin]);

            // Filtrar por tipo de espacio si se especifica
            if (!empty($tipoEspacio)) {
                $query->whereHas('espacio', function ($q) use ($tipoEspacio) {
                    $q->where('tipo_espacio', $tipoEspacio);
                });
            }

            $reservas = $query
                ->orderBy('fecha_reserva', 'desc')
                ->orderBy('hora', 'asc')
                ->get();

            // Formatear datos para exportación
            $datos = $reservas->map(function ($reserva) {
                // Determinar si es profesor o solicitante
                $usuario = 'N/A';
                $tipoUsuario = 'N/A';
                $run = 'N/A';
                $email = 'N/A';

                if ($reserva->profesor) {
                    $usuario = $reserva->profesor->name ?? 'Profesor no encontrado';
                    $tipoUsuario = 'Profesor';
                    $run = $reserva->profesor->run_profesor ?? 'N/A';
                    $email = $reserva->profesor->email ?? 'N/A';
                } elseif ($reserva->solicitante) {
                    $usuario = $reserva->solicitante->nombre ?? 'Solicitante no encontrado';
                    $tipoUsuario = ucfirst($reserva->solicitante->tipo_solicitante ?? 'Solicitante');
                    $run = $reserva->solicitante->run_solicitante ?? 'N/A';
                    $email = $reserva->solicitante->correo ?? 'N/A';
                }

                // Calcular duración
                $duracion = 'N/A';
                if ($reserva->hora && $reserva->hora_salida) {
                    $inicio = Carbon::parse($reserva->hora);
                    $fin = Carbon::parse($reserva->hora_salida);
                    $diff = $inicio->diffInMinutes($fin);

                    if ($diff >= 60) {
                        $horas = floor($diff / 60);
                        $minutos = $diff % 60;
                        $duracion = $minutos > 0 ? "{$horas}h {$minutos}min" : "{$horas}h";
                    } else {
                        $duracion = "{$diff} min";
                    }
                } elseif ($reserva->hora && $reserva->estado === 'activa') {
                    $duracion = 'En curso';
                }

                // Formatear hora de salida
                $horaSalida = 'N/A';
                if ($reserva->hora_salida) {
                    $horaSalida = Carbon::parse($reserva->hora_salida)->format('H:i:s');
                } elseif ($reserva->estado === 'activa') {
                    $horaSalida = 'En curso';
                }

                return [
                    'profesor_solicitante' => $usuario,
                    'run' => $run,
                    'email' => $email,
                    'espacio' => ($reserva->espacio->nombre_espacio ?? 'N/A') . ' (' . ($reserva->espacio->id_espacio ?? 'N/A') . ', Piso ' . ($reserva->espacio->piso->numero_piso ?? 'N/A') . ')',
                    'facultad' => $reserva->espacio->piso->facultad->nombre_facultad ?? 'N/A',
                    'fecha' => Carbon::parse($reserva->fecha_reserva)->format('d/m/Y'),
                    'hora_inicio' => $reserva->hora ? Carbon::parse($reserva->hora)->format('H:i:s') : 'N/A',
                    'hora_termino' => $horaSalida,
                    'duracion' => $duracion,
                    'tipo_usuario' => $tipoUsuario,
                    'estado' => $reserva->estado
                ];
            });

            $total_reservas = $reservas->count();
            $completadas = $reservas->where('estado', 'finalizada')->count();
            $canceladas = $reservas->where('estado', 'cancelada')->count();
            $en_progreso = $reservas->where('estado', 'activa')->count();

            if ($format === 'excel') {
                return $this->exportarHistoricoTipoEspacioExcel($datos, $fechaInicio, $fechaFin, $tipoEspacio);
            } elseif ($format === 'pdf') {
                return $this->exportarHistoricoTipoEspacioPDF($datos, $fechaInicio, $fechaFin, $tipoEspacio, $total_reservas, $completadas, $canceladas, $en_progreso);
            }

            return redirect()->back()->with('error', 'Formato de exportación no válido');
        } catch (\Exception $e) {
            \Log::error('Error en exportTipoEspacio: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error al exportar: ' . $e->getMessage());
        }
    }

    /**
     * Exportar histórico a Excel
     */


    /**
     * Exportar histórico a PDF
     */


    /**
     * Obtener el nombre del usuario (profesor o solicitante) de una reserva
     */


    /**
     * Obtener el tipo de usuario (profesor o solicitante) de una reserva
     */


    /**
     * Reporte de Salas de Estudio
     */
    public function salasEstudio(Request $request)
    {
        $fechaInicio = $request->input('fecha_inicio', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $fechaFin = $request->input('fecha_fin', Carbon::now()->endOfMonth()->format('Y-m-d'));
        $salaId = $request->input('sala_id');

        // Obtener todas las salas de estudio
        $salasEstudio = Espacio::where('tipo_espacio', 'Sala de Estudio')->get();

        // Construir query base
        $query = Reserva::with(['solicitante', 'espacio'])
            ->whereHas('espacio', function ($q) {
                $q->where('tipo_espacio', 'Sala de Estudio');
            })
            ->whereBetween('fecha_reserva', [$fechaInicio, $fechaFin])
            ->where('observaciones', 'Sala de Estudio')
            ->orderBy('fecha_reserva')
            ->orderBy('hora');

        if ($salaId) {
            $query->where('id_espacio', $salaId);
        }

        $reservas = $query->get();

        // Agrupar reservas por sesiones
        $gruposPorSala = [];

        foreach ($salasEstudio as $sala) {
            $reservasSala = $reservas->where('id_espacio', $sala->id_espacio);
            $grupos = $this->agruparReservasPorSesion($reservasSala);

            if (count($grupos) > 0) {
                $gruposPorSala[$sala->id_espacio] = [
                    'sala' => $sala,
                    'grupos' => $grupos
                ];
            }
        }

        // KPIs
        $totalAccesos = $reservas->count();
        $totalGrupos = collect($gruposPorSala)->sum(function ($item) {
            return count($item['grupos']);
        });
        $salasUsadas = count($gruposPorSala);
        $promedioPersonasPorGrupo = $totalGrupos > 0 ? round($totalAccesos / $totalGrupos, 1) : 0;

        return view('reportes.salas-estudio', compact(
            'gruposPorSala',
            'salasEstudio',
            'fechaInicio',
            'fechaFin',
            'salaId',
            'totalAccesos',
            'totalGrupos',
            'salasUsadas',
            'promedioPersonasPorGrupo'
        ));
    }

    /**
     * Agrupar reservas por sesión
     * Un grupo se forma cuando hay solapamiento de tiempo entre reservas
     */


    /**
     * Exportar reporte de salas de estudio
     */
    public function exportSalasEstudio(Request $request, $format)
    {
        $fechaInicio = $request->input('fecha_inicio', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $fechaFin = $request->input('fecha_fin', Carbon::now()->endOfMonth()->format('Y-m-d'));
        $salaId = $request->input('sala_id');

        // Obtener datos (mismo código que en salasEstudio)
        $salasEstudio = Espacio::where('tipo_espacio', 'Sala de Estudio')->get();

        $query = Reserva::with(['solicitante', 'espacio'])
            ->whereHas('espacio', function ($q) {
                $q->where('tipo_espacio', 'Sala de Estudio');
            })
            ->whereBetween('fecha_reserva', [$fechaInicio, $fechaFin])
            ->where('observaciones', 'Sala de Estudio')
            ->orderBy('fecha_reserva')
            ->orderBy('hora');

        if ($salaId) {
            $query->where('id_espacio', $salaId);
        }

        $reservas = $query->get();

        $gruposPorSala = [];
        foreach ($salasEstudio as $sala) {
            $reservasSala = $reservas->where('id_espacio', $sala->id_espacio);
            $grupos = $this->agruparReservasPorSesion($reservasSala);

            if (count($grupos) > 0) {
                $gruposPorSala[$sala->id_espacio] = [
                    'sala' => $sala,
                    'grupos' => $grupos
                ];
            }
        }

        // Obtener vetos activos
        $vetosActivos = \App\Models\VetoSalaEstudio::with(['solicitante'])
            ->where('estado', 'activo')
            ->orderBy('fecha_veto', 'desc')
            ->get();

        if ($format === 'pdf') {
            $pdf = PDF::loadView('reportes.salas-estudio-pdf', compact('gruposPorSala', 'fechaInicio', 'fechaFin', 'vetosActivos'));
            return $pdf->download('reporte-salas-estudio-' . now()->format('Y-m-d') . '.pdf');
        }

        // Para Excel, crear Export class más tarde
        return back()->with('error', 'Formato no soportado aún');
    }

    public function usoAuditorio(Request $request)
    {
        $fechaInicio = $request->get('fecha_inicio', now()->startOfMonth()->format('Y-m-d'));
        $fechaFin = $request->get('fecha_fin', now()->endOfMonth()->format('Y-m-d'));

        $data = $this->prepareAuditorioReportData($fechaInicio, $fechaFin);

        return view('reportes.uso-auditorio', array_merge($data, [
            'fechaInicio' => $fechaInicio,
            'fechaFin' => $fechaFin,
        ]));
    }

    public function exportUsoAuditorio(Request $request, $format)
    {
        try {
            $fechaInicio = $request->get('fecha_inicio', now()->startOfMonth()->format('Y-m-d'));
            $fechaFin = $request->get('fecha_fin', now()->endOfMonth()->format('Y-m-d'));

            $dataReporte = $this->prepareAuditorioReportData($fechaInicio, $fechaFin);
            $reservas = $dataReporte['reservasRaw'];
            $historico = $dataReporte['historico'];

            if ($format === 'excel') {
                $filename = 'uso_auditorio_' . date('Y-m-d_H-i-s') . '.xlsx';

                // Formatear datos para Excel (usar estructura similar a la tabla pero con más info si se desea)
                $excelData = $historico->map(function ($h) {
                    return [
                        'Usuario' => $h['usuario'],
                        'Auditorio' => $h['espacio'],
                        'Fecha' => $h['fecha'],
                        'Hora Entrada' => $h['hora_inicio'],
                        'Hora Salida' => $h['hora_fin'],
                        'Duración' => $h['duracion'],
                        'Motivo/Asig' => $h['asignatura'],
                        'Estado' => $h['estado'],
                    ];
                });

                return Excel::download(new \App\Exports\UsoAuditorioExport($excelData), $filename);
            } elseif ($format === 'pdf') {
                $pdfData = [
                    'datos' => $historico,
                    'fecha_inicio' => Carbon::parse($fechaInicio)->format('d/m/Y'),
                    'fecha_fin' => Carbon::parse($fechaFin)->format('d/m/Y'),
                    'fecha_generacion' => now()->format('d/m/Y H:i:s'),
                    'total_reservas' => $dataReporte['totalReservas'],
                    'completadas' => $reservas->where('estado', 'finalizada')->count(),
                    'activas' => $reservas->where('estado', 'activa')->count(),
                    'horas_utilizadas' => $dataReporte['horasUtilizadas'],
                ];

                $filename = 'uso_auditorio_' . date('Y-m-d_H-i-s') . '.pdf';
                return Pdf::loadView('reportes.pdf.uso-auditorio', $pdfData)->download($filename);
            }

            return redirect()->back()->with('error', 'Formato de exportación no válido');
        } catch (\Exception $e) {
            \Log::error('Error en exportUsoAuditorio: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error al exportar: ' . $e->getMessage());
        }
    }

    /**
     * Prepara los datos para el reporte de uso de auditorio (Web y Exportación)
     */

}
