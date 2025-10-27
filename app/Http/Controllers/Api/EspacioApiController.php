<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Espacio;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EspacioApiController extends Controller
{
    /**
     * GET /api/espacios
     * Lista todos los espacios con sus detalles
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function listarEspacios(Request $request)
    {
        try {
            // Obtener parámetros opcionales para filtrado
            $tipoEspacio = $request->query('tipo_espacio');
            $estado = $request->query('estado');
            $pisoId = $request->query('piso_id');

            $query = Espacio::with(['piso.facultad.sede']);

            // Aplicar filtros si existen
            if ($tipoEspacio) {
                $query->where('tipo_espacio', $tipoEspacio);
            }

            if ($estado) {
                $query->where('estado', $estado);
            }

            if ($pisoId) {
                $query->where('piso_id', $pisoId);
            }

            $espacios = $query->orderBy('id_espacio')->get();

            // Formatear la respuesta
            $espaciosFormateados = $espacios->map(function($espacio) {
                return [
                    'id_espacio' => $espacio->id_espacio,
                    'nombre_espacio' => $espacio->nombre_espacio,
                    'tipo_espacio' => $espacio->tipo_espacio,
                    'estado' => $espacio->estado,
                    'puestos_disponibles' => $espacio->puestos_disponibles,
                    'piso' => [
                        'id' => $espacio->piso->id ?? null,
                        'numero_piso' => $espacio->piso->numero_piso ?? null,
                    ],
                    'facultad' => [
                        'id_facultad' => $espacio->piso->facultad->id_facultad ?? null,
                        'nombre_facultad' => $espacio->piso->facultad->nombre_facultad ?? null,
                    ],
                    'sede' => [
                        'id_sede' => $espacio->piso->facultad->sede->id_sede ?? null,
                        'nombre_sede' => $espacio->piso->facultad->sede->nombre_sede ?? null,
                    ],
                ];
            });

            return response()->json([
                'success' => true,
                'total' => $espacios->count(),
                'espacios' => $espaciosFormateados
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener los espacios: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * GET /api/tipos-espacios
     * Lista todos los tipos de espacios disponibles
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function listarTiposEspacios()
    {
        try {
            // Obtener los tipos de espacios únicos desde la base de datos
            $tiposEspacios = Espacio::select('tipo_espacio')
                ->distinct()
                ->orderBy('tipo_espacio')
                ->pluck('tipo_espacio');

            // Contar cuántos espacios hay de cada tipo
            $tiposConConteo = Espacio::select('tipo_espacio', DB::raw('count(*) as total'))
                ->groupBy('tipo_espacio')
                ->orderBy('tipo_espacio')
                ->get()
                ->map(function($item) {
                    return [
                        'tipo_espacio' => $item->tipo_espacio,
                        'total_espacios' => $item->total,
                    ];
                });

            return response()->json([
                'success' => true,
                'total_tipos' => $tiposEspacios->count(),
                'tipos_espacios' => $tiposConConteo
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener los tipos de espacios: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * GET /api/espacios/resumen
     * Obtiene un resumen con espacios agrupados por tipo
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function resumenEspacios()
    {
        try {
            $resumen = Espacio::select(
                'tipo_espacio',
                'estado',
                DB::raw('count(*) as cantidad')
            )
            ->groupBy('tipo_espacio', 'estado')
            ->orderBy('tipo_espacio')
            ->orderBy('estado')
            ->get()
            ->groupBy('tipo_espacio')
            ->map(function($items, $tipo) {
                $estadisticas = [
                    'tipo' => $tipo,
                    'total' => $items->sum('cantidad'),
                    'por_estado' => []
                ];

                foreach ($items as $item) {
                    $estadisticas['por_estado'][$item->estado] = $item->cantidad;
                }

                return $estadisticas;
            })->values();

            return response()->json([
                'success' => true,
                'resumen' => $resumen
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener el resumen de espacios: ' . $e->getMessage()
            ], 500);
        }
    }
}
