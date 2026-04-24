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

        if ($searchTerm !== '') {
            $universidadIds = \App\Models\Universidad::where('nombre_universidad', 'like', '%' . $searchTerm . '%')
                ->pluck('id_universidad');
        }

        $profesores = Profesor::query()
            ->with(['carrera', 'universidad', 'facultad', 'areaAcademica'])
            ->when($searchTerm, function ($query) use ($searchTerm, $universidadIds) {
                $query->where(function ($q) use ($searchTerm, $universidadIds) {
                    $q->where('run_profesor', 'like', '%' . $searchTerm . '%')
                      ->orWhere('name', 'like', '%' . $searchTerm . '%')
                      ->orWhere('email', 'like', '%' . $searchTerm . '%')
                      ->orWhere('tipo_profesor', 'like', '%' . $searchTerm . '%')
                      ->orWhereHas('carrera', function ($subQuery) use ($searchTerm) {
                          $subQuery->where('nombre', 'like', '%' . $searchTerm . '%');
                      })
                      ->when($universidadIds->isNotEmpty(), function ($q2) use ($universidadIds) {
                          $q2->orWhereIn('id_universidad', $universidadIds);
                      })
                      ->orWhereHas('facultad', function ($subQuery) use ($searchTerm) {
                          $subQuery->where('nombre_facultad', 'like', '%' . $searchTerm . '%');
                      })
                      ->orWhereHas('areaAcademica', function ($subQuery) use ($searchTerm) {
                          $subQuery->where('nombre_area_academica', 'like', '%' . $searchTerm . '%');
                      });
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
