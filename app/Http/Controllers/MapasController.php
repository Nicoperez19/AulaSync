<?php
namespace App\Http\Controllers;

use App\Models\Universidad;
use App\Models\Facultad;
use App\Models\Piso;
use App\Models\Espacio;
use App\Models\Mapa;
use App\Models\Sede;
use App\Models\Bloque;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class MapasController extends Controller
{
    public function index()
    {
        $mapas = Mapa::with(['espacio.piso.facultad.universidad'])->latest()->get();
        return view('layouts.maps.maps_index', compact('mapas'));
    }

    public function add()
    {
        $universidades = Universidad::all();
        return view('layouts.maps.map_add', compact('universidades'));
    }

     public function store(Request $request)
    {
        try {
            $request->validate([
                'nombre' => 'required|string|max:255',
                // Permitimos imágenes y PDFs, máximo 10MB
                'archivo' => 'required|file|mimes:jpeg,png,jpg,gif,pdf|max:10240',
                'id_espacio' => 'required|exists:espacios,id_espacio',
            ]);

            $file = $request->file('archivo');
            // Limpiar nombre para evitar caracteres problemáticos
            $nombreMapaSlug = Str::slug($request->nombre);
            $extension = $file->getClientOriginalExtension();

            // Nombre con formato: nombre-mapa_YYYY-MM-DD_His.extension
            $fileName = "{$nombreMapaSlug}_" . date('Y-m-d_His') . "." . $extension;

            // Guardar en storage/app/public/mapas_subidos
            $path = $file->storeAs('mapas_subidos', $fileName, 'public');

            Mapa::create([
                'nombre' => $request->nombre,
                'ruta_imagen' => $path,  // Ruta relativa dentro de storage/app/public
                'id_espacio' => $request->id_espacio
            ]);

            return redirect()->route('mapas.index')
                ->with('success', 'Mapa guardado exitosamente.');

        } catch (\Exception $e) {
            Log::error('Error al guardar mapa: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Error al guardar el mapa.']);
        }
    }

    public function getSedes($universidadId)
    {
        try {
            Log::info('Obteniendo sedes para universidad:', ['universidad_id' => $universidadId]);
            $sedes = Sede::where('id_universidad', $universidadId)->get();
            Log::info('Sedes encontradas:', ['sedes' => $sedes->toArray()]);
            return response()->json($sedes);
        } catch (\Exception $e) {
            Log::error('Error al obtener sedes:', [
                'universidad_id' => $universidadId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['error' => 'Error al obtener las sedes: ' . $e->getMessage()], 500);
        }
    }

    public function getFacultadesPorSede($sedeId)
    {
        try {
            Log::info('Obteniendo facultades para sede:', ['sede_id' => $sedeId]);
            $facultades = Facultad::where('id_sede', $sedeId)->get();
            Log::info('Facultades encontradas:', ['facultades' => $facultades->toArray()]);
            return response()->json($facultades);
        } catch (\Exception $e) {
            Log::error('Error al obtener facultades:', [
                'sede_id' => $sedeId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['error' => 'Error al obtener las facultades: ' . $e->getMessage()], 500);
        }
    }

    public function getPisos($facultadId)
    {
        try {
            Log::info('Obteniendo pisos para facultad:', ['id_facultad' => $facultadId]);
            $pisos = Piso::where('id_facultad', $facultadId)->get();
            Log::info('Pisos encontrados:', ['pisos' => $pisos->toArray()]);
            return response()->json($pisos);
        } catch (\Exception $e) {
            Log::error('Error al obtener pisos:', [
                'id_facultad' => $facultadId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['error' => 'Error al obtener los pisos: ' . $e->getMessage()], 500);
        }
    }
    
}
