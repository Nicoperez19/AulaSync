<?php

namespace App\Http\Controllers\Docente;

use App\Http\Controllers\Controller;
use App\Models\Asignatura;
use App\Models\ProfesorColaborador;
use App\Models\SesionAsistencia;
use App\Policies\ClaseDocentePolicy;
use App\Services\ClasesDocenteService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Str;

class ReporteAsistenciaDocenteController extends Controller
{
    protected ClasesDocenteService $clasesService;
    protected ClaseDocentePolicy $policy;

    public function __construct(ClasesDocenteService $clasesService, ClaseDocentePolicy $policy)
    {
        $this->clasesService = $clasesService;
        $this->policy = $policy;
    }

    /**
     * Vista principal de reportes de asistencia para el docente.
     * Nombre de ruta: docente.reportes-asistencia.index
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $docenteRun = (string) ($request->attributes->get('docente_activo_run') ?? $user->run);
        $esSuperadmin = (bool) $request->attributes->get('es_superadmin_simulando', false);

        // Clases asignadas al docente
        $clases = $this->clasesService->getClasesDocente($docenteRun);

        // Filtros
        $asignaturaSeleccionada = $request->query('asignatura_id');
        if (!$asignaturaSeleccionada && $clases->isNotEmpty()) {
            $asignaturaSeleccionada = $clases->first()['id'];
        }

        $fechaInicioStr = $request->query('fecha_inicio');
        $fechaFinStr = $request->query('fecha_fin');

        $fechaFin = $fechaFinStr ? Carbon::parse($fechaFinStr) : Carbon::today();
        $fechaInicio = $fechaInicioStr ? Carbon::parse($fechaInicioStr) : Carbon::today()->subMonths(3);

        // Construir consulta de sesiones de este docente
        $sesionesQuery = SesionAsistencia::where('run_profesor', $docenteRun)
            ->whereBetween('fecha', [$fechaInicio->format('Y-m-d'), $fechaFin->format('Y-m-d')]);

        if ($asignaturaSeleccionada && $asignaturaSeleccionada !== 'todas') {
            if (str_starts_with($asignaturaSeleccionada, 'colab_')) {
                $colabId = substr($asignaturaSeleccionada, 6);
                $sesionesQuery->where('id_profesor_colaborador', $colabId);
            } else {
                $sesionesQuery->where('id_asignatura', $asignaturaSeleccionada);
            }
        }

        $sesiones = $sesionesQuery->with(['asignatura.carrera', 'profesorColaborador', 'espacio', 'asistencias.estudiante'])
            ->orderByDesc('fecha')
            ->get();

        // Métricas Globales del Filtro
        $totalSesiones = $sesiones->count();
        $totalPresentes = $sesiones->sum(fn($s) => $s->totalPresentes());
        $totalRegistrados = $sesiones->sum(fn($s) => $s->totalRegistrados());
        $promedioAsistencia = $totalRegistrados > 0 ? round(($totalPresentes / $totalRegistrados) * 100, 1) : 0;
        $totalAusentes = $totalRegistrados - $totalPresentes;

        // Desglose por Estudiante para la asignatura seleccionada
        $reporteEstudiantes = collect();
        $claseActual = $clases->firstWhere('id', $asignaturaSeleccionada);

        if ($claseActual) {
            $asignaturaModel = null;
            if (!str_starts_with($asignaturaSeleccionada, 'colab_')) {
                $asignaturaModel = Asignatura::with(['estudiantes'])->find($asignaturaSeleccionada);
            } else {
                $colab = ProfesorColaborador::with('asignatura.estudiantes')->find(substr($asignaturaSeleccionada, 6));
                $asignaturaModel = $colab?->asignatura;
            }

            if ($asignaturaModel) {
                $estudiantes = $asignaturaModel->estudiantes;

                foreach ($estudiantes as $est) {
                    $asistenciasEnRango = $sesiones->flatMap(fn($s) => $s->asistencias->where('estudiante_id', $est->id));
                    $totalClasesEst = $totalSesiones;
                    $presentesEst = $asistenciasEnRango->where('presente', true)->count();
                    $ausentesEst = $totalClasesEst - $presentesEst;
                    $porcentajeEst = $totalClasesEst > 0 ? round(($presentesEst / $totalClasesEst) * 100, 1) : 0;

                    $reporteEstudiantes->push([
                        'id' => $est->id,
                        'run' => $est->run,
                        'nombre' => $est->nombre,
                        'email' => $est->email,
                        'total_clases' => $totalClasesEst,
                        'presentes' => $presentesEst,
                        'ausentes' => $ausentesEst,
                        'porcentaje' => $porcentajeEst,
                        'estado' => $porcentajeEst >= 75 ? 'normal' : 'riesgo',
                    ]);
                }

                // Ordenar alfabéticamente por Apellidos, Nombres
                $reporteEstudiantes = $reporteEstudiantes->sortBy(function ($item) {
                    return Str::ascii(mb_strtolower($item['nombre'] ?? ''));
                })->values();
            }
        }

        return view('docente.reportes.index', compact(
            'clases',
            'asignaturaSeleccionada',
            'claseActual',
            'fechaInicio',
            'fechaFin',
            'sesiones',
            'totalSesiones',
            'totalPresentes',
            'totalAusentes',
            'totalRegistrados',
            'promedioAsistencia',
            'reporteEstudiantes',
            'esSuperadmin'
        ));
    }

    /**
     * Acceso directo al reporte de una asignatura específica.
     * Nombre de ruta: docente.reportes-asistencia.asignatura
     */
    public function asignatura(Request $request, string $id)
    {
        return redirect()->route('docente.reportes-asistencia.index', [
            'asignatura_id' => $id,
            'fecha_inicio' => $request->query('fecha_inicio'),
            'fecha_fin' => $request->query('fecha_fin'),
        ]);
    }

    /**
     * Muestra la ficha de detalle de una sesión de asistencia específica.
     * Nombre de ruta: docente.reportes-asistencia.sesion
     */
    public function sesionDetalle(Request $request, int|string $sesionId)
    {
        $user = Auth::user();
        $docenteRun = (string) ($request->attributes->get('docente_activo_run') ?? $user->run);
        $esSuperadmin = (bool) $request->attributes->get('es_superadmin_simulando', false);

        $sesion = SesionAsistencia::with([
            'asignatura.carrera',
            'profesorColaborador.asignatura',
            'espacio',
            'asistencias.estudiante',
        ])->findOrFail($sesionId);

        // Validar acceso: debe pertenecer al docente o ser superadmin
        if (!$esSuperadmin && (string)$sesion->run_profesor !== $docenteRun) {
            abort(403, 'No tienes autorización para ver la ficha de esta sesión.');
        }

        $tituloClase = $sesion->asignatura?->nombre_asignatura 
            ?? $sesion->profesorColaborador?->nombre_asignatura 
            ?? 'Clase Temporal';
        $codigoClase = $sesion->asignatura?->codigo_asignatura 
            ?? $sesion->profesorColaborador?->asignatura?->codigo_asignatura 
            ?? 'N/A';
        $seccionClase = $sesion->asignatura?->seccion 
            ?? $sesion->profesorColaborador?->asignatura?->seccion 
            ?? 'N/A';
        $carreraNombre = $sesion->asignatura?->carrera?->nombre 
            ?? ($sesion->profesorColaborador?->asignatura?->carrera?->nombre ?? 'Sin carrera');

        $asistencias = $sesion->asistencias->sortBy(function ($a) {
            return Str::ascii(mb_strtolower($a->estudiante?->nombre ?? ''));
        })->values();

        $totalPresentes = $sesion->totalPresentes();
        $totalAusentes = $sesion->totalAusentes();
        $totalRegistrados = $sesion->totalRegistrados();
        $porcentaje = $sesion->porcentajeAsistencia();

        return view('docente.reportes.sesion', compact(
            'sesion',
            'tituloClase',
            'codigoClase',
            'seccionClase',
            'carreraNombre',
            'asistencias',
            'totalPresentes',
            'totalAusentes',
            'totalRegistrados',
            'porcentaje',
            'esSuperadmin'
        ));
    }

    /**
     * Exporta el resumen de asistencia de los estudiantes a CSV / Excel.
     * Nombre de ruta: docente.reportes-asistencia.export
     */
    public function exportar(Request $request)
    {
        $user = Auth::user();
        $docenteRun = (string) ($request->attributes->get('docente_activo_run') ?? $user->run);

        $asignaturaId = $request->query('asignatura_id');
        $fechaInicioStr = $request->query('fecha_inicio');
        $fechaFinStr = $request->query('fecha_fin');

        $fechaFin = $fechaFinStr ? Carbon::parse($fechaFinStr) : Carbon::today();
        $fechaInicio = $fechaInicioStr ? Carbon::parse($fechaInicioStr) : Carbon::today()->subMonths(3);

        $sesionesQuery = SesionAsistencia::where('run_profesor', $docenteRun)
            ->whereBetween('fecha', [$fechaInicio->format('Y-m-d'), $fechaFin->format('Y-m-d')]);

        if ($asignaturaId && $asignaturaId !== 'todas') {
            if (str_starts_with($asignaturaId, 'colab_')) {
                $sesionesQuery->where('id_profesor_colaborador', substr($asignaturaId, 6));
            } else {
                $sesionesQuery->where('id_asignatura', $asignaturaId);
            }
        }

        $sesiones = $sesionesQuery->with(['asistencias.estudiante'])->get();
        $totalSesiones = $sesiones->count();

        $asignatura = Asignatura::find($asignaturaId);
        $nombreAsignatura = $asignatura?->nombre_asignatura ?? 'Todas_las_clases';

        $filename = 'reporte_asistencia_' . Str::slug($nombreAsignatura) . '_' . date('Ymd_His') . '.csv';

        $callback = function () use ($sesiones, $totalSesiones, $asignatura) {
            $output = fopen('php://output', 'w');
            // BOM UTF-8 para Excel
            fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

            // Encabezado
            fputcsv($output, ['RUN', 'Apellidos y Nombres', 'Correo Institucional', 'Total Clases', 'Asistencias', 'Inasistencias', '% Asistencia', 'Estado'], ';');

            if ($asignatura) {
                $estudiantes = $asignatura->estudiantes->sortBy(fn($e) => Str::ascii(mb_strtolower($e->nombre)));
                foreach ($estudiantes as $est) {
                    $asistenciasEst = $sesiones->flatMap(fn($s) => $s->asistencias->where('estudiante_id', $est->id));
                    $presentes = $asistenciasEst->where('presente', true)->count();
                    $ausentes = $totalSesiones - $presentes;
                    $pct = $totalSesiones > 0 ? round(($presentes / $totalSesiones) * 100, 1) : 0;
                    $estado = $pct >= 75 ? 'Normal' : 'En Riesgo';

                    fputcsv($output, [
                        $est->run,
                        $est->nombre,
                        $est->email,
                        $totalSesiones,
                        $presentes,
                        $ausentes,
                        $pct . '%',
                        $estado,
                    ], ';');
                }
            }

            fclose($output);
        };

        return Response::stream($callback, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }
}
