<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class AnalisisEspaciosExport implements FromArray, WithHeadings, WithStyles
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
            'ID Espacio',
            'Nombre',
            'Tipo de Espacio',
            'Piso',
            'Facultad',
            'Estado',
            'Puestos Disponibles',
            'Total Reservas',
            'Horas Utilizadas',
            'Promedio Utilización',
            'Estado Utilización'
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
