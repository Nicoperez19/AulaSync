<?php

namespace App\Livewire;

use App\Models\Espacio;  
use Livewire\Component;
use Livewire\WithPagination;

class SpacesTable extends Component
{
    use WithPagination;

    public $search = '';  
    public $sortField = 'id_espacio';
    public $sortDirection = 'asc';

    protected $queryString = [
        'search' => ['except' => ''],
        'sortField' => ['except' => 'id_espacio'],
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
        $espacios = Espacio::query()
            ->with('piso.facultad')
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('id_espacio', 'like', '%' . $this->search . '%')
                      ->orWhere('nombre_espacio', 'like', '%' . $this->search . '%')
                      ->orWhere('tipo_espacio', 'like', '%' . $this->search . '%')
                      ->orWhere('estado', 'like', '%' . $this->search . '%')
                      ->orWhereHas('piso.facultad', function ($subQuery) {
                          $subQuery->where('nombre_facultad', 'like', '%' . $this->search . '%');
                      })
                      ->orWhereHas('piso', function ($subQuery) {
                          $subQuery->where('numero_piso', 'like', '%' . $this->search . '%');
                      });
                });
            })
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate(10);  

        return view('livewire.spaces-table', compact('espacios'));
    }
}
