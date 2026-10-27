<?php

namespace App\Livewire;

use App\Models\Profesor;
use Livewire\Component;
use Livewire\WithPagination;

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
        $universidadIds = collect();

        $profesores = Profesor::query()
            ->with(['carrera', 'universidad', 'facultad', 'areaAcademica'])
            ->when($searchTerm !== '', function ($query) use ($searchTerm) {
                $cleanRun = preg_replace('/[^0-9Kk]/', '', $searchTerm);
                $termSinTilde = str_replace(
                    ['á', 'é', 'í', 'ó', 'ú', 'Á', 'É', 'Í', 'Ó', 'Ú'],
                    ['a', 'e', 'i', 'o', 'u', 'A', 'E', 'I', 'O', 'U'],
                    $searchTerm
                );
                $terms = array_unique(array_filter([$searchTerm, $termSinTilde, mb_strtoupper($searchTerm), mb_strtolower($searchTerm)]));

                $universidadIds = \App\Models\Universidad::where(function($uq) use ($terms) {
                    foreach ($terms as $t) {
                        $uq->orWhere('nombre_universidad', 'like', '%' . $t . '%');
                    }
                })->pluck('id_universidad');

                $query->where(function ($q) use ($terms, $cleanRun, $universidadIds) {
                    if (!empty($cleanRun)) {
                        $q->where('run_profesor', 'like', '%' . $cleanRun . '%')
                          ->orWhereRaw("REPLACE(REPLACE(run_profesor, '.', ''), '-', '') LIKE ?", ['%' . $cleanRun . '%']);
                    }

                    foreach ($terms as $t) {
                        $q->orWhere('name', 'like', '%' . $t . '%')
                          ->orWhere('email', 'like', '%' . $t . '%')
                          ->orWhere('tipo_profesor', 'like', '%' . $t . '%')
                          ->orWhereHas('carrera', function ($subQuery) use ($t) {
                              $subQuery->where('nombre', 'like', '%' . $t . '%');
                          })
                          ->orWhereHas('facultad', function ($subQuery) use ($t) {
                              $subQuery->where('nombre_facultad', 'like', '%' . $t . '%');
                          })
                          ->orWhereHas('areaAcademica', function ($subQuery) use ($t) {
                              $subQuery->where('nombre_area_academica', 'like', '%' . $t . '%');
                          });
                    }

                    if ($universidadIds->isNotEmpty()) {
                        $q->orWhereIn('id_universidad', $universidadIds);
                    }
                });
            })
            ->when($this->sortField, function ($query) {
                switch ($this->sortField) {
                    case 'run_profesor':
                        $query->orderBy('run_profesor', $this->sortDirection);
                        break;
                    case 'name':
                        $query->orderBy('name', $this->sortDirection);
                        break;
                    case 'email':
                        $query->orderBy('email', $this->sortDirection);
                        break;
                    case 'tipo_profesor':
                        $query->orderBy('tipo_profesor', $this->sortDirection);
                        break;
                    case 'carrera':
                        $query->join('carreras', 'profesors.id_carrera', '=', 'carreras.id_carrera')
                              ->orderBy('carreras.nombre', $this->sortDirection)
                              ->select('profesors.*');
                        break;
                    default:
                        $query->orderBy('run_profesor', 'asc');
                }
            })
            ->paginate(10);

        return view('livewire.professors-table', compact('profesores'));
    }
}
