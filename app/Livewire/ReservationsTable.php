<?php

namespace App\Livewire;

use App\Models\Reserva; 
use Livewire\Component;
use Livewire\WithPagination;

class ReservationsTable extends Component
{
    use WithPagination;

    public $search = '';

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function render()
    {
        $reservas = Reserva::where('id_reserva', 'like', '%' . $this->search . '%')
            ->orWhere('id_espacio', 'like', '%' . $this->search . '%')
            ->orWhere('fecha_reserva', 'like', '%' . $this->search . '%')
            ->paginate(10);

        return view('livewire.reservations-table', compact('reservas'));
    }
}