<?php

namespace App\Livewire;

use App\Models\Universidad;
use Livewire\Component;
use Livewire\WithPagination;

class UniversitiesTable extends Component
{
    use WithPagination;

    public $search = '';
    public $sortField = 'id_universidad';
    public $sortDirection = 'asc';

    protected $queryString = [
        'search' => ['except' => ''],
        'sortField' => ['except' => 'id_universidad'],
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
        $universidades = Universidad::query()
            ->with('comuna')
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('id_universidad', 'like', '%' . $this->search . '%')
                      ->orWhere('nombre_universidad', 'like', '%' . $this->search . '%')
                      ->orWhere('direccion_universidad', 'like', '%' . $this->search . '%')
                      ->orWhere('telefono_universidad', 'like', '%' . $this->search . '%')
                      ->orWhereHas('comuna', function ($subQuery) {
                          $subQuery->where('nombre_comuna', 'like', '%' . $this->search . '%');
                      });
                });
            })
            ->when($this->sortField, function ($query) {
                switch ($this->sortField) {
                    case 'id_universidad':
                        $query->orderBy('id_universidad', $this->sortDirection);
                        break;
                    case 'nombre_universidad':
                        $query->orderBy('nombre_universidad', $this->sortDirection);
                        break;
                    case 'direccion_universidad':
                        $query->orderBy('direccion_universidad', $this->sortDirection);
                        break;
                    case 'telefono_universidad':
                        $query->orderBy('telefono_universidad', $this->sortDirection);
                        break;
                    case 'comuna':
                        $query->join('comunas', 'universidades.comunas_id', '=', 'comunas.id')
                              ->orderBy('comunas.nombre_comuna', $this->sortDirection)
                              ->select('universidades.*');
                        break;
                    default:
                        $query->orderBy('id_universidad', 'asc');
                }
            })
            ->paginate(10);

        return view('livewire.universities-table', compact('universidades'));
    }
} 