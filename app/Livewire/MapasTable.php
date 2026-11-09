<?php

namespace App\Livewire;

use App\Models\Mapa;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Storage;

class MapasTable extends Component
{
    use WithPagination;

    protected $listeners = ['cerrarModal'];
    public $search = '';
    public $sortField = 'nombre_mapa';
    public $sortDirection = 'asc';
    public $perPage = 10;
    public $mapaSeleccionado = null;
    public $mostrarModal = false;
    public $mapaAEliminar = null;
    public $mostrarModalEliminar = false;

    protected $queryString = [
        'search' => ['except' => ''],
        'sortField' => ['except' => 'nombre_mapa'],
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
        $mapas = Mapa::with('piso')
            ->when($this->search, function ($query) {
                $query->where('nombre_mapa', 'like', '%' . $this->search . '%');
            })
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        return view('livewire.mapas-table', compact('mapas'));
    }

    public function eliminar($id)
    {
        Mapa::findOrFail($id)->delete();
        $this->resetPage();
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
            $this->resetPage();
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