<?php

namespace App\Livewire;

use App\Models\AsistenteAcademico;
use Livewire\Component;
use Livewire\WithPagination;
use App\Models\AreaAcademica;

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

        $sortField = in_array($this->sortField, ['nombre', 'email'], true) ? $this->sortField : 'nombre';
        $sortDirection = $this->sortDirection === 'desc' ? 'desc' : 'asc';

        $asistentesAcademicos = AsistenteAcademico::query()
            ->with(['areaAcademica.facultad.sede.universidad'])
            ->when($searchTerm !== '', function ($query) use ($searchTerm) {
                // Las áreas académicas viven en la base central: se resuelven sus IDs
                // aparte para no cruzar bases de datos dentro de un whereHas.
                $areaIds = AreaAcademica::query()
                    ->where(function ($aq) use ($searchTerm) {
                        $aq->where('nombre_area_academica', 'like', '%' . $searchTerm . '%')
                           ->orWhere('id_area_academica', 'like', '%' . $searchTerm . '%');
                    })
                    ->pluck('id_area_academica')
                    ->all();

                $query->where(function ($q) use ($searchTerm, $areaIds) {
                    $q->where('nombre', 'like', '%' . $searchTerm . '%')
                      ->orWhere('email', 'like', '%' . $searchTerm . '%')
                      ->orWhere('nombre_remitente', 'like', '%' . $searchTerm . '%')
                      ->orWhere('telefono', 'like', '%' . $searchTerm . '%');

                    if (!empty($areaIds)) {
                        $q->orWhereIn('id_area_academica', $areaIds);
                    }
                });
            })
            ->orderBy($sortField, $sortDirection)
            ->paginate($this->perPage);

        return view('livewire.asistentes-academicos-table', compact('asistentesAcademicos'));
    }
}
