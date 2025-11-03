<?php

namespace App\Livewire;

use App\Models\AreaAcademica;
use Livewire\Component;
use Livewire\WithPagination;

class EscuelasTable extends Component
{
    use WithPagination;

    public $search = '';
    public $sortField = 'nombre_area_academica';
    public $sortDirection = 'asc';
    public $perPage = 10;

    protected $queryString = [
        'search' => ['except' => ''],
        'sortField' => ['except' => 'nombre_area_academica'],
        'sortDirection' => ['except' => 'asc'],
    ];

    public function updatingSearch()
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

    public function render()
    {
        $escuelas = AreaAcademica::query()
            ->where('tipo_area_academica', 'escuela')
            ->with(['facultad.sede.universidad', 'carreras'])
            ->where(function ($query) {
                $query->where('id_area_academica', 'like', '%' . $this->search . '%')
                    ->orWhere('nombre_area_academica', 'like', '%' . $this->search . '%')
                    ->orWhereHas('facultad', function ($q) {
                        $q->where('nombre_facultad', 'like', '%' . $this->search . '%');
                    });
            })
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        return view('livewire.escuelas-table', compact('escuelas'));
    }
}
