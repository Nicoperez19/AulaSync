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

    public function updatedSearch()
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
        $searchTerm = trim($this->search);

        $asignaturas = Asignatura::with(['profesor', 'carrera'])
            ->when($searchTerm !== '', function ($query) use ($searchTerm) {
                $termSinTilde = str_replace(
                    ['á', 'é', 'í', 'ó', 'ú', 'Á', 'É', 'Í', 'Ó', 'Ú'],
                    ['a', 'e', 'i', 'o', 'u', 'A', 'E', 'I', 'O', 'U'],
                    $searchTerm
                );
                $terms = array_unique(array_filter([$searchTerm, $termSinTilde, mb_strtoupper($searchTerm), mb_strtolower($searchTerm)]));

                $query->where(function($q) use ($terms, $searchTerm) {
                    $q->where('id_asignatura', 'like', '%' . $searchTerm . '%')
                      ->orWhere('codigo_asignatura', 'like', '%' . $searchTerm . '%')
                      ->orWhere('seccion', 'like', '%' . $searchTerm . '%')
                      ->orWhere('periodo', 'like', '%' . $searchTerm . '%');

                    foreach ($terms as $t) {
                        $q->orWhere('nombre_asignatura', 'like', '%' . $t . '%')
                          ->orWhere('area_conocimiento', 'like', '%' . $t . '%')
                          ->orWhereHas('profesor', function($pq) use ($t) {
                              $pq->where('name', 'like', '%' . $t . '%');
                          })
                          ->orWhereHas('carrera', function($cq) use ($t) {
                              $cq->where('nombre', 'like', '%' . $t . '%');
                          });
                    }
                });
            })
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        return view('livewire.subjects-table', compact('asignaturas'));
    }
}
