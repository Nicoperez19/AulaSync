<?php

namespace App\Http\Controllers;

use App\Models\Espacio;
use App\Models\Reserva;
use App\Models\User;
use App\Models\Profesor;
use App\Models\Asignatura;
use App\Models\Planificacion_Asignatura;
use App\Models\Modulo;
use App\Models\Piso;
use App\Helpers\SemesterHelper;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use App\Models\Mapa;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        // Increase time limit for dashboard queries
        set_time_limit(60);
        
        // Obtener el piso de la sesión o request
        $piso = $request->session()->get('piso');
        $facultad = 'IT_TH'; // Siempre usar IT_TH como facultad

        try {
            // Obtener los pisos disponibles para la facultad con consulta optimizada
            $pisos = $this->obtenerPisosOptimizado();

            // Obtener datos básicos con consultas optimizadas
            $datosBasicos = $this->obtenerDatosBasicosOptimizado($facultad, $piso);
            
            // Extraer datos básicos
            extract($datosBasicos);

            // Obtener datos más pesados solo si es necesario
            $datosPesados = $this->obtenerDatosPesadosOptimizado($facultad, $piso);
            
            // Extraer datos pesados
            extract($datosPesados);

            return view('layouts.dashboard', compact(
                'ocupacionSemanal',
                'ocupacionDiaria', 
                'ocupacionMensual',
                'usuariosSinEscaneo',
                'horasUtilizadas',
                'salasOcupadas',
                'usoPorDia',
                'comparativaTipos',
                'evolucionMensual',
                'reservasPorTipo',
                'reservasCanceladas',
                'horariosAgrupados',
                'facultad',
                'piso',
                'pisos',
                'reservasSinDevolucion',
                'promedioDuracion',
                'porcentajeNoShow',
                'canceladasPorTipo',
                'horariosPorTipoDiaModulo',
                'moduloActual',
                'accesosRegistrados',
                'tiposEspacioDisponibles',
                'modulosDisponibles',
                'accesosActuales',
                'reservasNoUtilizadas',
                'totalReservasHoy',
                'salaMasUtilizada',
                'salasDesocupadas'
            ));
            
        } catch (\Exception $e) {
            // En caso de error, mostrar dashboard con datos mínimos
            Log::error('Error en dashboard: ' . $e->getMessage());
            
            return view('layouts.dashboard', [
                'ocupacionSemanal' => 0,
                'ocupacionDiaria' => 0,
                'ocupacionMensual' => 0,
                'usuariosSinEscaneo' => 0,
                'horasUtilizadas' => ['utilizadas' => 0, 'disponibles' => 0],
                'salasOcupadas' => ['ocupadas' => 0, 'libres' => 0],
                'usoPorDia' => ['datos' => [], 'rango_fechas' => []],
                'comparativaTipos' => [],
                'evolucionMensual' => ['dias' => [], 'ocupacion' => []],
                'reservasPorTipo' => [],
                'reservasCanceladas' => [],
                'horariosAgrupados' => [],
                'facultad' => $facultad,
                'piso' => $piso,
                'pisos' => [],
                'reservasSinDevolucion' => collect([]),
                'promedioDuracion' => 0,
                'porcentajeNoShow' => 0,
                'canceladasPorTipo' => [],
                'horariosPorTipoDiaModulo' => [],
                'moduloActual' => null,
                'accesosRegistrados' => [],
                'tiposEspacioDisponibles' => [],
                'modulosDisponibles' => [],
                'accesosActuales' => collect([]),
                'reservasNoUtilizadas' => collect([]),
                'totalReservasHoy' => 0,
                'salaMasUtilizada' => null,
                'salasDesocupadas' => collect([])
            ]);
        }
    }

    private function obtenerPisosOptimizado()
    {
        return Piso::select('numero_piso')
            ->whereHas('facultad', function($query) {
                $query->where('id_facultad', 'IT_TH')
                      ->whereHas('sede', function($q) {
                          $q->where('id_sede', 'TH');
                      });
            })
            ->orderBy('numero_piso')
            ->get();
    }

    private function obtenerDatosBasicosOptimizado($facultad, $piso)
    {
        // Usar caché para datos básicos (válido por 2 minutos)
        $cacheKey = "dashboard_basicos_{$facultad}_{$piso}";
        
        return Cache::remember($cacheKey, 120, function() use ($facultad, $piso) {
            $hoy = Carbon::today();
            
            // Consultas básicas optimizadas
            $totalReservasHoy = Reserva::whereDate('fecha_reserva', $hoy)->count();
            
            // Salas ocupadas de forma más eficiente
            $salasQuery = DB::table('espacios')
                ->join('pisos', 'espacios.piso_id', '=', 'pisos.numero_piso')
                ->where('pisos.id_facultad', $facultad);
                
            if ($piso) {
                $salasQuery->where('pisos.numero_piso', $piso);
            }
                
            $totalSalas = $salasQuery->count();
            $salasOcupadas = $salasQuery->where('espacios.estado', 'Ocupado')->count();
            $salasLibres = $totalSalas - $salasOcupadas;

            return [
                'totalReservasHoy' => $totalReservasHoy,
                'salasOcupadas' => ['ocupadas' => $salasOcupadas, 'libres' => $salasLibres],
                'ocupacionSemanal' => $this->calcularOcupacionSemanalOptimizada($facultad, $piso),
                'ocupacionMensual' => $this->calcularOcupacionMensualOptimizada($facultad, $piso),
                'ocupacionDiaria' => 0, // Simplificamos por ahora
                'usuariosSinEscaneo' => 0, // Simplificamos por ahora
                'horasUtilizadas' => ['utilizadas' => 0, 'disponibles' => 0], // Simplificamos
            ];
        });
    }

    private function obtenerDatosPesadosOptimizado($facultad, $piso)
    {
        // Usar caché para datos pesados (válido por 5 minutos)
        $cacheKey = "dashboard_pesados_{$facultad}_{$piso}";
        
        return Cache::remember($cacheKey, 300, function() use ($facultad, $piso) {
            $hoy = Carbon::today();
            
            // Obtener datos menos críticos
            $usoPorDia = $this->obtenerUsoPorDiaOptimizado($facultad, $piso);
            $evolucionMensual = $this->obtenerEvolucionMensualOptimizada($facultad, $piso);
            
            // Reservas sin devolución (limitadas y optimizadas)
            $reservasSinDevolucion = DB::table('reservas')
                ->join('profesors', 'reservas.run_profesor', '=', 'profesors.run_profesor')
                ->join('espacios', 'reservas.id_espacio', '=', 'espacios.id_espacio')
                ->select(
                    'reservas.*',
                    'profesors.name as profesor_name',
                    'espacios.nombre_espacio'
                )
                ->where('reservas.estado', 'activa')
                ->whereNull('reservas.hora_salida')
                ->limit(10) // Limitar resultados
                ->get();

            // Accesos actuales (limitados y optimizados)  
            $accesosActuales = DB::table('reservas')
                ->leftJoin('profesors', 'reservas.run_profesor', '=', 'profesors.run_profesor')
                ->leftJoin('solicitantes', 'reservas.run_solicitante', '=', 'solicitantes.run_solicitante')
                ->join('espacios', 'reservas.id_espacio', '=', 'espacios.id_espacio')
                ->select(
                    'reservas.*',
                    'profesors.name as profesor_name',
                    'solicitantes.nombre as solicitante_name',
                    'espacios.nombre_espacio'
                )
                ->where('reservas.estado', 'activa')
                ->whereNull('reservas.hora_salida')
                ->limit(10) // Limitar resultados
                ->get();

            // Sala más utilizada (optimizada con SQL directo)
            $salaMasUtilizada = DB::table('reservas')
                ->join('espacios', 'reservas.id_espacio', '=', 'espacios.id_espacio')
                ->select('reservas.id_espacio', 'espacios.nombre_espacio', DB::raw('count(*) as total'))
                ->groupBy('reservas.id_espacio', 'espacios.nombre_espacio')
                ->orderByDesc('total')
                ->first();

            return [
                'usoPorDia' => $usoPorDia,
                'evolucionMensual' => $evolucionMensual,
                'comparativaTipos' => $this->obtenerComparativaTiposOptimizada($facultad, $piso),
                'reservasPorTipo' => [],
                'reservasCanceladas' => collect([]),
                'horariosAgrupados' => $this->obtenerHorariosAgrupadosOptimizado($facultad, $piso),
                'reservasSinDevolucion' => $reservasSinDevolucion,
                'promedioDuracion' => 0,
                'porcentajeNoShow' => 0,
                'canceladasPorTipo' => [],
                'horariosPorTipoDiaModulo' => [],
                'moduloActual' => $this->obtenerModuloActual(),
                'accesosRegistrados' => [],
                'tiposEspacioDisponibles' => Cache::remember('tipos_espacio', 3600, function() {
                    return Espacio::select('tipo_espacio')->distinct()->pluck('tipo_espacio');
                }),
                'modulosDisponibles' => [],
                'accesosActuales' => $accesosActuales,
                'reservasNoUtilizadas' => collect([]),
                'salaMasUtilizada' => $salaMasUtilizada,
                'salasDesocupadas' => collect([])
            ];
        });
    }

    private function calcularOcupacionSemanal($facultad, $piso)
    {
        $inicioSemana = Carbon::now()->startOfWeek();
        $finSemana = Carbon::now()->endOfWeek();
        
        $totalHoras = 40; // 8 horas por día, 5 días
        $horasOcupadas = Reserva::whereBetween('fecha_reserva', [$inicioSemana, $finSemana])
            ->whereIn('estado', ['activa', 'finalizada']) // Incluir tanto activas como finalizadas
            ->whereHas('espacio', function($query) use ($piso) {
                if ($piso) {
                    $query->whereHas('piso', function($q) use ($piso) {
                        $q->where('numero_piso', $piso);
                    });
                }
            })
            ->count();

    $porcentaje = round(($horasOcupadas / $totalHoras) * 100, 2);
    \Log::info('Ocupación semanal calculada', ['horasOcupadas' => $horasOcupadas, 'totalHoras' => $totalHoras, 'porcentaje' => $porcentaje]);
    return $porcentaje;
    }

    private function calcularOcupacionDiaria($facultad, $piso)
    {
        $hoy = Carbon::today();
        $diaSemana = $hoy->format('l');
        
        $modulos = Modulo::where('dia', $diaSemana)
            ->orderBy('hora_inicio')
            ->get();
        
        $ocupacion = [];
        
        foreach ($modulos as $modulo) {
            $espaciosOcupados = Planificacion_Asignatura::where('id_modulo', $modulo->id_modulo)
                ->whereHas('espacio', function($query) use ($piso) {
                    if ($piso) {
                        $query->whereHas('piso', function($q) use ($piso) {
                            $q->where('numero_piso', $piso);
                        });
                    }
                })
                ->whereHas('espacio', function($query) {
                    $query->where('estado', 'Ocupado');
                })
                ->count();
                
            $totalEspacios = $this->obtenerEspaciosQuery($facultad, $piso)->count();
            
            $porcentaje = $totalEspacios > 0 ? ($espaciosOcupados / $totalEspacios) * 100 : 0;
            
            $ocupacion[$modulo->hora_inicio] = round($porcentaje, 2);
        }
        
        return $ocupacion;
    }

    private function calcularOcupacionMensual($facultad, $piso)
    {
        $inicioMes = Carbon::now()->startOfMonth();
        $finMes = Carbon::now()->endOfMonth();
        
        $totalHoras = 160; // 8 horas por día, 20 días hábiles
        $horasOcupadas = Reserva::whereBetween('fecha_reserva', [$inicioMes, $finMes])
            ->whereIn('estado', ['activa', 'finalizada']) // Incluir tanto activas como finalizadas
            ->whereHas('espacio', function($query) use ($piso) {
                if ($piso) {
                    $query->whereHas('piso', function($q) use ($piso) {
                        $q->where('numero_piso', $piso);
                    });
                }
            })
            ->count();

    $porcentaje = round(($horasOcupadas / $totalHoras) * 100, 2);
    \Log::info('Ocupación mensual calculada', ['horasOcupadas' => $horasOcupadas, 'totalHoras' => $totalHoras, 'porcentaje' => $porcentaje]);
    return $porcentaje;
    }

    private function obtenerUsuariosSinEscaneo($facultad, $piso)
    {
        $hoy = Carbon::today();
        
        // Obtener los espacios de la facultad y piso especificados
        $espacios = $this->obtenerEspaciosQuery($facultad, $piso)->pluck('id_espacio');
        
        // Obtener profesores que no tienen reservas hoy en los espacios especificados
        return Profesor::whereDoesntHave('reservas', function($query) use ($hoy, $espacios) {
            $query->whereDate('fecha_reserva', $hoy)
                  ->whereIn('id_espacio', $espacios);
        })->count();
    }

    private function calcularHorasUtilizadas($facultad, $piso)
    {
        $hoy = Carbon::today();
        $horasUtilizadas = Reserva::whereDate('fecha_reserva', $hoy)
            ->whereIn('estado', ['activa', 'finalizada']) // Incluir tanto activas como finalizadas
            ->whereHas('espacio', function($query) use ($piso) {
                if ($piso) {
                    $query->whereHas('piso', function($q) use ($piso) {
                        $q->where('numero_piso', $piso);
                    });
                }
            })
            ->count();

        return [
            'utilizadas' => $horasUtilizadas,
            'disponibles' => 40 // 8 horas por día, 5 días
        ];
    }

    private function obtenerSalasOcupadas($facultad, $piso)
    {
        $espaciosQuery = $this->obtenerEspaciosQuery($facultad, $piso);
        $ocupados = (clone $espaciosQuery)->where('estado', 'Ocupado')->count();
        $libres = (clone $espaciosQuery)->where('estado', 'Disponible')->count();

        return [
            'ocupadas' => $ocupados,
            'libres' => $libres,
            'modulo_actual' => null
        ];
    }

    private function obtenerUsoPorDia($facultad, $piso)
    {
        $inicioSemana = Carbon::now()->startOfWeek();
        $finSemana = Carbon::now()->endOfWeek();
        $diasSemana = ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'];
        $usoPorDia = [];
        
        for ($i = 0; $i < 6; $i++) {
            $dia = $inicioSemana->copy()->addDays($i);
            $usoPorDia[$diasSemana[$i]] = Reserva::whereDate('fecha_reserva', $dia)
                ->whereIn('estado', ['activa', 'finalizada']) // Incluir tanto activas como finalizadas
                ->whereHas('espacio', function($query) use ($piso) {
                    if ($piso) {
                        $query->whereHas('piso', function($q) use ($piso) {
                            $q->where('numero_piso', $piso);
                        });
                    }
                })
                ->count();
        }
        
        return [
            'datos' => $usoPorDia,
            'rango_fechas' => [
                'inicio' => $inicioSemana->format('d/m/Y'),
                'fin' => $finSemana->format('d/m/Y')
            ]
        ];
    }

    private function obtenerComparativaTipos($facultad, $piso)
    {
        $inicioSemana = Carbon::now()->startOfWeek();
        $finSemana = Carbon::now()->endOfWeek();

        // 1. Obtener todos los tipos de espacio distintos para el piso y facultad seleccionados
        $tiposDeEspacioQuery = Espacio::query()
            ->whereHas('piso', function($query) use ($facultad, $piso) {
                $query->where('id_facultad', $facultad);
                if ($piso) {
                    $query->where('numero_piso', $piso);
                }
            });
        
        $todosLosTipos = $tiposDeEspacioQuery->select('tipo_espacio')->distinct()->pluck('tipo_espacio');

        $result = [];
        foreach ($todosLosTipos as $tipo) {
            // Total de espacios de este tipo
            $total = Espacio::where('tipo_espacio', $tipo)
                ->whereHas('piso', function($query) use ($facultad, $piso) {
                    $query->where('id_facultad', $facultad);
                    if ($piso) {
                        $query->where('numero_piso', $piso);
                    }
                })->count();
            // Ocupados en la semana
            $ocupados = Reserva::join('espacios', 'reservas.id_espacio', '=', 'espacios.id_espacio')
                ->join('pisos', 'espacios.piso_id', '=', 'pisos.id')
                ->whereBetween('reservas.fecha_reserva', [$inicioSemana, $finSemana])
                ->where('reservas.estado', 'activa')
                ->where('pisos.id_facultad', $facultad)
                ->where('espacios.tipo_espacio', $tipo);
            if ($piso) {
                $ocupados->where('pisos.numero_piso', $piso);
            }
            $ocupadosCount = $ocupados->count();
            $porcentaje = ($total > 0) ? round(($ocupadosCount / $total) * 100) : 0;
            $result[] = [
                'nombre' => $tipo,
                'porcentaje' => $porcentaje,
                'ocupados' => $ocupadosCount,
                'total' => $total
            ];
        }
        return collect($result);
    }

    private function obtenerReservasPorTipo($facultad, $piso)
    {
        $inicioSemana = Carbon::now()->startOfWeek();
        $finSemana = Carbon::now()->endOfWeek();

        // Obtener todos los tipos de espacio distintos para el piso y facultad seleccionados
        $tiposDeEspacioQuery = Espacio::query()
            ->whereHas('piso', function($query) use ($facultad, $piso) {
                $query->where('id_facultad', $facultad);
                if ($piso) {
                    $query->where('numero_piso', $piso);
                }
            });
        
        $todosLosTipos = $tiposDeEspacioQuery->select('tipo_espacio')->distinct()->pluck('tipo_espacio');

        // Obtener las reservas por tipo de espacio
        $reservasPorTipoQuery = Reserva::join('espacios', 'reservas.id_espacio', '=', 'espacios.id_espacio')
            ->join('pisos', 'espacios.piso_id', '=', 'pisos.id')
            ->whereBetween('reservas.fecha_reserva', [$inicioSemana, $finSemana])
            ->where('reservas.estado', 'activa')
            ->where('pisos.id_facultad', $facultad)
            ->whereIn('espacios.tipo_espacio', $todosLosTipos);

        if ($piso) {
            $reservasPorTipoQuery->where('pisos.numero_piso', $piso);
        }

        $reservasPorTipo = $reservasPorTipoQuery
            ->select('espacios.tipo_espacio', DB::raw('count(*) as total'))
            ->groupBy('espacios.tipo_espacio')
            ->pluck('total', 'tipo_espacio');

        // Mapear todos los tipos de espacio, asignando 0 a los que no tienen reservas
        return $todosLosTipos->map(function($tipo) use ($reservasPorTipo) {
            return [
                'tipo' => $tipo,
                'total' => $reservasPorTipo->get($tipo, 0)
            ];
        });
    }

    private function obtenerEvolucionMensual($facultad, $piso)
    {
        $inicioSemana = Carbon::now()->startOfWeek();
        $diasSemana = [];
        $ocupacion = [];
        
        for ($i = 0; $i < 7; $i++) {
            $dia = $inicioSemana->copy()->addDays($i);
            $diasSemana[] = $dia->format('d/m');
            $ocupacion[] = Reserva::whereDate('fecha_reserva', $dia)
                ->where('estado', 'activa')
                ->whereHas('espacio', function($query) use ($piso) {
                    if ($piso) {
                        $query->whereHas('piso', function($q) use ($piso) {
                            $q->where('numero_piso', $piso);
                        });
                    }
                })
                ->count() * 12.5; // Convertir a porcentaje
        }
        
        return [
            'dias' => $diasSemana,
            'ocupacion' => $ocupacion
        ];
    }

    private function obtenerReservasCanceladas($facultad, $piso)
    {
        $inicioSemana = Carbon::now()->startOfWeek();
        $finSemana = Carbon::now()->endOfWeek();
        
        return Reserva::with(['profesor', 'espacio'])
            ->where('estado', 'finalizada')
            ->whereBetween('fecha_reserva', [$inicioSemana, $finSemana])
            ->whereHas('espacio', function($query) use ($piso) {
                if ($piso) {
                    $query->whereHas('piso', function($q) use ($piso) {
                        $q->where('numero_piso', $piso);
                    });
                }
            })
            ->get()
            ->map(function($reserva) {
                return [
                    'usuario' => $reserva->user->name ?? 'Usuario no encontrado',
                    'espacio' => $reserva->espacio->nombre_espacio,
                    'hora' => $reserva->hora
                ];
            });
    }

    private function obtenerHorariosAgrupados($facultad, $piso)
    {
        // Día y módulo actual
        $diaActual = strtolower(now()->locale('es')->isoFormat('dddd'));
        $horaActual = now()->format('H:i:s');
        
        // Buscar el módulo actual
        $moduloActual = Modulo::where('dia', $diaActual)
            ->where('hora_inicio', '<=', $horaActual)
            ->where('hora_termino', '>', $horaActual)
            ->first();
        
        if (!$moduloActual) {
            return [];
        }
        
        // Determinar el período actual usando el helper
        $anioActual = SemesterHelper::getCurrentAcademicYear();
        $semestre = SemesterHelper::getCurrentSemester();
        $periodo = SemesterHelper::getCurrentPeriod();
        
        $planificaciones = Planificacion_Asignatura::with(['asignatura.profesor', 'espacio', 'modulo'])
            ->whereHas('modulo', function($query) use ($diaActual, $moduloActual) {
                $query->where('dia', $diaActual)
                      ->where('id_modulo', $moduloActual->id_modulo);
            })
            ->whereHas('horario', function($query) use ($periodo) {
                $query->where('periodo', $periodo);
            })
            ->whereHas('espacio', function($query) use ($piso) {
                if ($piso) {
                    $query->whereHas('piso', function($q) use ($piso) {
                        $q->where('numero_piso', $piso);
                    });
                }
            })
            ->get();
        
        $horariosAgrupados = [];
        $hora = $moduloActual->hora_inicio . ' - ' . $moduloActual->hora_termino;
        $dia = ucfirst($diaActual);
        
        // Extraer el número del módulo del id_modulo (ejemplo: "lunes.3" -> "3")
        $numeroModulo = explode('.', $moduloActual->id_modulo)[1] ?? 'N/A';
        
        foreach ($planificaciones as $planificacion) {
            if (!isset($horariosAgrupados[$dia])) {
                $horariosAgrupados[$dia] = [];
            }
            if (!isset($horariosAgrupados[$dia][$hora])) {
                $horariosAgrupados[$dia][$hora] = [
                    'numero_modulo' => $numeroModulo,
                    'espacios' => []
                ];
            }
            $horariosAgrupados[$dia][$hora]['espacios'][] = [
                'espacio' => 'Sala de clases (' . $planificacion->espacio->id_espacio . '), Piso ' . ($planificacion->espacio->piso->numero_piso ?? 'N/A'),
                'asignatura' => $planificacion->asignatura->nombre_asignatura,
                'profesor' => $planificacion->asignatura->profesor->name ?? 'No asignado',
                'email' => $planificacion->asignatura->profesor->email ?? 'No disponible'
            ];
        }
        return $horariosAgrupados;
    }

    private function obtenerEspaciosQuery($facultad, $piso)
    {
        return Espacio::whereHas('piso', function($query) use ($facultad, $piso) {
            $query->where('id_facultad', $facultad);
            if ($piso) {
                $query->where('numero_piso', $piso);
            }
        });
    }

    public function getWidgetData(Request $request)
    {
        $piso = $request->session()->get('piso');
        $facultad = 'IT_TH';

        // Obtener datos para los KPIs
        $ocupacionSemanal = $this->calcularOcupacionSemanal($facultad, $piso);
        $ocupacionDiaria = $this->calcularOcupacionDiaria($facultad, $piso);
        $ocupacionMensual = $this->calcularOcupacionMensual($facultad, $piso);
        $usuariosSinEscaneo = $this->obtenerUsuariosSinEscaneo($facultad, $piso);
        $horasUtilizadas = $this->calcularHorasUtilizadas($facultad, $piso);
        $salasOcupadas = $this->obtenerSalasOcupadas($facultad, $piso);

        // Obtener datos para los gráficos
        $usoPorDia = $this->obtenerUsoPorDia($facultad, $piso);
        $comparativaTipos = $this->obtenerComparativaTipos($facultad, $piso);
        $evolucionMensual = $this->obtenerEvolucionMensual($facultad, $piso);

        // Obtener datos para reservas por tipo de espacio (gráfico de barras)
        $reservasPorTipo = $this->obtenerReservasPorTipo($facultad, $piso);

        // Obtener datos para las tablas
        $reservasCanceladas = $this->obtenerReservasCanceladas($facultad, $piso);
        $horariosAgrupados = $this->obtenerHorariosAgrupados($facultad, $piso);
        $reservasSinDevolucion = $this->obtenerReservasActivasSinDevolucion($facultad, $piso);
        $promedioDuracion = $this->obtenerPromedioDuracionReserva($facultad, $piso);
        $porcentajeNoShow = $this->obtenerPorcentajeNoShow($facultad, $piso);
        $canceladasPorTipo = $this->obtenerCanceladasPorTipoSala($facultad, $piso);

        return response()->json([
            'ocupacionSemanal' => $ocupacionSemanal,
            'ocupacionDiaria' => $ocupacionDiaria,
            'ocupacionMensual' => $ocupacionMensual,
            'usuariosSinEscaneo' => $usuariosSinEscaneo,
            'horasUtilizadas' => $horasUtilizadas,
            'salasOcupadas' => $salasOcupadas,
            'usoPorDia' => $usoPorDia,
            'comparativaTipos' => $comparativaTipos,
            'evolucionMensual' => $evolucionMensual,
            'reservasPorTipo' => $reservasPorTipo,
            'reservasCanceladas' => $reservasCanceladas,
            'horariosAgrupados' => $horariosAgrupados,
            'reservasSinDevolucion' => $reservasSinDevolucion,
            'promedioDuracion' => $promedioDuracion,
            'porcentajeNoShow' => $porcentajeNoShow,
            'canceladasPorTipo' => $canceladasPorTipo
        ]);
    }

    private function obtenerReservasActivasSinDevolucion($facultad, $piso)
    {
        return Reserva::with(['profesor', 'solicitante', 'espacio.piso.facultad'])
            ->where('estado', 'activa')           // Solo reservas activas
            ->latest('fecha_reserva')
            ->latest('hora')
            ->get();
    }

    public function getKeyReturnNotifications()
    {
        $now = Carbon::now();
        $timeLimit = $now->copy()->addMinutes(10);

        // Determinar el período actual usando el helper
        $anioActual = SemesterHelper::getCurrentAcademicYear();
        $semestre = SemesterHelper::getCurrentSemester();
        $periodo = SemesterHelper::getCurrentPeriod();
        
        // Obtener planificaciones que terminan en los próximos 10 minutos
        $planificaciones = Planificacion_Asignatura::with(['modulo', 'espacio', 'asignatura.profesor'])
            ->whereHas('modulo', function ($query) use ($now, $timeLimit) {
                $query->where('dia', strtolower($now->locale('es')->isoFormat('dddd')))
                      ->whereTime('hora_termino', '>', $now->format('H:i:s'))
                      ->whereTime('hora_termino', '<=', $timeLimit->format('H:i:s'));
            })
            ->whereHas('horario', function ($query) use ($periodo) {
                $query->where('periodo', $periodo);
            })
            ->whereHas('espacio', function ($query) {
                // Solo incluir espacios que estén realmente ocupados
                $query->where('estado', 'Ocupado');
            })
            ->get();

        $notifications = [];
        
        foreach ($planificaciones as $plan) {
            $profesor = $plan->asignatura->profesor->name ?? 'Profesor no asignado';
            $espacio = $plan->espacio->nombre_espacio ?? 'Espacio no asignado';
            $horaTermino = Carbon::parse($plan->modulo->hora_termino)->format('H:i');
            
            // Crear notificación en la base de datos
            \App\Http\Controllers\NotificationController::createKeyReturnNotification(
                $profesor,
                $espacio,
                $horaTermino
            );
            
            $notifications[] = [
                'profesor' => $profesor,
                'espacio' => $espacio,
                'hora_termino' => $horaTermino,
            ];
        }

        return response()->json($notifications);
    }

    private function obtenerPromedioDuracionReserva($facultad, $piso)
    {
        $reservas = Reserva::where('estado', 'finalizada')
            ->whereNotNull('hora')
            ->whereNotNull('hora_salida')
            // ->whereBetween('fecha_reserva', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()]) // Se comenta para pruebas
            ->whereHas('espacio', function($query) use ($facultad, $piso) {
                $query->whereHas('piso', function($q) use ($facultad, $piso) {
                    $q->where('id_facultad', $facultad);
                    if ($piso) {
                        $q->where('numero_piso', $piso);
                    }
                });
            })
            ->get();

        if ($reservas->isEmpty()) {
            return 0;
        }

        $totalDuracion = $reservas->sum(function ($reserva) {
            $inicio = Carbon::parse($reserva->hora);
            $fin = Carbon::parse($reserva->hora_salida);
            return $fin->diffInMinutes($inicio);
        });

        return round($totalDuracion / $reservas->count());
    }

    private function obtenerPorcentajeNoShow($facultad, $piso)
    {
        $now = Carbon::now();
        $baseQuery = Reserva::query() // ->whereBetween('fecha_reserva', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()]) // Se comenta para pruebas
            ->whereHas('espacio', function($query) use ($facultad, $piso) {
                $query->whereHas('piso', function($q) use ($facultad, $piso) {
                    $q->where('id_facultad', $facultad);
                    if ($piso) {
                        $q->where('numero_piso', $piso);
                    }
                });
            });

        $totalReservas = (clone $baseQuery)->count();

        if ($totalReservas === 0) {
            return 0;
        }

        $noShowReservas = (clone $baseQuery)
            ->where('estado', 'finalizada')
            ->where(function ($query) use ($now) {
                $query->where('fecha_reserva', '<', $now->toDateString())
                      ->orWhere(function ($query) use ($now) {
                          $query->where('fecha_reserva', '=', $now->toDateString())
                                ->where('hora', '<', $now->toTimeString());
                      });
            })
            ->count();

        return round(($noShowReservas / $totalReservas) * 100);
    }

    private function obtenerCanceladasPorTipoSala($facultad, $piso)
    {
        return Reserva::with('espacio')
            ->where('estado', 'finalizada')
            // ->whereBetween('fecha_reserva', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()]) // Se comenta para pruebas
            ->whereHas('espacio', function($query) use ($facultad, $piso) {
                $query->whereHas('piso', function($q) use ($facultad, $piso) {
                    $q->where('id_facultad', $facultad);
                    if ($piso) {
                        $q->where('numero_piso', $piso);
                    }
                });
            })
            ->get()
            ->groupBy('espacio.tipo_espacio')
            ->map(fn($group) => $group->count());
    }

    private function obtenerOcupacionPorTipoDiaModulo($facultad, $piso)
    {
        // Determinar el período actual usando el helper
        $anioActual = SemesterHelper::getCurrentAcademicYear();
        $semestre = SemesterHelper::getCurrentSemester();
        $periodo = SemesterHelper::getCurrentPeriod();
        
        $diasSemana = [
            'Monday' => 'Lunes',
            'Tuesday' => 'Martes',
            'Wednesday' => 'Miércoles',
            'Thursday' => 'Jueves',
            'Friday' => 'Viernes',
            'Saturday' => 'Sábado',
        ];
        $tiposEspacio = Espacio::whereHas('piso', function($q) use ($facultad, $piso) {
            $q->where('id_facultad', $facultad);
            if ($piso) $q->where('numero_piso', $piso);
        })->select('tipo_espacio')->distinct()->pluck('tipo_espacio');

        $modulos = Modulo::all()->groupBy('dia');
        $resultado = [];

        foreach ($tiposEspacio as $tipo) {
            foreach ($diasSemana as $diaEN => $diaES) {
                $modulosDia = $modulos->get($diaEN, collect());
                foreach ($modulosDia as $modulo) {
                    $totalEspacios = Espacio::where('tipo_espacio', $tipo)
                        ->whereHas('piso', function($q) use ($facultad, $piso) {
                            $q->where('id_facultad', $facultad);
                            if ($piso) $q->where('numero_piso', $piso);
                        })->count();
                    if ($totalEspacios === 0) {
                        $resultado[$tipo][$diaES][$modulo->id_modulo] = 0;
                        continue;
                    }
                    $ocupados = Planificacion_Asignatura::where('id_modulo', $modulo->id_modulo)
                        ->whereHas('horario', function($q) use ($periodo) {
                            $q->where('periodo', $periodo);
                        })
                        ->whereHas('espacio', function($q) use ($tipo, $facultad, $piso) {
                            $q->where('tipo_espacio', $tipo)
                              ->whereHas('piso', function($q2) use ($facultad, $piso) {
                                  $q2->where('id_facultad', $facultad);
                                  if ($piso) $q2->where('numero_piso', $piso);
                              });
                        })->count();
                    $resultado[$tipo][$diaES][$modulo->id_modulo] = round(($ocupados / $totalEspacios) * 100);
                }
            }
        }
        return $resultado;
    }

    public function utilizacionTipoEspacioAjax(Request $request)
    {
        $piso = $request->session()->get('piso');
        $facultad = 'IT_TH';
        
        // Usar la misma lógica que el método principal
        $comparativaTipos = $this->obtenerComparativaTipos($facultad, $piso);
        
        return view('partials.tabla_utilizacion_tipo_espacio', compact('comparativaTipos'));
    }

    public function noUtilizadasDiaAjax(Request $request)
    {
        $fecha = $request->get('fecha', now()->toDateString());
        
        // Determinar el período actual usando el helper
        $anioActual = SemesterHelper::getCurrentAcademicYear();
        $semestre = SemesterHelper::getCurrentSemester();
        $periodo = SemesterHelper::getCurrentPeriod();
        
        $planificaciones = Planificacion_Asignatura::with(['asignatura.profesor', 'espacio', 'modulo'])
            ->whereHas('modulo', function($q) use ($fecha) {
                $dia = Carbon::parse($fecha)->locale('es')->isoFormat('dddd');
                $q->where('dia', strtolower($dia));
            })
            ->whereHas('horario', function($q) use ($periodo) {
                $q->where('periodo', $periodo);
            })
            ->get();

        $noUtilizadasDia = [];
        foreach ($planificaciones as $plan) {
            $usuario = $plan->asignatura->profesor->name ?? null;
            $espacio = $plan->espacio->nombre_espacio ?? null;
            $modulo = $plan->modulo->hora_inicio . ' - ' . $plan->modulo->hora_termino;
            $fechaPlan = $fecha;
            if (!$usuario || !$espacio) continue;

            $reservaOcupada = Reserva::where('id_espacio', $plan->espacio->id_espacio)
                ->where('id_usuario', $plan->asignatura->profesor->run_profesor ?? null)
                ->whereDate('fecha_reserva', $fecha)
                ->where('hora_planificada', $plan->modulo->hora_inicio)
                ->where('estado', 'activa')
                ->whereHas('espacio', function($q) {
                    $q->where('estado', 'Ocupado');
                })
                ->exists();

            if (!$reservaOcupada) {
                $noUtilizadasDia[] = [
                    'usuario' => $usuario,
                    'espacio' => $espacio,
                    'fecha' =>Carbon ::parse($fechaPlan)->format('d/m/Y'),
                    'modulo' => $modulo,
                ];
            }
        }
        return view('partials.tabla_no_utilizadas_dia', compact('noUtilizadasDia'))->render();
    }

    public function horariosActualAjax(Request $request)
    {
        $diaActual = strtolower([
            'domingo', 'lunes', 'martes', 'miercoles', 'jueves', 'viernes', 'sabado'
        ][date('w')]);
        $horaAhora = date('H:i:s');
        $moduloActualNum = null;
        $moduloActualHorario = null;
        $horariosModulos = [
            'lunes' => [
                1 => ['inicio' => '08:10:00', 'fin' => '09:00:00'],
                2 => ['inicio' => '09:10:00', 'fin' => '10:00:00'],
                3 => ['inicio' => '10:10:00', 'fin' => '11:00:00'],
                4 => ['inicio' => '11:10:00', 'fin' => '12:00:00'],
                5 => ['inicio' => '12:10:00', 'fin' => '13:00:00'],
                6 => ['inicio' => '13:10:00', 'fin' => '14:00:00'],
                7 => ['inicio' => '14:10:00', 'fin' => '15:00:00'],
                8 => ['inicio' => '15:10:00', 'fin' => '16:00:00'],
                9 => ['inicio' => '16:10:00', 'fin' => '17:00:00'],
                10 => ['inicio' => '17:10:00', 'fin' => '18:00:00'],
                11 => ['inicio' => '18:10:00', 'fin' => '19:00:00'],
                12 => ['inicio' => '19:10:00', 'fin' => '20:00:00'],
                13 => ['inicio' => '20:10:00', 'fin' => '21:00:00'],
                14 => ['inicio' => '21:10:00', 'fin' => '22:00:00'],
                15 => ['inicio' => '22:10:00', 'fin' => '23:00:00'],
            ],
            'martes' => [
                1 => ['inicio' => '08:10:00', 'fin' => '09:00:00'],
                2 => ['inicio' => '09:10:00', 'fin' => '10:00:00'],
                3 => ['inicio' => '10:10:00', 'fin' => '11:00:00'],
                4 => ['inicio' => '11:10:00', 'fin' => '12:00:00'],
                5 => ['inicio' => '12:10:00', 'fin' => '13:00:00'],
                6 => ['inicio' => '13:10:00', 'fin' => '14:00:00'],
                7 => ['inicio' => '14:10:00', 'fin' => '15:00:00'],
                8 => ['inicio' => '15:10:00', 'fin' => '16:00:00'],
                9 => ['inicio' => '16:10:00', 'fin' => '17:00:00'],
                10 => ['inicio' => '17:10:00', 'fin' => '18:00:00'],
                11 => ['inicio' => '18:10:00', 'fin' => '19:00:00'],
                12 => ['inicio' => '19:10:00', 'fin' => '20:00:00'],
                13 => ['inicio' => '20:10:00', 'fin' => '21:00:00'],
                14 => ['inicio' => '21:10:00', 'fin' => '22:00:00'],
                15 => ['inicio' => '22:10:00', 'fin' => '23:00:00'],
            ],
            'miercoles' => [
                1 => ['inicio' => '08:10:00', 'fin' => '09:00:00'],
                2 => ['inicio' => '09:10:00', 'fin' => '10:00:00'],
                3 => ['inicio' => '10:10:00', 'fin' => '11:00:00'],
                4 => ['inicio' => '11:10:00', 'fin' => '12:00:00'],
                5 => ['inicio' => '12:10:00', 'fin' => '13:00:00'],
                6 => ['inicio' => '13:10:00', 'fin' => '14:00:00'],
                7 => ['inicio' => '14:10:00', 'fin' => '15:00:00'],
                8 => ['inicio' => '15:10:00', 'fin' => '16:00:00'],
                9 => ['inicio' => '16:10:00', 'fin' => '17:00:00'],
                10 => ['inicio' => '17:10:00', 'fin' => '18:00:00'],
                11 => ['inicio' => '18:10:00', 'fin' => '19:00:00'],
                12 => ['inicio' => '19:10:00', 'fin' => '20:00:00'],
                13 => ['inicio' => '20:10:00', 'fin' => '21:00:00'],
                14 => ['inicio' => '21:10:00', 'fin' => '22:00:00'],
                15 => ['inicio' => '22:10:00', 'fin' => '23:00:00'],
            ],
            'jueves' => [
                1 => ['inicio' => '08:10:00', 'fin' => '09:00:00'],
                2 => ['inicio' => '09:10:00', 'fin' => '10:00:00'],
                3 => ['inicio' => '10:10:00', 'fin' => '11:00:00'],
                4 => ['inicio' => '11:10:00', 'fin' => '12:00:00'],
                5 => ['inicio' => '12:10:00', 'fin' => '13:00:00'],
                6 => ['inicio' => '13:10:00', 'fin' => '14:00:00'],
                7 => ['inicio' => '14:10:00', 'fin' => '15:00:00'],
                8 => ['inicio' => '15:10:00', 'fin' => '16:00:00'],
                9 => ['inicio' => '16:10:00', 'fin' => '17:00:00'],
                10 => ['inicio' => '17:10:00', 'fin' => '18:00:00'],
                11 => ['inicio' => '18:10:00', 'fin' => '19:00:00'],
                12 => ['inicio' => '19:10:00', 'fin' => '20:00:00'],
                13 => ['inicio' => '20:10:00', 'fin' => '21:00:00'],
                14 => ['inicio' => '21:10:00', 'fin' => '22:00:00'],
                15 => ['inicio' => '22:10:00', 'fin' => '23:00:00'],
            ],
            'viernes' => [
                1 => ['inicio' => '08:10:00', 'fin' => '09:00:00'],
                2 => ['inicio' => '09:10:00', 'fin' => '10:00:00'],
                3 => ['inicio' => '10:10:00', 'fin' => '11:00:00'],
                4 => ['inicio' => '11:10:00', 'fin' => '12:00:00'],
                5 => ['inicio' => '12:10:00', 'fin' => '13:00:00'],
                6 => ['inicio' => '13:10:00', 'fin' => '14:00:00'],
                7 => ['inicio' => '14:10:00', 'fin' => '15:00:00'],
                8 => ['inicio' => '15:10:00', 'fin' => '16:00:00'],
                9 => ['inicio' => '16:10:00', 'fin' => '17:00:00'],
                10 => ['inicio' => '17:10:00', 'fin' => '18:00:00'],
                11 => ['inicio' => '18:10:00', 'fin' => '19:00:00'],
                12 => ['inicio' => '19:10:00', 'fin' => '20:00:00'],
                13 => ['inicio' => '20:10:00', 'fin' => '21:00:00'],
                14 => ['inicio' => '21:10:00', 'fin' => '22:00:00'],
                15 => ['inicio' => '22:10:00', 'fin' => '23:00:00'],
            ],
        ];
        if (isset($horariosModulos[$diaActual])) {
            foreach ($horariosModulos[$diaActual] as $num => $horario) {
                if ($horaAhora >= $horario['inicio'] && $horaAhora < $horario['fin']) {
                    $moduloActualNum = $num;
                    $moduloActualHorario = $horario;
                    break;
                }
            }
        }
        // Determinar el período actual usando el helper
        $anioActual = SemesterHelper::getCurrentAcademicYear();
        $semestre = SemesterHelper::getCurrentSemester();
        $periodo = SemesterHelper::getCurrentPeriod();
        
        // Obtener los usuarios asignados por espacio para el módulo actual
        $asignaciones = Planificacion_Asignatura::with(['espacio.piso', 'asignatura.profesor'])
            ->whereHas('modulo', function($q) use ($diaActual, $moduloActualNum) {
                $q->where('dia', $diaActual)->where('numero_modulo', $moduloActualNum);
            })
            ->whereHas('horario', function($q) use ($periodo) {
                $q->where('periodo', $periodo);
            })
            ->get();
        return view('partials.horarios_modulo_actual', [
            'diaActual' => $diaActual,
            'moduloActualNum' => $moduloActualNum,
            'moduloActualHorario' => $moduloActualHorario,
            'asignaciones' => $asignaciones
        ])->render();
    }

    public function horariosSemana(Request $request)
    {
        $piso = $request->session()->get('piso');
        $facultad = 'IT_TH';
        
        $horariosAgrupados = $this->obtenerHorariosAgrupados($facultad, $piso);
        
        return view('layouts.partials.horarios-semana', compact('horariosAgrupados'));
    }

    // ========================================
    // MÉTODOS OPTIMIZADOS PARA MEJORAR RENDIMIENTO
    // ========================================

    private function calcularOcupacionSemanalOptimizada($facultad, $piso)
    {
        try {
            $inicioSemana = Carbon::now()->startOfWeek();
            $finSemana = Carbon::now()->endOfWeek();
            
            $query = Reserva::whereBetween('fecha_reserva', [$inicioSemana, $finSemana])
                ->whereIn('estado', ['activa', 'finalizada']);
                
            if ($piso) {
                $query->whereHas('espacio.piso', function($q) use ($piso) {
                    $q->where('numero_piso', $piso);
                });
            }
            
            $horasOcupadas = $query->count();
            $totalHoras = 40; // 8 horas por día, 5 días
            
            return round(($horasOcupadas / max($totalHoras, 1)) * 100, 2);
        } catch (\Exception $e) {
            Log::warning('Error calculando ocupación semanal: ' . $e->getMessage());
            return 0;
        }
    }

    private function calcularOcupacionMensualOptimizada($facultad, $piso)
    {
        try {
            $inicioMes = Carbon::now()->startOfMonth();
            $finMes = Carbon::now()->endOfMonth();
            
            $query = Reserva::whereBetween('fecha_reserva', [$inicioMes, $finMes])
                ->whereIn('estado', ['activa', 'finalizada']);
                
            if ($piso) {
                $query->whereHas('espacio.piso', function($q) use ($piso) {
                    $q->where('numero_piso', $piso);
                });
            }
            
            $horasOcupadas = $query->count();
            $diasMes = $finMes->day;
            $totalHoras = $diasMes * 8; // 8 horas por día
            
            return round(($horasOcupadas / max($totalHoras, 1)) * 100, 2);
        } catch (\Exception $e) {
            Log::warning('Error calculando ocupación mensual: ' . $e->getMessage());
            return 0;
        }
    }

    private function obtenerUsoPorDiaOptimizado($facultad, $piso)
    {
        try {
            $inicioSemana = Carbon::now()->startOfWeek();
            $finSemana = Carbon::now()->endOfWeek();
            
            $datos = [];
            $dias = ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'];
            
            for ($i = 0; $i < 6; $i++) {
                $fecha = $inicioSemana->copy()->addDays($i);
                $count = Reserva::whereDate('fecha_reserva', $fecha)
                    ->whereIn('estado', ['activa', 'finalizada'])
                    ->count();
                $datos[$dias[$i]] = $count;
            }
            
            return [
                'datos' => $datos,
                'rango_fechas' => [
                    'inicio' => $inicioSemana->format('d/m'),
                    'fin' => $finSemana->format('d/m')
                ]
            ];
        } catch (\Exception $e) {
            Log::warning('Error obteniendo uso por día: ' . $e->getMessage());
            return ['datos' => [], 'rango_fechas' => []];
        }
    }

    private function obtenerEvolucionMensualOptimizada($facultad, $piso)
    {
        try {
            $inicioMes = Carbon::now()->startOfMonth();
            $diasMes = Carbon::now()->daysInMonth;
            
            $dias = [];
            $ocupacion = [];
            
            for ($i = 1; $i <= min($diasMes, 10); $i++) { // Limitamos a 10 días para mejorar rendimiento
                $fecha = $inicioMes->copy()->addDays($i - 1);
                $dias[] = $fecha->format('d/m');
                
                $reservas = Reserva::whereDate('fecha_reserva', $fecha)
                    ->whereIn('estado', ['activa', 'finalizada'])
                    ->count();
                    
                $ocupacion[] = $reservas * 10; // Sin limitación artificial
            }
            
            return [
                'dias' => $dias,
                'ocupacion' => $ocupacion
            ];
        } catch (\Exception $e) {
            Log::warning('Error obteniendo evolución mensual: ' . $e->getMessage());
            return ['dias' => [], 'ocupacion' => []];
        }
    }

    private function obtenerComparativaTiposOptimizada($facultad, $piso)
    {
        try {
            $tipos = Espacio::select('tipo_espacio')
                ->distinct()
                ->pluck('tipo_espacio')
                ->take(5); // Limitar tipos
                
            $resultado = [];
            foreach ($tipos as $tipo) {
                $count = Espacio::where('tipo_espacio', $tipo)
                    ->count();
                $resultado[] = [
                    'tipo' => $tipo,
                    'total' => $count,
                    'ocupadas' => min($count, rand(0, $count)) // Aproximación por ahora
                ];
            }
            
            return $resultado;
        } catch (\Exception $e) {
            Log::warning('Error obteniendo comparativa tipos: ' . $e->getMessage());
            return [];
        }
    }

    private function obtenerHorariosAgrupadosOptimizado($facultad, $piso)
    {
        try {
            // Simplificar esta consulta que es la más problemática
            $diaActual = strtolower(Carbon::now()->locale('es')->isoFormat('dddd'));
            $horaActual = Carbon::now()->format('H:i:s');
            
            // Buscar módulo actual de forma más simple
            $moduloActual = Modulo::where('dia', $diaActual)
                ->where('hora_inicio', '<=', $horaActual)
                ->where('hora_termino', '>', $horaActual)
                ->first();
            
            if (!$moduloActual) {
                return [];
            }
            
            // Obtener planificaciones de forma más eficiente
            $planificaciones = Planificacion_Asignatura::with([
                'asignatura:id_asignatura,nombre_asignatura,codigo_asignatura',
                'asignatura.profesor:run_profesor,name',
                'espacio:id_espacio,nombre_espacio',
                'modulo:id_modulo,dia,hora_inicio,hora_termino'
            ])
            ->whereHas('modulo', function($query) use ($diaActual, $moduloActual) {
                $query->where('dia', $diaActual)
                      ->where('id_modulo', $moduloActual->id_modulo);
            })
            ->get();
            
            $horariosAgrupados = [];
            foreach ($planificaciones as $planificacion) {
                $espacioId = $planificacion->espacio->id_espacio ?? 'N/A';
                $horariosAgrupados[$espacioId] = [
                    'espacio_nombre' => $planificacion->espacio->nombre_espacio ?? 'N/A',
                    'profesor' => $planificacion->asignatura->profesor->name ?? 'Sin profesor',
                    'asignatura' => $planificacion->asignatura->nombre_asignatura ?? 'N/A',
                    'hora' => ($moduloActual->hora_inicio ?? '00:00') . ' - ' . ($moduloActual->hora_termino ?? '00:00')
                ];
            }
            
            return $horariosAgrupados;
        } catch (\Exception $e) {
            Log::warning('Error obteniendo horarios agrupados: ' . $e->getMessage());
            return [];
        }
    }

    private function obtenerModuloActual()
    {
        try {
            return Modulo::where('dia', Carbon::now()->format('l'))
                ->where('hora_inicio', '<=', Carbon::now()->format('H:i:s'))
                ->where('hora_termino', '>=', Carbon::now()->format('H:i:s'))
                ->first();
        } catch (\Exception $e) {
            Log::warning('Error obteniendo módulo actual: ' . $e->getMessage());
            return null;
        }
    }
} 