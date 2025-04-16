<?php
namespace App\Http\Controllers;

use App\Models\Universidad;
use App\Models\Facultad;
use App\Models\Piso;
use App\Models\Mapa;
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
        // Validar los campos del formulario
        $request->validate([
            'id_universidad' => 'required',
            'id_facultad' => 'required',
            'piso_id' => 'required',
            'nombre_mapa' => 'required',
            'imagen' => 'required|image|max:2048', // Validar que sea una imagen y con tamaño máximo
        ]);

        // Si el campo de imagen tiene un archivo
        if ($request->hasFile('imagen')) {
            // Obtener el archivo de imagen
            $image = $request->file('imagen');
            // Generar un nombre único para la imagen
            $imageName = time() . '.' . $image->getClientOriginalExtension();
            // Definir la ruta donde se guardará la imagen
            $storagePath = 'images/mapas/' . $imageName;
            // Guardar la imagen en la carpeta "public/images/mapas"
            $image->storeAs('public/images/mapas', $imageName);

            // Crear el mapa en la base de datos con la ruta de la imagen
            $mapa = new Mapas();
            $mapa->id_universidad = $request->id_universidad;
            $mapa->id_facultad = $request->id_facultad;
            $mapa->piso_id = $request->piso_id;
            $mapa->nombre_mapa = $request->nombre_mapa;
            $mapa->ruta_mapa = $storagePath;
            $mapa->save();

            // Redirigir al índice de mapas con un mensaje de éxito
            return redirect()->route('mapas.index')->with('success', 'Mapa cargado exitosamente.');
        }

        // Si no se encuentra una imagen, redirigir con un mensaje de error
        return back()->with('error', 'Por favor, cargue una imagen.');
    }
}
