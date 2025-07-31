<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Asignatura; 

class SubjectsTable extends Component
{
    use WithPagination;

    public $search = '';
    public $sortField = 'id_asignatura';
    public $sortDirection = 'asc';
    public $perPage = 10;

    protected $queryString = [
        'search' => ['except' => ''],
        'sortField' => ['except' => 'id_asignatura'],
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
        $asignaturas = Asignatura::with(['profesor', 'carrera'])
            ->where(function($query) {
                $query->where('nombre_asignatura', 'like', '%' . $this->search . '%')
                      ->orWhere('id_asignatura', 'like', '%' . $this->search . '%')
                      ->orWhere('codigo_asignatura', 'like', '%' . $this->search . '%')
                      ->orWhere('seccion', 'like', '%' . $this->search . '%')
                      ->orWhere('area_conocimiento', 'like', '%' . $this->search . '%')
                      ->orWhere('periodo', 'like', '%' . $this->search . '%')
                      ->orWhereHas('profesor', function($q) {
                          $q->where('name', 'like', '%' . $this->search . '%');
                      })
                      ->orWhereHas('carrera', function($q) {
                          $q->where('nombre', 'like', '%' . $this->search . '%');
                      });
            })
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        return view('livewire.subjects-table', compact('asignaturas'));
    }
}
