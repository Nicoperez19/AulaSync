<?php

namespace App\Livewire;

use App\Models\Mapa;
use Livewire\Component;

class MapasTable extends Component
{
    public $mapas;
    public $mapaSeleccionado = null;
    public $mostrarModal = false;

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

    public function verMapa($id)
    {
        $this->mapaSeleccionado = Mapa::with(['bloques.espacio', 'piso'])->findOrFail($id);
        $this->mostrarModal = true;
    }

    public function cerrarModal()
    {
        $this->mostrarModal = false;
        $this->mapaSeleccionado = null;
    }
}
