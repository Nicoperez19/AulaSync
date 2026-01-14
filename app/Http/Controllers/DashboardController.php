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
use App\Models\Tenant;

class DashboardController extends Controller
{
    /**
     * Calcula los módulos reales de uso basado en hora de inicio y salida.
     * Considera:
     * - Mínimo 10 minutos para contar como uso válido (evita pruebas/errores)
     * - Cada módulo es de 50 minutos efectivos + 10 min de break
     * - Redondea hacia arriba si se usa más del 60% del módulo
     * 
     * @param string|null $horaInicio Hora de inicio (formato H:i:s o H:i)
     * @param string|null $horaSalida Hora de salida real (formato H:i:s o H:i)
     * @param int|null $modulosTeoricos Campo modulos de la reserva (fallback)
     * @return float Número de módulos calculados
     */
    private function calcularModulosReales($horaInicio, $horaSalida, $modulosTeoricos = null)
    {
        return $this->calcularModulosRealesPublic($horaInicio, $horaSalida, $modulosTeoricos);
    }
    
    /**
     * Versión pública del cálculo de módulos reales (para uso en closures)
     */
    public function calcularModulosRealesPublic($horaInicio, $horaSalida, $modulosTeoricos = null)
    {
        // Si no hay hora de salida, usar el valor teórico o 1 por defecto
        if (!$horaSalida || !$horaInicio) {
            return $modulosTeoricos ?? 1;
        }
        
        try {
            $inicio = Carbon::parse($horaInicio);
            $fin = Carbon::parse($horaSalida);
            
            // Detectar si hora_salida es menor que hora (error de datos o cruce de medianoche)
            // En estos casos, usar el valor teórico como fallback
            if ($fin->lt($inicio)) {
                return $modulosTeoricos ?? 1;
            }
            
            $minutosReales = $inicio->diffInMinutes($fin);
            
            // Si duró menos de 10 minutos, no contar como módulo válido
            // (probablemente fue una prueba o error)
            if ($minutosReales < 10) {
                return 0;
            }
            
            // Limitar a máximo 15 módulos por reserva (un día completo)
            // Si el cálculo da más, probablemente hay un error en los datos
            $modulosCalculados = $minutosReales / 50;
            if ($modulosCalculados > 15) {
                return $modulosTeoricos ?? 1;
            }
            
            // Redondear de forma inteligente:
            // - Menos de 0.2 módulos (10 min) = 0 (ya filtrado arriba)
            // - 0.2 a 0.7 módulos (10-35 min) = 0.5 módulos
            // - 0.7+ módulos (35+ min) = redondear al entero más cercano
            $parteDecimal = $modulosCalculados - floor($modulosCalculados);
            
            if ($parteDecimal < 0.3) {
                return floor($modulosCalculados);
            } elseif ($parteDecimal < 0.7) {
                return floor($modulosCalculados) + 0.5;
            } else {
                return ceil($modulosCalculados);
            }
            
        } catch (\Exception $e) {
            // Si hay error parseando las horas, usar fallback
            return $modulosTeoricos ?? 1;
        }
    }

    /**
     * Obtener y configurar el tenant actual para operaciones AJAX
     * Garantiza que Reserva::on('tenant') apunte a la BD correcta
     */
    private function ensureTenantContext()
    {
        $tenant = null;
        
        // Opción 1: Obtener de sesión
        if (session()->has('tenant_id')) {
            $tenant = Tenant::find(session('tenant_id'));
        }
        
        // Opción 2: Si no hay sesión, obtener el primer tenant activo
        if (!$tenant) {
            $tenant = Tenant::where('is_active', true)->first();
            if ($tenant) {
                $tenant->makeCurrent();
                session(['tenant_id' => $tenant->id]);
            }
        }
        
        return $tenant;
    }

    public function index(Request $request)
    {
        // Obtener el piso de la sesión o request
        $piso = $request->session()->get('piso');
        
        // Obtener contexto de tenant
        $tenant = Tenant::current();
        $sedeId = $tenant ? $tenant->sede_id : 'TH';
        $facultad = 'IT_' . $sedeId;

        // Obtener los pisos disponibles para la facultad
        $pisos = Piso::whereHas('facultad', function($query) use ($sedeId, $facultad) {
            $query->where('id_facultad', $facultad)
                  ->where('id_sede', $sedeId);
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

        // Obtener TODOS los espacios ocupados (incluyendo laboratorios, talleres, etc.) para el gráfico de torta
        $espaciosOcupadosTotal = $this->obtenerEspaciosOcupadosTotal($facultad, $piso);

        // Total de reservas hoy y sala más utilizada (solo queries ligeras)
        $totalReservasHoy = Reserva::whereDate('fecha_reserva', today())
            ->whereHas('espacio', function($query) {
                $query->where('tipo_espacio', 'Sala de Clases');
            })
            ->count();

        // Sala con más reservas hoy
        $salaMasReservas = Reserva::select('id_espacio', DB::raw('count(*) as total'))
            ->whereDate('fecha_reserva', today())
            ->whereHas('espacio', function($query) {
                $query->where('tipo_espacio', 'Sala de Clases');
            })
            ->groupBy('id_espacio')
            ->orderByDesc('total')
            ->with('espacio:id_espacio,nombre_espacio')
            ->first();

        // Sala con mayor ocupación (módulos utilizados / 15)
        $salaMasUtilizada = Reserva::select('id_espacio', DB::raw('count(*) as total'))
            ->whereDate('fecha_reserva', today())
            ->whereHas('espacio', function($query) {
                $query->where('tipo_espacio', 'Sala de Clases');
            })
            ->groupBy('id_espacio')
            ->with('espacio:id_espacio,nombre_espacio')
            ->get()
            ->map(function($item) {
                $item->ocupacion_modulos = ($item->total / 15) * 100;
                return $item;
            })
            ->sortByDesc('ocupacion_modulos')
            ->first();

        // Datos para gráficos de la primera pestaña (se cargan inicialmente)
        $usoPorDia = $this->obtenerUsoPorDia($facultad, $piso);
        $salasUtilizadasPorDia = $this->obtenerSalasUtilizadasPorDia($facultad, $piso);
        $ocupacionPorDia = $this->obtenerOcupacionPorDia($facultad, $piso);
        $salasPorTipoPorDia = $this->obtenerSalasPorTipoPorDia($facultad, $piso);
        $ocupacionPorTurno = $this->obtenerOcupacionPorTurno($facultad, $piso);
        $ocupacionPorTipo = $this->obtenerOcupacionPorTipo($facultad, $piso);
        $ocupacionPorSala = $this->obtenerOcupacionPorSala($facultad, $piso);
        $disponibilidadSalas = $this->obtenerDisponibilidadSalas($facultad, $piso);
        $evolucionMensual = $this->obtenerEvolucionMensual($facultad, $piso);

        // Array con ambas salas más utilizadas
        $salasUtilizadas = [
            'mas_reservas' => $salaMasReservas,
            'mas_ocupada' => $salaMasUtilizada
        ];

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

        // Obtener clases no realizadas de hoy (módulos individuales)
        $clasesNoRealizadasHoyRaw = ClaseNoRealizada::whereDate('fecha_clase', today())
            ->with(['asignatura', 'profesor', 'modulo', 'espacio'])
            ->get();

        // Agrupar módulos consecutivos como una sola clase
        $clasesAgrupadas = $clasesNoRealizadasHoyRaw->groupBy(function($clase) {
            return $clase->id_asignatura . '-' . 
                   $clase->run_profesor . '-' . 
                   $clase->id_espacio . '-' . 
                   $clase->fecha_clase->format('Y-m-d');
        });

        // Crear colección de clases únicas
        $clasesNoRealizadasHoy = collect();
        foreach ($clasesAgrupadas as $key => $modulos) {
            $primerModulo = $modulos->first();
            $ultimoModulo = $modulos->last();
            $estadoClase = $modulos->contains('estado', 'recuperada') ? 'recuperada' : 'pendiente';
            
            $claseAgrupada = clone $primerModulo;
            $claseAgrupada->modulos_count = $modulos->count();
            $claseAgrupada->modulos_detalle = $modulos->pluck('id_modulo')->toArray();
            $claseAgrupada->hora_inicio_clase = $primerModulo->modulo ? $primerModulo->modulo->hora_inicio : null;
            $claseAgrupada->hora_fin_clase = $ultimoModulo->modulo ? $ultimoModulo->modulo->hora_termino : null;
            $claseAgrupada->estado = $estadoClase;
            
            $clasesNoRealizadasHoy->push($claseAgrupada);
        }

        // Inicializar noUtilizadasDia vacío (se cargará on-demand si es necesario)
        $noUtilizadasDia = [];

        return view('layouts.dashboard', compact(
            'ocupacionSemanal',
            'ocupacionMensual',
            'salasOcupadas',
            'espaciosOcupadosTotal',
            'usoPorDia',
            'salasUtilizadasPorDia',
            'ocupacionPorDia',
            'salasPorTipoPorDia',
            'ocupacionPorTurno',
            'ocupacionPorTipo',
            'ocupacionPorSala',
            'disponibilidadSalas',
            'evolucionMensual',
            'comparativaTipos',
            'facultad',
            'piso',
            'pisos',
            'reservasSinDevolucion',
            'moduloActual',
            'accesosActuales',
            'totalReservasHoy',
            'salasUtilizadas',
            'horariosAgrupados',
            'clasesNoRealizadasHoy',
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

    /**
     * Calcula el promedio de ocupación por hora para un rango de fechas
     * VERSIÓN OPTIMIZADA: Usa una sola query SQL con agrupación
     * Considera lunes a sábado, con sábado funcionando 8-13hrs
     * Separa diurno (8-19) y vespertino (19-23)
     * 
     * @param Carbon $inicio Fecha inicial
     * @param Carbon $fin Fecha final
     * @param string|null $facultad Filtro opcional por facultad
     * @param string|null $piso Filtro opcional por piso
     * @param string|null $turno Filtro opcional por turno ('diurno', 'vespertino' o null)
     * @return float Promedio de ocupación en porcentaje
     */
    private function calcularOcupacionPromedioHora($inicio, $fin, $facultad = null, $piso = null, $turno = null)
    {
        // Si turno es null, calcular como promedio de diurno + vespertino
        if ($turno === null) {
            $diurno = $this->calcularOcupacionPromedioHora($inicio, $fin, $facultad, $piso, 'diurno');
            $vespertino = $this->calcularOcupacionPromedioHora($inicio, $fin, $facultad, $piso, 'vespertino');
            
            // Promedio simple de diurno y vespertino
            $resultado = ($diurno + $vespertino) / 2;
            
            Log::info('Ocupación promedio total (diurno + vespertino)', [
                'diurno' => $diurno,
                'vespertino' => $vespertino,
                'promedio' => round($resultado, 2),
                'periodo' => $inicio->format('Y-m-d') . ' a ' . $fin->format('Y-m-d'),
                'facultad' => $facultad,
                'piso' => $piso
            ]);
            
            return round($resultado, 2);
        }

        $totalEspacios = $this->obtenerEspaciosQuery($facultad, $piso)
            ->where('tipo_espacio', 'Sala de Clases')
            ->count();

        if ($totalEspacios === 0) {
            return 0;
        }

        // OPTIMIZADO: Usar una sola query con agrupación en lugar de iterar por cada hora
        // Determinar rango de horas según turno
        $horaInicioTurno = ($turno === 'vespertino') ? 19 : 8;
        $horaFinTurno = ($turno === 'diurno') ? 19 : 23;

        // Query optimizada: obtener conteo de reservas agrupadas por fecha y hora
        $query = Reserva::select(
                DB::raw('DATE(fecha_reserva) as fecha'),
                DB::raw('HOUR(hora) as hora_dia'),
                DB::raw('COUNT(*) as total_reservas')
            )
            ->whereBetween('fecha_reserva', [$inicio->format('Y-m-d'), $fin->format('Y-m-d')])
            ->whereIn('estado', ['activa', 'finalizada'])
            ->whereRaw('DAYOFWEEK(fecha_reserva) BETWEEN 2 AND 7') // Lunes (2) a Sábado (7)
            ->whereRaw('HOUR(hora) >= ?', [$horaInicioTurno])
            ->whereRaw('HOUR(hora) < ?', [$horaFinTurno])
            ->whereHas('espacio', function($query) use ($facultad, $piso) {
                if ($piso) {
                    $query->whereHas('piso', function($q) use ($facultad, $piso) {
                        $q->where('id_facultad', $facultad);
                        $q->where('numero_piso', $piso);
                    });
                } elseif ($facultad) {
                    $query->whereHas('piso', function($q) use ($facultad) {
                        $q->where('id_facultad', $facultad);
                    });
                }
                $query->where('tipo_espacio', 'Sala de Clases');
            })
            ->groupBy('fecha', 'hora_dia');

        // Para sábados, filtrar horas solo hasta las 13
        // Esto se maneja después al procesar resultados

        $reservasPorHora = $query->get();

        // Si no hay reservas, retornar 0
        if ($reservasPorHora->isEmpty()) {
            Log::warning('Sin datos de ocupación para calcular promedio', [
                'turno' => $turno ?? 'todos',
                'totalEspacios' => $totalEspacios,
                'totalHoras' => 0,
                'periodo' => $inicio->format('Y-m-d') . ' a ' . $fin->format('Y-m-d'),
                'facultad' => $facultad,
                'piso' => $piso
            ]);
            return 0;
        }

        // Procesar resultados: agrupar por hora y calcular máximos
        $maximosPorHora = [];
        
        foreach ($reservasPorHora as $registro) {
            $fecha = Carbon::parse($registro->fecha);
            $hora = $registro->hora_dia;
            
            // Filtrar sábados después de las 13 horas
            if ($fecha->isSaturday() && $hora >= 13) {
                continue;
            }
            
            // Calcular porcentaje de ocupación para esta hora
            $porcentajeOcupacion = min(($registro->total_reservas / $totalEspacios) * 100, 100);
            
            // Guardar el máximo por cada hora del día
            if (!isset($maximosPorHora[$hora]) || $porcentajeOcupacion > $maximosPorHora[$hora]) {
                $maximosPorHora[$hora] = $porcentajeOcupacion;
            }
        }

        // Calcular promedio de los máximos por hora
        if (count($maximosPorHora) === 0) {
            return 0;
        }

        $promedioTotal = array_sum($maximosPorHora) / count($maximosPorHora);
        $resultado = round($promedioTotal, 2);

        Log::info('Ocupación promedio por hora calculada (optimizado)', [
            'turno' => $turno ?? 'todos',
            'totalEspacios' => $totalEspacios,
            'horasConDatos' => count($maximosPorHora),
            'porcentaje' => $resultado,
            'periodo' => $inicio->format('Y-m-d') . ' a ' . $fin->format('Y-m-d'),
            'facultad' => $facultad,
            'piso' => $piso
        ]);

        return $resultado;
    }

    private function calcularOcupacionSemanal($facultad, $piso, $turno = null)
    {
        // Lunes a sábado de la semana actual
        $inicioSemana = Carbon::now()->startOfWeek();
        
        // Usar sábado como fin de semana (no domingo)
        $finSemana = $inicioSemana->copy()->addDays(5); // Sábado

        return $this->calcularOcupacionPromedioHora($inicioSemana, $finSemana, $facultad, $piso, $turno);
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
                    // Solo Salas de Clases
                    $query->where('tipo_espacio', 'Sala de Clases');
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

            $totalEspacios = $this->obtenerEspaciosQuery($facultad, $piso)
                ->where('tipo_espacio', 'Sala de Clases')
                ->count();

            $porcentaje = $totalEspacios > 0 ? ($espaciosOcupados / $totalEspacios) * 100 : 0;

            $ocupacion[$modulo->hora_inicio] = round($porcentaje, 2);
        }

        return $ocupacion;
    }

    private function calcularOcupacionMensual($facultad, $piso, $turno = null)
    {
        $inicioMes = Carbon::now()->startOfMonth();
        $finMes = Carbon::now()->endOfMonth();

        return $this->calcularOcupacionPromedioHora($inicioMes, $finMes, $facultad, $piso, $turno);
    }

    private function obtenerUsuariosSinEscaneo($facultad, $piso)
    {
        $hoy = Carbon::today();

        // Obtener los espacios de la facultad y piso especificados (solo Salas de Clases)
        $espacios = $this->obtenerEspaciosQuery($facultad, $piso)
            ->where('tipo_espacio', 'Sala de Clases')
            ->pluck('id_espacio');

        // Obtener profesores que no tienen reservas hoy en los espacios especificados
        return Profesor::whereDoesntHave('reservas', function($query) use ($hoy, $espacios) {
            $query->whereDate('fecha_reserva', $hoy)
                  ->whereIn('id_espacio', $espacios);
        })->count();
    }

    private function calcularHorasUtilizadas($facultad, $piso, $turno = null)
    {
        $hoy = Carbon::today();

        // Obtener total de SALAS DE CLASES para calcular horas disponibles correctamente
        $totalEspacios = $this->obtenerEspaciosQuery($facultad, $piso)
            ->where('tipo_espacio', 'Sala de Clases')
            ->count();
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
        // SOLO contar Salas de Clases para el KPI de % Ocupación
        $espaciosQuery = $this->obtenerEspaciosQuery($facultad, $piso)
            ->where('tipo_espacio', 'Sala de Clases');
        
        $totalEspacios = (clone $espaciosQuery)->count();
        
        // Obtener IDs de los espacios que cumplen el filtro (solo Salas de Clases)
        $idsEspaciosValidos = (clone $espaciosQuery)->pluck('id_espacio');

        // CORRECCIÓN CRÍTICA: Contar espacios ocupados basándose en RESERVAS ACTIVAS del día actual
        // SOLO de los espacios que son Salas de Clases
        $reservasActivasQuery = Reserva::where('estado', 'activa')
            ->where('fecha_reserva', Carbon::today())
            ->whereIn('id_espacio', $idsEspaciosValidos);

        // Si se especifica turno, filtrar por hora actual
        if ($turno !== null) {
            $horaActual = Carbon::now()->format('H:i:s');

            // Solo contar ocupados si la hora actual está en el turno solicitado
            if ($this->esTurno($horaActual, $turno)) {
                $ocupados = (clone $reservasActivasQuery)
                    ->select('id_espacio')
                    ->groupBy('id_espacio')
                    ->get()
                    ->count();
            } else {
                // Si no estamos en el turno, todas están libres
                $ocupados = 0;
            }
        } else {
            // Sin filtro de turno: contar todas las reservas activas
            $ocupados = (clone $reservasActivasQuery)
                ->select('id_espacio')
                ->groupBy('id_espacio')
                ->get()
                ->count();
        }

        $libres = $totalEspacios - $ocupados;

        return [
            'ocupadas' => $ocupados,
            'libres' => $libres,
            'modulo_actual' => null
        ];
    }

    /**
     * Obtener espacios ocupados/libres contando TODOS los tipos (para gráfico de torta)
     */
    private function obtenerEspaciosOcupadosTotal($facultad, $piso)
    {
        // Obtener TODOS los espacios (incluyendo laboratorios, talleres, etc.)
        $espaciosQuery = $this->obtenerEspaciosQuery($facultad, $piso);
        $totalEspacios = (clone $espaciosQuery)->count();
        
        // Obtener IDs de todos los espacios
        $idsEspaciosValidos = (clone $espaciosQuery)->pluck('id_espacio');

        // Contar espacios ocupados de TODOS los tipos basándose en reservas activas
        $ocupados = Reserva::where('estado', 'activa')
            ->where('fecha_reserva', Carbon::today())
            ->whereIn('id_espacio', $idsEspaciosValidos)
            ->select('id_espacio')
            ->groupBy('id_espacio')
            ->get()
            ->count();

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

            // Contar CANTIDAD DE RESERVAS (volver a como era antes)
            $cantidadReservas = Reserva::whereDate('fecha_reserva', $dia)
                ->whereIn('estado', ['activa', 'finalizada'])
                ->whereHas('espacio', function($query) use ($piso, $facultad) {
                    if ($piso) {
                        $query->whereHas('piso', function($q) use ($piso, $facultad) {
                            $q->where('id_facultad', $facultad);
                            $q->where('numero_piso', $piso);
                        });
                    } elseif ($facultad) {
                        $query->whereHas('piso', function($q) use ($facultad) {
                            $q->where('id_facultad', $facultad);
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

    private function obtenerSalasUtilizadasPorDia($facultad, $piso, $fechaInicio = null, $fechaFin = null)
    {
        // Si no se proporcionan fechas, usar la semana actual
        $inicioRango = $fechaInicio ? $fechaInicio->copy() : Carbon::now()->startOfWeek();
        $finRango = $fechaFin ? $fechaFin->copy() : Carbon::now()->endOfWeek();
        
        // Calcular los días en el rango (excluyendo domingos)
        $diasEnRango = [];
        $current = $inicioRango->copy();
        while ($current->lte($finRango)) {
            $diaSemana = $current->format('l');
            $nombreDia = $this->traducirDia($diaSemana);
            if ($nombreDia !== 'Domingo') {
                $diasEnRango[] = [
                    'fecha' => $current->copy(),
                    'nombre' => $nombreDia,
                    'etiqueta' => $nombreDia . ' ' . $current->format('d/m')
                ];
            }
            $current->addDay();
        }
        
        $labels = array_map(function($d) { return $d['etiqueta']; }, $diasEnRango);

        // Obtener todas las salas distintas en el período
        $salas = Espacio::whereHas('piso', function($query) use ($facultad, $piso) {
            if ($piso) {
                $query->where('id_facultad', $facultad);
                $query->where('numero_piso', $piso);
            } elseif ($facultad) {
                $query->where('id_facultad', $facultad);
            }
        })
        ->where('tipo_espacio', 'Sala de Clases')
        ->orderBy('id_espacio')
        ->get();

        $dataPorSala = [];
        
        // Para cada sala, calcular módulos REALES por día
        foreach ($salas as $sala) {
            $modulosPorDia = [];
            
            foreach ($diasEnRango as $diaInfo) {
                $dia = $diaInfo['fecha'];
                
                // Obtener reservas del día
                $reservasDia = Reserva::whereDate('fecha_reserva', $dia)
                    ->where('id_espacio', $sala->id_espacio)
                    ->whereIn('estado', ['activa', 'finalizada'])
                    ->whereNotNull('hora')
                    ->get();
                
                $cantidadModulos = 0;
                foreach ($reservasDia as $reserva) {
                    // Usar el método inteligente de cálculo
                    $cantidadModulos += $this->calcularModulosReales(
                        $reserva->hora, 
                        $reserva->hora_salida, 
                        $reserva->modulos
                    );
                }
                
                $modulosPorDia[] = round($cantidadModulos, 2);
            }
            
            // Solo incluir salas que tengan al menos algo de uso
            if (array_sum($modulosPorDia) > 0) {
                $dataPorSala[] = [
                    'sala' => $sala->id_espacio,
                    'datos' => $modulosPorDia
                ];
            }
        }

        return [
            'salas' => $dataPorSala,
            'dias' => $labels,
            'labels' => $labels,
            'rango_fechas' => [
                'inicio' => $inicioRango->format('d/m/Y'),
                'fin' => $finRango->format('d/m/Y')
            ]
        ];
    }

    private function obtenerOcupacionPorDia($facultad, $piso)
    {
        $inicioSemana = Carbon::now()->startOfWeek();
        $finSemana = Carbon::now()->endOfWeek();
        $diasSemana = ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'];
        $ocupacionPorDia = [];

        for ($i = 0; $i < 6; $i++) {
            $dia = $inicioSemana->copy()->addDays($i);
            // Calcular el promedio de promedios por hora para ese día específico
            $ocupacion = $this->calcularOcupacionPromediosHoraPorDia(
                $dia->copy(),
                $facultad,
                $piso
            );
            $ocupacionPorDia[$diasSemana[$i]] = $ocupacion;
        }

        return [
            'datos' => $ocupacionPorDia,
            'rango_fechas' => [
                'inicio' => $inicioSemana->format('d/m/Y'),
                'fin' => $finSemana->format('d/m/Y')
            ]
        ];
    }

    private function calcularOcupacionPromediosHoraPorDia($fecha, $facultad = null, $piso = null)
    {
        $totalEspacios = $this->obtenerEspaciosQuery($facultad, $piso)
            ->where('tipo_espacio', 'Sala de Clases')
            ->count();

        if ($totalEspacios === 0) {
            return 0;
        }

        // Array para almacenar los porcentajes de ocupación de cada hora
        $ocupacionesPorHora = [];

        $horaInicio = 8;
        if ($fecha->isSaturday()) {
            $horaFin = 13; // Sábado: 8 a 13
        } else {
            $horaFin = 23; // Lunes a viernes: 8 a 23
        }

        // Iterar por cada hora del día
        for ($hora = $horaInicio; $hora < $horaFin; $hora++) {
            $horaInicioFormato = sprintf('%02d:00:00', $hora);
            $horaFinFormato = sprintf('%02d:59:59', $hora);
            
            // Obtener todas las reservas que incluyan esta hora
            $reservasEnHora = Reserva::where('fecha_reserva', $fecha->format('Y-m-d'))
                ->whereBetween('hora', [$horaInicioFormato, $horaFinFormato])
                ->whereIn('estado', ['activa', 'finalizada'])
                ->whereHas('espacio', function($query) use ($facultad, $piso) {
                    if ($piso) {
                        $query->whereHas('piso', function($q) use ($facultad, $piso) {
                            $q->where('id_facultad', $facultad);
                            $q->where('numero_piso', $piso);
                        });
                    } elseif ($facultad) {
                        $query->whereHas('piso', function($q) use ($facultad) {
                            $q->where('id_facultad', $facultad);
                        });
                    }
                    $query->where('tipo_espacio', 'Sala de Clases');
                })
                ->count();

            // Calcular ocupación de esta hora en porcentaje (0-100)
            $ocupacionEnHora = ($reservasEnHora / $totalEspacios) * 100;
            $ocupacionesPorHora[] = min($ocupacionEnHora, 100);
        }

        // Retornar el promedio de todos los porcentajes por hora
        if (count($ocupacionesPorHora) === 0) {
            return 0;
        }

        $promedioTotal = array_sum($ocupacionesPorHora) / count($ocupacionesPorHora);
        return round($promedioTotal, 2);
    }

    private function obtenerSalasPorTipoPorDia($facultad, $piso, $fechaInicio = null, $fechaFin = null)
    {
        // Si no se proporcionan fechas, usar la semana actual
        $inicioRango = $fechaInicio ? $fechaInicio->copy() : Carbon::now()->startOfWeek();
        $finRango = $fechaFin ? $fechaFin->copy() : Carbon::now()->endOfWeek();
        
        // Calcular los días en el rango (excluyendo domingos)
        $diasEnRango = [];
        $current = $inicioRango->copy();
        while ($current->lte($finRango)) {
            $diaSemana = $current->format('l');
            $nombreDia = $this->traducirDia($diaSemana);
            if ($nombreDia !== 'Domingo') {
                $diasEnRango[] = [
                    'fecha' => $current->copy(),
                    'nombre' => $nombreDia,
                    'etiqueta' => $nombreDia . ' ' . $current->format('d/m')
                ];
            }
            $current->addDay();
        }
        
        $labels = array_map(function($d) { return $d['etiqueta']; }, $diasEnRango);
        $numDias = count($diasEnRango);

        // Inicializar estructura de datos
        $dataPorTipo = [];

        // Obtener TODOS los datos en UNA sola consulta
        $reservas = Reserva::whereBetween('fecha_reserva', [$inicioRango, $finRango])
            ->whereIn('estado', ['activa', 'finalizada'])
            ->with(['espacio' => function($q) {
                $q->select('id_espacio', 'tipo_espacio', 'piso_id');
            }, 'espacio.piso' => function($q) {
                $q->select('id', 'id_facultad', 'numero_piso');
            }])
            ->get();

        // Filtrar por facultad/piso en PHP (después de que lleguen)
        // Se eliminó el filtro que limitaba solo a 'Sala de Clases' para mostrar todos los tipos
        $reservasFiltradas = $reservas->filter(function($reserva) use ($facultad, $piso) {
            if (!$reserva->espacio || !$reserva->espacio->piso) {
                return false;
            }
            
            if ($piso) {
                return $reserva->espacio->piso->id_facultad == $facultad && 
                       $reserva->espacio->piso->numero_piso == $piso;
            } elseif ($facultad) {
                return $reserva->espacio->piso->id_facultad == $facultad;
            }
            return true;
        });

        // Agrupar por tipo de espacio y día (usando tiempo REAL de uso)
        $controller = $this; // Referencia para usar dentro del closure
        $agrupadoPorTipo = $reservasFiltradas->groupBy(function($reserva) {
            return $reserva->espacio->tipo_espacio;
        })->map(function($reservasPorTipo) use ($diasEnRango, $numDias, $controller) {
            $modulosPorDia = array_fill(0, $numDias, 0);
            
            foreach ($reservasPorTipo as $reserva) {
                $fechaReserva = Carbon::parse($reserva->fecha_reserva)->format('Y-m-d');
                
                // Encontrar el índice del día en nuestro rango
                foreach ($diasEnRango as $index => $diaInfo) {
                    if ($diaInfo['fecha']->format('Y-m-d') === $fechaReserva) {
                        // Usar el método inteligente de cálculo de módulos
                        $modulosPorDia[$index] += $controller->calcularModulosRealesPublic(
                            $reserva->hora, 
                            $reserva->hora_salida, 
                            $reserva->modulos
                        );
                        break;
                    }
                }
            }
            
            // Redondear los valores
            return array_map(function($val) { return round($val, 2); }, $modulosPorDia);
        });

        // Construir resultado
        foreach ($agrupadoPorTipo as $tipo => $datos) {
            if (array_sum($datos) > 0) {
                $dataPorTipo[] = [
                    'tipo' => $tipo,
                    'datos' => $datos
                ];
            }
        }

        return [
            'tipos' => $dataPorTipo,
            'dias' => $labels,
            'labels' => $labels,
            'rango_fechas' => [
                'inicio' => $inicioRango->format('d/m/Y'),
                'fin' => $finRango->format('d/m/Y')
            ]
        ];
    }

    private function obtenerOcupacionPorTurno($facultad, $piso, $fechaInicio = null, $fechaFin = null)
    {
        // Si no se proporcionan fechas, usar la semana actual
        $inicioRango = $fechaInicio ? $fechaInicio->copy() : Carbon::now()->startOfWeek();
        $finRango = $fechaFin ? $fechaFin->copy() : Carbon::now()->endOfWeek();
        
        // Calcular los días en el rango (excluyendo domingos)
        $diasEnRango = [];
        $current = $inicioRango->copy();
        while ($current->lte($finRango)) {
            $diaSemana = $current->format('l');
            $nombreDia = $this->traducirDia($diaSemana);
            if ($nombreDia !== 'Domingo') {
                $diasEnRango[] = [
                    'fecha' => $current->copy(),
                    'nombre' => $nombreDia,
                    'etiqueta' => $nombreDia . ' ' . $current->format('d/m')
                ];
            }
            $current->addDay();
        }
        
        $ocupacionDiurno = [];
        $ocupacionVespertino = [];
        $labels = [];

        foreach ($diasEnRango as $diaInfo) {
            $dia = $diaInfo['fecha'];
            $etiqueta = $diaInfo['etiqueta'];
            
            $diurno = $this->calcularOcupacionPromedioHora($dia->copy(), $dia->copy(), $facultad, $piso, 'diurno');
            $vespertino = $this->calcularOcupacionPromedioHora($dia->copy(), $dia->copy(), $facultad, $piso, 'vespertino');
            
            $ocupacionDiurno[] = $diurno;
            $ocupacionVespertino[] = $vespertino;
            $labels[] = $etiqueta;
        }

        return [
            'datos' => [
                'diurno' => $ocupacionDiurno,
                'vespertino' => $ocupacionVespertino,
                'total' => array_map(function($d, $v) { return round(($d + $v) / 2, 2); }, $ocupacionDiurno, $ocupacionVespertino)
            ],
            'dias' => $labels,
            'labels' => $labels,
            'rango_fechas' => [
                'inicio' => $inicioRango->format('d/m/Y'),
                'fin' => $finRango->format('d/m/Y')
            ]
        ];
    }

    private function obtenerOcupacionPorTipo($facultad, $piso, $fechaInicio = null, $fechaFin = null)
    {
        // Si no se proporcionan fechas, usar la semana actual
        $inicioRango = $fechaInicio ? $fechaInicio->copy() : Carbon::now()->startOfWeek();
        $finRango = $fechaFin ? $fechaFin->copy() : Carbon::now()->endOfWeek();
        
        // Calcular los días en el rango (excluyendo domingos)
        $diasEnRango = [];
        $current = $inicioRango->copy();
        while ($current->lte($finRango)) {
            $diaSemana = $current->format('l');
            $nombreDia = $this->traducirDia($diaSemana);
            if ($nombreDia !== 'Domingo') {
                $diasEnRango[] = [
                    'fecha' => $current->copy(),
                    'nombre' => $nombreDia,
                    'etiqueta' => $nombreDia . ' ' . $current->format('d/m')
                ];
            }
            $current->addDay();
        }
        
        $labels = array_map(function($d) { return $d['etiqueta']; }, $diasEnRango);
        
        // Obtener todos los tipos de espacio (no solo Salas de Clases)
        $tipos = Espacio::whereHas('piso', function($query) use ($facultad, $piso) {
            if ($piso) {
                $query->where('id_facultad', $facultad);
                $query->where('numero_piso', $piso);
            } elseif ($facultad) {
                $query->where('id_facultad', $facultad);
            }
        })
        ->distinct('tipo_espacio')
        ->pluck('tipo_espacio');

        $ocupacionPorTipo = [];
        
        foreach ($tipos as $tipo) {
            $datosOcupacion = [];
            
            // Obtener total de espacios de este tipo
            $espaciosTipo = Espacio::whereHas('piso', function($query) use ($facultad, $piso) {
                if ($piso) {
                    $query->where('id_facultad', $facultad);
                    $query->where('numero_piso', $piso);
                } elseif ($facultad) {
                    $query->where('id_facultad', $facultad);
                }
            })
            ->where('tipo_espacio', $tipo)
            ->get();
            
            $totalEspacios = $espaciosTipo->count();
            // Total de módulos disponibles por día para este tipo = espacios * 15 módulos
            $modulosTotalesPorDia = $totalEspacios * 15;
            
            foreach ($diasEnRango as $diaInfo) {
                $dia = $diaInfo['fecha'];
                
                // Obtener todas las reservas del día para este tipo de espacio
                $reservasDia = Reserva::where('fecha_reserva', $dia->format('Y-m-d'))
                    ->whereIn('estado', ['activa', 'finalizada'])
                    ->whereIn('id_espacio', $espaciosTipo->pluck('id_espacio'))
                    ->whereNotNull('hora')
                    ->get();
                
                // Calcular módulos reales usados
                $modulosUsados = 0;
                foreach ($reservasDia as $reserva) {
                    $modulosUsados += $this->calcularModulosReales(
                        $reserva->hora, 
                        $reserva->hora_salida, 
                        $reserva->modulos
                    );
                }
                
                // Calcular ocupación basada en módulos reales / módulos disponibles
                $ocupacion = $modulosTotalesPorDia > 0 ? ($modulosUsados / $modulosTotalesPorDia) * 100 : 0;
                $datosOcupacion[] = round($ocupacion, 2);
            }
            
            // Solo incluir tipos que tengan al menos algún dato
            if (array_sum($datosOcupacion) > 0 || $totalEspacios > 0) {
                $ocupacionPorTipo[] = [
                    'tipo' => $tipo,
                    'datos' => $datosOcupacion
                ];
            }
        }

        return [
            'tipos' => $ocupacionPorTipo,
            'dias' => $labels,
            'labels' => $labels,
            'rango_fechas' => [
                'inicio' => $inicioRango->format('d/m/Y'),
                'fin' => $finRango->format('d/m/Y')
            ]
        ];
    }

    private function obtenerOcupacionPorSala($facultad, $piso, $fechaInicio = null, $fechaFin = null)
    {
        // Si no se proporcionan fechas, usar la semana actual
        $inicioRango = $fechaInicio ? $fechaInicio->copy() : Carbon::now()->startOfWeek();
        $finRango = $fechaFin ? $fechaFin->copy() : Carbon::now()->endOfWeek();
        
        // Calcular los días en el rango (excluyendo domingos)
        $diasEnRango = [];
        $current = $inicioRango->copy();
        while ($current->lte($finRango)) {
            $diaSemana = $current->format('l');
            $nombreDia = $this->traducirDia($diaSemana);
            if ($nombreDia !== 'Domingo') {
                $diasEnRango[] = [
                    'fecha' => $current->copy(),
                    'nombre' => $nombreDia,
                    'etiqueta' => $nombreDia . ' ' . $current->format('d/m')
                ];
            }
            $current->addDay();
        }
        
        $labels = array_map(function($d) { return $d['etiqueta']; }, $diasEnRango);
        
        // Obtener todas las salas de clases
        $salas = Espacio::whereHas('piso', function($query) use ($facultad, $piso) {
            if ($piso) {
                $query->where('id_facultad', $facultad);
                $query->where('numero_piso', $piso);
            } elseif ($facultad) {
                $query->where('id_facultad', $facultad);
            }
        })
        ->where('tipo_espacio', 'Sala de Clases')
        ->orderBy('id_espacio')
        ->get();

        $ocupacionPorSala = [];
        
        foreach ($salas as $sala) {
            $datosOcupacion = [];
            $modulosTotales = 0;
            
            foreach ($diasEnRango as $diaInfo) {
                $dia = $diaInfo['fecha'];
                
                // Obtener reservas del día para esta sala
                $reservasDia = Reserva::where('fecha_reserva', $dia->format('Y-m-d'))
                    ->where('id_espacio', $sala->id_espacio)
                    ->whereIn('estado', ['activa', 'finalizada'])
                    ->whereNotNull('hora')
                    ->get();
                
                $numModulos = 0;
                foreach ($reservasDia as $reserva) {
                    // Usar el método inteligente de cálculo de módulos
                    $numModulos += $this->calcularModulosReales(
                        $reserva->hora, 
                        $reserva->hora_salida, 
                        $reserva->modulos
                    );
                }
                
                $modulosTotales += $numModulos;
                
                // Calcular ocupación: (módulos usados / 15) * 100
                $ocupacion = 15 > 0 ? ($numModulos / 15) * 100 : 0;
                $datosOcupacion[] = round($ocupacion, 2);
            }
            
            // Solo incluir salas que tengan al menos algo de uso
            if ($modulosTotales > 0) {
                $ocupacionPorSala[] = [
                    'sala' => $sala->id_espacio,
                    'modulos' => round($modulosTotales, 2),
                    'datos' => $datosOcupacion
                ];
            }
        }

        return [
            'salas' => $ocupacionPorSala,
            'dias' => $labels,
            'labels' => $labels,
            'rango_fechas' => [
                'inicio' => $inicioRango->format('d/m/Y'),
                'fin' => $finRango->format('d/m/Y')
            ]
        ];
    }

    private function obtenerDisponibilidadSalas($facultad, $piso)
    {
        $inicioSemana = Carbon::now()->startOfWeek();
        $finSemana = Carbon::now()->endOfWeek();
        $diasSemana = ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'];
        
        $disponibilidadPorDia = [];

        for ($i = 0; $i < 6; $i++) {
            $dia = $inicioSemana->copy()->addDays($i);
            // Calcular el promedio de promedios de desocupación para ese día
            $disponibilidad = $this->calcularDisponibilidadPromediosHoraPorDia(
                $dia->copy(),
                $facultad,
                $piso
            );
            $disponibilidadPorDia[$diasSemana[$i]] = $disponibilidad;
        }

        return [
            'datos' => $disponibilidadPorDia,
            'dias' => $diasSemana,
            'totalSalas' => Espacio::whereHas('piso', function($query) use ($facultad, $piso) {
                if ($piso) {
                    $query->where('id_facultad', $facultad);
                    $query->where('numero_piso', $piso);
                } elseif ($facultad) {
                    $query->where('id_facultad', $facultad);
                }
            })
            ->where('tipo_espacio', 'Sala de Clases')
            ->count(),
            'rango_fechas' => [
                'inicio' => $inicioSemana->format('d/m/Y'),
                'fin' => $finSemana->format('d/m/Y')
            ]
        ];
    }

    private function calcularDisponibilidadPromediosHoraPorDia($fecha, $facultad = null, $piso = null)
    {
        $totalEspacios = $this->obtenerEspaciosQuery($facultad, $piso)
            ->where('tipo_espacio', 'Sala de Clases')
            ->count();

        if ($totalEspacios === 0) {
            return 100; // Si no hay espacios, está 100% disponible
        }

        // Array para almacenar los porcentajes de desocupación de cada hora
        $desocupacionesPorHora = [];

        $horaInicio = 8;
        if ($fecha->isSaturday()) {
            $horaFin = 13; // Sábado: 8 a 13
        } else {
            $horaFin = 23; // Lunes a viernes: 8 a 23
        }

        // Iterar por cada hora del día
        for ($hora = $horaInicio; $hora < $horaFin; $hora++) {
            $horaInicioFormato = sprintf('%02d:00:00', $hora);
            $horaFinFormato = sprintf('%02d:59:59', $hora);
            
            // Obtener todas las reservas que incluyan esta hora
            $reservasEnHora = Reserva::where('fecha_reserva', $fecha->format('Y-m-d'))
                ->whereBetween('hora', [$horaInicioFormato, $horaFinFormato])
                ->whereIn('estado', ['activa', 'finalizada'])
                ->whereHas('espacio', function($query) use ($facultad, $piso) {
                    if ($piso) {
                        $query->whereHas('piso', function($q) use ($facultad, $piso) {
                            $q->where('id_facultad', $facultad);
                            $q->where('numero_piso', $piso);
                        });
                    } elseif ($facultad) {
                        $query->whereHas('piso', function($q) use ($facultad) {
                            $q->where('id_facultad', $facultad);
                        });
                    }
                    $query->where('tipo_espacio', 'Sala de Clases');
                })
                ->count();

            // Calcular desocupación de esta hora en porcentaje (100% - ocupación%)
            $ocupacionEnHora = ($reservasEnHora / $totalEspacios) * 100;
            $desocupacionEnHora = 100 - min($ocupacionEnHora, 100);
            $desocupacionesPorHora[] = $desocupacionEnHora;
        }

        // Retornar el promedio de todos los porcentajes de desocupación por hora
        if (count($desocupacionesPorHora) === 0) {
            return 100;
        }

        $promedioTotal = array_sum($desocupacionesPorHora) / count($desocupacionesPorHora);
        return round($promedioTotal, 2);
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

            // Calcular porcentaje real basado en horas (para el reporte mensual)
            $porcentaje = $totalHorasDisponibles > 0 ?
                round(($horasUtilizadas / $totalHorasDisponibles) * 100) : 0;

            // IMPORTANTE: Contar espacios ocupados AHORA basándose SOLO en reservas activas del día actual
            // Esto incluye TODAS las reservas: de profesores (run_profesor) Y espontáneas (run_solicitante)
            // CORRECCIÓN: usar groupBy para contar espacios únicos correctamente
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
                ->select('id_espacio')
                ->groupBy('id_espacio')
                ->get()
                ->count();

            $result[] = [
                'nombre' => $tipo,
                'porcentaje' => $totalEspaciosTipo > 0 ? round(($espaciosOcupados / $totalEspaciosTipo) * 100) : 0,  // Basado en ocupación actual
                'porcentaje_mensual' => $porcentaje,  // Guardar también el porcentaje mensual por si se necesita
                'ocupados' => $espaciosOcupados,
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
        // IMPORTANTE: Este método devuelve TODOS los tipos de espacio (no solo Salas de Clases)
        // Se usa para el gráfico "Estado Actual de Espacios" que debe mostrar TODOS los tipos
        return Espacio::whereHas('piso', function($query) use ($facultad, $piso) {
            $query->where('id_facultad', $facultad);
            if ($piso) {
                $query->where('numero_piso', $piso);
            }
        });
        // NO filtrar por tipo_espacio aquí, se filtra en cada método según necesidad
    }

    /**
     * Cargar datos de la pestaña "Utilización" por demanda
     */
    public function getUtilizacionData(Request $request)
    {
        $tenant = $this->ensureTenantContext();
        if (!$tenant) {
            return response()->json([
                'comparativaTipos' => [],
                'salasOcupadas' => ['total' => 0]
            ]);
        }

        $piso = $request->session()->get('piso');
        $facultad = 'IT_' . $tenant->sede_id;

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
        $tenant = $this->ensureTenantContext();
        
        if (!$tenant) {
            return view('partials.accesos_tab_content', ['reservasSinDevolucion' => collect(), 'accesosActuales' => collect()])->render();
        }

        $piso = $request->session()->get('piso');
        
        // Obtener todas las reservas activas sin devolver
        $reservasSinDevolucion = Reserva::with(['profesor', 'solicitante', 'espacio.piso.facultad'])
            ->where('estado', 'activa')
            ->whereNull('hora_salida')
            ->when($piso, function($query) use ($piso) {
                return $query->whereHas('espacio', function($q) use ($piso) {
                    $q->whereHas('piso', function($inner) use ($piso) {
                        $inner->where('numero_piso', $piso);
                    });
                });
            })
            ->latest('fecha_reserva')
            ->latest('hora')
            ->get();
        
        // Historial de Accesos: últimos 10 registros (incluye accesos en curso y finalizados)
        // No filtramos por hora_salida para incluir el histórico completo
        $accesosActuales = Reserva::with(['profesor', 'solicitante', 'espacio.piso.facultad'])
            ->where('estado', 'activa')
            ->when($piso, function($query) use ($piso) {
                return $query->whereHas('espacio', function($q) use ($piso) {
                    $q->whereHas('piso', function($inner) use ($piso) {
                        $inner->where('numero_piso', $piso);
                    });
                });
            })
            ->orderBy('fecha_reserva', 'desc')
            ->orderBy('hora', 'desc')
            ->limit(10)
            ->get();
            
        return view('partials.accesos_tab_content', compact('reservasSinDevolucion', 'accesosActuales'))->render();
    }

    public function getWidgetData(Request $request)
    {
        $tenant = $this->ensureTenantContext();
        if (!$tenant) {
            return response()->json([
                'ocupacionSemanal' => [],
                'ocupacionDiaria' => [],
                'ocupacionMensual' => [],
                'usuariosSinEscaneo' => 0,
                'horasUtilizadas' => [],
                'salasOcupadas' => []
            ]);
        }

        $piso = $request->session()->get('piso');
        $facultad = 'IT_' . $tenant->sede_id;

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
        $tenant = $this->ensureTenantContext();
        if (!$tenant) {
            return response()->json([
                'clasesNoRealizadas' => [],
                'recuperaciones' => [],
                'horasPromedio' => 0,
                'totalHoras' => 0,
                'tasaRecuperacion' => 0
            ]);
        }

        $piso = $request->session()->get('piso');
        $facultad = 'IT_' . $tenant->sede_id;
        $mes = now()->month;
        $anio = now()->year;
        $hoy = Carbon::now()->startOfDay();
        
        // Usar el período académico correcto (formato YYYY-S, ej: 2025-2)
        $periodo = SemesterHelper::getCurrentPeriod();

        // Obtener clases no realizadas del mes actual (solo hasta hoy), excluyendo atrasos
        $clasesNoRealizadasRaw = ClaseNoRealizada::whereMonth('fecha_clase', $mes)
            ->whereYear('fecha_clase', $anio)
            ->where('fecha_clase', '<=', $hoy)
            ->whereNotExists(function($query) {
                $query->select(DB::raw(1))
                    ->from('profesor_atrasos')
                    ->whereColumn('profesor_atrasos.id_asignatura', 'clases_no_realizadas.id_asignatura')
                    ->whereColumn('profesor_atrasos.id_espacio', 'clases_no_realizadas.id_espacio')
                    ->whereColumn('profesor_atrasos.id_modulo', 'clases_no_realizadas.id_modulo')
                    ->whereColumn('profesor_atrasos.fecha', 'clases_no_realizadas.fecha_clase');
            })
            ->with(['asignatura', 'profesor', 'modulo'])
            ->get();

        // =====================================================
        // AGRUPAR MÓDULOS CONSECUTIVOS COMO UNA SOLA CLASE
        // Una clase = misma asignatura + profesor + espacio + fecha
        // =====================================================
        $clasesAgrupadas = $clasesNoRealizadasRaw->groupBy(function($clase) {
            return $clase->id_asignatura . '-' . 
                   $clase->run_profesor . '-' . 
                   $clase->id_espacio . '-' . 
                   $clase->fecha_clase->format('Y-m-d');
        });

        // Crear colección de clases únicas (no módulos individuales)
        $clasesNoRealizadas = collect();
        foreach ($clasesAgrupadas as $key => $modulos) {
            // Tomar el primer módulo como representante de la clase
            $primerModulo = $modulos->first();
            $ultimoModulo = $modulos->last();
            
            // Determinar el estado de la clase (si algún módulo está recuperado, la clase está recuperada)
            $estadoClase = $modulos->contains('estado', 'recuperada') ? 'recuperada' : 'pendiente';
            
            // Crear objeto representativo de la clase
            $claseAgrupada = clone $primerModulo;
            $claseAgrupada->modulos_count = $modulos->count();
            $claseAgrupada->modulos_detalle = $modulos->pluck('id_modulo')->toArray();
            $claseAgrupada->hora_inicio = $primerModulo->modulo ? $primerModulo->modulo->hora_inicio : null;
            $claseAgrupada->hora_fin = $ultimoModulo->modulo ? $ultimoModulo->modulo->hora_termino : null;
            $claseAgrupada->estado = $estadoClase;
            
            $clasesNoRealizadas->push($claseAgrupada);
        }

        // Obtener clases que están programadas para recuperación (estado = 'pendiente')
        $clasesParaRecuperar = $clasesNoRealizadas->where('estado', 'pendiente')->count();
        
        // Obtener clases ya recuperadas (estado = 'recuperada')
        $clasesRecuperadas = $clasesNoRealizadas->where('estado', 'recuperada')->count();

        // Obtener todas las planificaciones del período actual
        $planificacionesMesRaw = Planificacion_Asignatura::whereHas('horario', function($q) use ($periodo) {
            $q->where('periodo', $periodo);
        })->with(['modulo', 'asignatura'])->get();

        // =====================================================
        // AGRUPAR PLANIFICACIONES POR CLASE (no por módulo)
        // Una clase = misma asignatura + espacio + día de semana
        // =====================================================
        $planificacionesAgrupadas = $planificacionesMesRaw->groupBy(function($plan) {
            $dia = $plan->modulo ? strtolower($plan->modulo->dia) : 'sin_dia';
            return $plan->id_asignatura . '-' . $plan->id_espacio . '-' . $dia;
        });

        // Agrupar por día para el gráfico de barras - SOLO HASTA HOY
        $diasDelMes = [];
        $inicio = Carbon::create($anio, $mes, 1);
        $fin = $hoy->copy(); // Solo hasta hoy, no fin de mes
        $dias = ['domingo', 'lunes', 'martes', 'miercoles', 'jueves', 'viernes', 'sabado'];
        
        // Inicializar días (de lunes a sábado, excluyendo domingo, solo hasta hoy)
        for ($fecha = $inicio->copy(); $fecha->lte($fin); $fecha->addDay()) {
            // Solo días laborales (Lun-Vie y Sábados), NO domingos
            if ($fecha->isWeekday() || $fecha->isSaturday()) {
                $dia = $fecha->format('d/m');
                $diasDelMes[$dia] = [
                    'realizadas' => 0,
                    'no_realizadas' => 0,
                    'por_realizar' => 0, // Clases pendientes para hoy (aún no han llegado a la hora)
                    'recuperadas' => 0,
                    'fecha' => $fecha->format('Y-m-d'),
                    'clases_no_realizadas_detalle' => []
                ];
            }
        }

        // Hora actual para determinar clases "por realizar" de hoy
        $horaActual = Carbon::now()->format('H:i:s');
        $fechaHoyFormato = $hoy->format('d/m');

        // Contar CLASES planificadas (agrupadas) solo para días que ya pasaron o es hoy
        foreach ($planificacionesAgrupadas as $key => $modulos) {
            $primerPlan = $modulos->first();
            if ($primerPlan && $primerPlan->modulo) {
                $dia = strtolower($primerPlan->modulo->dia);
                $horaInicioModulo = $primerPlan->modulo->hora_inicio;
                
                // Encontrar todas las fechas con este día de semana HASTA HOY
                for ($fecha = $inicio->copy(); $fecha->lte($fin); $fecha->addDay()) {
                    if (strtolower($dias[$fecha->dayOfWeek]) === $dia) {
                        $diaFormato = $fecha->format('d/m');
                        if (isset($diasDelMes[$diaFormato])) {
                            // Si es HOY y el módulo aún no ha comenzado + 15 min de gracia
                            if ($diaFormato === $fechaHoyFormato) {
                                $horaLimite = Carbon::parse($horaInicioModulo)->addMinutes(15)->format('H:i:s');
                                if ($horaActual < $horaLimite) {
                                    // La clase aún está por realizar (no ha pasado el tiempo de gracia)
                                    $diasDelMes[$diaFormato]['por_realizar']++;
                                } else {
                                    // Ya pasó el tiempo, cuenta como realizada (si no está en no_realizadas)
                                    $diasDelMes[$diaFormato]['realizadas']++;
                                }
                            } else {
                                // Días anteriores: cuenta como realizada por defecto
                                $diasDelMes[$diaFormato]['realizadas']++;
                            }
                        }
                    }
                }
            }
        }

        // Contar clases no realizadas por día y agregar detalle (ya agrupadas)
        foreach ($clasesNoRealizadas as $clase) {
            $diaFormato = $clase->fecha_clase->format('d/m');
            if (isset($diasDelMes[$diaFormato])) {
                $diasDelMes[$diaFormato]['no_realizadas']++;
                $diasDelMes[$diaFormato]['realizadas'] = max(0, $diasDelMes[$diaFormato]['realizadas'] - 1);
                
                // Contar recuperadas
                if ($clase->estado === 'recuperada') {
                    $diasDelMes[$diaFormato]['recuperadas']++;
                }
                
                // Agregar detalle del profesor para el modal
                $horaInicio = $clase->hora_inicio ? substr($clase->hora_inicio, 0, 5) : '';
                $horaFin = $clase->hora_fin ? substr($clase->hora_fin, 0, 5) : '';
                $horaRango = ($horaInicio && $horaFin) ? "$horaInicio - $horaFin" : '';
                
                $diasDelMes[$diaFormato]['clases_no_realizadas_detalle'][] = [
                    'id' => $clase->id,
                    'asignatura' => $clase->asignatura ? $clase->asignatura->nombre_asignatura : 'Sin asignatura',
                    'profesor' => $clase->profesor ? $clase->profesor->name : 'Sin profesor',
                    'profesor_id' => $clase->run_profesor ?? null,
                    'modulos' => $clase->modulos_count ?? 1,
                    'modulos_detalle' => $clase->modulos_detalle ?? [$clase->id_modulo],
                    'hora' => $horaRango,
                    'estado' => $clase->estado,
                    'motivo' => $clase->motivo ?? 'No especificado'
                ];
            }
        }

        // Calcular totales solo con los días procesados (hasta hoy)
        $totalRealizadas = collect($diasDelMes)->sum('realizadas');
        $totalNoRealizadas = collect($diasDelMes)->sum('no_realizadas');
        $totalPorRealizar = collect($diasDelMes)->sum('por_realizar');
        $totalClases = $totalRealizadas + $totalNoRealizadas + $totalPorRealizar;
        
        $porcentajeRealizadas = $totalClases > 0 ? round(($totalRealizadas / $totalClases) * 100, 1) : 0;
        $porcentajeNoRealizadas = $totalClases > 0 ? round(($totalNoRealizadas / $totalClases) * 100, 1) : 0;
        $porcentajePorRealizar = $totalClases > 0 ? round(($totalPorRealizar / $totalClases) * 100, 1) : 0;
        $porcentajeRecuperadas = $totalNoRealizadas > 0 ? round(($clasesRecuperadas / $totalNoRealizadas) * 100, 1) : 0;

        // Preparar arrays para el gráfico (solo días hasta hoy)
        $diasLabels = array_keys($diasDelMes);
        $datosRealizadas = array_values(array_map(function($d) { return max(0, $d['realizadas']); }, $diasDelMes));
        $datosNoRealizadas = array_values(array_map(function($d) { return $d['no_realizadas']; }, $diasDelMes));
        $datosPorRealizar = array_values(array_map(function($d) { return $d['por_realizar'] ?? 0; }, $diasDelMes));
        $datosRecuperadas = array_values(array_map(function($d) { return $d['recuperadas']; }, $diasDelMes));
        
        // Convertir detalle a JSON para pasar a la vista (escapado para JavaScript)
        $diasDelMesJson = json_encode($diasDelMes, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE);

        return view('partials.clases_no_realizadas_tab_content', compact(
            'diasDelMes',
            'diasDelMesJson',
            'totalRealizadas',
            'totalNoRealizadas',
            'totalPorRealizar',
            'clasesRecuperadas',
            'clasesParaRecuperar',
            'porcentajeRealizadas',
            'porcentajeNoRealizadas',
            'porcentajePorRealizar',
            'porcentajeRecuperadas',
            'diasLabels',
            'datosRealizadas',
            'datosNoRealizadas',
            'datosPorRealizar',
            'datosRecuperadas',
            'mes',
            'anio',
            'periodo'
        ))->render();
    }

    /**
     * Obtiene estadísticas filtradas por rango de fechas
     */
    public function getEstadisticasFiltradas(Request $request)
    {
        $fechaInicio = Carbon::parse($request->get('fecha_inicio', now()->startOfMonth()->format('Y-m-d')));
        $fechaFin = Carbon::parse($request->get('fecha_fin', now()->format('Y-m-d')));
        
        // Usar el período académico correcto
        $periodo = SemesterHelper::getCurrentPeriod();

        // Obtener clases no realizadas en el rango (módulos individuales)
        $clasesNoRealizadasRaw = ClaseNoRealizada::whereBetween('fecha_clase', [$fechaInicio, $fechaFin])
            ->with(['asignatura', 'profesor', 'modulo'])
            ->get();

        // =====================================================
        // AGRUPAR MÓDULOS CONSECUTIVOS COMO UNA SOLA CLASE
        // Una clase = misma asignatura + profesor + espacio + fecha
        // =====================================================
        $clasesAgrupadas = $clasesNoRealizadasRaw->groupBy(function($clase) {
            return $clase->id_asignatura . '-' . 
                   $clase->run_profesor . '-' . 
                   $clase->id_espacio . '-' . 
                   $clase->fecha_clase->format('Y-m-d');
        });

        // Crear colección de clases únicas
        $clasesNoRealizadas = collect();
        foreach ($clasesAgrupadas as $key => $modulos) {
            $primerModulo = $modulos->first();
            $estadoClase = $modulos->contains('estado', 'recuperada') ? 'recuperada' : 'pendiente';
            
            $claseAgrupada = clone $primerModulo;
            $claseAgrupada->modulos_count = $modulos->count();
            $claseAgrupada->estado = $estadoClase;
            
            $clasesNoRealizadas->push($claseAgrupada);
        }

        // Obtener todas las planificaciones del período
        $planificacionesRaw = Planificacion_Asignatura::whereHas('horario', function($q) use ($periodo) {
            $q->where('periodo', $periodo);
        })->with('modulo')->get();

        // Agrupar planificaciones por clase (no por módulo)
        $planificacionesAgrupadas = $planificacionesRaw->groupBy(function($plan) {
            $dia = $plan->modulo ? strtolower($plan->modulo->dia) : 'sin_dia';
            return $plan->id_asignatura . '-' . $plan->id_espacio . '-' . $dia;
        });

        // Calcular días laborales en el rango (Lun-Vie + Sábados hasta 13:00)
        $diasTotales = 0;
        $diasLaborales = 0;
        $porDiaSemana = [
            'Lunes' => ['realizadas' => 0, 'no_realizadas' => 0, 'total' => 0],
            'Martes' => ['realizadas' => 0, 'no_realizadas' => 0, 'total' => 0],
            'Miércoles' => ['realizadas' => 0, 'no_realizadas' => 0, 'total' => 0],
            'Jueves' => ['realizadas' => 0, 'no_realizadas' => 0, 'total' => 0],
            'Viernes' => ['realizadas' => 0, 'no_realizadas' => 0, 'total' => 0],
            'Sábado' => ['realizadas' => 0, 'no_realizadas' => 0, 'total' => 0],
        ];
        
        $diasSemana = ['domingo', 'lunes', 'martes', 'miercoles', 'jueves', 'viernes', 'sabado'];
        $diasSemanaES = ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'];
        
        // Contar CLASES planeadas (agrupadas) por día de semana en el rango
        for ($fecha = $fechaInicio->copy(); $fecha->lte($fechaFin); $fecha->addDay()) {
            $diasTotales++;
            
            // Solo días laborales (Lun-Vie y Sábados)
            if ($fecha->isWeekday() || $fecha->isSaturday()) {
                $diasLaborales++;
                $diaSemanaIndex = $fecha->dayOfWeek;
                $diaSemanaKey = $diasSemanaES[$diaSemanaIndex];
                
                if ($diaSemanaKey !== 'Domingo') {
                    // Contar CLASES (no módulos) planeadas para este día de la semana
                    $diaIngles = strtolower($diasSemana[$diaSemanaIndex]);
                    $clasesDelDia = 0;
                    
                    foreach ($planificacionesAgrupadas as $key => $modulos) {
                        $primerPlan = $modulos->first();
                        if ($primerPlan && $primerPlan->modulo && strtolower($primerPlan->modulo->dia) === $diaIngles) {
                            $clasesDelDia++; // +1 por clase, no por módulo
                        }
                    }
                    
                    $porDiaSemana[$diaSemanaKey]['total'] += $clasesDelDia;
                    $porDiaSemana[$diaSemanaKey]['realizadas'] += $clasesDelDia;
                }
            }
        }

        // Restar clases no realizadas (ya agrupadas)
        foreach ($clasesNoRealizadas as $clase) {
            $diaSemanaIndex = $clase->fecha_clase->dayOfWeek;
            $diaSemanaKey = $diasSemanaES[$diaSemanaIndex];
            
            if (isset($porDiaSemana[$diaSemanaKey])) {
                $porDiaSemana[$diaSemanaKey]['no_realizadas']++;
                $porDiaSemana[$diaSemanaKey]['realizadas']--;
            }
        }

        // Calcular totales (usando clases agrupadas)
        $totalRealizadas = collect($porDiaSemana)->sum('realizadas');
        $totalNoRealizadas = $clasesNoRealizadas->count();
        $clasesRecuperadas = $clasesNoRealizadas->where('estado', 'recuperada')->count();
        $clasesPendientes = $clasesNoRealizadas->where('estado', 'pendiente')->count();
        $total = $totalRealizadas + $totalNoRealizadas;
        
        $porcentajeRealizadas = $total > 0 ? round(($totalRealizadas / $total) * 100, 1) : 0;
        $porcentajeNoRealizadas = $total > 0 ? round(($totalNoRealizadas / $total) * 100, 1) : 0;
        $porcentajeRecuperadas = $totalNoRealizadas > 0 ? round(($clasesRecuperadas / $totalNoRealizadas) * 100, 1) : 0;
        $promedioDiario = $diasLaborales > 0 ? round($total / $diasLaborales, 1) : 0;

        return response()->json([
            'realizadas' => max(0, $totalRealizadas),
            'no_realizadas' => $totalNoRealizadas,
            'recuperadas' => $clasesRecuperadas,
            'pendientes' => $clasesPendientes,
            'total' => max(0, $total),
            'porcentaje_realizadas' => $porcentajeRealizadas,
            'porcentaje_no_realizadas' => $porcentajeNoRealizadas,
            'porcentaje_recuperadas' => $porcentajeRecuperadas,
            'dias_totales' => $diasTotales,
            'dias_laborales' => $diasLaborales,
            'promedio_diario' => $promedioDiario,
            'por_dia_semana' => $porDiaSemana,
            'fecha_inicio' => $fechaInicio->format('d/m/Y'),
            'fecha_fin' => $fechaFin->format('d/m/Y')
        ]);
    }

    public function obtenerDatosGraficosAjax(Request $request)
    {
        $tenant = Tenant::current();
        $facultadContext = 'IT_' . ($tenant ? $tenant->sede_id : 'TH');
        $facultad = $request->query('facultad') ?: $facultadContext;
        $piso = $request->query('piso');
        $tipo = $request->query('tipo');
        
        // Obtener fechas de rango (si se proporcionan)
        $fechaInicio = $request->query('fecha_inicio') 
            ? Carbon::parse($request->query('fecha_inicio')) 
            : null;
        $fechaFin = $request->query('fecha_fin') 
            ? Carbon::parse($request->query('fecha_fin')) 
            : null;

        $datos = [];

        if ($tipo === 'ocupacion_turno') {
            $datos = $this->obtenerOcupacionPorTurno($facultad, $piso, $fechaInicio, $fechaFin);
        } elseif ($tipo === 'ocupacion_tipo') {
            $datos = $this->obtenerOcupacionPorTipo($facultad, $piso, $fechaInicio, $fechaFin);
        } elseif ($tipo === 'ocupacion_sala') {
            $datos = $this->obtenerOcupacionPorSala($facultad, $piso, $fechaInicio, $fechaFin);
        } elseif ($tipo === 'salas_tipo') {
            $datos = $this->obtenerSalasPorTipoPorDia($facultad, $piso, $fechaInicio, $fechaFin);
        } elseif ($tipo === 'salas_individual') {
            $datos = $this->obtenerSalasUtilizadasPorDia($facultad, $piso, $fechaInicio, $fechaFin);
        }

        return response()->json($datos);
    }

    /**
     * Obtiene datos de gráficos filtrados por rango de fechas personalizado
     */
    public function getGraficosRango(Request $request)
    {
        $tenant = Tenant::current();
        $facultad = 'IT_' . ($tenant ? $tenant->sede_id : 'TH');
        $piso = $request->session()->get('piso');
        
        $fechaInicio = $request->query('fecha_inicio') 
            ? Carbon::parse($request->query('fecha_inicio')) 
            : Carbon::now()->startOfWeek();
        $fechaFin = $request->query('fecha_fin') 
            ? Carbon::parse($request->query('fecha_fin')) 
            : Carbon::now()->endOfWeek();
        
        // Validar que fecha_inicio sea anterior a fecha_fin
        if ($fechaInicio->gt($fechaFin)) {
            return response()->json(['error' => 'La fecha de inicio debe ser anterior a la fecha de fin'], 400);
        }
        
        // Calcular los días en el rango
        $diasEnRango = [];
        $current = $fechaInicio->copy();
        while ($current->lte($fechaFin)) {
            $diaSemana = $current->format('l'); // Nombre del día en inglés
            $nombreDia = $this->traducirDia($diaSemana);
            if ($nombreDia !== 'Domingo') { // Excluir domingos
                $diasEnRango[] = [
                    'fecha' => $current->copy(),
                    'nombre' => $nombreDia,
                    'etiqueta' => $nombreDia . ' ' . $current->format('d/m')
                ];
            }
            $current->addDay();
        }
        
        // Obtener datos para cada gráfico
        $usoPorDia = $this->obtenerUsoPorDiaRango($facultad, $piso, $diasEnRango, $fechaInicio, $fechaFin);
        $ocupacionPorDia = $this->obtenerOcupacionPorDiaRango($facultad, $piso, $diasEnRango, $fechaInicio, $fechaFin);
        $salasUtilizadasPorDia = $this->obtenerSalasUtilizadasPorDiaRango($facultad, $piso, $diasEnRango, $fechaInicio, $fechaFin);
        $disponibilidadSalas = $this->obtenerDisponibilidadSalasRango($facultad, $piso, $diasEnRango, $fechaInicio, $fechaFin);
        
        return response()->json([
            'usoPorDia' => $usoPorDia,
            'ocupacionPorDia' => $ocupacionPorDia,
            'salasUtilizadasPorDia' => $salasUtilizadasPorDia,
            'disponibilidadSalas' => $disponibilidadSalas,
        ]);
    }
    
    /**
     * Traduce el nombre del día de inglés a español
     */
    private function traducirDia($diaIngles)
    {
        $traducciones = [
            'Monday' => 'Lunes',
            'Tuesday' => 'Martes',
            'Wednesday' => 'Miércoles',
            'Thursday' => 'Jueves',
            'Friday' => 'Viernes',
            'Saturday' => 'Sábado',
            'Sunday' => 'Domingo'
        ];
        return $traducciones[$diaIngles] ?? $diaIngles;
    }
    
    /**
     * Obtiene uso por día para un rango de fechas personalizado
     */
    private function obtenerUsoPorDiaRango($facultad, $piso, $diasEnRango, $fechaInicio, $fechaFin)
    {
        $usoPorDia = [];
        $labels = [];
        
        foreach ($diasEnRango as $diaInfo) {
            $dia = $diaInfo['fecha'];
            $etiqueta = $diaInfo['etiqueta'];
            
            $cantidadReservas = Reserva::whereDate('fecha_reserva', $dia)
                ->whereIn('estado', ['activa', 'finalizada'])
                ->whereHas('espacio', function($query) use ($piso, $facultad) {
                    if ($piso) {
                        $query->whereHas('piso', function($q) use ($piso, $facultad) {
                            $q->where('id_facultad', $facultad);
                            $q->where('numero_piso', $piso);
                        });
                    } elseif ($facultad) {
                        $query->whereHas('piso', function($q) use ($facultad) {
                            $q->where('id_facultad', $facultad);
                        });
                    }
                    $query->where('tipo_espacio', 'Sala de Clases');
                })
                ->count();
            
            $usoPorDia[$etiqueta] = $cantidadReservas;
            $labels[] = $etiqueta;
        }
        
        return [
            'datos' => $usoPorDia,
            'labels' => $labels,
            'rango_fechas' => [
                'inicio' => $fechaInicio->format('d/m/Y'),
                'fin' => $fechaFin->format('d/m/Y')
            ]
        ];
    }
    
    /**
     * Obtiene ocupación por día para un rango de fechas personalizado
     */
    private function obtenerOcupacionPorDiaRango($facultad, $piso, $diasEnRango, $fechaInicio, $fechaFin)
    {
        $ocupacionPorDia = [];
        $labels = [];
        
        // Obtener total de salas
        $totalSalas = Espacio::whereHas('piso', function($query) use ($facultad, $piso) {
            if ($piso) {
                $query->where('id_facultad', $facultad);
                $query->where('numero_piso', $piso);
            } elseif ($facultad) {
                $query->where('id_facultad', $facultad);
            }
        })
        ->where('tipo_espacio', 'Sala de Clases')
        ->count();
        
        $modulosPorDia = 15; // Módulos disponibles por día
        $capacidadTotal = $totalSalas * $modulosPorDia;
        
        foreach ($diasEnRango as $diaInfo) {
            $dia = $diaInfo['fecha'];
            $etiqueta = $diaInfo['etiqueta'];
            
            // Calcular módulos utilizados
            $reservas = Reserva::whereDate('fecha_reserva', $dia)
                ->whereIn('estado', ['activa', 'finalizada'])
                ->whereHas('espacio', function($query) use ($piso, $facultad) {
                    if ($piso) {
                        $query->whereHas('piso', function($q) use ($piso, $facultad) {
                            $q->where('id_facultad', $facultad);
                            $q->where('numero_piso', $piso);
                        });
                    } elseif ($facultad) {
                        $query->whereHas('piso', function($q) use ($facultad) {
                            $q->where('id_facultad', $facultad);
                        });
                    }
                    $query->where('tipo_espacio', 'Sala de Clases');
                })
                ->get();
            
            $modulosUtilizados = 0;
            foreach ($reservas as $reserva) {
                $modulosUtilizados += $this->calcularModulosReales(
                    $reserva->hora,
                    $reserva->hora_salida,
                    $reserva->modulos
                );
            }
            
            $porcentaje = $capacidadTotal > 0 
                ? round(($modulosUtilizados / $capacidadTotal) * 100, 2) 
                : 0;
            
            $ocupacionPorDia[$etiqueta] = $porcentaje;
            $labels[] = $etiqueta;
        }
        
        return [
            'datos' => $ocupacionPorDia,
            'labels' => $labels,
            'rango_fechas' => [
                'inicio' => $fechaInicio->format('d/m/Y'),
                'fin' => $fechaFin->format('d/m/Y')
            ]
        ];
    }
    
    /**
     * Obtiene salas utilizadas por día para un rango de fechas personalizado
     */
    private function obtenerSalasUtilizadasPorDiaRango($facultad, $piso, $diasEnRango, $fechaInicio, $fechaFin)
    {
        $labels = array_map(function($d) { return $d['etiqueta']; }, $diasEnRango);
        
        // Obtener todas las salas
        $salas = Espacio::whereHas('piso', function($query) use ($facultad, $piso) {
            if ($piso) {
                $query->where('id_facultad', $facultad);
                $query->where('numero_piso', $piso);
            } elseif ($facultad) {
                $query->where('id_facultad', $facultad);
            }
        })
        ->where('tipo_espacio', 'Sala de Clases')
        ->orderBy('id_espacio')
        ->get();
        
        $dataPorSala = [];
        
        foreach ($salas as $sala) {
            $reservasPorDia = [];
            
            foreach ($diasEnRango as $diaInfo) {
                $dia = $diaInfo['fecha'];
                
                $cantidadReservas = Reserva::where('id_espacio', $sala->id_espacio)
                    ->whereDate('fecha_reserva', $dia)
                    ->whereIn('estado', ['activa', 'finalizada'])
                    ->count();
                
                $reservasPorDia[] = $cantidadReservas;
            }
            
            $dataPorSala[] = [
                'sala' => $sala->id_espacio,
                'datos' => $reservasPorDia
            ];
        }
        
        return [
            'salas' => $dataPorSala,
            'labels' => $labels,
            'rango_fechas' => [
                'inicio' => $fechaInicio->format('d/m/Y'),
                'fin' => $fechaFin->format('d/m/Y')
            ]
        ];
    }
    
    /**
     * Obtiene disponibilidad de salas para un rango de fechas personalizado
     */
    private function obtenerDisponibilidadSalasRango($facultad, $piso, $diasEnRango, $fechaInicio, $fechaFin)
    {
        $labels = array_map(function($d) { return $d['etiqueta']; }, $diasEnRango);
        
        // Obtener total de salas
        $totalSalas = Espacio::whereHas('piso', function($query) use ($facultad, $piso) {
            if ($piso) {
                $query->where('id_facultad', $facultad);
                $query->where('numero_piso', $piso);
            } elseif ($facultad) {
                $query->where('id_facultad', $facultad);
            }
        })
        ->where('tipo_espacio', 'Sala de Clases')
        ->count();
        
        $disponibilidadPorDia = [];
        $modulosPorDia = 15;
        $capacidadTotal = $totalSalas * $modulosPorDia;
        
        foreach ($diasEnRango as $diaInfo) {
            $dia = $diaInfo['fecha'];
            
            // Calcular módulos ocupados
            $reservas = Reserva::whereDate('fecha_reserva', $dia)
                ->whereIn('estado', ['activa', 'finalizada'])
                ->whereHas('espacio', function($query) use ($piso, $facultad) {
                    if ($piso) {
                        $query->whereHas('piso', function($q) use ($piso, $facultad) {
                            $q->where('id_facultad', $facultad);
                            $q->where('numero_piso', $piso);
                        });
                    } elseif ($facultad) {
                        $query->whereHas('piso', function($q) use ($facultad) {
                            $q->where('id_facultad', $facultad);
                        });
                    }
                    $query->where('tipo_espacio', 'Sala de Clases');
                })
                ->get();
            
            $modulosOcupados = 0;
            foreach ($reservas as $reserva) {
                $modulosOcupados += $this->calcularModulosReales(
                    $reserva->hora,
                    $reserva->hora_salida,
                    $reserva->modulos
                );
            }
            
            $porcentajeDisponible = $capacidadTotal > 0 
                ? round((($capacidadTotal - $modulosOcupados) / $capacidadTotal) * 100, 2) 
                : 100;
            
            $disponibilidadPorDia[] = max(0, $porcentajeDisponible);
        }
        
        return [
            'datos' => $disponibilidadPorDia,
            'labels' => $labels,
            'totalSalas' => $totalSalas,
            'rango_fechas' => [
                'inicio' => $fechaInicio->format('d/m/Y'),
                'fin' => $fechaFin->format('d/m/Y')
            ]
        ];
    }
}
