<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Carrera;
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

        $sortField = in_array($this->sortField, ['id_asignatura', 'codigo_asignatura', 'nombre_asignatura', 'seccion'], true)
            ? $this->sortField
            : 'id_asignatura';
        $sortDirection = $this->sortDirection === 'desc' ? 'desc' : 'asc';

        $asignaturas = Asignatura::with(['profesor', 'carrera'])
            ->when($searchTerm !== '', function ($query) use ($searchTerm) {
                // Las carreras viven en la base central: se resuelven sus IDs aparte
                $carreraIds = Carrera::query()
                    ->where('nombre', 'like', '%' . $searchTerm . '%')
                    ->pluck('id_carrera')
                    ->all();

                $cleanRun = preg_replace('/[^0-9Kk]/', '', $searchTerm);

                $query->where(function ($q) use ($searchTerm, $carreraIds, $cleanRun) {
                    $q->where('id_asignatura', 'like', '%' . $searchTerm . '%')
                      ->orWhere('codigo_asignatura', 'like', '%' . $searchTerm . '%')
                      ->orWhere('nombre_asignatura', 'like', '%' . $searchTerm . '%')
                      ->orWhere('seccion', 'like', '%' . $searchTerm . '%')
                      ->orWhere('periodo', 'like', '%' . $searchTerm . '%')
                      ->orWhere('area_conocimiento', 'like', '%' . $searchTerm . '%')
                      ->orWhereHas('profesor', function ($pq) use ($searchTerm) {
                          $pq->where('name', 'like', '%' . $searchTerm . '%');
                      });

                    if ($cleanRun !== '') {
                        $q->orWhere('run_profesor', 'like', '%' . $cleanRun . '%');
                    }

                    if (!empty($carreraIds)) {
                        $q->orWhereIn('id_carrera', $carreraIds);
                    }
                });
            })
            ->orderBy($sortField, $sortDirection)
            ->paginate($this->perPage);

        return view('livewire.subjects-table', compact('asignaturas'));
    }
}
