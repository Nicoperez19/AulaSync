<?php

namespace App\Livewire;

use App\Models\JefeCarrera;
use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Carrera;

class JefesCarreraTable extends Component
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

        $jefesCarrera = JefeCarrera::query()
            ->with(['carrera.areaAcademica.facultad.sede.universidad'])
            ->when($searchTerm !== '', function ($query) use ($searchTerm) {
                // Las carreras viven en la base central: se resuelven sus IDs aparte
                $carreraIds = Carrera::query()
                    ->where(function ($cq) use ($searchTerm) {
                        $cq->where('nombre', 'like', '%' . $searchTerm . '%')
                           ->orWhere('id_carrera', 'like', '%' . $searchTerm . '%');
                    })
                    ->pluck('id_carrera')
                    ->all();

                $query->where(function ($q) use ($searchTerm, $carreraIds) {
                    $q->where('nombre', 'like', '%' . $searchTerm . '%')
                      ->orWhere('email', 'like', '%' . $searchTerm . '%')
                      ->orWhere('telefono', 'like', '%' . $searchTerm . '%');

                    if (!empty($carreraIds)) {
                        $q->orWhereIn('id_carrera', $carreraIds);
                    }
                });
            })
            ->orderBy($sortField, $sortDirection)
            ->paginate($this->perPage);

        return view('livewire.jefes-carrera-table', compact('jefesCarrera'));
    }
}
