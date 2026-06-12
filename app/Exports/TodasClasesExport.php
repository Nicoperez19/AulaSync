<?php

namespace App\Exports;

use App\Models\Planificacion_Asignatura;
use App\Models\ClaseNoRealizada;
use App\Models\Reserva;
use App\Models\DiaFeriado;
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
use App\Helpers\ModulosHelper;

class TodasClasesExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithColumnWidths, WithTitle
{
    protected $fechaInicio;
    protected $fechaFin;
    protected $periodo;
    protected $search;
    protected $estado;
    protected $clasesNoRealizadasCache = [];
    protected $reservasCache = [];

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
        // Aumentar límite de memoria temporalmente
        ini_set('memory_limit', '512M');
        
        $clasesData = new Collection();

        // Si no hay rango de fechas, usar el período actual
        $fechaInicio = $this->fechaInicio ?? Carbon::now()->startOfMonth();
        $fechaFin = $this->fechaFin ?? Carbon::now()->endOfMonth();

        // Días de la semana para mapeo (0=Domingo, 1=Lunes, etc. según Carbon)
        $dias = ['domingo', 'lunes', 'martes', 'miércoles', 'jueves', 'viernes', 'sábado'];

        // Pre-cargar todos los feriados del rango en un mapa de fecha => nombre
        $feriadosEnRango = DiaFeriado::activos()
            ->enRango($fechaInicio->toDateString(), $fechaFin->toDateString())
            ->get();

        // Construir un set de fechas que son feriado (iterando día a día)
        $fechasFeriado = []; // ['Y-m-d' => 'Nombre del feriado']
        foreach ($feriadosEnRango as $feriado) {
            $cursor = Carbon::parse($feriado->fecha_inicio)->startOfDay();
            $fin    = Carbon::parse($feriado->fecha_fin)->startOfDay();
            while ($cursor <= $fin) {
                $fechasFeriado[$cursor->format('Y-m-d')] = $feriado->nombre;
                $cursor->addDay();
            }
        }

        // Generar fechas en el rango (optimizado) — excluye domingos (se manejan igual)
        $fechas = [];
        $currentDate = $fechaInicio->copy();
        while ($currentDate <= $fechaFin) {
            if ($currentDate->dayOfWeek >= 1 && $currentDate->dayOfWeek <= 6) {
                $fechas[] = $currentDate->format('Y-m-d');
            }
            $currentDate->addDay();
        }

        // Pre-cargar clases no realizadas en caché (solo IDs necesarios)
        $clasesNoRealizadasQuery = ClaseNoRealizada::selectRaw('
                fecha_clase, 
                id_espacio, 
                id_modulo, 
                run_profesor, 
                estado, 
                motivo, 
                observaciones
            ')
            ->whereBetween('fecha_clase', [$fechaInicio, $fechaFin]);
        
        // Aplicar filtro de búsqueda
        if ($this->search) {
            $searchTerm = '%' . $this->search . '%';
            $clasesNoRealizadasQuery->where(function($q) use ($searchTerm) {
                $q->whereHas('profesor', function($pq) use ($searchTerm) {
                    $pq->where('name', 'like', $searchTerm);
                })
                ->orWhereHas('asignatura', function($aq) use ($searchTerm) {
                    $aq->where('nombre_asignatura', 'like', $searchTerm)
                       ->orWhere('codigo_asignatura', 'like', $searchTerm);
                })
                ->orWhere('id_espacio', 'like', $searchTerm)
                ->orWhere('run_profesor', 'like', $searchTerm);
            });
        }
        
        // Aplicar filtro de estado
        if ($this->estado) {
            $clasesNoRealizadasQuery->where('estado', $this->estado);
        }

        $this->clasesNoRealizadasCache = $clasesNoRealizadasQuery->get()
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
                id_asignatura,
                hora, 
                hora_salida
            ')
            ->whereBetween('fecha_reserva', [$fechaInicio, $fechaFin])
            ->whereNotNull('run_profesor')
            ->whereNotNull('hora')
            ->get()
            ->groupBy(function($reserva) {
                return Carbon::parse($reserva->fecha_reserva)->format('Y-m-d') . '_' . 
                       $reserva->id_espacio . '_' . 
                       $reserva->run_profesor;
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

        // Aplicar filtro de búsqueda en planificaciones
        if ($this->search) {
            $searchTerm = '%' . $this->search . '%';
            $query->where(function($q) use ($searchTerm) {
                $q->whereHas('asignatura', function($aq) use ($searchTerm) {
                    $aq->where('nombre_asignatura', 'like', $searchTerm)
                       ->orWhere('codigo_asignatura', 'like', $searchTerm);
                })
                ->orWhereHas('horario.profesor', function($pq) use ($searchTerm) {
                    $pq->where('name', 'like', $searchTerm)
                       ->orWhere('run_profesor', 'like', $searchTerm);
                })
                ->orWhere('id_espacio', 'like', $searchTerm);
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
                    $diaFecha = $dias[$fecha->dayOfWeek];
                    
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

                        // ── Verificar primero si es día feriado ─────────────────────
                        if (isset($fechasFeriado[$fechaStr])) {
                            $estado = 'Feriado/Justificado';
                            $motivo = $fechasFeriado[$fechaStr];
                            $observaciones = 'Clase no realizada por día feriado o período sin actividades';

                            // Aplicar filtro de estado si viene especificado
                            if ($this->estado && $estado !== $this->transformEstado($this->estado)) {
                                continue;
                            }

                            $clasesData->push([
                                'fecha'             => $fechaStr,
                                'dia'               => ucfirst($diaFecha),
                                'periodo'           => $this->periodo ?? $planificacion->horario->periodo ?? 'N/A',
                                'profesor'          => $planificacion->horario->profesor->name,
                                'run_profesor'      => $runProfesor,
                                'asignatura'        => $planificacion->asignatura->nombre_asignatura ?? 'N/A',
                                'codigo_asignatura' => $planificacion->asignatura->codigo_asignatura ?? 'N/A',
                                'id_asignatura'     => $planificacion->id_asignatura,
                                'espacio'           => $planificacion->id_espacio,
                                'modulo'            => preg_replace('/^[A-Z]{2}\./', '', $planificacion->id_modulo),
                                'hora_inicio'       => $planificacion->modulo->hora_inicio,
                                'hora_fin'          => $planificacion->modulo->hora_termino,
                                'estado'            => $estado,
                                'hora_entrada'      => null,
                                'hora_salida'       => null,
                                'motivo'            => $motivo,
                                'observaciones'     => $observaciones,
                            ]);
                            continue; // No seguir evaluando lógica de reservas
                        }
                        // ─────────────────────────────────────────────────────────────
                        
                        // Fecha y hora actual para comparar si la clase ya pasó
                        $ahora = Carbon::now();
                        $fechaClase = Carbon::parse($fechaStr);
                        $horaFinModulo = Carbon::parse($planificacion->modulo->hora_termino);
                        $horaInicioModulo = Carbon::parse($planificacion->modulo->hora_inicio);
                        
                        // Margen de ingreso diferenciado (según ModulosHelper — fuente de verdad):
                        // - Módulo 1 (08:10): 40 min de tolerancia (puede ingresar desde 07:30)
                        // - Todos los demás módulos: 10 min de tolerancia
                        $minutosMargenIngreso = ModulosHelper::getMargenIngresoMinutos($planificacion->id_modulo);
                        
                        // Crear datetime completo de cuando termina la clase
                        $fechaHoraFinClase = $fechaClase->copy()->setTimeFromTimeString($horaFinModulo->format('H:i:s'));
                        
                        // Verificar si está en clases no realizadas (usando caché)
                        if (isset($this->clasesNoRealizadasCache[$claveClase])) {
                            $claseNoRealizada = $this->clasesNoRealizadasCache[$claveClase];
                            $estado = match($claseNoRealizada->estado) {
                                'no_realizada' => 'No Registrada',
                                'justificado'  => 'Justificada',
                                'recuperada'   => 'Recuperada',
                                'pendiente'    => 'Pendiente de Recuperación',
                                default        => 'No Registrada',
                            };
                            $motivo = $claseNoRealizada->motivo;
                            $observaciones = $claseNoRealizada->observaciones;
                        }
                        // Verificar si hay acceso registrado (usando caché de reservas del día)
                        elseif (isset($this->reservasCache[$claveReserva])) {
                            $reservasDelDia = $this->reservasCache[$claveReserva];
                            $reserva = null;
                            
                            // 1. Intentar buscar por coincidencia exacta de asignatura
                            foreach ($reservasDelDia as $r) {
                                if ($r->id_asignatura == $planificacion->id_asignatura) {
                                    $reserva = $r;
                                    break;
                                }
                            }
                            
                            // 2. Si no coincide por asignatura, buscar la que solape temporalmente con el módulo
                            if (!$reserva) {
                                foreach ($reservasDelDia as $r) {
                                    $horaAcceso = Carbon::parse($r->hora);
                                    $margenInicio = $horaInicioModulo->copy()->subMinutes($minutosMargenIngreso);
                                    if ($horaAcceso >= $margenInicio && $horaAcceso <= $horaFinModulo) {
                                        $reserva = $r;
                                        break;
                                    }
                                }
                            }
                            
                            if ($reserva) {
                                $horaInicioReserva = Carbon::parse($reserva->hora);
                                $horaFinReserva = $reserva->hora_salida ? Carbon::parse($reserva->hora_salida) : null;
                                
                                // Margen de ingreso: 40 min para 08:10, 10 min para el resto
                                $margenInicio = $horaInicioModulo->copy()->subMinutes($minutosMargenIngreso);
                                
                                // Caso 1: El ingreso ocurrió para este módulo directamente
                                $ingresoDirecto = ($horaInicioReserva >= $margenInicio && $horaInicioReserva <= $horaFinModulo);
                                
                                // Caso 2: El módulo está completamente dentro del rango de una sesión que empezó antes
                                $ingresoPrevio = ($horaInicioReserva < $margenInicio && 
                                                 (!$horaFinReserva || $horaFinReserva >= $horaInicioModulo));
                                
                                if ($ingresoDirecto || $ingresoPrevio) {
                                    $estado = 'Realizada';
                                    $horaEntrada = $reserva->hora;
                                    $horaSalida = $reserva->hora_salida;
                                    
                                    // Calcular si hubo atraso (solo aplica si es el ingreso directo del primer módulo)
                                    if ($ingresoDirecto) {
                                        $diferencia = $horaInicioReserva->diffInMinutes($horaInicioModulo, false);
                                        if ($diferencia > 15) {
                                            $observaciones = "Atraso de {$diferencia} minutos";
                                        }
                                    }
                                }
                                // Si la clase ya pasó y no hubo ingreso que la cubriera
                                elseif ($fechaHoraFinClase < $ahora) {
                                    $estado = 'No Registrada';
                                    $motivo = 'Sin registro de acceso';
                                    $observaciones = 'No se detectó ingreso durante el horario de clase';
                                }
                            }
                            // Si hay reservas del día pero ninguna coincide ni por horario ni por asignatura
                            elseif ($fechaHoraFinClase < $ahora) {
                                $estado = 'No Registrada';
                                $motivo = 'Sin registro de acceso';
                                $observaciones = 'No se detectó ingreso durante el horario de clase';
                            }
                        }
                        // Si la clase ya pasó y no hay registro de acceso ni está marcada como no realizada
                        elseif ($fechaHoraFinClase < $ahora) {
                            $estado = 'No Registrada';
                            $motivo = 'Sin registro de acceso';
                            $observaciones = 'No se detectó ingreso durante el horario de clase';
                        }
                        // Si la clase aún no ha pasado, mantener como Planificada
                        
                        // Aplicar filtro de estado si viene especificado
                        if ($this->estado && $estado !== $this->transformEstado($this->estado)) {
                            continue;
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
                            'id_asignatura' => $planificacion->id_asignatura,
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

        // Eliminar duplicados exactos antes de procesar agrupaciones
        $clasesData = $clasesData->unique(function($item) {
            return $item['fecha'] . '_' . $item['espacio'] . '_' . $item['modulo'] . '_' . $item['run_profesor'] . '_' . ($item['id_asignatura'] ?? '');
        });

        // Agrupar clases para post-procesar entrada/salida de módulos consecutivos
        $clasesAgrupadas = $clasesData->groupBy(function($item) {
            return $item['fecha'] . '_' . $item['espacio'] . '_' . $item['run_profesor'] . '_' . ($item['id_asignatura'] ?? '');
        });

        $clasesProcesadas = new Collection();

        foreach ($clasesAgrupadas as $grupoKey => $items) {
            // Ordenar los módulos del grupo cronológicamente por hora de inicio
            $itemsOrdenados = $items->sortBy('hora_inicio')->values();

            // Dividir los módulos ordenados en bloques de módulos consecutivos
            $bloquesConsecutivos = [];
            $bloqueActual = [];
            $ultimoModuloNum = null;

            foreach ($itemsOrdenados as $item) {
                $moduloNum = (int)$item['modulo'];
                if (empty($bloqueActual)) {
                    $bloqueActual[] = $item;
                } else {
                    if ($moduloNum === $ultimoModuloNum + 1) {
                        $bloqueActual[] = $item;
                    } else {
                        $bloquesConsecutivos[] = $bloqueActual;
                        $bloqueActual = [$item];
                    }
                }
                $ultimoModuloNum = $moduloNum;
            }
            if (!empty($bloqueActual)) {
                $bloquesConsecutivos[] = $bloqueActual;
            }

            // Procesar cada bloque consecutivo de forma independiente
            foreach ($bloquesConsecutivos as $bloque) {
                $bloqueItems = collect($bloque);

                // ── FASE 1: Obtener la hora de salida del bloque ──────────────────────────────────
                $horaSalidaBloque = null;
                foreach ($bloqueItems as $item) {
                    if ($item['estado'] === 'Realizada' && !empty($item['hora_salida'])) {
                        $horaSalidaBloque = $item['hora_salida'];
                        break;
                    }
                }

                // ── FASE 2: Verificar retiro anticipado con fórmula 10*(n-1) ─────────────────────
                if ($horaSalidaBloque !== null) {
                    $horaSalidaReal = Carbon::parse($horaSalidaBloque);
                    $posicionN = 0;

                    $itemsActualizados = collect();
                    foreach ($bloqueItems as $item) {
                        $posicionN++; // n comienza en 1

                        if ($item['estado'] === 'Realizada' && !empty($item['hora_entrada'])) {
                            $toleranciaMinutos = 10 * ($posicionN - 1); // fórmula: 10*(n-1)

                            $horarioCanon    = ModulosHelper::getHorarioModulo($item['dia'], (int) $item['modulo']);
                            $horaFinCanonica = $horarioCanon ? $horarioCanon['fin'] : $item['hora_fin'];
                            $horaMinimaSalida = Carbon::parse($horaFinCanonica)->subMinutes($toleranciaMinutos);

                            if ($horaSalidaReal->lt($horaMinimaSalida)) {
                                $minutosAntes = $horaSalidaReal->diffInMinutes($horaMinimaSalida);
                                if ($minutosAntes >= 5) {
                                    $item['estado']        = 'No Registrada';
                                    $item['motivo']        = 'Retiro anticipado del docente';
                                    $item['observaciones'] = "El docente se retiró {$minutosAntes} min antes del mínimo requerido para el módulo {$posicionN}";
                                    $item['hora_entrada']  = null;
                                    $item['hora_salida']   = null;
                                }
                            }
                        }
                        $itemsActualizados->push($item);
                    }
                    $bloqueItems = $itemsActualizados;
                }

                // ── FASE 3: Ajustar display de hora_entrada / hora_salida ─────────────────────────
                $realizadas = $bloqueItems->filter(function ($item) {
                    return $item['estado'] === 'Realizada' && !empty($item['hora_entrada']) && $item['hora_entrada'] !== 'N/A';
                });
                $totalRealizadas = $realizadas->count();

                if ($totalRealizadas > 1) {
                    $firstIndex = $realizadas->keys()->first();
                    $lastIndex  = $realizadas->keys()->last();

                    foreach ($bloqueItems as $index => $item) {
                        if ($index === $firstIndex) {
                            $item['hora_salida'] = null;
                        } elseif ($index === $lastIndex) {
                            $item['hora_entrada'] = null;
                        } elseif ($realizadas->has($index)) {
                            $item['hora_entrada'] = null;
                            $item['hora_salida']  = null;
                        }
                        $clasesProcesadas->push($item);
                    }
                } else {
                    foreach ($bloqueItems as $item) {
                        $clasesProcesadas->push($item);
                    }
                }
            }
        }

        $clasesData = $clasesProcesadas;

        // Ordenar por fecha, espacio y módulo
        return $clasesData->sortBy([
            ['fecha', 'asc'],
            ['espacio', 'asc'],
            ['modulo', 'asc'],
        ]);
    }

    /**
     * Transformar estado del filtro al formato de visualización
     */
    private function transformEstado($estado)
    {
        return match($estado) {
            'no_realizada'      => 'No Registrada',
            'justificado'       => 'Justificada',
            'recuperada'        => 'Recuperada',
            'pendiente'         => 'Pendiente de Recuperación',
            'feriado'           => 'Feriado/Justificado',
            default             => $estado,
        };
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
            $titulo .= ' ' . $this->fechaInicio->format('d-m-Y') . ' a ' . $this->fechaFin->format('d-m-Y');
        } elseif ($this->periodo) {
            $titulo .= ' ' . $this->periodo;
        }
        
        return substr($titulo, 0, 31); // Excel limita a 31 caracteres
    }
}
