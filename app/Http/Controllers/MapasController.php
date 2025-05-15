<?php
namespace App\Http\Controllers;

use App\Models\Universidad;
use App\Models\Facultad;
use App\Models\Piso;
use App\Models\Espacio;
use App\Models\Mapa;
use App\Models\Bloque;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MapasController extends Controller
{
    public function index()
    {
        $universidades = Universidad::all();
        return view('layouts.maps.map_index', compact('universidades'));
    }

    public function add()
    {
        $universidades = Universidad::all();
        return view('layouts.maps.map_add', compact('universidades'));
    }

    public function getFacultades($universidadId)
    {
        return response()->json(
            Facultad::where('id_universidad', $universidadId)->get()
        );
    }

    public function getPisos($facultadId)
    {
        return response()->json(
            Piso::where('id_facultad', $facultadId)->get()
        );
    }

    public function store(Request $request)
    {
        // Validar los datos del formulario
        $validated = $request->validate([
            'piso_id' => 'required|exists:pisos,id',
            'nombre_mapa' => 'required|string|max:255',
            'imagen' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        try {
            // Generar un ID único para el mapa
            $idMapa = Str::uuid()->toString();

            // Guardar la imagen del mapa original
            $rutaMapa = $request->file('imagen')->store('public/mapas');
            $rutaMapaPublica = Storage::url($rutaMapa);

            // Guardar la imagen del canvas si existe
            $rutaCanvas = $request->file('canvas_image')->store('public/canvas');
            $rutaCanvasPublica = Storage::url($rutaCanvas);

            // Crear el registro en la base de datos
            $mapa = new Mapa();
            $mapa->id_mapa = $idMapa;
            $mapa->nombre_mapa = $validated['nombre_mapa'];
            $mapa->ruta_mapa = $rutaMapaPublica;
            $mapa->ruta_canvas = $rutaCanvasPublica;
            $mapa->piso_id = $validated['piso_id'];
            $mapa->save();

            return response()->json([
                'success' => true,
                'message' => 'Mapa guardado correctamente',
                'id_mapa' => $idMapa
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al guardar el mapa: ' . $e->getMessage()
            ], 500);
        }
    }

    public function contarEspacios($pisoId)
    {
        $espacios = Espacio::where('piso_id', $pisoId)->get();

        return response()->json([
            'cantidad' => $espacios->count(),
            'espacios' => $espacios
        ]);
    }

}
