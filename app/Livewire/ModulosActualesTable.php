<?php

namespace App\Livewire;

use App\Helpers\SemesterHelper;
use App\Models\Asistencia;
use App\Models\ClaseNoRealizada;
use App\Models\DiaFeriado;
use App\Models\Espacio;
use App\Models\Modulo;
use App\Models\Piso;
use App\Models\Planificacion_Asignatura;
use App\Models\PlanificacionProfesorColaborador;
use App\Models\ProfesorColaborador;
use App\Models\Reserva;
use App\Models\Tenant;
use App\Services\OccupancyService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Livewire\Component;
use Livewire\Attributes\Locked;

class ModulosActualesTable extends Component
{
    public $planificaciones = [];

    public $espacios = [];

    #[Locked]
    public $pisos = [];

    public $horaActual;

    public $fechaActual;

    public $moduloActual;

    public $selectedPiso = null;

    public $esFeriado = false;

    public $nombreFeriado = '';

    public $periodoNoIniciado = false;

    public $nombrePeriodo = '';

    // Propiedades para manejo de tenants
    public $tenantId = null;

    public $tenantsDisponibles = [];

    public $mostrarSelectorSedes = false;

    // Inyección de servicio (no almacenar objetos no serializables)
    // private $occupancyService;



    public function mount()
    {
        set_time_limit(120);
        ini_set('max_execution_time', 120);

        // Obtener tenants disponibles
        $this->tenantsDisponibles = Tenant::where('is_active', true)
            ->with(['sede'])
            ->get()
            ->toArray();

        // Verificar si hay un tenant actual en sesión
        $currentTenant = Tenant::current();
        $this->tenantId = $currentTenant?->id;

        if (!$currentTenant && count($this->tenantsDisponibles) > 0) {
            // Si no hay tenant activo, mostrar selector
            $this->mostrarSelectorSedes = true;
        } else {
            // Si hay tenant, cargar datos
            $this->actualizarDatos();

            // Establecer el primer piso como seleccionado por defecto
            if (count($this->pisos) > 0 && is_array($this->pisos) && isset($this->pisos[0]['id'])) {
                $this->selectedPiso = $this->pisos[0]['id'];
            }
        }
    }

    /**
     * Seleccionar una sede/tenant
     */
    public function seleccionarSede($tenantId)
    {
        $tenant = Tenant::find($tenantId);
        if ($tenant && $tenant->is_active) {
            $tenant->makeCurrent();
            session(['tenant_id' => $tenant->id]);
            $this->tenantId = $tenant->id;
            $this->mostrarSelectorSedes = false;

            // Emitir evento para actualizar el header
            $nombreSede = $tenant->sede?->nombre_sede ?? $tenant->name ?? 'Sede';
            $this->dispatch('sedes:seleccionada', ['nombre' => $nombreSede]);

            $this->actualizarDatos();

            // Establecer el primer piso como seleccionado por defecto
            if (count($this->pisos) > 0 && is_array($this->pisos) && isset($this->pisos[0]['id'])) {
                $this->selectedPiso = $this->pisos[0]['id'];
            }
        }
    }

    /**
     * Obtener la próxima clase para un espacio específico (OPTIMIZADO)
     */
    private function obtenerProximaClase($idEspacio, $periodo, $planificacionesCache = null)
    {
        // DESACTIVADO TEMPORALMENTE para mejorar performance
        // Este método está causando timeouts por múltiples consultas
        return null;

        /*
         * // Si no se proporciona cache, hacer consulta optimizada
         * if ($planificacionesCache === null) {
         *     $dias = ['domingo', 'lunes', 'martes', 'miercoles', 'jueves', 'viernes', 'sabado'];
         *     $diaActual = $dias[Carbon::now()->dayOfWeek];
         *
         *     $diasPrefijos = [
         *         'lunes' => 'LU',
         *         'martes' => 'MA',
         *         'miercoles' => 'MI',
         *         'jueves' => 'JU',
         *         'viernes' => 'VI'
         *     ];
         *
         *     $prefijoDia = $diasPrefijos[$diaActual] ?? '';
         *     if (!$prefijoDia) return null;
         *
         *     $planificacionesCache = Planificacion_Asignatura::with(['asignatura.profesor'])
         *         ->where('id_espacio', $idEspacio)
         *         ->where('id_modulo', 'LIKE', $prefijoDia . '.%')
         *         ->whereHas('horario', function($q) use ($periodo) {
         *             $q->where('periodo', $periodo);
         *         })
         *         ->get();
         * }
         *
         * // Procesar con datos en cache...
         * // Resto de la lógica optimizada
         */
    }

    /**
     * Verificar si una clase debe marcarse como no realizada
     */
    private function verificarClaseNoRealizada($planificacionActiva, $tieneReservaProfesor, $periodo, $moduloActual)
    {
        if (!$planificacionActiva) {
            return false;  // Si no hay clase planificada, no hay nada que verificar
        }

        // Obtener todas las planificaciones de esta asignatura y espacio para encontrar el primer y último módulo
        $todasLasPlanificaciones = Planificacion_Asignatura::with(['modulo'])
            ->where('id_asignatura', $planificacionActiva->id_asignatura)
            ->where('id_espacio', $planificacionActiva->id_espacio)
            ->whereHas('horario', function ($q) use ($periodo) {
                $q->where('periodo', $periodo);
            })
            ->get()
            ->sortBy(function ($planificacion) {
                $moduloParts = explode('.', $planificacion->id_modulo);

                return isset($moduloParts[1]) ? (int) $moduloParts[1] : 0;
            });

        if ($todasLasPlanificaciones->isEmpty()) {
            return false;
        }

        // Obtener el primer y último módulo de la clase
        $primeraPlanificacion = $todasLasPlanificaciones->first();
        $ultimaPlanificacion = $todasLasPlanificaciones->last();

        $primerModuloParts = explode('.', $primeraPlanificacion->id_modulo);
        $ultimoModuloParts = explode('.', $ultimaPlanificacion->id_modulo);

        $numeroPrimerModulo = isset($primerModuloParts[1]) ? (int) $primerModuloParts[1] : 0;
        $numeroUltimoModulo = isset($ultimoModuloParts[1]) ? (int) $ultimoModuloParts[1] : 0;

        // Verificar si el profesor registró entrada HOY en este espacio (independiente del estado)
        $tuvoEntradaHoy = Reserva::where('id_espacio', $planificacionActiva->id_espacio)
            ->where('fecha_reserva', Carbon::now()->toDateString())
            ->whereNotNull('run_profesor')
            ->whereNotNull('hora')  // El profesor sí entró (hora es la hora de entrada)
            ->exists();

        // Si el profesor SÍ registró entrada en este espacio, la clase SÍ se realizó
        if ($tuvoEntradaHoy) {
            return false;  // La clase SÍ se realizó
        }

        // Verificar si el profesor registró entrada HOY en OTRO espacio (cambio de sala)
        $runProfesor = $planificacionActiva->horario->run_profesor ?? $planificacionActiva->asignatura->run_profesor ?? null;
        if ($runProfesor) {
            $tuvoEntradaEnOtroEspacio = Reserva::where('id_espacio', '!=', $planificacionActiva->id_espacio)
                ->where('fecha_reserva', Carbon::now()->toDateString())
                ->where('run_profesor', $runProfesor)
                ->whereNotNull('hora')  // El profesor sí entró
                ->exists();

            // Si el profesor registró entrada en otro espacio, la clase SÍ se realizó (solo en otro lugar)
            if ($tuvoEntradaEnOtroEspacio) {
                return false;  // La clase SÍ se realizó, pero en otro espacio
            }
        }

        // Verificar si la clase ya terminó completamente
        if ($this->verificarClaseFinalizada($numeroUltimoModulo, $moduloActual)) {
            // Si la clase terminó y NO hubo entrada, es clase no realizada
            // Construir el id_modulo completo con todos los módulos de la asignatura programados hoy
            // Extraer el prefijo del día (ej: "MI" de "MI.2")
            $prefijoDia = explode('.', $primeraPlanificacion->id_modulo)[0] ?? '';

            $todosModulos = Planificacion_Asignatura::where('id_asignatura', $planificacionActiva->id_asignatura)
                ->where('id_espacio', $planificacionActiva->id_espacio)
                ->where('id_modulo', 'LIKE', $prefijoDia . '.%')
                ->pluck('id_modulo')
                ->sort()
                ->values()
                ->toArray();

            // Si hay múltiples módulos, crear un string con el rango
            $idModuloCompleto = count($todosModulos) > 1
                ? implode(',', $todosModulos)
                : $primeraPlanificacion->id_modulo;

            ClaseNoRealizada::registrarClaseNoRealizada([
                'id_asignatura' => $planificacionActiva->id_asignatura,
                'id_espacio' => $planificacionActiva->id_espacio,
                'id_modulo' => $idModuloCompleto,
                'run_profesor' => $runProfesor ?? '',
                'fecha_clase' => Carbon::now()->toDateString(),
                'periodo' => $periodo,
                'motivo' => 'No se registró ingreso del profesor durante toda la clase',
            ]);

            return true;
        }

        // Obtener la hora de inicio del primer módulo para calcular el tiempo de gracia
        $diaActual = Carbon::now()->locale('es')->isoFormat('dddd');
        $diaKey = strtolower($diaActual);

        // Mapear días en español a las claves en inglés que usa el array
        $mapaDias = [
            'lunes' => 'lunes',
            'martes' => 'martes',
            'miércoles' => 'miercoles',
            'miercoles' => 'miercoles',
            'jueves' => 'jueves',
            'viernes' => 'viernes',
            'sábado' => 'sabado',
            'sabado' => 'sabado',
        ];

        $diaKey = $mapaDias[$diaKey] ?? $diaKey;
        $horariosDelDia = \App\Helpers\ModulosHelper::getHorariosModulos()[$diaKey] ?? null;

        if (!$horariosDelDia || !isset($horariosDelDia[$numeroPrimerModulo])) {
            return false;
        }

        $horaInicioPrimerModulo = $horariosDelDia[$numeroPrimerModulo]['inicio'];
        $horaActual = Carbon::now()->format('H:i:s');

        // Calcular el tiempo transcurrido desde el inicio del primer módulo
        $inicioModulo = Carbon::createFromTimeString($horaInicioPrimerModulo);
        $ahora = Carbon::createFromTimeString($horaActual);

        // Solo marcar como no realizada si han pasado 20 minutos desde el inicio del primer módulo
        // Y si NO hay reserva con entrada del profesor (ni en este espacio ni en otro)
        $hasPasado20Minutos = $ahora->gt($inicioModulo) && $ahora->diffInMinutes($inicioModulo) >= 20;

        // Verificar nuevamente si registró entrada en otro espacio
        $tuvoEntradaEnOtroEspacio = false;
        if ($runProfesor) {
            $tuvoEntradaEnOtroEspacio = Reserva::where('id_espacio', '!=', $planificacionActiva->id_espacio)
                ->where('fecha_reserva', Carbon::now()->toDateString())
                ->where('run_profesor', $runProfesor)
                ->whereNotNull('hora')
                ->exists();
        }

        if ($moduloActual && !$tuvoEntradaHoy && !$tuvoEntradaEnOtroEspacio && $hasPasado20Minutos) {
            // Registrar la clase no realizada
            // Construir el id_modulo completo con todos los módulos de la asignatura programados hoy
            // Extraer el prefijo del día (ej: "MI" de "MI.2")
            $prefijoDia = explode('.', $primeraPlanificacion->id_modulo)[0] ?? '';

            $todosModulos = Planificacion_Asignatura::where('id_asignatura', $planificacionActiva->id_asignatura)
                ->where('id_espacio', $planificacionActiva->id_espacio)
                ->where('id_modulo', 'LIKE', $prefijoDia . '.%')
                ->pluck('id_modulo')
                ->sort()
                ->values()
                ->toArray();

            // Si hay múltiples módulos, crear un string con el rango
            $idModuloCompleto = count($todosModulos) > 1
                ? implode(',', $todosModulos)
                : $primeraPlanificacion->id_modulo;

            ClaseNoRealizada::registrarClaseNoRealizada([
                'id_asignatura' => $planificacionActiva->id_asignatura,
                'id_espacio' => $planificacionActiva->id_espacio,
                'id_modulo' => $idModuloCompleto,
                'run_profesor' => $runProfesor ?? '',
                'fecha_clase' => Carbon::now()->toDateString(),
                'periodo' => $periodo,
                'motivo' => 'No se registró ingreso después de 20 minutos del primer módulo programado',
            ]);

            return true;
        }

        return false;
    }

    /**
     * Verificar si una clase ha finalizado
     */
    private function verificarClaseFinalizada($numeroUltimoModulo, $moduloActual)
    {
        if (!$moduloActual || !$numeroUltimoModulo) {
            return false;
        }

        // Solo considerar finalizada si NO estamos en break Y el módulo actual es mayor al último
        // Si estamos en break, la clase no puede estar finalizada aún
        if (isset($moduloActual['tipo']) && $moduloActual['tipo'] === 'break') {
            return false;
        }

        // Si el módulo actual es mayor al último módulo de la clase, la clase ha terminado
        return $moduloActual['numero'] > $numeroUltimoModulo;
    }

    /**
     * Verificar si una clase terminó antes (profesor registró salida)
     */
    private function verificarClaseTerminoAntes($espacio, $numeroUltimoModulo, $moduloActual)
    {
        if (!$moduloActual || !$numeroUltimoModulo) {
            return false;
        }

        // Si estamos en el rango de módulos de la clase o antes del final
        if ($moduloActual['numero'] <= $numeroUltimoModulo) {
            // Verificar si hay una reserva del profesor que ya finalizó (con hora_salida)
            $reservaFinalizada = Reserva::where('id_espacio', $espacio)
                ->where('fecha_reserva', Carbon::now()->toDateString())
                ->whereNotNull('run_profesor')
                ->where('estado', 'finalizada')
                ->whereNotNull('hora_salida')
                ->orderBy('created_at', 'desc')
                ->first();

            if ($reservaFinalizada) {
                // Verificar que la reserva terminó hoy
                $horaActual = Carbon::now()->format('H:i:s');
                $horaSalida = $reservaFinalizada->hora_salida;

                // Si ya registró salida, la clase terminó antes
                return $horaSalida < $horaActual || !empty($horaSalida);
            }
        }

        return false;
    }

    /**
     * Verificar si una clase está actualmente en curso
     */
    private function verificarClaseEnCurso($numeroModuloInicio, $numeroModuloFin, $moduloActual)
    {
        if (!$moduloActual || !$numeroModuloInicio || !$numeroModuloFin) {
            return false;
        }

        // CORRECCIÓN: Durante breaks, verificar si estamos dentro del rango horario de la clase
        // Una clase que va del módulo 3 al 5 sigue ocupando la sala durante el break entre módulos
        // La clase está en curso si estamos entre el módulo de inicio y fin (inclusive)
        // Esto aplica tanto para módulos regulares como para breaks entre ellos
        return $moduloActual['numero'] >= $numeroModuloInicio && $moduloActual['numero'] <= $numeroModuloFin;
    }

    /**
     * Obtener el módulo actual basado en la hora y día actual
     */
    private function obtenerModuloActual()
    {
        $dias = ['domingo', 'lunes', 'martes', 'miercoles', 'jueves', 'viernes', 'sabado'];
        $diaActual = $dias[Carbon::now()->dayOfWeek];
        $horaActual = Carbon::now()->format('H:i:s');

        // Si es fin de semana, no hay módulos
        if ($diaActual === 'domingo') {
            return null;
        }

        $horariosDelDia = \App\Helpers\ModulosHelper::getHorariosModulos()[$diaActual] ?? null;
        if (!$horariosDelDia) {
            return null;
        }

        // Buscar en qué módulo estamos
        foreach ($horariosDelDia as $numeroModulo => $modulo) {
            if ($horaActual >= $modulo['inicio'] && $horaActual < $modulo['fin']) {
                return [
                    'numero' => $numeroModulo,
                    'inicio' => $modulo['inicio'],
                    'fin' => $modulo['fin'],
                    'tipo' => 'modulo',
                ];
            }
        }

        // Si no estamos en un módulo, buscar el próximo módulo (estamos en break)
        foreach ($horariosDelDia as $numeroModulo => $modulo) {
            if ($horaActual < $modulo['inicio']) {
                return [
                    'numero' => $numeroModulo,
                    'inicio' => $modulo['inicio'],
                    'fin' => $modulo['fin'],
                    'tipo' => 'break',
                    'mensaje' => 'Próximo Módulo',
                ];
            }
        }

        return null;
    }

    /**
     * Calcular el rango de disponibilidad de un espacio
     */
    private function calcularRangoDisponibilidad($idEspacio, $periodo, $reservasEspacio)
    {
        if (!$this->moduloActual) {
            return null;
        }

        $moduloActualNumero = $this->moduloActual['numero'];
        $diaActual = Carbon::now()->locale('es')->isoFormat('dddd');
        $dias = ['domingo', 'lunes', 'martes', 'miercoles', 'jueves', 'viernes', 'sabado'];
        $diaKey = strtolower($diaActual);

        $mapaDias = [
            'lunes' => 'lunes',
            'martes' => 'martes',
            'miércoles' => 'miercoles',
            'miercoles' => 'miercoles',
            'jueves' => 'jueves',
            'viernes' => 'viernes',
            'sábado' => 'sabado',
            'sabado' => 'sabado',
        ];

        $diaKey = $mapaDias[$diaKey] ?? $diaKey;
        $horariosDelDia = \App\Helpers\ModulosHelper::getHorariosModulos()[$diaKey] ?? null;

        if (!$horariosDelDia) {
            return null;
        }

        // Obtener todas las planificaciones futuras para este espacio hoy
        $prefijoDia = strtoupper(substr($diaKey, 0, 2));
        $planificacionesFuturas = Planificacion_Asignatura::whereHas('horario', function ($q) use ($periodo) {
            $q->where('periodo', $periodo);
        })
            ->where('id_espacio', $idEspacio)
            ->where('id_modulo', 'like', $prefijoDia . '.%')
            ->get()
            ->map(function ($plan) {
                $moduloParts = explode('.', $plan->id_modulo);
                return isset($moduloParts[1]) ? (int) $moduloParts[1] : 0;
            })
            ->filter(function ($numModulo) use ($moduloActualNumero) {
                return $numModulo > $moduloActualNumero;
            })
            ->sort()
            ->values();

        // Obtener reservas futuras
        $reservasFuturas = $reservasEspacio->filter(function ($reserva) {
            return $reserva->estado === 'pendiente' || $reserva->estado === 'activa';
        });

        // Encontrar el próximo módulo ocupado
        $proximoModuloOcupado = $planificacionesFuturas->first();

        // Si no hay clases ni reservas futuras, disponible hasta el final del día
        if ($proximoModuloOcupado === null && $reservasFuturas->isEmpty()) {
            $ultimoModulo = max(array_keys($horariosDelDia));
            return [
                'desde' => $moduloActualNumero,
                'hasta' => $ultimoModulo,
                'hora_desde' => $horariosDelDia[$moduloActualNumero]['inicio'] ?? '--:--',
                'hora_hasta' => $horariosDelDia[$ultimoModulo]['fin'] ?? '--:--',
            ];
        }

        // Si hay un próximo módulo ocupado
        if ($proximoModuloOcupado !== null) {
            return [
                'desde' => $moduloActualNumero,
                'hasta' => $proximoModuloOcupado - 1,
                'hora_desde' => $horariosDelDia[$moduloActualNumero]['inicio'] ?? '--:--',
                'hora_hasta' => $horariosDelDia[$proximoModuloOcupado - 1]['fin'] ?? '--:--',
            ];
        }

        return null;
    }

    public function actualizarDatos()
    {
        try {
            // Establecer límite de tiempo de ejecución
            set_time_limit(120);
            ini_set('max_execution_time', 120);

            $this->horaActual = Carbon::now()->format('H:i:s');
            $this->fechaActual = Carbon::now()->locale('es')->isoFormat('dddd, D [de] MMMM [de] YYYY');

            // Verificar si el periodo académico ha iniciado
            $periodoActual = SemesterHelper::getPeriodoActual();
            if ($periodoActual && $periodoActual->noHaIniciado()) {
                $this->periodoNoIniciado = true;
                $this->nombrePeriodo = $periodoActual->nombre_completo;
                // Si el periodo no ha iniciado, seguimos mostrando los espacios
                // pero NO cargaremos las planificaciones del semestre (sí las reservas espontáneas)
            } else {
                $this->periodoNoIniciado = false;
                $this->nombrePeriodo = '';
            }

            // Verificar si la fecha actual es un día feriado o sin actividades
            $feriado = DiaFeriado::obtenerFeriadoEnFecha(Carbon::now()->toDateString());
            if ($feriado) {
                $this->esFeriado = true;
                $this->nombreFeriado = $feriado->nombre;
            } else {
                $this->esFeriado = false;
                $this->nombreFeriado = '';
            }

            // Obtener el módulo actual usando la nueva lógica
            $this->moduloActual = $this->obtenerModuloActual();

            // Obtener todos los pisos con sus espacios
            $pisosModels = Piso::with(['espacios'])->get();
            $this->pisos = $pisosModels;

            // Resto del procesamiento existente...
            if ($this->moduloActual) {
                // Determinar el período actual usando el helper
                $anioActual = SemesterHelper::getCurrentAcademicYear();
                $semestre = SemesterHelper::getCurrentSemester();
                $periodo = SemesterHelper::getCurrentPeriod();

                // Buscar el módulo en la base de datos para obtener el ID
                // El id_modulo tiene formato "JU.1", "LU.10", etc. Necesitamos extraer el número
                $diaActual = Carbon::now()->locale('es')->isoFormat('dddd');

                // Normalizar el día (quitar tildes)
                $diaActual = strtolower($diaActual);
                $diaActual = str_replace(['á', 'é', 'í', 'ó', 'ú'], ['a', 'e', 'i', 'o', 'u'], $diaActual);

                $prefijoDia = '';

                // Mapear el día a su prefijo
                switch ($diaActual) {
                    case 'lunes':
                        $prefijoDia = 'LU';
                        break;
                    case 'martes':
                        $prefijoDia = 'MA';
                        break;
                    case 'miercoles':
                        $prefijoDia = 'MI';
                        break;
                    case 'jueves':
                        $prefijoDia = 'JU';
                        break;
                    case 'viernes':
                        $prefijoDia = 'VI';
                        break;
                    case 'sabado':
                        $prefijoDia = 'SA';
                        break;
                }

                $idModulo = $prefijoDia . '.' . $this->moduloActual['numero'];
                $moduloDB = Modulo::where('id_modulo', $idModulo)
                    ->where('dia', $diaActual)
                    ->first();

                Log::info('ModulosActuales - Buscando módulo:', [
                    'id_modulo' => $idModulo,
                    'dia' => $diaActual,
                    'encontrado' => $moduloDB ? 'SÍ' : 'NO'
                ]);

                // 1. Obtener planificaciones activas para el módulo actual (OPTIMIZADO)
                // Si es feriado, no cargar planificaciones regulares
                $planificacionesActivas = collect();
                if (!$this->esFeriado && $moduloDB && !$this->periodoNoIniciado) {
                    $planificacionesActivas = Planificacion_Asignatura::where('id_modulo', $idModulo)
                        ->whereHas('horario', function ($query) use ($periodo) {
                            $query->where('periodo', $periodo);
                        })
                        ->with(['asignatura.profesor', 'horario.profesor', 'asignatura.carrera'])
                        ->get()
                        ->keyBy('id_espacio');


                    // Obtener planificaciones de profesores colaboradores vigentes
                    $planificacionesColaboradores = PlanificacionProfesorColaborador::with([
                        'profesorColaborador.profesor',
                        'profesorColaborador.asignatura.carrera',
                        'espacio',
                        'modulo',
                    ])
                        ->where('id_modulo', $moduloDB->id_modulo)
                        ->whereHas('profesorColaborador', function ($q) {
                            $q->activosYVigentes(Carbon::today());
                        })
                        ->get();

                    // Pre-cargar TODAS las planificaciones del período para optimizar búsquedas
                    $todasLasPlanificaciones = Planificacion_Asignatura::with(['modulo'])
                        ->whereHas('horario', function ($q) use ($periodo) {
                            $q->where('periodo', $periodo);
                        })
                        ->get()
                        ->groupBy('id_asignatura');  // Agrupar por asignatura para búsqueda rápida
                    // Pre-cargar planificaciones del período SOLO para el día actual y espacios relevantes
                    $idsEspacios = $this->pisos->flatMap(fn($p) => $p->espacios->pluck('id_espacio'));
                    $todasLasPlanificaciones = Planificacion_Asignatura::with(['modulo'])
                        ->whereIn('id_espacio', $idsEspacios)
                        ->where('id_modulo', 'like', $prefijoDia . '.%')
                        ->whereHas('horario', function ($q) use ($periodo) {
                            $q->where('periodo', $periodo);
                        })
                        ->get()
                        ->groupBy('id_asignatura');
                } else {
                    $planificacionesActivas = collect();
                    $planificacionesColaboradores = collect();
                    $todasLasPlanificaciones = collect();
                    $reservasProfesoresPendientes = collect();
                }

                // Obtener reservas activas de solicitantes para el día actual
                // groupBy en lugar de keyBy para retener TODAS las reservas del espacio y filtrar por módulo activo
                $reservasSolicitantes = Reserva::with(['solicitante'])
                    ->where('fecha_reserva', Carbon::now()->toDateString())
                    ->whereIn('estado', ['activa', 'programada'])
                    ->whereNotNull('run_solicitante')
                    ->get()
                    ->groupBy('id_espacio');

                // Obtener reservas de profesores para el día actual
                // Solo considerar las que tienen entrada registrada (hora) y están ACTIVAS
                // groupBy en lugar de keyBy para retener TODAS las reservas del espacio y filtrar por módulo activo
                $reservasProfesores = Reserva::with(['profesor', 'asignatura', 'asignatura.carrera'])
                    ->where('fecha_reserva', Carbon::now()->toDateString())
                    ->where('estado', 'activa')  // Solo activas, no finalizadas
                    ->whereNotNull('run_profesor')
                    ->whereNotNull('hora')  // Solo las que el profesor sí entró
                    ->get()
                    ->groupBy('id_espacio');

                // [NUEVO] Obtener reservas de profesores PROGRAMADAS (pendientes de entrada) para el día actual
                $reservasProfesoresPendientes = Reserva::with(['profesor', 'asignatura', 'asignatura.carrera'])
                    ->where('fecha_reserva', Carbon::now()->toDateString())
                    ->where('estado', 'programada')
                    ->whereNotNull('run_profesor')
                    ->get()
                    ->groupBy('id_espacio');


                // Crear índice de profesores que registraron entrada (para detectar cambios de sala)
                // flatten() es necesario porque $reservasProfesores ahora es un groupBy (Collection de Collections)
                $profesoresConEntrada = $reservasProfesores->flatten()->pluck('run_profesor')->unique();

                // [OPTIMIZACIÓN] Pre-cargar TODAS las reservas del día con entrada de profesor (para lookups O(1) en el loop)
                // Esto evita el N+1 de Reserva::exists() por espacio
                $todasReservasProfesoresConEntrada = Reserva::where('fecha_reserva', Carbon::now()->toDateString())
                    ->whereNotNull('run_profesor')
                    ->whereNotNull('hora')
                    ->get()
                    ->groupBy('id_espacio');

                // [OPTIMIZACIÓN] Pre-cargar clases de recuperación pendientes para hoy
                $clasesRecuperacionHoy = ClaseNoRealizada::with(['profesor', 'asignatura.carrera'])
                    ->where('fecha_clase', Carbon::now()->toDateString())
                    ->where('estado', 'pendiente')
                    ->get()
                    ->groupBy('id_espacio');

                // Obtener TODAS las reservas del día para calcular disponibilidad
                $todasLasReservas = Reserva::where('fecha_reserva', Carbon::now()->toDateString())
                    ->get()
                    ->groupBy('id_espacio');

                // Procesar espacios por piso con optimizaciones
                $this->espacios = [];
                $dias = ['domingo', 'lunes', 'martes', 'miercoles', 'jueves', 'viernes', 'sabado'];
                $diaActual = $dias[Carbon::now()->dayOfWeek];
                $horariosDelDia = \App\Helpers\ModulosHelper::getHorariosModulos()[$diaActual] ?? [];

                foreach ($this->pisos as $piso) {
                    $espaciosPiso = [];
                    // Ordenar espacios alfabéticamente por id_espacio
                    $espaciosOrdenados = $piso->espacios->sortBy('id_espacio')->values();
                    foreach ($espaciosOrdenados as $espacio) {
                        // Buscar si el espacio tiene una planificación activa (búsqueda O(1))
                        $planificacionActiva = $planificacionesActivas->get($espacio->id_espacio);

                        // Buscar si el espacio tiene una reserva de solicitante en el módulo actual
                        // Se filtra por rango de módulos (o por hora como fallback) para evitar que
                        // una reserva de otro bloque horario aparezca en el módulo que se está mostrando
                        $reservaSolicitante = $reservasSolicitantes
                            ->get($espacio->id_espacio, collect())
                            ->first(function ($reserva) {
                                $moduloActual = $this->moduloActual['numero'] ?? null;
                                $ini = $reserva->modulo_inicio;
                                $fin = $reserva->modulo_fin;
                                if ($moduloActual !== null && $ini !== null && $fin !== null) {
                                    return $moduloActual >= $ini && $moduloActual <= $fin;
                                }
                                // Fallback por hora si no tiene módulos definidos
                                $horaActual = now()->format('H:i:s');
                                $horaIni = $reserva->hora;
                                $horaSal = $reserva->hora_salida;
                                if ($horaIni && $horaSal) {
                                    return $horaActual >= $horaIni && $horaActual <= $horaSal;
                                }
                                return true; // sin información de horario → incluir
                            });

                        // Buscar si el espacio tiene una reserva activa de profesor en el módulo actual
                        $reservaProfesor = $reservasProfesores
                            ->get($espacio->id_espacio, collect())
                            ->first(function ($reserva) {
                                $moduloActual = $this->moduloActual['numero'] ?? null;
                                $ini = $reserva->modulo_inicio;
                                $fin = $reserva->modulo_fin;
                                if ($moduloActual !== null && $ini !== null && $fin !== null) {
                                    return $moduloActual >= $ini && $moduloActual <= $fin;
                                }
                                // Fallback por hora si no tiene módulos definidos
                                $horaActual = now()->format('H:i:s');
                                $horaIni = $reserva->hora;
                                $horaSal = $reserva->hora_salida;
                                if ($horaIni && $horaSal) {
                                    return $horaActual >= $horaIni && $horaActual <= $horaSal;
                                }
                                return true; // sin información de horario → incluir
                            });

                        // Buscar si el espacio tiene una reserva de profesor PENDIENTE en el módulo actual
                        $reservaProfesorPendiente = $reservasProfesoresPendientes
                            ->get($espacio->id_espacio, collect())
                            ->first(function ($reserva) {
                                $moduloActual = $this->moduloActual['numero'] ?? null;
                                $ini = $reserva->modulo_inicio;
                                $fin = $reserva->modulo_fin;
                                if ($moduloActual !== null && $ini !== null && $fin !== null) {
                                    return $moduloActual >= $ini && $moduloActual <= $fin;
                                }
                                return true; // sin módulos definidos → incluir (comportamiento anterior)
                            });


                        $tieneClase = false;
                        $tieneReservaSolicitante = false;
                        $tieneReservaProfesor = false;
                        $tieneReservaPendiente = false;  // Nueva bandera para reservas sin entrada
                        $datosClase = null;
                        $datosSolicitante = null;
                        $datosProfesor = null;
                        $claseMovidaAOtraSala = false;
                        $rangoDisponibilidad = null;
                        $esRecuperacion = false;

                        // [OPTIMIZACIÓN] Usar datos pre-cargados en lugar de hacer query por espacio
                        $claseRecuperacionPendiente = null;
                        $clasesEspacio = $clasesRecuperacionHoy->get($espacio->id_espacio, collect());
                        if ($clasesEspacio->isNotEmpty()) {
                            $claseRecuperacionPendiente = $clasesEspacio->first(function ($clase) use ($idModulo) {
                                return $clase->id_modulo === $idModulo
                                    || str_starts_with($clase->id_modulo, $idModulo . ',')
                                    || str_ends_with($clase->id_modulo, ',' . $idModulo)
                                    || str_contains($clase->id_modulo, ',' . $idModulo . ',');
                            });
                        }

                        // Verificar si hay una clase programada aquí pero el profesor la hizo en otro espacio
                        if ($planificacionActiva && !$reservaProfesor) {
                            $runProfesor = $planificacionActiva->asignatura->run_profesor ?? null;
                            if ($runProfesor && $profesoresConEntrada->contains($runProfesor)) {
                                // El profesor SÍ entró hoy, pero en otro espacio
                                $claseMovidaAOtraSala = true;
                            }
                        }

                        // Si hay recuperación programada, mostrarla
                        if ($claseRecuperacionPendiente) {
                            // Verificar regla de 20 minutos para recuperación
                            $runProfesor = $claseRecuperacionPendiente->run_profesor;
                            $horaActual = Carbon::now()->format('H:i:s');
                            $horaInicioModulo = $this->moduloActual['inicio'] ?? null;

                            $debeMarcarNoRealizada = false;
                            if ($horaInicioModulo && $runProfesor) {
                                $inicioModulo = Carbon::createFromTimeString($horaInicioModulo);
                                $ahora = Carbon::createFromTimeString($horaActual);
                                $hasPasado20Minutos = $ahora->gt($inicioModulo) && $ahora->diffInMinutes($inicioModulo) >= 20;

                                // [OPTIMIZACIÓN] Verificar usando datos pre-cargados en lugar de queries individuales
                                $reservasEspacio = $todasReservasProfesoresConEntrada->get($espacio->id_espacio, collect());
                                $tuvoEntradaHoy = $reservasEspacio->where('run_profesor', $runProfesor)->isNotEmpty();

                                $tuvoEntradaEnOtroEspacio = false;
                                if (!$tuvoEntradaHoy) {
                                    // Buscar en otros espacios usando la colección pre-cargada
                                    $tuvoEntradaEnOtroEspacio = $todasReservasProfesoresConEntrada
                                        ->except([$espacio->id_espacio])
                                        ->flatten()
                                        ->where('run_profesor', $runProfesor)
                                        ->isNotEmpty();
                                }

                                if ($hasPasado20Minutos && !$tuvoEntradaHoy && !$tuvoEntradaEnOtroEspacio) {
                                    $debeMarcarNoRealizada = true;
                                }
                            }

                            // Si no debe marcar como no realizada, mostrar la recuperación
                            if (!$debeMarcarNoRealizada) {
                                $esRecuperacion = true;
                                $tieneClase = true;

                                // Parsear id_modulo para obtener el rango de módulos
                                $idModuloOriginal = $claseRecuperacionPendiente->id_modulo;
                                $moduloInicio = $this->moduloActual['numero'];
                                $moduloFin = $moduloInicio;

                                // El id_modulo puede ser:
                                // - Un solo módulo: "LU.1"
                                // - Múltiples módulos separados por comas: "LU.1,LU.2,LU.3"
                                if (str_contains($idModuloOriginal, ',')) {
                                    // Múltiples módulos separados por comas
                                    $modulos = explode(',', $idModuloOriginal);
                                    $numeros = [];
                                    foreach ($modulos as $modulo) {
                                        if (preg_match('/\.(\d+)/', $modulo, $match)) {
                                            $numeros[] = (int) $match[1];
                                        }
                                    }
                                    if (count($numeros) > 0) {
                                        $moduloInicio = min($numeros);
                                        $moduloFin = max($numeros);
                                    }
                                } elseif (preg_match('/\.(\d+)/', $idModuloOriginal, $match)) {
                                    // Un solo módulo
                                    $moduloInicio = $moduloFin = (int) $match[1];
                                }

                                // Calcular hora de fin correcta basada en el módulo final
                                $horaInicio = $this->moduloActual['inicio'] ?? '--:--';
                                $horaFin = $this->moduloActual['fin'] ?? '--:--';

                                if ($moduloFin > $moduloInicio) {
                                    // Construir id_modulo del último módulo (ej: "MI.3" si estamos en miércoles)
                                    $dias = ['DO', 'LU', 'MA', 'MI', 'JU', 'VI', 'SA'];
                                    $prefijoDia = $dias[Carbon::now()->dayOfWeek];
                                    $idModuloFinal = $prefijoDia . '.' . $moduloFin;

                                    $moduloFinal = Modulo::where('id_modulo', $idModuloFinal)->first();
                                    if ($moduloFinal) {
                                        $horaFin = $moduloFinal->hora_termino;
                                    }
                                }

                                // Eliminar segundos de las horas
                                if (strlen($horaInicio) > 5) {
                                    $horaInicio = substr($horaInicio, 0, 5);
                                }
                                if (strlen($horaFin) > 5) {
                                    $horaFin = substr($horaFin, 0, 5);
                                }

                                // Obtener inscritos desde la planificación original
                                // Para recuperaciones, buscar SOLO por id_asignatura (sin filtrar por id_modulo)
                                // porque el id_modulo de la recuperación es diferente al original
                                $inscritos = null;

                                $planificacionOriginal = Planificacion_Asignatura::where('id_asignatura', $claseRecuperacionPendiente->id_asignatura)
                                    ->first();  // Tomar cualquier planificación de esta asignatura

                                if ($planificacionOriginal) {
                                    $inscritos = $planificacionOriginal->inscritos;
                                }

                                // Obtener información de la recuperación
                                $datosClase = [
                                    'codigo_asignatura' => $claseRecuperacionPendiente->asignatura->codigo_asignatura ?? '-',
                                    'nombre_asignatura' => $claseRecuperacionPendiente->asignatura->nombre_asignatura ?? '-',
                                    'seccion' => $claseRecuperacionPendiente->asignatura->seccion ?? '-',
                                    'profesor' => [
                                        'name' => $claseRecuperacionPendiente->profesor->name ?? '-',
                                    ],
                                    'carrera' => $claseRecuperacionPendiente->asignatura->carrera->nombre ?? '-',
                                    'modulo_inicio' => $moduloInicio,
                                    'modulo_fin' => $moduloFin,
                                    'hora_inicio' => $horaInicio,
                                    'hora_fin' => $horaFin,
                                    'inscritos' => $inscritos,
                                    'es_recuperacion' => true,
                                ];
                            } else {
                                // Marcar recuperación como no realizada nuevamente
                                $claseRecuperacionPendiente->update([
                                    'motivo' => 'Recuperación no realizada: No se registró ingreso después de 20 minutos',
                                    'observaciones' => ($claseRecuperacionPendiente->observaciones ?? '') . ' | Intento de recuperación el ' . Carbon::now()->format('d/m/Y') . ' sin éxito',
                                ]);
                            }
                        } elseif ($planificacionColaborador = $planificacionesColaboradores->firstWhere('id_espacio', $espacio->id_espacio)) {
                            // Clase de profesor colaborador vigente
                            $tieneClase = true;

                            $colaborador = $planificacionColaborador->profesorColaborador;

                            if ($colaborador) {
                                $horaInicio = $this->moduloActual['inicio'] ?? '--:--';
                                $horaFin = $this->moduloActual['fin'] ?? '--:--';

                                // Eliminar segundos de las horas (formato HH:mm:ss a HH:mm)
                                if (strlen($horaInicio) > 5) {
                                    $horaInicio = substr($horaInicio, 0, 5);
                                }
                                if (strlen($horaFin) > 5) {
                                    $horaFin = substr($horaFin, 0, 5);
                                }

                                $datosClase = [
                                    'codigo_asignatura' => 'TEMP',
                                    'nombre_asignatura' => $colaborador->nombre_asignatura ?? $colaborador->nombre_asignatura_temporal ?? '-',
                                    'seccion' => '-',
                                    'profesor' => [
                                        'name' => $colaborador->profesor ? $colaborador->profesor->name : '-',
                                    ],
                                    'carrera' => ($colaborador->asignatura && $colaborador->asignatura->carrera) ? $colaborador->asignatura->carrera->nombre : 'Asignatura Temporal',
                                    'modulo_inicio' => $this->moduloActual['numero'] ?? '--',
                                    'modulo_fin' => $this->moduloActual['numero'] ?? '--',
                                    'hora_inicio' => $horaInicio,
                                    'hora_fin' => $horaFin,
                                    'es_colaborador' => true,
                                    'tipo_clase' => $colaborador->tipo_clase ?? 'temporal',
                                ];
                            }
                        } elseif ($planificacionActiva) {
                            // Validar que la planificación tenga asignatura
                            if (!$planificacionActiva->asignatura) {
                                Log::warning('Planificación sin asignatura', [
                                    'id_planificacion' => $planificacionActiva->id,
                                    'id_espacio' => $espacio->id_espacio,
                                    'id_modulo' => $planificacionActiva->id_modulo
                                ]);
                                // Saltar esta planificación
                                $planificacionActiva = null;
                            }
                        }

                        // PRIORIDAD 2: Solo procesar planificación del espacio si NO hay reserva de profesor
                        // Esto evita confusión cuando el profesor está dando otra clase
                        if ($planificacionActiva && $planificacionActiva->asignatura && !$reservaProfesor) {
                            $tieneClase = true;

                            // Obtener todas las planificaciones de esta asignatura usando datos pre-cargados
                            $planificacionesAsignatura = $todasLasPlanificaciones
                                ->get($planificacionActiva->id_asignatura, collect())
                                ->sortBy(function ($planificacion) {
                                    // Extraer el número del módulo para ordenar
                                    $moduloParts = explode('.', $planificacion->id_modulo);

                                    return isset($moduloParts[1]) ? (int) $moduloParts[1] : 0;
                                });

                            $moduloInicio = $planificacionesAsignatura->first();
                            $moduloFin = $planificacionesAsignatura->last();

                            $numeroModuloInicio = $moduloInicio ? explode('.', $moduloInicio->id_modulo)[1] ?? '' : '';
                            $numeroModuloFin = $moduloFin ? explode('.', $moduloFin->id_modulo)[1] ?? '' : '';

                            // Si no se encontraron módulos específicos, usar el módulo actual como referencia
                            if (empty($numeroModuloInicio) || empty($numeroModuloFin)) {
                                $moduloActualParts = explode('.', $planificacionActiva->id_modulo);
                                $numeroModuloInicio = $numeroModuloInicio ?: ($moduloActualParts[1] ?? '');
                                $numeroModuloFin = $numeroModuloFin ?: ($moduloActualParts[1] ?? '');
                            }

                            $horaInicio = '';
                            $horaFin = '';

                            if ($numeroModuloInicio && isset($horariosDelDia[$numeroModuloInicio])) {
                                $horaInicio = substr($horariosDelDia[$numeroModuloInicio]['inicio'], 0, 5);
                            }

                            if ($numeroModuloFin && isset($horariosDelDia[$numeroModuloFin])) {
                                $horaFin = substr($horariosDelDia[$numeroModuloFin]['fin'], 0, 5);
                            }

                            $datosClase = [
                                'codigo_asignatura' => $planificacionActiva->asignatura->codigo_asignatura ?? '-',
                                'nombre_asignatura' => $planificacionActiva->asignatura->nombre_asignatura ?? '-',
                                'seccion' => $planificacionActiva->asignatura->seccion ?? '-',
                                'profesor' => [
                                    'name' => ($planificacionActiva->horario && $planificacionActiva->horario->profesor)
                                        ? $planificacionActiva->horario->profesor->name
                                        : ($planificacionActiva->asignatura->profesor->name ?? '-'),
                                ],
                                'carrera' => $planificacionActiva->asignatura->carrera->nombre ?? '-',
                                'modulo_inicio' => $numeroModuloInicio,
                                'modulo_fin' => $numeroModuloFin,
                                'hora_inicio' => $horaInicio,
                                'hora_fin' => $horaFin,
                                'inscritos' => $planificacionActiva->inscritos ?? null,
                            ];
                        }

                        if ($reservaSolicitante) {
                            // Determinar si la reserva de solicitante está en la franja actual
                            $reservaSolicitanteEnFranja = false;
                            $moduloInicioSol = $reservaSolicitante->modulo_inicio;
                            $moduloFinSol = $reservaSolicitante->modulo_fin;
                            $moduloActualNum = $this->moduloActual['numero'] ?? null;

                            if ($moduloActualNum && $moduloInicioSol && $moduloFinSol) {
                                $reservaSolicitanteEnFranja = ($moduloActualNum >= $moduloInicioSol && $moduloActualNum <= $moduloFinSol);
                            } elseif ($reservaSolicitante->estado === 'activa') {
                                // Si no tiene módulos definidos pero está activa, considerarla en franja
                                $reservaSolicitanteEnFranja = true;
                            }

                            // Solo marcar como "tiene reserva solicitante" (ocupado) si está ACTIVA
                            // Las reservas programadas NO se auto-activan aquí;
                            // el solicitante debe llegar y escanear su carnet/QR para activarla
                            if ($reservaSolicitante->estado === 'activa' && $reservaSolicitanteEnFranja) {
                                $tieneReservaSolicitante = true;
                            }

                            // Obtener horas de inicio/fin desde los módulos si están disponibles
                            $horaSolInicio = $reservaSolicitante->hora ?? '-';
                            $horaSolFin = $reservaSolicitante->hora_salida ?? '-';

                            if ($moduloInicioSol && isset($horariosDelDia[$moduloInicioSol])) {
                                $horaSolInicio = substr($horariosDelDia[$moduloInicioSol]['inicio'], 0, 5);
                            }
                            if ($moduloFinSol && isset($horariosDelDia[$moduloFinSol])) {
                                $horaSolFin = substr($horariosDelDia[$moduloFinSol]['fin'], 0, 5);
                            }

                            $datosSolicitante = [
                                'nombre' => $reservaSolicitante->solicitante->nombre ?? '-',
                                'run' => $reservaSolicitante->run_solicitante ?? '-',
                                'tipo_solicitante' => $reservaSolicitante->solicitante->tipo_solicitante ?? '-',
                                'hora_inicio' => $horaSolInicio,
                                'hora_salida' => $horaSolFin,
                                'nombre_actividad' => $reservaSolicitante->nombre_actividad ?? null,
                                'descripcion_actividad' => $reservaSolicitante->descripcion_actividad ?? null,
                                'modulo_inicio' => $moduloInicioSol,
                                'modulo_fin' => $moduloFinSol,
                                'es_programada' => $reservaSolicitante->estado === 'programada',
                            ];
                        }

                        // PRIORIDAD 1: Procesar reserva de profesor PRIMERO (es la clase que realmente está dando)
                        if ($reservaProfesor) {
                            $tieneReservaProfesor = true;

                            if ($reservaProfesor->asignatura && $reservaProfesor->tipo_reserva !== 'espontanea') {
                                // Buscar las planificaciones de ESTA asignatura en ESTE espacio
                                $planificacionesReserva = Planificacion_Asignatura::where('id_asignatura', $reservaProfesor->asignatura->id_asignatura)
                                    ->where('id_espacio', $reservaProfesor->id_espacio)
                                    ->whereHas('horario', function ($q) use ($periodo) {
                                        $q->where('periodo', $periodo);
                                    })
                                    ->with('modulo')
                                    ->get()
                                    ->sortBy(function ($planificacion) {
                                        $moduloParts = explode('.', $planificacion->id_modulo);
                                        return isset($moduloParts[1]) ? (int) $moduloParts[1] : 0;
                                    });

                                $inscritos = $planificacionesReserva->first()->inscritos ?? null;

                                // Obtener el rango de módulos de la clase que está dando
                                $moduloInicio = $planificacionesReserva->first();
                                $moduloFin = $planificacionesReserva->last();

                                $numeroModuloInicio = $moduloInicio ? explode('.', $moduloInicio->id_modulo)[1] ?? '' : '';
                                $numeroModuloFin = $moduloFin ? explode('.', $moduloFin->id_modulo)[1] ?? '' : '';

                                // FALLBACK: Si no hay planificaciones, intentar obtener módulos de las observaciones de la reserva
                                if (empty($numeroModuloInicio) || empty($numeroModuloFin)) {
                                    if ($reservaProfesor->observaciones && preg_match('/Módulos: (\d+)-(\d+)/', $reservaProfesor->observaciones, $matches)) {
                                        $numeroModuloInicio = $matches[1];
                                        $numeroModuloFin = $matches[2];
                                    } elseif ($reservaProfesor->modulos) {
                                        // Si modulos contiene la duración, calcular desde la hora
                                        $horaReserva = $reservaProfesor->hora;
                                        foreach ($horariosDelDia as $numMod => $horarioMod) {
                                            if ($horaReserva >= $horarioMod['inicio'] && $horaReserva <= $horarioMod['fin']) {
                                                $numeroModuloInicio = (string) $numMod;
                                                $numeroModuloFin = (string) ($numMod + (int) $reservaProfesor->modulos - 1);
                                                break;
                                            }
                                        }
                                    }
                                }

                                $horaInicio = '';
                                $horaFin = '';

                                if ($numeroModuloInicio && isset($horariosDelDia[$numeroModuloInicio])) {
                                    $horaInicio = substr($horariosDelDia[$numeroModuloInicio]['inicio'], 0, 5);
                                }

                                if ($numeroModuloFin && isset($horariosDelDia[$numeroModuloFin])) {
                                    $horaFin = substr($horariosDelDia[$numeroModuloFin]['fin'], 0, 5);
                                }

                                $datosProfesor = [
                                    'nombre' => $reservaProfesor->profesor->name ?? '-',
                                    'run' => $reservaProfesor->run_profesor ?? '-',
                                    'hora_inicio' => $horaInicio ?: ($reservaProfesor->hora ?? '-'),
                                    'hora_salida' => $horaFin ?: ($reservaProfesor->hora_salida ?? '-'),
                                    'nombre_asignatura' => $reservaProfesor->asignatura->nombre_asignatura ?? 'Sin asignatura',
                                    'codigo_asignatura' => $reservaProfesor->asignatura->codigo_asignatura ?? '-',
                                    'carrera' => $reservaProfesor->asignatura->carrera->nombre ?? '-',
                                    'inscritos' => $inscritos,
                                    'modulo_inicio' => $numeroModuloInicio,
                                    'modulo_fin' => $numeroModuloFin,
                                    'nombre_actividad' => $reservaProfesor->nombre_actividad ?? null,
                                    'descripcion_actividad' => $reservaProfesor->descripcion_actividad ?? null,
                                    'tipo_reserva' => $reservaProfesor->tipo_reserva ?? 'clase',
                                ];
                            } else {
                                // Reserva sin asignatura (uso libre o espontánea)
                                // Intentar obtener módulos de las observaciones de la reserva
                                $moduloInicioReserva = '';
                                $moduloFinReserva = '';

                                if ($reservaProfesor->observaciones && preg_match('/Módulos: (\d+)-(\d+)/', $reservaProfesor->observaciones, $matches)) {
                                    $moduloInicioReserva = $matches[1];
                                    $moduloFinReserva = $matches[2];
                                } elseif ($reservaProfesor->modulos) {
                                    // Si modulos contiene la duración, calcular desde la hora
                                    $horaReserva = $reservaProfesor->hora;
                                    foreach ($horariosDelDia as $numMod => $horarioMod) {
                                        if ($horaReserva >= $horarioMod['inicio'] && $horaReserva <= $horarioMod['fin']) {
                                            $moduloInicioReserva = (string) $numMod;
                                            $moduloFinReserva = (string) ($numMod + (int) $reservaProfesor->modulos - 1);
                                            break;
                                        }
                                    }
                                }

                                $horaInicio = $reservaProfesor->hora ?? '-';
                                $horaFin = $reservaProfesor->hora_salida ?? '-';

                                // Si tenemos módulos, obtener las horas correspondientes
                                if ($moduloInicioReserva && isset($horariosDelDia[$moduloInicioReserva])) {
                                    $horaInicio = substr($horariosDelDia[$moduloInicioReserva]['inicio'], 0, 5);
                                }
                                if ($moduloFinReserva && isset($horariosDelDia[$moduloFinReserva])) {
                                    $horaFin = substr($horariosDelDia[$moduloFinReserva]['fin'], 0, 5);
                                }

                                $datosProfesor = [
                                    'nombre' => $reservaProfesor->profesor->name ?? '-',
                                    'run' => $reservaProfesor->run_profesor ?? '-',
                                    'hora_inicio' => $horaInicio,
                                    'hora_salida' => $horaFin,
                                    'nombre_asignatura' => 'Reserva espontánea',
                                    'codigo_asignatura' => '-',
                                    'carrera' => '-',
                                    'inscritos' => null,
                                    'modulo_inicio' => $moduloInicioReserva,
                                    'modulo_fin' => $moduloFinReserva,
                                    'nombre_actividad' => $reservaProfesor->nombre_actividad ?? null,
                                    'descripcion_actividad' => $reservaProfesor->descripcion_actividad ?? null,
                                    'tipo_reserva' => $reservaProfesor->tipo_reserva ?? 'espontanea',
                                ];

                                Log::info('ModulosActuales - Procesando reserva espontánea:', [
                                    'espacio' => $espacio->id_espacio,
                                    'profesor' => $reservaProfesor->profesor->name ?? 'N/A',
                                    'run' => $reservaProfesor->run_profesor,
                                    'modulos' => $moduloInicioReserva . '-' . $moduloFinReserva,
                                ]);
                            }
                        }

                        // Procesar reserva pendiente de profesor (sin entrada aún pero reservado)
                        // Solo si NO hay ya una reserva activa con entrada
                        if (!$tieneReservaProfesor && $reservaProfesorPendiente) {
                            // Determinar si la reserva está programada (futura)
                            // NO auto-activar: el profesor debe llegar y escanear su carnet/QR
                            $esProgramadaProfesor = ($reservaProfesorPendiente->estado === 'programada');
                            $moduloInicioPend = $reservaProfesorPendiente->modulo_inicio;
                            $moduloFinPend = $reservaProfesorPendiente->modulo_fin;
                            $moduloActualNum = $this->moduloActual['numero'] ?? null;

                            // Solo mostrar como pendiente/programado si el módulo actual
                            // está dentro del rango de la reserva (o si no tiene módulos definidos)
                            $reservaPendEnFranja = true;  // Por defecto mostrar si no tiene módulos
                            if ($moduloActualNum && $moduloInicioPend && $moduloFinPend) {
                                $reservaPendEnFranja = ($moduloActualNum >= $moduloInicioPend && $moduloActualNum <= $moduloFinPend);
                            }

                            $tieneReservaPendiente = $reservaPendEnFranja;

                            // Obtener horas desde los módulos almacenados
                            $horaInicioPend = '-';
                            $horaFinPend = '-';
                            if ($moduloInicioPend && isset($horariosDelDia[$moduloInicioPend])) {
                                $horaInicioPend = substr($horariosDelDia[$moduloInicioPend]['inicio'], 0, 5);
                            }
                            if ($moduloFinPend && isset($horariosDelDia[$moduloFinPend])) {
                                $horaFinPend = substr($horariosDelDia[$moduloFinPend]['fin'], 0, 5);
                            }

                            // Obtener datos básicos de la reserva pendiente
                            $datosProfesor = [
                                'nombre' => $reservaProfesorPendiente->profesor->name ?? '-',
                                'run' => $reservaProfesorPendiente->run_profesor ?? '-',
                                'hora_inicio' => $horaInicioPend,
                                'hora_salida' => $horaFinPend,
                                'nombre_asignatura' => $reservaProfesorPendiente->id_asignatura
                                    ? ($reservaProfesorPendiente->asignatura->nombre_asignatura ?? 'Reservado')
                                    : 'Reserva Espontánea',
                                'codigo_asignatura' => $reservaProfesorPendiente->asignatura->codigo_asignatura ?? '-',
                                'carrera' => $reservaProfesorPendiente->asignatura->carrera->nombre ?? '-',
                                'inscritos' => null,
                                'modulo_inicio' => $moduloInicioPend ?? '',
                                'modulo_fin' => $moduloFinPend ?? '',
                                'es_pendiente' => true,
                                'es_programada' => $esProgramadaProfesor,
                                'tipo_reserva' => $reservaProfesorPendiente->tipo_reserva ?? 'espontanea',
                                'nombre_actividad' => $reservaProfesorPendiente->nombre_actividad ?? null,
                                'descripcion_actividad' => $reservaProfesorPendiente->descripcion_actividad ?? null,
                            ];

                            Log::info('ModulosActuales - Reserva pendiente encontrada:', [
                                'espacio' => $espacio->id_espacio,
                                'profesor' => $reservaProfesorPendiente->profesor->name ?? 'N/A',
                                'es_programada' => $esProgramadaProfesor,
                            ]);
                        }

                        // Buscar la próxima clase para este espacio si no tiene clase actual
                        // TEMPORALMENTE DESACTIVADO para evitar timeout
                        $proximaClase = null;
                        // if (!$tieneClase) {
                        //     $proximaClase = $this->obtenerProximaClase($espacio->id_espacio, $periodo);
                        // }

                        // Verificar si la clase debe marcarse como no realizada
                        $claseNoRealizada = false;
                        $claseFinalizada = false;
                        $claseTerminoAntes = false;

                        // Inicializar variables de módulos (necesarias para todas las rutas de lógica)
                        $numeroModuloInicio = 0;
                        $numeroModuloFin = 0;

                        // Si hay reserva de profesor, usar los módulos de la asignatura de la reserva
                        if ($tieneReservaProfesor && !empty($datosProfesor['modulo_inicio']) && !empty($datosProfesor['modulo_fin'])) {
                            $numeroModuloInicio = (int) $datosProfesor['modulo_inicio'];
                            $numeroModuloFin = (int) $datosProfesor['modulo_fin'];

                            // Verificar si la clase terminó antes (profesor registró salida)
                            $claseTerminoAntes = $this->verificarClaseTerminoAntes($espacio->id_espacio, $numeroModuloFin, $this->moduloActual);

                            // Verificar si la clase ha finalizado por horario
                            $claseFinalizada = $this->verificarClaseFinalizada($numeroModuloFin, $this->moduloActual);
                        } elseif ($tieneClase && $planificacionActiva && $planificacionActiva->asignatura) {
                            // Obtener los números de módulos para verificar estado
                            $planificacionesAsignatura = $todasLasPlanificaciones
                                ->get($planificacionActiva->id_asignatura, collect())
                                ->sortBy(function ($planificacion) {
                                    $moduloParts = explode('.', $planificacion->id_modulo);

                                    return isset($moduloParts[1]) ? (int) $moduloParts[1] : 0;
                                });

                            if ($planificacionesAsignatura->isNotEmpty()) {
                                $moduloInicio = $planificacionesAsignatura->first();
                                $moduloFin = $planificacionesAsignatura->last();

                                $numeroModuloInicio = $moduloInicio ? explode('.', $moduloInicio->id_modulo)[1] ?? 0 : 0;
                                $numeroModuloFin = $moduloFin ? explode('.', $moduloFin->id_modulo)[1] ?? 0 : 0;

                                // Verificar si la clase terminó antes (profesor registró salida)
                                $claseTerminoAntes = $this->verificarClaseTerminoAntes($espacio->id_espacio, (int) $numeroModuloFin, $this->moduloActual);

                                // Verificar si la clase ha finalizado por horario
                                $claseFinalizada = $this->verificarClaseFinalizada((int) $numeroModuloFin, $this->moduloActual);

                                // Solo verificar clase no realizada si no ha finalizado ni terminó antes
                                if (!$claseFinalizada && !$claseTerminoAntes && !$tieneReservaProfesor) {
                                    $claseNoRealizada = $this->verificarClaseNoRealizada($planificacionActiva, $tieneReservaProfesor, $periodo, $this->moduloActual);
                                }
                            }
                        }

                        // Determinar el estado dinámicamente
                        if ($claseMovidaAOtraSala) {
                            // Si la clase se movió a otro espacio, marcar como disponible
                            $estado = 'Disponible';
                            $tieneClase = false;
                            $datosClase = null;
                        } elseif ($tieneReservaSolicitante && !($datosSolicitante['es_programada'] ?? false)) {
                            // Reserva de solicitante ACTIVA (en franja actual) → Reserva Espontánea
                            $estado = 'Reserva Espontánea';
                        } elseif ($tieneReservaProfesor) {
                            if (($datosProfesor['tipo_reserva'] ?? '') === 'espontanea') {
                                $estado = 'Reserva Espontánea';
                                $tieneClase = false; // Ignorar clase programada
                            } else {
                                if ($claseFinalizada || $claseTerminoAntes) {
                                    $estado = 'Disponible';
                                    $tieneClase = false;
                                    $datosClase = null;
                                    $tieneReservaProfesor = false;
                                    $datosProfesor = null;
                                } else {
                                    $claseEnCurso = $this->verificarClaseEnCurso((int) $numeroModuloInicio, (int) $numeroModuloFin, $this->moduloActual);
                                    if ($claseEnCurso) {
                                        $estado = 'Clase registrada';
                                    } else {
                                        $estado = 'Disponible';
                                        $tieneClase = false;
                                        $datosClase = null;
                                        $tieneReservaProfesor = false;
                                        $datosProfesor = null;
                                    }
                                }
                            }
                        } elseif ($tieneClase && ($claseFinalizada || $claseTerminoAntes)) {
                            $estado = 'Disponible';
                            $tieneClase = false;
                            $datosClase = null;
                        } elseif ($tieneClase && !$tieneReservaProfesor && $claseNoRealizada) {
                            // Si hay clase programada pero se detectó que no fue realizada
                            $estado = 'Clase no registrada';
                        } elseif ($tieneClase && !$tieneReservaProfesor) {
                            // Si hay clase programada pero el profesor no ha registrado su ingreso
                            // Verificar si la clase ya debería haber empezado (no estamos en break antes de la clase)
                            $claseYaDebioEmpezar = false;
                            if ($this->moduloActual && isset($this->moduloActual['tipo'])) {
                                if ($this->moduloActual['tipo'] === 'break') {
                                    // Si estamos en break, verificar si el siguiente módulo es el inicio de la clase
                                    // Y si faltan 10 minutos o menos para que empiece
                                    $minutosParaInicio = 999;
                                    if ($this->moduloActual['numero'] == $numeroModuloInicio) {
                                        $inicioM = Carbon::createFromTimeString($this->moduloActual['inicio']);
                                        $minutosParaInicio = Carbon::now()->diffInMinutes($inicioM, false);
                                    }

                                    $estaCerca = ($this->moduloActual['numero'] == $numeroModuloInicio && $minutosParaInicio <= 15 && $minutosParaInicio >= 0);

                                    // La clase ya debió empezar si el módulo actual es mayor al de inicio,
                                    // o si estamos en el break previo (mismo número) y faltan 15 min o menos.
                                    $claseYaDebioEmpezar = ($this->moduloActual['numero'] > $numeroModuloInicio) || $estaCerca;
                                } else {
                                    // En módulo: verificar si estamos en o después del módulo de inicio
                                    $claseYaDebioEmpezar = $this->moduloActual['numero'] >= $numeroModuloInicio;
                                }
                            }

                            // Si la clase ya debió empezar pero el profesor no ha llegado, marcarla como "Clase Programada"
                            // Si la clase aún no debía empezar, el espacio está "Disponible" y NO mostrar información de clase futura
                            if ($claseYaDebioEmpezar) {
                                $estado = 'Clase Programada';
                            } else {
                                // La clase es más tarde, mostrar como disponible sin información de clase
                                $estado = 'Disponible';
                                $tieneClase = false;
                                $datosClase = null;
                            }
                        } elseif ($proximaClase) {
                            $estado = 'Clase Programada';
                        } elseif ($tieneReservaPendiente) {
                            // Si hay una reserva pendiente (profesor reservó pero no ha marcado entrada)
                            if (!empty($datosProfesor['es_programada'])) {
                                $estado = 'Programado';
                            } else {
                                $estado = 'Reservado';
                            }
                            $tieneReservaProfesor = true;  // Marcar para mostrar info del profesor
                        } elseif ($reservaSolicitante && ($datosSolicitante['es_programada'] ?? false)) {
                            // Reserva de solicitante PROGRAMADA → solo mostrar si estamos en su franja de módulos
                            $modActNum = $this->moduloActual['numero'] ?? null;
                            $modIniSol = $datosSolicitante['modulo_inicio'] ?? null;
                            $modFinSol = $datosSolicitante['modulo_fin'] ?? null;
                            $solEnFranja = true;  // Por defecto si no tiene módulos
                            if ($modActNum && $modIniSol && $modFinSol) {
                                $solEnFranja = ($modActNum >= $modIniSol && $modActNum <= $modFinSol);
                            }
                            if (!$solEnFranja) {
                                // Fuera de franja → Disponible, no mostrar como programado
                                $estado = 'Disponible';
                                $tieneReservaSolicitante = false;
                                $datosSolicitante = null;
                            } else {
                                $estado = 'Programado';
                                $tieneReservaSolicitante = true;
                            }
                        } else {
                            // Si no hay clase, ni reserva, ni nada, el espacio está disponible
                            // NO confiar en el estado de la BD que puede estar desactualizado
                            $estado = 'Disponible';
                            $tieneClase = false;
                            $datosClase = null;
                        }

                        // Calcular rango de disponibilidad SOLO si el estado final es "Disponible"
                        if ($estado === 'Disponible') {
                            $rangoDisponibilidad = $this->calcularRangoDisponibilidad($espacio->id_espacio, $periodo, $todasLasReservas->get($espacio->id_espacio, collect()));
                            // CRÍTICO: Limpiar TODA la información cuando está disponible para evitar datos fantasma
                            $tieneClase = false;
                            $datosClase = null;
                            $tieneReservaProfesor = false;
                            $datosProfesor = null;
                            $tieneReservaSolicitante = false;
                            $datosSolicitante = null;
                            $tieneReservaPendiente = false;
                        }

                        // Obtener conteo de asistencia actual para este espacio
                        $asistenciaActual = Asistencia::where('id_espacio', $espacio->id_espacio)
                            ->where('estado', Asistencia::ESTADO_PRESENTE)
                            ->whereDate('created_at', Carbon::today())
                            ->count();

                        $espaciosPiso[] = [
                            'id_espacio' => $espacio->id_espacio ?? 'N/A',
                            'nombre_espacio' => $espacio->nombre_espacio ?? 'N/A',
                            'estado' => $estado ?? 'Disponible',
                            'tipo_espacio' => $espacio->tipo_espacio ?? 'N/A',
                            'puestos_disponibles' => $espacio->puestos_disponibles ?? 0,
                            // Asistencia actual (alumnos presentes en este momento)
                            'asistencia_actual' => $asistenciaActual,
                            // Total de inscritos del curso (prioridad: clase planificada > reserva profesor)
                            'total_inscritos' => (($tieneClase ?? false) && isset($datosClase['inscritos']) && $datosClase['inscritos'] > 0)
                                ? $datosClase['inscritos']
                                : (($tieneReservaProfesor ?? false) && !empty($datosProfesor['inscritos']) ? $datosProfesor['inscritos'] : 0),
                            // Capacidad máxima de la sala (siempre desde el espacio)
                            'capacidad_maxima' => ($espacio->capacidad_maxima && $espacio->capacidad_maxima > 0) ? $espacio->capacidad_maxima : ($espacio->puestos_disponibles ?? 0),
                            // Mostrar información de clase siempre que exista, independientemente del estado
                            'tiene_clase' => $tieneClase ?? false,
                            'tiene_reserva_solicitante' => $tieneReservaSolicitante ?? false,
                            'tiene_reserva_profesor' => $tieneReservaProfesor ?? false,
                            'tiene_reserva_pendiente' => $tieneReservaPendiente ?? false,
                            'datos_clase' => $datosClase,
                            'datos_solicitante' => $datosSolicitante,
                            'datos_profesor' => $datosProfesor,
                            'modulo' => [
                                'numero' => $this->moduloActual['numero'] ?? '--',
                                'inicio' => $this->moduloActual['inicio'] ?? '--:--',
                                'fin' => $this->moduloActual['fin'] ?? '--:--',
                            ],
                            'piso' => $piso->getDisplayNameAttribute(),
                            'proxima_clase' => $proximaClase,
                            'rango_disponibilidad' => $rangoDisponibilidad,
                            'es_recuperacion' => $esRecuperacion,
                            'es_programada' => ($estado === 'Programado'),
                        ];
                    }
                    $this->espacios[$piso->id] = $espaciosPiso;
                }

                Log::info('ModulosActuales - Total espacios procesados: ' . count($this->espacios));
                foreach ($this->pisos as $piso) {
                    $count = isset($this->espacios[$piso->id]) ? count($this->espacios[$piso->id]) : 0;
                    Log::info("  Piso {$piso->id}: {$count} espacios");
                }
            } else {
                // Procesar espacios cuando no hay módulo activo
                Log::info('ModulosActuales - Procesando espacios sin módulo activo');
                Log::info('ModulosActuales - Total pisos: ' . count($this->pisos));

                $this->espacios = [];
                foreach ($this->pisos as $piso) {
                    Log::info('ModulosActuales - Procesando piso: ' . $piso->id, [
                        'nombre' => $piso->nombre_piso,
                        'espacios_count' => count($piso->espacios ?? [])
                    ]);

                    $espaciosPiso = [];

                    try {
                        // Ordenar espacios alfabéticamente por id_espacio
                        $espaciosOrdenados = $piso->espacios ? $piso->espacios->sortBy('id_espacio')->values() : collect();

                        Log::info('ModulosActuales - Espacios ordenados del piso ' . $piso->id . ': ' . count($espaciosOrdenados));

                        foreach ($espaciosOrdenados as $espacio) {
                            Log::info('ModulosActuales - Procesando espacio: ' . ($espacio->id_espacio ?? 'SIN_ID'));

                            try {
                                // Obtener conteo de asistencia actual para este espacio
                                $asistenciaActual = Asistencia::where('id_espacio', $espacio->id_espacio)
                                    ->where('estado', Asistencia::ESTADO_PRESENTE)
                                    ->whereDate('created_at', Carbon::today())
                                    ->count();

                                $espaciosPiso[] = [
                                    'id_espacio' => $espacio->id_espacio ?? 'N/A',
                                    'nombre_espacio' => $espacio->nombre_espacio ?? 'N/A',
                                    'estado' => 'Disponible',
                                    'tipo_espacio' => $espacio->tipo_espacio ?? 'N/A',
                                    'puestos_disponibles' => $espacio->puestos_disponibles ?? 0,
                                    'asistencia_actual' => $asistenciaActual,
                                    'total_inscritos' => 0,
                                    'capacidad_maxima' => ($espacio->capacidad_maxima && $espacio->capacidad_maxima > 0) ? $espacio->capacidad_maxima : ($espacio->puestos_disponibles ?? 0),
                                    'tiene_clase' => false,
                                    'tiene_reserva_solicitante' => false,
                                    'tiene_reserva_profesor' => false,
                                    'datos_clase' => null,
                                    'datos_solicitante' => null,
                                    'datos_profesor' => null,
                                    'modulo' => null,
                                    'piso' => $piso->getDisplayNameAttribute(),
                                    'proxima_clase' => null,
                                ];
                            } catch (\Exception $espaceException) {
                                Log::error('Error procesando espacio en modo sin módulo activo: ' . $espaceException->getMessage(), [
                                    'espacio_id' => $espacio->id_espacio ?? 'UNKNOWN',
                                    'piso_id' => $piso->id,
                                    'exception' => get_class($espaceException),
                                    'file' => $espaceException->getFile(),
                                    'line' => $espaceException->getLine(),
                                ]);
                            }
                        }
                    } catch (\Exception $pisoException) {
                        Log::error('Error procesando piso en modo sin módulo activo: ' . $pisoException->getMessage(), [
                            'piso_id' => $piso->id,
                            'exception' => get_class($pisoException),
                            'file' => $pisoException->getFile(),
                            'line' => $pisoException->getLine(),
                        ]);
                    }

                    $this->espacios[$piso->id] = $espaciosPiso;
                    Log::info('ModulosActuales - Espacios procesados del piso ' . $piso->id . ': ' . count($espaciosPiso));
                }
            }
        } catch (\Exception $e) {
            Log::error('Error en actualizarDatos: ' . $e->getMessage(), [
                'exception' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
                'moduloActual' => $this->moduloActual,
                'pisos_count' => count($this->pisos ?? [])
            ]);

            // Valores por defecto seguros en caso de error
            $this->espacios = [];

            // Si tenemos pisos cargados, al menos crear estructura vacía
            if ($this->pisos && count($this->pisos) > 0) {
                foreach ($this->pisos as $piso) {
                    $this->espacios[$piso->id] = [];
                }
            }
        }
    }

    /**
     * Obtener todos los espacios procesados para la vista
     */
    public function getTodosLosEspacios()
    {
        $todosLosEspacios = [];

        if (!is_array($this->espacios)) {
            Log::warning('getTodosLosEspacios - this->espacios no es un array, es: ' . gettype($this->espacios));
            return [];
        }

        foreach ($this->pisos as $piso) {
            $espaciosPiso = $this->espacios[$piso->id] ?? [];

            if (!is_array($espaciosPiso)) {
                Log::warning('getTodosLosEspacios - espaciosPiso no es un array para piso ' . $piso->id . ', es: ' . gettype($espaciosPiso));
                continue;
            }

            foreach ($espaciosPiso as $espacio) {
                // Excluir salas de estudio
                if (isset($espacio['tipo_espacio']) &&
                    (strtolower($espacio['tipo_espacio']) === 'sala de estudio' ||
                        strtolower($espacio['tipo_espacio']) === 'sala estudio' ||
                        strpos(strtolower($espacio['tipo_espacio']), 'estudio') !== false)) {
                    continue;
                }

                // Ya no es necesario asignar el piso aquí porque ya viene de actualizarDatos()
                // $espacio['piso'] ya está definido como $piso->nombre_piso
                $todosLosEspacios[] = $espacio;
            }
        }

        // Ordenar todos los espacios alfabéticamente por id_espacio
        usort($todosLosEspacios, function ($a, $b) {
            return strcmp($a['id_espacio'], $b['id_espacio']);
        });

        Log::info('getTodosLosEspacios - Total espacios a mostrar: ' . count($todosLosEspacios));

        return $todosLosEspacios;
    }

    /**
     * Determinar el color del estado para un espacio
     */
    public function getEstadoColor($estado, $tieneClase, $tieneReservaSolicitante, $tieneReservaProfesor = false)
    {
        if (strtolower($estado) === 'clase registrada' || $estado === 'Clase registrada') {
            return 'bg-red-500';
        } elseif (strtolower($estado) === 'reserva espontanea' || $estado === 'Reserva Espontánea') {
            return 'bg-red-500';
        } elseif (strtolower($estado) === 'clase no registrada' || $estado === 'Clase no registrada') {
            return 'bg-black';
        } elseif (strtolower($estado) === 'reservado' || $estado === 'Reservado') {
            return 'bg-yellow-400';
        } elseif (strtolower($estado) === 'programado' || $estado === 'Programado') {
            return 'bg-yellow-500';
        } elseif (strtolower($estado) === 'clase programada' || $estado === 'Clase Programada') {
            return 'bg-yellow-500';
        } elseif (strtolower($estado) === 'disponible' || $estado === 'Disponible') {
            return 'bg-green-500';
        } elseif (strtolower($estado) === 'mantencion' || $estado === 'Mantención') {
            return 'bg-gray-400';
        } else {
            return 'bg-green-500';
        }
    }

    /**
     * Obtener solo los apellidos del profesor
     */
    public function getApellidosProfesor($nombreCompleto)
    {
        if (empty($nombreCompleto)) {
            return '-';
        }

        // Si el nombre tiene formato "APELLIDO, NOMBRE"
        if (strpos($nombreCompleto, ',') !== false) {
            $partes = explode(',', $nombreCompleto);
            $apellidos = trim($partes[0]);

            // Convertir a minúsculas manteniendo las tildes
            $apellidos = mb_strtolower($apellidos, 'UTF-8');

            // Convertir primera letra de cada palabra a mayúscula manteniendo tildes
            $apellidos = mb_convert_case($apellidos, MB_CASE_TITLE, 'UTF-8');

            return $apellidos;
        }

        // Si es un nombre simple, convertir a minúsculas y luego a título
        $nombre = mb_strtolower($nombreCompleto, 'UTF-8');

        return mb_convert_case($nombre, MB_CASE_TITLE, 'UTF-8');
    }

    /**
     * Obtener el primer apellido del profesor
     */
    public function getPrimerApellido($nombreCompleto)
    {
        $apellidos = explode(',', $nombreCompleto);

        return trim($apellidos[0] ?? '');
    }

    /**
     * Obtener el primer apellido del solicitante
     */
    public function getPrimerApellidoSolicitante($nombreCompleto)
    {
        $apellidos = explode(',', $nombreCompleto);

        return trim($apellidos[0] ?? '');
    }

    /**
     * Determinar si mostrar información de clase o solicitante
     */
    public function getTipoOcupacion($espacio)
    {
        if ($espacio['tiene_reserva_solicitante']) {
            return 'solicitante';
        } elseif ($espacio['tiene_clase']) {
            return 'clase';
        } else {
            return 'disponible';
        }
    }

    public function selectPiso($pisoId)
    {
        $this->selectedPiso = $pisoId;
    }

    public function render()
    {
        return view('livewire.modulos-actuales-table');
    }

    public function getHoraActualProperty()
    {
        return Carbon::now()->format('H:i:s');
    }

    public function getFechaActualProperty()
    {
        return Carbon::now()->locale('es')->isoFormat('dddd, D [de] MMMM [de] YYYY');
    }

    public function actualizarAutomaticamente()
    {
        try {
            set_time_limit(60);
            ini_set('max_execution_time', 60);

            $this->actualizarDatos();
        } catch (\Exception $e) {
            // Log del error pero continúa la ejecución
            Log::error('Error en actualizarAutomaticamente: ' . $e->getMessage());

            // Actualizar solo datos básicos en caso de error
            $this->horaActual = Carbon::now()->format('H:i:s');
            $this->fechaActual = Carbon::now()->locale('es')->isoFormat('dddd, D [de] MMMM [de] YYYY');
            $this->moduloActual = $this->obtenerModuloActual();
        }
    }

    public function getModuloActual()
    {
        if ($this->moduloActual) {
            return $this->moduloActual['numero'] ?? 'N/A';
        }

        return null;
    }

    public function obtenerProximoModulo()
    {
        $dias = ['domingo', 'lunes', 'martes', 'miercoles', 'jueves', 'viernes', 'sabado'];
        $diaActual = $dias[Carbon::now()->dayOfWeek];
        $horaActual = Carbon::now()->format('H:i:s');

        if (isset(\App\Helpers\ModulosHelper::getHorariosModulos()[$diaActual])) {
            foreach (\App\Helpers\ModulosHelper::getHorariosModulos()[$diaActual] as $numeroModulo => $horario) {
                if ($horaActual < $horario['inicio']) {
                    return $numeroModulo;
                }
            }
        }

        return null;
    }

    public function obtenerProximoModuloInfo()
    {
        $dias = ['domingo', 'lunes', 'martes', 'miercoles', 'jueves', 'viernes', 'sabado'];
        $diaActual = $dias[Carbon::now()->dayOfWeek];
        $horaActual = Carbon::now()->format('H:i:s');

        if (isset(\App\Helpers\ModulosHelper::getHorariosModulos()[$diaActual])) {
            foreach (\App\Helpers\ModulosHelper::getHorariosModulos()[$diaActual] as $numeroModulo => $horario) {
                if ($horaActual < $horario['inicio']) {
                    return $horario;
                }
            }
        }

        return null;
    }

    /**
     * Método auxiliar para validar arrays de manera segura
     */
    public function validarArray($array, $key, $default = null)
    {
        if (is_array($array) && array_key_exists($key, $array)) {
            return $array[$key];
        }

        return $default;
    }
}
