<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use App\Models\Espacio;
use App\Models\Reserva;
use App\Models\Planificacion_Asignatura;
use App\Models\Piso;
use App\Models\AreaAcademica;
use App\Models\Asignatura;
use App\Helpers\SemesterHelper;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use App\Exports\AccesosExport;

class ReportController extends Controller
{
    /**
     * Determina si una hora está en el turno diurno o vespertino
     * Diurno: 08:00 - 19:00
     * Vespertino: 19:00 - 23:00
     */
    private function esTurno($hora, $turno = null)
    {
        if ($turno === null) {
            return true;
        }

        $horaInt = (int) substr($hora, 0, 2);
        
        if ($turno === 'diurno') {
            return $horaInt >= 8 && $horaInt < 19;
        } elseif ($turno === 'vespertino') {
            return $horaInt >= 19 && $horaInt < 23;
        }
        
        return true;
    }

    /**
     * Calcula las horas disponibles por turno
     */
    private function horasPorTurno($turno = null, $fecha = null)
    {
        // Si es sábado, solo hay clases hasta las 13:00 (5 horas en turno diurno)
        if ($fecha && $fecha->isSaturday()) {
            if ($turno === 'diurno') {
                return 5; // 08:00 - 13:00 los sábados
            } elseif ($turno === 'vespertino') {
                return 0; // No hay clases vespertinas los sábados
            }
            return 5; // Total los sábados
        }
        
        // Días normales (lunes a viernes)
        if ($turno === 'diurno') {
            return 11; // 08:00 - 19:00
        } elseif ($turno === 'vespertino') {
            return 4; // 19:00 - 23:00
        }
        
        return 15; // Total
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
            ->map(function($day) use ($anio, $mes) {
                return Carbon::create($anio, $mes, $day);
            })
            ->filter(function($date) {
                return $date->isWeekday();
            })->count();

        $modulos_posibles = $total_espacios * $dias_laborales * 15; // 15 módulos por día
        $modulos_reservados = Reserva::whereMonth('fecha_reserva', $mes)
            ->whereYear('fecha_reserva', $anio)
            ->count();
        
        // Calcular horas desde planificaciones
        $inicioMes = Carbon::create($anio, $mes, 1)->startOfDay();
        $finMes = Carbon::create($anio, $mes, 1)->endOfMonth()->endOfDay();
        $periodo = SemesterHelper::getCurrentPeriod();
        
        $horas_planificaciones = 0;
        $planificaciones = Planificacion_Asignatura::with(['modulo'])
            ->whereHas('horario', function($q) use ($periodo) {
                $q->where('periodo', $periodo);
            })
            ->get();
        
        // Calcular horas de planificaciones para el mes
        for ($fecha = $inicioMes->copy(); $fecha->lte($finMes); $fecha->addDay()) {
            if (!$fecha->isWeekday() && !$fecha->isSaturday()) continue;
            
            $diaSemana = strtolower($fecha->locale('es')->isoFormat('dddd'));
            $planificacionesDia = $planificaciones->filter(function($plan) use ($diaSemana) {
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
        
        $horas_reservas = $reservas_mes->sum(function($reserva) {
            if ($reserva->hora && $reserva->hora_salida) {
                $inicio = Carbon::parse($reserva->hora);
                $fin = Carbon::parse($reserva->hora_salida);
                return $inicio->diffInHours($fin, true); // true para incluir decimales
            }
            return 0.83; // Si no hay hora de salida, asumir 1 módulo de 50 minutos
        });
        
        // Total de horas utilizadas
        $horas_utilizadas = $horas_planificaciones + $horas_reservas;
        
        // Calcular horas totales disponibles considerando sábados (5 horas) vs días normales (15 horas)
        $horas_totales_disponibles = 0;
        for ($fecha = $inicioMes->copy(); $fecha->lte($finMes); $fecha->addDay()) {
            if ($fecha->isWeekday() || $fecha->isSaturday()) {
                $horas_totales_disponibles += $total_espacios * $this->horasPorTurno(null, $fecha);
            }
        }
        $promedio_utilizacion = $horas_totales_disponibles > 0 ? 
            round(($horas_utilizadas / $horas_totales_disponibles) * 100) : 0;

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
            $planificaciones_tipo = $planificaciones->filter(function($plan) use ($tipo) {
                return $plan->espacio && $plan->espacio->tipo_espacio === $tipo;
            });
            
            for ($fecha = $inicioMes->copy(); $fecha->lte($finMes); $fecha->addDay()) {
                if (!$fecha->isWeekday() && !$fecha->isSaturday()) continue;
                
                $diaSemana = strtolower($fecha->locale('es')->isoFormat('dddd'));
                $planificacionesDia = $planificaciones_tipo->filter(function($plan) use ($diaSemana) {
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
            
            $horas_reservas_tipo = $reservas_tipo->sum(function($r) {
                if ($r->hora && $r->hora_salida) {
                    return Carbon::parse($r->hora)->diffInHours(Carbon::parse($r->hora_salida), true);
                }
                return 0.83; // 50 min default si no hay hora_salida
            });
            
            // Total de horas utilizadas = planificaciones + reservas
            $horas_utilizadas = $horas_plan_tipo + $horas_reservas_tipo;
            
            // Calcular horas disponibles considerando sábados (5 horas) vs días normales (15 horas)
            $horas_disponibles_tipo = 0;
            for ($fecha = $inicioMes->copy(); $fecha->lte($finMes); $fecha->addDay()) {
                if ($fecha->isWeekday() || $fecha->isSaturday()) {
                    $horas_disponibles_tipo += $total_espacios_tipo * $this->horasPorTurno(null, $fecha);
                }
            }
            
            // Calcular porcentaje real basado en horas utilizadas vs disponibles
            $promedio = $horas_disponibles_tipo > 0 ? 
                round(($horas_utilizadas / $horas_disponibles_tipo) * 100) : 0;
                
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
                        $horas_disponibles_turno += $total_espacios_tipo * $this->horasPorTurno($turno, $fecha);
                    }
                }
                
                // Calcular horas desde planificaciones para este turno
                $horas_plan_turno = 0;
                $planificaciones_tipo = $planificaciones->filter(function($plan) use ($tipo) {
                    return $plan->espacio && $plan->espacio->tipo_espacio === $tipo;
                });
                
                for ($fecha = $inicioMes->copy(); $fecha->lte($finMes); $fecha->addDay()) {
                    if (!$fecha->isWeekday() && !$fecha->isSaturday()) continue;
                    
                    $diaSemana = strtolower($fecha->locale('es')->isoFormat('dddd'));
                    $planificacionesDia = $planificaciones_tipo->filter(function($plan) use ($diaSemana) {
                        return $plan->modulo && strtolower($plan->modulo->dia) === $diaSemana;
                    });
                    
                    foreach ($planificacionesDia as $plan) {
                        if ($plan->modulo && $plan->modulo->hora_inicio && $plan->modulo->hora_termino) {
                            if ($this->esTurno($plan->modulo->hora_inicio, $turno)) {
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
                    ->filter(function($r) use ($turno) {
                        return $r->hora && $this->esTurno($r->hora, $turno);
                    });
                
                $horas_reservas_turno = $reservas_tipo_turno->sum(function($r) {
                    if ($r->hora && $r->hora_salida) {
                        return Carbon::parse($r->hora)->diffInHours(Carbon::parse($r->hora_salida), true);
                    }
                    return 0.83;
                });
                
                $horas_utilizadas_turno = $horas_plan_turno + $horas_reservas_turno;
                $promedio_turno = $horas_disponibles_turno > 0 ? 
                    round(($horas_utilizadas_turno / $horas_disponibles_turno) * 100) : 0;
                
                $estadisticasTurnos[$tipo][$turno] = [
                    'horas_utilizadas' => round($horas_utilizadas_turno),
                    'promedio' => $promedio_turno,
                    'total_reservas' => $reservas_tipo_turno->count()
                ];
            }
        }

        $diasDisponibles = ['lunes', 'martes', 'miercoles', 'jueves', 'viernes','sabado'];
        $tiposEspacioDisponibles = $tipos;
        $diaActual = strtolower(now()->locale('es')->isoFormat('dddd'));
        if (!in_array($diaActual, $diasDisponibles)) $diaActual = 'lunes';

        $ocupacionHorarios = [];
        foreach ($tiposEspacioDisponibles as $tipo) {
            foreach ($diasDisponibles as $dia) {
                for ($moduloNum = 1; $moduloNum <= 15; $moduloNum++) {
                    $totalEspacios = Espacio::where('tipo_espacio', $tipo)->count();
                    if ($totalEspacios === 0) {
                        $ocupacionHorarios[$tipo][$dia][$moduloNum] = 0;
                        continue;
                    }
                    $ocupados = Planificacion_Asignatura::where('id_modulo', $dia.'.'.$moduloNum)
                        ->whereHas('espacio', function($q) use ($tipo) {
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
        $espaciosQuery = Espacio::with(['piso.facultad', 'reservas' => function($query) use ($mes, $anio) {
            $query->whereMonth('fecha_reserva', $mes)
                  ->whereYear('fecha_reserva', $anio);
        }]);

        // Aplicar filtros solo si están presentes
        if (!empty($tipoEspacioFiltro)) {
            $espaciosQuery->where('tipo_espacio', $tipoEspacioFiltro);
        }
        if (!empty($pisoFiltro)) {
            $espaciosQuery->whereHas('piso', function($q) use ($pisoFiltro) {
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
        $total_reservas = $espacios->sum(function($espacio) {
            return $espacio->reservas->count();
        });

        // Calcular promedio de utilización basado en días laborales del mes
        $dias_laborales = $this->calcularDiasLaborales($anio, $mes);
        $horas_totales_disponibles = $total_espacios * $dias_laborales * 15; // 15 horas por día laboral
        
        // Calcular horas reales utilizadas
        $reservas_mes = Reserva::whereMonth('fecha_reserva', $mes)
            ->whereYear('fecha_reserva', $anio)
            ->get();
        
        $horas_utilizadas = $reservas_mes->sum(function($reserva) {
            if ($reserva->hora && $reserva->hora_salida) {
                $inicio = Carbon::parse($reserva->hora);
                $fin = Carbon::parse($reserva->hora_salida);
                return $inicio->diffInHours($fin, true); // true para incluir decimales
            }
            return 0.83; // Si no hay hora de salida, asumir 1 módulo de 50 minutos
        });
        
        // Calcular promedio de utilización basado en horas reales
        $promedio_utilizacion = $horas_totales_disponibles > 0 ? 
            round(($horas_utilizadas / $horas_totales_disponibles) * 100) : 0;

        // Calcular estadísticas detalladas por espacio
        $resumen = [];
        $labels_grafico = [];
        $data_grafico = [];
        $data_reservas_grafico = [];

        foreach ($espacios as $espacio) {
            $total_reservas_espacio = $espacio->reservas->count();
            $horas_utilizadas = $espacio->reservas->sum(function($reserva) {
                if ($reserva->hora && $reserva->hora_salida) {
                    $inicio = \Carbon\Carbon::parse($reserva->hora);
                    $fin = \Carbon\Carbon::parse($reserva->hora_salida);
                    return $inicio->diffInHours($fin, true); // true para incluir decimales
                }
                return 0.83; // Si no hay hora de salida, asumir 1 módulo de 50 minutos
            });
            
            // Calcular porcentaje de utilización basado en días con reservas
            $dias_con_reservas = $espacio->reservas->unique('fecha_reserva')->count();
            $porcentaje_utilizacion = $dias_laborales > 0 ? 
                round(($dias_con_reservas / $dias_laborales) * 100, 1) : 0;
            
            // Determinar estado de utilización
            $estado_utilizacion = $this->determinarEstadoUtilizacion($porcentaje_utilizacion);
            
            $resumen[] = [
                'id_espacio' => $espacio->id_espacio,
                'nombre' => $espacio->nombre_espacio,
                'tipo_espacio' => $espacio->tipo_espacio,
                'piso' => $espacio->piso ? $espacio->piso->numero_piso : 'N/A',
                'facultad' => $espacio->piso && $espacio->piso->facultad ? 
                    $espacio->piso->facultad->nombre_facultad : 'N/A',
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
        $pisosDisponibles = Piso::whereHas('facultad', function($q) {
            $q->where('id_facultad', 'IT_TH');
        })->orderBy('numero_piso')->pluck('numero_piso', 'numero_piso');
        $estadosDisponibles = ['Disponible', 'Ocupado', 'Mantenimiento'];

        // Configuración de horarios
        $diasDisponibles = ['lunes', 'martes', 'miercoles', 'jueves', 'viernes'];
        $diaActual = strtolower(now()->locale('es')->isoFormat('dddd'));
        if (!in_array($diaActual, $diasDisponibles)) $diaActual = 'lunes';

        // Calcular ocupación por horarios de forma más precisa
        $ocupacionHorarios = $this->calcularOcupacionHorarios($espacios, $mes, $anio, $diasDisponibles);

        // Obtener datos del histórico de reservas
        $fechaInicio = $request->get('fecha_inicio', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $fechaFin = $request->get('fecha_fin', Carbon::now()->endOfMonth()->format('Y-m-d'));
        
        // Obtener reservas del rango de fechas con información completa
        $reservasQuery = Reserva::with(['espacio.piso.facultad', 'profesor', 'solicitante', 'asignatura'])
            ->whereBetween('fecha_reserva', [$fechaInicio, $fechaFin])
            ->whereHas('espacio', function($q) use ($espacios) {
                $q->whereIn('id_espacio', $espacios->pluck('id_espacio'));
            });

        // Aplicar filtros al histórico
        if (!empty($tipoEspacioFiltro)) {
            $reservasQuery->whereHas('espacio', function($q) use ($tipoEspacioFiltro) {
                $q->where('tipo_espacio', $tipoEspacioFiltro);
            });
        }
        if (!empty($pisoFiltro)) {
            $reservasQuery->whereHas('espacio.piso', function($q) use ($pisoFiltro) {
                $q->where('numero_piso', $pisoFiltro);
            });
        }
        if (!empty($estadoFiltro)) {
            $reservasQuery->where('estado', $estadoFiltro);
        }
        if (!empty($busqueda)) {
            $reservasQuery->whereHas('espacio', function($q) use ($busqueda) {
                $q->where('nombre_espacio', 'like', '%' . $busqueda . '%');
            });
        }

        $reservas = $reservasQuery->orderBy('fecha_reserva', 'desc')
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
            
            $duracionFormateada = $duracionMinutos > 0 ? 
                (floor($duracionMinutos / 60) > 0 ? floor($duracionMinutos / 60) . 'h ' : '') . 
                ($duracionMinutos % 60) . ' min' : '0 min';

            $historico[] = [
                'id_reserva' => $reserva->id_reserva,
                'fecha' => Carbon::parse($reserva->fecha_reserva)->format('d/m/Y'),
                'hora_inicio' => $reserva->hora ? Carbon::parse($reserva->hora)->format('H:i') : 'N/A',
                'hora_fin' => $reserva->hora_salida ? Carbon::parse($reserva->hora_salida)->format('H:i') : 'N/A',
                'espacio' => $reserva->espacio->nombre_espacio . ' (' . $reserva->espacio->id_espacio . ')',
                'tipo_espacio' => $reserva->espacio->tipo_espacio,
                'piso' => $reserva->espacio->piso ? $reserva->espacio->piso->numero_piso : 'N/A',
                'facultad' => $reserva->espacio->piso && $reserva->espacio->piso->facultad ? 
                    $reserva->espacio->piso->facultad->nombre_facultad : 'N/A',
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
    private function calcularDiasLaborales($anio, $mes)
    {
        $fecha = \Carbon\Carbon::create($anio, $mes, 1);
        $diasEnMes = $fecha->daysInMonth;
        
        $diasLaborales = 0;
        for ($dia = 1; $dia <= $diasEnMes; $dia++) {
            $fechaDia = \Carbon\Carbon::create($anio, $mes, $dia);
            if ($fechaDia->isWeekday()) {
                $diasLaborales++;
            }
        }
        
        return $diasLaborales;
    }

    /**
     * Determinar estado de utilización basado en porcentaje
     */
    private function determinarEstadoUtilizacion($porcentaje)
    {
        if ($porcentaje >= 80) return 'Óptimo';
        if ($porcentaje >= 40) return 'Medio uso';
        return 'Bajo uso';
    }

    /**
     * Calcular ocupación por horarios por espacio individual
     */
    private function calcularOcupacionHorarios($espacios, $mes, $anio, $diasDisponibles)
    {
        $ocupacionHorarios = [];
        
        // Calcular ocupación por espacio individual
        foreach ($espacios as $espacio) {
            $espacioId = $espacio->id_espacio;
            $ocupacionHorarios[$espacioId] = [];
            
            foreach ($diasDisponibles as $dia) {
                $ocupacionHorarios[$espacioId][$dia] = [];
                
                // Inicializar todos los módulos en 0
                for ($moduloNum = 1; $moduloNum <= 15; $moduloNum++) {
                    $ocupacionHorarios[$espacioId][$dia][$moduloNum] = 0;
                }
                
                // Obtener reservas para este espacio específico en este día
                $reservasDelDia = Reserva::where('id_espacio', $espacioId)
                    ->whereMonth('fecha_reserva', $mes)
                    ->whereYear('fecha_reserva', $anio)
                    ->get()
                    ->filter(function($reserva) use ($dia) {
                        $diaSemana = strtolower(\Carbon\Carbon::parse($reserva->fecha_reserva)->locale('es')->isoFormat('dddd'));
                        return $diaSemana === $dia;
                    });
                
                // Contar reservas por módulo
                $ocupadosPorModulo = [];
                for ($moduloNum = 1; $moduloNum <= 15; $moduloNum++) {
                    $ocupadosPorModulo[$moduloNum] = 0;
                }
                
                foreach ($reservasDelDia as $reserva) {
                    if ($reserva->hora) {
                        $hora = \Carbon\Carbon::parse($reserva->hora);
                        $modulo = $this->obtenerModuloPorHora($hora->hour);
                        if (isset($ocupadosPorModulo[$modulo])) {
                            $ocupadosPorModulo[$modulo]++;
                        }
                    }
                }
                
                // Calcular porcentaje de ocupación por módulo (1 espacio = 100% si está ocupado)
                for ($moduloNum = 1; $moduloNum <= 15; $moduloNum++) {
                    $ocupacionHorarios[$espacioId][$dia][$moduloNum] = $ocupadosPorModulo[$moduloNum] > 0 ? 100 : 0;
                }
            }
        }
        
        return $ocupacionHorarios;
    }

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

        // Query base optimizada
        $espaciosQuery = Espacio::with(['piso.facultad']);

        // Aplicar filtros
        if (!empty($tipoEspacioFiltro)) {
            $espaciosQuery->where('tipo_espacio', $tipoEspacioFiltro);
        }
        if (!empty($pisoFiltro)) {
            $espaciosQuery->whereHas('piso', function($q) use ($pisoFiltro) {
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
        $espaciosIds = $espacios->pluck('id_espacio');

        // Días laborales simplificado
        $dias_laborales = collect(range(1, now()->daysInMonth))
            ->map(function($day) use ($anio, $mes) {
                return Carbon::create($anio, $mes, $day);
            })
            ->filter(function($date) {
                return $date->isWeekday();
            })->count();

        // Obtener estadísticas de reservas en una sola consulta
        $reservasStats = Reserva::whereIn('id_espacio', $espaciosIds)
            ->whereMonth('fecha_reserva', $mes)
            ->whereYear('fecha_reserva', $anio)
            ->selectRaw('
                id_espacio,
                COUNT(*) as total_reservas,
                COUNT(DISTINCT fecha_reserva) as dias_con_reservas,
                SUM(CASE WHEN hora IS NOT NULL AND hora_salida IS NOT NULL 
                    THEN TIMESTAMPDIFF(MINUTE, hora, hora_salida) ELSE 0 END) / 60 as horas_utilizadas
            ')
            ->groupBy('id_espacio')
            ->get()
            ->keyBy('id_espacio');

        // Calcular datos para exportación
        $datos = [];
        foreach ($espacios as $espacio) {
            $stats = $reservasStats->get($espacio->id_espacio);
            $total_reservas_espacio = $stats ? $stats->total_reservas : 0;
            $horas_utilizadas = $stats ? round($stats->horas_utilizadas) : 0;
            $dias_con_reservas = $stats ? $stats->dias_con_reservas : 0;
            
            $promedio = $dias_laborales > 0 ? round(($dias_con_reservas / $dias_laborales) * 100) : 0;
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
                'horas_utilizadas' => $horas_utilizadas,
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
                ->whereHas('espacio', function($q) use ($espacios) {
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
            
            $reservas = $reservasQuery->orderBy('fecha_reserva', 'desc')
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
                
                $duracionFormateada = $duracionMinutos > 0 ? 
                    (floor($duracionMinutos / 60) > 0 ? floor($duracionMinutos / 60) . 'h ' : '') . 
                    ($duracionMinutos % 60) . ' min' : '0 min';

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

    private function exportarHistoricoExcel($datos, $fechaInicio, $fechaFin)
    {
        try {
            $filename = 'historico_espacios_' . $fechaInicio . '_' . $fechaFin . '.xlsx';
            
            return Excel::download(new class($datos) implements \Maatwebsite\Excel\Concerns\FromArray, \Maatwebsite\Excel\Concerns\WithHeadings, \Maatwebsite\Excel\Concerns\WithStyles {
                private $datos;
                
                public function __construct($datos) {
                    $this->datos = $datos;
                }
                
                public function array(): array {
                    return $this->datos;
                }
                
                public function headings(): array {
                    return [
                        'Fecha',
                        'Hora Inicio',
                        'Hora Fin',
                        'Espacio',
                        'Usuario',
                        'Tipo Usuario',
                        'Horas Utilizadas',
                        'Duración',
                        'Estado'
                    ];
                }
                
                public function styles(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet) {
                    return [
                        1 => ['font' => ['bold' => true]],
                    ];
                }
            }, $filename);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error al exportar a Excel: ' . $e->getMessage());
        }
    }

    private function exportarHistoricoPDF($datos, $fechaInicio, $fechaFin, $piso = '', $tipoUsuario = '', $tipoEspacioFiltro = '')
    {
        try {
            // Calcular resumen
            $total = count($datos);
            $completadas = collect($datos)->where('estado', 'Finalizada')->count();
            $canceladas = collect($datos)->where('estado', 'Cancelada')->count();
            $enProgreso = collect($datos)->where('estado', 'En progreso')->count();
            $activas = collect($datos)->where('estado', 'Activa')->count();

            $data = [
                'datos' => $datos,
                'fecha_inicio' => Carbon::parse($fechaInicio)->format('d/m/Y'),
                'fecha_fin' => Carbon::parse($fechaFin)->format('d/m/Y'),
                'fecha_generacion' => Carbon::now()->format('d/m/Y H:i:s'),
                'total_registros' => $total,
                'filtros_aplicados' => [
                    'tipo_espacio' => $tipoEspacioFiltro,
                    'piso' => $piso,
                    'estado' => '',
                    'busqueda' => ''
                ],
                'resumen' => [
                    'total' => $total,
                    'completadas' => $completadas,
                    'canceladas' => $canceladas,
                    'en_progreso' => $enProgreso + $activas
                ]
            ];

            $filename = 'historico_espacios_' . $fechaInicio . '_' . $fechaFin . '.pdf';
            $pdf = Pdf::loadView('reportes.pdf.historico-espacios', $data);
            return $pdf->download($filename);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error al exportar a PDF: ' . $e->getMessage());
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
        $piso = $request->get('piso', ''); // Valor vacío por defecto
        $tipoUsuario = $request->get('tipo_usuario', ''); // Valor vacío por defecto
        $espacio = $request->get('espacio', ''); // Valor vacío por defecto

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


    
    /**
     * Identificar problemas específicos de una área académica
     */


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
                    'incidencias' => [] // Optimización: evitar consultas adicionales
                ]);
            }
        });

        return $accesos;
    }

    /**
     * Determinar el tipo de usuario basado en los campos del modelo
     */
    private function determinarTipoUsuario($profesor)
    {
        if (!$profesor) {
            return 'externo';
        }

        if ($profesor->tipo_profesor) {
            return 'profesor';
        }

        return 'externo';
    }

    /**
     * Calcular duración de la reserva
     */
    private function calcularDuracion($horaEntrada, $horaSalida)
    {
        if (!$horaSalida) {
            return 'En curso';
        }

        $entrada = Carbon::parse($horaEntrada);
        $salida = Carbon::parse($horaSalida);
        $duracion = $entrada->diffInMinutes($salida);

        if ($duracion < 60) {
            return $duracion . ' min';
        } else {
            $horas = floor($duracion / 60);
            $minutos = $duracion % 60;
            return $horas . 'h ' . $minutos . 'min';
        }
    }

    /**
     * Obtener incidencias de la reserva
     */
    private function obtenerIncidencias($idReserva)
    {
        // Aquí puedes implementar la lógica para obtener incidencias
        // Por ahora retornamos un array vacío
        return [];
    }

    /**
     * Obtener pisos disponibles (con caché)
     */
    private function obtenerPisosDisponibles()
    {
        return Cache::remember('reportes.pisos_disponibles', 3600, function () {
            return Piso::whereHas('facultad', function ($query) {
                $query->where('id_facultad', 'IT_TH');
            })
            ->orderBy('numero_piso')
            ->pluck('numero_piso', 'numero_piso');
        });
    }

    /**
     * Obtener espacios disponibles (con caché)
     */
    private function obtenerEspaciosDisponibles()
    {
        return Cache::remember('reportes.espacios_disponibles', 3600, function () {
            return Espacio::whereHas('piso.facultad', function ($query) {
                $query->where('id_facultad', 'IT_TH');
            })
            ->orderBy('nombre_espacio')
            ->pluck('nombre_espacio', 'nombre_espacio');
        });
    }

    /**
     * Obtener tipos de usuario
     */
    private function obtenerTiposUsuario()
    {
        return [
            'profesor' => 'Profesor',
            'solicitante' => 'Solicitante',
            'estudiante' => 'Estudiante',
            'administrativo' => 'Personal Administrativo'
        ];
    }

    /**
     * Exportar accesos a Excel
     */
    private function exportarAccesosExcel($accesos)
    {
        try {
            // Obtener código de espacio
            $codigoEspacio = $accesos->first()['id_espacio'] ?? 'sin_codigo';
            // Obtener año y semestre usando el helper
            $anio = SemesterHelper::getCurrentAcademicYear();
            $semestre = SemesterHelper::getCurrentSemester();
            $filename = 'accesos_registrados_' . $codigoEspacio . '_' . $anio . '_semestre_' . $semestre . '.xlsx';
            return Excel::download(new AccesosExport($accesos), $filename);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error al exportar a Excel: ' . $e->getMessage());
        }
    }

    /**
     * Exportar accesos a PDF
     */
    private function exportarAccesosPDF($accesos)
    {
        try {
            $data = [
                'accesos' => $accesos,
                'fecha_generacion' => Carbon::now()->format('d/m/Y H:i:s'),
                'total_accesos' => $accesos->count(),
                'usuarios_unicos' => $accesos->unique('run')->count(),
                'espacios_utilizados' => $accesos->unique('espacio')->count(),
                'en_curso' => $accesos->where('hora_salida', 'En curso')->count()
            ];

            // Obtener código de espacio
            $codigoEspacio = $accesos->first()['id_espacio'] ?? 'sin_codigo';
            // Obtener año y semestre usando el helper
            $anio = SemesterHelper::getCurrentAcademicYear();
            $semestre = SemesterHelper::getCurrentSemester();
            $filename = 'accesos_registrados_' . $codigoEspacio . '_' . $anio . '_semestre_' . $semestre . '.pdf';
            $pdf = Pdf::loadView('reportes.pdf.accesos', $data);
            return $pdf->download($filename);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error al exportar a PDF: ' . $e->getMessage());
        }
    }

    /**
     * Obtener tipos de espacio disponibles
     */
    private function obtenerTiposEspacioDisponibles()
    {
        return Espacio::select('tipo_espacio')
            ->distinct()
            ->orderBy('tipo_espacio')
            ->pluck('tipo_espacio', 'tipo_espacio');
    }

    /**
     * Obtener días disponibles
     */
    private function obtenerDiasDisponibles()
    {
        return [
            'lunes' => 'Lunes',
            'martes' => 'Martes',
            'miercoles' => 'Miércoles',
            'jueves' => 'Jueves',
            'viernes' => 'Viernes',
            'sabado' => 'Sábado'
        ];
    }

 
    private function generarDatosOcupacionHorarios($fechaInicio, $fechaFin, $piso = null, $tipoUsuario = null, $tipoEspacioFiltro = null, $diaFiltro = null)
    {
        $modulosHorarios = [
            1 => ['inicio' => '08:10', 'fin' => '09:00'],
            2 => ['inicio' => '09:10', 'fin' => '10:00'],
            3 => ['inicio' => '10:10', 'fin' => '11:00'],
            4 => ['inicio' => '11:10', 'fin' => '12:00'],
            5 => ['inicio' => '12:10', 'fin' => '13:00'],
            6 => ['inicio' => '13:10', 'fin' => '14:00'],
            7 => ['inicio' => '14:10', 'fin' => '15:00'],
            8 => ['inicio' => '15:10', 'fin' => '16:00'],
            9 => ['inicio' => '16:10', 'fin' => '17:00'],
            10 => ['inicio' => '17:10', 'fin' => '18:00'],
            11 => ['inicio' => '18:10', 'fin' => '19:00'],
            12 => ['inicio' => '19:10', 'fin' => '20:00'],
            13 => ['inicio' => '20:10', 'fin' => '21:00'],
            14 => ['inicio' => '21:10', 'fin' => '22:00'],
            15 => ['inicio' => '22:10', 'fin' => '23:00']
        ];

        $dias = ['lunes', 'martes', 'miercoles', 'jueves', 'viernes', 'sabado'];
        
        // Aplicar filtro de día si está especificado
        if (!empty($diaFiltro) && in_array($diaFiltro, $dias)) {
            $dias = [$diaFiltro];
        }

        // Obtener tipos de espacio
        $tiposQuery = Espacio::query();
        if (!empty($piso)) {
            $tiposQuery->whereHas('piso', function ($q) use ($piso) {
                $q->where('numero_piso', $piso);
            });
        }
        $tiposEspacio = $tiposQuery->distinct()->pluck('tipo_espacio');

        // Aplicar filtro de tipo de espacio si está especificado
        if (!empty($tipoEspacioFiltro) && in_array($tipoEspacioFiltro, $tiposEspacio->toArray())) {
            $tiposEspacio = collect([$tipoEspacioFiltro]);
        }

        $ocupacionHorarios = [];

        foreach ($tiposEspacio as $tipo) {
            $ocupacionHorarios[$tipo] = [];
            
            foreach ($dias as $dia) {
                $ocupacionHorarios[$tipo][$dia] = [];
                
                foreach ($modulosHorarios as $moduloNum => $horario) {
                    // Contar reservas para este tipo de espacio, día y módulo
                    $reservasQuery = Reserva::whereBetween('fecha_reserva', [$fechaInicio, $fechaFin])
                        ->where('estado', 'activa')
                        ->whereRaw('DAYOFWEEK(fecha_reserva) = ?', [$this->obtenerNumeroDia($dia)])
                        ->whereTime('hora', '>=', $horario['inicio'])
                        ->whereTime('hora', '<', $horario['fin'])
                        ->whereHas('espacio', function ($q) use ($tipo, $piso) {
                            $q->where('tipo_espacio', $tipo);
                            if (!empty($piso)) {
                                $q->whereHas('piso', function ($q2) use ($piso) {
                                    $q2->where('numero_piso', $piso);
                                });
                            }
                        });

                    if (!empty($tipoUsuario)) {
                        $reservasQuery->whereHas('user', function ($q) use ($tipoUsuario) {
                            if ($tipoUsuario === 'profesor') {
                                $q->whereNotNull('tipo_profesor');
                            } elseif ($tipoUsuario === 'estudiante') {
                                $q->whereNull('tipo_profesor')->whereNotNull('id_carrera');
                            } elseif ($tipoUsuario === 'administrativo') {
                                $q->whereNull('tipo_profesor')->whereNull('id_carrera')->whereNotNull('id_facultad');
                            } else {
                                $q->whereNull('tipo_profesor')->whereNull('id_carrera')->whereNull('id_facultad');
                            }
                        });
                    }

                    $totalReservas = $reservasQuery->count();
                    
                    // Calcular capacidad máxima para este módulo
                    $espaciosDelTipo = Espacio::where('tipo_espacio', $tipo);
                    if (!empty($piso)) {
                        $espaciosDelTipo->whereHas('piso', function ($q) use ($piso) {
                            $q->where('numero_piso', $piso);
                        });
                    }
                    $totalEspacios = $espaciosDelTipo->count();
                    
                    // Calcular porcentaje de ocupación
                    $porcentajeOcupacion = $totalEspacios > 0 ? round(($totalReservas / $totalEspacios) * 100) : 0;
                    
                    $ocupacionHorarios[$tipo][$dia][$moduloNum] = $porcentajeOcupacion;
                }
            }
        }

        return $ocupacionHorarios;
    }

    /**
     * Calcular horarios pico basados en los datos de ocupación
     */
    private function calcularHorariosPico($ocupacionHorarios)
    {
        $modulosPico = [
            1 => '08:10-09:00',
            2 => '09:10-10:00',
            3 => '10:10-11:00',
            4 => '11:10-12:00',
            5 => '12:10-13:00',
            6 => '13:10-14:00',
            7 => '14:10-15:00',
            8 => '15:10-16:00',
            9 => '16:10-17:00',
            10 => '17:10-18:00',
            11 => '18:10-19:00',
            12 => '19:10-20:00',
            13 => '20:10-21:00',
            14 => '21:10-22:00',
            15 => '22:10-23:00'
        ];

        $horariosPico = [];
        $promediosModulos = [];

        // Calcular promedio de ocupación por módulo
        foreach ($modulosPico as $moduloNum => $horario) {
            $sumaOcupacion = 0;
            $contador = 0;
            
            foreach ($ocupacionHorarios as $tipo => $dias) {
                foreach ($dias as $dia => $modulosData) {
                    if (isset($modulosData[$moduloNum])) {
                        $sumaOcupacion += $modulosData[$moduloNum];
                        $contador++;
                    }
                }
            }
            
            $promedio = $contador > 0 ? $sumaOcupacion / $contador : 0;
            $promediosModulos[$moduloNum] = [
                'horario' => $horario,
                'promedio' => $promedio
            ];
        }

        // Ordenar por promedio de ocupación (descendente)
        uasort($promediosModulos, function($a, $b) {
            return $b['promedio'] <=> $a['promedio'];
        });

        // Tomar los 3 horarios con mayor ocupación
        $contador = 0;
        foreach ($promediosModulos as $moduloNum => $data) {
            if ($contador >= 3) break;
            
            $nivelDemanda = 'Baja demanda';
            $colorClase = 'bg-[#E5FFF2] text-[#05CD99]';
            
            if ($data['promedio'] >= 80) {
                $nivelDemanda = 'Alta demanda';
                $colorClase = 'bg-[#FFE5E5] text-[#F97E5E]';
            } elseif ($data['promedio'] >= 40) {
                $nivelDemanda = 'Media demanda';
                $colorClase = 'bg-[#FFF7E5] text-[#F7B267]';
            }
            
            $horariosPico[] = [
                'horario' => $data['horario'],
                'nivel_demanda' => $nivelDemanda,
                'color_clase' => $colorClase,
                'porcentaje' => round($data['promedio'], 1)
            ];
            
            $contador++;
        }

        return $horariosPico;
    }

    /**
     * Obtener número de día de la semana para MySQL
     */
    private function obtenerNumeroDia($dia)
    {
        $dias = [
            'lunes' => 2,
            'martes' => 3,
            'miercoles' => 4,
            'jueves' => 5,
            'viernes' => 6,
            'sabado' => 7,
            'domingo' => 1
        ];
        
        return $dias[$dia] ?? 1;
    }

    /**
     * Obtener hora de inicio o fin de un módulo
     */
    private function obtenerHoraModulo($moduloNum, $tipo = 'inicio')
    {
        $modulosHorarios = [
            1 => ['inicio' => '08:10', 'fin' => '09:00'],
            2 => ['inicio' => '09:10', 'fin' => '10:00'],
            3 => ['inicio' => '10:10', 'fin' => '11:00'],
            4 => ['inicio' => '11:10', 'fin' => '12:00'],
            5 => ['inicio' => '12:10', 'fin' => '13:00'],
            6 => ['inicio' => '13:10', 'fin' => '14:00'],
            7 => ['inicio' => '14:10', 'fin' => '15:00'],
            8 => ['inicio' => '15:10', 'fin' => '16:00'],
            9 => ['inicio' => '16:10', 'fin' => '17:00'],
            10 => ['inicio' => '17:10', 'fin' => '18:00'],
            11 => ['inicio' => '18:10', 'fin' => '19:00'],
            12 => ['inicio' => '19:10', 'fin' => '20:00'],
            13 => ['inicio' => '20:10', 'fin' => '21:00'],
            14 => ['inicio' => '21:10', 'fin' => '22:00'],
            15 => ['inicio' => '22:10', 'fin' => '23:00']
        ];
        
        return $modulosHorarios[$moduloNum][$tipo] ?? '00:00';
    }

    /**
     * Obtener módulo por hora
     */
    private function obtenerModuloPorHora($hora)
    {
        $horaInt = (int)$hora;
        
        if ($horaInt >= 8 && $horaInt < 9) return 1;
        if ($horaInt >= 9 && $horaInt < 10) return 2;
        if ($horaInt >= 10 && $horaInt < 11) return 3;
        if ($horaInt >= 11 && $horaInt < 12) return 4;
        if ($horaInt >= 12 && $horaInt < 13) return 5;
        if ($horaInt >= 13 && $horaInt < 14) return 6;
        if ($horaInt >= 14 && $horaInt < 15) return 7;
        if ($horaInt >= 15 && $horaInt < 16) return 8;
        if ($horaInt >= 16 && $horaInt < 17) return 9;
        if ($horaInt >= 17 && $horaInt < 18) return 10;
        if ($horaInt >= 18 && $horaInt < 19) return 11;
        if ($horaInt >= 19 && $horaInt < 20) return 12;
        if ($horaInt >= 20 && $horaInt < 21) return 13;
        if ($horaInt >= 21 && $horaInt < 22) return 14;
        if ($horaInt >= 22 && $horaInt < 23) return 15;
        
        return 1; // Por defecto
    }



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
                $espaciosQuery->whereHas('piso', function($q) use ($piso) {
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

    private function exportarHorariosExcel($datos, $fecha, $moduloInicio, $moduloFin, $modulosDia)
    {
        $filename = 'ocupacion_horarios_' . $fecha . '_modulos_' . ($moduloInicio + 1) . '_' . ($moduloFin + 1) . '.xlsx';

        return Excel::download(new class($datos, $moduloInicio, $moduloFin, $modulosDia) implements \Maatwebsite\Excel\Concerns\FromArray, \Maatwebsite\Excel\Concerns\WithHeadings, \Maatwebsite\Excel\Concerns\WithStyles {
            private $datos;
            private $moduloInicio;
            private $moduloFin;
            private $modulosDia;

            public function __construct($datos, $moduloInicio, $moduloFin, $modulosDia) {
                $this->datos = $datos;
                $this->moduloInicio = $moduloInicio;
                $this->moduloFin = $moduloFin;
                $this->modulosDia = $modulosDia;
            }

            public function array(): array {
                return $this->datos;
            }

            public function headings(): array {
                $headers = ['Espacio', 'Tipo', 'Piso', 'Facultad'];
                
                for ($i = $this->moduloInicio; $i <= $this->moduloFin; $i++) {
                    $headers[] = 'Módulo ' . ($i + 1) . ' (' . $this->modulosDia[$i] . ')';
                }
                
                return $headers;
            }

            public function styles(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet) {
                return [
                    1 => ['font' => ['bold' => true]],
                ];
            }
        }, $filename);
    }

    private function exportarHorariosPDF($datos, $fecha, $moduloInicio, $moduloFin, $modulosDia)
    {
        $data = [
            'datos' => $datos,
            'fecha' => Carbon::parse($fecha)->format('d/m/Y'),
            'moduloInicio' => $moduloInicio + 1,
            'moduloFin' => $moduloFin + 1,
            'modulosDia' => $modulosDia,
            'fecha_generacion' => Carbon::now()->format('d/m/Y H:i:s'),
            'total_registros' => count($datos)
        ];

        $filename = 'ocupacion_horarios_' . $fecha . '_modulos_' . ($moduloInicio + 1) . '_' . ($moduloFin + 1) . '.pdf';
        $pdf = Pdf::loadView('reportes.pdf.horarios', $data);
        return $pdf->download($filename);
    }

    private function exportarResumenExcel($datos)
    {
        $filename = 'analisis_espacios_' . date('Y-m-d_H-i-s') . '.xlsx';

        return Excel::download(new class($datos) implements \Maatwebsite\Excel\Concerns\FromArray, \Maatwebsite\Excel\Concerns\WithHeadings, \Maatwebsite\Excel\Concerns\WithStyles {
            private $datos;
            
            public function __construct($datos) {
                $this->datos = $datos;
            }
            
            public function array(): array {
                return $this->datos;
            }
            
            public function headings(): array {
                return [
                    'ID Espacio',
                    'Nombre',
                    'Tipo de Espacio',
                    'Piso',
                    'Facultad',
                    'Estado',
                    'Puestos Disponibles',
                    'Total Reservas',
                    'Horas Utilizadas',
                    'Promedio Utilización',
                    'Estado Utilización'
                ];
            }
            
            public function styles(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet) {
                return [
                    1 => ['font' => ['bold' => true]],
                ];
            }
        }, $filename);
    }

    private function exportarResumenPDF($datos, $tipoEspacioFiltro = '', $pisoFiltro = '', $estadoFiltro = '', $busqueda = '')
    {
        $data = [
            'datos' => $datos,
            'fecha_generacion' => Carbon::now()->format('d/m/Y H:i:s'),
            'periodo' => Carbon::now()->format('m/Y'),
            'total_registros' => count($datos),
            'filtros_aplicados' => [
                'tipo_espacio' => $tipoEspacioFiltro,
                'piso' => $pisoFiltro,
                'estado' => $estadoFiltro,
                'busqueda' => $busqueda
            ]
        ];

        $filename = 'analisis_espacios_' . date('Y-m-d_H-i-s') . '.pdf';
        $pdf = Pdf::loadView('reportes.pdf.espacios', $data);
        return $pdf->download($filename);
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
                $query->whereHas('espacio', function($q) use ($request) {
                    $q->where('tipo_espacio', $request->tipo_espacio);
                });
            }

            // Obtener datos paginados
            $reservas = $query->orderBy('fecha_reserva', 'desc')
                ->orderBy('hora', 'asc')
                ->paginate(15);
                


            // Calcular KPIs con estados correctos
            $total = $query->count();
            $finalizadas = $query->where('estado', 'finalizada')->count();
            $activas = $query->where('estado', 'activa')->count();

            // Formatear datos para la respuesta
            $data = $reservas->getCollection()->map(function($reserva) {
                
                
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
                    $inicio = \Carbon\Carbon::parse($reserva->hora);
                    $fin = \Carbon\Carbon::parse($reserva->hora_salida);
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
                    $horaSalida = \Carbon\Carbon::parse($reserva->hora_salida)->format('H:i:s');
                } elseif ($reserva->estado === 'activa') {
                    $horaSalida = 'En curso';
                }

                return [
                    'profesor_solicitante' => $usuario,
                    'run' => $run,
                    'email' => $email,
                    'espacio' => ($reserva->espacio->nombre_espacio ?? 'N/A') . ' (' . ($reserva->espacio->id_espacio ?? 'N/A') . ', Piso ' . ($reserva->espacio->piso->numero_piso ?? 'N/A') . ')',
                    'facultad' => $reserva->espacio->piso->facultad->nombre_facultad ?? 'N/A',
                    'fecha' => \Carbon\Carbon::parse($reserva->fecha_reserva)->format('d/m/Y'),
                    'hora_inicio' => $reserva->hora ? \Carbon\Carbon::parse($reserva->hora)->format('H:i:s') : 'N/A',
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
                $query->whereHas('espacio', function($q) use ($tipoEspacio) {
                    $q->where('tipo_espacio', $tipoEspacio);
                });
            }

            $reservas = $query->orderBy('fecha_reserva', 'desc')
                ->orderBy('hora', 'asc')
                ->get();

            // Formatear datos para exportación
            $datos = $reservas->map(function($reserva) {
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
                    $inicio = \Carbon\Carbon::parse($reserva->hora);
                    $fin = \Carbon\Carbon::parse($reserva->hora_salida);
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
                    $horaSalida = \Carbon\Carbon::parse($reserva->hora_salida)->format('H:i:s');
                } elseif ($reserva->estado === 'activa') {
                    $horaSalida = 'En curso';
                }

                return [
                    'profesor_solicitante' => $usuario,
                    'run' => $run,
                    'email' => $email,
                    'espacio' => ($reserva->espacio->nombre_espacio ?? 'N/A') . ' (' . ($reserva->espacio->id_espacio ?? 'N/A') . ', Piso ' . ($reserva->espacio->piso->numero_piso ?? 'N/A') . ')',
                    'facultad' => $reserva->espacio->piso->facultad->nombre_facultad ?? 'N/A',
                    'fecha' => \Carbon\Carbon::parse($reserva->fecha_reserva)->format('d/m/Y'),
                    'hora_inicio' => $reserva->hora ? \Carbon\Carbon::parse($reserva->hora)->format('H:i:s') : 'N/A',
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
    private function exportarHistoricoTipoEspacioExcel($datos, $fechaInicio, $fechaFin, $tipoEspacio)
    {
        try {
            $filename = 'historico_tipo_espacio_' . date('Y-m-d_H-i-s') . '.xlsx';
            
            return Excel::download(new class($datos, $fechaInicio, $fechaFin, $tipoEspacio) implements \Maatwebsite\Excel\Concerns\FromArray, \Maatwebsite\Excel\Concerns\WithHeadings, \Maatwebsite\Excel\Concerns\WithStyles {
                private $datos;
                private $fechaInicio;
                private $fechaFin;
                private $tipoEspacio;

                public function __construct($datos, $fechaInicio, $fechaFin, $tipoEspacio) {
                    $this->datos = $datos;
                    $this->fechaInicio = $fechaInicio;
                    $this->fechaFin = $fechaFin;
                    $this->tipoEspacio = $tipoEspacio;
                }

                public function array(): array {
                    return $this->datos->toArray();
                }

                public function headings(): array {
                    return [
                        'Profesor/Solicitante',
                        'RUN',
                        'Email',
                        'Espacio',
                        'Facultad',
                        'Fecha',
                        'Hora Entrada',
                        'Hora Salida',
                        'Duración',
                        'Tipo Usuario',
                        'Estado'
                    ];
                }

                public function styles(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet) {
                    $sheet->getStyle('A1:K1')->getFont()->setBold(true);
                    $sheet->getStyle('A1:K1')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setRGB('E2E8F0');
                    
                    foreach (range('A', 'K') as $col) {
                        $sheet->getColumnDimension($col)->setAutoSize(true);
                    }
                    
                    return $sheet;
                }
            }, $filename);

        } catch (\Exception $e) {
            \Log::error('Error al exportar a Excel: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Exportar histórico a PDF
     */
    private function exportarHistoricoTipoEspacioPDF($datos, $fechaInicio, $fechaFin, $tipoEspacio, $total_reservas, $completadas,$canceladas,$en_progreso)
    {
        try {
            $data = [
                'datos' => $datos,
                'fecha_generacion' => Carbon::now()->format('d/m/Y H:i:s'),
                'fecha_inicio' => Carbon::parse($fechaInicio)->format('d/m/Y'),
                'fecha_fin' => Carbon::parse($fechaFin)->format('d/m/Y'),
                'tipo_espacio' => $tipoEspacio ?: 'Todos',
                'total_registros' => $total_reservas,
                'total_reservas' => $total_reservas,
                'completadas' => $completadas,
                'canceladas' => $canceladas,
                'en_progreso' => $en_progreso
            ];

            $filename = 'historico_tipo_espacio_' . date('Y-m-d_H-i-s') . '.pdf';
            $pdf = Pdf::loadView('reportes.pdf.historico-tipo-espacio', $data);
            return $pdf->download($filename);

        } catch (\Exception $e) {
            \Log::error('Error al exportar a PDF: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Obtener el nombre del usuario (profesor o solicitante) de una reserva
     */
    private function obtenerNombreUsuario($reserva)
    {
        if ($reserva->profesor) {
            return $reserva->profesor->name ?? 'Profesor no encontrado';
        } elseif ($reserva->solicitante) {
            return $reserva->solicitante->nombre ?? 'Solicitante no encontrado';
        }
        return 'Usuario no encontrado';
    }

    /**
     * Obtener el tipo de usuario (profesor o solicitante) de una reserva
     */
    private function obtenerTipoUsuario($reserva)
    {
        if ($reserva->profesor) {
            return 'Profesor';
        } elseif ($reserva->solicitante) {
            return ucfirst($reserva->solicitante->tipo_solicitante ?? 'Solicitante');
        }
        return 'N/A';
    }

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
            ->whereHas('espacio', function($q) {
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
        $totalGrupos = collect($gruposPorSala)->sum(function($item) {
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
    private function agruparReservasPorSesion($reservas)
    {
        $grupos = [];
        $grupoActual = [];
        $horaFinGrupo = null;

        foreach ($reservas as $reserva) {
            // Asegurar que fecha_reserva es un objeto Carbon
            $fechaReserva = $reserva->fecha_reserva instanceof \Carbon\Carbon 
                ? $reserva->fecha_reserva 
                : Carbon::parse($reserva->fecha_reserva);
            
            $horaEntrada = Carbon::parse($fechaReserva->format('Y-m-d') . ' ' . $reserva->hora);
            $horaSalida = $reserva->hora_salida 
                ? Carbon::parse($fechaReserva->format('Y-m-d') . ' ' . $reserva->hora_salida)
                : $horaEntrada->copy()->addHours(2);

            // Si el grupo está vacío o hay solapamiento, agregar al grupo actual
            if (empty($grupoActual) || ($horaFinGrupo && $horaEntrada->lte($horaFinGrupo))) {
                $grupoActual[] = $reserva;
                
                // Actualizar hora fin del grupo (la más tardía)
                if (!$horaFinGrupo || $horaSalida->gt($horaFinGrupo)) {
                    $horaFinGrupo = $horaSalida;
                }
            } else {
                // No hay solapamiento, guardar grupo anterior y crear uno nuevo
                if (count($grupoActual) > 0) {
                    $primeraReserva = $grupoActual[0];
                    $primeraFecha = $primeraReserva->fecha_reserva instanceof \Carbon\Carbon 
                        ? $primeraReserva->fecha_reserva 
                        : Carbon::parse($primeraReserva->fecha_reserva);
                    
                    $grupos[] = [
                        'reservas' => $grupoActual,
                        'hora_inicio' => Carbon::parse($primeraFecha->format('Y-m-d') . ' ' . $primeraReserva->hora),
                        'hora_fin' => $horaFinGrupo,
                        'fecha' => $primeraFecha
                    ];
                }
                
                $grupoActual = [$reserva];
                $horaFinGrupo = $horaSalida;
            }
        }

        // Agregar el último grupo
        if (count($grupoActual) > 0) {
            $primeraReserva = $grupoActual[0];
            $primeraFecha = $primeraReserva->fecha_reserva instanceof \Carbon\Carbon 
                ? $primeraReserva->fecha_reserva 
                : Carbon::parse($primeraReserva->fecha_reserva);
            
            $grupos[] = [
                'reservas' => $grupoActual,
                'hora_inicio' => Carbon::parse($primeraFecha->format('Y-m-d') . ' ' . $primeraReserva->hora),
                'hora_fin' => $horaFinGrupo,
                'fecha' => $primeraFecha
            ];
        }

        return $grupos;
    }

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
            ->whereHas('espacio', function($q) {
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
}