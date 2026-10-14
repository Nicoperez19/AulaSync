<?php

namespace App\Exports;

use App\Helpers\ModulosHelper;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class OcupacionSemanalExport implements FromArray, WithColumnWidths, WithStyles, WithTitle
{
    protected array $datos;

    protected string $titulo;

    public function __construct(array $datos, string $titulo = 'Ocupación Semanal')
    {
        $this->datos = $datos;
        $this->titulo = $titulo;
    }

    public function array(): array
    {
        $ocupacion = $this->datos['ocupacion'] ?? [];
        $resumen = $this->datos['resumen'] ?? [];
        $tipo = ucfirst(str_replace('_', ' ', $this->datos['tipo_filtro'] ?? 'Todos'));
        $periodo = $this->datos['periodo'] ?? 'N/A';
        $inicio = $this->datos['semana_inicio'] ?? '';
        $fin = $this->datos['semana_fin'] ?? '';
        $totalEspacios = $this->datos['total_espacios'] ?? 0;

        $rows = [];

        // Fila 1: Título principal
        $rows[] = ['SIA - REPORTE DE OCUPACIÓN SEMANAL'];

        // Fila 2: Subtítulo con metadata
        $rows[] = ["Período: {$periodo} | Semana: {$inicio} al {$fin} | Filtro: {$tipo} | Total Espacios: {$totalEspacios}"];

        // Fila 3: Espacio en blanco
        $rows[] = [''];

        // Fila 4: Encabezados de tabla
        $rows[] = [
            'Módulo',
            'Horario',
            'Lunes',
            'Martes',
            'Miércoles',
            'Jueves',
            'Viernes',
            'Sábado',
        ];

        // Filas 5 a 19: Módulos 1 al 15
        for ($m = 1; $m <= 15; $m++) {
            $horario = ModulosHelper::getHorarioModulo('lunes', $m);
            $inicioH = substr($horario['inicio'] ?? '00:00', 0, 5);
            $finH = substr($horario['fin'] ?? '00:00', 0, 5);
            $horarioStr = "{$inicioH} - {$finH}";

            $row = [
                "Módulo {$m}",
                $horarioStr,
            ];

            foreach (['lunes', 'martes', 'miercoles', 'jueves', 'viernes', 'sabado'] as $dia) {
                $val = $ocupacion[$dia][$m] ?? null;
                if ($val === null) {
                    $row[] = 'N/A';
                } else {
                    $row[] = "{$val}%";
                }
            }

            $rows[] = $row;
        }

        // Fila 20: Promedios por día
        $promediosDia = $resumen['promedios_por_dia'] ?? [];
        $rowProm = ['Promedio Día', '—'];
        foreach (['lunes', 'martes', 'miercoles', 'jueves', 'viernes', 'sabado'] as $dia) {
            $prom = $promediosDia[$dia] ?? 0;
            $rowProm[] = "{$prom}%";
        }
        $rows[] = $rowProm;

        // Fila 21: Espacio en blanco
        $rows[] = [''];

        // Fila 22: Resumen ejecutivo
        $promGeneral = $resumen['promedio_general'] ?? 0;
        $picoMax = $resumen['pico_maximo'] ?? 0;
        $diaPico = ucfirst($resumen['dia_pico'] ?? 'N/A');
        $modPico = $resumen['modulo_pico'] ?? 'N/A';

        $rows[] = ['RESUMEN DE OCUPACIÓN', '', '', '', '', '', '', ''];
        $rows[] = ['Ocupación Promedio General:', "{$promGeneral}%", '', 'Pico Máximo Registrado:', "{$picoMax}%", "Día: {$diaPico} (Mód. {$modPico})", '', ''];
        $rows[] = ['Criterio de alertas:', 'Baja: 0% - 35% (Verde)', 'Media: 36% - 75% (Ámbar)', 'Alta: 76% - 100% (Rojo)', '', '', '', ''];

        return $rows;
    }

    public function styles(Worksheet $sheet)
    {
        // Título principal A1:H1
        $sheet->mergeCells('A1:H1');
        $sheet->mergeCells('A2:H2');
        $sheet->mergeCells('A22:H22');

        $sheet->getStyle('A1')->applyFromArray([
            'font' => ['bold' => true, 'size' => 14, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'D2091E']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ]);
        $sheet->getRowDimension(1)->setRowHeight(28);

        $sheet->getStyle('A2')->applyFromArray([
            'font' => ['bold' => true, 'size' => 10, 'color' => ['rgb' => '334155']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F1F5F9']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ]);
        $sheet->getRowDimension(2)->setRowHeight(20);

        // Encabezados de tabla en fila 4 (A4:H4)
        $sheet->getStyle('A4:H4')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => '1E293B'], 'size' => 10],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'E2E8F0']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '94A3B8']]],
        ]);
        $sheet->getRowDimension(4)->setRowHeight(24);

        // Estilos para las celdas de ocupación (Filas 5 a 19, columnas C a H)
        $ocupacion = $this->datos['ocupacion'] ?? [];
        $dias = ['lunes', 'martes', 'miercoles', 'jueves', 'viernes', 'sabado'];
        $cols = ['C', 'D', 'E', 'F', 'G', 'H'];

        for ($m = 1; $m <= 15; $m++) {
            $rowNum = 4 + $m;
            $sheet->getRowDimension($rowNum)->setRowHeight(20);

            // Módulo y Horario
            $sheet->getStyle("A{$rowNum}:B{$rowNum}")->applyFromArray([
                'font' => ['bold' => true, 'size' => 9, 'color' => ['rgb' => '334155']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'CBD5E1']]],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F8FAFC']],
            ]);

            foreach ($dias as $idx => $dia) {
                $col = $cols[$idx];
                $cell = "{$col}{$rowNum}";
                $val = $ocupacion[$dia][$m] ?? null;

                if ($val === null) {
                    $sheet->getStyle($cell)->applyFromArray([
                        'font' => ['color' => ['rgb' => '94A3B8'], 'size' => 9],
                        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F1F5F9']],
                        'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                        'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_HAIR, 'color' => ['rgb' => 'CBD5E1']]],
                    ]);
                } else {
                    $porcentaje = (int) $val;
                    if ($porcentaje <= 35) {
                        $bg = 'D1FAE5'; // Verde suave
                        $fg = '065F46'; // Verde oscuro
                    } elseif ($porcentaje <= 75) {
                        $bg = 'FEF3C7'; // Ámbar suave
                        $fg = '92400E'; // Ámbar oscuro
                    } else {
                        $bg = 'FEE2E2'; // Rojo suave
                        $fg = '991B1B'; // Rojo oscuro
                    }

                    $sheet->getStyle($cell)->applyFromArray([
                        'font' => ['bold' => true, 'color' => ['rgb' => $fg], 'size' => 9],
                        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $bg]],
                        'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                        'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'CBD5E1']]],
                    ]);
                }
            }
        }

        // Fila 20: Promedio por día
        $sheet->getStyle('A20:H20')->applyFromArray([
            'font' => ['bold' => true, 'size' => 9, 'color' => ['rgb' => '1E293B']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'E2E8F0']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_MEDIUM, 'color' => ['rgb' => '94A3B8']]],
        ]);
        $sheet->getRowDimension(20)->setRowHeight(22);

        // Fila 22: Encabezado Resumen
        $sheet->getStyle('A22')->applyFromArray([
            'font' => ['bold' => true, 'size' => 10, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '334155']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT, 'vertical' => Alignment::VERTICAL_CENTER],
        ]);
        $sheet->getRowDimension(22)->setRowHeight(22);

        // Fila 23 & 24: Resumen
        $sheet->getStyle('A23:H24')->applyFromArray([
            'font' => ['size' => 9, 'color' => ['rgb' => '334155']],
            'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
        ]);

        return [];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 16,
            'B' => 18,
            'C' => 14,
            'D' => 14,
            'E' => 14,
            'F' => 14,
            'G' => 14,
            'H' => 14,
        ];
    }

    public function title(): string
    {
        return substr($this->titulo, 0, 31);
    }
}
