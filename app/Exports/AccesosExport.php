<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class AccesosExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithColumnWidths
{
    protected $accesos;

    public function __construct($accesos)
    {
        $this->accesos = $accesos;
    }

    public function collection()
    {
        return $this->accesos;
    }

    public function headings(): array
    {
        return [
            'ID',
            'Usuario',
            'RUN',
            'Email',
            'Tipo Usuario',
            'Espacio',
            'Piso',
            'Facultad',
            'Fecha',
            'Hora Entrada',
            'Hora Salida',
            'Duración',
            'Tipo Reserva',
            'Estado'
        ];
    }

    public function map($acceso): array
    {
        return [
            $acceso['id'],
            $acceso['usuario'],
            $acceso['run'],
            $acceso['email'],
            ucfirst($acceso['tipo_usuario']),
            $acceso['espacio'] . ' (' . $acceso['id_espacio'] . ')',
            $acceso['piso'],
            $acceso['facultad'],
            $acceso['fecha'],
            $acceso['hora_entrada'],
            $acceso['hora_salida'],
            $acceso['duracion'],
            $acceso['tipo_reserva'],
            ucfirst($acceso['estado'])
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            // Estilo para el encabezado
            1 => [
                'font' => [
                    'bold' => true,
                    'color' => ['rgb' => 'FFFFFF'],
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '34495E'],
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
            ],
            // Estilo para las filas de datos
            'A:N' => [
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
            'A' => 10,  // ID
            'B' => 25,  // Usuario
            'C' => 15,  // RUN
            'D' => 30,  // Email
            'E' => 15,  // Tipo Usuario
            'F' => 25,  // Espacio
            'G' => 10,  // Piso
            'H' => 25,  // Facultad
            'I' => 12,  // Fecha
            'J' => 12,  // Hora Entrada
            'K' => 12,  // Hora Salida
            'L' => 15,  // Duración
            'M' => 15,  // Tipo Reserva
            'N' => 12,  // Estado
        ];
    }
} 