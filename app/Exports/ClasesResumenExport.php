<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use Illuminate\Support\Collection;

class ClasesResumenExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithColumnWidths, WithTitle
{
    protected $datos;
    protected $periodo;

    public function __construct($datos, $periodo)
    {
        $this->datos = $datos;
        $this->periodo = $periodo;
    }

    public function collection()
    {
        // Calculate summary statistics
        $totalRealizadas = 0;
        $totalNoRealizadas = 0;
        $totalRecuperadas = 0;
        
        foreach ($this->datos as $dia) {
            if (is_array($dia)) {
                $totalRealizadas += $dia['realizadas'] ?? 0;
                $totalNoRealizadas += $dia['no_realizadas'] ?? 0;
                $totalRecuperadas += $dia['recuperadas'] ?? 0;
            }
        }
        
        $totalClases = $totalRealizadas + $totalNoRealizadas;
        $porcentajeRealizadas = $totalClases > 0 ? round(($totalRealizadas / $totalClases) * 100, 1) : 0;
        $porcentajeNoRealizadas = $totalClases > 0 ? round(($totalNoRealizadas / $totalClases) * 100, 1) : 0;
        $porcentajeRecuperadas = $totalNoRealizadas > 0 ? round(($totalRecuperadas / $totalNoRealizadas) * 100, 1) : 0;
        
        $collection = collect([
            [
                'concepto' => 'Total de Clases',
                'valor' => $totalClases,
                'porcentaje' => '100%'
            ],
            [
                'concepto' => 'Clases Realizadas',
                'valor' => $totalRealizadas,
                'porcentaje' => $porcentajeRealizadas . '%'
            ],
            [
                'concepto' => 'Clases No Realizadas',
                'valor' => $totalNoRealizadas,
                'porcentaje' => $porcentajeNoRealizadas . '%'
            ],
            [
                'concepto' => 'Clases Recuperadas',
                'valor' => $totalRecuperadas,
                'porcentaje' => $porcentajeRecuperadas . '% (de no realizadas)'
            ],
            [
                'concepto' => 'Clases Pendientes',
                'valor' => $totalNoRealizadas - $totalRecuperadas,
                'porcentaje' => ($totalNoRealizadas - $totalRecuperadas > 0 ? round((($totalNoRealizadas - $totalRecuperadas) / $totalNoRealizadas) * 100, 1) : 0) . '% (de no realizadas)'
            ]
        ]);
        
        return $collection;
    }

    public function headings(): array
    {
        return [
            'Concepto',
            'Cantidad',
            'Porcentaje'
        ];
    }

    public function map($row): array
    {
        return [
            $row['concepto'],
            $row['valor'],
            $row['porcentaje']
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => [
                    'bold' => true,
                    'color' => ['rgb' => 'FFFFFF'],
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '6366F1'], // Indigo color
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
            ],
            'A:C' => [
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_LEFT,
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
            ],
            'A2:A6' => [
                'font' => [
                    'bold' => true,
                ],
            ],
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 30,  // Concepto
            'B' => 15,  // Cantidad
            'C' => 30,  // Porcentaje
        ];
    }

    public function title(): string
    {
        return 'Resumen General';
    }
}
