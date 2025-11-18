<?php

namespace App\Livewire;

use App\Models\RecuperacionClase;
use App\Models\Profesor;
use App\Models\Modulo;
use App\Models\Espacio;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use App\Mail\NotificacionRecuperacionClase;

class RecuperacionClasesTable extends Component
{
    use WithPagination;

    public $search = '';
    public $estado = '';
    public $sortField = 'fecha_clase_original';
    public $sortDirection = 'desc';
    
    // Propiedades para el modal de reagendar
    public $showReagendarModal = false;
    public $recuperacionId;
    public $fecha_reagendada = '';
    public $id_modulo_reagendado = '';
    public $id_espacio_reagendado = '';
    public $notas = '';

    protected $queryString = ['search', 'estado'];

    protected function rulesReagendar()
    {
        return [
            'fecha_reagendada' => 'required|date|after_or_equal:today',
            'id_modulo_reagendado' => 'required|exists:modulos,id_modulo',
            'id_espacio_reagendado' => 'nullable|exists:espacios,id_espacio',
            'notas' => 'nullable|string',
        ];
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingEstado()
    {
        $this->resetPage();
    }

    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
    }

    public function openReagendarModal($id)
    {
        $recuperacion = RecuperacionClase::findOrFail($id);
        $this->recuperacionId = $id;
        $this->fecha_reagendada = $recuperacion->fecha_reagendada ? $recuperacion->fecha_reagendada->format('Y-m-d') : '';
        $this->id_modulo_reagendado = $recuperacion->id_modulo_reagendado ?? '';
        $this->id_espacio_reagendado = $recuperacion->id_espacio_reagendado ?? $recuperacion->id_espacio;
        $this->notas = $recuperacion->notas ?? '';
        $this->showReagendarModal = true;
    }

    public function closeReagendarModal()
    {
        $this->showReagendarModal = false;
        $this->reset(['recuperacionId', 'fecha_reagendada', 'id_modulo_reagendado', 'id_espacio_reagendado', 'notas']);
    }

    public function reagendar()
    {
        $this->validate($this->rulesReagendar());

        $recuperacion = RecuperacionClase::findOrFail($this->recuperacionId);
        $recuperacion->update([
            'fecha_reagendada' => $this->fecha_reagendada,
            'id_modulo_reagendado' => $this->id_modulo_reagendado,
            'id_espacio_reagendado' => $this->id_espacio_reagendado,
            'notas' => $this->notas,
            'estado' => 'reagendada',
            'gestionado_por' => Auth::user()->run,
        ]);

        // Crear notificación de clase reagendada
        \App\Models\Notificacion::crearNotificacionClaseReagendada($recuperacion);

        session()->flash('message', 'Clase reagendada exitosamente.');
        $this->closeReagendarModal();
    }

    public function notificar($id)
    {
        $recuperacion = RecuperacionClase::with(['profesor', 'asignatura', 'moduloOriginal', 'moduloReagendado', 'espacio', 'espacioReagendado'])
            ->findOrFail($id);

        try {
            // Enviar correo al profesor
            if ($recuperacion->profesor && $recuperacion->profesor->email) {
                Mail::to($recuperacion->profesor->email)
                    ->send(new NotificacionRecuperacionClase($recuperacion));
                
                $recuperacion->marcarComoNotificado();
                
                // Mensaje de éxito
                $this->dispatch('notify', [
                    'type' => 'success',
                    'message' => 'Correo enviado exitosamente a ' . $recuperacion->profesor->email
                ]);
                
                session()->flash('message', 'Notificación enviada exitosamente.');
            } else {
                // Mensaje de error
                $this->dispatch('notify', [
                    'type' => 'error',
                    'message' => 'El profesor no tiene email registrado'
                ]);
                
                session()->flash('error', 'El profesor no tiene email registrado.');
            }
        } catch (\Exception $e) {
            // Mensaje de error
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Error al enviar: ' . $e->getMessage()
            ]);
            
            session()->flash('error', 'Error al enviar notificación: ' . $e->getMessage());
        }
    }

    public function obviar($id)
    {
        $recuperacion = RecuperacionClase::findOrFail($id);
        $recuperacion->update([
            'estado' => 'obviada',
            'gestionado_por' => Auth::user()->run,
        ]);
        session()->flash('message', 'Reagendamiento obviado.');
    }

    public function marcarRealizada($id)
    {
        $recuperacion = RecuperacionClase::findOrFail($id);
        $recuperacion->update([
            'estado' => 'realizada',
            'gestionado_por' => Auth::user()->run,
        ]);
        session()->flash('message', 'Clase marcada como realizada.');
    }

    public function render()
    {
        $query = RecuperacionClase::with(['profesor', 'asignatura', 'licencia', 'moduloOriginal', 'espacioReagendado'])
            ->when($this->search, function ($q) {
                $q->whereHas('profesor', function ($query) {
                    $query->where('name', 'like', '%' . $this->search . '%')
                          ->orWhere('run_profesor', 'like', '%' . $this->search . '%');
                })->orWhereHas('asignatura', function ($query) {
                    $query->where('nombre_asignatura', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->estado, function ($q) {
                $q->where('estado', $this->estado);
            })
            ->orderBy($this->sortField, $this->sortDirection);

        $recuperaciones = $query->paginate(15);
        $modulos = Modulo::orderBy('id_modulo')->get();
        $espacios = Espacio::orderBy('nombre_espacio')->get();

        return view('livewire.recuperacion-clases-table', [
            'recuperaciones' => $recuperaciones,
            'modulos' => $modulos,
            'espacios' => $espacios,
        ]);
    }
}
