<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class SupportTicketsReportExport implements WithMultipleSheets
{
    public function __construct(
        private string $period,
        private string $site,
        private string $school,
        private string $statusCondition,
        private string $priorityCondition,
        private string $categoryCondition,
        private array $stats,
        private string $averageResolution,
        private array $sites,
        private array $schools,
        private array $priorities,
        private array $categories,
        private array $tickets,
        private string $generatedBy,
        private string $generatedAt,
    ) {}

    public function sheets(): array
    {
        $summary = [
            ['UNIVERSIDAD CATÓLICA DE LA SANTÍSIMA CONCEPCIÓN', ''],
            ['INSTITUTO TECNOLÓGICO · SEDE TALCAHUANO · UNIDAD DE SOPORTE', ''],
            ['REPORTE OFICIAL DE GESTIÓN DE SOPORTE', ''],
            ['Período', $this->period],
            ['Sede seleccionada', $this->site],
            ['Escuela seleccionada', $this->school],
            ['Condición / estado', $this->statusCondition],
            ['Prioridad', $this->priorityCondition],
            ['Tipo de problema', $this->categoryCondition],
            ['Generado por', $this->generatedBy],
            ['Fecha de emisión', $this->generatedAt],
            [],
            ['Indicador', 'Resultado'],
            ['Tickets solicitados', $this->stats['total']],
            ['Pendientes', $this->stats['open']],
            ['En proceso', $this->stats['in_progress']],
            ['Cerrados', $this->stats['closed']],
            ['Sedes con solicitudes', $this->stats['sedes']],
            ['Escuelas con solicitudes', $this->stats['escuelas']],
            ['Prioridad alta', $this->stats['priority_high']],
            ['Prioridad media', $this->stats['priority_medium']],
            ['Prioridad baja', $this->stats['priority_low']],
            ['Tipo de problema más reportado', $this->stats['top_category']?->label ?? 'Sin datos'],
            ['Tiempo promedio de resolución', $this->averageResolution],
        ];

        return [
            new SupportTicketsReportSheet('Resumen', $summary, [1, 2, 3, 13]),
            new SupportTicketsReportSheet('Por sede', array_merge(
                [['Sede', 'Solicitados', 'Pendientes', 'En proceso', 'Cerrados', 'Prioridad baja', 'Prioridad media', 'Prioridad alta', 'Acceso y cuenta', 'Red y conectividad', 'Equipos y hardware', 'Software y plataforma', 'Reservas y espacios', 'Otro']],
                $this->sites
            )),
            new SupportTicketsReportSheet('Por escuela', array_merge(
                [['Escuela', 'Solicitados', 'Pendientes', 'En proceso', 'Cerrados', 'Prioridad baja', 'Prioridad media', 'Prioridad alta', 'Acceso y cuenta', 'Red y conectividad', 'Equipos y hardware', 'Software y plataforma', 'Reservas y espacios', 'Otro']],
                $this->schools
            )),
            new SupportTicketsReportSheet('Prioridades', array_merge(
                [['Prioridad', 'Solicitados']],
                $this->priorities
            )),
            new SupportTicketsReportSheet('Tipos de problema', array_merge(
                [['Tipo de problema', 'Solicitados']],
                $this->categories
            )),
            new SupportTicketsReportSheet('Tickets', array_merge(
                [['N° ticket', 'Título', 'Descripción', 'Solicitante', 'Sede', 'Escuela', 'Tipo de problema', 'Prioridad', 'Estado', 'Asignado a', 'Respuestas', 'Fecha de solicitud', 'Fecha de cierre']],
                $this->tickets
            )),
        ];
    }
}

class SupportTicketsReportSheet implements FromArray, WithTitle, WithStyles, ShouldAutoSize
{
    public function __construct(private string $title, private array $rows, private array $headerRows = [1]) {}

    public function array(): array
    {
        return $this->rows;
    }

    public function title(): string
    {
        return $this->title;
    }

    public function styles(Worksheet $sheet): array
    {
        $styles = [];
        foreach ($this->headerRows as $row) {
            $styles[$row] = [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => '26374A']],
                'alignment' => ['horizontal' => 'center', 'vertical' => 'center'],
            ];
        }

        return $styles;
    }
}
