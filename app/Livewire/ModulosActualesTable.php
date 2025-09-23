<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Planificacion_Asignatura;
use App\Models\Espacio;
use App\Models\Piso;
use App\Models\Modulo;
use App\Models\Reserva;
use App\Helpers\SemesterHelper;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class ModulosActualesTable extends Component
{
    public $planificaciones = [];
    public $espacios = [];
    public $pisos = [];
    public $horaActual;
    public $fechaActual;
    public $moduloActual;
    public $selectedPiso = null;

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
            15 => ['inicio' => '22:10:00', 'fin' => '23:00:00']
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
            15 => ['inicio' => '22:10:00', 'fin' => '23:00:00']
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
            15 => ['inicio' => '22:10:00', 'fin' => '23:00:00']
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
            15 => ['inicio' => '22:10:00', 'fin' => '23:00:00']
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
            15 => ['inicio' => '22:10:00', 'fin' => '23:00:00']
        ]
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
                    'tipo' => 'modulo'
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
                    'mensaje' => 'Próximo Módulo'
                ];
            }
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
            
            // Obtener el módulo actual usando la nueva lógica
            $this->moduloActual = $this->obtenerModuloActual();

            // Obtener todos los pisos con sus espacios
            $this->pisos = Piso::with(['espacios'])->get();

            if (!$this->pisos) {
                $this->pisos = collect();
            }

            // Resto del procesamiento existente...
            if ($this->moduloActual) {
                // Determinar el período actual usando el helper
                $anioActual = SemesterHelper::getCurrentAcademicYear();
                $semestre = SemesterHelper::getCurrentSemester();
                $periodo = SemesterHelper::getCurrentPeriod();

                // Buscar el módulo en la base de datos para obtener el ID
                // El id_modulo tiene formato "JU.1", "LU.10", etc. Necesitamos extraer el número
                $diaActual = Carbon::now()->locale('es')->isoFormat('dddd');
            $prefijoDia = '';
            
            // Mapear el día a su prefijo
            switch (strtolower($diaActual)) {
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
            
            $moduloDB = Modulo::where('id_modulo', $prefijoDia . '.' . $this->moduloActual['numero'])
                ->where('dia', $diaActual)
                ->first();

            if ($moduloDB) {
                // Obtener todas las planificaciones del módulo actual con eager loading optimizado
                $planificacionesActivas = Planificacion_Asignatura::with([
                    'asignatura.profesor',
                    'espacio',
                    'modulo'
                ])
                ->where('id_modulo', $moduloDB->id_modulo)
                ->whereHas('horario', function($q) use ($periodo) {
                    $q->where('periodo', $periodo);
                })
                ->get()
                ->keyBy('id_espacio'); // Indexar por espacio para búsqueda rápida
                
                // Pre-cargar TODAS las planificaciones del período para optimizar búsquedas
                $todasLasPlanificaciones = Planificacion_Asignatura::with(['modulo'])
                    ->whereHas('horario', function($q) use ($periodo) {
                        $q->where('periodo', $periodo);
                    })
                    ->get()
                    ->groupBy('id_asignatura'); // Agrupar por asignatura para búsqueda rápida
            } else {
                $planificacionesActivas = collect();
                $todasLasPlanificaciones = collect();
            }

            // Obtener reservas activas de solicitantes para el día actual
            $reservasSolicitantes = Reserva::with(['solicitante'])
                ->where('fecha_reserva', Carbon::now()->toDateString())
                ->where('estado', 'activa')
                ->whereNotNull('run_solicitante')
                ->get()
                ->keyBy('id_espacio'); // Indexar por espacio para búsqueda rápida

            // Obtener reservas activas de profesores para el día actual
            $reservasProfesores = Reserva::with(['profesor'])
                ->where('fecha_reserva', Carbon::now()->toDateString())
                ->where('estado', 'activa')
                ->whereNotNull('run_profesor')
                ->get()
                ->keyBy('id_espacio'); // Indexar por espacio para búsqueda rápida

            // Procesar espacios por piso con optimizaciones
            $this->espacios = [];
            $dias = ['domingo', 'lunes', 'martes', 'miercoles', 'jueves', 'viernes', 'sabado'];
            $diaActual = $dias[Carbon::now()->dayOfWeek];
            $horariosDelDia = $this->horariosModulos[$diaActual] ?? [];
            
            foreach ($this->pisos as $piso) {
                $espaciosPiso = [];
                foreach ($piso->espacios as $espacio) {
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

                    if ($planificacionActiva) {
                        $tieneClase = true;
                        
                        // Obtener todas las planificaciones de esta asignatura usando datos pre-cargados
                        $planificacionesAsignatura = $todasLasPlanificaciones->get($planificacionActiva->id_asignatura, collect())
                            ->sortBy(function($planificacion) {
                                // Extraer el número del módulo para ordenar
                                $moduloParts = explode('.', $planificacion->id_modulo);
                                return isset($moduloParts[1]) ? (int)$moduloParts[1] : 0;
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
                                'name' => $planificacionActiva->asignatura->profesor->name ?? '-'
                            ],
                            'modulo_inicio' => $numeroModuloInicio,
                            'modulo_fin' => $numeroModuloFin,
                            'hora_inicio' => $horaInicio,
                            'hora_fin' => $horaFin
                        ];
                    }

                    if ($reservaSolicitante) {
                        $tieneReservaSolicitante = true;
                        $datosSolicitante = [
                            'nombre' => $reservaSolicitante->solicitante->nombre ?? '-',
                            'run' => $reservaSolicitante->run_solicitante ?? '-',
                            'tipo_solicitante' => $reservaSolicitante->solicitante->tipo_solicitante ?? '-',
                            'hora_inicio' => $reservaSolicitante->hora ?? '-',
                            'hora_salida' => $reservaSolicitante->hora_salida ?? '-'
                        ];
                    }

                    if ($reservaProfesor) {
                        $tieneReservaProfesor = true;
                        $datosProfesor = [
                            'nombre' => $reservaProfesor->profesor->name ?? '-',
                            'run' => $reservaProfesor->run_profesor ?? '-',
                            'hora_inicio' => $reservaProfesor->hora ?? '-',
                            'hora_salida' => $reservaProfesor->hora_salida ?? '-'
                        ];
                    }

                    // Buscar la próxima clase para este espacio si no tiene clase actual
                    // TEMPORALMENTE DESACTIVADO para evitar timeout
                    $proximaClase = null;
                    // if (!$tieneClase) {
                    //     $proximaClase = $this->obtenerProximaClase($espacio->id_espacio, $periodo);
                    // }

                    // Determinar el estado dinámicamente
                    if (($tieneClase && $tieneReservaProfesor) || $tieneReservaSolicitante) {
                        // Si hay clase y el profesor registró su ingreso, O si hay reserva de solicitante
                        $estado = 'Ocupado';
                    } elseif ($tieneClase && !$tieneReservaProfesor) {
                        // Si hay clase programada pero el profesor no ha registrado su ingreso
                        $estado = 'En Programa';
                    } elseif ($proximaClase) {
                        $estado = 'En Programa';
                    } else {
                        $estado = $espacio->estado ?? 'Disponible';
                    }

                    $espaciosPiso[] = [
                        'id_espacio' => $espacio->id_espacio ?? 'N/A',
                        'nombre_espacio' => $espacio->nombre_espacio ?? 'N/A',
                        'estado' => $estado ?? 'Disponible',
                        'tipo_espacio' => $espacio->tipo_espacio ?? 'N/A',
                        'puestos_disponibles' => $espacio->puestos_disponibles ?? 0,
                        'tiene_clase' => $tieneClase ?? false,
                        'tiene_reserva_solicitante' => $tieneReservaSolicitante ?? false,
                        'tiene_reserva_profesor' => $tieneReservaProfesor ?? false,
                        'datos_clase' => $datosClase,
                        'datos_solicitante' => $datosSolicitante,
                        'datos_profesor' => $datosProfesor,
                        'modulo' => [
                            'numero' => $this->moduloActual['numero'] ?? '--',
                            'inicio' => $this->moduloActual['inicio'] ?? '--:--',
                            'fin' => $this->moduloActual['fin'] ?? '--:--'
                        ],
                        'piso' => $piso->nombre_piso ?? 'N/A',
                        'proxima_clase' => $proximaClase
                    ];
                }
                $this->espacios[$piso->id] = $espaciosPiso;
            }
        } else {
            // Procesar espacios cuando no hay módulo activo
            $this->espacios = [];
            foreach ($this->pisos as $piso) {
                $espaciosPiso = [];
                foreach ($piso->espacios as $espacio) {
                    $espaciosPiso[] = [
                        'id_espacio' => $espacio->id_espacio ?? 'N/A',
                        'nombre_espacio' => $espacio->nombre_espacio ?? 'N/A',
                        'estado' => 'Disponible',
                        'tipo_espacio' => $espacio->tipo_espacio ?? 'N/A',
                        'puestos_disponibles' => $espacio->puestos_disponibles ?? 0,
                        'tiene_clase' => false,
                        'tiene_reserva_solicitante' => false,
                        'tiene_reserva_profesor' => false,
                        'datos_clase' => null,
                        'datos_solicitante' => null,
                        'datos_profesor' => null,
                        'modulo' => null,
                        'piso' => $piso->nombre_piso ?? 'N/A',
                        'proxima_clase' => null
                    ];
                }
                $this->espacios[$piso->id] = $espaciosPiso;
            }
        }
        } catch (\Exception $e) {
            Log::error('Error en actualizarDatos: ' . $e->getMessage());
            
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
                
                // Excluir TH-AUD específicamente
                if (isset($espacio['id_espacio']) && $espacio['id_espacio'] === 'TH-AUD') {
                    continue;
                }
                
                $espacio['piso'] = $piso->numero_piso;
                $todosLosEspacios[] = $espacio;
            }
        }
        
        return $todosLosEspacios;
    }

    /**
     * Determinar el color del estado para un espacio
     */
    public function getEstadoColor($estado, $tieneClase, $tieneReservaSolicitante, $tieneReservaProfesor = false)
    {
        if (strtolower($estado) === 'ocupado' || $estado === 'Ocupado') {
            return 'bg-red-500';
        } elseif (strtolower($estado) === 'reservado' || $estado === 'Reservado') {
            return 'bg-yellow-400';
        } elseif (strtolower($estado) === 'en programa' || $estado === 'En Programa') {
            return 'bg-yellow-500';
        } elseif ($tieneClase || $tieneReservaSolicitante || $tieneReservaProfesor) {
            return 'bg-yellow-400';
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