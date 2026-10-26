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
            'UA',
            'Asignatura',
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
            $acceso['ua'] ?? 'N/A',
            $acceso['asignatura'] ?? 'N/A',
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
            'A:P' => [
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
            'A' => 12,  // ID
            'B' => 28,  // Usuario
            'C' => 15,  // RUN
            'D' => 28,  // Email
            'E' => 15,  // Tipo Usuario
            'F' => 25,  // UA
            'G' => 30,  // Asignatura
            'H' => 25,  // Espacio
            'I' => 10,  // Piso
            'J' => 25,  // Facultad
            'K' => 14,  // Fecha
            'L' => 14,  // Hora Entrada
            'M' => 14,  // Hora Salida
            'N' => 15,  // Duración
            'O' => 15,  // Tipo Reserva
            'P' => 12,  // Estado
        ];
    }
} 