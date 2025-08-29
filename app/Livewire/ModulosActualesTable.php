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
    public $slideCarrusel = 'ocupados';
    public $planificaciones = [];
    public $espacios = [];
    public $pisos = [];
    public $horaActual;
    public $fechaActual;
    public $moduloActual;
    public $selectedPiso = null;

    public $indiceCarrusel = 0;
    public $itemsPorPagina = 5;

    public $todosLosEspacios = [];

    public function mount()
    {
        $this->actualizarDatos();
        
        if ($this->pisos->count() > 0) {
            $this->selectedPiso = $this->pisos->first()->id;
        }
    }

    public function actualizarDatos()
    {
        $this->horaActual = Carbon::now()->format('H:i:s');
        $this->fechaActual = Carbon::now()->locale('es')->isoFormat('dddd, D [de] MMMM [de] YYYY');
        $hoy = Carbon::now()->locale('es')->isoFormat('dddd');

        $this->moduloActual = Modulo::where('dia', $hoy)
            ->where('hora_inicio', '<=', $this->horaActual)
            ->where('hora_termino', '>', $this->horaActual)
            ->first();

        $this->pisos = Piso::with(['espacios'])->get();

        // Preparar todos los espacios
        $this->todosLosEspacios = [];
        if ($this->moduloActual) {
            foreach ($this->pisos as $piso) {
                foreach ($piso->espacios as $espacio) {
                    $estado = $espacio->estado ?? 'Disponible';
                    $tieneClase = false;
                    $tieneReservaSolicitante = false;
                    $datosClase = null;
                    $datosSolicitante = null;
                    $datosProfesor = null;
                    $tieneReservaProfesor = false;

                    $planificacionActiva = Planificacion_Asignatura::with(['asignatura.profesor'])
                        ->where('id_modulo', $this->moduloActual->id_modulo)
                        ->where('id_espacio', $espacio->id_espacio)
                        ->first();

                    if ($planificacionActiva) {
                        $tieneClase = true;
                        $datosClase = [
                            'codigo_asignatura' => $planificacionActiva->asignatura->codigo_asignatura ?? '-',
                            'nombre_asignatura' => $planificacionActiva->asignatura->nombre_asignatura ?? '-',
                            'seccion' => $planificacionActiva->asignatura->seccion ?? '-',
                            'profesor' => ['name' => $planificacionActiva->asignatura->profesor->name ?? '-']
                        ];
                    }

                    $reservaSolicitante = Reserva::with(['solicitante'])
                        ->where('fecha_reserva', Carbon::now()->toDateString())
                        ->where('estado', 'activa')
                        ->where('id_espacio', $espacio->id_espacio)
                        ->whereNotNull('run_solicitante')
                        ->first();

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

                    // Si tienes lógica para reservaProfesor, agrégala aquí
                    // Ejemplo:
                    // $reservaProfesor = ...;
                    // if ($reservaProfesor) { ... }

                    $this->todosLosEspacios[] = [
                        'id_espacio' => $espacio->id_espacio,
                        'nombre_espacio' => $espacio->nombre_espacio,
                        'estado' => $estado,
                        'tipo_espacio' => $espacio->tipo_espacio,
                        'puestos_disponibles' => $espacio->puestos_disponibles,
                        'tiene_clase' => $tieneClase,
                        'tiene_reserva_solicitante' => $tieneReservaSolicitante,
                        'datos_clase' => $datosClase,
                        'datos_solicitante' => $datosSolicitante,
                        'piso' => $piso->numero_piso,
                        'modulo' => $this->moduloActual ? [
                            'id' => $this->moduloActual->id,
                            'hora_inicio' => $this->moduloActual->hora_inicio,
                            'hora_termino' => $this->moduloActual->hora_termino
                        ] : null,
                    ];
                }
            }
        } else {
            // Procesar espacios cuando no hay módulo activo (entre módulos)
            foreach ($this->pisos as $piso) {
                foreach ($piso->espacios as $espacio) {
                    $estado = $espacio->estado ?? 'Disponible';
                    $tieneClase = false;
                    $tieneReservaSolicitante = false;
                    $datosClase = null;
                    $datosSolicitante = null;
                    $proximaClase = null;

                    // Buscar próxima clase (siguiente módulo)
                    $proximoModulo = Modulo::where('dia', Carbon::now()->locale('es')->isoFormat('dddd'))
                        ->where('hora_inicio', '>', $this->horaActual)
                        ->orderBy('hora_inicio', 'asc')
                        ->first();

                    if ($proximoModulo) {
                        $planificacionProxima = Planificacion_Asignatura::with(['asignatura.profesor'])
                            ->where('id_modulo', $proximoModulo->id_modulo)
                            ->where('id_espacio', $espacio->id_espacio)
                            ->first();

                        if ($planificacionProxima) {
                            $tieneClase = true;
                            $datosClase = [
                                'codigo_asignatura' => $planificacionProxima->asignatura->codigo_asignatura ?? '-',
                                'nombre_asignatura' => $planificacionProxima->asignatura->nombre_asignatura ?? '-',
                                'seccion' => $planificacionProxima->asignatura->seccion ?? '-',
                                'profesor' => ['name' => $planificacionProxima->asignatura->profesor->name ?? '-'],
                                'es_proxima' => true,
                                'hora_inicio' => $proximoModulo->hora_inicio,
                                'hora_termino' => $proximoModulo->hora_termino
                            ];
                        }
                    }

                    // NO buscar reservas de solicitantes cuando estamos entre módulos
                    // Solo mantenemos ocupados y próximas clases

                    $this->todosLosEspacios[] = [
                        'id_espacio' => $espacio->id_espacio,
                        'nombre_espacio' => $espacio->nombre_espacio,
                        'estado' => $estado,
                        'tipo_espacio' => $espacio->tipo_espacio,
                        'puestos_disponibles' => $espacio->puestos_disponibles,
                        'tiene_clase' => $tieneClase,
                        'tiene_reserva_solicitante' => false, // No mostrar reservados entre módulos
                        'datos_clase' => $datosClase,
                        'datos_solicitante' => null,
                        'piso' => $piso->numero_piso,
                        'modulo' => null,
                        'es_entre_modulos' => true
                    ];
                }
            }
        }
    }

    public function getEspaciosCarruselProperty()
    {
        return array_slice($this->todosLosEspacios, $this->indiceCarrusel, $this->itemsPorPagina);
    }

    public function avanzarCarrusel()
    {
        $total = count($this->todosLosEspacios);
        $this->indiceCarrusel = ($this->indiceCarrusel + $this->itemsPorPagina) % max($total, 1);
    }

    public function getPrimerApellido($nombreCompleto)
    {
        $apellidos = explode(',', $nombreCompleto);
        return trim($apellidos[0] ?? '');
    }

    public function getPrimerApellidoSolicitante($nombreCompleto)
    {
        $apellidos = explode(',', $nombreCompleto);
        return trim($apellidos[0] ?? '');
    }

    public function actualizarAutomaticamente()
    {
        $this->actualizarDatos();
    }

    public function render()
    {
        return view('livewire.modulos-actuales-table');
    }

}
