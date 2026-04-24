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

        if ($searchTerm !== '') {
            $universidadIds = \App\Models\Universidad::where('nombre_universidad', 'like', '%' . $searchTerm . '%')
                ->pluck('id_universidad');
            
            $sedeIds = \App\Models\Sede::where('nombre_sede', 'like', '%' . $searchTerm . '%')
                ->pluck('id_sede');
        }

        $areasAcademicas = AreaAcademica::query()
            ->with('facultad.sede.universidad')
            ->when($searchTerm, function ($query) use ($searchTerm, $universidadIds, $sedeIds) {
                $query->where(function ($q) use ($searchTerm, $universidadIds, $sedeIds) {
                    $q->where('id_area_academica', 'like', '%' . $searchTerm . '%')
                      ->orWhere('nombre_area_academica', 'like', '%' . $searchTerm . '%')
                      ->orWhere('tipo_area_academica', 'like', '%' . $searchTerm . '%')
                      ->orWhereHas('facultad', function ($subQuery) use ($searchTerm) {
                          $subQuery->where('nombre_facultad', 'like', '%' . $searchTerm . '%');
                      })
                      ->when($sedeIds->isNotEmpty(), function ($q2) use ($sedeIds) {
                          $q2->orWhereHas('facultad', function ($subQuery) use ($sedeIds) {
                              $subQuery->whereIn('id_sede', $sedeIds);
                          });
                      })
                      ->when($universidadIds->isNotEmpty(), function ($q2) use ($universidadIds) {
                          $q2->orWhereHas('facultad', function ($subQuery) use ($universidadIds) {
                              $subQuery->whereIn('id_universidad', $universidadIds);
                          });
                      });
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