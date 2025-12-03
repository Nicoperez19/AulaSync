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

class ClasesNoRealizadasExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithColumnWidths, WithTitle
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
        // Convert datos array to collection for export - only days with no realizadas
        $collection = collect();
        
        foreach ($this->datos as $diaKey => $dia) {
            if (is_array($dia) && isset($dia['no_realizadas']) && $dia['no_realizadas'] > 0) {
                // Add each class detail if available
                if (isset($dia['clases_no_realizadas_detalle']) && is_array($dia['clases_no_realizadas_detalle'])) {
                    foreach ($dia['clases_no_realizadas_detalle'] as $clase) {
                        $collection->push([
                            'fecha' => $diaKey,
                            'asignatura' => $clase['asignatura'] ?? 'N/A',
                            'profesor' => $clase['profesor'] ?? 'N/A',
                            'modulo' => $clase['modulo'] ?? 'N/A',
                            'hora' => $clase['hora'] ?? '',
                            'estado' => $clase['estado'] ?? 'no_realizada',
                            'motivo' => $clase['motivo'] ?? 'No especificado',
                        ]);
                    }
                } else {
                    // If no detail, add summary row
                    $collection->push([
                        'fecha' => $diaKey,
                        'asignatura' => 'Resumen',
                        'profesor' => '-',
                        'modulo' => '-',
                        'hora' => '-',
                        'estado' => 'Total: ' . $dia['no_realizadas'],
                        'motivo' => '-',
                    ]);
                }
            }
        }
        
        return $collection;
    }

    public function headings(): array
    {
        return [
            'Fecha',
            'Asignatura',
            'Profesor',
            'Módulo',
            'Hora',
            'Estado',
            'Motivo'
        ];
    }

    public function map($row): array
    {
        $estadoTexto = $row['estado'];
        if ($row['estado'] === 'recuperada') {
            $estadoTexto = 'Recuperada';
        } elseif ($row['estado'] === 'pendiente') {
            $estadoTexto = 'Pendiente';
        } elseif ($row['estado'] === 'no_realizada') {
            $estadoTexto = 'No Realizada';
        }

        return [
            $row['fecha'],
            $row['asignatura'],
            $row['profesor'],
            $row['modulo'],
            $row['hora'],
            $estadoTexto,
            $row['motivo']
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
                    'startColor' => ['rgb' => 'EF4444'], // Red color
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
            ],
            'A:G' => [
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_LEFT,
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
            ],
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 12,  // Fecha
            'B' => 30,  // Asignatura
            'C' => 25,  // Profesor
            'D' => 12,  // Módulo
            'E' => 15,  // Hora
            'F' => 15,  // Estado
            'G' => 30,  // Motivo
        ];
    }

    public function title(): string
    {
        return 'Clases No Realizadas';
    }
}
