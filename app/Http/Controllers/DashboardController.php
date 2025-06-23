<?php

namespace App\Http\Controllers;

use App\Models\Espacio;
use App\Models\Reserva;
use App\Models\User;
use App\Models\Asignatura;
use App\Models\PlanificacionAsignatura;
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
        $horariosPorDia = $this->obtenerHorariosPorDia($facultad, $piso);

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
            'horariosPorDia',
            'facultad',
            'piso',
            'pisos'
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
            $espaciosOcupados = PlanificacionAsignatura::where('id_modulo', $modulo->id_modulo)
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
        $hoy = Carbon::today();
        $diaSemana = $hoy->format('l');
        $horaActual = Carbon::now()->format('H:i:s');
        
        $totalEspacios = $this->obtenerEspaciosQuery($facultad, $piso)->count();
        
        $moduloActual = Modulo::where('dia', $diaSemana)
            ->where('hora_inicio', '<=', $horaActual)
            ->where('hora_termino', '>=', $horaActual)
            ->first();
        
        if ($moduloActual) {
            $espaciosOcupados = PlanificacionAsignatura::where('id_modulo', $moduloActual->id_modulo)
                ->whereHas('espacio', function($query) use ($piso) {
                    if ($piso) {
                        $query->whereHas('piso', function($q) use ($piso) {
                            $q->where('numero_piso', $piso);
                        });
                    }
                })
                ->count();
        } else {
            $espaciosOcupados = 0;
        }
        
        return [
            'ocupadas' => $espaciosOcupados,
            'libres' => $totalEspacios - $espaciosOcupados,
            'modulo_actual' => $moduloActual ? $moduloActual->hora_inicio . ' - ' . $moduloActual->hora_termino : 'Sin módulo actual'
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
        return Espacio::withCount(['reservas' => function($query) {
            $query->where('estado', 'activa');
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
        return Asignatura::withCount(['planificaciones' => function($query) use ($piso) {
            $query->whereHas('modulo', function($q) {
                $q->where('dia', Carbon::now()->format('l'));
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
        return Espacio::select('tipo_espacio', DB::raw('count(*) as total'))
            ->whereHas('piso', function($query) use ($piso) {
                if ($piso) {
                    $query->where('numero_piso', $piso);
                }
            })
            ->groupBy('tipo_espacio')
            ->get()
            ->map(function($item) {
                return [
                    'tipo' => $item->tipo_espacio,
                    'total' => $item->total
                ];
            });
    }

    private function obtenerEvolucionMensual($facultad, $piso)
    {
        $inicioMes = Carbon::now()->startOfMonth();
        $diasMes = [];
        $ocupacion = [];
        
        for ($i = 0; $i < 30; $i++) {
            $dia = $inicioMes->copy()->addDays($i);
            $diasMes[] = $dia->format('Y-m-d');
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
            'dias' => $diasMes,
            'ocupacion' => $ocupacion
        ];
    }

    private function obtenerReservasCanceladas($facultad, $piso)
    {
        return Reserva::with(['user', 'espacio'])
            ->where('estado', 'finalizada')
            ->whereDate('fecha_reserva', Carbon::today())
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

    private function obtenerHorariosPorDia($facultad, $piso)
    {
        $hoy = Carbon::today();
        $diaSemana = $hoy->format('l');
        
        return PlanificacionAsignatura::with(['asignatura.user', 'espacio', 'modulo', 'horario'])
            ->whereHas('modulo', function($query) use ($diaSemana) {
                $query->where('dia', $diaSemana);
            })
            ->whereHas('espacio', function($query) use ($piso) {
                if ($piso) {
                    $query->whereHas('piso', function($q) use ($piso) {
                        $q->where('numero_piso', $piso);
                    });
                }
            })
            ->get()
            ->map(function($planificacion) {
                return [
                    'modulo' => $planificacion->modulo->hora_inicio . ' - ' . $planificacion->modulo->hora_termino,
                    'dia' => $planificacion->modulo->dia,
                    'asignatura' => $planificacion->asignatura->nombre_asignatura,
                    'espacio' => $planificacion->espacio->nombre_espacio,
                    'usuario' => $planificacion->asignatura->user->name ?? 'No asignado'
                ];
            });
    }

    private function obtenerEspaciosQuery($facultad, $piso)
    {
        $query = Espacio::query();
        
        // Filtrar por la facultad IT_TH específicamente
        $query->whereHas('piso.facultad', function($q) {
            $q->where('id_facultad', 'IT_TH');
        });
        
        if ($piso) {
            $query->whereHas('piso', function($q) use ($piso) {
                $q->where('numero_piso', $piso);
            });
        }
        
        return $query;
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
        $horariosPorDia = $this->obtenerHorariosPorDia($facultad, $piso);

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
            'horariosPorDia' => $horariosPorDia
        ]);
    }
} 