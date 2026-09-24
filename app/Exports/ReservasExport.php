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

class ReservasExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithColumnWidths, WithTitle
{
    protected $reservas;
    protected $tipoEspacio;
    protected $fechaInicio;
    protected $fechaFin;

    public function __construct($reservas, $tipoEspacio = null, $fechaInicio = null, $fechaFin = null)
    {
        $this->reservas = $reservas;
        $this->tipoEspacio = $tipoEspacio;
        $this->fechaInicio = $fechaInicio ? Carbon::parse($fechaInicio) : null;
        $this->fechaFin = $fechaFin ? Carbon::parse($fechaFin) : null;
    }

    public function collection()
    {
        return $this->reservas;
    }

    public function headings(): array
    {
        return [
            'ID Reserva',
            'Fecha',
            'Espacio',
            'Tipo de Espacio',
            'Ubicación / Piso',
            'Usuario / Solicitante',
            'RUN',
            'Tipo Usuario',
            'Hora Inicio',
            'Hora Fin',
            'Módulos',
            'Actividad / Asignatura',
            'Tipo Reserva',
            'Estado',
            'Registrado Por',
            'Observaciones',
        ];
    }

    public function map($reserva): array
    {
        $fecha = 'N/A';
        if (!empty($reserva->fecha_reserva)) {
            $fecha = is_string($reserva->fecha_reserva)
                ? Carbon::parse($reserva->fecha_reserva)->format('d/m/Y')
                : $reserva->fecha_reserva->format('d/m/Y');
        }

        $usuario = $reserva->nombre_usuario ?? 'N/A';
        $run = $reserva->run_profesor ?: ($reserva->run_solicitante ?: 'N/A');

        $tipoUsuario = 'Externo/Desconocido';
        if ($reserva->run_profesor) {
            $tipoUsuario = 'Profesor';
        } elseif ($reserva->solicitante) {
            $tipoUsuario = ucfirst($reserva->solicitante->tipo_solicitante ?? 'Solicitante');
        }

        $horaInicio = $reserva->hora ? substr($reserva->hora, 0, 5) : 'N/A';
        $horaFin = $reserva->hora_salida ? substr($reserva->hora_salida, 0, 5) : 'N/A';

        $actividad = $reserva->nombre_actividad
            ?: ($reserva->asignatura->nombre_asignatura ?? ($reserva->descripcion_actividad ?: 'Sin actividad especificada'));

        $piso = 'N/A';
        if ($reserva->espacio && $reserva->espacio->piso) {
            $piso = 'Piso ' . $reserva->espacio->piso->numero_piso;
            if ($reserva->espacio->piso->facultad) {
                $piso .= ' (' . ($reserva->espacio->piso->facultad->nombre_facultad ?? '') . ')';
            }
        }

        return [
            $reserva->id_reserva,
            $fecha,
            $reserva->espacio->nombre_espacio ?? $reserva->id_espacio ?? 'N/A',
            $reserva->espacio->tipo_espacio ?? 'N/A',
            $piso,
            $usuario,
            $run,
            $tipoUsuario,
            $horaInicio,
            $horaFin,
            $reserva->modulos ?? 1,
            $actividad,
            ucfirst($reserva->tipo_reserva ?? 'Directa'),
            ucfirst($reserva->estado ?? 'Desconocido'),
            $reserva->creado_por ?? 'Sistema',
            $reserva->observaciones ?? '',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $lastRow = $sheet->getHighestRow();

        // Aplicar colores según el estado (Columna N = Estado)
        for ($row = 2; $row <= $lastRow; $row++) {
            $estado = strtolower((string)$sheet->getCell('N' . $row)->getValue());

            $fillColor = match($estado) {
                'activa'     => 'D1FAE5', // Verde claro
                'finalizada' => 'E2E8F0', // Gris claro / azul pizarra
                'programada' => 'FEF3C7', // Amarillo claro
                'cancelada'  => 'FEE2E2', // Rojo claro
                default      => 'FFFFFF', // Blanco
            };

            $sheet->getStyle('N' . $row)->applyFromArray([
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => $fillColor],
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
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
                    'startColor' => ['rgb' => '0284C7'], // Azul institucional
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
                ],
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['rgb' => 'E5E7EB'],
                    ],
                ],
            ],
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 18,  // ID Reserva
            'B' => 13,  // Fecha
            'C' => 20,  // Espacio
            'D' => 22,  // Tipo de Espacio
            'E' => 25,  // Ubicación / Piso
            'F' => 30,  // Usuario / Solicitante
            'G' => 14,  // RUN
            'H' => 18,  // Tipo Usuario
            'I' => 12,  // Hora Inicio
            'J' => 12,  // Hora Fin
            'K' => 10,  // Módulos
            'L' => 35,  // Actividad / Asignatura
            'M' => 16,  // Tipo Reserva
            'N' => 15,  // Estado
            'O' => 25,  // Registrado Por
            'P' => 35,  // Observaciones
        ];
    }

    public function title(): string
    {
        $titulo = 'Reservas';

        if ($this->tipoEspacio) {
            $titulo .= ' ' . $this->tipoEspacio;
        }

        if ($this->fechaInicio && $this->fechaFin) {
            $titulo .= ' ' . $this->fechaInicio->format('d-m-Y') . ' a ' . $this->fechaFin->format('d-m-Y');
        }

        return substr($titulo, 0, 31);
    }
}
