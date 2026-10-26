<?php

namespace App\Http\Controllers;

use App\Exports\OcupacionSemanalExport;
use App\Models\OcupacionSemanalSnapshot;
use App\Services\OcupacionSemanalService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class OcupacionHistoricoController extends Controller
{
    protected OcupacionSemanalService $service;

    public function __construct(OcupacionSemanalService $service)
    {
        $this->service = $service;
    }

    /**
     * Listado histórico de semanas almacenadas
     */
    public function index(Request $request)
    {
        $this->asegurarSnapshotsSemanasCerradas();

        $tipoFilter = strtolower($request->query('tipo', 'todos'));
        $anio = $request->query('anio', now()->year);
        $periodo = $request->query('periodo');

        $query = OcupacionSemanalSnapshot::query()
            ->with('generador')
            ->where('tipo_filtro', $tipoFilter)
            ->where('anio', $anio);

        if ($periodo) {
            $query->where('periodo', $periodo);
        }

        $snapshots = $query->orderBy('semana_inicio', 'desc')->paginate(15)->withQueryString();

        $aniosDisponibles = OcupacionSemanalSnapshot::select('anio')
            ->distinct()
            ->orderBy('anio', 'desc')
            ->pluck('anio');

        if ($aniosDisponibles->isEmpty()) {
            $aniosDisponibles = collect([now()->year]);
        }

        return view('reportes.ocupacion-semanal', [
            'snapshots' => $snapshots,
            'tipoFilter' => $tipoFilter,
            'anio' => $anio,
            'periodo' => $periodo,
            'aniosDisponibles' => $aniosDisponibles,
        ]);
    }

    /**
     * Registra automáticamente los snapshots de las semanas ya finalizadas (sábado concluido)
     * del período académico actual si aún no estaban almacenadas.
     */
    protected function asegurarSnapshotsSemanasCerradas(): void
    {
        try {
            $hoy = Carbon::today();
            $lunesSemanaActual = $hoy->copy()->startOfWeek();

            // Si hoy es domingo, la semana de lunes a sábado ya cerró ayer sábado;
            // de lunes a sábado, la última semana cerrada es la anterior.
            $ultimoLunesCerrado = $hoy->dayOfWeekIso === 7
                ? $lunesSemanaActual->copy()
                : $lunesSemanaActual->copy()->subWeek();

            $periodoActivo = \App\Models\PeriodoAcademico::obtenerPeriodoActual($hoy);
            $fechaInicioRevision = $periodoActivo && $periodoActivo->fecha_inicio
                ? Carbon::parse($periodoActivo->fecha_inicio)->startOfWeek()
                : $ultimoLunesCerrado->copy()->subWeeks(5);

            // Limitar máximo a las últimas 12 semanas cerradas para mantener respuesta rápida
            $limiteMinimo = $ultimoLunesCerrado->copy()->subWeeks(11);
            if ($fechaInicioRevision->lt($limiteMinimo)) {
                $fechaInicioRevision = $limiteMinimo;
            }

            if ($fechaInicioRevision->gt($ultimoLunesCerrado)) {
                return;
            }

            $semanasRegistradas = OcupacionSemanalSnapshot::whereBetween('semana_inicio', [
                $fechaInicioRevision->format('Y-m-d'),
                $ultimoLunesCerrado->format('Y-m-d'),
            ])
                ->selectRaw('semana_inicio, count(distinct tipo_filtro) as total_tipos')
                ->groupBy('semana_inicio')
                ->get()
                ->keyBy(fn($item) => Carbon::parse($item->semana_inicio)->format('Y-m-d'));

            $cursor = $fechaInicioRevision->copy();
            while ($cursor->lte($ultimoLunesCerrado)) {
                $key = $cursor->format('Y-m-d');
                $registro = $semanasRegistradas->get($key);
                if (!$registro || (int) $registro->total_tipos < count(OcupacionSemanalService::TIPOS_FILTRO)) {
                    $this->service->guardarTodosLosTipos($cursor->copy(), false, null, 'automatico');
                }
                $cursor->addWeek();
            }
        } catch (\Throwable $e) {
            // Ignorar silenciosamente si falla auto-generación para no bloquear la vista
        }
    }

    /**
     * Exportar snapshot guardado a Excel o PDF
     */
    public function export(Request $request, $id, string $format)
    {
        $snapshot = OcupacionSemanalSnapshot::findOrFail($id);

        $datos = [
            'ocupacion' => $snapshot->datos,
            'total_espacios' => $snapshot->total_espacios,
            'semana_inicio' => $snapshot->semana_inicio->format('Y-m-d'),
            'semana_fin' => $snapshot->semana_fin->format('Y-m-d'),
            'periodo' => $snapshot->periodo,
            'anio' => $snapshot->anio,
            'semana_iso' => $snapshot->semana_iso,
            'tipo_filtro' => $snapshot->tipo_filtro,
            'resumen' => $snapshot->resumen,
        ];

        $semanaStr = $datos['semana_inicio'].'_al_'.$datos['semana_fin'];
        $tipoStr = str_replace('_', '-', $snapshot->tipo_filtro);
        $fileName = "historico_ocupacion_{$tipoStr}_{$semanaStr}";

        if (strtolower($format) === 'excel' || strtolower($format) === 'xlsx') {
            return Excel::download(
                new OcupacionSemanalExport($datos, "Semana {$snapshot->semana_iso}"),
                "{$fileName}.xlsx"
            );
        }

        if (strtolower($format) === 'pdf') {
            $pdf = Pdf::loadView('pdf.ocupacion-semanal', ['datos' => $datos]);
            $pdf->setPaper('a4', 'landscape');

            return $pdf->download("{$fileName}.pdf");
        }

        abort(404, 'Formato no soportado');
    }

    /**
     * Generar o refrescar manualmente un snapshot de una semana
     */
    public function generarManual(Request $request)
    {
        $request->validate([
            'fecha_semana' => 'required|date',
            'tipo_filtro' => 'nullable|string|in:todos,laboratorios,salas_clases,salas_estudio,todos_los_tipos',
        ]);

        $lunesDate = Carbon::parse($request->input('fecha_semana'))->startOfWeek();
        $tipo = $request->input('tipo_filtro', 'todos');
        $userId = auth()->id();

        if ($tipo === 'todos_los_tipos') {
            $this->service->guardarTodosLosTipos($lunesDate, true, $userId, 'manual');
            $msg = "Se generaron exitosamente los snapshots para todos los tipos de la semana del {$lunesDate->format('d/m/Y')}.";
        } else {
            $this->service->guardarSnapshot($lunesDate, $tipo, true, $userId, 'manual');
            $msg = "Se generó exitosamente el snapshot ({$tipo}) para la semana del {$lunesDate->format('d/m/Y')}.";
        }

        return redirect()->back()->with('success', $msg);
    }
}
