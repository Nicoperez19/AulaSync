<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Planificacion_Asignatura;
use App\Models\Reserva;
use App\Models\Asignatura;
use App\Models\Carrera;
use App\Models\Espacio;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;

class OccupancyHistory extends Component
{
    public $vistaRapidaLimite = 10;
    public $vistaRapidaOpciones = [10, 25];
    public $vistaRapida = [];
    public $meses = [];
    public $mensajeError = '';

    public function mount()
    {
        $this->cargarVistaRapida();
        $this->cargarMeses();
    }

    public function updatedVistaRapidaLimite()
    {
        $this->cargarVistaRapida();
    }

    public function cargarVistaRapida()
    {
        $planificaciones = DB::connection('tenant')->table('planificacion_asignaturas as p')
            ->join('espacios as e', 'p.id_espacio', '=', 'e.id_espacio')
            ->join('asignaturas as a', 'p.id_asignatura', '=', 'a.id_asignatura')
            ->join('carreras as c', 'a.id_carrera', '=', 'c.id_carrera')
            ->leftJoin('reservas as r', function($q) {
                $q->on('r.id_espacio', '=', 'p.id_espacio')
                  ->on('r.id_asignatura', '=', 'p.id_asignatura')
                  ->whereIn('r.estado', ['activa', 'finalizada']);
            })
            ->select([
                DB::raw('COALESCE(r.fecha_reserva, p.created_at) as fecha'),
                'c.nombre as carrera',
                'a.nombre_asignatura as asignatura',
                'e.nombre_espacio as espacio',
                DB::raw(
                    "CASE WHEN r.id_reserva IS NOT NULL THEN 'Ocupado' ELSE 'No utilizado' END as estado_espacio"
                ),
                'r.estado as estado_reserva',
            ])
            ->orderByDesc('p.id')
            ->limit($this->vistaRapidaLimite)
            ->get();
        $this->vistaRapida = $planificaciones;
    }

    public function cargarMeses()
    {
        $this->meses = DB::connection('tenant')->table('planificacion_asignaturas as p')
            ->leftJoin('reservas as r', function($q) {
                $q->on('r.id_espacio', '=', 'p.id_espacio');
            })
            ->select([
                DB::raw('DATE_FORMAT(COALESCE(r.fecha_reserva, p.created_at), "%Y-%m") as mes'),
                DB::raw('MIN(COALESCE(r.fecha_reserva, p.created_at)) as fecha_generacion'),
                DB::raw('COUNT(*) as total_registros')
            ])
            ->groupBy(DB::raw('DATE_FORMAT(COALESCE(r.fecha_reserva, p.created_at), "%Y-%m")'))
            ->orderByDesc('mes')
            ->limit(6)
            ->get();
    }

    public function exportarExcelMes($mes)
    {
        $datos = DB::connection('tenant')->table('planificacion_asignaturas as p')
            ->join('espacios as e', 'p.id_espacio', '=', 'e.id_espacio')
            ->join('asignaturas as a', 'p.id_asignatura', '=', 'a.id_asignatura')
            ->join('carreras as c', 'a.id_carrera', '=', 'c.id_carrera')
            ->leftJoin('reservas as r', function($q) {
                $q->on('r.id_espacio', '=', 'p.id_espacio')
                  ->on('r.id_asignatura', '=', 'p.id_asignatura')
                  ->whereIn('r.estado', ['activa', 'finalizada']);
            })
            ->select([
                DB::raw('COALESCE(r.fecha_reserva, p.created_at) as fecha'),
                'c.nombre as carrera',
                'a.nombre_asignatura as asignatura',
                'e.nombre_espacio as espacio',
                DB::raw(
                    "CASE WHEN r.id_reserva IS NOT NULL THEN 'Ocupado' ELSE 'No utilizado' END as estado_espacio"
                ),
                'r.estado as estado_reserva',
            ])
            ->whereRaw('DATE_FORMAT(COALESCE(r.fecha_reserva, p.created_at), "%Y-%m") = ?', [$mes])
            ->get();
        return Excel::download(new \App\Exports\HistoricoOcupacionExport($datos), 'historico_ocupacion_'.$mes.'.xlsx');
    }

    public function exportarPDFMes($mes)
    {
        $datos = DB::connection('tenant')->table('planificacion_asignaturas as p')
            ->join('espacios as e', 'p.id_espacio', '=', 'e.id_espacio')
            ->join('asignaturas as a', 'p.id_asignatura', '=', 'a.id_asignatura')
            ->join('carreras as c', 'a.id_carrera', '=', 'c.id_carrera')
            ->leftJoin('reservas as r', function($q) {
                $q->on('r.id_espacio', '=', 'p.id_espacio')
                  ->on('r.id_asignatura', '=', 'p.id_asignatura')
                  ->whereIn('r.estado', ['activa', 'finalizada']);
            })
            ->select([
                DB::raw('COALESCE(r.fecha_reserva, p.created_at) as fecha'),
                'c.nombre as carrera',
                'a.nombre_asignatura as asignatura',
                'e.nombre_espacio as espacio',
                DB::raw(
                    "CASE WHEN r.id_reserva IS NOT NULL THEN 'Ocupado' ELSE 'No utilizado' END as estado_espacio"
                ),
                'r.estado as estado_reserva',
            ])
            ->whereRaw('DATE_FORMAT(COALESCE(r.fecha_reserva, p.created_at), "%Y-%m") = ?', [$mes])
            ->get();
        $periodo = $mes;
        $pdf = Pdf::loadView('reportes.pdf.historico-espacios', [
            'datos' => $datos,
            'fecha_generacion' => now()->format('d/m/Y H:i:s'),
            'total_registros' => count($datos),
            'periodo' => $periodo,
        ]);
        return response()->streamDownload(function () use ($pdf) {
            echo $pdf->stream();
        }, 'historico_ocupacion_'.$mes.'.pdf');
    }

    public function exportarExcelTotal()
    {
        $datos = DB::connection('tenant')->table('planificacion_asignaturas as p')
            ->join('espacios as e', 'p.id_espacio', '=', 'e.id_espacio')
            ->join('asignaturas as a', 'p.id_asignatura', '=', 'a.id_asignatura')
            ->join('carreras as c', 'a.id_carrera', '=', 'c.id_carrera')
            ->leftJoin('reservas as r', function($q) {
                $q->on('r.id_espacio', '=', 'p.id_espacio')
                  ->on('r.id_asignatura', '=', 'p.id_asignatura')
                  ->whereIn('r.estado', ['activa', 'finalizada']);
            })
            ->select([
                DB::raw('COALESCE(r.fecha_reserva, p.created_at) as fecha'),
                'c.nombre as carrera',
                'a.nombre_asignatura as asignatura',
                'e.nombre_espacio as espacio',
                DB::raw(
                    "CASE WHEN r.id_reserva IS NOT NULL THEN 'Ocupado' ELSE 'No utilizado' END as estado_espacio"
                ),
                'r.estado as estado_reserva',
            ])
            ->orderByDesc('p.id')
            ->get();
        return Excel::download(new \App\Exports\HistoricoOcupacionExport($datos), 'historico_ocupacion_total.xlsx');
    }

    public function exportarPDFTotal()
    {
        $datos = DB::connection('tenant')->table('planificacion_asignaturas as p')
            ->join('espacios as e', 'p.id_espacio', '=', 'e.id_espacio')
            ->join('asignaturas as a', 'p.id_asignatura', '=', 'a.id_asignatura')
            ->join('carreras as c', 'a.id_carrera', '=', 'c.id_carrera')
            ->leftJoin('reservas as r', function($q) {
                $q->on('r.id_espacio', '=', 'p.id_espacio')
                  ->on('r.id_asignatura', '=', 'p.id_asignatura')
                  ->whereIn('r.estado', ['activa', 'finalizada']);
            })
            ->select([
                DB::raw('COALESCE(r.fecha_reserva, p.created_at) as fecha'),
                'c.nombre as carrera',
                'a.nombre_asignatura as asignatura',
                'e.nombre_espacio as espacio',
                DB::raw(
                    "CASE WHEN r.id_reserva IS NOT NULL THEN 'Ocupado' ELSE 'No utilizado' END as estado_espacio"
                ),
                'r.estado as estado_reserva',
            ])
            ->orderByDesc('p.id')
            ->get();
        $periodo = 'Todos los registros';
        $pdf = Pdf::loadView('reportes.pdf.historico-espacios', [
            'datos' => $datos,
            'fecha_generacion' => now()->format('d/m/Y H:i:s'),
            'total_registros' => count($datos),
            'periodo' => $periodo,
        ]);
        return response()->streamDownload(function () use ($pdf) {
            echo $pdf->stream();
        }, 'historico_ocupacion_total.pdf');
    }

    public function render()
    {
        return view('livewire.occupancy-history', [
            'vistaRapida' => $this->vistaRapida,
            'vistaRapidaLimite' => $this->vistaRapidaLimite,
            'vistaRapidaOpciones' => $this->vistaRapidaOpciones,
            'meses' => $this->meses,
        ]);
    }
}
