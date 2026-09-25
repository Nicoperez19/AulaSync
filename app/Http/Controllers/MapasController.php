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

    public function edit($id)
    {
        $mapa = Mapa::withoutGlobalScopes()->with('bloques.espacio')->findOrFail($id);
        $pisos = Piso::all();

        // Obtener sede y facultad del tenant actual
        $tenant = \App\Models\Tenant::current();
        $sede = $tenant ? Sede::find($tenant->sede_id) : null;
        


        $facultad = $sede ? Facultad::where('id_sede', $sede->id_sede)->first() : null;
        


        return view('mapas.edit', compact('mapa', 'pisos', 'sede', 'facultad'));
    }
    public function update(Request $request, $id)
    {
        try {

            
            $mapa = Mapa::withoutGlobalScopes()->findOrFail($id);
            $request->validate([
                'nombre_mapa' => 'required|string|max:255',
                'piso_id' => 'required|integer',
                'bloques' => 'required|string',
                'archivo' => 'nullable|file|mimes:jpeg,png,jpg,gif,pdf|max:10240'
            ]);
            
            // Validar manualmente que el piso exista en la base de datos tenant
            if (!Piso::where('id', $request->piso_id)->exists()) {
                return back()->withErrors(['piso_id' => 'El piso seleccionado no existe.'])->withInput();
            }

            $mapa->nombre_mapa = $request->nombre_mapa;
            $mapa->piso_id = $request->piso_id;

            // Si se sube una nueva imagen, reemplazar la anterior
            if ($request->hasFile('archivo')) {
                $file = $request->file('archivo');
                $nombreMapaSlug = Str::slug($request->nombre_mapa);
                $extension = $file->getClientOriginalExtension();
                $fileName = "{$nombreMapaSlug}.{$extension}";
                $path = $file->storeAs('mapas_subidos', $fileName, 'public');
                $mapa->ruta_mapa = $path;
                $mapa->ruta_canvas = $path;
            }

            $mapa->save();
            


            $bloques = json_decode($request->bloques, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \Exception('Error al decodificar los bloques: ' . json_last_error_msg());
            }

            $mapa->bloques()->delete();

            
            foreach ($bloques as $index => $bloque) {
                try {
                    Bloque::create([
                        'id_bloque' => Str::uuid(),
                        'id_mapa' => $mapa->id_mapa,
                        'id_espacio' => $bloque['id_espacio'],
                        'posicion_x' => $bloque['posicion_x'],
                        'posicion_y' => $bloque['posicion_y'],
                        'estado' => $bloque['estado']
                    ]);
                } catch (\Exception $bloqueError) {
                    Log::error("Error al actualizar bloque #{$index}:", [
                        'error' => $bloqueError->getMessage(),
                        'bloque' => $bloque
                    ]);
                    throw $bloqueError;
                }
            }
            


            return redirect()->route('mapas.index')
                ->with('success', 'Mapa actualizado exitosamente.');
        } catch (\Exception $e) {
            \Log::error('Error al actualizar mapa: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Error al actualizar el mapa: ' . $e->getMessage()]);
        }
    }

    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $mapas = Mapa::withoutGlobalScopes()->with(['piso.espacios'])->latest()->get();

        return view('mapas.index', compact('mapas'));
    }

        public function add()
    {
        $universidades = Universidad::all();

        // Obtener sede y facultad del tenant actual
        $tenant = \App\Models\Tenant::current();
        $sede = $tenant ? Sede::find($tenant->sede_id) : null;
        


        $facultad = $sede ? Facultad::where('id_sede', $sede->id_sede)->first() : null;
        


        return view('mapas.create', compact('universidades', 'sede', 'facultad'));
    }

    public function create()
    {
        return $this->add();
    }

    public function store(Request $request)
    {
        try {


            $request->validate([
                'nombre_mapa' => 'required|string|max:255',
                'archivo' => 'required|file|mimes:jpeg,png,jpg,gif,pdf|max:10240',
                'piso_id' => 'required|integer',
                'bloques' => 'required|string'
            ]);
            
            // Validar manualmente que el piso exista en la base de datos tenant
            if (!Piso::where('id', $request->piso_id)->exists()) {
                return back()->withErrors(['piso_id' => 'El piso seleccionado no existe.'])->withInput();
            }

            // Decodificar los bloques
            $bloques = json_decode($request->bloques, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \Exception('Error al decodificar los bloques: ' . json_last_error_msg());
            }



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

            $fileName = "{$nombreMapaSlug}.{$extension}";
            

            
            // Guardar el archivo
            try {
                $content = file_get_contents($file->getRealPath());
                $filePath = 'mapas_subidos/' . $fileName;
                
                Storage::disk('public')->put($filePath, $content);
                
                // Verificar si el archivo se guardó correctamente
                if (Storage::disk('public')->exists($filePath)) {
                    $path = $filePath;


                } else {
                    $path = false;
                    Log::error('Archivo no existe después de guardarlo', ['path' => $filePath]);
                }
            } catch (\Exception $e) {
                Log::error('Error guardando archivo:', ['error' => $e->getMessage()]);
                $path = false;
            }



            // Generar ID único basado en slug + timestamp para evitar duplicados
            $slugBase = Str::slug($request->nombre_mapa);
            $idMapa = $slugBase . '-' . time();
            

            
            // Crear mapa sin global scopes para evitar conflictos
            $mapa = Mapa::withoutGlobalScopes()->create([
                'id_mapa' => $idMapa,
                'nombre_mapa' => $request->nombre_mapa,
                'ruta_mapa' => $path,
                'ruta_canvas' => $path,
                'piso_id' => $request->piso_id
            ]);
            


            foreach ($bloques as $index => $bloque) {
                try {

                    
                    Bloque::create([
                        'id_bloque' => Str::uuid(),
                        'id_mapa' => $mapa->id_mapa,
                        'id_espacio' => $bloque['id_espacio'],
                        'posicion_x' => $bloque['posicion_x'],
                        'posicion_y' => $bloque['posicion_y'],
                        'estado' => $bloque['estado']
                    ]);
                    

                } catch (\Exception $bloqueError) {
                    Log::error("Error al crear bloque #{$index}:", [
                        'error' => $bloqueError->getMessage(),
                        'bloque' => $bloque
                    ]);
                    throw $bloqueError;
                }
            }

            // Si viene desde el wizard de inicialización, redirigir de vuelta al wizard
            if ($request->has('redirect_to_init') && $request->redirect_to_init) {
                return redirect()->route('tenant.initialization.index')
                    ->with('success', 'Mapa guardado exitosamente.');
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
            $sedes = Sede::where('id_universidad', $universidadId)->get();

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
            $facultades = Facultad::where('id_sede', $sedeId)->get();

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
            // Auto-curación para Los Ángeles: asegurar que los edificios tengan sus nombres oficiales
            if ($facultadId === 'IT_LA') {
                Piso::where('id_facultad', 'IT_LA')->where('numero_piso', 1)->where(function($q) {
                    $q->whereNull('nombre_piso')->orWhere('nombre_piso', 'Piso 1')->orWhere('nombre_piso', 'LIKE', '%1er%');
                })->update(['nombre_piso' => 'CAUPOLICÁN 276']);

                Piso::where('id_facultad', 'IT_LA')->where('numero_piso', 2)->where(function($q) {
                    $q->whereNull('nombre_piso')->orWhere('nombre_piso', 'Piso 2');
                })->update(['nombre_piso' => 'VILLAGRÁN 220']);

                Piso::where('id_facultad', 'IT_LA')->where('numero_piso', 3)->where(function($q) {
                    $q->whereNull('nombre_piso')->orWhere('nombre_piso', 'Piso 3')->orWhere('nombre_piso', 'NOT LIKE', '%251%');
                })->update(['nombre_piso' => 'VILLAGRÁN 251']);

                // Auto-poblar espacios de Villagrán 251 si no existen en la base de datos
                $piso251 = Piso::where('id_facultad', 'IT_LA')->where('numero_piso', 3)->first();
                if ($piso251 && Espacio::where('id_espacio', 'LIKE', 'LA-4%')->count() === 0) {
                    $file = database_path('seeders/Data/Espacios/LA.php');
                    if (file_exists($file)) {
                        $todos = require $file;
                        foreach ($todos as $e) {
                            if (!Espacio::where('id_espacio', $e['id_espacio'])->exists()) {
                                if (str_starts_with($e['id_espacio'], 'LA-4') || in_array($e['piso_id'] ?? null, [12, 13])) {
                                    $e['piso_id'] = $piso251->id;
                                }
                                $e['capacidad_maxima'] = $e['capacidad_maxima'] ?? $e['puestos_disponibles'] ?? 0;
                                $e['created_at'] = now();
                                $e['updated_at'] = now();
                                Espacio::insert($e);
                            }
                        }
                    }
                }
            }

            $pisos = Piso::where('id_facultad', $facultadId)->orderBy('numero_piso')->get();

            // Garantizar que la colección siempre lleve el nombre correcto
            if ($facultadId === 'IT_LA') {
                $pisos->transform(function ($piso) {
                    if ($piso->numero_piso == 1 && (empty($piso->nombre_piso) || $piso->nombre_piso === 'Piso 1')) {
                        $piso->nombre_piso = 'CAUPOLICÁN 276';
                    } elseif ($piso->numero_piso == 2 && (empty($piso->nombre_piso) || $piso->nombre_piso === 'Piso 2')) {
                        $piso->nombre_piso = 'VILLAGRÁN 220';
                    } elseif ($piso->numero_piso == 3 && (empty($piso->nombre_piso) || $piso->nombre_piso === 'Piso 3' || !str_contains($piso->nombre_piso, '251'))) {
                        $piso->nombre_piso = 'VILLAGRÁN 251';
                    }
                    return $piso;
                });
            }

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
            $piso = Piso::withoutGlobalScopes()->find($pisoId);
            $nombrePiso = strtoupper($piso->nombre_piso ?? '');
            $numeroPiso = $piso->numero_piso ?? null;
            $idFacultad = $piso->id_facultad ?? null;

            $query = Espacio::withoutGlobalScopes()
                ->select('id_espacio', 'nombre_espacio');

            if (str_contains($nombrePiso, '251') || ($idFacultad === 'IT_LA' && $numeroPiso == 3) || $pisoId == 12 || $pisoId == 13) {
                $query->where(function ($q) use ($pisoId) {
                    $q->where('piso_id', $pisoId)
                      ->orWhere('id_espacio', 'LIKE', 'LA-4%');
                });
            } elseif (str_contains($nombrePiso, '220') || ($idFacultad === 'IT_LA' && $numeroPiso == 2) || $pisoId == 10 || $pisoId == 11) {
                $query->where(function ($q) use ($pisoId) {
                    $q->where('piso_id', $pisoId)
                      ->orWhere('id_espacio', 'LIKE', 'LA-2%')
                      ->orWhere('id_espacio', 'LIKE', 'LA-C%');
                });
            } elseif (str_contains($nombrePiso, 'CAUPOLICÁN') || str_contains($nombrePiso, 'CAUPOLICAN') || ($idFacultad === 'IT_LA' && $numeroPiso == 1) || $pisoId == 8 || $pisoId == 9) {
                $query->where(function ($q) use ($pisoId) {
                    $q->where('piso_id', $pisoId)
                      ->orWhere('id_espacio', 'LIKE', 'LA-0%')
                      ->orWhere('id_espacio', 'LIKE', 'LA-1%')
                      ->orWhere('id_espacio', 'LA-LAB');
                });
            } else {
                $pisoIds = [$pisoId];
                if ($pisoId == 8) $pisoIds = [8, 9];
                elseif ($pisoId == 10) $pisoIds = [10, 11];
                elseif ($pisoId == 12) $pisoIds = [12, 13];

                $query->whereIn('piso_id', $pisoIds);
            }

            $espacios = $query->orderBy('nombre_espacio')->get();

            if ($espacios->isEmpty()) {
                return response()->json([]);
            }

            return response()->json($espacios);
        } catch (\Exception $e) {
            Log::error('Error al obtener espacios:', [
                'id_piso' => $pisoId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([]);
        }
    }

    public function getEspaciosPorFacultad($facultadId)
    {
        try {


            // Obtener todos los pisos de la facultad
            $pisos = Piso::where('id_facultad', $facultadId)->pluck('id');

            if ($pisos->isEmpty()) {
                return response()->json([
                    'message' => 'No hay pisos disponibles para esta facultad',
                    'espacios' => []
                ], 200);
            }

            // Obtener todos los espacios de esos pisos
            $espacios = Espacio::select('id_espacio', 'nombre_espacio')
                ->whereIn('piso_id', $pisos)
                ->get();



            return response()->json($espacios);
        } catch (\Exception $e) {
            Log::error('Error al obtener espacios por facultad:', [
                'id_facultad' => $facultadId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['error' => 'Error al obtener los espacios: ' . $e->getMessage()], 500);
        }
    }

    public function getBloquesPorMapa($mapaId)
    {
        try {


            $bloques = Bloque::with('espacio')
                ->where('id_mapa', $mapaId)
                ->get();



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

    public function getBloques($mapaId)
    {
        try {
            $bloques = Bloque::with('espacio')
                ->where('id_mapa', $mapaId)
                ->get();


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

