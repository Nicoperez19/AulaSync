<?php

namespace App\Exports;

use App\Models\IncidenciaEspacio;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

/**
 * Exporta a Excel las incidencias de espacios con los mismos filtros del tablero.
 */
class IncidenciasEspaciosExport implements FromQuery, WithHeadings, WithMapping, WithStyles, WithColumnWidths, WithTitle
{
    public function __construct(protected Builder $query)
    {
    }

    public function query()
    {
        return $this->query;
    }

    public function title(): string
    {
        return 'Incidencias';
    }

    public function headings(): array
    {
        return [
            'ID',
            'Espacio',
            'Nombre del espacio',
            'Tipo de falla',
            'Severidad',
            'Estado',
            'Sala fuera de servicio',
            'Descripción',
            'Reportado por',
            'Fecha de reporte',
            'Atendido por',
            'Resolución / motivo de cierre',
            'Cerrado por',
            'Fecha de cierre',
            'Horas abierta',
        ];
    }

    /** @param  IncidenciaEspacio  $i */
    public function map($i): array
    {
        return [
            $i->id,
            $i->id_espacio,
            $i->espacio?->nombre_espacio ?? '—',
            $i->categoria_label,
            $i->severidad_label,
            $i->estado_label,
            $i->bloquea_uso ? 'Sí' : 'No',
            $i->descripcion,
            $i->reportado_por_nombre ?? '—',
            $i->reportado_en?->format('d/m/Y H:i'),
            $i->asignado_a ?? '—',
            $i->resolucion ?? '—',
            $i->resuelto_por_nombre ?? '—',
            $i->resuelto_en?->format('d/m/Y H:i') ?? '—',
            $i->horas_abierta,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // Texto largo con salto de línea en descripción y resolución
        $sheet->getStyle('H:H')->getAlignment()->setWrapText(true);
        $sheet->getStyle('L:L')->getAlignment()->setWrapText(true);
        $sheet->getStyle('A:O')->getAlignment()->setVertical(Alignment::VERTICAL_TOP);
        $sheet->freezePane('A2');

        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'D2091E'],
                ],
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
            'A' => 7,   // ID
            'B' => 11,  // Espacio
            'C' => 26,  // Nombre
            'D' => 30,  // Tipo de falla
            'E' => 11,  // Severidad
            'F' => 12,  // Estado
            'G' => 14,  // Fuera de servicio
            'H' => 50,  // Descripción
            'I' => 22,  // Reportado por
            'J' => 17,  // Fecha de reporte
            'K' => 20,  // Atendido por
            'L' => 40,  // Resolución
            'M' => 22,  // Cerrado por
            'N' => 17,  // Fecha de cierre
            'O' => 12,  // Horas abierta
        ];
    }
}
