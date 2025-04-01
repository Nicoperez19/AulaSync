<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Facultad;

class FacultiesTable extends Component
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
        $facultades = Facultad::where('nombre', 'like', '%' . $this->search . '%')
            ->orWhere('ubicacion', 'like', '%' . $this->search . '%')
            ->orWhereHas('universidad', function ($query) {
                $query->where('nombre_universidad', 'like', '%' . $this->search . '%');
            })
            ->orderBy('nombre', 'asc')
            ->paginate($this->perPage);

        return view('livewire.faculties-table', compact('facultades'));
    }
}
