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
        $asistentesAcademicos = AsistenteAcademico::query()
            ->with(['areaAcademica.facultad.sede.universidad'])
            ->where(function ($query) {
                $query->where('nombre', 'like', '%' . $this->search . '%')
                    ->orWhere('email', 'like', '%' . $this->search . '%')
                    ->orWhereHas('areaAcademica', function ($q) {
                        $q->where('nombre_area_academica', 'like', '%' . $this->search . '%')
                          ->where('tipo_area_academica', 'escuela');
                    });
            })
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        return view('livewire.asistentes-academicos-table', compact('asistentesAcademicos'));
    }
}
