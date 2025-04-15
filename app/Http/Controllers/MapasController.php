<?php

namespace App\Http\Controllers;

use App\Models\Mapa;
use App\Models\Universidad;
use App\Models\Facultad;
use App\Models\Piso;
use App\Models\Espacio;
use App\Models\Bloque;
use Illuminate\Http\Request;

class MapasController extends Controller
{
    public function index()
    {
        $universidades = Universidad::all();
        return view('layouts.maps.map_index', compact('universidades'));
    }
    public function create()
    {
        $universidades = Universidad::all(); // Si necesitas pasar info
        return view('layouts.maps.map_add', compact('universidades'));
    }


    public function getFacultades($universidadId)
    {
        $facultades = Facultad::where('id_universidad', $universidadId)->get();
        return response()->json($facultades);
    }

    public function getPisos($facultadId)
    {
        $pisos = Piso::where('id_facultad', $facultadId)->get();
        return response()->json($pisos);
    }

    public function getEspacios($pisoId)
    {
        $espacios = Espacio::where('id_piso', $pisoId)->get();
        return response()->json($espacios);
    }

    public function saveMap(Request $request)
    {
        $request->validate([
            'piso_id' => 'required|exists:pisos,id_piso',
            'bloques' => 'required|array'
        ]);

        Bloque::where('id_mapa', $request->piso_id)->delete();

        // Guardar nuevos bloques
        foreach ($request->bloques as $bloque) {
            Bloque::create([
                'id_mapa' => $request->piso_id,
                'pos_x' => $bloque['x'],
                'pos_y' => $bloque['y'],
                'ancho' => $bloque['width'],
                'alto' => $bloque['height'],
                'color_bloque' => $bloque['color'],
                'id_espacio' => $bloque['espacioId']
            ]);
        }

        return response()->json(['success' => true]);
    }

    public function loadMap($pisoId)
    {
        $bloques = Bloque::where('id_mapa', $pisoId)->get();
        return response()->json($bloques);
    }

    public function store(Request $request)
{
    $request->validate([
        'id_mapa' => 'required|string|unique:mapas,id_mapa',
        'nombre_mapa' => 'required|string|max:255',
        'id_espacio' => 'required|exists:espacios,id_espacio',
        'archivo_mapa' => 'required|file|mimes:jpg,jpeg,png,pdf|max:2048',
    ]);

    // Guardar archivo
    $ruta = $request->file('archivo_mapa')->store("mapas/{$request->id_espacio}", 'public');

    // Crear registro en DB
    Mapa::create([
        'id_mapa' => $request->id_mapa,
        'nombre_mapa' => $request->nombre_mapa,
        'ruta_mapa' => $ruta,
        'id_espacio' => $request->id_espacio,
    ]);

    return redirect()->route('mapas.index')->with('success', 'Mapa guardado correctamente.');
}

}