<?php

namespace App\Livewire;

use App\Models\AreaAcademica;
use Livewire\Component;
use Livewire\WithPagination;

class EscuelasTable extends Component
{
    use WithPagination;

    public $search = '';
    public $sortField = 'nombre_area_academica';
    public $sortDirection = 'asc';
    public $perPage = 10;

    protected $queryString = [
        'search' => ['except' => ''],
        'sortField' => ['except' => 'nombre_area_academica'],
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

        $escuelas = AreaAcademica::query()
            ->where('tipo_area_academica', 'escuela')
            ->with(['facultad.sede.universidad', 'carreras'])
            ->when($searchTerm !== '', function ($query) use ($searchTerm) {
                $termSinTilde = str_replace(
                    ['á', 'é', 'í', 'ó', 'ú', 'Á', 'É', 'Í', 'Ó', 'Ú'],
                    ['a', 'e', 'i', 'o', 'u', 'A', 'E', 'I', 'O', 'U'],
                    $searchTerm
                );
                $terms = array_unique(array_filter([$searchTerm, $termSinTilde, mb_strtoupper($searchTerm), mb_strtolower($searchTerm)]));

                $query->where(function ($q) use ($terms, $searchTerm) {
                    $q->where('id_area_academica', 'like', '%' . $searchTerm . '%');

                    foreach ($terms as $t) {
                        $q->orWhere('nombre_area_academica', 'like', '%' . $t . '%')
                          ->orWhereHas('facultad', function ($fq) use ($t) {
                              $fq->where('nombre_facultad', 'like', '%' . $t . '%');
                          });
                    }
                });
            })
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        return view('livewire.escuelas-table', compact('escuelas'));
    }
}
