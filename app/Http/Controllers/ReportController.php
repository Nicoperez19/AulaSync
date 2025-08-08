<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
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

        $modulos_posibles = $total_espacios * $dias_laborales * 15;
        $modulos_reservados = Reserva::whereMonth('fecha_reserva', $mes)
            ->whereYear('fecha_reserva', $anio)
            ->count();
        $promedio_utilizacion = $modulos_posibles > 0 ? round(($modulos_reservados / $modulos_posibles) * 100) : 0;

        $tipos = Espacio::distinct()->pluck('tipo_espacio');
        $resumen = [];
        $labels_grafico = [];
        $data_grafico = [];
        $data_reservas_grafico = [];
        foreach ($tipos as $tipo) {
            $espacios = Espacio::where('tipo_espacio', $tipo)->pluck('id_espacio');
            $total_espacios_tipo = $espacios->count();
            $reservas_tipo = Reserva::whereIn('id_espacio', $espacios)
                ->whereMonth('fecha_reserva', $mes)
                ->whereYear('fecha_reserva', $anio)
                ->get();
            $total_reservas_tipo = $reservas_tipo->count();
            $horas_utilizadas = $reservas_tipo->sum(function($r) {
                return $r->hora && $r->hora_salida ? Carbon::parse($r->hora)->diffInMinutes(Carbon::parse($r->hora_salida))/60 : 0;
            });
            
            $espacios_con_reservas = Reserva::whereIn('id_espacio', $espacios)
                ->whereMonth('fecha_reserva', $mes)
                ->whereYear('fecha_reserva', $anio)
                ->distinct('id_espacio')
                ->count('id_espacio');
            
            $promedio = $total_espacios_tipo > 0 ? round(($espacios_con_reservas / $total_espacios_tipo) * 100) : 0;
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
            'ocupacionHorarios'
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
        $horas_totales_disponibles = $total_espacios * $dias_laborales * 15; // 15 módulos por día
        $promedio_utilizacion = $horas_totales_disponibles > 0 ? 
            round(($total_reservas / $horas_totales_disponibles) * 100, 1) : 0;

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
                    return $inicio->diffInHours($fin);
                }
                return 1; // Si no hay hora de salida, asumir 1 hora
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
        $fechaInicio = Carbon::now()->startOfMonth()->format('Y-m-d');
        $fechaFin = Carbon::now()->endOfMonth()->format('Y-m-d');
        
        // Obtener reservas del mes actual con información completa
        $reservasQuery = Reserva::with(['espacio.piso.facultad', 'user'])
            ->whereBetween('fecha_reserva', [$fechaInicio, $fechaFin])
            ->where('estado', 'activa')
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
                'usuario' => $reserva->user->name ?? 'Usuario no encontrado',
                'tipo_usuario' => ucfirst($this->determinarTipoUsuario($reserva->user)),
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
     * Calcular ocupación por horarios de forma optimizada
     */
    private function calcularOcupacionHorarios($espacios, $mes, $anio, $diasDisponibles)
    {
        $ocupacionHorarios = [];
        
        // Obtener todas las reservas del mes de una vez
        $espaciosIds = $espacios->pluck('id_espacio');
        $reservas = Reserva::whereIn('id_espacio', $espaciosIds)
            ->whereMonth('fecha_reserva', $mes)
            ->whereYear('fecha_reserva', $anio)
            ->get();

        foreach ($espacios as $espacio) {
            $ocupacionHorarios[$espacio->id_espacio] = [];
            
            foreach ($diasDisponibles as $dia) {
                $ocupacionHorarios[$espacio->id_espacio][$dia] = [];
                
                // Inicializar todos los módulos en 0
                for ($moduloNum = 1; $moduloNum <= 15; $moduloNum++) {
                    $ocupacionHorarios[$espacio->id_espacio][$dia][$moduloNum] = 0;
                }
                
                // Calcular ocupación real para este día
                $reservasDelDia = $reservas->where('id_espacio', $espacio->id_espacio)
                    ->filter(function($reserva) use ($dia) {
                        $diaSemana = strtolower(\Carbon\Carbon::parse($reserva->fecha_reserva)->locale('es')->isoFormat('dddd'));
                        return $diaSemana === $dia;
                    });
                
                foreach ($reservasDelDia as $reserva) {
                    if ($reserva->hora) {
                        $hora = \Carbon\Carbon::parse($reserva->hora);
                        $modulo = $this->obtenerModuloPorHora($hora->hour);
                        if (isset($ocupacionHorarios[$espacio->id_espacio][$dia][$modulo])) {
                            $ocupacionHorarios[$espacio->id_espacio][$dia][$modulo] = 100;
                        }
                    }
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
            return $this->exportarResumenPDF($datos);
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
            $reservasQuery = Reserva::with(['espacio', 'user'])
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
                $reservasQuery->whereHas('user', function($q) use ($tipoUsuario) {
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

                $datosExport[] = [
                    'fecha' => Carbon::parse($reserva->fecha_reserva)->format('d/m/Y'),
                    'hora_inicio' => $reserva->hora ? Carbon::parse($reserva->hora)->format('H:i') : 'N/A',
                    'hora_fin' => $reserva->hora_salida ? Carbon::parse($reserva->hora_salida)->format('H:i') : 'N/A',
                    'espacio' => $reserva->espacio->nombre_espacio . ' (' . $reserva->espacio->id_espacio . ')',
                    'usuario' => $reserva->user->name ?? 'Usuario no encontrado',
                    'tipo_usuario' => ucfirst($this->determinarTipoUsuario($reserva->user)),
                    'horas_utilizadas' => round($horasUtilizadas, 1),
                    'duracion' => $duracionFormateada,
                    'estado' => ucfirst($reserva->estado)
                ];
            }

            if ($format === 'excel') {
                return $this->exportarHistoricoExcel($datosExport, $fechaInicio, $fechaFin);
            } elseif ($format === 'pdf') {
                return $this->exportarHistoricoPDF($datosExport, $fechaInicio, $fechaFin);
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

    private function exportarHistoricoPDF($datos, $fechaInicio, $fechaFin)
    {
        try {
            $data = [
                'datos' => $datos,
                'fecha_generacion' => Carbon::now()->format('d/m/Y H:i:s'),
                'periodo' => Carbon::parse($fechaInicio)->format('d/m/Y') . ' - ' . Carbon::parse($fechaFin)->format('d/m/Y'),
                'total_registros' => count($datos)
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

    // 4. Reportes por unidad académica
    public function unidadAcademica(Request $request)
    {
        // Obtener todas las áreas académicas con sus carreras
        $areasAcademicas = AreaAcademica::with(['carreras', 'facultad'])->get();
        
        $datosUnidadAcademica = [];
        
        foreach ($areasAcademicas as $areaAcademica) {
            // Obtener asignaturas de todas las carreras de esta área académica
            $carrerasIds = $areaAcademica->carreras->pluck('id_carrera');
            $asignaturas = Asignatura::whereIn('id_carrera', $carrerasIds)->get();
            
            // Obtener planificaciones de estas asignaturas
            $asignaturasIds = $asignaturas->pluck('id_asignatura');
            $planificaciones = Planificacion_Asignatura::whereIn('id_asignatura', $asignaturasIds)->get();
            
            // Obtener espacios utilizados por esta área académica
            $espaciosUtilizados = $planificaciones->pluck('id_espacio')->unique();
            $totalEspacios = Espacio::whereHas('piso.facultad', function($q) use ($areaAcademica) {
                $q->where('id_facultad', $areaAcademica->id_facultad);
            })->count();
            
            // Calcular porcentaje de utilización
            $porcentajeUtilizacion = $totalEspacios > 0 ? round(($espaciosUtilizados->count() / $totalEspacios) * 100, 2) : 0;
            
            // Obtener reservas rechazadas (estado = 'rechazada') - solo reservas generales
            $reservasRechazadas = Reserva::where('estado', 'rechazada')->count();
            
            // Obtener espacios no utilizados
            $espaciosNoUtilizados = Espacio::whereHas('piso.facultad', function($q) use ($areaAcademica) {
                $q->where('id_facultad', $areaAcademica->id_facultad);
            })->whereNotIn('id_espacio', $espaciosUtilizados)->get();
            
            // Detalles de espacios no utilizados
            $detallesEspaciosNoUtilizados = $espaciosNoUtilizados->map(function($espacio) {
                return [
                    'id' => $espacio->id_espacio,
                    'nombre' => $espacio->nombre_espacio,
                    'tipo' => $espacio->tipo_espacio,
                    'piso' => $espacio->piso->numero_piso ?? 'N/A',
                    'capacidad' => $espacio->puestos_disponibles ?? 'N/A'
                ];
            });
            
            $datosUnidadAcademica[] = [
                'area_academica' => $areaAcademica,
                'total_carreras' => $areaAcademica->carreras->count(),
                'total_asignaturas' => $asignaturas->count(),
                'total_planificaciones' => $planificaciones->count(),
                'espacios_utilizados' => $espaciosUtilizados->count(),
                'total_espacios' => $totalEspacios,
                'porcentaje_utilizacion' => $porcentajeUtilizacion,
                'reservas_rechazadas' => $reservasRechazadas,
                'espacios_no_utilizados' => $detallesEspaciosNoUtilizados,
                'problemas' => $this->identificarProblemas($areaAcademica, $asignaturas, $planificaciones, $espaciosNoUtilizados)
            ];
        }
        
        return view('reportes.unidad-academica', compact('datosUnidadAcademica'));
    }
    
    public function exportUnidadAcademica($format)
    {
        // Obtener los mismos datos que en unidadAcademica
        $areasAcademicas = AreaAcademica::with(['carreras', 'facultad'])->get();
        
        $datosUnidadAcademica = [];
        
        foreach ($areasAcademicas as $areaAcademica) {
            $carrerasIds = $areaAcademica->carreras->pluck('id_carrera');
            $asignaturas = Asignatura::whereIn('id_carrera', $carrerasIds)->get();
            $asignaturasIds = $asignaturas->pluck('id_asignatura');
            $planificaciones = Planificacion_Asignatura::whereIn('id_asignatura', $asignaturasIds)->get();
            
            $espaciosUtilizados = $planificaciones->pluck('id_espacio')->unique();
            $totalEspacios = Espacio::whereHas('piso.facultad', function($q) use ($areaAcademica) {
                $q->where('id_facultad', $areaAcademica->id_facultad);
            })->count();
            
            $porcentajeUtilizacion = $totalEspacios > 0 ? round(($espaciosUtilizados->count() / $totalEspacios) * 100, 2) : 0;
            
            $reservasRechazadas = Reserva::where('estado', 'rechazada')->count();
            
            $espaciosNoUtilizados = Espacio::whereHas('piso.facultad', function($q) use ($areaAcademica) {
                $q->where('id_facultad', $areaAcademica->id_facultad);
            })->whereNotIn('id_espacio', $espaciosUtilizados)->get();
            
            $datosUnidadAcademica[] = [
                'area_academica' => $areaAcademica,
                'total_carreras' => $areaAcademica->carreras->count(),
                'total_asignaturas' => $asignaturas->count(),
                'total_planificaciones' => $planificaciones->count(),
                'espacios_utilizados' => $espaciosUtilizados->count(),
                'total_espacios' => $totalEspacios,
                'porcentaje_utilizacion' => $porcentajeUtilizacion,
                'reservas_rechazadas' => $reservasRechazadas,
                'espacios_no_utilizados' => $espaciosNoUtilizados
            ];
        }
        
        if ($format === 'pdf') {
            $pdf = Pdf::loadView('reportes.pdf.unidad-academica', compact('datosUnidadAcademica'));
            $filename = 'unidad_academica_' . date('Y-m-d_H-i-s') . '.pdf';
            return $pdf->download($filename);
        }
        
        // Excel: implementar si se requiere
        return back();
    }
    
    /**
     * Identificar problemas específicos de una área académica
     */
    private function identificarProblemas($areaAcademica, $asignaturas, $planificaciones, $espaciosNoUtilizados)
    {
        $problemas = [];
        
        // Problema 1: Área sin asignaturas
        if ($asignaturas->count() == 0) {
            $problemas[] = [
                'tipo' => 'sin_asignaturas',
                'descripcion' => 'Esta área académica no tiene asignaturas registradas',
                'severidad' => 'alta'
            ];
        }
        
        // Problema 2: Área sin planificaciones
        if ($planificaciones->count() == 0) {
            $problemas[] = [
                'tipo' => 'sin_planificaciones',
                'descripcion' => 'No hay planificaciones de horarios para las asignaturas',
                'severidad' => 'alta'
            ];
        }
        
        // Problema 3: Muchos espacios no utilizados
        if ($espaciosNoUtilizados->count() > 5) {
            $problemas[] = [
                'tipo' => 'espacios_no_utilizados',
                'descripcion' => 'Hay ' . $espaciosNoUtilizados->count() . ' espacios sin utilizar',
                'severidad' => 'media'
            ];
        }
        
        // Problema 4: Baja utilización de espacios
        $totalEspacios = Espacio::whereHas('piso.facultad', function($q) use ($areaAcademica) {
            $q->where('id_facultad', $areaAcademica->id_facultad);
        })->count();
        
        if ($totalEspacios > 0) {
            $porcentajeUtilizacion = ($planificaciones->pluck('id_espacio')->unique()->count() / $totalEspacios) * 100;
            if ($porcentajeUtilizacion < 30) {
                $problemas[] = [
                    'tipo' => 'baja_utilizacion',
                    'descripcion' => 'Solo se utiliza el ' . round($porcentajeUtilizacion, 1) . '% de los espacios disponibles',
                    'severidad' => 'media'
                ];
            }
        }
        
        return $problemas;
    }

    /**
     * Obtener accesos registrados con filtros
     */
    public function obtenerAccesosRegistrados($fechaInicio, $fechaFin, $piso = null, $tipoUsuario = null, $espacio = null)
    {
        $query = Reserva::with(['profesor', 'espacio.piso.facultad'])
            ->whereBetween('fecha_reserva', [$fechaInicio, $fechaFin])
            ->whereIn('estado', ['activa', 'finalizada']) // Mostrar activas y finalizadas
            ->whereNotNull('hora') // Solo las que tienen hora de entrada (escaneo QR)
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
            $query->whereHas('profesor', function ($q) use ($tipoUsuario) {
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

        // Filtrar por espacio
        if (!empty($espacio)) {
            $query->whereHas('espacio', function ($q) use ($espacio) {
                $q->where('nombre_espacio', 'like', '%' . $espacio . '%');
            });
        }

        return $query->get()->map(function ($reserva) {
            return [
                'id' => $reserva->id_reserva,
                'usuario' => $reserva->profesor->name ?? 'Profesor no encontrado',
                'run' => $reserva->profesor->run_profesor ?? 'N/A',
                'email' => $reserva->profesor->email ?? 'N/A',
                'tipo_usuario' => $this->determinarTipoUsuario($reserva->profesor),
                'espacio' => $reserva->espacio->nombre_espacio ?? 'Espacio no encontrado',
                'id_espacio' => $reserva->espacio->id_espacio ?? '',
                'piso' => $reserva->espacio->piso->numero_piso ?? 'N/A',
                'facultad' => $reserva->espacio->piso->facultad->nombre_facultad ?? 'N/A',
                'fecha' => Carbon::parse($reserva->fecha_reserva)->format('d/m/Y'),
                'hora_entrada' => $reserva->hora,
                'hora_salida' => $reserva->hora_salida ? Carbon::parse($reserva->hora_salida)->format('H:i:s') : 'En curso',
                'tipo_reserva' => $reserva->tipo_reserva ?? 'Directa',
                'estado' => $reserva->estado,
                'duracion' => $this->calcularDuracion($reserva->hora, $reserva->hora_salida),
                'incidencias' => $this->obtenerIncidencias($reserva->id_reserva)
            ];
        });
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
     * Obtener pisos disponibles
     */
    private function obtenerPisosDisponibles()
    {
        return Piso::whereHas('facultad', function ($query) {
            $query->where('id_facultad', 'IT_TH');
        })
        ->orderBy('numero_piso')
        ->pluck('numero_piso', 'numero_piso');
    }

    /**
     * Obtener espacios disponibles
     */
    private function obtenerEspaciosDisponibles()
    {
        return Espacio::whereHas('piso.facultad', function ($query) {
            $query->where('id_facultad', 'IT_TH');
        })
        ->orderBy('nombre_espacio')
        ->pluck('nombre_espacio', 'nombre_espacio');
    }

    /**
     * Obtener tipos de usuario
     */
    private function obtenerTiposUsuario()
    {
        return [
            'profesor' => 'Profesor',
            'estudiante' => 'Estudiante',
            'administrativo' => 'Administrativo',
            'externo' => 'Externo'
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

    private function exportarResumenPDF($datos)
    {
        $data = [
            'datos' => $datos,
            'fecha_generacion' => Carbon::now()->format('d/m/Y H:i:s'),
            'periodo' => Carbon::now()->format('m/Y'),
            'total_registros' => count($datos)
        ];

        $filename = 'analisis_espacios_' . date('Y-m-d_H-i-s') . '.pdf';
        $pdf = Pdf::loadView('reportes.pdf.espacios', $data);
        return $pdf->download($filename);
    }
} 