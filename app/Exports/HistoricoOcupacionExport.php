<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class HistoricoOcupacionExport implements FromCollection, WithHeadings
{
    protected $datos;

    public function __construct($datos)
    {
        $this->datos = $datos;
    }

    public function collection()
    {
        // Mapear los datos para solo las columnas requeridas
        return collect($this->datos)->map(function($row) {
            return [
                'fecha' => $row->fecha,
                'carrera' => $row->carrera,
                'asignatura' => $row->asignatura,
                'espacio' => $row->espacio,
                'estado_espacio' => $row->estado_espacio,
                'estado_reserva' => $row->estado_reserva,
            ];
        });
    }

    public function headings(): array
    {
        return [
            'Fecha',
            'Carrera',
            'Asignatura',
            'Espacio',
            'Estado del Espacio',
            'Estado de la Reserva',
        ];
    }
}
