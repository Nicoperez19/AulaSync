<?php

namespace App\Services;

use App\Models\Planificacion_Asignatura;
use App\Models\ClaseNoRealizada;
use App\Models\Reserva;
use App\Models\DiaFeriado;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use App\Helpers\ModulosHelper;

class TodasClasesService
{
    protected $clasesNoRealizadasCache = [];
    protected $reservasCache = [];

    /**
     * Obtener todas las clases (planificadas, realizadas y no realizadas)
     */
    public function obtenerTodasLasClases($fechaInicio = null, $fechaFin = null, $periodo = null, $search = null, $estado = null)
    {
        $fechaInicio = $fechaInicio ? Carbon::parse($fechaInicio) : null;
        $fechaFin = $fechaFin ? Carbon::parse($fechaFin) : null;

        // Aumentar límite de memoria temporalmente para procesos grandes
        ini_set('memory_limit', '512M');
        
        $clasesData = new Collection();

        if (!$fechaInicio || !$fechaFin) {
            if ($periodo) {
                $partes = explode('-', $periodo);
                if (count($partes) === 2) {
                    $anio = (int)$partes[0];
                    $semestre = (int)$partes[1];
                    
                    $periodoModel = \App\Models\PeriodoAcademico::where('anio', $anio)
                        ->where('semestre', $semestre)
                        ->first();
                        
                    if ($periodoModel) {
                        if (!$fechaInicio) {
                            $fechaInicio = Carbon::parse($periodoModel->fecha_inicio);
                        }
                        if (!$fechaFin) {
                            $fechaFin = Carbon::parse($periodoModel->fecha_fin);
                        }
                    }
                }
            }
            
            $fechaInicio = $fechaInicio ?? Carbon::now()->startOfMonth();
            $fechaFin = $fechaFin ?? Carbon::now()->endOfMonth();
        }

        $hoy = Carbon::today();
        if ($fechaFin->gt($hoy)) {
            $fechaFin = $hoy->copy();
        }

        $dias = ['domingo', 'lunes', 'martes', 'miércoles', 'jueves', 'viernes', 'sábado'];

        $feriadosEnRango = DiaFeriado::activos()
            ->enRango($fechaInicio->toDateString(), $fechaFin->toDateString())
            ->get();

        $fechasFeriado = [];
        foreach ($feriadosEnRango as $feriado) {
            $cursor = Carbon::parse($feriado->fecha_inicio)->startOfDay();
            $fin    = Carbon::parse($feriado->fecha_fin)->startOfDay();
            while ($cursor <= $fin) {
                $fechasFeriado[$cursor->format('Y-m-d')] = $feriado->nombre;
                $cursor->addDay();
            }
        }

        $fechas = [];
        $currentDate = $fechaInicio->copy();
        while ($currentDate <= $fechaFin) {
            if ($currentDate->dayOfWeek >= 1 && $currentDate->dayOfWeek <= 6) {
                $fechas[] = $currentDate->format('Y-m-d');
            }
            $currentDate->addDay();
        }

        $clasesNoRealizadasQuery = ClaseNoRealizada::selectRaw('
                id,
                fecha_clase, 
                id_espacio, 
                id_modulo, 
                run_profesor, 
                estado, 
                motivo, 
                observaciones
            ')
            ->whereBetween('fecha_clase', [$fechaInicio, $fechaFin]);
        
        if ($search) {
            $searchTerm = '%' . $search . '%';
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
        
        $this->clasesNoRealizadasCache = [];
        foreach ($clasesNoRealizadasQuery->get() as $clase) {
            $fecha = Carbon::parse($clase->fecha_clase)->format('Y-m-d');
            $runNorm = $this->normalizeRun($clase->run_profesor);
            $modulos = explode(',', $clase->id_modulo);
            foreach ($modulos as $mod) {
                $modTrim = trim($mod);
                $key = "{$fecha}_{$clase->id_espacio}_{$modTrim}_{$runNorm}";
                $this->clasesNoRealizadasCache[$key] = $clase;
            }
        }

        $this->reservasCache = Reserva::selectRaw('
                fecha_reserva, 
                id_espacio, 
                run_profesor, 
                run_solicitante,
                id_asignatura,
                hora, 
                hora_salida
            ')
            ->whereBetween('fecha_reserva', [$fechaInicio, $fechaFin])
            ->where(function($q) {
                $q->whereNotNull('run_profesor')
                  ->orWhereNotNull('run_solicitante');
            })
            ->whereNotNull('hora')
            ->get()
            ->groupBy(function($reserva) {
                return Carbon::parse($reserva->fecha_reserva)->format('Y-m-d') . '_' . 
                       $reserva->id_espacio;
            })
            ->all();

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

        if ($periodo) {
            $query->whereHas('horario', function($q) use ($periodo) {
                $q->where('periodo', $periodo);
            });
        }

        if ($search) {
            $searchTerm = '%' . $search . '%';
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

        $query->chunk(100, function($planificaciones) use (&$clasesData, $fechas, $dias, $fechasFeriado, $periodo, $estado) {
            foreach ($planificaciones as $planificacion) {
                if (!$planificacion->modulo || !$planificacion->horario || !$planificacion->horario->profesor) {
                    continue;
                }

                $diaModulo = \App\Helpers\ModulosHelper::normalizarDia($planificacion->modulo->dia);
                
                foreach ($fechas as $fechaStr) {
                    $fecha = Carbon::parse($fechaStr);
                    $diaFecha = \App\Helpers\ModulosHelper::normalizarDia($dias[$fecha->dayOfWeek]);
                    
                    if ($diaFecha === $diaModulo) {
                        $runProfesor = $this->normalizeRun($planificacion->horario->profesor->run_profesor);
                        
                        $claveClase = $fechaStr . '_' . 
                                      $planificacion->id_espacio . '_' . 
                                      $planificacion->id_modulo . '_' . 
                                      $runProfesor;
                        
                        $claveReserva = $fechaStr . '_' . $planificacion->id_espacio;
                        
                        $estadoStr = 'Planificada';
                        $horaEntrada = null;
                        $horaSalida = null;
                        $motivo = null;
                        $observaciones = null;
                        $claseId = null;

                        if (isset($fechasFeriado[$fechaStr])) {
                            $estadoStr = 'Feriado/Justificado';
                            $motivo = $fechasFeriado[$fechaStr];
                            $observaciones = 'Clase no realizada por día feriado o período sin actividades';

                            if ($estado && $estadoStr !== $this->transformEstado($estado)) {
                                continue;
                            }

                            $clasesData->push([
                                'id'                => null,
                                'fecha'             => clone $fecha,
                                'dia'               => ucfirst($diaFecha),
                                'periodo'           => $periodo ?? $planificacion->horario->periodo ?? 'N/A',
                                'profesor'          => $planificacion->horario->profesor->name,
                                'run_profesor'      => $runProfesor,
                                'asignatura'        => $planificacion->asignatura->nombre_asignatura ?? 'N/A',
                                'codigo_asignatura' => $planificacion->asignatura->codigo_asignatura ?? 'N/A',
                                'id_asignatura'     => $planificacion->id_asignatura,
                                'espacio'           => $planificacion->id_espacio,
                                'modulo'            => preg_replace('/^[A-Z]{2}\./', '', $planificacion->id_modulo),
                                'hora_inicio'       => $planificacion->modulo->hora_inicio,
                                'hora_fin'          => $planificacion->modulo->hora_termino,
                                'estado'            => $estadoStr,
                                'hora_entrada'      => null,
                                'hora_salida'       => null,
                                'motivo'            => $motivo,
                                'observaciones'     => $observaciones,
                            ]);
                            continue;
                        }
                        
                        $ahora = Carbon::now();
                        $fechaClase = Carbon::parse($fechaStr);
                        $horaFinModulo = Carbon::parse($planificacion->modulo->hora_termino);
                        $horaInicioModulo = Carbon::parse($planificacion->modulo->hora_inicio);
                        
                        $minutosMargenIngreso = ModulosHelper::getMargenIngresoMinutos($planificacion->id_modulo);
                        $fechaHoraFinClase = $fechaClase->copy()->setTimeFromTimeString($horaFinModulo->format('H:i:s'));
                        
                        if (isset($this->clasesNoRealizadasCache[$claveClase])) {
                            $claseNoRealizada = $this->clasesNoRealizadasCache[$claveClase];
                            $claseId = $claseNoRealizada->id;
                            $estadoStr = match($claseNoRealizada->estado) {
                                'no_realizada' => 'No Registrada',
                                'realizada', 'registrada' => 'Realizada',
                                'justificado'  => 'Justificada',
                                'recuperada'   => 'Recuperada',
                                'pendiente'    => 'Pendiente de Recuperación',
                                default        => 'No Registrada',
                            };
                            $motivo = $claseNoRealizada->motivo;
                            $observaciones = $claseNoRealizada->observaciones;
                        }
                        elseif (isset($this->reservasCache[$claveReserva])) {
                            $reservasDelDia = collect($this->reservasCache[$claveReserva])->filter(function($r) use ($runProfesor) {
                                $reservaRunProfesor = $this->normalizeRun($r->run_profesor);
                                $reservaRunSolicitante = $this->normalizeRun($r->run_solicitante);
                                return (!empty($reservaRunProfesor) && $reservaRunProfesor === $runProfesor) || 
                                       (!empty($reservaRunSolicitante) && $reservaRunSolicitante === $runProfesor);
                            });
                            
                            $reserva = null;
                            
                            if ($reservasDelDia->isNotEmpty()) {
                                foreach ($reservasDelDia as $r) {
                                    if ($r->id_asignatura == $planificacion->id_asignatura) {
                                        $reserva = $r;
                                        break;
                                    }
                                }
                                
                                if (!$reserva) {
                                    foreach ($reservasDelDia as $r) {
                                        $horaAcceso = Carbon::parse($r->hora);
                                        $horaSalidaTemp = $r->hora_salida ? Carbon::parse($r->hora_salida) : null;
                                        $margenInicio = $horaInicioModulo->copy()->subMinutes($minutosMargenIngreso);
                                        
                                        if ($horaAcceso >= $margenInicio && $horaAcceso <= $horaFinModulo) {
                                            $reserva = $r;
                                            break;
                                        }
                                        if ($horaAcceso < $margenInicio && (!$horaSalidaTemp || $horaSalidaTemp >= $horaInicioModulo)) {
                                            $reserva = $r;
                                            break;
                                        }
                                        if (($r->modulos ?? 1) > 1 && $horaAcceso <= $horaFinModulo) {
                                            $reserva = $r;
                                            break;
                                        }
                                    }
                                }
                            }
                            
                            if ($reserva) {
                                $horaInicioReserva = Carbon::parse($reserva->hora);
                                $horaFinReserva = $reserva->hora_salida ? Carbon::parse($reserva->hora_salida) : null;
                                
                                $margenInicio = $horaInicioModulo->copy()->subMinutes($minutosMargenIngreso);
                                $ingresoDirecto = ($horaInicioReserva >= $margenInicio && $horaInicioReserva <= $horaFinModulo);
                                $ingresoPrevio = ($horaInicioReserva < $margenInicio && 
                                                 (!$horaFinReserva || $horaFinReserva >= $horaInicioModulo));
                                $mismoBloqueClase = ($reserva->id_asignatura == $planificacion->id_asignatura)
                                    && ($horaInicioReserva <= $horaFinModulo);

                                if ($ingresoDirecto || $ingresoPrevio || $mismoBloqueClase) {
                                    $estadoStr = 'Realizada';
                                    $horaEntrada = $reserva->hora;
                                    $horaSalida = $reserva->hora_salida;
                                    
                                    if ($ingresoDirecto) {
                                        $diferencia = $horaInicioReserva->diffInMinutes($horaInicioModulo, false);
                                        if ($diferencia > 15) {
                                            $observaciones = "Atraso de {$diferencia} minutos";
                                        }
                                    }
                                }
                                elseif ($fechaHoraFinClase < $ahora) {
                                    $estadoStr = 'No Registrada';
                                    $motivo = 'Sin registro de acceso';
                                    $observaciones = 'No se detectó ingreso durante el horario de clase';
                                }
                            }
                            elseif ($fechaHoraFinClase < $ahora) {
                                $estadoStr = 'No Registrada';
                                $motivo = 'Sin registro de acceso';
                                $observaciones = 'No se detectó ingreso durante el horario de clase';
                            }
                        }
                        elseif ($fechaHoraFinClase < $ahora) {
                            $estadoStr = 'No Registrada';
                            $motivo = 'Sin registro de acceso';
                            $observaciones = 'No se detectó ingreso durante el horario de clase';
                        }
                        
                        if ($estadoStr === 'Planificada') {
                            continue;
                        }
                        
                        if ($estado && $estadoStr !== $this->transformEstado($estado)) {
                            continue;
                        }
                        
                        $clasesData->push([
                            'id' => $claseId,
                            'fecha' => clone $fecha,
                            'dia' => ucfirst($diaFecha),
                            'periodo' => $periodo ?? $planificacion->horario->periodo ?? 'N/A',
                            'profesor' => $planificacion->horario->profesor->name,
                            'run_profesor' => $runProfesor,
                            'asignatura' => $planificacion->asignatura->nombre_asignatura ?? 'N/A',
                            'codigo_asignatura' => $planificacion->asignatura->codigo_asignatura ?? 'N/A',
                            'id_asignatura' => $planificacion->id_asignatura,
                            'espacio' => $planificacion->id_espacio,
                            'modulo' => preg_replace('/^[A-Z]{2}\./', '', $planificacion->id_modulo),
                            'hora_inicio' => $planificacion->modulo->hora_inicio,
                            'hora_fin' => $planificacion->modulo->hora_termino,
                            'estado' => $estadoStr,
                            'hora_entrada' => $horaEntrada,
                            'hora_salida' => $horaSalida,
                            'motivo' => $motivo,
                            'observaciones' => $observaciones,
                            'hora_deteccion' => $fechaHoraFinClase,
                        ]);
                    }
                }
            }
            
            unset($planificaciones);
            gc_collect_cycles();
        });

        $this->clasesNoRealizadasCache = [];
        $this->reservasCache = [];

        $clasesData = $clasesData->unique(function($item) {
            return $item['fecha']->format('Y-m-d') . '_' . $item['espacio'] . '_' . $item['modulo'] . '_' . $this->normalizeRun($item['run_profesor']) . '_' . ($item['id_asignatura'] ?? '');
        });

        $clasesAgrupadas = $clasesData->groupBy(function($item) {
            return $item['fecha']->format('Y-m-d') . '_' . $item['espacio'] . '_' . $this->normalizeRun($item['run_profesor']) . '_' . ($item['id_asignatura'] ?? '');
        });

        $clasesProcesadas = new Collection();

        foreach ($clasesAgrupadas as $grupoKey => $items) {
            $itemsOrdenados = $items->sortBy('hora_inicio')->values();

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

            foreach ($bloquesConsecutivos as $bloque) {
                $bloqueItems = collect($bloque);

                $horaSalidaBloque = null;
                foreach ($bloqueItems as $item) {
                    if ($item['estado'] === 'Realizada' && !empty($item['hora_salida'])) {
                        $horaSalidaBloque = $item['hora_salida'];
                        break;
                    }
                }

                if ($horaSalidaBloque !== null) {
                    $horaSalidaReal = Carbon::parse($horaSalidaBloque);
                    $posicionN = 0;
                    $totalModulosBloque = count($bloqueItems);

                    $modulosCompletados = 0;
                    foreach ($bloqueItems as $bItem) {
                        $horarioCanon = ModulosHelper::getHorarioModulo($bItem['dia'], (int) $bItem['modulo']);
                        $finModulo = Carbon::parse($horarioCanon ? $horarioCanon['fin'] : $bItem['hora_fin']);
                        if ($horaSalidaReal->gte($finModulo->subMinutes(10))) {
                            $modulosCompletados++;
                        }
                    }

                    $itemsActualizados = collect();
                    foreach ($bloqueItems as $item) {
                        $posicionN++;

                        if ($item['estado'] === 'Realizada' && !empty($item['hora_entrada'])) {
                            $toleranciaMinutos = 10 * ($posicionN - 1);

                            $horarioCanon    = ModulosHelper::getHorarioModulo($item['dia'], (int) $item['modulo']);
                            $horaFinCanonica = $horarioCanon ? $horarioCanon['fin'] : $item['hora_fin'];
                            $horaMinimaSalida = Carbon::parse($horaFinCanonica)->subMinutes($toleranciaMinutos);

                            if ($horaSalidaReal->lt($horaMinimaSalida)) {
                                $horaPrimerModulo = Carbon::parse($bloqueItems->first()['hora_inicio'] ?? $horaSalidaBloque);
                                $minutosDesdeInicio = $horaPrimerModulo->diffInMinutes($horaSalidaReal);

                                if ($minutosDesdeInicio > 40) {
                                    $minutosAntes = $horaSalidaReal->diffInMinutes($horaMinimaSalida);
                                    if ($minutosAntes >= 5) {
                                        $item['estado'] = 'Realizada';
                                        $obsRetiro = "Retiro anticipado: Se retiró {$minutosAntes} min antes del mínimo (completó {$modulosCompletados} de {$totalModulosBloque} módulos)";
                                        $item['observaciones'] = !empty($item['observaciones']) 
                                            ? $item['observaciones'] . " | " . $obsRetiro 
                                            : $obsRetiro;
                                    }
                                }
                            }
                        }
                        $itemsActualizados->push($item);
                    }
                    $bloqueItems = $itemsActualizados;
                }

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

        return $clasesData->sortBy([
            ['fecha', 'desc'],
            ['espacio', 'asc'],
            ['modulo', 'asc'],
        ])->values();
    }

    private function transformEstado($estado)
    {
        return match($estado) {
            'no_realizada'            => 'No Registrada',
            'realizada', 'registrada' => 'Realizada',
            'justificado'             => 'Justificada',
            'recuperada'              => 'Recuperada',
            'pendiente'               => 'Pendiente de Recuperación',
            'feriado'                 => 'Feriado/Justificado',
            default                   => $estado,
        };
    }

    private function normalizeRun($run)
    {
        if (!$run) {
            return '';
        }
        if (str_contains($run, '-')) {
            $parts = explode('-', $run);
            $run = $parts[0];
        }
        return preg_replace('/[^0-9]/', '', $run);
    }
}
