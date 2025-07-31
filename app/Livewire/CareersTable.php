<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Carrera;

class CareersTable extends Component
{
    use WithPagination;

    public $search = '';
    public $sortField = 'id_carrera';
    public $sortDirection = 'asc';
    public $perPage = 10;

    protected $queryString = [
        'search' => ['except' => ''],
        'sortField' => ['except' => 'id_carrera'],
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
        $carreras = Carrera::query()
            ->with(['areaAcademica.facultad.sede.universidad'])
            ->where(function ($query) {
                $query->where('id_carrera', 'like', '%' . $this->search . '%')
                    ->orWhere('nombre', 'like', '%' . $this->search . '%')
                    ->orWhereHas('areaAcademica', function ($q) {
                        $q->where('nombre_area_academica', 'like', '%' . $this->search . '%');
                    })
                    ->orWhereHas('areaAcademica.facultad', function ($q) {
                        $q->where('nombre_facultad', 'like', '%' . $this->search . '%');
                    })
                    ->orWhereHas('areaAcademica.facultad.sede', function ($q) {
                        $q->where('nombre_sede', 'like', '%' . $this->search . '%');
                    })
                    ->orWhereHas('areaAcademica.facultad.sede.universidad', function ($q) {
                        $q->where('nombre_universidad', 'like', '%' . $this->search . '%');
                    });
            })
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        return view('livewire.careers-table', compact('carreras'));
    }
}
