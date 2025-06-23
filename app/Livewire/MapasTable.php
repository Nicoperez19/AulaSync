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

<<<<<<< HEAD
    public function verMapa($ruta)
    {
        $rutaPublica = asset($ruta);
        $this->dispatch('mostrar-mapa', ['ruta' => $rutaPublica]);
=======
    public function verMapa($id)
    {
        $mapa = Mapa::with('bloques')->findOrFail($id);
        $bloques = $mapa->bloques->map(function($bloque) {
            return [
                'id_espacio' => $bloque->id_espacio,
                'posicion_x' => $bloque->posicion_x,
                'posicion_y' => $bloque->posicion_y
            ];
        });

        $this->dispatch('mostrar-mapa', [
            'ruta' => asset($mapa->ruta_mapa),
            'bloques' => $bloques
        ]);
>>>>>>> Nperez
    }
}
