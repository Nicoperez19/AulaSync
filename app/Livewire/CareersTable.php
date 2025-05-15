<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Carrera;

class CareersTable extends Component
{
    use WithPagination;

    public $search = '';
    public $perPage = 10;
    public $paginationOptions = [5, 10, 50];

    protected $updatesQueryString = ['search', 'perPage'];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function render()
    {
        $carreras = Carrera::where('nombre', 'like', '%' . $this->search . '%')
            ->orWhereHas('facultad', function ($query) {
                $query->where('nombre_facultad', 'like', '%' . $this->search . '%');
            })
            ->orWhereHas('facultad.universidad', function ($query) {
                $query->where('nombre_universidad', 'like', '%' . $this->search . '%');
            })
            ->orderBy('nombre', 'asc')
            ->paginate($this->perPage);

        return view('livewire.careers-table', compact('carreras'));
    }
}
