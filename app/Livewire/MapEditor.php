<?php

namespace App\Livewire;

use App\Models\Mapa;
use Livewire\Component;
use App\Models\Universidad;
use App\Models\Facultad;
use App\Models\Piso;
use App\Models\Espacio;


class MapEditor extends Component
{
    public $universidades;
    public $facultades = [];
    public $pisos = [];
    public $espacios = [];
    public $selectedUniversidad;
    public $selectedFacultad;
    public $selectedPiso;
    public $mapName;

    protected $listeners = ['saveCanvasData' => 'saveMap'];

    public function mount()
    {
        $this->universidades = Universidad::all();  // Cargar universidades
    }

    public function updatedSelectedUniversidad($universidadId)
    {
        $this->facultades = Facultad::where('id_universidad', $universidadId)->get();
        $this->pisos = [];
        $this->espacios = [];
        dd($this->facultades);  // Verificar que se están cargando correctamente las facultades

    }

    public function updatedSelectedFacultad($facultadId)
    {
        $this->pisos = Piso::where('id_facultad', $facultadId)->get();
        $this->espacios = [];
    }

    public function updatedSelectedPiso($pisoId)
    {
        $this->espacios = Espacio::where('id', $pisoId)->get();
    }

    public function saveCanvasData($squares)
    {
        // Guardar mapa aquí con los datos
        $this->canvasData = $squares;

        Mapa::create([
            'nombre' => $this->mapName,
            'canvas_data' => json_encode($squares),
            'id_espacio' => $this->selectedEspacio,
        ]);

        session()->flash('message', '¡Mapa guardado con éxito!');
    }

    public function render()
    {
        return view('livewire.map-editor');
    }
}
