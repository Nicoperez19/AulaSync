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
    public $perPage = 20;

    protected $queryString = [
        'search' => ['except' => ''],
        'sortField' => ['except' => 'fecha_reserva'],
        'sortDirection' => ['except' => 'desc'],
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

        $reservas = Reserva::query()
            ->with(['profesor', 'solicitante', 'espacio', 'asignatura'])
            ->when($searchTerm !== '', function ($query) use ($searchTerm) {
                $cleanRun = preg_replace('/[^0-9Kk]/', '', $searchTerm);
                $termSinTilde = str_replace(
                    ['á', 'é', 'í', 'ó', 'ú', 'Á', 'É', 'Í', 'Ó', 'Ú'],
                    ['a', 'e', 'i', 'o', 'u', 'A', 'E', 'I', 'O', 'U'],
                    $searchTerm
                );
                $terms = array_unique(array_filter([$searchTerm, $termSinTilde, mb_strtoupper($searchTerm), mb_strtolower($searchTerm)]));

                $query->where(function ($q) use ($terms, $searchTerm, $cleanRun) {
                    $q->where('id_reserva', 'like', '%' . $searchTerm . '%')
                      ->orWhere('id_espacio', 'like', '%' . $searchTerm . '%')
                      ->orWhere('fecha_reserva', 'like', '%' . $searchTerm . '%');

                    if (!empty($cleanRun)) {
                        $q->orWhere('run_profesor', 'like', '%' . $cleanRun . '%')
                          ->orWhere('run_solicitante', 'like', '%' . $cleanRun . '%');
                    }

                    foreach ($terms as $t) {
                        $q->orWhere('estado', 'like', '%' . $t . '%')
                          ->orWhereHas('profesor', function ($pq) use ($t) {
                              $pq->where('name', 'like', '%' . $t . '%');
                          })
                          ->orWhereHas('solicitante', function ($sq) use ($t) {
                              $sq->where('nombre', 'like', '%' . $t . '%');
                          })
                          ->orWhereHas('espacio', function ($eq) use ($t) {
                              $eq->where('nombre_espacio', 'like', '%' . $t . '%');
                          })
                          ->orWhereHas('asignatura', function ($aq) use ($t) {
                              $aq->where('nombre_asignatura', 'like', '%' . $t . '%');
                          });
                    }
                });
            })
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        return view('livewire.reservations-table', compact('reservas'));
    }
}