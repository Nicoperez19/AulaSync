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
        $this->actualizarDatos();
        
        // Establecer el primer piso como seleccionado por defecto
        if ($this->pisos->count() > 0) {
            $this->selectedPiso = $this->pisos->first()->id;
        }
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
                    'fin' => $modulo['fin']
                ];
            }
        }

        return null;
    }

    public function actualizarDatos()
    {
        $this->horaActual = Carbon::now()->format('H:i:s');
        $this->fechaActual = Carbon::now()->locale('es')->isoFormat('dddd, D [de] MMMM [de] YYYY');
        
        // Obtener el módulo actual usando la nueva lógica
        $this->moduloActual = $this->obtenerModuloActual();

        // Obtener todos los pisos con sus espacios (optimizado)
        $this->pisos = Piso::with(['espacios'])->get();

        if ($this->moduloActual) {
            // Determinar el período actual usando el helper
            $periodo = SemesterHelper::getCurrentPeriod();

            // Buscar el módulo en la base de datos para obtener el ID
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
                // Optimizar consulta de planificaciones
                $planificacionesActivas = Planificacion_Asignatura::select([
                    'id_planificacion',
                    'id_espacio',
                    'id_modulo',
                    'id_asignatura'
                ])
                ->with([
                    'asignatura:id_asignatura,codigo_asignatura,nombre_asignatura,seccion,id_profesor',
                    'asignatura.profesor:id,name'
                ])
                ->where('id_modulo', $moduloDB->id_modulo)
                ->whereHas('horario', function($q) use ($periodo) {
                    $q->where('periodo', $periodo);
                })
                ->get()
                ->keyBy('id_espacio');
            } else {
                $planificacionesActivas = collect();
            }

            // Optimizar consultas de reservas
            $reservasSolicitantes = Reserva::select([
                'id_reserva',
                'id_espacio',
                'run_solicitante',
                'hora',
                'hora_salida'
            ])
            ->with(['solicitante:id,nombre,tipo_solicitante'])
            ->where('fecha_reserva', Carbon::now()->toDateString())
            ->where('estado', 'activa')
            ->whereNotNull('run_solicitante')
            ->get()
            ->keyBy('id_espacio');

            $reservasProfesores = Reserva::select([
                'id_reserva',
                'id_espacio',
                'run_profesor',
                'hora',
                'hora_salida'
            ])
            ->with(['profesor:id,name'])
            ->where('fecha_reserva', Carbon::now()->toDateString())
            ->where('estado', 'activa')
            ->whereNotNull('run_profesor')
            ->get()
            ->keyBy('id_espacio');

            // Procesar espacios por piso de manera más eficiente
            $this->espacios = [];
            foreach ($this->pisos as $piso) {
                $espaciosPiso = [];
                foreach ($piso->espacios as $espacio) {
                    // Usar keyBy para acceso más rápido
                    $planificacionActiva = $planificacionesActivas->get($espacio->id_espacio);
                    $reservaSolicitante = $reservasSolicitantes->get($espacio->id_espacio);
                    $reservaProfesor = $reservasProfesores->get($espacio->id_espacio);
                    
                    $estado = $espacio->estado ?? 'Disponible';
                    $tieneClase = !is_null($planificacionActiva);
                    $tieneReservaSolicitante = !is_null($reservaSolicitante);
                    $tieneReservaProfesor = !is_null($reservaProfesor);
                    
                    $datosClase = null;
                    $datosSolicitante = null;
                    $datosProfesor = null;

                    if ($planificacionActiva) {
                        $datosClase = [
                            'codigo_asignatura' => $planificacionActiva->asignatura->codigo_asignatura ?? '-',
                            'nombre_asignatura' => $planificacionActiva->asignatura->nombre_asignatura ?? '-',
                            'seccion' => $planificacionActiva->asignatura->seccion ?? '-',
                            'profesor' => [
                                'name' => $planificacionActiva->asignatura->profesor->name ?? '-'
                            ]
                        ];
                    }

                    if ($reservaSolicitante) {
                        $datosSolicitante = [
                            'nombre' => $reservaSolicitante->solicitante->nombre ?? '-',
                            'run' => $reservaSolicitante->run_solicitante ?? '-',
                            'tipo_solicitante' => $reservaSolicitante->solicitante->tipo_solicitante ?? '-',
                            'hora_inicio' => $reservaSolicitante->hora ?? '-',
                            'hora_salida' => $reservaSolicitante->hora_salida ?? '-'
                        ];
                    }

                    if ($reservaProfesor) {
                        $datosProfesor = [
                            'nombre' => $reservaProfesor->profesor->name ?? '-',
                            'run' => $reservaProfesor->run_profesor ?? '-',
                            'hora_inicio' => $reservaProfesor->hora ?? '-',
                            'hora_salida' => $reservaProfesor->hora_salida ?? '-'
                        ];
                    }

                    $espaciosPiso[] = [
                        'id_espacio' => $espacio->id_espacio,
                        'nombre_espacio' => $espacio->nombre_espacio,
                        'estado' => $estado,
                        'tipo_espacio' => $espacio->tipo_espacio,
                        'puestos_disponibles' => $espacio->puestos_disponibles,
                        'tiene_clase' => $tieneClase,
                        'tiene_reserva_solicitante' => $tieneReservaSolicitante,
                        'tiene_reserva_profesor' => $tieneReservaProfesor,
                        'datos_clase' => $datosClase,
                        'datos_solicitante' => $datosSolicitante,
                        'datos_profesor' => $datosProfesor,
                        'modulo' => [
                            'numero' => $this->moduloActual['numero'],
                            'inicio' => $this->moduloActual['inicio'],
                            'fin' => $this->moduloActual['fin']
                        ],
                        'piso' => $piso->nombre_piso,
                        'proxima_clase' => null
                    ];
                }
                $this->espacios[$piso->id] = $espaciosPiso;
            }
        } else {
            // Procesar espacios cuando no hay módulo activo (más eficiente)
            $this->espacios = [];
            foreach ($this->pisos as $piso) {
                $espaciosPiso = [];
                foreach ($piso->espacios as $espacio) {
                    $espaciosPiso[] = [
                        'id_espacio' => $espacio->id_espacio,
                        'nombre_espacio' => $espacio->nombre_espacio,
                        'estado' => 'Disponible',
                        'tipo_espacio' => $espacio->tipo_espacio,
                        'puestos_disponibles' => $espacio->puestos_disponibles,
                        'tiene_clase' => false,
                        'tiene_reserva_solicitante' => false,
                        'datos_clase' => null,
                        'datos_solicitante' => null,
                        'modulo' => null,
                        'piso' => $piso->nombre_piso,
                        'proxima_clase' => null
                    ];
                }
                $this->espacios[$piso->id] = $espaciosPiso;
            }
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
        } elseif (strtolower($estado) === 'proximo' || $estado === 'Proximo' || strtolower($estado) === 'próximo') {
            return 'bg-blue-500';
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
        $this->actualizarDatos();
    }

    public function getModuloActual()
    {
        if ($this->moduloActual) {
            return $this->moduloActual['numero'] ?? 'N/A';
        }
        return null;
    }
} 