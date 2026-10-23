<?php

namespace App\Livewire;

use App\Models\Profesor;
use Livewire\Component;
use Livewire\WithPagination;
use App\Models\AreaAcademica;
use App\Models\Carrera;
use App\Models\Facultad;
use App\Models\Universidad;

class ProfessorsTable extends Component
{
    use WithPagination;

    public $search = '';
    public $sortField = 'run_profesor';
    public $sortDirection = 'asc';

    protected $queryString = [
        'search' => ['except' => ''],
        'sortField' => ['except' => 'run_profesor'],
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
        $centralDb = config('database.connections.mysql.database');

        $sortField = in_array($this->sortField, ['run_profesor', 'name', 'email', 'tipo_profesor', 'carrera'], true)
            ? $this->sortField
            : 'run_profesor';
        $sortDirection = $this->sortDirection === 'desc' ? 'desc' : 'asc';

        $profesores = Profesor::query()
            ->with(['carrera', 'universidad', 'facultad', 'areaAcademica'])
            ->when($searchTerm !== '', function ($query) use ($searchTerm) {
                $cleanRun = preg_replace('/[^0-9Kk]/', '', $searchTerm);

                // Universidad, facultad, carrera y área académica viven en la base central:
                // se resuelven sus IDs aparte para no cruzar bases de datos en un whereHas.
                $universidadIds = Universidad::where('nombre_universidad', 'like', '%' . $searchTerm . '%')
                    ->pluck('id_universidad')->all();
                $facultadIds = Facultad::where('nombre_facultad', 'like', '%' . $searchTerm . '%')
                    ->pluck('id_facultad')->all();
                $carreraIds = Carrera::where('nombre', 'like', '%' . $searchTerm . '%')
                    ->pluck('id_carrera')->all();
                $areaIds = AreaAcademica::where('nombre_area_academica', 'like', '%' . $searchTerm . '%')
                    ->pluck('id_area_academica')->all();

                $query->where(function ($q) use ($searchTerm, $cleanRun, $universidadIds, $facultadIds, $carreraIds, $areaIds) {
                    $q->where('name', 'like', '%' . $searchTerm . '%')
                      ->orWhere('email', 'like', '%' . $searchTerm . '%')
                      ->orWhere('tipo_profesor', 'like', '%' . $searchTerm . '%');

                    if ($cleanRun !== '') {
                        $q->orWhere('run_profesor', 'like', '%' . $cleanRun . '%');
                    }
                    if (!empty($universidadIds)) {
                        $q->orWhereIn('id_universidad', $universidadIds);
                    }
                    if (!empty($facultadIds)) {
                        $q->orWhereIn('id_facultad', $facultadIds);
                    }
                    if (!empty($carreraIds)) {
                        $q->orWhereIn('id_carrera', $carreraIds);
                    }
                    if (!empty($areaIds)) {
                        $q->orWhereIn('id_area_academica', $areaIds);
                    }
                });
            })
            ->when($sortField === 'carrera', function ($query) use ($centralDb, $sortDirection) {
                // La tabla carreras está en la base central: se ordena con una subconsulta calificada
                $query->orderByRaw(
                    "(select c.nombre from `{$centralDb}`.`carreras` c where c.id_carrera = profesors.id_carrera limit 1) {$sortDirection}"
                );
            }, function ($query) use ($sortField, $sortDirection) {
                $query->orderBy($sortField, $sortDirection);
            })
            ->paginate(10);

        return view('livewire.professors-table', compact('profesores'));
    }
}
