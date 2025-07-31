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
        $facultades = Facultad::with(['universidad', 'sede', 'campus'])
            ->where(function ($query) {
                $query->where('nombre_facultad', 'like', '%' . $this->search . '%')
                    ->orWhere('id_facultad', 'like', '%' . $this->search . '%')
                    ->orWhereHas('universidad', function ($subQuery) {
                        $subQuery->where('nombre_universidad', 'like', '%' . $this->search . '%');
                    })
                    ->orWhereHas('sede', function ($subQuery) {
                        $subQuery->where('nombre_sede', 'like', '%' . $this->search . '%');
                    });
            })
            ->orderBy('nombre_facultad', 'asc')
            ->paginate($this->perPage);

        return view('livewire.faculties-table', compact('facultades'));
    }
}
