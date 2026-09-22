<?php

namespace App\Exports;

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
use App\Services\TodasClasesService;

class TodasClasesExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithColumnWidths, WithTitle
{
    protected $fechaInicio;
    protected $fechaFin;
    protected $periodo;
    protected $search;
    protected $estado;

    public function __construct($fechaInicio = null, $fechaFin = null, $periodo = null, $search = null, $estado = null)
    {
        $this->fechaInicio = $fechaInicio ? Carbon::parse($fechaInicio) : null;
        $this->fechaFin = $fechaFin ? Carbon::parse($fechaFin) : null;
        $this->periodo = $periodo;
        $this->search = $search;
        $this->estado = $estado;
    }

    public function collection()
    {
        $servicio = new TodasClasesService();
        $clases = $servicio->obtenerTodasLasClases(
            $this->fechaInicio,
            $this->fechaFin,
            $this->periodo,
            $this->search,
            $this->estado
        );
        
        // El Excel los ordena por fecha asc
        return $clases->sortBy([
            ['fecha', 'asc'],
            ['espacio', 'asc'],
            ['modulo', 'asc'],
        ])->values();
    }

    public function headings(): array
    {
        return [
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
            'Hora Entrada',
            'Hora Salida',
            'Motivo',
            'Observaciones',
        ];
    }

    public function map($clase): array
    {
        // Optimizar formato de fecha (evitar parsear si ya es string)
        $fecha = is_string($clase['fecha']) 
            ? Carbon::parse($clase['fecha'])->format('d/m/Y')
            : $clase['fecha']->format('d/m/Y');
            
        return [
            $fecha,
            $clase['dia'],
            $clase['periodo'],
            $clase['profesor'],
            $clase['run_profesor'],
            $clase['asignatura'],
            $clase['codigo_asignatura'],
            $clase['espacio'],
            $clase['modulo'],
            $clase['hora_inicio'],
            $clase['hora_fin'],
            $clase['estado'],
            $clase['hora_entrada'] ?? 'N/A',
            $clase['hora_salida'] ?? 'N/A',
            $clase['motivo'] ?? '',
            $clase['observaciones'] ?? '',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $lastRow = $sheet->getHighestRow();
        
        // Aplicar colores según el estado
        for ($row = 2; $row <= $lastRow; $row++) {
            $estado = $sheet->getCell('L' . $row)->getValue(); // Columna Estado
            
            $fillColor = match($estado) {
                'Realizada'           => 'D1FAE5', // Verde claro
                'No Registrada'       => 'FEE2E2', // Rojo claro
                'Justificada'         => 'FEF3C7', // Amarillo claro
                'Recuperada'          => 'DBEAFE', // Azul claro
                'Feriado/Justificado' => 'EDE9FE', // Violeta claro
                default               => 'FFFFFF', // Blanco
            };
            
            $sheet->getStyle('L' . $row)->applyFromArray([
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => $fillColor],
                ],
            ]);
        }
        
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
                    'startColor' => ['rgb' => '7C3AED'], // Púrpura
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
            'A' => 12,  // Fecha
            'B' => 12,  // Día
            'C' => 12,  // Período
            'D' => 30,  // Profesor
            'E' => 12,  // RUN Profesor
            'F' => 35,  // Asignatura
            'G' => 18,  // Código Asignatura
            'H' => 12,  // Espacio
            'I' => 10,  // Módulo
            'J' => 12,  // Hora Inicio
            'K' => 12,  // Hora Fin
            'L' => 15,  // Estado
            'M' => 12,  // Hora Entrada
            'N' => 12,  // Hora Salida
            'O' => 30,  // Motivo
            'P' => 35,  // Observaciones
        ];
    }

    public function title(): string
    {
        $titulo = 'Todas las Clases';
        
        if ($this->fechaInicio && $this->fechaFin) {
            $inicio = $this->fechaInicio instanceof Carbon ? $this->fechaInicio : Carbon::parse($this->fechaInicio);
            $fin = $this->fechaFin instanceof Carbon ? $this->fechaFin : Carbon::parse($this->fechaFin);
            $titulo .= ' ' . $inicio->format('d-m-Y') . ' a ' . $fin->format('d-m-Y');
        } elseif ($this->periodo) {
            $titulo .= ' ' . $this->periodo;
        }
        
        return substr($titulo, 0, 31); // Excel limita a 31 caracteres
    }
}
