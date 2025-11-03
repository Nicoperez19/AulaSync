<?php

namespace App\Livewire;

use App\Models\Configuracion;
use Livewire\Component;
use Livewire\WithPagination;

class ConfiguracionTable extends Component
{
    use WithPagination;

    public $search = '';
    public $sortField = 'clave';
    public $sortDirection = 'asc';
    public $perPage = 10;

    protected $queryString = [
        'search' => ['except' => ''],
        'sortField' => ['except' => 'clave'],
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
        $configuraciones = Configuracion::query()
            ->where(function ($query) {
                $query->where('clave', 'like', '%' . $this->search . '%')
                    ->orWhere('descripcion', 'like', '%' . $this->search . '%');
            })
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        return view('livewire.configuracion-table', compact('configuraciones'));
    }
}
