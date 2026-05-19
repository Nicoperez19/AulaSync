<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class OcupacionHorariosExport implements FromArray, WithHeadings, WithStyles
{
    private $datos;
    private $moduloInicio;
    private $moduloFin;
    private $modulosDia;

    public function __construct($datos, $moduloInicio, $moduloFin, $modulosDia)
    {
        $this->datos = $datos;
        $this->moduloInicio = $moduloInicio;
        $this->moduloFin = $moduloFin;
        $this->modulosDia = $modulosDia;
    }

    public function array(): array
    {
        return $this->datos;
    }

    public function headings(): array
    {
        $headers = ['Espacio', 'Tipo', 'Piso', 'Facultad'];

        for ($i = $this->moduloInicio; $i <= $this->moduloFin; $i++) {
            $headers[] = 'Módulo ' . ($i + 1) . ' (' . $this->modulosDia[$i] . ')';
        }

        return $headers;
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
