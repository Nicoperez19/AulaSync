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
        $profesores = Profesor::query()
            ->with('carrera')
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('run_profesor', 'like', '%' . $this->search . '%')
                      ->orWhere('name', 'like', '%' . $this->search . '%')
                      ->orWhere('email', 'like', '%' . $this->search . '%')
                      ->orWhere('tipo_profesor', 'like', '%' . $this->search . '%')
                      ->orWhereHas('carrera', function ($subQuery) {
                          $subQuery->where('nombre', 'like', '%' . $this->search . '%');
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