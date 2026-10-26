<?php

namespace App\Livewire;

use App\Models\Sede;
use Livewire\Component;
use Livewire\WithPagination;

class SedesTable extends Component
{
    use WithPagination;

    public $search = '';
    public $sortField = 'id_sede';
    public $sortDirection = 'asc';

    protected $queryString = [
        'search' => ['except' => ''],
        'sortField' => ['except' => 'id_sede'],
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

        $sedes = Sede::query()
            ->with('universidad')
            ->when($searchTerm !== '', function ($query) use ($searchTerm) {
                $termSinTilde = str_replace(
                    ['á', 'é', 'í', 'ó', 'ú', 'Á', 'É', 'Í', 'Ó', 'Ú'],
                    ['a', 'e', 'i', 'o', 'u', 'A', 'E', 'I', 'O', 'U'],
                    $searchTerm
                );
                $terms = array_unique(array_filter([$searchTerm, $termSinTilde, mb_strtoupper($searchTerm), mb_strtolower($searchTerm)]));

                $query->where(function ($q) use ($terms, $searchTerm) {
                    $q->where('id_sede', 'like', '%' . $searchTerm . '%');

                    foreach ($terms as $t) {
                        $q->orWhere('nombre_sede', 'like', '%' . $t . '%')
                          ->orWhereHas('universidad', function ($subQuery) use ($t) {
                              $subQuery->where('nombre_universidad', 'like', '%' . $t . '%');
                          });
                    }
                });
            })
            ->when($this->sortField, function ($query) {
                switch ($this->sortField) {
                    case 'id_sede':
                        $query->orderBy('id_sede', $this->sortDirection);
                        break;
                    case 'nombre_sede':
                        $query->orderBy('nombre_sede', $this->sortDirection);
                        break;
                    case 'universidad':
                        $query->join('universidades', 'sedes.id_universidad', '=', 'universidades.id_universidad')
                              ->orderBy('universidades.nombre_universidad', $this->sortDirection)
                              ->select('sedes.*');
                        break;
                    default:
                        $query->orderBy('id_sede', 'asc');
                }
            })
            ->paginate(10);

        return view('livewire.sedes-table', compact('sedes'));
    }
} 