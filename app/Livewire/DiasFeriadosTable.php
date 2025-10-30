<?php

namespace App\Livewire;

use App\Models\DiaFeriado;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class DiasFeriadosTable extends Component
{
    use WithPagination;

    public $search = '';

    public $tipo = '';

    public $sortField = 'fecha_inicio';

    public $sortDirection = 'desc';

    // Propiedades para el modal de crear/editar
    public $showModal = false;

    public $editMode = false;

    public $feriadoId;

    public $fecha_inicio = '';

    public $fecha_fin = '';

    public $nombre = '';

    public $descripcion = '';

    public $tipo_feriado = 'feriado';

    public $activo = true;

    protected $queryString = ['search', 'tipo'];

    protected function rules()
    {
        return [
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date|after_or_equal:fecha_inicio',
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'tipo_feriado' => 'required|in:feriado,semana_reajuste,suspension_actividades',
            'activo' => 'boolean',
        ];
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingTipo()
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
        $this->reset(['fecha_inicio', 'fecha_fin', 'nombre', 'descripcion', 'tipo_feriado', 'activo', 'editMode', 'feriadoId']);
        $this->tipo_feriado = 'feriado';
        $this->activo = true;
        $this->showModal = true;
    }

    public function openEditModal($id)
    {
        $feriado = DiaFeriado::findOrFail($id);
        $this->feriadoId = $id;
        $this->fecha_inicio = $feriado->fecha_inicio->format('Y-m-d');
        $this->fecha_fin = $feriado->fecha_fin->format('Y-m-d');
        $this->nombre = $feriado->nombre;
        $this->descripcion = $feriado->descripcion;
        $this->tipo_feriado = $feriado->tipo;
        $this->activo = $feriado->activo;
        $this->editMode = true;
        $this->showModal = true;
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->reset(['fecha_inicio', 'fecha_fin', 'nombre', 'descripcion', 'tipo_feriado', 'activo', 'editMode', 'feriadoId']);
    }

    public function save()
    {
        $this->validate();

        $data = [
            'fecha_inicio' => $this->fecha_inicio,
            'fecha_fin' => $this->fecha_fin,
            'nombre' => $this->nombre,
            'descripcion' => $this->descripcion,
            'tipo' => $this->tipo_feriado,
            'activo' => $this->activo,
            'created_by' => Auth::user()->run ?? null,
        ];

        if ($this->editMode) {
            $feriado = DiaFeriado::findOrFail($this->feriadoId);
            $feriado->update($data);
            session()->flash('message', 'Día feriado actualizado exitosamente.');
        } else {
            DiaFeriado::create($data);
            session()->flash('message', 'Día feriado creado exitosamente.');
        }

        $this->closeModal();
    }

    public function delete($id)
    {
        $feriado = DiaFeriado::findOrFail($id);
        $feriado->delete();
        session()->flash('message', 'Día feriado eliminado exitosamente.');
    }

    public function toggleActivo($id)
    {
        $feriado = DiaFeriado::findOrFail($id);
        $feriado->update(['activo' => ! $feriado->activo]);
        session()->flash('message', 'Estado actualizado exitosamente.');
    }

    public function render()
    {
        $query = DiaFeriado::query();

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('nombre', 'like', '%'.$this->search.'%')
                    ->orWhere('descripcion', 'like', '%'.$this->search.'%');
            });
        }

        if ($this->tipo) {
            $query->where('tipo', $this->tipo);
        }

        $feriados = $query->orderBy($this->sortField, $this->sortDirection)
            ->paginate(10);

        return view('livewire.dias-feriados-table', [
            'feriados' => $feriados,
        ]);
    }
}
