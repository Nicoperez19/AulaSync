<?php

namespace App\Http\Controllers;

use App\Exports\IncidenciasEspaciosExport;
use App\Http\Requests\ReportarIncidenciaEspacioRequest;
use App\Models\Espacio;
use App\Models\IncidenciaEspacio;
use App\Services\IncidenciaEspacioService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Facades\Excel;

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
            // Filtros activos que viajan a la exportación (Excel/PDF) para que coincida con lo que se ve.
            'exportParams' => array_filter($filtros, fn ($v) => $v !== null && $v !== ''),
            'categorias' => IncidenciaEspacio::CATEGORIAS,
            'severidades' => IncidenciaEspacio::SEVERIDADES,
            'estados' => IncidenciaEspacio::ESTADOS,
            'espacios' => Espacio::orderBy('id_espacio')->get(['id_espacio', 'nombre_espacio', 'estado']),
        ]);
    }

    /**
     * Exportar el reporte de incidencias a Excel o PDF, respetando los filtros del tablero.
     */
    public function export(Request $request, string $format)
    {
        $format = strtolower($format);

        if (! in_array($format, ['excel', 'xlsx', 'pdf'], true)) {
            abort(404, 'Formato no soportado');
        }

        $filtros = $request->only(['estado', 'categoria', 'severidad', 'espacio', 'desde', 'hasta', 'buscar']);
        $query = $this->incidencias->consultaFiltrada($filtros);
        $nombre = 'incidencias_espacios_' . now()->format('Y-m-d_His');

        if ($format !== 'pdf') {
            return Excel::download(new IncidenciasEspaciosExport($query), "{$nombre}.xlsx");
        }

        $limite = 500;
        $total = (clone $query)->reorder()->count();

        $porEstado = (clone $query)->reorder()
            ->select('estado', \Illuminate\Support\Facades\DB::raw('COUNT(*) as total'))
            ->groupBy('estado')
            ->pluck('total', 'estado');

        $resumen = [
            'abiertas' => (int) ($porEstado['abierta'] ?? 0),
            'en_proceso' => (int) ($porEstado['en_proceso'] ?? 0),
            'resueltas' => (int) ($porEstado['resuelta'] ?? 0),
            'descartadas' => (int) ($porEstado['descartada'] ?? 0),
            'bloqueantes' => (clone $query)->reorder()->where('bloquea_uso', true)->count(),
        ];

        $pdf = Pdf::loadView('pdf.incidencias-espacios', [
            'incidencias' => $query->limit($limite)->get(),
            'resumen' => $resumen,
            'truncado' => $total > $limite,
            'limite' => $limite,
            'descripcionFiltros' => $this->describirFiltros($filtros),
        ]);
        $pdf->setPaper('a4', 'landscape');

        return $pdf->download("{$nombre}.pdf");
    }

    /**
     * Texto legible con los filtros aplicados (para el encabezado del PDF).
     */
    protected function describirFiltros(array $filtros): string
    {
        $partes = [];

        if (! empty($filtros['estado'])) {
            $partes[] = 'Estado: ' . ($filtros['estado'] === 'pendientes'
                ? 'Pendientes (abiertas y en proceso)'
                : (IncidenciaEspacio::ESTADOS[$filtros['estado']] ?? $filtros['estado']));
        }
        if (! empty($filtros['categoria'])) {
            $partes[] = 'Falla: ' . (IncidenciaEspacio::CATEGORIAS[$filtros['categoria']]['label'] ?? $filtros['categoria']);
        }
        if (! empty($filtros['severidad'])) {
            $partes[] = 'Severidad: ' . (IncidenciaEspacio::SEVERIDADES[$filtros['severidad']] ?? $filtros['severidad']);
        }
        if (! empty($filtros['espacio'])) {
            $partes[] = 'Espacio: ' . $filtros['espacio'];
        }
        if (! empty($filtros['desde'])) {
            $partes[] = 'Desde: ' . $filtros['desde'];
        }
        if (! empty($filtros['hasta'])) {
            $partes[] = 'Hasta: ' . $filtros['hasta'];
        }
        if (! empty($filtros['buscar'])) {
            $partes[] = 'Búsqueda: "' . $filtros['buscar'] . '"';
        }

        return implode(' · ', $partes);
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
