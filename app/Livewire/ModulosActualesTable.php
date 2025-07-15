<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Planificacion_Asignatura;
use Carbon\Carbon;

class ModulosActualesTable extends Component
{
    public $planificaciones = [];
    public $horaActual;
    public $fechaActual;
    public $moduloActual;

    public function mount()
    {
        $this->actualizarDatos();
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

        if ($this->moduloActual) {
            // Determinar el período actual
            $mesActual = date('n');
            $anioActual = date('Y');
            $semestre = ($mesActual >= 1 && $mesActual <= 7) ? 1 : 2;
            $periodo = $anioActual . '-' . $semestre;

            $this->planificaciones = Planificacion_Asignatura::with([
                'asignatura.profesor',
                'espacio',
                'modulo'
            ])
            ->where('id_modulo', $this->moduloActual->id_modulo)
            ->whereHas('horario', function($q) use ($periodo) {
                $q->where('periodo', $periodo);
            })
            ->get()
            ->map(function ($planificacion) {
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
        }
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
} 