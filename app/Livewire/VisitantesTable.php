<?php

namespace App\Livewire;

use App\Models\Visitante;
use Livewire\Component;
use Livewire\WithPagination;

class VisitantesTable extends Component
{
    use WithPagination;

    public $search = '';
    public $sortField = 'run_solicitante';
    public $sortDirection = 'asc';

    protected $queryString = [
        'search' => ['except' => ''],
        'sortField' => ['except' => 'run_solicitante'],
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
        $visitantes = Visitante::query()
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('run_solicitante', 'like', '%' . $this->search . '%')
                      ->orWhere('nombre', 'like', '%' . $this->search . '%')
                      ->orWhere('correo', 'like', '%' . $this->search . '%')
                      ->orWhere('telefono', 'like', '%' . $this->search . '%')
                      ->orWhere('tipo_solicitante', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->sortField, function ($query) {
                switch ($this->sortField) {
                    case 'run_solicitante':
                        $query->orderBy('run_solicitante', $this->sortDirection);
                        break;
                    case 'nombre':
                        $query->orderBy('nombre', $this->sortDirection);
                        break;
                    case 'correo':
                        $query->orderBy('correo', $this->sortDirection);
                        break;
                    case 'telefono':
                        $query->orderBy('telefono', $this->sortDirection);
                        break;
                    case 'tipo_solicitante':
                        $query->orderBy('tipo_solicitante', $this->sortDirection);
                        break;
                    default:
                        $query->orderBy('run_solicitante', 'asc');
                }
            })
            ->paginate(10);

        return view('livewire.visitantes-table', compact('visitantes'));
    }
}