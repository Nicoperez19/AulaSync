<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class HistoricoEspaciosExport implements FromArray, WithHeadings, WithStyles
{
    private $datos;

    public function __construct($datos)
    {
        $this->datos = $datos;
    }

    public function array(): array
    {
        return $this->datos;
    }

    public function headings(): array
    {
        return [
            'Fecha',
            'Hora Inicio',
            'Hora Fin',
            'Espacio',
            'Usuario',
            'Tipo Usuario',
            'Horas Utilizadas',
            'Duración',
            'Estado'
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
