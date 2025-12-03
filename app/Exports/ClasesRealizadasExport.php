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

class ClasesRealizadasExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithColumnWidths, WithTitle
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
        // Convert datos array to collection for export
        $collection = collect();
        
        foreach ($this->datos as $diaKey => $dia) {
            if (is_array($dia) && isset($dia['realizadas']) && $dia['realizadas'] > 0) {
                $collection->push([
                    'fecha' => $diaKey,
                    'realizadas' => $dia['realizadas'],
                    'no_realizadas' => $dia['no_realizadas'] ?? 0,
                    'recuperadas' => $dia['recuperadas'] ?? 0,
                    'total' => ($dia['realizadas'] + ($dia['no_realizadas'] ?? 0)),
                    'porcentaje_realizadas' => $dia['realizadas'] > 0 && ($dia['realizadas'] + ($dia['no_realizadas'] ?? 0)) > 0
                        ? round(($dia['realizadas'] / ($dia['realizadas'] + ($dia['no_realizadas'] ?? 0))) * 100, 1)
                        : 0,
                ]);
            }
        }
        
        return $collection;
    }

    public function headings(): array
    {
        return [
            'Fecha',
            'Clases Realizadas',
            'Clases No Realizadas',
            'Clases Recuperadas',
            'Total de Clases',
            '% Realizadas'
        ];
    }

    public function map($row): array
    {
        return [
            $row['fecha'],
            $row['realizadas'],
            $row['no_realizadas'],
            $row['recuperadas'],
            $row['total'],
            $row['porcentaje_realizadas'] . '%'
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
                    'startColor' => ['rgb' => '10B981'], // Green color
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
            ],
            'A:F' => [
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
            'A' => 15,  // Fecha
            'B' => 20,  // Clases Realizadas
            'C' => 20,  // Clases No Realizadas
            'D' => 20,  // Clases Recuperadas
            'E' => 18,  // Total de Clases
            'F' => 15,  // % Realizadas
        ];
    }

    public function title(): string
    {
        return 'Clases Realizadas';
    }
}
