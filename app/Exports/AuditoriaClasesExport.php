<?php

namespace App\Exports;

use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class AuditoriaClasesExport implements FromCollection, WithColumnWidths, WithHeadings, WithMapping, WithStyles, WithTitle
{
    protected $query;

    public function __construct($query)
    {
        $this->query = $query;
    }

    public function collection()
    {
        return $this->query->with(['profesor', 'asignatura', 'espacio'])->get();
    }

    public function headings(): array
    {
        return [
            'ID',
            'Fecha y Hora',
            'Usuario Responsable',
            'Rol',
            'Acción Realizada',
            'RUN Docente',
            'Nombre Docente',
            'Asignatura',
            'Espacio',
            'Módulo',
            'Fecha de la Clase',
            'Estado Anterior',
            'Estado Nuevo',
            'Motivo Declarado',
            'Observaciones / Detalle',
            'IP',
            'ID de Lote (Masivo)',
        ];
    }

    public function map($row): array
    {
        $fechaClase = $row->fecha_clase ? Carbon::parse($row->fecha_clase)->format('d/m/Y') : 'N/A';
        $profesorNombre = $row->profesor ? $row->profesor->name : 'N/A';
        $asignaturaNombre = $row->asignatura ? $row->asignatura->nombre_asignatura : ($row->id_asignatura ?? 'N/A');

        return [
            $row->id,
            $row->created_at->format('d/m/Y H:i:s'),
            $row->user_nombre,
            $row->user_rol ?? 'N/A',
            ucfirst(str_replace('_', ' ', $row->accion)),
            $row->run_profesor ?? 'N/A',
            $profesorNombre,
            $asignaturaNombre,
            $row->id_espacio ?? 'N/A',
            $row->id_modulo ?? 'N/A',
            $fechaClase,
            $row->estado_anterior ?? '—',
            $row->estado_nuevo ?? '—',
            $row->motivo ?? '—',
            $row->observaciones ?? '—',
            $row->ip ?? 'N/A',
            $row->lote_uuid ?? 'N/A',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $lastRow = $sheet->getHighestRow();

        $sheet->getStyle('A1:Q1')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 10],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1E293B']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '000000']]],
        ]);
        $sheet->getRowDimension(1)->setRowHeight(25);

        if ($lastRow > 1) {
            $sheet->getStyle("A2:Q{$lastRow}")->applyFromArray([
                'font' => ['size' => 9],
                'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'E2E8F0']]],
            ]);
        }

        return [];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 8,
            'B' => 18,
            'C' => 22,
            'D' => 16,
            'E' => 18,
            'F' => 14,
            'G' => 26,
            'H' => 26,
            'I' => 14,
            'J' => 12,
            'K' => 14,
            'L' => 16,
            'M' => 16,
            'N' => 26,
            'O' => 32,
            'P' => 16,
            'Q' => 36,
        ];
    }

    public function title(): string
    {
        return 'Auditoría Justificaciones';
    }
}
