<?php

namespace App\Http\Controllers;

use App\Models\Mapa;
use App\Models\Universidad;
use App\Models\Facultad;
use App\Models\Piso;
use App\Models\Espacio;
use Illuminate\Http\Request;

class MapasController extends Controller
{
    /**
     * Mostrar el formulario para agregar un nuevo mapa.
     */
    public function add()
    {
        // Cargar todas las universidades
        $universidades = Universidad::all();
        return view('layouts.maps.map_add', compact('universidades'));
    }

    /**
     * Guardar los datos del mapa (incluyendo bloques y relaciones).
     */
    public function store(Request $request)
    {
        $request->validate([
            'selectedUniversidad' => 'required',
            'selectedFacultad' => 'required',
            'selectedPiso' => 'required',
            'selectedEspacio' => 'required',
            'mapName' => 'required|string|max:255',
            'canvasData' => 'required', // Asegúrate de que canvasData esté presente
        ]);

        // Crear el mapa en la base de datos
        $mapa = Mapa::create([
            'nombre_mapa' => $request->mapName,
            'canvas_data' => json_encode($request->canvasData),
            'id_espacio' => $request->selectedEspacio,
        ]);

        session()->flash('message', '¡Mapa guardado con éxito!');

        return redirect()->route('mapas.add');
    }

    /**
     * Obtener las facultades de una universidad.
     */
    public function getFacultades($universidadId)
    {
        return Facultad::where('id_universidad', $universidadId)->get();
    }

    /**
     * Obtener los pisos de una facultad.
     */
    public function getPisos($facultadId)
    {
        return Piso::where('id_facultad', $facultadId)->get();
    }

    /**
     * Obtener los espacios de un piso.
     */
    public function getEspacios($pisoId)
    {
        return Espacio::where('id', $pisoId)->get();
    }
}
