<?php

namespace App\Exports;

use App\Models\ClaseNoRealizada;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use Carbon\Carbon;

class ClasesNoRealizadasExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithColumnWidths, WithTitle
{
    protected $fechaInicio;
    protected $fechaFin;
    protected $periodo;
    protected $estado;

    public function __construct($fechaInicio = null, $fechaFin = null, $periodo = null, $estado = null)
    {
        $this->fechaInicio = $fechaInicio ? Carbon::parse($fechaInicio) : null;
        $this->fechaFin = $fechaFin ? Carbon::parse($fechaFin) : null;
        $this->periodo = $periodo;
        $this->estado = $estado;
    }

    public function collection()
    {
        $query = ClaseNoRealizada::with(['profesor', 'asignatura', 'espacio', 'modulo'])
            ->orderBy('fecha_clase', 'desc')
            ->orderBy('id_modulo', 'asc');

        // Filtrar por rango de fechas
        if ($this->fechaInicio && $this->fechaFin) {
            $query->whereBetween('fecha_clase', [$this->fechaInicio, $this->fechaFin]);
        } elseif ($this->fechaInicio) {
            $query->where('fecha_clase', '>=', $this->fechaInicio);
        } elseif ($this->fechaFin) {
            $query->where('fecha_clase', '<=', $this->fechaFin);
        }

        // Filtrar por periodo
        if ($this->periodo) {
            $query->where('periodo', $this->periodo);
        }

        // Filtrar por estado
        if ($this->estado) {
            $query->where('estado', $this->estado);
        }

        return $query->get();
    }

    public function headings(): array
    {
        return [
            'ID',
            'Fecha',
            'Día',
            'Período',
            'Profesor',
            'RUN Profesor',
            'Asignatura',
            'Código Asignatura',
            'Espacio',
            'Módulo',
            'Hora Inicio',
            'Hora Fin',
            'Estado',
            'Motivo',
            'Observaciones',
            'Fecha Detección',
        ];
    }

    public function map($clase): array
    {
        $estadoTexto = match($clase->estado) {
            'no_realizada' => 'No Realizada',
            'justificado' => 'Justificada',
            'recuperada' => 'Recuperada',
            default => ucfirst($clase->estado),
        };

        return [
            $clase->id,
            $clase->fecha_clase ? $clase->fecha_clase->format('d/m/Y') : 'N/A',
            $clase->fecha_clase ? ucfirst($clase->fecha_clase->locale('es')->isoFormat('dddd')) : 'N/A',
            $clase->periodo ?? 'N/A',
            $clase->profesor->name ?? 'N/A',
            $clase->run_profesor ?? 'N/A',
            $clase->asignatura->nombre_asignatura ?? 'N/A',
            $clase->asignatura->codigo_asignatura ?? 'N/A',
            $clase->id_espacio ?? 'N/A',
            $clase->modulo ? preg_replace('/^[A-Z]{2}\./', '', $clase->id_modulo) : $clase->id_modulo,
            $clase->modulo->hora_inicio ?? 'N/A',
            $clase->modulo->hora_termino ?? 'N/A',
            $estadoTexto,
            $clase->motivo ?? 'No especificado',
            $clase->observaciones ?? '',
            $clase->hora_deteccion ? $clase->hora_deteccion->format('d/m/Y H:i') : 'N/A',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $lastRow = $sheet->getHighestRow();
        
        return [
            // Estilo para el encabezado
            1 => [
                'font' => [
                    'bold' => true,
                    'color' => ['rgb' => 'FFFFFF'],
                    'size' => 11,
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '2563EB'], // Azul
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                    'wrapText' => true,
                ],
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['rgb' => '000000'],
                    ],
                ],
            ],
            // Estilo para las filas de datos
            'A2:P' . $lastRow => [
                'alignment' => [
                    'vertical' => Alignment::VERTICAL_CENTER,
                    'wrapText' => true,
                ],
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['rgb' => 'CCCCCC'],
                    ],
                ],
            ],
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 8,   // ID
            'B' => 12,  // Fecha
            'C' => 12,  // Día
            'D' => 12,  // Período
            'E' => 30,  // Profesor
            'F' => 12,  // RUN Profesor
            'G' => 35,  // Asignatura
            'H' => 18,  // Código Asignatura
            'I' => 12,  // Espacio
            'J' => 10,  // Módulo
            'K' => 12,  // Hora Inicio
            'L' => 12,  // Hora Fin
            'M' => 15,  // Estado
            'N' => 25,  // Motivo
            'O' => 35,  // Observaciones
            'P' => 18,  // Fecha Detección
        ];
    }

    public function title(): string
    {
        $titulo = 'Clases No Realizadas';
        
        if ($this->fechaInicio && $this->fechaFin) {
            $titulo .= ' ' . $this->fechaInicio->format('d-m-Y') . ' a ' . $this->fechaFin->format('d-m-Y');
        } elseif ($this->periodo) {
            $titulo .= ' Periodo ' . $this->periodo;
        }
        
        return substr($titulo, 0, 31); // Excel limita a 31 caracteres
    }
}
