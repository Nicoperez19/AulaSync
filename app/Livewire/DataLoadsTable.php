<?php

namespace App\Livewire;

use App\Models\DataLoad;
use Livewire\Component;
use Livewire\WithPagination;

class DataLoadsTable extends Component
{
    use WithPagination;

    public $search = '';
    public $sortField = 'created_at';
    public $sortDirection = 'desc';

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
        return view('livewire.data-loads-table', [
            'dataLoads' => DataLoad::query()
                ->when($this->search, function ($query) {
                    $query->where(function ($query) {
                        $query->where('nombre_archivo', 'like', '%' . $this->search . '%')
                            ->orWhere('tipo_carga', 'like', '%' . $this->search . '%')
                            ->orWhere('estado', 'like', '%' . $this->search . '%');
                    });
                })
                ->orderBy($this->sortField, $this->sortDirection)
                ->paginate(10)
        ]);
    }
}
