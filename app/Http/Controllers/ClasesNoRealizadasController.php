<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Exports\ClasesNoRealizadasExport;
use App\Exports\TodasClasesExport;
use App\Models\ProfesorAtraso;
use App\Helpers\SemesterHelper;
use Maatwebsite\Excel\Facades\Excel;
use Carbon\Carbon;

class ClasesNoRealizadasController extends Controller
{
    public function index()
    {
        $periodo = SemesterHelper::getCurrentPeriod();
        
        // Obtener estadísticas de atrasos (simplificado)
        $totalAtrasos = ProfesorAtraso::where('periodo', $periodo)->count();
        $promedioMinutosAtraso = ProfesorAtraso::where('periodo', $periodo)
            ->avg('minutos_atraso') ?? 0;
        
        return view('admin.clases-no-realizadas', compact(
            'totalAtrasos',
            'promedioMinutosAtraso',
            'periodo'
        ));
    }

    /**
     * Exportar clases no realizadas a Excel
     */
    public function exportExcel(Request $request)
    {
        $request->validate([
            'fecha_inicio' => 'nullable|date',
            'fecha_fin' => 'nullable|date|after_or_equal:fecha_inicio',
            'periodo' => 'nullable|string|max:20',
            'estado' => 'nullable|in:no_realizada,justificado,recuperada',
        ]);

        $fechaInicio = $request->input('fecha_inicio');
        $fechaFin = $request->input('fecha_fin');
        $periodo = $request->input('periodo');
        $estado = $request->input('estado');

        // Generar nombre de archivo descriptivo
        $nombreArchivo = 'Clases_No_Realizadas';
        
        if ($fechaInicio && $fechaFin) {
            $nombreArchivo .= '_' . Carbon::parse($fechaInicio)->format('d-m-Y');
            $nombreArchivo .= '_a_' . Carbon::parse($fechaFin)->format('d-m-Y');
        } elseif ($periodo) {
            $nombreArchivo .= '_Periodo_' . str_replace('/', '-', $periodo);
        } else {
            $nombreArchivo .= '_' . Carbon::now()->format('d-m-Y');
        }
        
        $nombreArchivo .= '.xlsx';

        return Excel::download(
            new ClasesNoRealizadasExport($fechaInicio, $fechaFin, $periodo, $estado),
            $nombreArchivo
        );
    }

    /**
     * Exportar todas las clases (realizadas y no realizadas) a Excel
     */
    public function exportAllExcel(Request $request)
    {
        $request->validate([
            'fecha_inicio' => 'nullable|date',
            'fecha_fin' => 'nullable|date|after_or_equal:fecha_inicio',
            'periodo' => 'nullable|string|max:20',
        ]);

        $fechaInicio = $request->input('fecha_inicio');
        $fechaFin = $request->input('fecha_fin');
        $periodo = $request->input('periodo');

        // Generar nombre de archivo descriptivo
        $nombreArchivo = 'Todas_Las_Clases';
        
        if ($fechaInicio && $fechaFin) {
            $nombreArchivo .= '_' . Carbon::parse($fechaInicio)->format('d-m-Y');
            $nombreArchivo .= '_a_' . Carbon::parse($fechaFin)->format('d-m-Y');
        } elseif ($periodo) {
            $nombreArchivo .= '_Periodo_' . str_replace('/', '-', $periodo);
        } else {
            $nombreArchivo .= '_' . Carbon::now()->format('d-m-Y');
        }
        
        $nombreArchivo .= '.xlsx';

        return Excel::download(
            new TodasClasesExport($fechaInicio, $fechaFin, $periodo),
            $nombreArchivo
        );
    }
}
