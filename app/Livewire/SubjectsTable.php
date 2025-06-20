<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Asignatura; 

class SubjectsTable extends Component
{
    use WithPagination;

    public $search = '';
    public $perPage = 10;
    public $paginationOptions = [5, 10, 50];

    // Sync query string with pagination and search
    protected $updatesQueryString = ['search', 'perPage'];

    public function updatingSearch()
    {
        $this->resetPage(); // Reset to the first page when search is updated
    }

    public function render()
    {
        $asignaturas = Asignatura::where('nombre', 'like', '%' . $this->search . '%')
            ->orWhere('id_asignatura', 'like', '%' . $this->search . '%')
            ->orWhere('area_conocimiento', 'like', '%' . $this->search . '%')
            ->orderBy('nombre', 'asc')
            ->paginate($this->perPage);

        return view('livewire.subjects-table', compact('asignaturas'));
    }
}
