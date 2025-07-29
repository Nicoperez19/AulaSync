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

            // Procesar espacios por piso
            $this->espacios = [];
            foreach ($this->pisos as $piso) {
                $espaciosPiso = [];
                foreach ($piso->espacios as $espacio) {
                    // Buscar si el espacio tiene una planificación activa
                    $planificacionActiva = $planificacionesActivas->where('id_espacio', $espacio->id_espacio)->first();
                    
                    $estado = $espacio->estado ?? 'Disponible';
                    $tieneClase = false;
                    $datosClase = null;

                    if ($planificacionActiva) {
                        $tieneClase = true;
                        // No cambiar el estado, mantener el original de la BD
                        $datosClase = [
                            'codigo_asignatura' => $planificacionActiva->asignatura->codigo_asignatura ?? '-',
                            'nombre_asignatura' => $planificacionActiva->asignatura->nombre_asignatura ?? '-',
                            'seccion' => $planificacionActiva->asignatura->seccion ?? '-',
                            'profesor' => [
                                'name' => $planificacionActiva->asignatura->profesor->name ?? '-'
                            ]
                        ];
                    }

                    $espaciosPiso[] = [
                        'id_espacio' => $espacio->id_espacio,
                        'nombre_espacio' => $espacio->nombre_espacio,
                        'estado' => $estado, // Mantener el estado original de la BD
                        'tipo_espacio' => $espacio->tipo_espacio,
                        'puestos_disponibles' => $espacio->puestos_disponibles,
                        'tiene_clase' => $tieneClase,
                        'datos_clase' => $datosClase,
                        'modulo' => [
                            'id_modulo' => $this->moduloActual->id_modulo,
                            'numero_modulo' => explode('.', $this->moduloActual->id_modulo)[1] ?? 'N/A',
                            'hora_inicio' => substr($this->moduloActual->hora_inicio, 0, 5),
                            'hora_termino' => substr($this->moduloActual->hora_termino, 0, 5),
                        ]
                    ];
                }
                $this->espacios[$piso->id] = $espaciosPiso;
            }

            // Establecer el primer piso como seleccionado por defecto
            if ($this->pisos->count() > 0 && $this->selectedPiso === null) {
                $this->selectedPiso = $this->pisos->first()->id;
            }

            // Mantener las planificaciones para compatibilidad
            $this->planificaciones = $planificacionesActivas->map(function ($planificacion) {
                return [
                    'id' => $planificacion->id,
                    'modulo' => [
                        'id_modulo' => $planificacion->modulo->id_modulo,
                        'numero_modulo' => explode('.', $planificacion->modulo->id_modulo)[1] ?? 'N/A',
                        'hora_inicio' => substr($planificacion->modulo->hora_inicio, 0, 5),
                        'hora_termino' => substr($planificacion->modulo->hora_termino, 0, 5),
                    ],
                    'asignatura' => [
                        'codigo_asignatura' => $planificacion->asignatura->codigo_asignatura ?? '-',
                        'nombre_asignatura' => $planificacion->asignatura->nombre_asignatura ?? '-',
                        'seccion' => $planificacion->asignatura->seccion ?? '-',
                        'profesor' => [
                            'name' => $planificacion->asignatura->profesor->name ?? '-'
                        ]
                    ],
                    'espacio' => [
                        'id_espacio' => $planificacion->espacio->id_espacio ?? '-',
                        'nombre_espacio' => $planificacion->espacio->nombre_espacio ?? '-',
                        'estado' => $planificacion->espacio->estado ?? 'Disponible'
                    ]
                ];
            });
        } else {
            $this->planificaciones = [];
            $this->espacios = [];
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