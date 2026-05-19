<?php
namespace App\Http\Controllers;

use App\Helpers\SemesterHelper;
use App\Models\Asignatura;
use App\Models\ClaseNoRealizada;
use App\Models\Espacio;
use App\Models\Mapa;
use App\Models\Modulo;
use App\Models\Piso;
use App\Models\Planificacion_Asignatura;
use App\Models\Profesor;
use App\Models\RecuperacionClase;
use App\Models\Reserva;
use App\Models\Tenant;
use App\Models\User;
use App\Services\OccupancyService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DashboardController extends Controller
{
    use \App\Traits\DashboardCalculationsTrait;

    protected $occupancyService;

    public function __construct(OccupancyService $occupancyService)
    {
        $this->occupancyService = $occupancyService;
    }

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
    private function ensureTenantContext(): ?Tenant
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
        $pisos = Piso::whereHas('facultad', function ($query) use ($sedeId, $facultad) {
            $query
                ->where('id_facultad', $facultad)
                ->where('id_sede', $sedeId);
        })
            ->orderBy('numero_piso')
            ->get();

        // SOLO CARGAR DATOS ESENCIALES PARA LOS KPIs
        // Usar OccupancyService para garantizar coherencia
        $ocupacionSemanal = [
            'diurno' => $this->occupancyService->calcularOcupacionSemanal($facultad, $piso, 'diurno'),
            'vespertino' => $this->occupancyService->calcularOcupacionSemanal($facultad, $piso, 'vespertino'),
            'total' => $this->occupancyService->calcularOcupacionSemanal($facultad, $piso)
        ];

        $ocupacionMensual = [
            'diurno' => $this->occupancyService->calcularOcupacionMensual($facultad, $piso, 'diurno'),
            'vespertino' => $this->occupancyService->calcularOcupacionMensual($facultad, $piso, 'vespertino'),
            'total' => $this->occupancyService->calcularOcupacionMensual($facultad, $piso)
        ];

        $salasOcupadas = [
            'diurno' => $this->occupancyService->obtenerSalasOcupadas($facultad, $piso, 'diurno'),
            'vespertino' => $this->occupancyService->obtenerSalasOcupadas($facultad, $piso, 'vespertino'),
            'total' => $this->occupancyService->obtenerSalasOcupadas($facultad, $piso)
        ];

        // Obtener TODOS los espacios ocupados (incluyendo laboratorios, talleres, etc.) para el gráfico de torta
        $espaciosOcupadosTotal = $this->occupancyService->obtenerEspaciosOcupadosTotal($facultad, $piso);

        // Total de reservas hoy y sala más utilizada (solo queries ligeras)
        $totalReservasHoy = Reserva::whereDate('fecha_reserva', today())
            ->count();

        // Sala con más reservas hoy
        $salaMasReservas = Reserva::select('id_espacio', DB::raw('count(*) as total'))
            ->whereDate('fecha_reserva', today())
            ->groupBy('id_espacio')
            ->orderByDesc('total')
            ->with('espacio:id_espacio,nombre_espacio')
            ->first();

        // Sala con mayor ocupación (módulos utilizados / 15)
        $salaMasUtilizada = Reserva::select('id_espacio', DB::raw('count(*) as total'))
            ->whereDate('fecha_reserva', today())
            ->groupBy('id_espacio')
            ->with('espacio:id_espacio,nombre_espacio')
            ->get()
            ->map(function ($item) {
                $item->ocupacion_modulos = ($item->total / 15) * 100;
                return $item;
            })
            ->sortByDesc('ocupacion_modulos')
            ->first();

        // Datos para gráficos de la primera pestaña (se cargan inicialmente)
        $usoPorDia = $this->occupancyService->obtenerUsoPorDia($facultad, $piso);
        $salasUtilizadasPorDia = $this->occupancyService->obtenerSalasUtilizadasPorDia($facultad, $piso);
        $ocupacionPorDia = $this->occupancyService->obtenerOcupacionPorDia($facultad, $piso);
        $salasPorTipoPorDia = $this->obtenerSalasPorTipoPorDia($facultad, $piso);
        $ocupacionPorTurno = $this->occupancyService->obtenerOcupacionPorTurno($facultad, $piso);
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
            ->whereHas('espacio', function ($query) use ($facultad, $piso) {
                $query->whereHas('piso', function ($q) use ($facultad, $piso) {
                    $q->where('id_facultad', $facultad);
                    if ($piso) {
                        $q->where('numero_piso', $piso);
                    }
                });
            })
            ->orderBy('fecha_reserva', 'desc')
            ->get();

        // Obtener el nombre del día en español (coincidente con la tabla modulos)
        $diaActualModulo = $this->getNombreDiaEspanol(Carbon::now());

        // Obtener módulo actual
        $moduloActual = Modulo::where('dia', $diaActualModulo)
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
        $clasesAgrupadas = $clasesNoRealizadasHoyRaw->groupBy(function ($clase) {
            return $clase->id_asignatura . '-'
                . $clase->run_profesor . '-'
                . $clase->id_espacio . '-'
                . $clase->fecha_clase->format('Y-m-d');
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


    /**
     * Calcula las horas totales disponibles para un turno
     * @param string|null $turno 'diurno', 'vespertino' o null para todos
     * @param Carbon|null $fecha Fecha para determinar si es sábado
     * @return int|float Horas disponibles en el turno
     */


    /**
     * Calcula las horas utilizadas desde planificaciones para un rango de fechas
     * @param Carbon $inicio Fecha inicial
     * @param Carbon $fin Fecha final
     * @param string|null $piso Filtro opcional por piso
     * @param string|null $tipoEspacio Filtro opcional por tipo de espacio
     * @param string|null $turno Filtro opcional por turno ('diurno', 'vespertino' o null)
     * @return float Total de horas utilizadas
     */


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








    /**
     * Obtener espacios ocupados/libres contando TODOS los tipos (para gráfico de torta)
     */


















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
            ->when($piso, function ($query) use ($piso) {
                return $query->whereHas('espacio', function ($q) use ($piso) {
                    $q->whereHas('piso', function ($inner) use ($piso) {
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
            ->when($piso, function ($query) use ($piso) {
                return $query->whereHas('espacio', function ($q) use ($piso) {
                    $q->whereHas('piso', function ($inner) use ($piso) {
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

    public function getWidgetData(Request $request): \Illuminate\Http\JsonResponse
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

        // Total de reservas hoy y sala más utilizada (mismo cálculo que en index)
        $totalReservasHoy = Reserva::whereDate('fecha_reserva', today())->count();

        $salaMasReservas = Reserva::select('id_espacio', DB::raw('count(*) as total'))
            ->whereDate('fecha_reserva', today())
            ->groupBy('id_espacio')
            ->orderByDesc('total')
            ->with('espacio:id_espacio,nombre_espacio')
            ->first();

        $salaMasUtilizada = Reserva::select('id_espacio', DB::raw('count(*) as total'))
            ->whereDate('fecha_reserva', today())
            ->groupBy('id_espacio')
            ->with('espacio:id_espacio,nombre_espacio')
            ->get()
            ->map(function ($item) {
                $item->ocupacion_modulos = ($item->total / 15) * 100;
                return $item;
            })
            ->sortByDesc('ocupacion_modulos')
            ->first();

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
            'totalReservasHoy' => (int) $totalReservasHoy,
            'salasUtilizadas' => [
                'mas_reservas' => $salaMasReservas,
                'mas_ocupada' => $salaMasUtilizada
            ],
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
                $query
                    ->where('dia', strtolower($now->locale('es')->isoFormat('dddd')))
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
            $profesor = $plan->horario->profesor->name ?? 'Profesor no asignado';
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
            ->whereHas('modulo', function ($q) use ($fecha) {
                $dia = $this->getNombreDiaEspanol(Carbon::parse($fecha));
                $q->where('dia', $dia);
            })
            ->whereHas('horario', function ($q) use ($periodo) {
                $q->where('periodo', $periodo);
            })
            ->get();

        $noUtilizadasDia = [];
        foreach ($planificaciones as $plan) {
            $usuario = $plan->horario->profesor->name ?? null;
            $espacio = $plan->espacio->nombre_espacio ?? null;
            $modulo = $plan->modulo->hora_inicio . ' - ' . $plan->modulo->hora_termino;
            $fechaPlan = $fecha;
            if (!$usuario || !$espacio)
                continue;

            $reservaOcupada = Reserva::where('id_espacio', $plan->espacio->id_espacio)
                ->where('id_usuario', $plan->horario->profesor->run_profesor ?? null)
                ->whereDate('fecha_reserva', $fecha)
                ->where('hora_planificada', $plan->modulo->hora_inicio)
                ->where('estado', 'activa')
                ->whereHas('espacio', function ($q) {
                    $q->where('estado', 'Ocupado');
                })
                ->exists();

            if (!$reservaOcupada) {
                $noUtilizadasDia[] = [
                    'usuario' => $usuario,
                    'espacio' => $espacio,
                    'fecha' => Carbon::parse($fechaPlan)->format('d/m/Y'),
                    'modulo' => $modulo,
                ];
            }
        }
        return view('partials.tabla_no_utilizadas_dia', compact('noUtilizadasDia'))->render();
    }

    public function horariosActualAjax(Request $request)
    {
        $diaActual = $this->getNombreDiaEspanol(now());
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

        $asignaciones = Planificacion_Asignatura::with(['espacio.piso', 'asignatura', 'horario.profesor'])
            ->where('id_modulo', $idModulo)
            ->whereHas('horario', function ($q) use ($periodo) {
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








    /**
     * Calcular horas utilizadas de una reserva individual
     * Método auxiliar para evitar duplicación de código
     */


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
            ->whereNotExists(function ($query) {
                $query
                    ->select(DB::raw(1))
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
        $clasesAgrupadas = $clasesNoRealizadasRaw->groupBy(function ($clase) {
            return $clase->id_asignatura . '-'
                . $clase->run_profesor . '-'
                . $clase->id_espacio . '-'
                . $clase->fecha_clase->format('Y-m-d');
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
        $planificacionesMesRaw = Planificacion_Asignatura::whereHas('horario', function ($q) use ($periodo) {
            $q->where('periodo', $periodo);
        })->with(['modulo', 'asignatura'])->get();

        // =====================================================
        // AGRUPAR PLANIFICACIONES POR CLASE (no por módulo)
        // Una clase = misma asignatura + espacio + día de semana
        // =====================================================
        $planificacionesAgrupadas = $planificacionesMesRaw->groupBy(function ($plan) {
            $dia = $plan->modulo ? strtolower($plan->modulo->dia) : 'sin_dia';
            return $plan->id_asignatura . '-' . $plan->id_espacio . '-' . $dia;
        });

        // =====================================================
        // CARGAR RESERVAS DEL MES PARA VERIFICAR CLASES REALIZADAS
        // Una clase solo se considera "realizada" si tiene una reserva
        // =====================================================
        $reservasMes = Reserva::whereMonth('fecha_reserva', $mes)
            ->whereYear('fecha_reserva', $anio)
            ->where('fecha_reserva', '<=', $hoy)
            ->whereIn('estado', ['activa', 'finalizada'])
            ->whereNotNull('hora')
            ->get();

        // Construir lookup indexado por fecha|espacio para búsqueda rápida
        $reservasLookup = [];
        foreach ($reservasMes as $reserva) {
            $fechaKey = $reserva->fecha_reserva->format('Y-m-d');
            $keyBase = $fechaKey . '|' . $reserva->id_espacio;
            $reservasLookup[$keyBase] = true;
            // También indexar por asignatura si está disponible
            if ($reserva->id_asignatura) {
                $keyAsig = $keyBase . '|' . $reserva->id_asignatura;
                $reservasLookup[$keyAsig] = true;
            }
        }

        // Agrupar por día para el gráfico de barras - SOLO HASTA HOY
        $diasDelMes = [];
        $inicio = Carbon::create($anio, $mes, 1);
        $fin = $hoy->copy();  // Solo hasta hoy, no fin de mes
        $dias = ['domingo', 'lunes', 'martes', 'miercoles', 'jueves', 'viernes', 'sabado'];

        // Inicializar días (de lunes a sábado, excluyendo domingo, solo hasta hoy)
        for ($fecha = $inicio->copy(); $fecha->lte($fin); $fecha->addDay()) {
            // Solo días laborales (Lun-Vie y Sábados), NO domingos
            if ($fecha->isWeekday() || $fecha->isSaturday()) {
                $dia = $fecha->format('d/m');
                $diasDelMes[$dia] = [
                    'realizadas' => 0,
                    'no_realizadas' => 0,
                    'por_realizar' => 0,  // Clases pendientes para hoy (aún no han llegado a la hora)
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
        // Una clase se considera "realizada" SOLO si hay una reserva que lo respalde
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
                                    continue;
                                }
                            }

                            // Verificar si existe una reserva para esta clase en esta fecha
                            $fechaStr = $fecha->format('Y-m-d');
                            $lookupKeyAsig = $fechaStr . '|' . $primerPlan->id_espacio . '|' . $primerPlan->id_asignatura;
                            $lookupKeyBase = $fechaStr . '|' . $primerPlan->id_espacio;

                            // Primero buscar por espacio+asignatura (más preciso), luego solo espacio
                            $tieneReserva = isset($reservasLookup[$lookupKeyAsig]) || isset($reservasLookup[$lookupKeyBase]);

                            if ($tieneReserva) {
                                $diasDelMes[$diaFormato]['realizadas']++;
                            } else {
                                // No hay reserva → la clase no se realizó
                                $diasDelMes[$diaFormato]['no_realizadas']++;
                            }
                        }
                    }
                }
            }
        }

        // Agregar detalle de clases no realizadas (ya agrupadas)
        // Los conteos ya se calcularon arriba basándose en reservas,
        // aquí solo agregamos la información de detalle y recuperaciones
        foreach ($clasesNoRealizadas as $clase) {
            $diaFormato = $clase->fecha_clase->format('d/m');
            if (isset($diasDelMes[$diaFormato])) {
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
        $datosRealizadas = array_values(array_map(function ($d) {
            return max(0, $d['realizadas']);
        }, $diasDelMes));
        $datosNoRealizadas = array_values(array_map(function ($d) {
            return $d['no_realizadas'];
        }, $diasDelMes));
        $datosPorRealizar = array_values(array_map(function ($d) {
            return $d['por_realizar'] ?? 0;
        }, $diasDelMes));
        $datosRecuperadas = array_values(array_map(function ($d) {
            return $d['recuperadas'];
        }, $diasDelMes));

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
        $clasesAgrupadas = $clasesNoRealizadasRaw->groupBy(function ($clase) {
            return $clase->id_asignatura . '-'
                . $clase->run_profesor . '-'
                . $clase->id_espacio . '-'
                . $clase->fecha_clase->format('Y-m-d');
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
        $planificacionesRaw = Planificacion_Asignatura::whereHas('horario', function ($q) use ($periodo) {
            $q->where('periodo', $periodo);
        })->with('modulo')->get();

        // Agrupar planificaciones por clase (no por módulo)
        $planificacionesAgrupadas = $planificacionesRaw->groupBy(function ($plan) {
            $dia = $plan->modulo ? strtolower($plan->modulo->dia) : 'sin_dia';
            return $plan->id_asignatura . '-' . $plan->id_espacio . '-' . $dia;
        });

        // Cargar reservas del rango para verificar clases realizadas
        $reservasRango = Reserva::whereBetween('fecha_reserva', [$fechaInicio, $fechaFin])
            ->whereIn('estado', ['activa', 'finalizada'])
            ->whereNotNull('hora')
            ->get();

        // Construir lookup indexado por fecha|espacio
        $reservasLookupFiltro = [];
        foreach ($reservasRango as $reserva) {
            $fechaKey = $reserva->fecha_reserva->format('Y-m-d');
            $keyBase = $fechaKey . '|' . $reserva->id_espacio;
            $reservasLookupFiltro[$keyBase] = true;
            if ($reserva->id_asignatura) {
                $reservasLookupFiltro[$keyBase . '|' . $reserva->id_asignatura] = true;
            }
        }

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
                    $fechaStr = $fecha->format('Y-m-d');

                    foreach ($planificacionesAgrupadas as $key => $modulos) {
                        $primerPlan = $modulos->first();
                        if ($primerPlan && $primerPlan->modulo && strtolower($primerPlan->modulo->dia) === $diaIngles) {
                            $porDiaSemana[$diaSemanaKey]['total']++;

                            // Verificar si existe reserva para esta clase
                            $lookupKeyAsig = $fechaStr . '|' . $primerPlan->id_espacio . '|' . $primerPlan->id_asignatura;
                            $lookupKeyBase = $fechaStr . '|' . $primerPlan->id_espacio;
                            $tieneReserva = isset($reservasLookupFiltro[$lookupKeyAsig]) || isset($reservasLookupFiltro[$lookupKeyBase]);

                            if ($tieneReserva) {
                                $porDiaSemana[$diaSemanaKey]['realizadas']++;
                            } else {
                                $porDiaSemana[$diaSemanaKey]['no_realizadas']++;
                            }
                        }
                    }
                }
            }
        }

        // Calcular totales (usando conteos basados en reservas)
        $totalRealizadas = collect($porDiaSemana)->sum('realizadas');
        $totalNoRealizadas = collect($porDiaSemana)->sum('no_realizadas');
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
            $diaSemana = $current->format('l');  // Nombre del día en inglés
            $nombreDia = $this->traducirDia($diaSemana);
            if ($nombreDia !== 'Domingo') {  // Excluir domingos
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


    /**
     * Obtiene uso por día para un rango de fechas personalizado
     */


    /**
     * Obtiene ocupación por día para un rango de fechas personalizado
     */


    /**
     * Obtiene salas utilizadas por día para un rango de fechas personalizado
     */


    /**
     * Obtiene disponibilidad de salas para un rango de fechas personalizado
     */


    /**
     * Devuelve el nombre del día en español tal como está en el seeder de módulos.
     * Esto asegura que las consultas a la tabla Modulos funcionen correctamente
     * independientemente de la configuración local del sistema.
     */

}
