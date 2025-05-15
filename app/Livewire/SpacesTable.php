<?php

namespace App\Livewire;

use App\Models\Espacio;  
use Livewire\Component;
use Livewire\WithPagination;

class SpacesTable extends Component
{
    use WithPagination;

    public $search = '';  

    public function mount()
    {
       
    }

    public function render()
    {
        $espacios = Espacio::where('id_espacio', 'like', '%' . $this->search . '%')
            ->orWhere('tipo_espacio', 'like', '%' . $this->search . '%')
            ->orWhere('estado', 'like', '%' . $this->search . '%')
            ->with('piso')  
            ->paginate(10);  

        return view('livewire.spaces-table', compact('espacios'));
    }
}
