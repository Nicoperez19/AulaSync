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

        $espacios = Espacio::query()
            ->with('piso.facultad.sede')
            ->when($searchTerm !== '', function ($query) use ($searchTerm) {
                $termSinTilde = str_replace(
                    ['á', 'é', 'í', 'ó', 'ú', 'Á', 'É', 'Í', 'Ó', 'Ú'],
                    ['a', 'e', 'i', 'o', 'u', 'A', 'E', 'I', 'O', 'U'],
                    $searchTerm
                );
                $terms = array_unique(array_filter([$searchTerm, $termSinTilde, mb_strtoupper($searchTerm), mb_strtolower($searchTerm)]));

                $query->where(function ($q) use ($terms, $searchTerm) {
                    $q->where('id_espacio', 'like', '%' . $searchTerm . '%');
                    foreach ($terms as $t) {
                        $q->orWhere('nombre_espacio', 'like', '%' . $t . '%')
                          ->orWhere('tipo_espacio', 'like', '%' . $t . '%')
                          ->orWhere('estado', 'like', '%' . $t . '%')
                          ->orWhereHas('piso.facultad', function ($subQuery) use ($t) {
                              $subQuery->where('nombre_facultad', 'like', '%' . $t . '%');
                          })
                          ->orWhereHas('piso', function ($subQuery) use ($t) {
                              $subQuery->where('numero_piso', 'like', '%' . $t . '%');
                          });
                    }
                });
            })
            ->when($this->sortField, function ($query) {
                switch ($this->sortField) {
                    case 'id_espacio':
                        $query->orderBy('id_espacio', $this->sortDirection);
                        break;
                    case 'nombre_espacio':
                        $query->orderBy('nombre_espacio', $this->sortDirection);
                        break;
                    case 'tipo_espacio':
                        $query->orderBy('tipo_espacio', $this->sortDirection);
                        break;
                    case 'estado':
                        $query->orderBy('estado', $this->sortDirection);
                        break;
                    case 'puestos_disponibles':
                        $query->orderBy('puestos_disponibles', $this->sortDirection);
                        break;
                    default:
                        $query->orderBy('id_espacio', 'asc');
                }
            })
            ->paginate(10);  

        return view('livewire.spaces-table', compact('espacios'));
    }
}
