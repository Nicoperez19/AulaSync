<?php

namespace App\Http\Controllers;

use App\Exports\AuditoriaClasesExport;
use App\Models\AuditoriaClase;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class AuditoriaClaseController extends Controller
{
    /**
     * Listado principal de auditoría con filtros avanzados
     */
    public function index(Request $request)
    {
        $query = $this->buildFilteredQuery($request);

        $registros = $query->orderBy('created_at', 'desc')->paginate(20)->withQueryString();

        $accionesDisponibles = [
            'justificar' => 'Justificación Individual',
            'justificar_masivo' => 'Justificación Masiva',
            'registrar' => 'Registro Individual',
            'registrar_masivo' => 'Registro Masivo',
            'cambiar_estado' => 'Cambio de Estado',
            'reagendar' => 'Reagendamiento',
            'marcar_recuperada' => 'Marcada como Recuperada',
            'eliminar' => 'Eliminación',
        ];

        return view('reportes.auditoria-clases', [
            'registros' => $registros,
            'accionesDisponibles' => $accionesDisponibles,
            'accionSeleccionada' => $request->query('accion', ''),
            'fechaInicio' => $request->query('fecha_inicio', ''),
            'fechaFin' => $request->query('fecha_fin', ''),
            'search' => $request->query('search', ''),
        ]);
    }

    /**
     * Exportación en Excel o PDF del registro filtrado
     */
    public function export(Request $request, string $format)
    {
        $query = $this->buildFilteredQuery($request)->orderBy('created_at', 'desc');

        $fileName = 'auditoria_clases_' . now()->format('Y-m-d_His');

        if (strtolower($format) === 'excel' || strtolower($format) === 'xlsx') {
            return Excel::download(new AuditoriaClasesExport($query), "{$fileName}.xlsx");
        }

        if (strtolower($format) === 'pdf') {
            $registros = $query->limit(300)->get();
            $pdf = Pdf::loadView('pdf.auditoria-clases', ['registros' => $registros]);
            $pdf->setPaper('a4', 'landscape');
            return $pdf->download("{$fileName}.pdf");
        }

        abort(404, 'Formato no soportado');
    }

    /**
     * Retorna el historial de una clase en formato JSON para modals
     */
    public function historialAjax(Request $request, $claseId)
    {
        $query = AuditoriaClase::where('entidad_id', $claseId)
            ->with(['profesor', 'user'])
            ->orderBy('created_at', 'desc');

        $items = $query->get()->map(function ($item) {
            return [
                'id' => $item->id,
                'fecha_hora' => $item->created_at->format('d/m/Y H:i:s'),
                'usuario' => $item->user_nombre,
                'rol' => $item->user_rol ?? 'Usuario',
                'accion' => ucfirst(str_replace('_', ' ', $item->accion)),
                'estado_anterior' => $item->estado_anterior ?? '—',
                'estado_nuevo' => $item->estado_nuevo ?? '—',
                'motivo' => $item->motivo,
                'observaciones' => $item->observaciones,
                'ip' => $item->ip,
            ];
        });

        return response()->json([
            'success' => true,
            'historial' => $items,
        ]);
    }

    /**
     * Construye la consulta filtrada común para listado y exports
     */
    protected function buildFilteredQuery(Request $request)
    {
        $query = AuditoriaClase::query()->with(['profesor', 'asignatura', 'espacio']);

        if ($request->filled('accion')) {
            $accion = $request->query('accion');
            if ($accion === 'justificar') {
                $query->where(function ($q) {
                    $q->where('accion', 'justificar')
                      ->orWhere('accion', 'justificar_masivo')
                      ->orWhere('accion', 'like', '%justific%');
                });
            } elseif ($accion === 'registrar') {
                $query->where(function ($q) {
                    $q->where('accion', 'registrar')
                      ->orWhere('accion', 'registrar_masivo')
                      ->orWhere('accion', 'like', '%registra%');
                });
            } else {
                $query->where('accion', $accion);
            }
        }

        $fechaInicio = $request->query('fecha_inicio');
        $fechaFin = $request->query('fecha_fin');

        if (!empty($fechaInicio) || !empty($fechaFin)) {
            try {
                $fInicio = !empty($fechaInicio) ? Carbon::parse($fechaInicio)->format('Y-m-d') : null;
            } catch (\Throwable $e) {
                $fInicio = null;
            }
            try {
                $fFin = !empty($fechaFin) ? Carbon::parse($fechaFin)->format('Y-m-d') : null;
            } catch (\Throwable $e) {
                $fFin = null;
            }

            if ($fInicio || $fFin) {
                $query->where(function ($dq) use ($fInicio, $fFin) {
                    // 1. Coincide con la fecha de la clase (fecha_clase)
                    $dq->where(function ($q1) use ($fInicio, $fFin) {
                        if ($fInicio) $q1->whereDate('fecha_clase', '>=', $fInicio);
                        if ($fFin) $q1->whereDate('fecha_clase', '<=', $fFin);
                    })
                    // 2. O coincide con la fecha en que se registró la acción (created_at)
                    ->orWhere(function ($q2) use ($fInicio, $fFin) {
                        if ($fInicio) $q2->whereDate('created_at', '>=', $fInicio);
                        if ($fFin) $q2->whereDate('created_at', '<=', $fFin);
                    });
                });
            }
        }

        if ($request->filled('search')) {
            $search = trim($request->query('search'));
            $cleanRun = preg_replace('/[^0-9kK]/', '', $search);

            $query->where(function ($q) use ($search, $cleanRun) {
                $q->where('run_profesor', 'like', "%{$search}%")
                  ->orWhere('user_nombre', 'like', "%{$search}%")
                  ->orWhere('motivo', 'like', "%{$search}%")
                  ->orWhere('observaciones', 'like', "%{$search}%")
                  ->orWhere('id_asignatura', 'like', "%{$search}%")
                  ->orWhere('id_espacio', 'like', "%{$search}%")
                  ->orWhereHas('profesor', function ($pq) use ($search) {
                      $pq->where('name', 'like', "%{$search}%");
                  })
                  ->orWhereHas('asignatura', function ($aq) use ($search) {
                      $aq->where('nombre_asignatura', 'like', "%{$search}%")
                        ->orWhere('codigo_asignatura', 'like', "%{$search}%");
                  });

                if (!empty($cleanRun) && strlen($cleanRun) >= 3) {
                    $q->orWhereRaw("REPLACE(REPLACE(run_profesor, '.', ''), '-', '') LIKE ?", ["%{$cleanRun}%"]);
                }
            });
        }

        return $query;
    }
}
