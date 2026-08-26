<?php

namespace App\Livewire;

use App\Models\AsistenteAcademico;
use Livewire\Component;
use Livewire\WithPagination;

class AsistentesAcademicosTable extends Component
{
    use WithPagination;

    public $search = '';
    public $sortField = 'nombre';
    public $sortDirection = 'asc';
    public $perPage = 10;

    protected $queryString = [
        'search' => ['except' => ''],
        'sortField' => ['except' => 'nombre'],
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

        $asistentesAcademicos = AsistenteAcademico::query()
            ->with(['areaAcademica.facultad.sede.universidad'])
            ->when($searchTerm !== '', function ($query) use ($searchTerm) {
                $termSinTilde = str_replace(
                    ['á', 'é', 'í', 'ó', 'ú', 'Á', 'É', 'Í', 'Ó', 'Ú'],
                    ['a', 'e', 'i', 'o', 'u', 'A', 'E', 'I', 'O', 'U'],
                    $searchTerm
                );
                $terms = array_unique(array_filter([$searchTerm, $termSinTilde, mb_strtoupper($searchTerm), mb_strtolower($searchTerm)]));

                $query->where(function ($q) use ($terms) {
                    foreach ($terms as $t) {
                        $q->orWhere('nombre', 'like', '%' . $t . '%')
                          ->orWhere('email', 'like', '%' . $t . '%')
                          ->orWhereHas('areaAcademica', function ($aq) use ($t) {
                              $aq->where('nombre_area_academica', 'like', '%' . $t . '%')
                                 ->where('tipo_area_academica', 'escuela');
                          });
                    }
                });
            })
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        return view('livewire.asistentes-academicos-table', compact('asistentesAcademicos'));
    }
}
