<?php

namespace App\Livewire;

use App\Models\Mapa;
use App\Models\Facultad;
use Livewire\Component;
use Illuminate\Support\Facades\Storage;

class MapasTable extends Component
{
    protected $listeners = ['cerrarModal'];
    public $search = '';
    public $mapaSeleccionado = null;
    public $mostrarModal = false;
    public $mapaAEliminar = null;
    public $mostrarModalEliminar = false;

    protected $queryString = [
        'search' => ['except' => ''],
    ];

    /**
     * Se mantiene por compatibilidad: la lista se vuelve a consultar en cada render().
     */
    public function mount()
    {
        // Nada que precargar: render() consulta los mapas con el filtro vigente.
    }

    public function render()
    {
        $searchTerm = trim($this->search);

        $mapas = Mapa::with('piso')
            ->when($searchTerm !== '', function ($query) use ($searchTerm) {
                // Las facultades viven en la base central: se resuelven sus IDs aparte
                $facultadIds = Facultad::where('nombre_facultad', 'like', '%' . $searchTerm . '%')
                    ->pluck('id_facultad')
                    ->all();

                $query->where(function ($q) use ($searchTerm, $facultadIds) {
                    $q->where('nombre_mapa', 'like', '%' . $searchTerm . '%')
                      ->orWhere('id_mapa', 'like', '%' . $searchTerm . '%')
                      ->orWhereHas('piso', function ($pq) use ($searchTerm, $facultadIds) {
                          $pq->where(function ($sub) use ($searchTerm, $facultadIds) {
                              $sub->where('numero_piso', 'like', '%' . $searchTerm . '%')
                                  ->orWhere('nombre_piso', 'like', '%' . $searchTerm . '%');

                              if (!empty($facultadIds)) {
                                  $sub->orWhereIn('id_facultad', $facultadIds);
                              }
                          });
                      });
                });
            })
            ->orderBy('nombre_mapa')
            ->get();

        return view('livewire.mapas-table', compact('mapas'));
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
