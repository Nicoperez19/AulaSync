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
     * Vista unificada de Auditorías separada por Navpills (Justificaciones vs Reagendamientos)
     */
    public function index(Request $request)
    {
        $this->sincronizarRecuperacionesSinAuditoria();

        $tab = $request->query('tab', 'justificaciones');
        if (!in_array($tab, ['justificaciones', 'reagendamientos'], true)) {
            $tab = 'justificaciones';
        }

        $queryJustificaciones = $this->buildFilteredQuery($request, false);
        $registrosJustificaciones = $queryJustificaciones->orderBy('created_at', 'desc')->paginate(20, ['*'], 'page_just')->withQueryString();

        $queryReagendamientos = $this->buildFilteredQuery($request, true);
        $registrosReagendamientos = $queryReagendamientos->orderBy('created_at', 'desc')->paginate(20, ['*'], 'page_reag')->withQueryString();

        $accionesReagendamiento = ['reagendar', 'marcar_recuperada', 'obviar_recuperacion'];
        $totalReagendamientos = AuditoriaClase::where(function ($q) use ($accionesReagendamiento) {
            $q->whereIn('accion', $accionesReagendamiento)
              ->orWhere('accion', 'like', '%reagend%')
              ->orWhere('accion', 'like', '%recupera%')
              ->orWhere('observaciones', 'like', '%Reagendada%');
        })->count();

        $totalJustificaciones = AuditoriaClase::whereNotIn('accion', $accionesReagendamiento)->count();

        $accionesDisponiblesJustificaciones = [
            'justificar' => 'Justificación Individual',
            'justificar_masivo' => 'Justificación Masiva',
            'registrar' => 'Registro Individual',
            'registrar_masivo' => 'Registro Masivo',
            'cambiar_estado' => 'Cambio de Estado',
            'eliminar' => 'Eliminación',
        ];

        $accionesDisponiblesReagendamientos = [
            'reagendar' => 'Reagendamiento de Clase',
            'marcar_recuperada' => 'Marcada como Recuperada',
            'obviar_recuperacion' => 'Recuperación Obviada',
        ];

        $accionesDisponibles = ($tab === 'reagendamientos') ? $accionesDisponiblesReagendamientos : $accionesDisponiblesJustificaciones;
        $registros = ($tab === 'reagendamientos') ? $registrosReagendamientos : $registrosJustificaciones;

        return view('reportes.auditoria-clases', [
            'registros' => $registros,
            'registrosJustificaciones' => $registrosJustificaciones,
            'registrosReagendamientos' => $registrosReagendamientos,
            'tab' => $tab,
            'totalJustificaciones' => $totalJustificaciones,
            'totalReagendamientos' => $totalReagendamientos,
            'accionesDisponibles' => $accionesDisponibles,
            'accionesDisponiblesJustificaciones' => $accionesDisponiblesJustificaciones,
            'accionesDisponiblesReagendamientos' => $accionesDisponiblesReagendamientos,
            'accionSeleccionada' => $request->query('accion', ''),
            'fechaInicio' => $request->query('fecha_inicio', ''),
            'fechaFin' => $request->query('fecha_fin', ''),
            'search' => $request->query('search', ''),
        ]);
    }

    /**
     * Redirige o muestra la pestaña de Reagendamientos dentro de la vista unificada de Auditorías
     */
    public function reagendamientos(Request $request)
    {
        $request->merge(['tab' => 'reagendamientos']);
        return $this->index($request);
    }

    /**
     * Exportación en Excel o PDF según la pestaña (navpill) activa y filtros aplicados
     */
    public function export(Request $request, string $format)
    {
        $this->sincronizarRecuperacionesSinAuditoria();

        $tab = $request->query('tab', 'justificaciones');
        $soloReagendamientos = ($tab === 'reagendamientos');

        $query = $this->buildFilteredQuery($request, $soloReagendamientos)->orderBy('created_at', 'desc');

        $prefijo = $soloReagendamientos ? 'auditoria_reagendamientos_' : 'auditoria_clases_';
        $fileName = $prefijo . now()->format('Y-m-d_His');

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
     * Exportación directa de Reagendamientos en Excel o PDF
     */
    public function exportReagendamientos(Request $request, string $format)
    {
        $request->merge(['tab' => 'reagendamientos']);
        return $this->export($request, $format);
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
    protected function buildFilteredQuery(Request $request, bool $soloReagendamientos = false)
    {
        $query = AuditoriaClase::query()->with(['profesor', 'asignatura', 'espacio']);

        $accionesReagendamiento = ['reagendar', 'marcar_recuperada', 'obviar_recuperacion'];

        if ($soloReagendamientos) {
            $query->where(function ($q) use ($accionesReagendamiento) {
                $q->whereIn('accion', $accionesReagendamiento)
                  ->orWhere('accion', 'like', '%reagend%')
                  ->orWhere('accion', 'like', '%recupera%')
                  ->orWhere('observaciones', 'like', '%Reagendada%');
            });
        } else {
            $query->whereNotIn('accion', $accionesReagendamiento);
        }

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

    /**
     * Sincroniza registros históricos de recuperacion_clases que aún no tengan fila en auditoria_clases
     */
    protected function sincronizarRecuperacionesSinAuditoria(): void
    {
        try {
            $recuperaciones = \App\Models\RecuperacionClase::whereIn('estado', ['reagendada', 'realizada', 'obviada'])
                ->get();

            if ($recuperaciones->isEmpty()) {
                return;
            }

            $tenantId = \App\Models\Tenant::current()?->id;

            foreach ($recuperaciones as $rc) {
                $fechaReag = $rc->fecha_reagendada ? $rc->fecha_reagendada->format('Y-m-d') : null;
                $fechaOrig = $rc->fecha_clase_original ? $rc->fecha_clase_original->format('Y-m-d') : null;

                $existe = AuditoriaClase::where('run_profesor', $rc->run_profesor)
                    ->where('id_asignatura', $rc->id_asignatura)
                    ->whereIn('accion', ['reagendar', 'marcar_recuperada', 'obviar_recuperacion'])
                    ->where(function ($q) use ($rc, $fechaReag, $fechaOrig) {
                        if ($rc->id_clase_no_realizada) {
                            $q->where('entidad_id', $rc->id_clase_no_realizada);
                        }
                        $q->orWhere('entidad_id', $rc->id_recuperacion);
                        if ($fechaReag) {
                            $q->orWhereDate('fecha_clase', $fechaReag);
                        }
                        if ($fechaOrig) {
                            $q->orWhereDate('fecha_clase', $fechaOrig);
                        }
                    })
                    ->exists();

                if ($existe) {
                    continue;
                }

                $userId = null;
                $userNombre = 'Sistema';
                $userRol = null;

                if (!empty($rc->gestionado_por)) {
                    $runGestor = preg_replace('/[^0-9kK]/', '', (string) $rc->gestionado_por);
                    $usuario = null;
                    try {
                        $usuario = \App\Models\User::where('run', $rc->gestionado_por)
                            ->orWhere('run', $runGestor)
                            ->first();
                    } catch (\Throwable $e) {
                        $usuario = null;
                    }

                    if ($usuario) {
                        $userId = $usuario->id;
                        $userNombre = $usuario->name;
                        try {
                            $userRol = method_exists($usuario, 'getRoleNames') ? $usuario->getRoleNames()->first() : ($usuario->rol ?? null);
                        } catch (\Throwable $e) {
                            $userRol = null;
                        }
                    } else {
                        $userNombre = "RUN: {$rc->gestionado_por}";
                    }
                }

                $accion = match ($rc->estado) {
                    'realizada' => 'marcar_recuperada',
                    'obviada' => 'obviar_recuperacion',
                    default => 'reagendar',
                };

                $obs = $rc->notas ?: (
                    $fechaReag
                        ? "Reagendada desde " . ($fechaOrig ? Carbon::parse($fechaOrig)->format('d/m/Y') : 'N/A') . " ({$rc->id_espacio}, mód. {$rc->id_modulo_original}) a " . Carbon::parse($fechaReag)->format('d/m/Y') . " (" . ($rc->id_espacio_reagendado ?? $rc->id_espacio) . ", mód. {$rc->id_modulo_reagendado})."
                        : "Gestión de recuperación ({$rc->estado})."
                );

                AuditoriaClase::create([
                    'tenant_id' => $tenantId,
                    'user_id' => $userId,
                    'user_nombre' => $userNombre,
                    'user_rol' => $userRol,
                    'accion' => $accion,
                    'entidad_tipo' => \App\Models\RecuperacionClase::class,
                    'entidad_id' => $rc->id_clase_no_realizada ?? $rc->id_recuperacion,
                    'run_profesor' => $rc->run_profesor,
                    'id_asignatura' => $rc->id_asignatura,
                    'id_espacio' => $rc->id_espacio_reagendado ?? $rc->id_espacio,
                    'id_modulo' => $rc->id_modulo_reagendado ?? $rc->id_modulo_original,
                    'fecha_clase' => $fechaReag ?? $fechaOrig,
                    'estado_anterior' => 'pendiente',
                    'estado_nuevo' => $rc->estado,
                    'valores_anteriores' => [
                        'fecha_clase' => $fechaOrig,
                        'id_espacio' => $rc->id_espacio,
                        'id_modulo' => $rc->id_modulo_original,
                    ],
                    'valores_nuevos' => [
                        'fecha_clase' => $fechaReag,
                        'id_espacio' => $rc->id_espacio_reagendado ?? $rc->id_espacio,
                        'id_modulo' => $rc->id_modulo_reagendado,
                    ],
                    'motivo' => 'Reagendamiento / Recuperación de clase',
                    'observaciones' => $obs,
                    'origen' => 'web',
                    'created_at' => $rc->updated_at ?? $rc->created_at ?? now(),
                ]);
            }
        } catch (\Throwable $e) {
            // No bloquear la vista si falla la sincronización histórica
        }
    }
}
