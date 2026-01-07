<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Carrera;
use App\Models\Sede;
use App\Models\Universidad;

class CareersTable extends Component
{
    use WithPagination;

    public $search = '';
    public $sortField = 'id_carrera';
    public $sortDirection = 'asc';
    public $perPage = 10;

    protected $queryString = [
        'search' => ['except' => ''],
        'sortField' => ['except' => 'id_carrera'],
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
        $searchTerm = trim($this->search);

        $sedeIds = [];
        if ($searchTerm !== '') {
            // Buscar coincidencias en sedes y universidades usando conexión central
            $sedeIds = Sede::on('mysql')
                ->where('nombre_sede', 'like', "%{$searchTerm}%")
                ->pluck('id_sede')
                ->all();

            $universidadIds = Universidad::on('mysql')
                ->where('nombre_universidad', 'like', "%{$searchTerm}%")
                ->pluck('id_universidad');

            if ($universidadIds->isNotEmpty()) {
                $sedeIdsFromUniversidades = Sede::on('mysql')
                    ->whereIn('id_universidad', $universidadIds)
                    ->pluck('id_sede')
                    ->all();

                $sedeIds = array_values(array_unique(array_merge($sedeIds, $sedeIdsFromUniversidades)));
            }
        }

        $carreras = Carrera::query()
            ->with(['areaAcademica.facultad.sede.universidad'])
            ->when($searchTerm !== '', function ($query) use ($searchTerm, $sedeIds) {
                $query->where(function ($innerQuery) use ($searchTerm, $sedeIds) {
                    $innerQuery->where('id_carrera', 'like', "%{$searchTerm}%")
                        ->orWhere('nombre', 'like', "%{$searchTerm}%")
                        ->orWhereHas('areaAcademica', function ($q) use ($searchTerm) {
                            $q->where('nombre_area_academica', 'like', "%{$searchTerm}%");
                        })
                        ->orWhereHas('areaAcademica.facultad', function ($q) use ($searchTerm) {
                            $q->where('nombre_facultad', 'like', "%{$searchTerm}%");
                        });

                    if (!empty($sedeIds)) {
                        $innerQuery->orWhereHas('areaAcademica.facultad', function ($q) use ($sedeIds) {
                            $q->whereIn('id_sede', $sedeIds);
                        });
                    }
                });
            })
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        return view('livewire.careers-table', compact('carreras'));
    }
}
