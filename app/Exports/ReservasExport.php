<?php

namespace App\Exports;

use App\Livewire\ReservationsTable;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ReservasExport implements WithMultipleSheets
{
    protected $reservas;
    protected $tipoEspacio;
    protected $fechaInicio;
    protected $fechaFin;
    protected $metricas;

    public function __construct($reservas, $tipoEspacio = null, $fechaInicio = null, $fechaFin = null, array $metricas = [])
    {
        $this->reservas = $reservas;
        $this->tipoEspacio = $tipoEspacio;
        $this->fechaInicio = $fechaInicio;
        $this->fechaFin = $fechaFin;
        $this->metricas = !empty($metricas)
            ? $metricas
            : ReservationsTable::calcularMetricasOcupacion($reservas, $tipoEspacio, $fechaInicio, $fechaFin);
    }

    public function sheets(): array
    {
        return [
            new ReservasDetalleSheet($this->reservas, $this->tipoEspacio, $this->fechaInicio, $this->fechaFin),
            new ReservasResumenOcupacionSheet($this->metricas, $this->tipoEspacio),
        ];
    }
}

class ReservasDetalleSheet implements FromCollection, WithHeadings, WithMapping, WithStyles, WithColumnWidths, WithTitle
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
            'Código Sala',
            'Espacio',
            'Tipo de Espacio',
            'Ubicación / Piso',
            'Usuario / Solicitante',
            'RUN',
            'Categoría (Docente / Externo)',
            'Institución / Entidad',
            'Actividad / Asignatura',
            'Descripción / Detalle',
            'Hora Inicio',
            'Hora Fin',
            'Módulos',
            'Horas Utilizadas',
            '% Ocupación Sala (Día)',
            'Tipo Reserva',
            'Estado',
            'Registrado Por',
            'Observaciones',
        ];
    }

    public function map($reserva): array
    {
        $fecha = 'N/A';
        $esSabado = false;
        if (!empty($reserva->fecha_reserva)) {
            $carbonFecha = is_string($reserva->fecha_reserva)
                ? Carbon::parse($reserva->fecha_reserva)
                : $reserva->fecha_reserva;
            $fecha = $carbonFecha->format('d/m/Y');
            $esSabado = $carbonFecha->isSaturday();
        }

        $usuario = $reserva->nombre_usuario ?? 'N/A';
        $run = $reserva->run_profesor ?: ($reserva->run_solicitante ?: 'N/A');

        $infoExtra = ReservationsTable::extraerInstitucionYActividad($reserva);
        $horas = ReservationsTable::calcularHorasReserva($reserva);
        $modulos = max(1, (int) ($reserva->modulos ?? 1));

        $horasDiaSala = $esSabado ? 5.0 : 15.0;
        $pctOcupacionDia = min(100, round(($horas / $horasDiaSala) * 100, 1)) . '%';

        $horaInicio = $reserva->hora ? substr($reserva->hora, 0, 5) : 'N/A';
        $horaFin = $reserva->hora_salida ? substr($reserva->hora_salida, 0, 5) : 'N/A';

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
            $reserva->id_espacio ?? 'N/A',
            $reserva->espacio->nombre_espacio ?? $reserva->id_espacio ?? 'N/A',
            $reserva->espacio->tipo_espacio ?? 'N/A',
            $piso,
            $usuario,
            $run,
            $infoExtra['categoria'],
            $infoExtra['institucion'],
            $infoExtra['actividad'],
            $infoExtra['descripcion'] ?: ($reserva->descripcion_actividad ?? ''),
            $horaInicio,
            $horaFin,
            $modulos,
            $horas,
            $pctOcupacionDia,
            ucfirst($reserva->tipo_reserva ?? 'Directa'),
            ucfirst($reserva->estado ?? 'Desconocido'),
            $reserva->creado_por ?? 'Sistema',
            $reserva->observaciones ?? '',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $lastRow = max(2, $sheet->getHighestRow());

        // Habilitar AutoFilter en todas las columnas para filtrar por Institución (Servicio de Salud, Municipalidad, etc.)
        $sheet->setAutoFilter('A1:U' . $lastRow);

        // Colorear Categoría (Columna I) y Estado (Columna S)
        for ($row = 2; $row <= $lastRow; $row++) {
            $categoria = strtolower((string) $sheet->getCell('I' . $row)->getValue());
            $catColor = match ($categoria) {
                'externo' => 'FEF3C7', // Ámbar claro para destacar externos
                'docente' => 'E0E7FF', // Índigo claro para docentes
                default   => 'F1F5F9',
            };
            $sheet->getStyle('I' . $row)->applyFromArray([
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => $catColor],
                ],
                'font' => ['bold' => true],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ]);

            $estado = strtolower((string) $sheet->getCell('S' . $row)->getValue());
            $fillColor = match ($estado) {
                'activa'     => 'D1FAE5',
                'finalizada' => 'E2E8F0',
                'programada' => 'FEF3C7',
                'cancelada'  => 'FEE2E2',
                default      => 'FFFFFF',
            };

            $sheet->getStyle('S' . $row)->applyFromArray([
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
            1 => [
                'font' => [
                    'bold' => true,
                    'color' => ['rgb' => 'FFFFFF'],
                    'size' => 11,
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '950101'], // Rojo institucional
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
            'A2:U' . $lastRow => [
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
            'C' => 14,  // Código Sala
            'D' => 22,  // Espacio
            'E' => 20,  // Tipo de Espacio
            'F' => 24,  // Ubicación / Piso
            'G' => 28,  // Usuario / Solicitante
            'H' => 14,  // RUN
            'I' => 22,  // Categoría (Docente / Externo)
            'J' => 28,  // Institución / Entidad
            'K' => 34,  // Actividad / Asignatura
            'L' => 36,  // Descripción / Detalle
            'M' => 12,  // Hora Inicio
            'N' => 12,  // Hora Fin
            'O' => 10,  // Módulos
            'P' => 15,  // Horas Utilizadas
            'Q' => 18,  // % Ocupación Sala (Día)
            'R' => 15,  // Tipo Reserva
            'S' => 14,  // Estado
            'T' => 24,  // Registrado Por
            'U' => 35,  // Observaciones
        ];
    }

    public function title(): string
    {
        return 'Detalle de Reservas';
    }
}

class ReservasResumenOcupacionSheet implements FromArray, WithStyles, WithColumnWidths, WithTitle
{
    protected array $metricas;
    protected ?string $tipoEspacio;

    public function __construct(array $metricas, ?string $tipoEspacio = null)
    {
        $this->metricas = $metricas;
        $this->tipoEspacio = $tipoEspacio;
    }

    public function array(): array
    {
        $m = $this->metricas;
        $rows = [];

        $rows[] = ['REPORTE EJECUTIVO DE OCUPACIÓN POR RESERVAS DE SALAS (EXTERNOS + DOCENTES)', '', '', '', '', '', ''];
        $rows[] = [
            'Período Evaluado: ' . ($m['fecha_inicio_calculo'] ?? '-') . ' al ' . ($m['fecha_fin_calculo'] ?? '-')
            . ' | Filtro Espacio: ' . ($this->tipoEspacio ?: 'Todos los espacios')
            . ' | Generado: ' . now()->format('d/m/Y H:i'),
            '', '', '', '', '', ''
        ];
        $rows[] = ['', '', '', '', '', '', ''];

        // Bloque 1: KPIs Globales (Don Boris)
        $rows[] = ['1. RESUMEN GENERAL DE OCUPACIÓN (EXTERNOS + DOCENTES)', '', '', '', '', '', ''];
        $rows[] = [
            'Segmento / Categoría',
            'Cantidad de Reservas',
            'Módulos Utilizados',
            'Horas Reservadas',
            '% Participación (del Total Reservado)',
            '% Ocupación (sobre Salas Reservadas)',
            '% Ocupación (sobre Total Campus)'
        ];

        $horasDispCatalogo = max(1, (float) ($m['horas_disponibles_catalogo'] ?? 1));
        $pctDocCatalogo = min(100, round((($m['horas_docentes'] ?? 0) / $horasDispCatalogo) * 100, 2));
        $pctExtCatalogo = min(100, round((($m['horas_externos'] ?? 0) / $horasDispCatalogo) * 100, 2));

        $rows[] = [
            'Reservas de Docentes',
            $m['reservas_docentes'] ?? 0,
            $m['modulos_docentes'] ?? 0,
            ($m['horas_docentes'] ?? 0) . ' hrs',
            ($m['pct_share_docentes'] ?? 0) . '%',
            ($m['pct_ocupacion_docentes_salas'] ?? 0) . '%',
            $pctDocCatalogo . '%',
        ];
        $rows[] = [
            'Reservas de Externos (Servicio de Salud, Municipalidad, etc.)',
            $m['reservas_externos'] ?? 0,
            $m['modulos_externos'] ?? 0,
            ($m['horas_externos'] ?? 0) . ' hrs',
            ($m['pct_share_externos'] ?? 0) . '%',
            ($m['pct_ocupacion_externos_salas'] ?? 0) . '%',
            $pctExtCatalogo . '%',
        ];
        $rows[] = [
            'TOTAL CONSOLIDADO (EXTERNOS + DOCENTES)',
            $m['reservas_activas_validas'] ?? 0,
            $m['modulos_totales'] ?? 0,
            ($m['horas_totales'] ?? 0) . ' hrs',
            '100%',
            ($m['pct_ocupacion_salas_usadas'] ?? 0) . '%',
            ($m['pct_ocupacion_catalogo'] ?? 0) . '%',
        ];
        $rows[] = [
            'Parámetros de Capacidad Base',
            'Días hábiles: ' . ($m['dias_habiles'] ?? 1),
            'Salas usadas: ' . ($m['espacios_usados_count'] ?? 0),
            'Hrs disp. salas usadas: ' . ($m['horas_disponibles_salas_usadas'] ?? 0) . ' hrs',
            'Salas en campus: ' . ($m['espacios_catalogo_count'] ?? 0),
            'Hrs disp. campus: ' . ($m['horas_disponibles_catalogo'] ?? 0) . ' hrs',
            'Jornada: 15h L-V / 5h Sáb',
        ];

        $rows[] = ['', '', '', '', '', '', ''];

        // Bloque 2: Desglose por Institución / Entidad Solicitante
        $rows[] = ['2. DESGLOSE POR INSTITUCIÓN / ENTIDAD SOLICITANTE (FILTRO SERVICIO DE SALUD, MUNICIPALIDAD, DOCENTES, ETC.)', '', '', '', '', '', ''];
        $rows[] = [
            'Institución / Entidad / Agrupación',
            'Categoría',
            'Total Reservas',
            'Módulos Utilizados',
            'Horas Utilizadas',
            '% del Total de Horas Reservadas',
            '% Ocupación sobre Salas Usadas'
        ];

        if (!empty($m['por_institucion'])) {
            foreach ($m['por_institucion'] as $inst) {
                $rows[] = [
                    $inst['institucion'],
                    $inst['categoria'],
                    $inst['total_reservas'],
                    $inst['modulos'],
                    $inst['horas'] . ' hrs',
                    $inst['pct_del_total_reservas'] . '%',
                    $inst['pct_ocupacion_salas'] . '%',
                ];
            }
        } else {
            $rows[] = ['Sin registros en el período seleccionado', '-', 0, 0, '0 hrs', '0%', '0%'];
        }

        $rows[] = ['', '', '', '', '', '', ''];

        // Bloque 3: Desglose por Tipo de Espacio
        $rows[] = ['3. PORCENTAJE DE OCUPACIÓN POR TIPO DE ESPACIO', '', '', '', '', '', ''];
        $rows[] = [
            'Tipo de Espacio',
            'Salas Reservadas',
            'Total Reservas',
            'Reservas Docentes / Externos',
            'Horas Reservadas (Doc. / Ext.)',
            'Horas Totales vs Disponibles',
            '% Ocupación del Tipo de Espacio'
        ];

        if (!empty($m['por_tipo_espacio'])) {
            foreach ($m['por_tipo_espacio'] as $esp) {
                $rows[] = [
                    $esp['tipo_espacio'],
                    $esp['num_salas_usadas'],
                    $esp['total_reservas'],
                    'Doc: ' . $esp['reservas_docentes'] . ' | Ext: ' . $esp['reservas_externos'],
                    'Doc: ' . $esp['horas_docentes'] . 'h | Ext: ' . $esp['horas_externos'] . 'h',
                    $esp['horas_totales'] . 'h / ' . $esp['horas_disponibles'] . 'h',
                    $esp['pct_ocupacion'] . '%',
                ];
            }
        } else {
            $rows[] = ['Sin registros en el período seleccionado', 0, 0, '-', '-', '-', '0%'];
        }

        return $rows;
    }

    public function styles(Worksheet $sheet)
    {
        $highestRow = $sheet->getHighestRow();

        $sheet->mergeCells('A1:G1');
        $sheet->mergeCells('A2:G2');

        $sheet->getStyle('A1')->applyFromArray([
            'font' => ['bold' => true, 'size' => 13, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '950101']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ]);

        $sheet->getStyle('A2')->applyFromArray([
            'font' => ['italic' => true, 'size' => 10, 'color' => ['rgb' => '334155']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F1F5F9']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);

        for ($row = 4; $row <= $highestRow; $row++) {
            $valA = (string) $sheet->getCell('A' . $row)->getValue();

            if (str_starts_with($valA, '1. ') || str_starts_with($valA, '2. ') || str_starts_with($valA, '3. ')) {
                $sheet->mergeCells("A{$row}:G{$row}");
                $sheet->getStyle("A{$row}:G{$row}")->applyFromArray([
                    'font' => ['bold' => true, 'size' => 11, 'color' => ['rgb' => '1E293B']],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'E2E8F0']],
                ]);
            } elseif (in_array($valA, ['Segmento / Categoría', 'Institución / Entidad / Agrupación', 'Tipo de Espacio'])) {
                $sheet->getStyle("A{$row}:G{$row}")->applyFromArray([
                    'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1E3A8A']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                ]);
            } elseif (str_starts_with($valA, 'TOTAL CONSOLIDADO')) {
                $sheet->getStyle("A{$row}:G{$row}")->applyFromArray([
                    'font' => ['bold' => true, 'color' => ['rgb' => '065F46']],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'D1FAE5']],
                ]);
            } elseif ($valA !== '') {
                $sheet->getStyle("A{$row}:G{$row}")->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['rgb' => 'CBD5E1'],
                        ],
                    ],
                ]);
            }
        }

        return [];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 44,
            'B' => 22,
            'C' => 20,
            'D' => 26,
            'E' => 28,
            'F' => 28,
            'G' => 28,
        ];
    }

    public function title(): string
    {
        return 'Resumen Ocupación';
    }
}
