<?php

namespace App\Livewire;

use App\Models\Reserva; 
use Livewire\Component;
use Livewire\WithPagination;

class ReservationsTable extends Component
{
    use WithPagination;

    public $search = '';
    public $sortField = 'fecha_reserva';
    public $sortDirection = 'desc';
    public $perPage = 10;

    protected $queryString = [
        'search' => ['except' => ''],
        'sortField' => ['except' => 'fecha_reserva'],
        'sortDirection' => ['except' => 'desc'],
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

        $reservas = Reserva::query()
            ->with(['profesor', 'solicitante', 'espacio', 'asignatura'])
            ->when($searchTerm !== '', function ($query) use ($searchTerm) {
                $query->where(function ($q) use ($searchTerm) {
                    $q->where('id_reserva', 'like', '%' . $searchTerm . '%')
                      ->orWhere('id_espacio', 'like', '%' . $searchTerm . '%')
                      ->orWhere('fecha_reserva', 'like', '%' . $searchTerm . '%')
                      ->orWhere('run_profesor', 'like', '%' . $searchTerm . '%')
                      ->orWhere('run_solicitante', 'like', '%' . $searchTerm . '%')
                      ->orWhere('estado', 'like', '%' . $searchTerm . '%')
                      ->orWhereHas('profesor', function ($pq) use ($searchTerm) {
                          $pq->where('name', 'like', '%' . $searchTerm . '%');
                      })
                      ->orWhereHas('solicitante', function ($sq) use ($searchTerm) {
                          $sq->where('nombre', 'like', '%' . $searchTerm . '%');
                      })
                      ->orWhereHas('espacio', function ($eq) use ($searchTerm) {
                          $eq->where('nombre_espacio', 'like', '%' . $searchTerm . '%');
                      })
                      ->orWhereHas('asignatura', function ($aq) use ($searchTerm) {
                          $aq->where('nombre_asignatura', 'like', '%' . $searchTerm . '%');
                      });
                });
            })
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        return view('livewire.reservations-table', compact('reservas'));
    }
}