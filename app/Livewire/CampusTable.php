<?php

namespace App\Livewire;

use App\Models\Campus;
use Livewire\Component;
use Livewire\WithPagination;

class CampusTable extends Component
{
    use WithPagination;

    public $search = '';
    public $sortField = 'id_campus';
    public $sortDirection = 'asc';

    protected $queryString = [
        'search' => ['except' => ''],
        'sortField' => ['except' => 'id_campus'],
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
        $campus = Campus::query()
            ->with('sede.universidad')
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('id_campus', 'like', '%' . $this->search . '%')
                      ->orWhere('nombre_campus', 'like', '%' . $this->search . '%')
                      ->orWhereHas('sede', function ($subQuery) {
                          $subQuery->where('nombre_sede', 'like', '%' . $this->search . '%');
                      })
                      ->orWhereHas('sede.universidad', function ($subQuery) {
                          $subQuery->where('nombre_universidad', 'like', '%' . $this->search . '%');
                      });
                });
            })
            ->when($this->sortField, function ($query) {
                switch ($this->sortField) {
                    case 'id_campus':
                        $query->orderBy('id_campus', $this->sortDirection);
                        break;
                    case 'nombre_campus':
                        $query->orderBy('nombre_campus', $this->sortDirection);
                        break;
                    case 'sede':
                        $query->join('sedes', 'campus.id_sede', '=', 'sedes.id_sede')
                              ->orderBy('sedes.nombre_sede', $this->sortDirection)
                              ->select('campus.*');
                        break;
                    case 'universidad':
                        $query->join('sedes', 'campus.id_sede', '=', 'sedes.id_sede')
                              ->join('universidades', 'sedes.id_universidad', '=', 'universidades.id_universidad')
                              ->orderBy('universidades.nombre_universidad', $this->sortDirection)
                              ->select('campus.*');
                        break;
                    default:
                        $query->orderBy('id_campus', 'asc');
                }
            })
            ->paginate(10);

        return view('livewire.campus-table', compact('campus'));
    }
} 