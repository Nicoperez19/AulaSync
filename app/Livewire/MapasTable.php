<?php

namespace App\Livewire;

use App\Models\Mapa;

use Livewire\Component;

class MapasTable extends Component
{
    public $mapas;

    public function mount()
    {
        $this->mapas = Mapa::with('piso')->get();
    }

    public function render()
    {
        return view('livewire.mapas-table');
    }

    public function eliminar($id)
    {
        Mapa::findOrFail($id)->delete();
        $this->mount(); // refrescar la lista
    }

    public function verMapa($ruta)
    {
        $rutaPublica = asset($ruta);
        $this->dispatch('mostrar-mapa', ['ruta' => $rutaPublica]);
    }
}
