<?php

namespace App\Livewire;

use App\Models\Espacio;  
use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Facultad;

class SpacesTable extends Component
{

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

        $sortField = in_array($this->sortField, ['id_espacio', 'nombre_espacio', 'tipo_espacio', 'estado', 'puestos_disponibles'], true)
            ? $this->sortField
            : 'id_espacio';
        $sortDirection = $this->sortDirection === 'desc' ? 'desc' : 'asc';

        $espacios = Espacio::query()
            ->with('piso.facultad.sede')
            ->when($searchTerm !== '', function ($query) use ($searchTerm) {
                // Las facultades viven en la base central: se resuelven sus IDs aparte
                $facultadIds = Facultad::where('nombre_facultad', 'like', '%' . $searchTerm . '%')
                    ->pluck('id_facultad')->all();

                $query->where(function ($q) use ($searchTerm, $facultadIds) {
                    $q->where('id_espacio', 'like', '%' . $searchTerm . '%')
                      ->orWhere('nombre_espacio', 'like', '%' . $searchTerm . '%')
                      ->orWhere('tipo_espacio', 'like', '%' . $searchTerm . '%')
                      ->orWhere('estado', 'like', '%' . $searchTerm . '%')
                      ->orWhereHas('piso', function ($pq) use ($searchTerm, $facultadIds) {
                          $pq->where(function ($sub) use ($searchTerm, $facultadIds) {
                              $sub->where('numero_piso', 'like', '%' . $searchTerm . '%')
                                  ->orWhere('nombre_piso', 'like', '%' . $searchTerm . '%');

                              if (!empty($facultadIds)) {
                                  $sub->orWhereIn('id_facultad', $facultadIds);
                              }
                          });
                      });
                });
            })
            ->orderBy($sortField, $sortDirection)
            ->paginate(10);

        return view('livewire.spaces-table', compact('espacios'));
    }
}
