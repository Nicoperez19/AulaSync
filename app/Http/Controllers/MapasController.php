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
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $mapas = Mapa::with(['piso.espacios'])->latest()->get();

        return view('layouts.maps.map_index', compact('mapas'));
    }

    public function add()
    {
        $universidades = Universidad::all();
        return view('layouts.maps.map_add', compact('universidades'));
    }

    public function store(Request $request)
    {
        try {
            Log::info('Datos recibidos en store:', $request->all());

            $request->validate([
                'nombre_mapa' => 'required|string|max:255',
                'archivo' => 'required|file|mimes:jpeg,png,jpg,gif,pdf|max:10240',
                'id_espacio' => 'required|exists:pisos,id',
                'bloques' => 'required|string'
            ]);

            // Decodificar los bloques
            $bloques = json_decode($request->bloques, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \Exception('Error al decodificar los bloques: ' . json_last_error_msg());
            }

            // Log de las posiciones de los bloques
            Log::info('Posiciones de los bloques:', $bloques);

            // Validar la estructura de los bloques
            foreach ($bloques as $bloque) {
                if (
                    !isset($bloque['id_espacio']) || !isset($bloque['posicion_x']) ||
                    !isset($bloque['posicion_y']) || !isset($bloque['estado'])
                ) {
                    throw new \Exception('Estructura de bloques inválida');
                }
            }

            $file = $request->file('archivo');
            $nombreMapaSlug = Str::slug($request->nombre_mapa);
            $extension = $file->getClientOriginalExtension();

            $fileName = "{$nombreMapaSlug}_" . date('Y-m-d_His') . "." . $extension;
            $path = $file->storeAs('mapas_subidos', $fileName, 'public');

            // Crear el mapa usando el nombre completo como id_mapa
            $mapa = Mapa::create([
                'id_mapa' => $request->nombre_mapa,
                'nombre_mapa' => $request->nombre_mapa,
                'ruta_mapa' => $path,
                'ruta_canvas' => $path, // Por ahora usamos la misma imagen para el canvas
                'piso_id' => $request->id_espacio
            ]);

            // Guardar los bloques
            foreach ($bloques as $bloque) {
                Bloque::create([
                    'id_bloque' => Str::uuid(),
                    'id_mapa' => $mapa->id_mapa,
                    'id_espacio' => $bloque['id_espacio'],
                    'posicion_x' => $bloque['posicion_x'],
                    'posicion_y' => $bloque['posicion_y'],
                    'estado' => $bloque['estado']
                ]);
            }

            return redirect()->route('mapas.index')
                ->with('success', 'Mapa guardado exitosamente.');

        } catch (\Exception $e) {
            Log::error('Error al guardar mapa: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            return back()->withErrors(['error' => 'Error al guardar el mapa: ' . $e->getMessage()]);
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

    public function getEspaciosPorPiso($pisoId)
    {
        try {
            Log::info('Obteniendo espacios para piso:', ['piso_id' => $pisoId]);

            // Primero verificamos si el piso existe
            $piso = Piso::find($pisoId);
            if (!$piso) {
                return response()->json(['error' => 'Piso no encontrado'], 404);
            }

            // Obtenemos los espacios con las columnas que existen
            $espacios = Espacio::select('id_espacio', 'nombre_espacio')
                ->where('piso_id', $pisoId)
                ->get();

            Log::info('Espacios encontrados:', ['espacios' => $espacios->toArray()]);

            if ($espacios->isEmpty()) {
                return response()->json([
                    'message' => 'No hay espacios disponibles para este piso',
                    'espacios' => []
                ], 200);
            }

            return response()->json($espacios);
        } catch (\Exception $e) {
            Log::error('Error al obtener espacios:', [
                'id_piso' => $pisoId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['error' => 'Error al obtener los espacios: ' . $e->getMessage()], 500);
        }
    }

    public function getBloquesPorMapa($mapaId)
    {
        try {
            Log::info('Obteniendo bloques para mapa:', ['mapa_id' => $mapaId]);

            $bloques = Bloque::with('espacio')
                ->where('id_mapa', $mapaId)
                ->get();

            Log::info('Bloques encontrados:', ['bloques' => $bloques->toArray()]);

            return response()->json($bloques);
        } catch (\Exception $e) {
            Log::error('Error al obtener bloques:', [
                'mapa_id' => $mapaId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['error' => 'Error al obtener los bloques: ' . $e->getMessage()], 500);
        }
    }

    public function updateEstadoBloque(Request $request, $bloqueId)
    {
        try {
            $request->validate([
                'estado' => 'required|boolean'
            ]);

            $bloque = Bloque::findOrFail($bloqueId);
            $bloque->estado = $request->estado;
            $bloque->save();

            return response()->json(['success' => true, 'bloque' => $bloque]);
        } catch (\Exception $e) {
            Log::error('Error al actualizar estado del bloque:', [
                'bloque_id' => $bloqueId,
                'error' => $e->getMessage()
            ]);
            return response()->json(['error' => 'Error al actualizar el estado del bloque'], 500);
        }
    }
}
