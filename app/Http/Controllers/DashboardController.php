<?php

namespace App\Http\Controllers;

use App\Models\Espacio;
use App\Models\Reserva;
use App\Models\User;
use App\Models\Asignatura;
use App\Models\Planificacion_Asignatura;
use App\Models\Modulo;
use App\Models\Piso;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Models\Mapa;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        // Obtener el piso de la sesión o request
        $piso = $request->session()->get('piso');
        $facultad = 'IT_TH'; // Siempre usar IT_TH como facultad

        // Obtener los pisos disponibles para la facultad
        $pisos = Piso::whereHas('facultad', function($query) {
            $query->where('id_facultad', 'IT_TH')
                  ->whereHas('sede', function($q) {
                      $q->where('id_sede', 'TH');
                  });
        })
        ->orderBy('numero_piso')
        ->get();

        // Obtener datos para los KPIs
        $ocupacionSemanal = $this->calcularOcupacionSemanal($facultad, $piso);
        $ocupacionDiaria = $this->calcularOcupacionDiaria($facultad, $piso);
        $ocupacionMensual = $this->calcularOcupacionMensual($facultad, $piso);
        $usuariosSinEscaneo = $this->obtenerUsuariosSinEscaneo($facultad, $piso);
        $horasUtilizadas = $this->calcularHorasUtilizadas($facultad, $piso);
        $salasOcupadas = $this->obtenerSalasOcupadas($facultad, $piso);

        // Obtener datos para los gráficos
        $usoPorDia = $this->obtenerUsoPorDia($facultad, $piso);
        $topSalas = $this->obtenerTopSalas($facultad, $piso);
        $topAsignaturas = $this->obtenerTopAsignaturas($facultad, $piso);
        $comparativaTipos = $this->obtenerComparativaTipos($facultad, $piso);
        $evolucionMensual = $this->obtenerEvolucionMensual($facultad, $piso);

        // Obtener datos para las tablas
        $reservasCanceladas = $this->obtenerReservasCanceladas($facultad, $piso);
        $horariosAgrupados = $this->obtenerHorariosAgrupados($facultad, $piso);
        $reservasSinDevolucion = $this->obtenerReservasActivasSinDevolucion($facultad, $piso);
        $promedioDuracion = $this->obtenerPromedioDuracionReserva($facultad, $piso);
        $porcentajeNoShow = $this->obtenerPorcentajeNoShow($facultad, $piso);
        $canceladasPorTipo = $this->obtenerCanceladasPorTipoSala($facultad, $piso);

        $horariosPorTipoDiaModulo = $this->obtenerOcupacionPorTipoDiaModulo($facultad, $piso);

        return view('layouts.dashboard', compact(
            'ocupacionSemanal',
            'ocupacionDiaria',
            'ocupacionMensual',
            'usuariosSinEscaneo',
            'horasUtilizadas',
            'salasOcupadas',
            'usoPorDia',
            'topSalas',
            'topAsignaturas',
            'comparativaTipos',
            'evolucionMensual',
            'reservasCanceladas',
            'horariosAgrupados',
            'facultad',
            'piso',
            'pisos',
            'reservasSinDevolucion',
            'promedioDuracion',
            'porcentajeNoShow',
            'canceladasPorTipo',
            'horariosPorTipoDiaModulo'
        ));
    }

    private function calcularOcupacionSemanal($facultad, $piso)
    {
        $inicioSemana = Carbon::now()->startOfWeek();
        $finSemana = Carbon::now()->endOfWeek();
        
        $totalHoras = 40; // 8 horas por día, 5 días
        $horasOcupadas = Reserva::whereBetween('fecha_reserva', [$inicioSemana, $finSemana])
            ->where('estado', 'activa')
            ->whereHas('espacio', function($query) use ($piso) {
                if ($piso) {
                    $query->whereHas('piso', function($q) use ($piso) {
                        $q->where('numero_piso', $piso);
                    });
                }
            })
            ->count();

        return round(($horasOcupadas / $totalHoras) * 100, 2);
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
            ->where('estado', 'activa')
            ->whereHas('espacio', function($query) use ($piso) {
                if ($piso) {
                    $query->whereHas('piso', function($q) use ($piso) {
                        $q->where('numero_piso', $piso);
                    });
                }
            })
            ->count();

        return round(($horasOcupadas / $totalHoras) * 100, 2);
    }

    private function obtenerUsuariosSinEscaneo($facultad, $piso)
    {
        $hoy = Carbon::today();
        
        // Obtener los espacios de la facultad y piso especificados
        $espacios = $this->obtenerEspaciosQuery($facultad, $piso)->pluck('id_espacio');
        
        // Obtener usuarios que no tienen reservas hoy en los espacios especificados
        return User::whereDoesntHave('reservas', function($query) use ($hoy, $espacios) {
            $query->whereDate('fecha_reserva', $hoy)
                  ->whereIn('id_espacio', $espacios);
        })->count();
    }

    private function calcularHorasUtilizadas($facultad, $piso)
    {
        $hoy = Carbon::today();
        $horasUtilizadas = Reserva::whereDate('fecha_reserva', $hoy)
            ->where('estado', 'activa')
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
        $diasSemana = ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'];
        $usoPorDia = [];
        
        for ($i = 0; $i < 6; $i++) {
            $dia = $inicioSemana->copy()->addDays($i);
            $usoPorDia[$diasSemana[$i]] = Reserva::whereDate('fecha_reserva', $dia)
                ->where('estado', 'activa')
                ->whereHas('espacio', function($query) use ($piso) {
                    if ($piso) {
                        $query->whereHas('piso', function($q) use ($piso) {
                            $q->where('numero_piso', $piso);
                        });
                    }
                })
                ->count();
        }
        
        return $usoPorDia;
    }

    private function obtenerTopSalas($facultad, $piso)
    {
        $inicioSemana = Carbon::now()->startOfWeek();
        $finSemana = Carbon::now()->endOfWeek();
        
        return Espacio::withCount(['reservas' => function($query) use ($inicioSemana, $finSemana) {
            $query->where('estado', 'activa')
                  ->whereBetween('fecha_reserva', [$inicioSemana, $finSemana]);
        }])
        ->whereHas('piso', function($query) use ($piso) {
            if ($piso) {
                $query->where('numero_piso', $piso);
            }
        })
        ->orderBy('reservas_count', 'desc')
        ->take(3)
        ->get()
        ->map(function($espacio) {
            return [
                'nombre' => $espacio->nombre_espacio,
                'uso' => $espacio->reservas_count
            ];
        });
    }

    private function obtenerTopAsignaturas($facultad, $piso)
    {
        $inicioSemana = Carbon::now()->startOfWeek();
        $finSemana = Carbon::now()->endOfWeek();
        $diasSemana = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
        
        return Asignatura::withCount(['planificaciones' => function($query) use ($piso, $diasSemana) {
            $query->whereHas('modulo', function($q) use ($diasSemana) {
                $q->whereIn('dia', $diasSemana);
            })
            ->whereHas('espacio', function($q) use ($piso) {
                if ($piso) {
                    $q->whereHas('piso', function($q) use ($piso) {
                        $q->where('numero_piso', $piso);
                    });
                }
            });
        }])
        ->orderBy('planificaciones_count', 'desc')
        ->take(5)
        ->get()
        ->map(function($asignatura) {
            return [
                'nombre' => $asignatura->nombre_asignatura,
                'uso' => $asignatura->planificaciones_count,
                'profesor' => $asignatura->user->name ?? 'No asignado'
            ];
        });
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

        // 2. Obtener las horas reservadas para los tipos de espacio encontrados
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
            ->select('espacios.tipo_espacio', DB::raw('count(*) as horas_reservadas'))
            ->groupBy('espacios.tipo_espacio')
            ->pluck('horas_reservadas', 'tipo_espacio');

        $totalHorasReservadas = $reservasPorTipo->sum();

        // 3. Mapear todos los tipos de espacio, asignando 0 a los que no tienen reservas
        return $todosLosTipos->map(function($tipo) use ($reservasPorTipo, $totalHorasReservadas) {
            $horas = $reservasPorTipo->get($tipo, 0);
            $porcentaje = ($totalHorasReservadas > 0) ? ($horas / $totalHorasReservadas) * 100 : 0;
            
            return [
                'tipo' => $tipo,
                'total' => round($porcentaje)
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
        
        return Reserva::with(['user', 'espacio'])
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
        $diasSemana = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
        $diasTraducidos = [
            'Monday' => 'Lunes',
            'Tuesday' => 'Martes', 
            'Wednesday' => 'Miércoles',
            'Thursday' => 'Jueves',
            'Friday' => 'Viernes',
            'Saturday' => 'Sábado'
        ];
        
        $planificaciones = Planificacion_Asignatura::with(['asignatura.user', 'espacio', 'modulo'])
            ->whereHas('modulo', function($query) use ($diasSemana) {
                $query->whereIn('dia', $diasSemana);
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
        
        foreach ($planificaciones as $planificacion) {
            $dia = $diasTraducidos[$planificacion->modulo->dia] ?? $planificacion->modulo->dia;
            $hora = $planificacion->modulo->hora_inicio . ' - ' . $planificacion->modulo->hora_termino;
            
            if (!isset($horariosAgrupados[$dia])) {
                $horariosAgrupados[$dia] = [];
            }
            
            if (!isset($horariosAgrupados[$dia][$hora])) {
                $horariosAgrupados[$dia][$hora] = [];
            }
            
            $horariosAgrupados[$dia][$hora][] = [
                'espacio' => $planificacion->espacio->nombre_espacio . ' (ID: ' . $planificacion->espacio->id_espacio . ')',
                'asignatura' => $planificacion->asignatura->nombre_asignatura,
                'profesor' => $planificacion->asignatura->user->name ?? 'No asignado',
                'email' => $planificacion->asignatura->user->email ?? 'No disponible'
            ];
        }
        
        // Ordenar los arrays por día y hora
        ksort($horariosAgrupados);
        foreach ($horariosAgrupados as &$horarios) {
            ksort($horarios);
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

    public function setPiso(Request $request)
    {
        $request->validate([
            'piso' => 'nullable|integer'
        ]);

        session(['piso' => $request->piso]);

        return response()->json(['success' => true]);
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
        $topSalas = $this->obtenerTopSalas($facultad, $piso);
        $topAsignaturas = $this->obtenerTopAsignaturas($facultad, $piso);
        $comparativaTipos = $this->obtenerComparativaTipos($facultad, $piso);
        $evolucionMensual = $this->obtenerEvolucionMensual($facultad, $piso);

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
            'topSalas' => $topSalas,
            'topAsignaturas' => $topAsignaturas,
            'comparativaTipos' => $comparativaTipos,
            'evolucionMensual' => $evolucionMensual,
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
        $inicioSemana = Carbon::now()->startOfWeek();
        $finSemana = Carbon::now()->endOfWeek();
        
        return Reserva::with(['user', 'espacio'])
            ->where('estado', 'activa')
            ->whereNull('hora_salida')
            ->whereBetween('fecha_reserva', [$inicioSemana, $finSemana])
            ->whereHas('espacio', function ($query) use ($facultad, $piso) {
                $query->whereHas('piso', function ($q) use ($facultad, $piso) {
                    $q->where('id_facultad', $facultad);
                    if ($piso) {
                        $q->where('numero_piso', $piso);
                    }
                });
            })
            ->latest('fecha_reserva')
            ->latest('hora')
            ->get();
    }

    public function getKeyReturnNotifications()
    {
        $now = Carbon::now();
        $timeLimit = $now->copy()->addMinutes(10);

        // Obtener planificaciones que terminan en los próximos 10 minutos
        $planificaciones = Planificacion_Asignatura::with(['modulo', 'espacio', 'asignatura.user'])
            ->whereHas('modulo', function ($query) use ($now, $timeLimit) {
                $query->where('dia', strtolower($now->locale('es')->isoFormat('dddd')))
                      ->whereTime('hora_termino', '>', $now->format('H:i:s'))
                      ->whereTime('hora_termino', '<=', $timeLimit->format('H:i:s'));
            })
            ->whereHas('espacio', function ($query) {
                // Solo incluir espacios que estén realmente ocupados
                $query->where('estado', 'Ocupado');
            })
            ->get();

        $notifications = [];
        
        foreach ($planificaciones as $plan) {
            $profesor = $plan->asignatura->user->name ?? 'Profesor no asignado';
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
        $diasSemana = [
            'Monday' => 'Lunes',
            'Tuesday' => 'Martes',
            'Wednesday' => 'Miércoles',
            'Thursday' => 'Jueves',
            'Friday' => 'Viernes',
            'Saturday' => 'Sábado',
        ];
        $tiposEspacio = \App\Models\Espacio::whereHas('piso', function($q) use ($facultad, $piso) {
            $q->where('id_facultad', $facultad);
            if ($piso) $q->where('numero_piso', $piso);
        })->select('tipo_espacio')->distinct()->pluck('tipo_espacio');

        $modulos = \App\Models\Modulo::all()->groupBy('dia');
        $resultado = [];

        foreach ($tiposEspacio as $tipo) {
            foreach ($diasSemana as $diaEN => $diaES) {
                // Obtener todos los módulos de ese día
                $modulosDia = $modulos->get($diaEN, collect());
                foreach ($modulosDia as $modulo) {
                    // Total de espacios de este tipo
                    $totalEspacios = \App\Models\Espacio::where('tipo_espacio', $tipo)
                        ->whereHas('piso', function($q) use ($facultad, $piso) {
                            $q->where('id_facultad', $facultad);
                            if ($piso) $q->where('numero_piso', $piso);
                        })->count();
                    if ($totalEspacios === 0) {
                        $resultado[$tipo][$diaES][$modulo->id_modulo] = 0;
                        continue;
                    }
                    // Planificaciones activas para ese tipo, día y módulo
                    $ocupados = \App\Models\Planificacion_Asignatura::where('id_modulo', $modulo->id_modulo)
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
} 