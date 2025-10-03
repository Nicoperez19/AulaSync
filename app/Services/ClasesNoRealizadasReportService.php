<?php

namespace App\Services;

use App\Models\ClaseNoRealizada;
use App\Models\Planificacion_Asignatura;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Log;

class ClasesNoRealizadasReportService
{
    /**
     * Generar reporte semanal de clases no realizadas
     */
    public function generarReporteSemanal($fechaInicio = null, $fechaFin = null)
    {
        if (!$fechaInicio) {
            $fechaInicio = Carbon::now()->startOfWeek();
        } else {
            $fechaInicio = Carbon::parse($fechaInicio);
        }

        if (!$fechaFin) {
            $fechaFin = Carbon::now()->endOfWeek();
        } else {
            $fechaFin = Carbon::parse($fechaFin);
        }

        // Obtener clases no realizadas de la semana
        $clasesNoRealizadas = ClaseNoRealizada::with(['profesor', 'asignatura', 'espacio'])
            ->whereBetween('fecha_clase', [$fechaInicio, $fechaFin])
            ->orderBy('fecha_clase', 'asc')
            ->orderBy('id_modulo', 'asc')
            ->get();

        // Agrupar por profesor
        $clasesPorProfesor = $clasesNoRealizadas->groupBy('run_profesor');

        // Calcular estadísticas
        $estadisticas = [
            'total_clases_no_realizadas' => $clasesNoRealizadas->count(),
            'total_no_realizadas' => $clasesNoRealizadas->where('estado', 'no_realizada')->count(),
            'total_justificadas' => $clasesNoRealizadas->where('estado', 'justificado')->count(),
            'profesores_afectados' => $clasesPorProfesor->count(),
        ];

        // Preparar datos para el reporte
        $datosReporte = [];
        foreach ($clasesPorProfesor as $runProfesor => $clases) {
            $profesor = $clases->first()->profesor;
            
            $clasesDelProfesor = $clases->map(function ($clase) {
                return [
                    'fecha' => $clase->fecha_clase->format('d/m/Y'),
                    'dia_semana' => $clase->fecha_clase->locale('es')->isoFormat('dddd'),
                    'asignatura' => $clase->asignatura->nombre_asignatura ?? 'N/A',
                    'codigo_asignatura' => $clase->asignatura->codigo_asignatura ?? 'N/A',
                    'espacio' => $clase->id_espacio,
                    'modulo' => preg_replace('/^[A-Z]{2}\./', '', $clase->id_modulo),
                    'estado' => $clase->estado,
                    'motivo' => $clase->motivo ?? 'No especificado',
                    'observaciones' => $clase->observaciones ?? '',
                ];
            });

            $datosReporte[] = [
                'profesor' => $profesor->name ?? 'Profesor no encontrado',
                'run' => $runProfesor,
                'email' => $profesor->email ?? null,
                'total_ausencias' => $clases->count(),
                'no_realizadas' => $clases->where('estado', 'no_realizada')->count(),
                'justificadas' => $clases->where('estado', 'justificado')->count(),
                'clases' => $clasesDelProfesor->toArray(),
            ];
        }

        return [
            'periodo' => [
                'inicio' => $fechaInicio->format('d/m/Y'),
                'fin' => $fechaFin->format('d/m/Y'),
                'semana' => $fechaInicio->weekOfYear,
                'anio' => $fechaInicio->year,
            ],
            'estadisticas' => $estadisticas,
            'profesores' => $datosReporte,
        ];
    }

    /**
     * Generar reporte mensual de clases no realizadas
     */
    public function generarReporteMensual($mes = null, $anio = null)
    {
        if (!$mes) {
            $mes = Carbon::now()->month;
        }
        if (!$anio) {
            $anio = Carbon::now()->year;
        }

        $fechaInicio = Carbon::create($anio, $mes, 1)->startOfMonth();
        $fechaFin = Carbon::create($anio, $mes, 1)->endOfMonth();

        // Obtener clases no realizadas del mes
        $clasesNoRealizadas = ClaseNoRealizada::with(['profesor', 'asignatura', 'espacio'])
            ->whereBetween('fecha_clase', [$fechaInicio, $fechaFin])
            ->orderBy('fecha_clase', 'asc')
            ->orderBy('id_modulo', 'asc')
            ->get();

        // Calcular total de clases programadas en el mes
        $totalClasesProgramadas = Planificacion_Asignatura::whereHas('horario', function ($query) use ($fechaInicio) {
            $query->where('periodo', $fechaInicio->year . '-' . ceil($fechaInicio->month / 6));
        })->count() * 4; // Aproximadamente 4 semanas

        // Agrupar por profesor
        $clasesPorProfesor = $clasesNoRealizadas->groupBy('run_profesor');

        // Calcular estadísticas generales
        $estadisticas = [
            'total_clases_no_realizadas' => $clasesNoRealizadas->count(),
            'total_no_realizadas' => $clasesNoRealizadas->where('estado', 'no_realizada')->count(),
            'total_justificadas' => $clasesNoRealizadas->where('estado', 'justificado')->count(),
            'total_recuperadas' => $clasesNoRealizadas->where('observaciones', 'like', '%reagendad%')->count(),
            'profesores_afectados' => $clasesPorProfesor->count(),
            'total_clases_programadas' => $totalClasesProgramadas,
            'porcentaje_no_realizadas' => $totalClasesProgramadas > 0 
                ? round(($clasesNoRealizadas->count() / $totalClasesProgramadas) * 100, 2) 
                : 0,
        ];

        // Preparar datos por profesor
        $datosReporte = [];
        foreach ($clasesPorProfesor as $runProfesor => $clases) {
            $profesor = $clases->first()->profesor;
            
            $clasesDelProfesor = $clases->map(function ($clase) {
                $esRecuperada = stripos($clase->observaciones ?? '', 'reagendad') !== false;
                $esJustificada = $clase->estado === 'justificado';
                
                return [
                    'fecha' => $clase->fecha_clase->format('d/m/Y'),
                    'dia_semana' => $clase->fecha_clase->locale('es')->isoFormat('dddd'),
                    'asignatura' => $clase->asignatura->nombre_asignatura ?? 'N/A',
                    'codigo_asignatura' => $clase->asignatura->codigo_asignatura ?? 'N/A',
                    'espacio' => $clase->id_espacio,
                    'modulo' => preg_replace('/^[A-Z]{2}\./', '', $clase->id_modulo),
                    'estado' => $clase->estado,
                    'recuperada' => $esRecuperada ? 'Sí' : 'No',
                    'justificada' => $esJustificada ? 'Sí' : 'No',
                    'motivo' => $clase->motivo ?? 'No especificado',
                    'observaciones' => $clase->observaciones ?? '',
                ];
            });

            $datosReporte[] = [
                'profesor' => $profesor->name ?? 'Profesor no encontrado',
                'run' => $runProfesor,
                'email' => $profesor->email ?? null,
                'total_ausencias' => $clases->count(),
                'no_realizadas' => $clases->where('estado', 'no_realizada')->count(),
                'justificadas' => $clases->where('estado', 'justificado')->count(),
                'recuperadas' => $clases->filter(function ($clase) {
                    return stripos($clase->observaciones ?? '', 'reagendad') !== false;
                })->count(),
                'porcentaje_cumplimiento' => 100 - (($clases->count() / max($totalClasesProgramadas / $clasesPorProfesor->count(), 1)) * 100),
                'clases' => $clasesDelProfesor->toArray(),
            ];
        }

        // Ordenar por mayor cantidad de ausencias
        usort($datosReporte, function ($a, $b) {
            return $b['total_ausencias'] - $a['total_ausencias'];
        });

        return [
            'periodo' => [
                'mes' => $fechaInicio->locale('es')->isoFormat('MMMM'),
                'anio' => $anio,
                'inicio' => $fechaInicio->format('d/m/Y'),
                'fin' => $fechaFin->format('d/m/Y'),
            ],
            'estadisticas' => $estadisticas,
            'profesores' => $datosReporte,
        ];
    }

    /**
     * Generar PDF del reporte semanal
     */
    public function generarPDFSemanal($fechaInicio = null, $fechaFin = null)
    {
        $datos = $this->generarReporteSemanal($fechaInicio, $fechaFin);
        
        $pdf = Pdf::loadView('pdf.clases-no-realizadas-semanal', $datos);
        $pdf->setPaper('a4', 'portrait');
        
        return $pdf;
    }

    /**
     * Generar PDF del reporte mensual
     */
    public function generarPDFMensual($mes = null, $anio = null)
    {
        $datos = $this->generarReporteMensual($mes, $anio);
        
        $pdf = Pdf::loadView('pdf.clases-no-realizadas-mensual', $datos);
        $pdf->setPaper('a4', 'landscape');
        
        return $pdf;
    }
}
