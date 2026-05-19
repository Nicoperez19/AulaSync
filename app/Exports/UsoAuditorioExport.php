<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class UsoAuditorioExport implements FromArray, WithHeadings, WithStyles
{
    private $datos;

    public function __construct($datos)
    {
        $this->datos = $datos;
    }

    public function array(): array
    {
        return $this->datos->toArray();
    }

    public function headings(): array
    {
        return ['Usuario', 'Auditorio', 'Fecha', 'Hora Entrada', 'Hora Salida', 'Duración', 'Motivo/Asignatura', 'Estado'];
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->getStyle('A1:H1')->getFont()->setBold(true);
        $sheet->getStyle('A1:H1')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setRGB('E2E8F0');
        foreach (range('A', 'H') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
        return $sheet;
    }
}
