<?php

namespace App\Livewire;

use App\Models\Mapa;
use Livewire\Component;
use Illuminate\Support\Facades\Storage;

class MapasTable extends Component
{
    protected $listeners = ['cerrarModal'];
    public $mapas;
    public $mapaSeleccionado = null;
    public $mostrarModal = false;
    public $mapaAEliminar = null;
    public $mostrarModalEliminar = false;

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

    /**
     * Preparar y mostrar modal de confirmación
     */
    public function confirmarEliminarMapa($id)
    {
        $this->mapaAEliminar = Mapa::find($id);
        if ($this->mapaAEliminar) {
            $this->mostrarModalEliminar = true;
        } else {
            $this->dispatch('notify', ['type' => 'error', 'message' => 'Mapa no encontrado']);
        }
    }

    /**
     * Elimina el mapa seleccionado (invocado desde el modal)
     */
    public function eliminarMapa()
    {
        if (!$this->mapaAEliminar) return;

        try {
            $ruta = $this->mapaAEliminar->ruta_mapa ?? null;
            $this->mapaAEliminar->delete();

            if ($ruta) {
                Storage::disk('public')->delete($ruta);
            }

            $this->mostrarModalEliminar = false;
            $this->mapaAEliminar = null;
            $this->mount();
            $this->dispatch('notify', ['type' => 'success', 'message' => 'Mapa eliminado correctamente']);
        } catch (\Exception $e) {
            $this->dispatch('notify', ['type' => 'error', 'message' => $e->getMessage()]);
        }
    }

    public function cerrarEliminarModal()
    {
        $this->mostrarModalEliminar = false;
        $this->mapaAEliminar = null;
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
