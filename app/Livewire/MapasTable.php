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
        try {
            $this->mapaSeleccionado = Mapa::with(['piso.facultad'])->findOrFail($id);
            $this->mostrarModal = true;
        } catch (\Exception $e) {
            // En caso de error, mostrar un mensaje o manejar la excepción
            $this->mapaSeleccionado = null;
            $this->mostrarModal = false;
        }
    }

    public function cerrarModal()
    {
        $this->mostrarModal = false;
        $this->mapaSeleccionado = null;
        $this->dispatch('modalClosed');
    }

    public function limpiarEstado()
    {
        $this->mapaSeleccionado = null;
        $this->mostrarModal = false;
    }
}
