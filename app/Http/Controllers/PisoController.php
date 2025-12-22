<?php
namespace App\Http\Controllers;

use App\Models\Piso;
use App\Models\Facultad;
use App\Models\Universidad;
use App\Models\Espacio;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PisoController extends Controller
{
    public function index(Request $request)
    {
        $universidadId = $request->input('universidad');
        $facultades = Facultad::when($universidadId, function ($query, $universidadId) {
            return $query->where('id_universidad', $universidadId);
        })->withCount('pisos')->get();

        return view('layouts.floors.floors_index', [
            'universidades' => Universidad::all(),
            'facultades' => $facultades,
        ]);
    }

    public function agregarPiso(Request $request, $facultadId)
    {
        try {
            $facultad = Facultad::findOrFail($facultadId);

            $ultimoPiso = Piso::where('id_facultad', $facultad->id_facultad)
                ->orderBy('numero_piso', 'desc')
                ->first();

            $nuevoNumeroPiso = $ultimoPiso ? $ultimoPiso->numero_piso + 1 : 1;

            $piso = new Piso();
            $piso->numero_piso = $nuevoNumeroPiso;
            $piso->id_facultad = $facultad->id_facultad;
            $piso->save();

            return redirect()->route('floors_index', [
                'universidad' => $request->input('universidad')
            ])->with('success', 'Piso agregado exitosamente.');
        } catch (\Exception $e) {
            return redirect()->route('floors_index', [
                'universidad' => $request->input('universidad')
            ])->with('error', 'Error al agregar el piso: ' . $e->getMessage());
        }
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'numero_piso' => 'required|integer',
                'nombre_piso' => 'required|string|max:255',
                'id_facultad' => 'required|exists:facultades,id_facultad'
            ]);

            // Verificar si ya existe un piso con ese número en la misma facultad
            $pisoExistente = Piso::where('id_facultad', $validated['id_facultad'])
                ->where('numero_piso', $validated['numero_piso'])
                ->first();

            if ($pisoExistente) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ya existe un piso con ese número en esta facultad'
                ], 422);
            }

            $piso = Piso::create([
                'numero_piso' => $validated['numero_piso'],
                'nombre_piso' => $validated['nombre_piso'],
                'id_facultad' => $validated['id_facultad']
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Piso creado exitosamente',
                'piso' => [
                    'id' => $piso->id_piso,
                    'numero_piso' => $piso->numero_piso,
                    'nombre_piso' => $piso->nombre_piso
                ]
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error de validación',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al crear el piso: ' . $e->getMessage()
            ], 500);
        }
    }

    public function eliminarPiso(Request $request, $facultadId)
    {
        try {
            $facultad = Facultad::findOrFail($facultadId);

            $ultimoPiso = Piso::where('id_facultad', $facultadId)->latest('numero_piso')->first();

            if ($ultimoPiso) {
                $ultimoPiso->delete();
                return redirect()->route('floors_index', [
                    'universidad' => $request->input('universidad')
                ])->with('success', 'Último piso eliminado exitosamente.');
            }

            return redirect()->route('floors_index', [
                'universidad' => $request->input('universidad')
            ])->with('error', 'No hay pisos para eliminar en esta facultad.');

        } catch (\Exception $e) {
            return redirect()->route('floors_index', [
                'universidad' => $request->input('universidad')
            ])->with('error', 'Error al eliminar el piso: ' . $e->getMessage());
        }
    }

    /**
     * Obtiene todos los pisos de una facultad (para el wizard)
     *
     * @param int $facultadId
     * @return \Illuminate\Http\JsonResponse
     */
    public function getPisos($facultadId)
    {
        try {
            Log::info('getPisos llamado con facultadId: ' . $facultadId);

            // Usar DB directo para evitar problemas con global scopes en contexto tenant
            $pisos = \DB::connection('tenant')
                ->table('pisos')
                ->where('id_facultad', $facultadId)
                ->orderBy('numero_piso')
                ->get(['id', 'numero_piso', 'nombre_piso']);

            Log::info('Pisos encontrados: ' . count($pisos));

            return response()->json($pisos);
        } catch (\Exception $e) {
            Log::error('Error en getPisos: ' . $e->getMessage());
            return response()->json([
                'error' => 'Error al obtener pisos: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtiene todos los espacios de un piso (para el wizard)
     *
     * @param int $pisoId
     * @return \Illuminate\Http\JsonResponse
     */
    public function getEspaciosPorPiso($pisoId)
    {
        try {
            Log::info('getEspaciosPorPiso llamado con pisoId: ' . $pisoId);

            // Usar DB directo para evitar problemas con global scopes en contexto tenant
            $espacios = \DB::connection('tenant')
                ->table('espacios')
                ->where('piso_id', $pisoId)
                ->orderBy('nombre_espacio')
                ->get(['id_espacio as id', 'nombre_espacio as nombre', 'tipo_espacio as tipo', 'puestos_disponibles as capacidad']);

            Log::info('Espacios encontrados para piso ' . $pisoId . ': ' . count($espacios));

            // Retornar solo los espacios del piso seleccionado (sin fallback)
            // Si no hay espacios, retorna array vacío - el usuario verá mensaje apropiado
            return response()->json($espacios);
        } catch (\Exception $e) {
            Log::error('Error en getEspaciosPorPiso: ' . $e->getMessage());
            return response()->json([
                'error' => 'Error al obtener espacios: ' . $e->getMessage()
            ], 500);
        }
    }
}
