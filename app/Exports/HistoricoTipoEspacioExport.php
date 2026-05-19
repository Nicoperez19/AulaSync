<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class HistoricoTipoEspacioExport implements FromArray, WithHeadings, WithStyles
{
    private $datos;
    private $fechaInicio;
    private $fechaFin;
    private $tipoEspacio;

    public function __construct($datos, $fechaInicio, $fechaFin, $tipoEspacio)
    {
        $this->datos = $datos;
        $this->fechaInicio = $fechaInicio;
        $this->fechaFin = $fechaFin;
        $this->tipoEspacio = $tipoEspacio;
    }

    public function array(): array
    {
        return $this->datos->toArray();
    }

    public function headings(): array
    {
        return [
            'Profesor/Solicitante',
            'RUN',
            'Email',
            'Espacio',
            'Facultad',
            'Fecha',
            'Hora Entrada',
            'Hora Salida',
            'Duración',
            'Tipo Usuario',
            'Estado'
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->getStyle('A1:K1')->getFont()->setBold(true);
        $sheet->getStyle('A1:K1')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setRGB('E2E8F0');

        foreach (range('A', 'K') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        return $sheet;
    }
}
