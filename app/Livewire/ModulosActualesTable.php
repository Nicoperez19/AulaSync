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
