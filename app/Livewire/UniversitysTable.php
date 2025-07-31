<?php

namespace App\Livewire;

use App\Models\Universidad;
use Livewire\Component;
use Livewire\WithPagination;

class UniversitysTable extends Component
{
    use WithPagination;

    public $search = '';
    public $sortField = 'id_universidad';
    public $sortDirection = 'asc';

    protected $queryString = [
        'search' => ['except' => ''],
        'sortField' => ['except' => 'id_universidad'],
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
        $universidades = Universidad::query()
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('nombre_universidad', 'like', '%' . $this->search . '%')
                      ->orWhere('id_universidad', 'like', '%' . $this->search . '%')
                      ->orWhere('direccion_universidad', 'like', '%' . $this->search . '%')
                      ->orWhere('telefono_universidad', 'like', '%' . $this->search . '%');
                });
            })
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate(10);

        return view('livewire.universitys-table', compact('universidades'));
    }
}
