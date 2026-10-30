<?php

namespace App\Exports;

use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class CumplimientoEscuelasExport implements WithMultipleSheets
{
    protected $escuelasData;
    protected $clasesDetalle;
    protected $fechaInicio;
    protected $fechaFin;
    protected $periodo;

    public function __construct(array $escuelasData, $clasesDetalle, $fechaInicio, $fechaFin, $periodo)
    {
        $this->escuelasData = $escuelasData;
        $this->clasesDetalle = $clasesDetalle;
        $this->fechaInicio = $fechaInicio;
        $this->fechaFin = $fechaFin;
        $this->periodo = $periodo;
    }

    public function sheets(): array
    {
        return [
            new HojaResumenEscuelas($this->escuelasData, $this->fechaInicio, $this->fechaFin, $this->periodo),
            new HojaDetalleClasesEscuelas($this->clasesDetalle, $this->periodo),
        ];
    }
}

class HojaResumenEscuelas implements FromCollection, WithHeadings, WithStyles, WithColumnWidths, WithTitle
{
    protected $escuelasData;
    protected $fechaInicio;
    protected $fechaFin;
    protected $periodo;

    public function __construct(array $escuelasData, $fechaInicio, $fechaFin, $periodo)
    {
        $this->escuelasData = $escuelasData;
        $this->fechaInicio = $fechaInicio;
        $this->fechaFin = $fechaFin;
        $this->periodo = $periodo;
    }

    public function collection()
    {
        $rows = collect();
        $totales = [
            'nombre'         => 'TOTAL GENERAL SEDE',
            'total'          => 0,
            'impartidas'     => 0,
            'realizadas'     => 0,
            'recuperadas'    => 0,
            'justificadas'   => 0,
            'no_registradas' => 0,
            'pct'            => 0,
        ];

        foreach ($this->escuelasData as $esc) {
            $totales['total'] += $esc['total'] ?? 0;
            $totales['impartidas'] += $esc['impartidas'] ?? 0;
            $totales['realizadas'] += $esc['realizadas'] ?? 0;
            $totales['recuperadas'] += $esc['recuperadas'] ?? 0;
            $totales['justificadas'] += $esc['justificadas'] ?? 0;
            $totales['no_registradas'] += $esc['no_registradas'] ?? 0;

            $rows->push([
                $esc['nombre'] ?? 'Sin Escuela',
                $esc['total'] ?? 0,
                $esc['impartidas'] ?? 0,
                $esc['realizadas'] ?? 0,
                $esc['recuperadas'] ?? 0,
                $esc['justificadas'] ?? 0,
                $esc['no_registradas'] ?? 0,
                ($esc['pct'] ?? 0) . '%',
            ]);
        }

        if ($totales['total'] > 0) {
            $totales['pct'] = round(($totales['impartidas'] / $totales['total']) * 100, 1);
        }

        // Fila de totales
        $rows->push([
            $totales['nombre'],
            $totales['total'],
            $totales['impartidas'],
            $totales['realizadas'],
            $totales['recuperadas'],
            $totales['justificadas'],
            $totales['no_registradas'],
            $totales['pct'] . '%',
        ]);

        return $rows;
    }

    public function headings(): array
    {
        return [
            'Escuela / Área Académica',
            'Total Clases Planificadas',
            'Clases Impartidas (Efectivas)',
            'Realizadas Normales',
            'Recuperadas',
            'Justificadas',
            'No Registradas / Ausencias',
            '% de Cumplimiento',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $lastRow = count($this->escuelasData) + 2;

        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 11],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '1E293B'],
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
            ],
            $lastRow => [
                'font' => ['bold' => true, 'size' => 11, 'color' => ['rgb' => '1E293B']],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'E2E8F0'],
                ],
                'borders' => [
                    'top' => ['borderStyle' => Border::BORDER_MEDIUM],
                    'bottom' => ['borderStyle' => Border::BORDER_DOUBLE],
                ],
            ],
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 45,
            'B' => 24,
            'C' => 26,
            'D' => 20,
            'E' => 16,
            'F' => 16,
            'G' => 26,
            'H' => 20,
        ];
    }

    public function title(): string
    {
        return 'Resumen por Escuela';
    }
}

class HojaDetalleClasesEscuelas implements FromCollection, WithHeadings, WithStyles, WithColumnWidths, WithTitle
{
    protected $clasesDetalle;
    protected $periodo;

    public function __construct($clasesDetalle, $periodo)
    {
        $this->clasesDetalle = $clasesDetalle;
        $this->periodo = $periodo;
    }

    public function collection()
    {
        return collect($this->clasesDetalle)->map(function ($c) {
            $fecha = ($c['fecha'] instanceof Carbon)
                ? $c['fecha']->format('d/m/Y')
                : Carbon::parse($c['fecha'])->format('d/m/Y');

            $horario = ($c['modulo'] ?? '') . ' (' . substr($c['hora_inicio'] ?? '', 0, 5) . ' - ' . substr($c['hora_fin'] ?? '', 0, 5) . ')';

            return [
                $fecha,
                $c['dia'] ?? '',
                $c['escuela'] ?? 'Sin Escuela',
                $c['carrera'] ?? 'N/A',
                $c['ua'] ?? 'N/A',
                $c['asignatura'] ?? 'N/A',
                $c['codigo_asignatura'] ?? '',
                $c['seccion'] ?? '',
                $c['profesor'] ?? 'N/A',
                $c['run_profesor'] ?? '',
                $c['espacio'] ?? '',
                $horario,
                $c['estado_bloque'] ?? $c['estado'] ?? 'Realizada',
                $c['observaciones'] ?? '',
            ];
        });
    }

    public function headings(): array
    {
        return [
            'Fecha',
            'Día',
            'Escuela / Área Académica',
            'Carrera',
            'UA',
            'Asignatura',
            'Código',
            'Sección',
            'Docente',
            'RUN Docente',
            'Espacio',
            'Horario',
            'Estado',
            'Observaciones',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 10],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'D12420'],
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
            ],
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 14,
            'B' => 12,
            'C' => 38,
            'D' => 32,
            'E' => 12,
            'F' => 35,
            'G' => 14,
            'H' => 10,
            'I' => 32,
            'J' => 14,
            'K' => 12,
            'L' => 22,
            'M' => 18,
            'N' => 45,
        ];
    }

    public function title(): string
    {
        return 'Detalle de Clases';
    }
}
