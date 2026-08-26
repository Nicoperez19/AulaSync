<?php

namespace App\Livewire;

use App\Models\Universidad;
use Livewire\Component;
use Livewire\WithPagination;

class UniversitysTable extends Component
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

        $universidades = Universidad::query()
            ->when($searchTerm !== '', function ($query) use ($searchTerm) {
                $termSinTilde = str_replace(
                    ['á', 'é', 'í', 'ó', 'ú', 'Á', 'É', 'Í', 'Ó', 'Ú'],
                    ['a', 'e', 'i', 'o', 'u', 'A', 'E', 'I', 'O', 'U'],
                    $searchTerm
                );
                $terms = array_unique(array_filter([$searchTerm, $termSinTilde, mb_strtoupper($searchTerm), mb_strtolower($searchTerm)]));

                $query->where(function ($q) use ($terms, $searchTerm) {
                    $q->where('id_universidad', 'like', '%' . $searchTerm . '%')
                      ->orWhere('telefono_universidad', 'like', '%' . $searchTerm . '%');

                    foreach ($terms as $t) {
                        $q->orWhere('nombre_universidad', 'like', '%' . $t . '%')
                          ->orWhere('direccion_universidad', 'like', '%' . $t . '%');
                    }
                });
            })
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate(10);

        return view('livewire.universitys-table', compact('universidades'));
    }
}
