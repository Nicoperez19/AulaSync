<?php

namespace App\Exports;

use App\Models\Planificacion_Asignatura;
use App\Models\ClaseNoRealizada;
use App\Models\Reserva;
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
use Illuminate\Support\Collection;

class TodasClasesExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithColumnWidths, WithTitle
{
    protected $fechaInicio;
    protected $fechaFin;
    protected $periodo;
    protected $clasesNoRealizadasCache = [];
    protected $reservasCache = [];

    public function __construct($fechaInicio = null, $fechaFin = null, $periodo = null)
    {
        $this->fechaInicio = $fechaInicio ? Carbon::parse($fechaInicio) : null;
        $this->fechaFin = $fechaFin ? Carbon::parse($fechaFin) : null;
        $this->periodo = $periodo;
    }

    public function collection()
    {
        // Aumentar límite de memoria temporalmente
        ini_set('memory_limit', '512M');
        
        $clasesData = new Collection();

        // Si no hay rango de fechas, usar el período actual
        $fechaInicio = $this->fechaInicio ?? Carbon::now()->startOfMonth();
        $fechaFin = $this->fechaFin ?? Carbon::now()->endOfMonth();

        // Días de la semana para mapeo
        $dias = ['lunes', 'martes', 'miercoles', 'jueves', 'viernes', 'sabado'];

        // Generar fechas en el rango (optimizado)
        $fechas = [];
        $currentDate = $fechaInicio->copy();
        while ($currentDate <= $fechaFin) {
            if ($currentDate->dayOfWeek >= 1 && $currentDate->dayOfWeek <= 6) {
                $fechas[] = $currentDate->format('Y-m-d');
            }
            $currentDate->addDay();
        }

        // Pre-cargar clases no realizadas en caché (solo IDs necesarios)
        $this->clasesNoRealizadasCache = ClaseNoRealizada::selectRaw('
                fecha_clase, 
                id_espacio, 
                id_modulo, 
                run_profesor, 
                estado, 
                motivo, 
                observaciones
            ')
            ->whereBetween('fecha_clase', [$fechaInicio, $fechaFin])
            ->get()
            ->mapWithKeys(function($clase) {
                $key = Carbon::parse($clase->fecha_clase)->format('Y-m-d') . '_' . 
                       $clase->id_espacio . '_' . 
                       $clase->id_modulo . '_' . 
                       $clase->run_profesor;
                return [$key => $clase];
            })
            ->all();

        // Pre-cargar reservas en caché (solo campos necesarios)
        $this->reservasCache = Reserva::selectRaw('
                fecha_reserva, 
                id_espacio, 
                run_profesor, 
                hora, 
                hora_salida
            ')
            ->whereBetween('fecha_reserva', [$fechaInicio, $fechaFin])
            ->whereNotNull('run_profesor')
            ->whereNotNull('hora')
            ->get()
            ->mapWithKeys(function($reserva) {
                $key = Carbon::parse($reserva->fecha_reserva)->format('Y-m-d') . '_' . 
                       $reserva->id_espacio . '_' . 
                       $reserva->run_profesor;
                return [$key => $reserva];
            })
            ->all();

        // Procesar planificaciones en chunks para optimizar memoria
        $query = Planificacion_Asignatura::select([
                'id',
                'id_asignatura', 
                'id_espacio', 
                'id_modulo', 
                'id_horario'
            ])
            ->with([
                'asignatura:id_asignatura,nombre_asignatura,codigo_asignatura',
                'modulo:id_modulo,dia,hora_inicio,hora_termino',
                'horario' => function($query) {
                    $query->select('id_horario', 'run_profesor', 'periodo')
                        ->with('profesor:run_profesor,name');
                }
            ])
            ->whereHas('modulo')
            ->whereHas('horario.profesor');

        // Filtrar por periodo si se especifica
        if ($this->periodo) {
            $query->whereHas('horario', function($q) {
                $q->where('periodo', $this->periodo);
            });
        }

        // Procesar en chunks de 100 planificaciones a la vez
        $query->chunk(100, function($planificaciones) use (&$clasesData, $fechas, $dias, $fechaInicio) {
            foreach ($planificaciones as $planificacion) {
                if (!$planificacion->modulo || !$planificacion->horario || !$planificacion->horario->profesor) {
                    continue;
                }

                $diaModulo = strtolower($planificacion->modulo->dia);
                
                // Para cada fecha en el rango
                foreach ($fechas as $fechaStr) {
                    $fecha = Carbon::parse($fechaStr);
                    $diaFecha = $dias[$fecha->dayOfWeek - 1];
                    
                    // Solo procesar si el día coincide con el módulo
                    if ($diaFecha === $diaModulo) {
                        $runProfesor = $planificacion->horario->profesor->run_profesor;
                        
                        // Crear claves de búsqueda
                        $claveClase = $fechaStr . '_' . 
                                      $planificacion->id_espacio . '_' . 
                                      $planificacion->id_modulo . '_' . 
                                      $runProfesor;
                        
                        $claveReserva = $fechaStr . '_' . 
                                        $planificacion->id_espacio . '_' . 
                                        $runProfesor;
                        
                        // Determinar el estado de la clase
                        $estado = 'Planificada';
                        $horaEntrada = null;
                        $horaSalida = null;
                        $motivo = null;
                        $observaciones = null;
                        
                        // Verificar si está en clases no realizadas (usando caché)
                        if (isset($this->clasesNoRealizadasCache[$claveClase])) {
                            $claseNoRealizada = $this->clasesNoRealizadasCache[$claveClase];
                            $estado = match($claseNoRealizada->estado) {
                                'no_realizada' => 'No Realizada',
                                'justificado' => 'Justificada',
                                'recuperada' => 'Recuperada',
                                default => 'No Realizada',
                            };
                            $motivo = $claseNoRealizada->motivo;
                            $observaciones = $claseNoRealizada->observaciones;
                        }
                        // Verificar si hay acceso registrado (usando caché)
                        elseif (isset($this->reservasCache[$claveReserva])) {
                            $reserva = $this->reservasCache[$claveReserva];
                            
                            // Verificar si el acceso corresponde al horario del módulo
                            $horaInicioModulo = Carbon::parse($planificacion->modulo->hora_inicio);
                            $horaFinModulo = Carbon::parse($planificacion->modulo->hora_termino);
                            $horaAcceso = Carbon::parse($reserva->hora);
                            
                            // Considerar un margen de 30 minutos antes del inicio y durante toda la clase
                            $margenInicio = $horaInicioModulo->copy()->subMinutes(30);
                            
                            if ($horaAcceso >= $margenInicio && $horaAcceso <= $horaFinModulo) {
                                $estado = 'Realizada';
                                $horaEntrada = $reserva->hora;
                                $horaSalida = $reserva->hora_salida;
                                
                                // Calcular si hubo atraso
                                $diferencia = $horaAcceso->diffInMinutes($horaInicioModulo, false);
                                if ($diferencia > 15) {
                                    $observaciones = "Atraso de {$diferencia} minutos";
                                }
                            }
                        }
                        
                        // Agregar la clase al resultado
                        $clasesData->push([
                            'fecha' => $fechaStr,
                            'dia' => ucfirst($diaFecha),
                            'periodo' => $this->periodo ?? $planificacion->horario->periodo ?? 'N/A',
                            'profesor' => $planificacion->horario->profesor->name,
                            'run_profesor' => $runProfesor,
                            'asignatura' => $planificacion->asignatura->nombre_asignatura ?? 'N/A',
                            'codigo_asignatura' => $planificacion->asignatura->codigo_asignatura ?? 'N/A',
                            'espacio' => $planificacion->id_espacio,
                            'modulo' => preg_replace('/^[A-Z]{2}\./', '', $planificacion->id_modulo),
                            'hora_inicio' => $planificacion->modulo->hora_inicio,
                            'hora_fin' => $planificacion->modulo->hora_termino,
                            'estado' => $estado,
                            'hora_entrada' => $horaEntrada,
                            'hora_salida' => $horaSalida,
                            'motivo' => $motivo,
                            'observaciones' => $observaciones,
                        ]);
                    }
                }
            }
            
            // Liberar memoria después de cada chunk
            unset($planificaciones);
            gc_collect_cycles();
        });

        // Limpiar cachés
        $this->clasesNoRealizadasCache = [];
        $this->reservasCache = [];

        // Ordenar por fecha, espacio y módulo
        return $clasesData->sortBy([
            ['fecha', 'asc'],
            ['espacio', 'asc'],
            ['modulo', 'asc'],
        ]);
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
                'Realizada' => 'D1FAE5', // Verde claro
                'No Realizada' => 'FEE2E2', // Rojo claro
                'Justificada' => 'FEF3C7', // Amarillo claro
                'Recuperada' => 'DBEAFE', // Azul claro
                default => 'FFFFFF', // Blanco
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
            $titulo .= ' ' . $this->fechaInicio->format('d-m-Y') . ' a ' . $this->fechaFin->format('d-m-Y');
        } elseif ($this->periodo) {
            $titulo .= ' ' . $this->periodo;
        }
        
        return substr($titulo, 0, 31); // Excel limita a 31 caracteres
    }
}
