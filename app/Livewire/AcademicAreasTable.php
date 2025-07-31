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
        $areasAcademicas = AreaAcademica::query()
            ->with('facultad.sede.universidad')
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('id_area_academica', 'like', '%' . $this->search . '%')
                      ->orWhere('nombre_area_academica', 'like', '%' . $this->search . '%')
                      ->orWhere('tipo_area_academica', 'like', '%' . $this->search . '%')
                      ->orWhereHas('facultad', function ($subQuery) {
                          $subQuery->where('nombre_facultad', 'like', '%' . $this->search . '%');
                      })
                      ->orWhereHas('facultad.sede', function ($subQuery) {
                          $subQuery->where('nombre_sede', 'like', '%' . $this->search . '%');
                      })
                      ->orWhereHas('facultad.sede.universidad', function ($subQuery) {
                          $subQuery->where('nombre_universidad', 'like', '%' . $this->search . '%');
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
                        $query->join('facultades', 'area_academicas.id_facultad', '=', 'facultades.id_facultad')
                              ->join('sedes', 'facultades.id_sede', '=', 'sedes.id_sede')
                              ->orderBy('sedes.nombre_sede', $this->sortDirection)
                              ->select('area_academicas.*');
                        break;
                    case 'universidad':
                        $query->join('facultades', 'area_academicas.id_facultad', '=', 'facultades.id_facultad')
                              ->join('sedes', 'facultades.id_sede', '=', 'sedes.id_sede')
                              ->join('universidades', 'sedes.id_universidad', '=', 'universidades.id_universidad')
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