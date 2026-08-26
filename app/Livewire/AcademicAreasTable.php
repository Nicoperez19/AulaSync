<?php

namespace App\Livewire;

use App\Models\AreaAcademica;
use Livewire\Component;
use Livewire\WithPagination;

class AcademicAreasTable extends Component
{
    use WithPagination;

    public $search = '';
    public $sortField = 'id_area_academica';
    public $sortDirection = 'asc';

    protected $queryString = [
        'search' => ['except' => ''],
        'sortField' => ['except' => 'id_area_academica'],
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
        $universidadIds = collect();
        $sedeIds = collect();

        $areasAcademicas = AreaAcademica::query()
            ->with('facultad.sede.universidad')
            ->when($searchTerm !== '', function ($query) use ($searchTerm) {
                $termSinTilde = str_replace(
                    ['á', 'é', 'í', 'ó', 'ú', 'Á', 'É', 'Í', 'Ó', 'Ú'],
                    ['a', 'e', 'i', 'o', 'u', 'A', 'E', 'I', 'O', 'U'],
                    $searchTerm
                );
                $terms = array_unique(array_filter([$searchTerm, $termSinTilde, mb_strtoupper($searchTerm), mb_strtolower($searchTerm)]));

                try {
                    $universidadIds = \App\Models\Universidad::where(function($uq) use ($terms) {
                        foreach ($terms as $t) {
                            $uq->orWhere('nombre_universidad', 'like', '%' . $t . '%');
                        }
                    })->pluck('id_universidad');
                    
                    $sedeIds = \App\Models\Sede::where(function($sq) use ($terms) {
                        foreach ($terms as $t) {
                            $sq->orWhere('nombre_sede', 'like', '%' . $t . '%');
                        }
                    })->pluck('id_sede');
                } catch (\Throwable $e) {
                    $universidadIds = collect();
                    $sedeIds = collect();
                }

                $query->where(function ($q) use ($terms, $searchTerm, $universidadIds, $sedeIds) {
                    $q->where('id_area_academica', 'like', '%' . $searchTerm . '%');

                    foreach ($terms as $t) {
                        $q->orWhere('nombre_area_academica', 'like', '%' . $t . '%')
                          ->orWhere('tipo_area_academica', 'like', '%' . $t . '%')
                          ->orWhereHas('facultad', function ($subQuery) use ($t) {
                              $subQuery->where('nombre_facultad', 'like', '%' . $t . '%');
                          });
                    }

                    if ($sedeIds->isNotEmpty()) {
                        $q->orWhereHas('facultad', function ($subQuery) use ($sedeIds) {
                            $subQuery->whereIn('id_sede', $sedeIds);
                        });
                    }

                    if ($universidadIds->isNotEmpty()) {
                        $q->orWhereHas('facultad', function ($subQuery) use ($universidadIds) {
                            $subQuery->whereIn('id_universidad', $universidadIds);
                        });
                    }
                });
            })
            ->when($this->sortField, function ($query) {
                switch ($this->sortField) {
                    case 'id_area_academica':
                        $query->orderBy('id_area_academica', $this->sortDirection);
                        break;
                    case 'nombre_area_academica':
                        $query->orderBy('nombre_area_academica', $this->sortDirection);
                        break;
                    case 'tipo_area_academica':
                        $query->orderBy('tipo_area_academica', $this->sortDirection);
                        break;
                    case 'facultad':
                        $query->join('facultades', 'area_academicas.id_facultad', '=', 'facultades.id_facultad')
                              ->orderBy('facultades.nombre_facultad', $this->sortDirection)
                              ->select('area_academicas.*');
                        break;
                    case 'sede':
                        $centralDb = config('database.connections.mysql.database');
                        $query->join('facultades', 'area_academicas.id_facultad', '=', 'facultades.id_facultad')
                              ->join($centralDb . '.sedes', 'facultades.id_sede', '=', 'sedes.id_sede')
                              ->orderBy('sedes.nombre_sede', $this->sortDirection)
                              ->select('area_academicas.*');
                        break;
                    case 'universidad':
                        $centralDb = config('database.connections.mysql.database');
                        $query->join('facultades', 'area_academicas.id_facultad', '=', 'facultades.id_facultad')
                              ->join($centralDb . '.sedes', 'facultades.id_sede', '=', 'sedes.id_sede')
                              ->join($centralDb . '.universidades', 'sedes.id_universidad', '=', 'universidades.id_universidad')
                              ->orderBy('universidades.nombre_universidad', $this->sortDirection)
                              ->select('area_academicas.*');
                        break;
                    default:
                        $query->orderBy('id_area_academica', 'asc');
                }
            })
            ->paginate(10);

        return view('livewire.academic-areas-table', compact('areasAcademicas'));
    }
} 