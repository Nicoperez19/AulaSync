<?php

namespace App\Livewire;

use App\Helpers\SemesterHelper;
use App\Models\ClaseNoRealizada;
use App\Models\DiaFeriado;
use App\Models\Espacio;
use App\Models\Modulo;
use App\Models\Piso;
use App\Models\Planificacion_Asignatura;
use App\Models\PlanificacionProfesorColaborador;
use App\Models\ProfesorColaborador;
use App\Models\Reserva;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Livewire\Component;

class ModulosActualesTable extends Component
{
    public $planificaciones = [];

    public $espacios = [];

    public $pisos = [];

    public $horaActual;

    public $fechaActual;

    public $moduloActual;

    public $selectedPiso = null;

    public $esFeriado = false;

    public $nombreFeriado = '';

    // Horarios de módulos basados en la referencia JavaScript
    private $horariosModulos = [
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

    public function mount()
    {
        set_time_limit(120);
        ini_set('max_execution_time', 120);

        $this->actualizarDatos();

        // Establecer el primer piso como seleccionado por defecto
        if (count($this->pisos) > 0) {
            $this->selectedPiso = $this->pisos[0]->id;
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
        // Si no se proporciona cache, hacer consulta optimizada
        if ($planificacionesCache === null) {
            $dias = ['domingo', 'lunes', 'martes', 'miercoles', 'jueves', 'viernes', 'sabado'];
            $diaActual = $dias[Carbon::now()->dayOfWeek];

            $diasPrefijos = [
                'lunes' => 'LU',
                'martes' => 'MA',
                'miercoles' => 'MI',
                'jueves' => 'JU',
                'viernes' => 'VI'
            ];

            $prefijoDia = $diasPrefijos[$diaActual] ?? '';
            if (!$prefijoDia) return null;

            $planificacionesCache = Planificacion_Asignatura::with(['asignatura.profesor'])
                ->where('id_espacio', $idEspacio)
                ->where('id_modulo', 'LIKE', $prefijoDia . '.%')
                ->whereHas('horario', function($q) use ($periodo) {
                    $q->where('periodo', $periodo);
                })
                ->get();
        }

        // Procesar con datos en cache...
        // Resto de la lógica optimizada
        */
    }

    /**
     * Verificar si una clase debe marcarse como no realizada
     */
    private function verificarClaseNoRealizada($planificacionActiva, $tieneReservaProfesor, $periodo, $moduloActual)
    {
        if (! $planificacionActiva) {
            return false; // Si no hay clase planificada, no hay nada que verificar
        }

        // Obtener todas las planificaciones de esta asignatura para encontrar el primer y último módulo
        $todasLasPlanificaciones = Planificacion_Asignatura::with(['modulo'])
            ->where('id_asignatura', $planificacionActiva->id_asignatura)
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
        $tuvoEntradaHoy = \App\Models\Reserva::where('id_espacio', $planificacionActiva->id_espacio)
            ->where('fecha_reserva', Carbon::now()->toDateString())
            ->whereNotNull('run_profesor')
            ->whereNotNull('hora') // El profesor sí entró (hora es la hora de entrada)
            ->exists();

        // Si el profesor SÍ registró entrada en este espacio, la clase SÍ se realizó
        if ($tuvoEntradaHoy) {
            return false; // La clase SÍ se realizó
        }

        // Verificar si el profesor registró entrada HOY en OTRO espacio (cambio de sala)
        $runProfesor = $planificacionActiva->asignatura->run_profesor ?? null;
        if ($runProfesor) {
            $tuvoEntradaEnOtroEspacio = \App\Models\Reserva::where('id_espacio', '!=', $planificacionActiva->id_espacio)
                ->where('fecha_reserva', Carbon::now()->toDateString())
                ->where('run_profesor', $runProfesor)
                ->whereNotNull('hora') // El profesor sí entró
                ->exists();

            // Si el profesor registró entrada en otro espacio, la clase SÍ se realizó (solo en otro lugar)
            if ($tuvoEntradaEnOtroEspacio) {
                return false; // La clase SÍ se realizó, pero en otro espacio
            }
        }

        // Verificar si la clase ya terminó completamente
        if ($this->verificarClaseFinalizada($numeroUltimoModulo, $moduloActual)) {
            // Si la clase terminó y NO hubo entrada, es clase no realizada
            // Construir el id_modulo completo con todos los módulos de la asignatura programados hoy
            // Extraer el prefijo del día (ej: "MI" de "MI.2")
            $prefijoDia = explode('.', $primeraPlanificacion->id_modulo)[0] ?? '';
            
            $todosModulos = Planificacion_Asignatura::where('id_asignatura', $planificacionActiva->id_asignatura)
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
                'run_profesor' => $planificacionActiva->asignatura->run_profesor ?? '',
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
        ];

        $diaKey = $mapaDias[$diaKey] ?? $diaKey;
        $horariosDelDia = $this->horariosModulos[$diaKey] ?? null;

        if (! $horariosDelDia || ! isset($horariosDelDia[$numeroPrimerModulo])) {
            return false;
        }

        $horaInicioPrimerModulo = $horariosDelDia[$numeroPrimerModulo]['inicio'];
        $horaActual = Carbon::now()->format('H:i:s');

        // Calcular el tiempo transcurrido desde el inicio del primer módulo
        $inicioModulo = Carbon::createFromTimeString($horaInicioPrimerModulo);
        $ahora = Carbon::createFromTimeString($horaActual);

        // Solo marcar como no realizada si han pasado 20 minutos desde el inicio del primer módulo
        // Y si NO hay reserva con entrada del profesor (ni en este espacio ni en otro)
        $hasPasado20Minutos = $ahora->diffInMinutes($inicioModulo) >= 20;

        // Verificar nuevamente si registró entrada en otro espacio
        $tuvoEntradaEnOtroEspacio = false;
        if ($runProfesor) {
            $tuvoEntradaEnOtroEspacio = \App\Models\Reserva::where('id_espacio', '!=', $planificacionActiva->id_espacio)
                ->where('fecha_reserva', Carbon::now()->toDateString())
                ->where('run_profesor', $runProfesor)
                ->whereNotNull('hora')
                ->exists();
        }

        if ($moduloActual && ! $tuvoEntradaHoy && ! $tuvoEntradaEnOtroEspacio && $hasPasado20Minutos) {
            // Registrar la clase no realizada
            // Construir el id_modulo completo con todos los módulos de la asignatura programados hoy
            // Extraer el prefijo del día (ej: "MI" de "MI.2")
            $prefijoDia = explode('.', $primeraPlanificacion->id_modulo)[0] ?? '';
            
            $todosModulos = Planificacion_Asignatura::where('id_asignatura', $planificacionActiva->id_asignatura)
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
                'run_profesor' => $planificacionActiva->asignatura->run_profesor ?? '',
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
        if (! $moduloActual || ! $numeroUltimoModulo) {
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
        if (! $moduloActual || ! $numeroUltimoModulo) {
            return false;
        }

        // Si estamos en el rango de módulos de la clase o antes del final
        if ($moduloActual['numero'] <= $numeroUltimoModulo) {
            // Verificar si hay una reserva del profesor que ya finalizó (con hora_salida)
            $reservaFinalizada = \App\Models\Reserva::where('id_espacio', $espacio)
                ->where('fecha_reserva', \Carbon\Carbon::now()->toDateString())
                ->whereNotNull('run_profesor')
                ->where('estado', 'finalizada')
                ->whereNotNull('hora_salida')
                ->orderBy('created_at', 'desc')
                ->first();

            if ($reservaFinalizada) {
                // Verificar que la reserva terminó hoy
                $horaActual = \Carbon\Carbon::now()->format('H:i:s');
                $horaSalida = $reservaFinalizada->hora_salida;

                // Si ya registró salida, la clase terminó antes
                return $horaSalida < $horaActual || ! empty($horaSalida);
            }
        }

        return false;
    }

    /**
     * Verificar si una clase está actualmente en curso
     */
    private function verificarClaseEnCurso($numeroModuloInicio, $numeroModuloFin, $moduloActual)
    {
        if (! $moduloActual || ! $numeroModuloInicio || ! $numeroModuloFin) {
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
        if ($diaActual === 'domingo' || $diaActual === 'sabado') {
            return null;
        }

        $horariosDelDia = $this->horariosModulos[$diaActual] ?? null;
        if (! $horariosDelDia) {
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
        ];

        $diaKey = $mapaDias[$diaKey] ?? $diaKey;
        $horariosDelDia = $this->horariosModulos[$diaKey] ?? null;

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
            $this->pisos = Piso::with(['espacios'])->get();

            if (! $this->pisos) {
                $this->pisos = collect();
            }

            Log::info('ModulosActuales - Pisos cargados: ' . $this->pisos->count());
            Log::info('ModulosActuales - Módulo actual: ' . json_encode($this->moduloActual));

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
                }

                $idModulo = $prefijoDia.'.'.$this->moduloActual['numero'];
                $moduloDB = Modulo::where('id_modulo', $idModulo)
                    ->where('dia', $diaActual)
                    ->first();

                Log::info('ModulosActuales - Buscando módulo:', [
                    'id_modulo' => $idModulo,
                    'dia' => $diaActual,
                    'encontrado' => $moduloDB ? 'SÍ' : 'NO'
                ]);

                if ($moduloDB) {
                    // Obtener todas las planificaciones del módulo actual con eager loading optimizado
                    $planificacionesActivas = Planificacion_Asignatura::with([
                        'asignatura.profesor',
                        'asignatura.carrera',
                        'espacio',
                        'modulo',
                    ])
                        ->where('id_modulo', $moduloDB->id_modulo)
                        ->whereHas('horario', function ($q) use ($periodo) {
                            $q->where('periodo', $periodo);
                        })
                        ->get()
                        ->keyBy('id_espacio'); // Indexar por espacio para búsqueda rápida

                    Log::info('ModulosActuales - Planificaciones activas encontradas: ' . $planificacionesActivas->count());
                    Log::info('ModulosActuales - ID Módulo buscado: ' . $moduloDB->id_modulo);
                    Log::info('ModulosActuales - Periodo: ' . $periodo);

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
                        ->groupBy('id_asignatura'); // Agrupar por asignatura para búsqueda rápida
                } else {
                    $planificacionesActivas = collect();
                    $planificacionesColaboradores = collect();
                    $todasLasPlanificaciones = collect();
                }

                // Obtener reservas activas de solicitantes para el día actual
                $reservasSolicitantes = Reserva::with(['solicitante'])
                    ->where('fecha_reserva', Carbon::now()->toDateString())
                    ->where('estado', 'activa')
                    ->whereNotNull('run_solicitante')
                    ->get()
                    ->keyBy('id_espacio'); // Indexar por espacio para búsqueda rápida

                // Obtener reservas de profesores para el día actual
                // Solo considerar las que tienen entrada registrada (hora) y están ACTIVAS
                $reservasProfesores = Reserva::with(['profesor', 'asignatura', 'asignatura.carrera'])
                    ->where('fecha_reserva', Carbon::now()->toDateString())
                    ->where('estado', 'activa') // Solo activas, no finalizadas
                    ->whereNotNull('run_profesor')
                    ->whereNotNull('hora') // Solo las que el profesor sí entró
                    ->get()
                    ->keyBy('id_espacio'); // Indexar por espacio para búsqueda rápida

                // Crear índice de profesores que registraron entrada (para detectar cambios de sala)
                $profesoresConEntrada = $reservasProfesores->pluck('run_profesor')->unique();

                // Obtener TODAS las reservas del día (incluyendo las no activas) para calcular disponibilidad
                $todasLasReservas = Reserva::where('fecha_reserva', Carbon::now()->toDateString())
                    ->get()
                    ->groupBy('id_espacio');

                // Procesar espacios por piso con optimizaciones
                $this->espacios = [];
                $dias = ['domingo', 'lunes', 'martes', 'miercoles', 'jueves', 'viernes', 'sabado'];
                $diaActual = $dias[Carbon::now()->dayOfWeek];
                $horariosDelDia = $this->horariosModulos[$diaActual] ?? [];

                foreach ($this->pisos as $piso) {
                    $espaciosPiso = [];
                    // Ordenar espacios alfabéticamente por id_espacio
                    $espaciosOrdenados = $piso->espacios->sortBy('id_espacio')->values();
                    foreach ($espaciosOrdenados as $espacio) {
                        // Buscar si el espacio tiene una planificación activa (búsqueda O(1))
                        $planificacionActiva = $planificacionesActivas->get($espacio->id_espacio);

                        // Buscar si el espacio tiene una reserva de solicitante (búsqueda O(1))
                        $reservaSolicitante = $reservasSolicitantes->get($espacio->id_espacio);

                        // Buscar si el espacio tiene una reserva de profesor (búsqueda O(1))
                        $reservaProfesor = $reservasProfesores->get($espacio->id_espacio);

                        $tieneClase = false;
                        $tieneReservaSolicitante = false;
                        $tieneReservaProfesor = false;
                        $datosClase = null;
                        $datosSolicitante = null;
                        $datosProfesor = null;
                        $claseMovidaAOtraSala = false;
                        $rangoDisponibilidad = null;
                        $esRecuperacion = false;

                        // Verificar si hay una clase de recuperación pendiente para este espacio hoy
                        // Una clase está pendiente de recuperación cuando: estado='pendiente' y fecha_clase=hoy y id_espacio coincide
                        // El id_modulo puede ser un solo módulo "LU.1" o múltiples "LU.1,LU.2,LU.3"
                        $claseRecuperacionPendiente = \App\Models\ClaseNoRealizada::where('fecha_clase', Carbon::now()->toDateString())
                            ->where('id_espacio', $espacio->id_espacio)
                            ->where('estado', 'pendiente') // Está esperando ser recuperada
                            ->where(function($query) use ($idModulo) {
                                // Buscar si id_modulo es exactamente el módulo actual
                                // O si contiene el módulo actual en una lista separada por comas
                                $query->where('id_modulo', $idModulo)
                                      ->orWhere('id_modulo', 'LIKE', $idModulo . ',%')
                                      ->orWhere('id_modulo', 'LIKE', '%,' . $idModulo)
                                      ->orWhere('id_modulo', 'LIKE', '%,' . $idModulo . ',%');
                            })
                            ->with(['profesor', 'asignatura.carrera'])
                            ->first();

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
                                $hasPasado20Minutos = $ahora->diffInMinutes($inicioModulo) >= 20;
                                
                                // Verificar si el profesor ha registrado entrada hoy
                                $tuvoEntradaHoy = \App\Models\Reserva::where('fecha_reserva', Carbon::now()->toDateString())
                                    ->where('id_espacio', $espacio->id_espacio)
                                    ->where('run_profesor', $runProfesor)
                                    ->whereNotNull('hora')
                                    ->exists();
                                
                                $tuvoEntradaEnOtroEspacio = false;
                                if (!$tuvoEntradaHoy) {
                                    $tuvoEntradaEnOtroEspacio = \App\Models\Reserva::where('id_espacio', '!=', $espacio->id_espacio)
                                        ->where('fecha_reserva', Carbon::now()->toDateString())
                                        ->where('run_profesor', $runProfesor)
                                        ->whereNotNull('hora')
                                        ->exists();
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
                                            $numeros[] = (int)$match[1];
                                        }
                                    }
                                    if (count($numeros) > 0) {
                                        $moduloInicio = min($numeros);
                                        $moduloFin = max($numeros);
                                    }
                                } elseif (preg_match('/\.(\d+)/', $idModuloOriginal, $match)) {
                                    // Un solo módulo
                                    $moduloInicio = $moduloFin = (int)$match[1];
                                }

                                // Calcular hora de fin correcta basada en el módulo final
                                $horaInicio = $this->moduloActual['inicio'] ?? '--:--';
                                $horaFin = $this->moduloActual['fin'] ?? '--:--';
                                
                                if ($moduloFin > $moduloInicio) {
                                    // Construir id_modulo del último módulo (ej: "MI.3" si estamos en miércoles)
                                    $dias = ['DO', 'LU', 'MA', 'MI', 'JU', 'VI', 'SA'];
                                    $prefijoDia = $dias[Carbon::now()->dayOfWeek];
                                    $idModuloFinal = $prefijoDia . '.' . $moduloFin;
                                    
                                    $moduloFinal = \App\Models\Modulo::where('id_modulo', $idModuloFinal)->first();
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
                                    
                                $planificacionOriginal = \App\Models\Planificacion_Asignatura::where('id_asignatura', $claseRecuperacionPendiente->id_asignatura)
                                    ->first(); // Tomar cualquier planificación de esta asignatura
                                
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
                        if ($planificacionActiva && $planificacionActiva->asignatura && !$tieneReservaProfesor) {
                            $tieneClase = true;

                            // Obtener todas las planificaciones de esta asignatura usando datos pre-cargados
                            $planificacionesAsignatura = $todasLasPlanificaciones->get($planificacionActiva->id_asignatura, collect())
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
                                    'name' => $planificacionActiva->asignatura->profesor->name ?? '-',
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
                            $tieneReservaSolicitante = true;
                            $datosSolicitante = [
                                'nombre' => $reservaSolicitante->solicitante->nombre ?? '-',
                                'run' => $reservaSolicitante->run_solicitante ?? '-',
                                'tipo_solicitante' => $reservaSolicitante->solicitante->tipo_solicitante ?? '-',
                                'hora_inicio' => $reservaSolicitante->hora ?? '-',
                                'hora_salida' => $reservaSolicitante->hora_salida ?? '-',
                            ];
                        }

                        // PRIORIDAD 1: Procesar reserva de profesor PRIMERO (es la clase que realmente está dando)
                        if ($reservaProfesor) {
                            $tieneReservaProfesor = true;
                            
                            // Si la reserva tiene asignatura, usarla para obtener la planificación correcta
                            if ($reservaProfesor->asignatura) {
                                // Buscar las planificaciones de ESTA asignatura (no del espacio)
                                $planificacionesReserva = Planificacion_Asignatura::where('id_asignatura', $reservaProfesor->asignatura->id_asignatura)
                                    ->whereHas('horario', function($q) use ($periodo) {
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
                                ];
                            } else {
                                // Reserva sin asignatura (uso libre)
                                $datosProfesor = [
                                    'nombre' => $reservaProfesor->profesor->name ?? '-',
                                    'run' => $reservaProfesor->run_profesor ?? '-',
                                    'hora_inicio' => $reservaProfesor->hora ?? '-',
                                    'hora_salida' => $reservaProfesor->hora_salida ?? '-',
                                    'nombre_asignatura' => 'Uso libre',
                                    'codigo_asignatura' => '-',
                                    'carrera' => '-',
                                    'inscritos' => null,
                                ];
                            }
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
                            $numeroModuloInicio = (int)$datosProfesor['modulo_inicio'];
                            $numeroModuloFin = (int)$datosProfesor['modulo_fin'];
                            
                            // Verificar si la clase terminó antes (profesor registró salida)
                            $claseTerminoAntes = $this->verificarClaseTerminoAntes($espacio->id_espacio, $numeroModuloFin, $this->moduloActual);
                            
                            // Verificar si la clase ha finalizado por horario
                            $claseFinalizada = $this->verificarClaseFinalizada($numeroModuloFin, $this->moduloActual);
                        } elseif ($tieneClase && $planificacionActiva && $planificacionActiva->asignatura) {
                            // Obtener los números de módulos para verificar estado
                            $planificacionesAsignatura = $todasLasPlanificaciones->get($planificacionActiva->id_asignatura, collect())
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
                                if (! $claseFinalizada && ! $claseTerminoAntes && ! $tieneReservaProfesor) {
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
                        } elseif ($tieneReservaSolicitante) {
                            // CORRECIÓN BUG 2: Si hay reserva espontánea activa, SIEMPRE mostrar como Ocupado
                            // Esto debe evaluarse ANTES que las verificaciones de clase finalizada
                            $estado = 'Ocupado';
                        } elseif ($tieneClase && ($claseFinalizada || $claseTerminoAntes)) {
                            // Si la clase ya terminó (por horario o porque el profesor se fue antes)
                            // Limpiar información de la clase y marcar como disponible
                            $estado = 'Disponible';
                            $tieneClase = false;
                            $datosClase = null;
                        } elseif ($tieneClase && $tieneReservaProfesor && ! $claseFinalizada && ! $claseTerminoAntes) {
                            // Si hay clase y el profesor registró su ingreso Y la clase NO ha terminado
                            // Verificar que realmente esté en el rango de módulos de la clase
                            $claseEnCurso = $this->verificarClaseEnCurso((int) $numeroModuloInicio, (int) $numeroModuloFin, $this->moduloActual);
                            if ($claseEnCurso) {
                                $estado = 'Ocupado';
                            } else {
                                // Si no estamos en el rango de la clase, el espacio está disponible
                                $estado = 'Disponible';
                                $tieneClase = false;
                                $datosClase = null;
                                $tieneReservaProfesor = false;
                                $datosProfesor = null;
                            }
                        } elseif ($tieneClase && ! $tieneReservaProfesor && $claseNoRealizada) {
                            // Si hay clase programada pero se detectó que no fue realizada
                            $estado = 'Clase no realizada';
                        } elseif ($tieneClase && ! $tieneReservaProfesor) {
                            // Si hay clase programada pero el profesor no ha registrado su ingreso
                            // Verificar si la clase ya debería haber empezado (no estamos en break antes de la clase)
                            $claseYaDebioEmpezar = false;
                            if ($this->moduloActual && isset($this->moduloActual['tipo'])) {
                                if ($this->moduloActual['tipo'] === 'break') {
                                    // En break: verificar si el siguiente módulo es mayor al de inicio de la clase
                                    $claseYaDebioEmpezar = $this->moduloActual['numero'] >= $numeroModuloInicio;
                                } else {
                                    // En módulo: verificar si estamos en o después del módulo de inicio
                                    $claseYaDebioEmpezar = $this->moduloActual['numero'] >= $numeroModuloInicio;
                                }
                            }

                            // Si la clase ya debió empezar pero el profesor no ha llegado, marcarla como "Clase por iniciar"
                            // Si la clase aún no debía empezar, el espacio está "Disponible" y NO mostrar información de clase futura
                            if ($claseYaDebioEmpezar) {
                                $estado = 'Clase por iniciar';
                            } else {
                                // La clase es más tarde, mostrar como disponible sin información de clase
                                $estado = 'Disponible';
                                $tieneClase = false;
                                $datosClase = null;
                            }
                        } elseif ($proximaClase) {
                            $estado = 'Clase por iniciar';
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
                        }

                        $espaciosPiso[] = [
                            'id_espacio' => $espacio->id_espacio ?? 'N/A',
                            'nombre_espacio' => $espacio->nombre_espacio ?? 'N/A',
                            'estado' => $estado ?? 'Disponible',
                            'tipo_espacio' => $espacio->tipo_espacio ?? 'N/A',
                            'puestos_disponibles' => $espacio->puestos_disponibles ?? 0,
                            'capacidad_maxima' => (($tieneClase ?? false) && isset($datosClase['inscritos']) && $datosClase['inscritos'] > 0)
                                ? $datosClase['inscritos']
                                : (($tieneReservaProfesor ?? false) && !empty($datosProfesor['inscritos']) ? $datosProfesor['inscritos'] : ($espacio->capacidad_maxima ?? 0)),
                            // Mostrar información de clase siempre que exista, independientemente del estado
                            'tiene_clase' => $tieneClase ?? false,
                            'tiene_reserva_solicitante' => $tieneReservaSolicitante ?? false,
                            'tiene_reserva_profesor' => $tieneReservaProfesor ?? false,
                            'datos_clase' => $datosClase,
                            'datos_solicitante' => $datosSolicitante,
                            'datos_profesor' => $datosProfesor,
                            'modulo' => [
                                'numero' => $this->moduloActual['numero'] ?? '--',
                                'inicio' => $this->moduloActual['inicio'] ?? '--:--',
                                'fin' => $this->moduloActual['fin'] ?? '--:--',
                            ],
                            'piso' => $piso->getNombrePisoAttribute(),
                            'proxima_clase' => $proximaClase,
                            'rango_disponibilidad' => $rangoDisponibilidad,
                            'es_recuperacion' => $esRecuperacion,
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
                $this->espacios = [];
                foreach ($this->pisos as $piso) {
                    $espaciosPiso = [];
                    // Ordenar espacios alfabéticamente por id_espacio
                    $espaciosOrdenados = $piso->espacios->sortBy('id_espacio')->values();
                    foreach ($espaciosOrdenados as $espacio) {
                        $espaciosPiso[] = [
                            'id_espacio' => $espacio->id_espacio ?? 'N/A',
                            'nombre_espacio' => $espacio->nombre_espacio ?? 'N/A',
                            'estado' => 'Disponible',
                            'tipo_espacio' => $espacio->tipo_espacio ?? 'N/A',
                            'puestos_disponibles' => $espacio->puestos_disponibles ?? 0,
                            'capacidad_maxima' => $espacio->capacidad_maxima ?? 0,
                            'tiene_clase' => false,
                            'tiene_reserva_solicitante' => false,
                            'tiene_reserva_profesor' => false,
                            'datos_clase' => null,
                            'datos_solicitante' => null,
                            'datos_profesor' => null,
                            'modulo' => null,
                            'piso' => $piso->getNombrePisoAttribute(),
                            'proxima_clase' => null,
                        ];
                    }
                    $this->espacios[$piso->id] = $espaciosPiso;
                }
            }
        } catch (\Exception $e) {
            Log::error('Error en actualizarDatos: '.$e->getMessage());

            // Valores por defecto seguros en caso de error
            $this->espacios = [];
        }
    }

    /**
     * Obtener todos los espacios procesados para la vista
     */
    public function getTodosLosEspacios()
    {
        $todosLosEspacios = [];

        foreach ($this->pisos as $piso) {
            $espaciosPiso = $this->espacios[$piso->id] ?? [];
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
        if (strtolower($estado) === 'ocupado' || $estado === 'Ocupado') {
            return 'bg-red-500';
        } elseif (strtolower($estado) === 'clase finalizada' || $estado === 'Clase finalizada') {
            return 'bg-blue-500'; // Color azul para clases que terminaron
        } elseif (strtolower($estado) === 'clase no realizada' || $estado === 'Clase no realizada') {
            return 'bg-black'; // Color más oscuro para indicar problema
        } elseif (strtolower($estado) === 'reservado' || $estado === 'Reservado') {
            return 'bg-yellow-400';
        } elseif (strtolower($estado) === 'clase por iniciar' || $estado === 'Clase por iniciar') {
            return 'bg-yellow-500'; // Amarillo más brillante para que sea visible el puntito
        } elseif (strtolower($estado) === 'en programa' || $estado === 'En Programa') {
            return 'bg-yellow-500';
        } elseif (strtolower($estado) === 'disponible' || $estado === 'Disponible') {
            return 'bg-green-500';
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
            Log::error('Error en actualizarAutomaticamente: '.$e->getMessage());

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

        if (isset($this->horariosModulos[$diaActual])) {
            foreach ($this->horariosModulos[$diaActual] as $numeroModulo => $horario) {
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

        if (isset($this->horariosModulos[$diaActual])) {
            foreach ($this->horariosModulos[$diaActual] as $numeroModulo => $horario) {
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
