<?php

namespace App\Http\Controllers;

use App\Http\Requests\ReportarIncidenciaEspacioRequest;
use App\Models\Espacio;
use App\Models\IncidenciaEspacio;
use App\Services\IncidenciaEspacioService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

/**
 * Reporte y seguimiento de fallas e incidencias en espacios.
 */
class IncidenciaEspacioController extends Controller
{
    public function __construct(protected IncidenciaEspacioService $incidencias)
    {
        $this->middleware('can:acciones rapidas');
    }

    /**
     * Tablero de incidencias (listado + filtros + indicadores).
     */
    public function index(Request $request)
    {
        $filtros = $request->only(['estado', 'categoria', 'severidad', 'espacio', 'desde', 'hasta', 'buscar']);

        // Por defecto se muestran las pendientes.
        if (! $request->has('estado')) {
            $filtros['estado'] = 'pendientes';
        }

        return view('acciones-rapidas.incidencias', [
            'incidencias' => $this->incidencias->listar($filtros),
            'stats' => $this->incidencias->estadisticas(),
            'filtros' => $filtros,
            'categorias' => IncidenciaEspacio::CATEGORIAS,
            'severidades' => IncidenciaEspacio::SEVERIDADES,
            'estados' => IncidenciaEspacio::ESTADOS,
            'espacios' => Espacio::orderBy('id_espacio')->get(['id_espacio', 'nombre_espacio', 'estado']),
        ]);
    }

    /**
     * Reportar una incidencia.
     */
    public function store(ReportarIncidenciaEspacioRequest $request): JsonResponse
    {
        try {
            $resultado = $this->incidencias->reportar($request->id_espacio, $request->validated());

            return response()->json($resultado, $resultado['status'] ?? 200);
        } catch (\Throwable $e) {
            Log::error('Error al reportar incidencia: ' . $e->getMessage());

            return response()->json(['success' => false, 'mensaje' => 'Error interno del servidor.'], 500);
        }
    }

    /**
     * Detalle + bitácora de una incidencia.
     */
    public function show(int $id): JsonResponse
    {
        $detalle = $this->incidencias->detalle($id);

        if (! $detalle) {
            return response()->json(['success' => false, 'mensaje' => 'Incidencia no encontrada'], 404);
        }

        return response()->json(['success' => true, 'incidencia' => $detalle]);
    }

    /**
     * Cambiar el estado de una incidencia (en proceso / resuelta / descartada / reabrir).
     */
    public function cambiarEstado(Request $request, int $id): JsonResponse
    {
        $data = $request->validate([
            'estado' => ['required', Rule::in(array_keys(IncidenciaEspacio::ESTADOS))],
            'nota' => 'nullable|string|max:1000',
        ]);

        $resultado = $this->incidencias->cambiarEstado($id, $data['estado'], $data['nota'] ?? null);

        return response()->json($resultado, $resultado['status'] ?? 200);
    }

    /**
     * Agregar un comentario a la bitácora.
     */
    public function comentar(Request $request, int $id): JsonResponse
    {
        $data = $request->validate(['comentario' => 'required|string|min:2|max:1000']);

        $resultado = $this->incidencias->comentar($id, $data['comentario']);

        return response()->json($resultado, $resultado['status'] ?? 200);
    }
}
