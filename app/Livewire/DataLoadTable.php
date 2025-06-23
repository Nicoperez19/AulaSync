<?php

namespace App\Livewire;

use App\Models\DataLoad;
use Livewire\Component;
use Livewire\WithPagination;

class DataLoadTable extends Component
{
    use WithPagination;

    protected $listeners = ['fileUploaded' => '$refresh'];
    
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
        $dataLoads = DataLoad::query()
            ->when($this->search, function ($query) {
                $query->where(function ($query) {
                    $query->where('nombre_archivo', 'like', '%' . $this->search . '%')
                        ->orWhereHas('user', function ($query) {
                            $query->where('name', 'like', '%' . $this->search . '%')
                                ->orWhere('run', 'like', '%' . $this->search . '%');
                        });
                });
            })
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate(10);

        return view('livewire.data-load-table', [
            'dataLoads' => $dataLoads
        ]);
    }
}
