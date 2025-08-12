<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Planificacion_Asignatura;
use App\Models\Espacio;
use App\Models\Piso;
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

    public function mount()
    {
        $this->actualizarDatos();
        
        // Establecer el primer piso como seleccionado por defecto
        if ($this->pisos->count() > 0) {
            $this->selectedPiso = $this->pisos->first()->id;
        }
    }

    public function actualizarDatos()
    {
        $this->horaActual = Carbon::now()->format('H:i:s');
        $this->fechaActual = Carbon::now()->locale('es')->isoFormat('dddd, D [de] MMMM [de] YYYY');
        
        $hoy = Carbon::now()->locale('es')->isoFormat('dddd');
        
        // Obtener el módulo actual
        $this->moduloActual = \App\Models\Modulo::where('dia', $hoy)
            ->where('hora_inicio', '<=', $this->horaActual)
            ->where('hora_termino', '>', $this->horaActual)
            ->first();

        // Obtener todos los pisos con sus espacios
        $this->pisos = Piso::with(['espacios'])->get();

        if ($this->moduloActual) {
            // Determinar el período actual usando el helper
            $anioActual = SemesterHelper::getCurrentAcademicYear();
            $semestre = SemesterHelper::getCurrentSemester();
            $periodo = SemesterHelper::getCurrentPeriod();

            // Obtener todas las planificaciones del módulo actual
            $planificacionesActivas = Planificacion_Asignatura::with([
                'asignatura.profesor',
                'espacio',
                'modulo'
            ])
            ->where('id_modulo', $this->moduloActual->id_modulo)
            ->whereHas('horario', function($q) use ($periodo) {
                $q->where('periodo', $periodo);
            })
            ->get();

            // Obtener reservas activas de solicitantes para el día actual
            $reservasSolicitantes = \App\Models\Reserva::with(['solicitante'])
                ->where('fecha_reserva', Carbon::now()->toDateString())
                ->where('estado', 'activa')
                ->whereNotNull('run_solicitante')
                ->get();

            // Procesar espacios por piso
            $this->espacios = [];
            foreach ($this->pisos as $piso) {
                $espaciosPiso = [];
                foreach ($piso->espacios as $espacio) {
                    // Buscar si el espacio tiene una planificación activa
                    $planificacionActiva = $planificacionesActivas->where('id_espacio', $espacio->id_espacio)->first();
                    
                    // Buscar si el espacio tiene una reserva de solicitante
                    $reservaSolicitante = $reservasSolicitantes->where('id_espacio', $espacio->id_espacio)->first();
                    
                    $estado = $espacio->estado ?? 'Disponible';
                    $tieneClase = false;
                    $tieneReservaSolicitante = false;
                    $datosClase = null;
                    $datosSolicitante = null;

                    if ($planificacionActiva) {
                        $tieneClase = true;
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
                        $tieneReservaSolicitante = true;
                        $datosSolicitante = [
                            'nombre' => $reservaSolicitante->solicitante->nombre ?? '-',
                            'run' => $reservaSolicitante->run_solicitante ?? '-',
                            'tipo_solicitante' => $reservaSolicitante->solicitante->tipo_solicitante ?? '-',
                            'hora_inicio' => $reservaSolicitante->hora ?? '-',
                            'hora_salida' => $reservaSolicitante->hora_salida ?? '-'
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
                        'datos_clase' => $datosClase,
                        'datos_solicitante' => $datosSolicitante,
                        'modulo' => [
                            'id' => $this->moduloActual->id,
                            'hora_inicio' => $this->moduloActual->hora_inicio,
                            'hora_termino' => $this->moduloActual->hora_termino
                        ],
                        'piso' => $piso->nombre_piso,
                        'proxima_clase' => null
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
    public function getEstadoColor($estado, $tieneClase, $tieneReservaSolicitante)
    {
        if (strtolower($estado) === 'ocupado' || $estado === 'Ocupado') {
            return 'bg-red-500';
        } elseif (strtolower($estado) === 'reservado' || $estado === 'Reservado') {
            return 'bg-yellow-400';
        } elseif (strtolower($estado) === 'proximo' || $estado === 'Proximo' || strtolower($estado) === 'próximo') {
            return 'bg-blue-500';
        } elseif ($tieneClase || $tieneReservaSolicitante) {
            return 'bg-yellow-400';
        } else {
            return 'bg-green-500';
        }
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
            return $this->moduloActual->numero_modulo ?? 'N/A';
        }
        return null;
    }
} 