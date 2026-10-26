<?php

namespace App\Exports;

use App\Services\TodasClasesService;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use Illuminate\Support\Collection;
use Carbon\Carbon;
use App\Services\TodasClasesService;
use App\Helpers\SemesterHelper;

class TodasClasesExport implements WithMultipleSheets
{
    protected $fechaInicio;

    protected $fechaFin;

    protected $periodo;

    protected $search;

    protected $estado;

    protected $ua;

    public function __construct($fechaInicio = null, $fechaFin = null, $periodo = null, $search = null, $estado = null, $ua = null)
    {
        $this->fechaInicio = $fechaInicio ? Carbon::parse($fechaInicio) : null;
        $this->fechaFin = $fechaFin ? Carbon::parse($fechaFin) : null;
        $this->periodo = $periodo;
        $this->search = $search;
        $this->estado = $estado;
        $this->ua = $ua;
    }

    public function sheets(): array
    {
        $servicio = new TodasClasesService;
        $clases = $servicio->obtenerTodasLasClases(
            $this->fechaInicio,
            $this->fechaFin,
            $this->periodo,
            $this->search,
            $this->estado,
            $this->ua
        );

        // Si se solicitó un semestre específico, generar la hoja exclusiva de ese semestre
        if (!empty($this->periodo)) {
            $clasesSemestre = $clases->filter(function ($item) {
                return ($item['periodo'] ?? $this->periodo) === $this->periodo;
            })->sortBy([
                ['fecha', 'asc'],
                ['espacio', 'asc'],
                ['modulo', 'asc'],
            ])->values();

            return [
                new TodasClasesSemestreSheet($clasesSemestre, $this->periodo),
            ];
        }

        // Si no se filtró por un único semestre, separar automáticamente en una hoja por cada semestre
        $porPeriodo = $clases->groupBy(function ($item) {
            return !empty($item['periodo']) && $item['periodo'] !== 'N/A'
                ? $item['periodo']
                : 'Sin Semestre';
        })->sortKeys();

        if ($porPeriodo->isEmpty()) {
            $periodoActual = \App\Helpers\SemesterHelper::getCurrentPeriod();
            return [
                new TodasClasesSemestreSheet(collect(), $periodoActual),
            ];
        }

        $sheets = [];
        foreach ($porPeriodo as $codigoPeriodo => $itemsPeriodo) {
            $itemsOrdenados = $itemsPeriodo->sortBy([
                ['fecha', 'asc'],
                ['espacio', 'asc'],
                ['modulo', 'asc'],
            ])->values();

            $sheets[] = new TodasClasesSemestreSheet($itemsOrdenados, (string) $codigoPeriodo);
        }

        return $sheets;
    }
}

class TodasClasesSemestreSheet implements FromCollection, WithHeadings, WithMapping, WithStyles, WithColumnWidths, WithTitle
{
    protected Collection $clases;
    protected string $periodo;

    public function __construct(Collection $clases, string $periodo)
    {
        $this->clases = $clases;
        $this->periodo = $periodo;
    }

    public function collection()
    {
        return $this->clases;
    }

    public function headings(): array
    {
        return [
            'Fecha',
            'Día',
            'Período',
            'Profesor',
            'RUN Profesor',
            'UA (UNIDAD ACADÉMICA)',
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
        $fecha = 'N/A';
        if (! empty($clase['fecha'])) {
            $fecha = is_string($clase['fecha'])
                ? Carbon::parse($clase['fecha'])->format('d/m/Y')
                : $clase['fecha']->format('d/m/Y');
        }

        return [
            $fecha,
            $clase['dia'],
            $clase['periodo'] ?? $this->periodo,
            $clase['profesor'],
            $clase['run_profesor'],
            $clase['ua'] ?? 'N/A',
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
            $estado = $sheet->getCell('M'.$row)->getValue(); // Columna Estado (M tras añadir UA)

            $fillColor = match ($estado) {
                'Realizada' => 'D1FAE5', // Verde claro
                'No Registrada' => 'FEE2E2', // Rojo claro
                'Justificada' => 'FEF3C7', // Amarillo claro
                'Recuperada' => 'DBEAFE', // Azul claro
                'Feriado/Justificado' => 'EDE9FE', // Violeta claro
                default => 'FFFFFF', // Blanco
            };

            $sheet->getStyle('M'.$row)->applyFromArray([
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => $fillColor],
                ],
            ]);
        }
        
        $styles = [
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
        ];

        if ($lastRow >= 2) {
            // Estilo para las filas de datos
            $styles['A2:Q' . $lastRow] = [
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
            ];
        }

        return $styles;
    }

    public function columnWidths(): array
    {
        return [
            'A' => 12,  // Fecha
            'B' => 12,  // Día
            'C' => 12,  // Período
            'D' => 30,  // Profesor
            'E' => 12,  // RUN Profesor
            'F' => 25,  // UA (UNIDAD ACADÉMICA)
            'G' => 35,  // Asignatura
            'H' => 18,  // Código Asignatura
            'I' => 12,  // Espacio
            'J' => 10,  // Módulo
            'K' => 12,  // Hora Inicio
            'L' => 12,  // Hora Fin
            'M' => 15,  // Estado
            'N' => 12,  // Hora Entrada
            'O' => 12,  // Hora Salida
            'P' => 30,  // Motivo
            'Q' => 35,  // Observaciones
        ];
    }

    public function title(): string
    {
        if (preg_match('/^(\d{4})-([12])$/', $this->periodo, $m)) {
            $titulo = "{$m[2]}° Semestre {$m[1]} ({$this->periodo})";
        } else {
            $titulo = 'Semestre ' . $this->periodo;
        }

        return substr($titulo, 0, 31); // Excel limita a 31 caracteres
    }
}
