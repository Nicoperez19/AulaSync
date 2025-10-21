<?php

namespace App\Livewire;

use App\Models\LicenciaProfesor;
use App\Models\Profesor;
use App\Services\LicenciaRecuperacionService;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;

class LicenciasProfesoresTable extends Component
{
    use WithPagination;

    public $search = '';
    public $estado = '';
    public $sortField = 'fecha_inicio';
    public $sortDirection = 'desc';
    
    // Propiedades para el modal de crear/editar
    public $showModal = false;
    public $editMode = false;
    public $licenciaId;
    public $run_profesor = '';
    public $fecha_inicio = '';
    public $fecha_fin = '';
    public $motivo = '';
    public $observaciones = '';
    public $genera_recuperacion = true;

    protected $queryString = ['search', 'estado'];

    protected function rules()
    {
        return [
            'run_profesor' => 'required|exists:profesors,run_profesor',
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date|after_or_equal:fecha_inicio',
            'motivo' => 'nullable|string|max:255',
            'observaciones' => 'nullable|string',
            'genera_recuperacion' => 'boolean',
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

    public function openCreateModal()
    {
        $this->reset(['run_profesor', 'fecha_inicio', 'fecha_fin', 'motivo', 'observaciones', 'genera_recuperacion', 'editMode', 'licenciaId']);
        $this->genera_recuperacion = true;
        $this->showModal = true;
    }

    public function openEditModal($id)
    {
        $licencia = LicenciaProfesor::findOrFail($id);
        $this->licenciaId = $id;
        $this->run_profesor = $licencia->run_profesor;
        $this->fecha_inicio = $licencia->fecha_inicio->format('Y-m-d');
        $this->fecha_fin = $licencia->fecha_fin->format('Y-m-d');
        $this->motivo = $licencia->motivo;
        $this->observaciones = $licencia->observaciones;
        $this->genera_recuperacion = $licencia->genera_recuperacion;
        $this->editMode = true;
        $this->showModal = true;
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->reset(['run_profesor', 'fecha_inicio', 'fecha_fin', 'motivo', 'observaciones', 'genera_recuperacion', 'editMode', 'licenciaId']);
    }

    public function save()
    {
        $this->validate();

        $data = [
            'run_profesor' => $this->run_profesor,
            'fecha_inicio' => $this->fecha_inicio,
            'fecha_fin' => $this->fecha_fin,
            'motivo' => $this->motivo,
            'observaciones' => $this->observaciones,
            'genera_recuperacion' => $this->genera_recuperacion,
        ];

        $service = new LicenciaRecuperacionService();

        if ($this->editMode) {
            $licencia = LicenciaProfesor::findOrFail($this->licenciaId);
            $licencia->update($data);
            
            // Regenerar clases a recuperar si está habilitado
            if ($licencia->genera_recuperacion) {
                $clasesGeneradas = $service->regenerarClasesARecuperar($licencia);
                session()->flash('message', "Licencia actualizada. {$clasesGeneradas} clases programadas para recuperar.");
            } else {
                // Si se desactiva, eliminar las clases pendientes
                $service->eliminarClasesARecuperar($licencia);
                session()->flash('message', 'Licencia actualizada exitosamente.');
            }
        } else {
            $data['created_by'] = Auth::user()->run;
            $data['estado'] = 'activa';
            $licencia = LicenciaProfesor::create($data);
            
            // El Observer se encargará de generar las clases automáticamente
            session()->flash('message', 'Licencia creada exitosamente.');
        }

        $this->closeModal();
    }

    public function cambiarEstado($id, $nuevoEstado)
    {
        $licencia = LicenciaProfesor::findOrFail($id);
        $licencia->update(['estado' => $nuevoEstado]);
        session()->flash('message', 'Estado de licencia actualizado.');
    }

    public function delete($id)
    {
        $licencia = LicenciaProfesor::findOrFail($id);
        
        // El cascade en la base de datos eliminará automáticamente las recuperaciones asociadas
        $licencia->delete();
        
        session()->flash('message', 'Licencia y clases asociadas eliminadas exitosamente.');
    }

    public function render()
    {
        $query = LicenciaProfesor::with(['profesor', 'creador', 'recuperaciones'])
            ->when($this->search, function ($q) {
                $q->whereHas('profesor', function ($query) {
                    $query->where('name', 'like', '%' . $this->search . '%')
                          ->orWhere('run_profesor', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->estado, function ($q) {
                $q->where('estado', $this->estado);
            })
            ->orderBy($this->sortField, $this->sortDirection);

        $licencias = $query->paginate(15);
        $profesores = Profesor::orderBy('name')->get();

        return view('livewire.licencias-profesores-table', [
            'licencias' => $licencias,
            'profesores' => $profesores,
        ]);
    }
}
