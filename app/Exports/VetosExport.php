<?php

namespace App\Exports;

use App\Models\VetoSalaEstudio;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class VetosExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithColumnWidths
{
    protected $estado;

    public function __construct($estado = '')
    {
        $this->estado = $estado;
    }

    public function collection()
    {
        $query = VetoSalaEstudio::select([
            'id',
            'run_vetado',
            'tipo_veto',
            'estado',
            'observacion',
            'fecha_veto',
            'vetado_por',
            'fecha_liberacion',
            'liberado_por'
        ])
        ->with(['solicitante:run,nombre']); // Solo cargar campos necesarios

        if ($this->estado) {
            $query->where('estado', $this->estado);
        }

        return $query->orderBy('fecha_veto', 'desc')
                    ->limit(10000) // Límite para exportación
                    ->get();
    }

    public function headings(): array
    {
        return [
            'ID',
            'RUN',
            'Nombre',
            'Tipo de Veto',
            'Estado',
            'Motivo',
            'Fecha Veto',
            'Vetado Por',
            'Fecha Liberación',
            'Liberado Por'
        ];
    }

    public function map($veto): array
    {
        return [
            $veto->id,
            $veto->run_vetado,
            $veto->solicitante->nombre ?? 'N/A',
            ucfirst($veto->tipo_veto),
            ucfirst($veto->estado),
            $veto->observacion,
            $veto->fecha_veto->format('d/m/Y H:i'),
            $veto->vetado_por ?? 'Sistema',
            $veto->fecha_liberacion ? $veto->fecha_liberacion->format('d/m/Y H:i') : '-',
            $veto->liberado_por ?? '-'
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
                    'startColor' => ['rgb' => 'DC2626'], // Rojo
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
            'A' => 8,   // ID
            'B' => 12,  // RUN
            'C' => 30,  // Nombre
            'D' => 15,  // Tipo de Veto
            'E' => 12,  // Estado
            'F' => 40,  // Motivo
            'G' => 18,  // Fecha Veto
            'H' => 20,  // Vetado Por
            'I' => 18,  // Fecha Liberación
            'J' => 20,  // Liberado Por
        ];
    }
}
