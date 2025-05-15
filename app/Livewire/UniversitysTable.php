<?php

namespace App\Livewire;

use App\Models\Universidad;
use Livewire\Component;
use Livewire\WithPagination;
class UniversitysTable extends Component
{
    use WithPagination;

    public $search = '';
    public function mount()
    {
    }
    public function render()
    {
        $universidades = Universidad::where('nombre_universidad', 'like', '%' . $this->search . '%')
        ->paginate(10); 
        return view('livewire.universitys-table',compact('universidades'));
    }
}
