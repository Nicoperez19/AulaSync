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

        $sedeIds = [];
        $terms = [];
        if ($searchTerm !== '') {
            $termSinTilde = str_replace(
                ['á', 'é', 'í', 'ó', 'ú', 'Á', 'É', 'Í', 'Ó', 'Ú'],
                ['a', 'e', 'i', 'o', 'u', 'A', 'E', 'I', 'O', 'U'],
                $searchTerm
            );
            $terms = array_unique(array_filter([$searchTerm, $termSinTilde, mb_strtoupper($searchTerm), mb_strtolower($searchTerm)]));

            try {
                // Buscar coincidencias en sedes y universidades usando conexión central
                $sedeIds = Sede::on('mysql')
                    ->where(function($sq) use ($terms) {
                        foreach ($terms as $t) {
                            $sq->orWhere('nombre_sede', 'like', "%{$t}%");
                        }
                    })
                    ->pluck('id_sede')
                    ->all();

                $universidadIds = Universidad::on('mysql')
                    ->where(function($uq) use ($terms) {
                        foreach ($terms as $t) {
                            $uq->orWhere('nombre_universidad', 'like', "%{$t}%");
                        }
                    })
                    ->pluck('id_universidad');

                if ($universidadIds->isNotEmpty()) {
                    $sedeIdsFromUniversidades = Sede::on('mysql')
                        ->whereIn('id_universidad', $universidadIds)
                        ->pluck('id_sede')
                        ->all();

                    $sedeIds = array_values(array_unique(array_merge($sedeIds, $sedeIdsFromUniversidades)));
                }
            } catch (\Throwable $e) {
                // ignore
            }
        }

        $carreras = Carrera::query()
            ->with(['areaAcademica.facultad.sede.universidad'])
            ->when($searchTerm !== '', function ($query) use ($searchTerm, $terms, $sedeIds) {
                $query->where(function ($innerQuery) use ($searchTerm, $terms, $sedeIds) {
                    $innerQuery->where('id_carrera', 'like', "%{$searchTerm}%");

                    foreach ($terms as $t) {
                        $innerQuery->orWhere('nombre', 'like', "%{$t}%")
                            ->orWhereHas('areaAcademica', function ($q) use ($t) {
                                $q->where('nombre_area_academica', 'like', "%{$t}%");
                            })
                            ->orWhereHas('areaAcademica.facultad', function ($q) use ($t) {
                                $q->where('nombre_facultad', 'like', "%{$t}%");
                            });
                    }

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
