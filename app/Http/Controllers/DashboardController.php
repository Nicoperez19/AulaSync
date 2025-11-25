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
use App\Models\ClaseNoRealizada;
use App\Models\RecuperacionClase;
use App\Helpers\SemesterHelper;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
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

        // SOLO CARGAR DATOS ESENCIALES PARA LOS KPIs
        $ocupacionSemanal = [
            'diurno' => $this->calcularOcupacionSemanal($facultad, $piso, 'diurno'),
            'vespertino' => $this->calcularOcupacionSemanal($facultad, $piso, 'vespertino'),
            'total' => $this->calcularOcupacionSemanal($facultad, $piso)
        ];

        $ocupacionMensual = [
            'diurno' => $this->calcularOcupacionMensual($facultad, $piso, 'diurno'),
            'vespertino' => $this->calcularOcupacionMensual($facultad, $piso, 'vespertino'),
            'total' => $this->calcularOcupacionMensual($facultad, $piso)
        ];

        $salasOcupadas = [
            'diurno' => $this->obtenerSalasOcupadas($facultad, $piso, 'diurno'),
            'vespertino' => $this->obtenerSalasOcupadas($facultad, $piso, 'vespertino'),
            'total' => $this->obtenerSalasOcupadas($facultad, $piso)
        ];

        // Total de reservas hoy y sala más utilizada (solo queries ligeras)
        $totalReservasHoy = Reserva::whereDate('fecha_reserva', today())
            ->whereHas('espacio', function($query) {
                $query->where('tipo_espacio', 'Sala de Clases');
            })
            ->count();

        $salaMasUtilizada = Reserva::select('id_espacio', DB::raw('count(*) as total'))
            ->whereDate('fecha_reserva', '>=', Carbon::now()->subDays(7))
            ->whereHas('espacio', function($query) {
                $query->where('tipo_espacio', 'Sala de Clases');
            })
            ->groupBy('id_espacio')
            ->orderByDesc('total')
            ->with('espacio:id_espacio,nombre_espacio')
            ->first();

        // Datos para gráficos de la primera pestaña (se cargan inicialmente)
        $usoPorDia = $this->obtenerUsoPorDia($facultad, $piso);
        $evolucionMensual = $this->obtenerEvolucionMensual($facultad, $piso);

        // Datos para pestaña "Utilización" (se cargarán on-demand, pero incluimos valores vacíos)
        $comparativaTipos = [];

        // Datos para pestaña "Accesos"
        $reservasSinDevolucion = $this->obtenerReservasActivasSinDevolucion($facultad, $piso);
        $accesosActuales = Reserva::with(['profesor', 'solicitante', 'espacio.piso.facultad'])
            ->where('estado', 'activa')
            ->whereNull('hora_salida')
            ->whereHas('espacio', function($query) use ($facultad, $piso) {
                $query->whereHas('piso', function($q) use ($facultad, $piso) {
                    $q->where('id_facultad', $facultad);
                    if ($piso) {
                        $q->where('numero_piso', $piso);
                    }
                });
            })
            ->orderBy('fecha_reserva', 'desc')
            ->get();

        // Obtener módulo actual
        $moduloActual = Modulo::where('dia', Carbon::now()->format('l'))
            ->where('hora_inicio', '<=', Carbon::now()->format('H:i:s'))
            ->where('hora_termino', '>=', Carbon::now()->format('H:i:s'))
            ->first();

        // Obtener horarios agrupados del día actual
        $horariosAgrupados = $this->obtenerHorariosAgrupados($facultad, $piso);

        // Inicializar noUtilizadasDia vacío (se cargará on-demand si es necesario)
        $noUtilizadasDia = [];

        return view('layouts.dashboard', compact(
            'ocupacionSemanal',
            'ocupacionMensual',
            'salasOcupadas',
            'usoPorDia',
            'evolucionMensual',
            'comparativaTipos',
            'facultad',
            'piso',
            'pisos',
            'reservasSinDevolucion',
            'moduloActual',
            'accesosActuales',
            'totalReservasHoy',
            'salaMasUtilizada',
            'horariosAgrupados',
            'noUtilizadasDia'
        ));
    }

    /**
     * Determina si una hora está en el turno diurno o vespertino
     * Diurno: 08:00 - 19:00
     * Vespertino: 19:00 - 23:00
     * @param string $hora Hora en formato H:i:s o H:i
     * @param string $turno 'diurno' o 'vespertino' o null para todos
     * @return bool
     */
    private function esTurno($hora, $turno = null)
    {
        if ($turno === null) {
            return true; // Sin filtro de turno
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
     * Calcula las horas totales disponibles para un turno
     * @param string|null $turno 'diurno', 'vespertino' o null para todos
     * @param Carbon|null $fecha Fecha para determinar si es sábado
     * @return int|float Horas disponibles en el turno
     */
    private function horasPorTurno($turno = null, $fecha = null)
    {
        // Verificar si es sábado (clases solo hasta 13:00)
        $esSabado = $fecha ? $fecha->isSaturday() : false;
        
        if ($esSabado) {
            if ($turno === 'diurno') {
                return 5; // Sábado: 08:00 - 13:00 = 5 horas
            } elseif ($turno === 'vespertino') {
                return 0; // Sábado: no hay clases vespertinas
            }
            return 5; // Sábado total: 08:00 - 13:00 = 5 horas
        }
        
        // Días normales (lunes a viernes)
        if ($turno === 'diurno') {
            return 11; // 08:00 - 19:00 = 11 horas
        } elseif ($turno === 'vespertino') {
            return 4; // 19:00 - 23:00 = 4 horas
        }

        return 15; // Total: 08:00 - 23:00 = 15 horas
    }

    /**
     * Calcula las horas utilizadas desde planificaciones para un rango de fechas
     * @param Carbon $inicio Fecha inicial
     * @param Carbon $fin Fecha final
     * @param string|null $piso Filtro opcional por piso
     * @param string|null $tipoEspacio Filtro opcional por tipo de espacio
     * @param string|null $turno Filtro opcional por turno ('diurno', 'vespertino' o null)
     * @return float Total de horas utilizadas
     */
    private function calcularHorasDesdePlanificaciones($inicio, $fin, $piso = null, $tipoEspacio = null, $turno = null)
    {
        $periodo = SemesterHelper::getCurrentPeriod();
        $horasTotales = 0;

        // Obtener las planificaciones del período actual
        $planificaciones = Planificacion_Asignatura::with(['modulo', 'espacio'])
            ->whereHas('horario', function($q) use ($periodo) {
                $q->where('periodo', $periodo);
            })
            ->whereHas('espacio', function($query) use ($piso, $tipoEspacio) {
                if ($piso) {
                    $query->whereHas('piso', function($q) use ($piso) {
                        $q->where('numero_piso', $piso);
                    });
                }
                // Filtrar por tipo de espacio: si no se especifica, usar solo Sala de Clases
                if ($tipoEspacio) {
                    $query->where('tipo_espacio', $tipoEspacio);
                } else {
                    $query->where('tipo_espacio', 'Sala de Clases');
                }
            })
            ->get();

        // Iterar por cada día en el rango
        for ($fecha = $inicio->copy(); $fecha->lte($fin); $fecha->addDay()) {
            // Solo contar días laborales (lunes a viernes) y sábados
            if (!$fecha->isWeekday() && !$fecha->isSaturday()) {
                continue;
            }

            $diaSemana = strtolower($fecha->locale('es')->isoFormat('dddd'));

            // Filtrar planificaciones para este día
            $planificacionesDia = $planificaciones->filter(function($plan) use ($diaSemana) {
                return $plan->modulo && strtolower($plan->modulo->dia) === $diaSemana;
            });

            // Sumar horas de cada planificación
            foreach ($planificacionesDia as $plan) {
                if ($plan->modulo && $plan->modulo->hora_inicio && $plan->modulo->hora_termino) {
                    // Filtrar por turno si está especificado
                    if (!$this->esTurno($plan->modulo->hora_inicio, $turno)) {
                        continue;
                    }

                    $inicio_modulo = Carbon::parse($plan->modulo->hora_inicio);
                    $fin_modulo = Carbon::parse($plan->modulo->hora_termino);
                    $horasTotales += $inicio_modulo->diffInHours($fin_modulo, true);
                }
            }
        }

        return $horasTotales;
    }

    private function calcularOcupacionSemanal($facultad, $piso, $turno = null)
    {
        $inicioSemana = Carbon::now()->startOfWeek();
        $finSemana = Carbon::now()->endOfWeek();

        // Obtener número total de espacios disponibles
        $totalEspacios = $this->obtenerEspaciosQuery($facultad, $piso)->count();

        // Calcular total de horas disponibles considerando cada día de la semana
        $totalHoras = 0;
        for ($fecha = $inicioSemana->copy(); $fecha->lte($finSemana); $fecha->addDay()) {
            if ($fecha->isWeekday() || $fecha->isSaturday()) {
                $horasPorDia = $this->horasPorTurno($turno, $fecha);
                $totalHoras += $totalEspacios * $horasPorDia;
            }
        }

        // 1. Calcular horas desde PLANIFICACIONES (clases programadas)
        $horasPlanificaciones = $this->calcularHorasDesdePlanificaciones($inicioSemana, $finSemana, $piso, null, $turno);

        // 2. Calcular horas desde RESERVAS espontáneas (adicionales a planificación)
        $reservas = Reserva::whereBetween('fecha_reserva', [$inicioSemana, $finSemana])
            ->whereIn('estado', ['activa', 'finalizada'])
            ->whereHas('espacio', function($query) use ($piso) {
                if ($piso) {
                    $query->whereHas('piso', function($q) use ($piso) {
                        $q->where('numero_piso', $piso);
                    });
                }
                // Solo considerar Salas de Clases
                $query->where('tipo_espacio', 'Sala de Clases');
            })
            ->get();

        $horasReservas = $reservas->sum(function($reserva) use ($turno) {
            if ($reserva->hora && $reserva->hora_salida) {
                // Filtrar por turno si está especificado
                if ($turno && !$this->esTurno($reserva->hora, $turno)) {
                    return 0;
                }

                $inicio = Carbon::parse($reserva->hora);
                $fin = Carbon::parse($reserva->hora_salida);
                return $inicio->diffInHours($fin, true); // true para incluir decimales
            }
            // Si no hay hora_salida, verificar turno y asumir 1 módulo de 50 minutos
            if ($reserva->hora && $turno && !$this->esTurno($reserva->hora, $turno)) {
                return 0;
            }
            return 0.83; // 50/60 horas
        });

        // Total de horas ocupadas = planificaciones + reservas espontáneas
        $horasOcupadas = $horasPlanificaciones + $horasReservas;

        $porcentaje = $totalHoras > 0 ? round(($horasOcupadas / $totalHoras) * 100, 2) : 0;
        Log::info('Ocupación semanal calculada', [
            'turno' => $turno ?? 'todos',
            'horasPlanificaciones' => round($horasPlanificaciones, 2),
            'horasReservas' => round($horasReservas, 2),
            'horasOcupadas' => round($horasOcupadas, 2),
            'totalEspacios' => $totalEspacios,
            'totalHoras' => $totalHoras,
            'porcentaje' => $porcentaje
        ]);
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
                    // Solo considerar Salas de Clases
                    $query->where('tipo_espacio', 'Sala de Clases');
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

    private function calcularOcupacionMensual($facultad, $piso, $turno = null)
    {
        $inicioMes = Carbon::now()->startOfMonth();
        $finMes = Carbon::now()->endOfMonth();

        // Obtener número total de espacios disponibles
        $totalEspacios = $this->obtenerEspaciosQuery($facultad, $piso)->count();

        // Calcular total de horas disponibles considerando cada día del mes
        $totalHoras = 0;
        for ($dia = $inicioMes->copy(); $dia->lte($finMes); $dia->addDay()) {
            if ($dia->isWeekday() || $dia->isSaturday()) {
                $horasPorDia = $this->horasPorTurno($turno, $dia);
                $totalHoras += $totalEspacios * $horasPorDia;
            }
        }

        // 1. Calcular horas desde PLANIFICACIONES (clases programadas)
        $horasPlanificaciones = $this->calcularHorasDesdePlanificaciones($inicioMes, $finMes, $piso, null, $turno);

        // 2. Calcular horas desde RESERVAS espontáneas
        $reservas = Reserva::whereBetween('fecha_reserva', [$inicioMes, $finMes])
            ->whereIn('estado', ['activa', 'finalizada'])
            ->whereHas('espacio', function($query) use ($piso) {
                if ($piso) {
                    $query->whereHas('piso', function($q) use ($piso) {
                        $q->where('numero_piso', $piso);
                    });
                }
                // Solo considerar Salas de Clases
                $query->where('tipo_espacio', 'Sala de Clases');
            })
            ->get();

        $horasReservas = $reservas->sum(function($reserva) use ($turno) {
            if ($reserva->hora && $reserva->hora_salida) {
                // Filtrar por turno si está especificado
                if ($turno && !$this->esTurno($reserva->hora, $turno)) {
                    return 0;
                }

                $inicio = Carbon::parse($reserva->hora);
                $fin = Carbon::parse($reserva->hora_salida);
                return $inicio->diffInHours($fin, true); // true para incluir decimales
            }
            // Si no hay hora_salida, verificar turno y asumir 1 módulo de 50 minutos
            if ($reserva->hora && $turno && !$this->esTurno($reserva->hora, $turno)) {
                return 0;
            }
            return 0.83; // 50/60 horas
        });

        // Total de horas ocupadas = planificaciones + reservas espontáneas
        $horasOcupadas = $horasPlanificaciones + $horasReservas;

        $porcentaje = $totalHoras > 0 ? round(($horasOcupadas / $totalHoras) * 100, 2) : 0;
        Log::info('Ocupación mensual calculada', [
            'turno' => $turno ?? 'todos',
            'horasPlanificaciones' => round($horasPlanificaciones, 2),
            'horasReservas' => round($horasReservas, 2),
            'horasOcupadas' => round($horasOcupadas, 2),
            'totalEspacios' => $totalEspacios,
            'totalHoras' => $totalHoras,
            'porcentaje' => $porcentaje
        ]);
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

    private function calcularHorasUtilizadas($facultad, $piso, $turno = null)
    {
        $hoy = Carbon::today();

        // Obtener total de espacios para calcular horas disponibles correctamente
        $totalEspacios = $this->obtenerEspaciosQuery($facultad, $piso)->count();
        $horasPorDia = $this->horasPorTurno($turno, $hoy);
        $totalHorasDisponibles = $totalEspacios * $horasPorDia;

        // Calcular horas REALES utilizadas (no solo contar reservas)
        $reservas = Reserva::whereDate('fecha_reserva', $hoy)
            ->whereIn('estado', ['activa', 'finalizada'])
            ->whereHas('espacio', function($query) use ($piso) {
                if ($piso) {
                    $query->whereHas('piso', function($q) use ($piso) {
                        $q->where('numero_piso', $piso);
                    });
                }
                // Solo considerar Salas de Clases
                $query->where('tipo_espacio', 'Sala de Clases');
            })
            ->get();

        $horasRealmenteUtilizadas = $reservas->sum(function($reserva) use ($turno) {
            if ($reserva->hora && $reserva->hora_salida) {
                // Filtrar por turno si está especificado
                if ($turno && !$this->esTurno($reserva->hora, $turno)) {
                    return 0;
                }

                $inicio = Carbon::parse($reserva->hora);
                $fin = Carbon::parse($reserva->hora_salida);
                return $inicio->diffInHours($fin, true); // true para incluir decimales
            }
            // Si no hay hora_salida, verificar turno y asumir 1 módulo de 50 minutos
            if ($reserva->hora && $turno && !$this->esTurno($reserva->hora, $turno)) {
                return 0;
            }
            return 0.83; // 50/60 horas
        });

        return [
            'utilizadas' => round($horasRealmenteUtilizadas, 2),
            'disponibles' => $totalHorasDisponibles
        ];
    }

    private function obtenerSalasOcupadas($facultad, $piso, $turno = null)
    {
        $espaciosQuery = $this->obtenerEspaciosQuery($facultad, $piso);
        $totalEspacios = (clone $espaciosQuery)->count();

        // Contar espacios ocupados basándose en reservas activas del día actual
        // Incluye tanto reservas de profesores como reservas espontáneas
        $espaciosOcupadosQuery = Reserva::where('estado', 'activa')
            ->where('fecha_reserva', Carbon::today())
            ->whereHas('espacio', function($query) use ($facultad, $piso) {
                $query->whereHas('piso', function($q) use ($facultad, $piso) {
                    $q->where('id_facultad', $facultad);
                    if ($piso) {
                        $q->where('numero_piso', $piso);
                    }
                });
            });

        // Si se especifica turno, filtrar por hora actual
        if ($turno !== null) {
            $horaActual = Carbon::now()->format('H:i:s');

            // Solo contar ocupados si la hora actual está en el turno solicitado
            if ($this->esTurno($horaActual, $turno)) {
                $ocupados = (clone $espaciosOcupadosQuery)
                    ->distinct('id_espacio')
                    ->count('id_espacio');
            } else {
                // Si no estamos en el turno, todas están libres para ese turno
                $ocupados = 0;
            }
        } else {
            $ocupados = (clone $espaciosOcupadosQuery)
                ->distinct('id_espacio')
                ->count('id_espacio');
        }

        $libres = $totalEspacios - $ocupados;

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

            // Contar CANTIDAD DE RESERVAS (no horas)
            $cantidadReservas = Reserva::whereDate('fecha_reserva', $dia)
                ->whereIn('estado', ['activa', 'finalizada'])
                ->whereHas('espacio', function($query) use ($piso) {
                    if ($piso) {
                        $query->whereHas('piso', function($q) use ($piso) {
                            $q->where('numero_piso', $piso);
                        });
                    }
                    // Solo considerar Salas de Clases
                    $query->where('tipo_espacio', 'Sala de Clases');
                })
                ->count();

            $usoPorDia[$diasSemana[$i]] = $cantidadReservas;
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
        // Cambiar a período mensual en lugar de semanal
        $inicioMes = Carbon::now()->startOfMonth();
        $finMes = Carbon::now()->endOfMonth();

        // 1. Obtener todos los tipos de espacio distintos para el piso y facultad seleccionados
        $tiposDeEspacioQuery = Espacio::query()
            ->whereHas('piso', function($query) use ($facultad, $piso) {
                $query->where('id_facultad', $facultad);
                if ($piso) {
                    $query->where('numero_piso', $piso);
                }
            });

        $todosLosTipos = $tiposDeEspacioQuery->select('tipo_espacio')->distinct()->pluck('tipo_espacio');

        Log::info('obtenerComparativaTipos - Tipos encontrados', [
            'facultad' => $facultad,
            'piso' => $piso,
            'tipos' => $todosLosTipos->toArray(),
            'cantidad' => $todosLosTipos->count()
        ]);

        // Si no hay tipos de espacio, retornar array vacío pero válido
        if ($todosLosTipos->isEmpty()) {
            Log::warning('obtenerComparativaTipos - No se encontraron tipos de espacio');
            return [];
        }

        $result = [];
        foreach ($todosLosTipos as $tipo) {
            // Total de espacios de este tipo
            $totalEspaciosTipo = Espacio::where('tipo_espacio', $tipo)
                ->whereHas('piso', function($query) use ($facultad, $piso) {
                    $query->where('id_facultad', $facultad);
                    if ($piso) {
                        $query->where('numero_piso', $piso);
                    }
                })->count();

            // Calcular horas totales disponibles para este tipo de espacio en el mes
            // Considerando que sábados solo tienen 5 horas
            $totalHorasDisponibles = 0;
            for ($dia = $inicioMes->copy(); $dia->lte($finMes); $dia->addDay()) {
                if ($dia->isWeekday() || $dia->isSaturday()) {
                    $horasPorDia = $this->horasPorTurno(null, $dia);
                    $totalHorasDisponibles += $totalEspaciosTipo * $horasPorDia;
                }
            }

            // 1. Calcular horas desde PLANIFICACIONES para este tipo de espacio
            $horasPlanificaciones = $this->calcularHorasDesdePlanificaciones($inicioMes, $finMes, $piso, $tipo);

            // 2. Obtener reservas espontáneas del mes para este tipo de espacio
            $reservas = Reserva::join('espacios', 'reservas.id_espacio', '=', 'espacios.id_espacio')
                ->join('pisos', 'espacios.piso_id', '=', 'pisos.id')
                ->whereBetween('reservas.fecha_reserva', [$inicioMes, $finMes])
                ->whereIn('reservas.estado', ['activa', 'finalizada'])
                ->where('pisos.id_facultad', $facultad)
                ->where('espacios.tipo_espacio', $tipo);

            if ($piso) {
                $reservas->where('pisos.numero_piso', $piso);
            }

            $reservasData = $reservas->select('reservas.hora', 'reservas.hora_salida')->get();

            // Calcular horas reales desde reservas
            $horasReservas = $reservasData->sum(function($reserva) {
                if ($reserva->hora && $reserva->hora_salida) {
                    $inicio = Carbon::parse($reserva->hora);
                    $fin = Carbon::parse($reserva->hora_salida);
                    return $inicio->diffInHours($fin, true);
                }
                return 0.83; // 50 min default
            });

            // Total horas utilizadas = planificaciones + reservas espontáneas
            $horasUtilizadas = $horasPlanificaciones + $horasReservas;

            // Calcular porcentaje real basado en horas
            $porcentaje = $totalHorasDisponibles > 0 ?
                round(($horasUtilizadas / $totalHorasDisponibles) * 100) : 0;

            // Contar espacios ocupados actualmente basándose SOLO en reservas activas del día actual
            // Incluye tanto reservas de profesores como reservas espontáneas
            $espaciosOcupados = Reserva::where('estado', 'activa')
                ->where('fecha_reserva', Carbon::today())
                ->whereHas('espacio', function($query) use ($tipo, $facultad, $piso) {
                    $query->where('tipo_espacio', $tipo)
                        ->whereHas('piso', function($q) use ($facultad, $piso) {
                            $q->where('id_facultad', $facultad);
                            if ($piso) {
                                $q->where('numero_piso', $piso);
                            }
                        });
                })
                ->distinct('id_espacio')
                ->count('id_espacio');

            $result[] = [
                'nombre' => $tipo,
                'porcentaje' => $porcentaje,
                'ocupados' => $espaciosOcupados,  // Basado en porcentaje de horas
                'total' => $totalEspaciosTipo
            ];
        }

        Log::info('obtenerComparativaTipos - Resultado final', [
            'cantidad_tipos' => count($result),
            'result' => $result
        ]);

        return $result;
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

        // Obtener total de espacios para calcular el porcentaje correctamente
        $totalEspacios = $this->obtenerEspaciosQuery($facultad, $piso)->count();

        for ($i = 0; $i < 7; $i++) {
            $dia = $inicioSemana->copy()->addDays($i);
            $diasSemana[] = $dia->format('d/m');

            // Calcular horas disponibles para este día específico (considerando sábados)
            $horasPorDia = $this->horasPorTurno(null, $dia);
            $totalHorasPorDia = $totalEspacios * $horasPorDia;

            // 1. Calcular horas desde PLANIFICACIONES para este día
            $horasPlanificaciones = $this->calcularHorasDesdePlanificaciones($dia, $dia, $piso);

            // 2. Calcular horas desde RESERVAS espontáneas
            $reservas = Reserva::whereDate('fecha_reserva', $dia)
                ->whereIn('estado', ['activa', 'finalizada'])
                ->whereHas('espacio', function($query) use ($piso) {
                    if ($piso) {
                        $query->whereHas('piso', function($q) use ($piso) {
                            $q->where('numero_piso', $piso);
                        });
                    }
                    // Solo considerar Salas de Clases
                    $query->where('tipo_espacio', 'Sala de Clases');
                })
                ->get();

            $horasReservas = $reservas->sum(function($reserva) {
                if ($reserva->hora && $reserva->hora_salida) {
                    $inicio = Carbon::parse($reserva->hora);
                    $fin = Carbon::parse($reserva->hora_salida);
                    return $inicio->diffInHours($fin, true);
                }
                return 0.83; // 50 min default
            });

            // Total de horas utilizadas
            $horasUtilizadas = $horasPlanificaciones + $horasReservas;

            // Calcular porcentaje real de ocupación
            $porcentaje = $totalHorasPorDia > 0 ? round(($horasUtilizadas / $totalHorasPorDia) * 100, 2) : 0;
            $ocupacion[] = $porcentaje;
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
                // Solo considerar Salas de Clases
                $query->where('tipo_espacio', 'Sala de Clases');
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

        Log::info('obtenerHorariosAgrupados - Inicio', [
            'diaActual' => $diaActual,
            'horaActual' => $horaActual,
            'facultad' => $facultad,
            'piso' => $piso
        ]);

        // Buscar el módulo actual
        $moduloActual = Modulo::where('dia', $diaActual)
            ->where('hora_inicio', '<=', $horaActual)
            ->where('hora_termino', '>', $horaActual)
            ->first();

        // Si no hay módulo actual, buscar el siguiente módulo del día
        if (!$moduloActual) {
            $moduloActual = Modulo::where('dia', $diaActual)
                ->where('hora_inicio', '>', $horaActual)
                ->orderBy('hora_inicio', 'asc')
                ->first();
        }

        // Si aún no hay módulo, buscar el primer módulo del día
        if (!$moduloActual) {
            $moduloActual = Modulo::where('dia', $diaActual)
                ->orderBy('hora_inicio', 'asc')
                ->first();
        }

        Log::info('obtenerHorariosAgrupados - Módulo encontrado', [
            'moduloActual' => $moduloActual ? $moduloActual->id_modulo : 'null'
        ]);

        if (!$moduloActual) {
            Log::warning('obtenerHorariosAgrupados - No se encontró ningún módulo para el día actual');
            return [];
        }

        // Determinar el período actual usando el helper
        $anioActual = SemesterHelper::getCurrentAcademicYear();
        $semestre = SemesterHelper::getCurrentSemester();
        $periodo = SemesterHelper::getCurrentPeriod();

        Log::info('obtenerHorariosAgrupados - Período', [
            'anio' => $anioActual,
            'semestre' => $semestre,
            'periodo' => $periodo
        ]);

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
                // Solo considerar Salas de Clases
                $query->where('tipo_espacio', 'Sala de Clases');
            })
            ->get();

        Log::info('obtenerHorariosAgrupados - Planificaciones encontradas', [
            'cantidad' => $planificaciones->count()
        ]);

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

            // Create unique key from space and subject to prevent exact duplicates
            // while still allowing different classes in the same space
            $espacioId = $planificacion->espacio->id_espacio;
            $asignaturaId = $planificacion->asignatura->id_asignatura ?? 'unknown';
            $uniqueKey = $espacioId . '_' . $asignaturaId;

            if (!isset($horariosAgrupados[$dia][$hora]['espacios'][$uniqueKey])) {
                $horariosAgrupados[$dia][$hora]['espacios'][$uniqueKey] = [
                    'espacio' => 'Sala de clases (' . $espacioId . '), Piso ' . ($planificacion->espacio->piso->numero_piso ?? 'N/A'),
                    'asignatura' => $planificacion->asignatura->nombre_asignatura,
                    'profesor' => $planificacion->asignatura->profesor->name ?? 'No asignado',
                    'email' => $planificacion->asignatura->profesor->email ?? 'No disponible'
                ];
            }
        }

        Log::info('obtenerHorariosAgrupados - Resultado final', [
            'dias' => array_keys($horariosAgrupados),
            'total_espacios' => collect($horariosAgrupados)->flatten(2)->count()
        ]);

        return $horariosAgrupados;
    }

    private function obtenerEspaciosQuery($facultad, $piso)
    {
        return Espacio::whereHas('piso', function($query) use ($facultad, $piso) {
            $query->where('id_facultad', $facultad);
            if ($piso) {
                $query->where('numero_piso', $piso);
            }
        })
        // Solo considerar Salas de Clases (excluir Taller, Laboratorio y Sala de Estudio)
        ->where('tipo_espacio', 'Sala de Clases');
    }

    /**
     * Cargar datos de la pestaña "Utilización" por demanda
     */
    public function getUtilizacionData(Request $request)
    {
        $piso = $request->session()->get('piso');
        $facultad = 'IT_TH';

        $comparativaTipos = $this->obtenerComparativaTipos($facultad, $piso);
        $salasOcupadas = [
            'total' => $this->obtenerSalasOcupadas($facultad, $piso)
        ];

        return response()->json([
            'comparativaTipos' => $comparativaTipos,
            'salasOcupadas' => $salasOcupadas
        ]);
    }

    /**
     * Cargar datos de la pestaña "Accesos" por demanda
     */
    public function getAccesosData(Request $request)
    {
        $piso = $request->session()->get('piso');
        $facultad = 'IT_TH';

        $reservasSinDevolucion = $this->obtenerReservasActivasSinDevolucion($facultad, $piso);
        // Incluir TODAS las reservas activas: de profesores (run_profesor) y espontáneas (run_solicitante)
        $accesosActuales = Reserva::with(['profesor', 'solicitante', 'espacio.piso.facultad', 'asignatura'])
            ->where('estado', 'activa')
            ->where('fecha_reserva', Carbon::today())
            ->whereHas('espacio', function($query) use ($facultad, $piso) {
                $query->whereHas('piso', function($q) use ($facultad, $piso) {
                    $q->where('id_facultad', $facultad);
                    if ($piso) {
                        $q->where('numero_piso', $piso);
                    }
                });
                // Pestaña de Accesos muestra TODOS los tipos de espacios
            })
            ->orderBy('fecha_reserva', 'desc')
            ->orderBy('hora', 'desc')
            ->get();
            
        Log::info('getAccesosData - Datos cargados', [
            'reservasSinDevolucion' => $reservasSinDevolucion->count(),
            'accesosActuales' => $accesosActuales->count(),
            'piso' => $piso,
            'facultad' => $facultad
        ]);

        return view('partials.accesos_tab_content', compact('reservasSinDevolucion', 'accesosActuales'))->render();
    }

    public function getWidgetData(Request $request)
    {
        $piso = $request->session()->get('piso');
        $facultad = 'IT_TH';

        // Obtener datos para los KPIs - DIURNO Y VESPERTINO
        $ocupacionSemanal = [
            'diurno' => $this->calcularOcupacionSemanal($facultad, $piso, 'diurno'),
            'vespertino' => $this->calcularOcupacionSemanal($facultad, $piso, 'vespertino'),
            'total' => $this->calcularOcupacionSemanal($facultad, $piso)
        ];
        $ocupacionDiaria = $this->calcularOcupacionDiaria($facultad, $piso);
        $ocupacionMensual = [
            'diurno' => $this->calcularOcupacionMensual($facultad, $piso, 'diurno'),
            'vespertino' => $this->calcularOcupacionMensual($facultad, $piso, 'vespertino'),
            'total' => $this->calcularOcupacionMensual($facultad, $piso)
        ];
        $usuariosSinEscaneo = $this->obtenerUsuariosSinEscaneo($facultad, $piso);
        $horasUtilizadas = [
            'diurno' => $this->calcularHorasUtilizadas($facultad, $piso, 'diurno'),
            'vespertino' => $this->calcularHorasUtilizadas($facultad, $piso, 'vespertino'),
            'total' => $this->calcularHorasUtilizadas($facultad, $piso)
        ];
        $salasOcupadas = [
            'diurno' => $this->obtenerSalasOcupadas($facultad, $piso, 'diurno'),
            'vespertino' => $this->obtenerSalasOcupadas($facultad, $piso, 'vespertino'),
            'total' => $this->obtenerSalasOcupadas($facultad, $piso)
        ];

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
            'ocupacionSemanal' => [
                'diurno' => (float) $ocupacionSemanal['diurno'],
                'vespertino' => (float) $ocupacionSemanal['vespertino'],
                'total' => (float) $ocupacionSemanal['total']
            ],
            'ocupacionDiaria' => (float) $ocupacionDiaria,
            'ocupacionMensual' => [
                'diurno' => (float) $ocupacionMensual['diurno'],
                'vespertino' => (float) $ocupacionMensual['vespertino'],
                'total' => (float) $ocupacionMensual['total']
            ],
            'usuariosSinEscaneo' => (int) $usuariosSinEscaneo,
            'horasUtilizadas' => [
                'diurno' => (float) $horasUtilizadas['diurno'],
                'vespertino' => (float) $horasUtilizadas['vespertino'],
                'total' => (float) $horasUtilizadas['total']
            ],
            'salasOcupadas' => $salasOcupadas,
            'usoPorDia' => $usoPorDia,
            'comparativaTipos' => $comparativaTipos,
            'evolucionMensual' => $evolucionMensual,
            'reservasPorTipo' => $reservasPorTipo,
            'reservasCanceladas' => $reservasCanceladas,
            'horariosAgrupados' => $horariosAgrupados,
            'reservasSinDevolucion' => $reservasSinDevolucion,
            'promedioDuracion' => (float) $promedioDuracion,
            'porcentajeNoShow' => (float) $porcentajeNoShow,
            'canceladasPorTipo' => $canceladasPorTipo
        ]);
    }

    private function obtenerReservasActivasSinDevolucion($facultad, $piso)
    {
        return Reserva::with(['profesor', 'solicitante', 'espacio.piso.facultad'])
            ->where('estado', 'activa')
            ->whereNull('hora_salida')
            ->whereHas('espacio', function($query) use ($facultad, $piso) {
                $query->whereHas('piso', function($q) use ($facultad, $piso) {
                    $q->where('id_facultad', $facultad);
                    if ($piso) {
                        $q->where('numero_piso', $piso);
                    }
                });
            })
            ->latest('fecha_reserva')
            ->latest('hora')
            ->get();
            
        Log::info('Reservas sin devolución encontradas', [
            'total' => $reservas->count(),
            'facultad' => $facultad,
            'piso' => $piso
        ]);
        
        return $reservas;
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
            // NotificationController::createKeyReturnNotification(
            //     $profesor,
            //     $espacio,
            //     $horaTermino
            // );

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
                // Solo considerar Salas de Clases
                $query->where('tipo_espacio', 'Sala de Clases');
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
                // Solo considerar Salas de Clases
                $query->where('tipo_espacio', 'Sala de Clases');
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
                // Solo considerar Salas de Clases
                $query->where('tipo_espacio', 'Sala de Clases');
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
        // Construir id_modulo usando el formato correcto (ej: "LU.5")
        $prefijosDias = ['DO', 'LU', 'MA', 'MI', 'JU', 'VI', 'SA'];
        $diasArray = ['domingo', 'lunes', 'martes', 'miercoles', 'jueves', 'viernes', 'sabado'];
        $indexDia = array_search($diaActual, $diasArray);
        $prefijo = $indexDia !== false ? $prefijosDias[$indexDia] : 'LU';
        $idModulo = $prefijo . '.' . $moduloActualNum;
        
        $asignaciones = Planificacion_Asignatura::with(['espacio.piso', 'asignatura.profesor'])
            ->where('id_modulo', $idModulo)
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

            // Obtener número total de espacios disponibles
            $totalEspacios = $this->obtenerEspaciosQuery($facultad, $piso)->count();

            // Calcular total de horas disponibles: espacios × días × horas por día
            $diasLaborales = 5; // Lunes a viernes
            $horasPorDia = 15;
            $totalHoras = $totalEspacios * $diasLaborales * $horasPorDia;

            $query = Reserva::whereBetween('fecha_reserva', [$inicioSemana, $finSemana])
                ->whereIn('estado', ['activa', 'finalizada']);

            if ($piso) {
                $query->whereHas('espacio.piso', function($q) use ($piso) {
                    $q->where('numero_piso', $piso);
                });
            }

            // Calcular horas REALES utilizadas
            $reservas = $query->get();
            $horasOcupadas = $reservas->sum(function($reserva) {
                if ($reserva->hora && $reserva->hora_salida) {
                    $inicio = Carbon::parse($reserva->hora);
                    $fin = Carbon::parse($reserva->hora_salida);
                    return $inicio->diffInHours($fin, true);
                }
                return 0.83; // 50 min default
            });

            return $totalHoras > 0 ? round(($horasOcupadas / $totalHoras) * 100, 2) : 0;
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

            // Obtener número total de espacios disponibles
            $totalEspacios = $this->obtenerEspaciosQuery($facultad, $piso)->count();

            // Calcular días laborales del mes
            $diasLaborales = 0;
            for ($dia = $inicioMes->copy(); $dia->lte($finMes); $dia->addDay()) {
                if ($dia->isWeekday()) {
                    $diasLaborales++;
                }
            }

            // Calcular total de horas disponibles: espacios × días laborales × horas por día
            $horasPorDia = 15;
            $totalHoras = $totalEspacios * $diasLaborales * $horasPorDia;

            $query = Reserva::whereBetween('fecha_reserva', [$inicioMes, $finMes])
                ->whereIn('estado', ['activa', 'finalizada']);

            if ($piso) {
                $query->whereHas('espacio.piso', function($q) use ($piso) {
                    $q->where('numero_piso', $piso);
                });
            }

            // Calcular horas REALES utilizadas
            $reservas = $query->get();
            $horasOcupadas = $reservas->sum(function($reserva) {
                if ($reserva->hora && $reserva->hora_salida) {
                    $inicio = Carbon::parse($reserva->hora);
                    $fin = Carbon::parse($reserva->hora_salida);
                    return $inicio->diffInHours($fin, true);
                }
                return 0.83; // 50 min default
            });

            return $totalHoras > 0 ? round(($horasOcupadas / $totalHoras) * 100, 2) : 0;
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

                // Calcular horas REALES utilizadas
                $reservas = Reserva::whereDate('fecha_reserva', $fecha)
                    ->whereIn('estado', ['activa', 'finalizada'])
                    ->get();

                $horasUtilizadas = $reservas->sum(function($reserva) {
                    if ($reserva->hora && $reserva->hora_salida) {
                        $inicio = Carbon::parse($reserva->hora);
                        $fin = Carbon::parse($reserva->hora_salida);
                        return $inicio->diffInHours($fin, true);
                    }
                    return 0.83;
                });

                $datos[$dias[$i]] = round($horasUtilizadas, 2);
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

            $totalEspacios = $this->obtenerEspaciosQuery($facultad, $piso)->count();
            $horasPorDia = 15;
            $totalHorasPorDia = $totalEspacios * $horasPorDia;

            $dias = [];
            $ocupacion = [];

            for ($i = 1; $i <= min($diasMes, 10); $i++) { // Limitamos a 10 días para mejorar rendimiento
                $fecha = $inicioMes->copy()->addDays($i - 1);
                $dias[] = $fecha->format('d/m');

                // Calcular horas REALES utilizadas
                $reservas = Reserva::whereDate('fecha_reserva', $fecha)
                    ->whereIn('estado', ['activa', 'finalizada'])
                    ->get();

                $horasUtilizadas = $reservas->sum(function($reserva) {
                    if ($reserva->hora && $reserva->hora_salida) {
                        $inicio = Carbon::parse($reserva->hora);
                        $fin = Carbon::parse($reserva->hora_salida);
                        return $inicio->diffInHours($fin, true);
                    }
                    return 0.83;
                });

                // Calcular porcentaje real de ocupación
                $porcentaje = $totalHorasPorDia > 0 ? round(($horasUtilizadas / $totalHorasPorDia) * 100, 2) : 0;
                $ocupacion[] = $porcentaje;
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

    /**
     * Calcular horas utilizadas de una reserva individual
     * Método auxiliar para evitar duplicación de código
     */
    private function calcularHorasReserva($reserva)
    {
        if ($reserva->hora && $reserva->hora_salida) {
            $inicio = Carbon::parse($reserva->hora);
            $fin = Carbon::parse($reserva->hora_salida);
            return $inicio->diffInHours($fin, true); // true para incluir decimales
        }
        // Si no hay hora_salida, asumir 1 módulo de 50 minutos
        return 0.83; // 50/60 horas
    }

    public function getClasesNoRealizadasData(Request $request)
    {
        $piso = $request->session()->get('piso');
        $facultad = 'IT_TH';
        $mes = now()->month;
        $anio = now()->year;

        // Obtener clases no realizadas del mes actual
        $clasesNoRealizadas = ClaseNoRealizada::whereMonth('fecha_clase', $mes)
            ->whereYear('fecha_clase', $anio)
            ->get();

        // Obtener clases recuperadas del mes
        $clasesRecuperadas = RecuperacionClase::whereMonth('created_at', $mes)
            ->whereYear('created_at', $anio)
            ->where('estado', 'recuperada')
            ->get();

        // Agrupar por día para el gráfico de barras
        $diasDelMes = [];
        $inicio = Carbon::create($anio, $mes, 1);
        $fin = $inicio->copy()->endOfMonth();

        for ($fecha = $inicio->copy(); $fecha->lte($fin); $fecha->addDay()) {
            if ($fecha->isWeekday() || $fecha->isSaturday()) {
                $dia = $fecha->format('d/m');
                $diasDelMes[$dia] = [
                    'realizadas' => 0,
                    'no_realizadas' => 0
                ];
            }
        }

        // Contar clases por día
        $planificacionesMes = Planificacion_Asignatura::whereHas('horario', function($q) use ($anio, $mes) {
            $q->where('periodo', Carbon::create($anio, $mes, 1)->format('Y-m'));
        })->get();

        foreach ($planificacionesMes as $plan) {
            if ($plan->modulo) {
                $dia = $plan->modulo->dia;
                // Encontrar todas las fechas con este día de semana en el mes
                for ($fecha = $inicio->copy(); $fecha->lte($fin); $fecha->addDay()) {
                    if (strtolower($fecha->locale('es')->isoFormat('dddd')) === strtolower($dia)) {
                        $diaFormato = $fecha->format('d/m');
                        if (isset($diasDelMes[$diaFormato])) {
                            $diasDelMes[$diaFormato]['realizadas']++;
                        }
                    }
                }
            }
        }

        // Contar clases no realizadas por día
        foreach ($clasesNoRealizadas as $clase) {
            $diaFormato = $clase->fecha_clase->format('d/m');
            if (isset($diasDelMes[$diaFormato])) {
                $diasDelMes[$diaFormato]['no_realizadas']++;
                $diasDelMes[$diaFormato]['realizadas']--;
            }
        }

        // Calcular totales y estadísticas
        $totalPlaneadas = $planificacionesMes->count();
        $totalNoRealizadas = $clasesNoRealizadas->count();
        $totalRealizadas = $totalPlaneadas - $totalNoRealizadas;
        $totalRecuperadas = $clasesRecuperadas->count();
        $porcentajeNoRealizadas = $totalPlaneadas > 0 ? round(($totalNoRealizadas / $totalPlaneadas) * 100, 1) : 0;
        $porcentajeRealizadas = $totalPlaneadas > 0 ? round(($totalRealizadas / $totalPlaneadas) * 100, 1) : 0;

        // Preparar arrays para el gráfico
        $diasLabels = array_keys($diasDelMes);
        $datosRealizadas = array_values(array_map(function($d) { return $d['realizadas']; }, $diasDelMes));
        $datosNoRealizadas = array_values(array_map(function($d) { return $d['no_realizadas']; }, $diasDelMes));

        return view('partials.clases_no_realizadas_tab_content', compact(
            'diasDelMes',
            'totalRealizadas',
            'totalNoRealizadas',
            'totalRecuperadas',
            'porcentajeRealizadas',
            'porcentajeNoRealizadas',
            'diasLabels',
            'datosRealizadas',
            'datosNoRealizadas',
            'mes',
            'anio'
        ))->render();
    }
}
