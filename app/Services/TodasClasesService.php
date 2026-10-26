<?php

namespace App\Services;

use App\Models\Planificacion_Asignatura;
use App\Models\PlanificacionProfesorColaborador;
use App\Models\ProfesorColaborador;
use App\Models\ClaseNoRealizada;
use App\Models\Reserva;
use App\Models\DiaFeriado;
use App\Models\Modulo;
use App\Models\SesionAsistencia;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;
use App\Helpers\ModulosHelper;
use App\Helpers\SemesterHelper;

class TodasClasesService
{
    protected $clasesNoRealizadasCache = [];
    protected $reservasCache = [];
    protected $reservasPorFechaCache = [];
    protected $sesionesAsistenciaCache = [];

    /**
     * Obtener todas las clases (planificadas, realizadas y no realizadas)
     */
    public function obtenerTodasLasClases($fechaInicio = null, $fechaFin = null, $periodo = null, $search = null, $estado = null, $ua = null)
    {
        $fechaInicio = $fechaInicio ? Carbon::parse($fechaInicio)->startOfDay() : null;
        $fechaFin = $fechaFin ? Carbon::parse($fechaFin)->endOfDay() : null;

        // Aumentar límites de memoria y tiempo de ejecución temporalmente para procesos grandes
        @ini_set('memory_limit', '512M');
        @set_time_limit(180);
        @ini_set('max_execution_time', '180');
        
        $clasesData = new Collection();

        \App\Helpers\SemesterHelper::asegurarSeparacionSemestres();

        try {
            $periodosModels = \App\Models\PeriodoAcademico::orderBy('anio')->orderBy('semestre')->get();
        } catch (\Throwable $e) {
            $periodosModels = collect();
        }

        // Si no se especificó período explícito, inferirlo de las fechas o usar el semestre activo actual
        if (!$periodo) {
            if ($fechaInicio && $fechaFin) {
                $pIni = $this->resolverPeriodoParaFecha($fechaInicio->format('Y-m-d'), $periodosModels);
                $pFin = $this->resolverPeriodoParaFecha($fechaFin->format('Y-m-d'), $periodosModels);
                if ($pIni === $pFin) {
                    $periodo = $pIni;
                }
            } else {
                $periodo = \App\Helpers\SemesterHelper::getCurrentPeriod($fechaInicio ?: null);
            }
        }

        if ($periodo) {
            $limitesSemestre = \App\Helpers\SemesterHelper::getLimitesPeriodo($periodo, $periodosModels);
            $inicioSemestre = $limitesSemestre['inicio'];
            $finSemestre = $limitesSemestre['fin'];

            if (!$fechaInicio || $fechaInicio->lt($inicioSemestre) || $fechaInicio->gt($finSemestre)) {
                $fechaInicio = $inicioSemestre->copy();
            }
            if (!$fechaFin || $fechaFin->gt($finSemestre) || $fechaFin->lt($inicioSemestre)) {
                $fechaFin = $finSemestre->copy();
            }
        } else {
            if (!$fechaInicio) {
                $periodoActual = \App\Helpers\SemesterHelper::getCurrentPeriod();
                $limitesActual = \App\Helpers\SemesterHelper::getLimitesPeriodo($periodoActual, $periodosModels);
                $fechaInicio = $limitesActual['inicio']->copy();
            }
            if (!$fechaFin) {
                $fechaFin = Carbon::today()->endOfDay();
            }
        }

        $hoy = Carbon::today()->endOfDay();
        if ($fechaFin->gt($hoy)) {
            $fechaFin = $hoy->copy();
        }

        $dias = ['domingo', 'lunes', 'martes', 'miércoles', 'jueves', 'viernes', 'sábado'];

        $feriadosEnRango = $fechaInicio->lte($fechaFin)
            ? DiaFeriado::activos()->enRango($fechaInicio->toDateString(), $fechaFin->toDateString())->get()
            : collect();

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
        $periodoPorFecha = [];
        $currentDate = $fechaInicio->copy()->startOfDay();
        $fechaFinDia = $fechaFin->copy()->startOfDay();
        while ($currentDate <= $fechaFinDia) {
            if ($currentDate->dayOfWeek >= 1 && $currentDate->dayOfWeek <= 6) {
                $fStr = $currentDate->format('Y-m-d');
                $pFecha = $this->resolverPeriodoParaFecha($fStr, $periodosModels);
                if (!$periodo || $pFecha === $periodo) {
                    $fechas[] = $fStr;
                    $periodoPorFecha[$fStr] = $pFecha;
                }
            }
            $currentDate->addDay();
        }

        // ── 1. CACHE DE CLASES NO REALIZADAS / JUSTIFICADAS / REGISTRADAS ──
        $clasesNoRealizadasQuery = ClaseNoRealizada::with([
                'asignatura:id_asignatura,nombre_asignatura,codigo_asignatura,id_carrera,periodo',
                'asignatura.carrera:id_carrera,nombre,id_area_academica',
                'asignatura.carrera.areaAcademica:id_area_academica,nombre_area_academica',
                'profesor:run_profesor,name,id_carrera,id_area_academica',
                'profesor.carrera:id_carrera,nombre',
                'profesor.areaAcademica:id_area_academica,nombre_area_academica',
                'modulo:id_modulo,dia,hora_inicio,hora_termino',
            ])
            ->whereBetween('fecha_clase', [$fechaInicio->toDateString(), $fechaFin->toDateString()]);
        
        if ($search) {
            $this->aplicarFiltroBusquedaClaseNoRealizada($clasesNoRealizadasQuery, $search);
        }
        
        $this->clasesNoRealizadasCache = [];
        $todasClasesNoRealizadas = $clasesNoRealizadasQuery->get();
        $clasesNoRealizadasUsadasIds = [];

        foreach ($todasClasesNoRealizadas as $clase) {
            $fecha = Carbon::parse($clase->fecha_clase)->format('Y-m-d');
            $runNorm = $this->normalizeRun($clase->run_profesor);
            $modulos = explode(',', $clase->id_modulo);
            foreach ($modulos as $mod) {
                $modTrim = trim($mod);
                $key = "{$fecha}_{$clase->id_espacio}_{$modTrim}_{$runNorm}";
                $this->clasesNoRealizadasCache[$key] = $clase;
            }
        }

        // ── 2. CACHE DE RESERVAS (POR FECHA+ESPACIO Y POR FECHA PARA SALAS PARALELAS / CAMBIOS DE SALA / ESPONTÁNEAS) ──
        $todasReservas = Reserva::with([
                'profesor:run_profesor,name,id_carrera,id_area_academica',
                'profesor.carrera:id_carrera,nombre',
                'profesor.areaAcademica:id_area_academica,nombre_area_academica',
                'asignatura:id_asignatura,nombre_asignatura,codigo_asignatura,id_carrera,periodo',
                'asignatura.carrera:id_carrera,nombre,id_area_academica',
                'asignatura.carrera.areaAcademica:id_area_academica,nombre_area_academica',
            ])
            ->whereBetween('fecha_reserva', [$fechaInicio->toDateString(), $fechaFin->toDateString()])
            ->where(function($q) {
                $q->whereNotNull('run_profesor')
                  ->orWhereNotNull('run_solicitante');
            })
            ->whereNotNull('hora')
            ->get();

        $this->reservasCache = $todasReservas
            ->groupBy(function($reserva) {
                return Carbon::parse($reserva->fecha_reserva)->format('Y-m-d') . '_' . $reserva->id_espacio;
            })
            ->all();

        $this->reservasPorFechaCache = $todasReservas
            ->groupBy(function($reserva) {
                return Carbon::parse($reserva->fecha_reserva)->format('Y-m-d');
            })
            ->all();

        $reservasUsadasIds = [];

        // ── 3. CACHE DE SESIONES DE ASISTENCIA ESTUDIANTIL (RESPALDO PORTAL DOCENTE / ESPACIOS EXTERNOS) ──
        $this->sesionesAsistenciaCache = [];
        try {
            if (Schema::connection('tenant')->hasTable('sesiones_asistencia')) {
                $sesiones = SesionAsistencia::whereBetween('fecha', [$fechaInicio->toDateString(), $fechaFin->toDateString()])->get();
                foreach ($sesiones as $sesion) {
                    $fStr = Carbon::parse($sesion->fecha)->format('Y-m-d');
                    if ($sesion->id_asignatura) {
                        $this->sesionesAsistenciaCache["{$fStr}_asig_{$sesion->id_asignatura}"] = $sesion;
                    }
                    if ($sesion->id_profesor_colaborador) {
                        $this->sesionesAsistenciaCache["{$fStr}_colab_{$sesion->id_profesor_colaborador}"] = $sesion;
                    }
                    if ($sesion->run_profesor) {
                        $rNorm = $this->normalizeRun($sesion->run_profesor);
                        $this->sesionesAsistenciaCache["{$fStr}_run_{$rNorm}"] = $sesion;
                    }
                }
            }
        } catch (\Throwable $e) {
            // Ignorar si la tabla aún no existe en algún entorno
        }

        // ── 4. MAPA DE CO-DOCENTES (TITULARES Y COLABORADORES) POR ASIGNATURA Y MÓDULO ──
        // Permite:
        // a) Reconocer el registro cuando el co-docente (titular o colaborador) abre la sala de la misma asignatura.
        // b) No penalizar con falso "Retiro anticipado" al titular cuando parte de sus módulos los dicta el colaborador.
        $coDocentesPorAsignatura = [];
        $modulosColaboradorPorAsig = [];

        $todosColabs = ProfesorColaborador::with(['profesor:run_profesor,name'])
            ->get();

        $planificacionesColabTodas = PlanificacionProfesorColaborador::withoutGlobalScope('vigencia_activa')
            ->with(['profesorColaborador.profesor:run_profesor,name'])
            ->get();

        foreach ($todosColabs as $c) {
            if ($c->id_asignatura && $c->run_profesor_colaborador) {
                $runColabNorm = $this->normalizeRun($c->run_profesor_colaborador);
                $coDocentesPorAsignatura[$c->id_asignatura][$runColabNorm] = $c->profesor->name ?? $runColabNorm;
            }
        }

        foreach ($planificacionesColabTodas as $pc) {
            $colab = $pc->profesorColaborador;
            if ($colab && $colab->id_asignatura && $colab->run_profesor_colaborador) {
                $runColabNorm = $this->normalizeRun($colab->run_profesor_colaborador);
                $modulosColaboradorPorAsig[$colab->id_asignatura . '_' . $pc->id_modulo][$runColabNorm] = [
                    'run' => $runColabNorm,
                    'nombre' => $colab->profesor->name ?? $runColabNorm,
                    'fecha_inicio' => $pc->fecha_inicio_vigencia ?? $colab->fecha_inicio,
                    'fecha_fin' => $pc->fecha_fin_vigencia ?? $colab->fecha_termino,
                ];
            }
        }

        // Mapa de semestres asociados a cada asignatura según sus horarios titulares
        $periodoActualReal = \App\Helpers\SemesterHelper::getCurrentAcademicYear() . '-' . \App\Helpers\SemesterHelper::getCurrentSemester();
        $asignaturasValidasPeriodo = [];
        $periodosPorAsignatura = [];
        try {
            $asigPeriodosRows = \Illuminate\Support\Facades\DB::connection('tenant')
                ->table('planificacion_asignaturas as pa')
                ->join('horarios as h', 'pa.id_horario', '=', 'h.id_horario')
                ->whereNotNull('pa.id_asignatura')
                ->whereNotNull('h.periodo')
                ->select('pa.id_asignatura', 'h.periodo', 'pa.fecha_fin_vigencia')
                ->get();
            foreach ($asigPeriodosRows as $apr) {
                if ($apr->periodo === $periodoActualReal && $apr->fecha_fin_vigencia !== null) {
                    continue;
                }
                $periodosPorAsignatura[$apr->id_asignatura][$apr->periodo] = true;
            }
        } catch (\Throwable $e) {
        }

        // ── 5. PROCESAR PLANIFICACIONES DE TITULARES (INCLUYENDO HISTÓRICAS CON VIGENCIA) ──
        $tieneVigenciaPlan = Planificacion_Asignatura::tieneColumnaVigencia();
        $colsPlan = ['id', 'id_asignatura', 'id_espacio', 'id_modulo', 'id_horario'];
        if ($tieneVigenciaPlan) {
            $colsPlan = array_merge($colsPlan, ['fecha_inicio_vigencia', 'fecha_fin_vigencia', 'es_espacio_externo']);
        }
        $relEspacio = $tieneVigenciaPlan ? 'espacio:id_espacio,nombre_espacio,tipo_espacio,es_externo' : 'espacio:id_espacio,nombre_espacio,tipo_espacio';

        $query = Planificacion_Asignatura::withoutGlobalScope('vigencia_activa')
            ->select($colsPlan)
            ->with([
                'asignatura:id_asignatura,nombre_asignatura,codigo_asignatura,id_carrera,run_profesor,run_profesor_reemplazo,periodo',
                'asignatura.carrera:id_carrera,nombre,id_area_academica',
                'asignatura.carrera.areaAcademica:id_area_academica,nombre_area_academica',
                $relEspacio,
                'modulo:id_modulo,dia,hora_inicio,hora_termino',
                'horario' => function($query) {
                    $query->select('id_horario', 'run_profesor', 'periodo')
                        ->with([
                            'profesor:run_profesor,name,id_carrera,id_area_academica',
                            'profesor.carrera:id_carrera,nombre',
                            'profesor.areaAcademica:id_area_academica,nombre_area_academica',
                        ]);
                }
            ])
            ->whereHas('modulo')
            ->whereHas('horario.profesor');

        if ($periodo) {
            if ($periodo === $periodoActualReal && $tieneVigenciaPlan) {
                $query->whereNull('fecha_fin_vigencia');
            }
            $query->whereHas('horario', function($q) use ($periodo) {
                $q->where('periodo', $periodo);
            });
        }

        if ($search) {
            $palabras = array_values(array_filter(preg_split('/\s+/', trim($search))));
            $searchTerm = '%' . trim($search) . '%';
            $cleanRun = preg_replace('/[^0-9kK]/', '', $search);

            $query->where(function($q) use ($searchTerm, $palabras, $cleanRun) {
                $q->whereHas('asignatura', function($aq) use ($searchTerm) {
                    $aq->where('nombre_asignatura', 'like', $searchTerm)
                       ->orWhere('codigo_asignatura', 'like', $searchTerm);
                })
                ->orWhere('id_espacio', 'like', $searchTerm);

                if (!empty($cleanRun) && strlen($cleanRun) >= 3) {
                    $q->whereHas('horario.profesor', function($pq) use ($cleanRun) {
                        $pq->where('run_profesor', 'like', '%' . $cleanRun . '%')
                           ->orWhereRaw("REPLACE(REPLACE(run_profesor, '.', ''), '-', '') LIKE ?", ['%' . $cleanRun . '%']);
                    });
                }

                if (!empty($palabras)) {
                    $q->whereHas('horario.profesor', function($pq) use ($palabras) {
                        $pq->where(function($nameQ) use ($palabras) {
                            foreach ($palabras as $p) {
                                $nameQ->where('name', 'like', '%' . $p . '%');
                            }
                        });
                    });
                }
            });
        }

        $query->chunk(100, function($planificaciones) use (
            &$clasesData,
            &$clasesNoRealizadasUsadasIds,
            &$reservasUsadasIds,
            &$coDocentesPorAsignatura,
            &$asignaturasValidasPeriodo,
            $fechas,
            $periodoPorFecha,
            $periodosModels,
            $dias,
            $fechasFeriado,
            $periodo,
            $estado
        ) {
            foreach ($planificaciones as $planificacion) {
                if (!$planificacion->modulo || !$planificacion->horario || !$planificacion->horario->profesor) {
                    continue;
                }

                $periodoPlan = $planificacion->horario->periodo ?? null;
                if (empty($periodoPlan) && !empty($planificacion->id_horario) && preg_match('/(\d{4}-[12])$/', (string) $planificacion->id_horario, $mP)) {
                    $periodoPlan = $mP[1];
                }
                if ($periodo && $periodoPlan && $periodoPlan !== $periodo) {
                    continue;
                }
                if ($periodo && $planificacion->asignatura && !empty($planificacion->asignatura->periodo) && $planificacion->asignatura->periodo !== $periodo) {
                    continue;
                }
                if ($planificacion->id_asignatura) {
                    $asignaturasValidasPeriodo[$planificacion->id_asignatura] = true;
                }

                $runProfesor = $this->normalizeRun($planificacion->horario->profesor->run_profesor);
                if ($planificacion->id_asignatura) {
                    $coDocentesPorAsignatura[$planificacion->id_asignatura][$runProfesor] = $planificacion->horario->profesor->name;
                    if (!empty($planificacion->asignatura?->run_profesor_reemplazo)) {
                        $runReemp = $this->normalizeRun($planificacion->asignatura->run_profesor_reemplazo);
                        $coDocentesPorAsignatura[$planificacion->id_asignatura][$runReemp] = 'Docente Reemplazo';
                    }
                }

                $diaModulo = ModulosHelper::normalizarDia($planificacion->modulo->dia);
                $inicioVigencia = $planificacion->fecha_inicio_vigencia ? Carbon::parse($planificacion->fecha_inicio_vigencia)->format('Y-m-d') : null;
                $finVigencia = $planificacion->fecha_fin_vigencia ? Carbon::parse($planificacion->fecha_fin_vigencia)->format('Y-m-d') : null;

                // Acotar siempre la vigencia máxima del horario a los límites de su propio semestre
                if ($periodoPlan) {
                    $limPlan = \App\Helpers\SemesterHelper::getLimitesPeriodo($periodoPlan, $periodosModels);
                    $iniSemPlan = $limPlan['inicio']->format('Y-m-d');
                    $finSemPlan = $limPlan['fin']->format('Y-m-d');
                    if (!$inicioVigencia || $inicioVigencia < $iniSemPlan || empty($planificacion->fecha_fin_vigencia)) {
                        $inicioVigencia = $iniSemPlan;
                    }
                    if (!$finVigencia || $finVigencia > $finSemPlan) {
                        $finVigencia = $finSemPlan;
                    }
                }

                $esExterno = (bool) ($planificacion->es_espacio_externo || ($planificacion->espacio && $planificacion->espacio->esEspacioExterno()));

                foreach ($fechas as $fechaStr) {
                    $periodoFecha = $periodoPorFecha[$fechaStr] ?? $periodo;

                    // No mezclar semestres: el horario de un semestre solo aplica en fechas de ese mismo semestre
                    if ($periodoPlan && $periodoFecha && $periodoPlan !== $periodoFecha) {
                        continue;
                    }

                    // Respetar vigencia de horario (cuando hubo ajustes durante el semestre)
                    if ($inicioVigencia && $fechaStr < $inicioVigencia) {
                        continue;
                    }
                    if ($finVigencia && $fechaStr > $finVigencia) {
                        continue;
                    }

                    $fecha = Carbon::parse($fechaStr);
                    $diaFecha = ModulosHelper::normalizarDia($dias[$fecha->dayOfWeek]);
                    
                    if ($diaFecha === $diaModulo) {
                        $claveClase = $fechaStr . '_' . 
                                      $planificacion->id_espacio . '_' . 
                                      $planificacion->id_modulo . '_' . 
                                      $runProfesor;
                        
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

                            if (isset($this->clasesNoRealizadasCache[$claveClase])) {
                                $claseNoRealizada = $this->clasesNoRealizadasCache[$claveClase];
                                $claseId = $claseNoRealizada->id;
                                $clasesNoRealizadasUsadasIds[$claseNoRealizada->id] = true;
                                $estadoStr = match($claseNoRealizada->estado) {
                                    'no_realizada' => 'No Registrada',
                                    'realizada', 'registrada' => 'Realizada',
                                    'justificado'  => 'Feriado/Justificado',
                                    'recuperada'   => 'Recuperada',
                                    'pendiente'    => 'Pendiente de Recuperación',
                                    default        => 'Feriado/Justificado',
                                };
                                $motivo = $claseNoRealizada->motivo ?: $motivo;
                                $observaciones = $claseNoRealizada->observaciones ?: $observaciones;
                            }

                            if ($estado && $estadoStr !== $this->transformEstado($estado)) {
                                continue;
                            }

                            $clasesData->push([
                                'id'                => $claseId,
                                'fecha'             => clone $fecha,
                                'dia'               => ucfirst($diaFecha),
                                'periodo'           => $periodoPlan ?: ($periodoFecha ?: 'N/A'),
                                'profesor'          => $planificacion->horario->profesor->name,
                                'run_profesor'      => $runProfesor,
                                'ua'                => $this->resolverUa($planificacion->asignatura, $planificacion->horario->profesor),
                                'carrera'           => $this->resolverNombreUa($planificacion->asignatura, $planificacion->horario->profesor),
                                'asignatura'        => $planificacion->asignatura->nombre_asignatura ?? 'N/A',
                                'codigo_asignatura' => $planificacion->asignatura->codigo_asignatura ?? 'N/A',
                                'id_asignatura'     => $planificacion->id_asignatura,
                                'espacio'           => $planificacion->id_espacio,
                                'modulo'            => preg_replace('/^[A-Z]{2}\./', '', $planificacion->id_modulo),
                                'id_modulo_raw'     => $planificacion->id_modulo,
                                'hora_inicio'       => $planificacion->modulo->hora_inicio,
                                'hora_fin'          => $planificacion->modulo->hora_termino,
                                'estado'            => $estadoStr,
                                'hora_entrada'      => null,
                                'hora_salida'       => null,
                                'motivo'            => $motivo,
                                'observaciones'     => $observaciones,
                                'rol_bloque'        => 'titular',
                            ]);
                            continue;
                        }
                        
                        $ahora = Carbon::now();
                        $fechaClase = Carbon::parse($fechaStr);
                        $horaFinModulo = Carbon::parse($planificacion->modulo->hora_termino);
                        $horaInicioModulo = Carbon::parse($planificacion->modulo->hora_inicio);
                        $numModulo = (int) preg_replace('/^[A-Z]{2}\./', '', $planificacion->id_modulo);
                        
                        $minutosMargenIngreso = ModulosHelper::getMargenIngresoMinutos($planificacion->id_modulo);
                        $fechaHoraFinClase = $fechaClase->copy()->setTimeFromTimeString($horaFinModulo->format('H:i:s'));

                        // Buscar match con Reserva primero (en espacio exacto, espacio alternativo/paralelo o por co-docente)
                        $runsAceptados = [$runProfesor => $planificacion->horario->profesor->name];
                        if ($planificacion->id_asignatura && isset($coDocentesPorAsignatura[$planificacion->id_asignatura])) {
                            foreach ($coDocentesPorAsignatura[$planificacion->id_asignatura] as $rCo => $nomCo) {
                                $runsAceptados[$rCo] = $nomCo;
                            }
                        }

                        $matchReserva = $this->resolverReservaParaClase(
                            $fechaStr,
                            $planificacion->id_espacio,
                            $planificacion->id_asignatura,
                            $runProfesor,
                            $runsAceptados,
                            $horaInicioModulo,
                            $horaFinModulo,
                            $minutosMargenIngreso,
                            $numModulo
                        );

                        if ($matchReserva) {
                            $reserva = $matchReserva['reserva'];
                            $reservasUsadasIds[$reserva->id_reserva] = true;
                            $estadoStr = 'Realizada';
                            $horaEntrada = $reserva->hora;
                            $horaSalida = $reserva->hora_salida;

                            if (!empty($matchReserva['nota'])) {
                                $observaciones = $matchReserva['nota'];
                            }

                            if ($matchReserva['ingreso_directo']) {
                                $horaInicioReserva = Carbon::parse($reserva->hora);
                                $diferencia = $horaInicioReserva->diffInMinutes($horaInicioModulo, false);
                                if ($diferencia > 15) {
                                    $obsAtraso = "Atraso de {$diferencia} minutos";
                                    $observaciones = $observaciones ? "{$observaciones} | {$obsAtraso}" : $obsAtraso;
                                }
                            }

                            if (isset($this->clasesNoRealizadasCache[$claveClase])) {
                                $claseNoRealizada = $this->clasesNoRealizadasCache[$claveClase];
                                $claseId = $claseNoRealizada->id;
                                $clasesNoRealizadasUsadasIds[$claseNoRealizada->id] = true;
                            }
                        }
                        elseif (isset($this->clasesNoRealizadasCache[$claveClase])) {
                            $claseNoRealizada = $this->clasesNoRealizadasCache[$claveClase];
                            $claseId = $claseNoRealizada->id;
                            $clasesNoRealizadasUsadasIds[$claseNoRealizada->id] = true;
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

                        // Respaldo por lista de asistencia del Portal Docente o Espacio Externo (Gimnasio UdeC / Pista Atlética)
                        if ($estadoStr === 'Planificada' || $estadoStr === 'No Registrada') {
                            $sesionAsist = ($planificacion->id_asignatura && isset($this->sesionesAsistenciaCache["{$fechaStr}_asig_{$planificacion->id_asignatura}"]))
                                ? $this->sesionesAsistenciaCache["{$fechaStr}_asig_{$planificacion->id_asignatura}"]
                                : ($this->sesionesAsistenciaCache["{$fechaStr}_run_{$runProfesor}"] ?? null);

                            if ($sesionAsist && (!$sesionAsist->id_asignatura || $sesionAsist->id_asignatura == $planificacion->id_asignatura)) {
                                $estadoStr = 'Realizada';
                                $motivo = null;
                                $observaciones = 'Respaldada por lista de asistencia de estudiantes' . ($sesionAsist->id_espacio ? " ({$sesionAsist->id_espacio})" : '');
                            } elseif ($esExterno && $fechaHoraFinClase < $ahora) {
                                $estadoStr = 'Realizada';
                                $motivo = null;
                                $observaciones = "Actividad realizada en espacio externo ({$planificacion->id_espacio})";
                            } elseif ($estadoStr === 'Planificada' && $fechaHoraFinClase < $ahora) {
                                $estadoStr = 'No Registrada';
                                $motivo = 'Sin registro de acceso';
                                $observaciones = 'No se detectó ingreso durante el horario de clase';
                            }
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
                            'periodo' => $periodoPlan ?: ($periodoFecha ?: 'N/A'),
                            'profesor' => $planificacion->horario->profesor->name,
                            'run_profesor' => $runProfesor,
                            'ua' => $this->resolverUa($planificacion->asignatura, $planificacion->horario->profesor),
                            'carrera' => $this->resolverNombreUa($planificacion->asignatura, $planificacion->horario->profesor),
                            'asignatura' => $planificacion->asignatura->nombre_asignatura ?? 'N/A',
                            'codigo_asignatura' => $planificacion->asignatura->codigo_asignatura ?? 'N/A',
                            'id_asignatura' => $planificacion->id_asignatura,
                            'espacio' => $planificacion->id_espacio,
                            'modulo' => preg_replace('/^[A-Z]{2}\./', '', $planificacion->id_modulo),
                            'id_modulo_raw' => $planificacion->id_modulo,
                            'hora_inicio' => $planificacion->modulo->hora_inicio,
                            'hora_fin' => $planificacion->modulo->hora_termino,
                            'estado' => $estadoStr,
                            'hora_entrada' => $horaEntrada,
                            'hora_salida' => $horaSalida,
                            'motivo' => $motivo,
                            'observaciones' => $observaciones,
                            'hora_deteccion' => $fechaHoraFinClase,
                            'rol_bloque' => 'titular',
                        ]);
                    }
                }
            }
            
            unset($planificaciones);
            gc_collect_cycles();
        });

        // ── 6. PROCESAR PLANIFICACIONES DE PROFESORES COLABORADORES (INCLUYENDO HISTÓRICAS CON VIGENCIA) ──
        $tieneVigenciaColab = PlanificacionProfesorColaborador::tieneColumnaVigencia();
        $colsColab = ['id', 'id_profesor_colaborador', 'id_espacio', 'id_modulo', 'created_at', 'updated_at'];
        if ($tieneVigenciaColab) {
            $colsColab = array_merge($colsColab, ['fecha_inicio_vigencia', 'fecha_fin_vigencia']);
        }

        $queryColab = PlanificacionProfesorColaborador::withoutGlobalScope('vigencia_activa')
            ->select($colsColab)
            ->with([
                'modulo:id_modulo,dia,hora_inicio,hora_termino',
                $relEspacio,
                'profesorColaborador' => function($q) {
                    $q->select('id', 'run_profesor_colaborador', 'id_asignatura', 'nombre_asignatura_temporal', 'fecha_inicio', 'fecha_termino', 'estado', 'tipo_clase', 'created_at', 'updated_at')
                      ->with([
                          'profesor:run_profesor,name,id_carrera,id_area_academica',
                          'profesor.carrera:id_carrera,nombre',
                          'profesor.areaAcademica:id_area_academica,nombre_area_academica',
                          'asignatura:id_asignatura,nombre_asignatura,codigo_asignatura,id_carrera,run_profesor,periodo',
                          'asignatura.carrera:id_carrera,nombre,id_area_academica',
                          'asignatura.carrera.areaAcademica:id_area_academica,nombre_area_academica',
                      ]);
                }
            ])
            ->whereHas('modulo')
            ->whereHas('profesorColaborador.profesor');

        if ($periodo && $periodo === $periodoActualReal && $tieneVigenciaColab) {
            $queryColab->whereNull('fecha_fin_vigencia');
        }

        if ($search) {
            $palabras = array_values(array_filter(preg_split('/\s+/', trim($search))));
            $searchTerm = '%' . trim($search) . '%';
            $cleanRun = preg_replace('/[^0-9kK]/', '', $search);

            $queryColab->where(function($q) use ($searchTerm, $palabras, $cleanRun) {
                $q->whereHas('profesorColaborador.asignatura', function($aq) use ($searchTerm) {
                    $aq->where('nombre_asignatura', 'like', $searchTerm)
                       ->orWhere('codigo_asignatura', 'like', $searchTerm);
                })
                ->orWhereHas('profesorColaborador', function($cq) use ($searchTerm) {
                    $cq->where('nombre_asignatura_temporal', 'like', $searchTerm);
                })
                ->orWhere('id_espacio', 'like', $searchTerm);

                if (!empty($cleanRun) && strlen($cleanRun) >= 3) {
                    $q->whereHas('profesorColaborador.profesor', function($pq) use ($cleanRun) {
                        $pq->where('run_profesor', 'like', '%' . $cleanRun . '%')
                           ->orWhereRaw("REPLACE(REPLACE(run_profesor, '.', ''), '-', '') LIKE ?", ['%' . $cleanRun . '%']);
                    });
                }

                if (!empty($palabras)) {
                    $q->whereHas('profesorColaborador.profesor', function($pq) use ($palabras) {
                        $pq->where(function($nameQ) use ($palabras) {
                            foreach ($palabras as $p) {
                                $nameQ->where('name', 'like', '%' . $p . '%');
                            }
                        });
                    });
                }
            });
        }

        $queryColab->chunk(100, function($planificacionesColab) use (
            &$clasesData,
            &$clasesNoRealizadasUsadasIds,
            &$reservasUsadasIds,
            &$coDocentesPorAsignatura,
            &$asignaturasValidasPeriodo,
            $periodosPorAsignatura,
            $periodosModels,
            $fechas,
            $periodoPorFecha,
            $dias,
            $fechasFeriado,
            $periodo,
            $estado
        ) {
            foreach ($planificacionesColab as $planificacion) {
                $colab = $planificacion->profesorColaborador;
                if (!$planificacion->modulo || !$colab || !$colab->profesor) {
                    continue;
                }

                $periodosColab = $this->resolverPeriodosColaborador($planificacion, $colab, $periodosPorAsignatura, $periodosModels);
                if ($periodo && !empty($periodosColab) && !isset($periodosColab[$periodo])) {
                    continue;
                }

                $diaModulo = ModulosHelper::normalizarDia($planificacion->modulo->dia);
                $profesorModel = $colab->profesor;
                $asignaturaNombre = $colab->asignatura->nombre_asignatura ?? $colab->nombre_asignatura_temporal ?? 'N/A';
                $asignaturaCodigo = $colab->asignatura->codigo_asignatura ?? 'N/A';
                $idAsignatura = $colab->id_asignatura;
                if ($idAsignatura) {
                    $asignaturasValidasPeriodo[$idAsignatura] = true;
                }

                // Fechas de vigencia del colaborador y de la planificación específica
                $inicioColab = $colab->fecha_inicio ? Carbon::parse($colab->fecha_inicio)->format('Y-m-d') : null;
                $finColab = $colab->fecha_termino ? Carbon::parse($colab->fecha_termino)->format('Y-m-d') : null;
                $inicioPlan = $planificacion->fecha_inicio_vigencia ? Carbon::parse($planificacion->fecha_inicio_vigencia)->format('Y-m-d') : null;
                $finPlan = $planificacion->fecha_fin_vigencia ? Carbon::parse($planificacion->fecha_fin_vigencia)->format('Y-m-d') : null;

                // Acotar estrictamente la vigencia del colaborador al semestre al que pertenece
                $periodoUnicoColab = (!empty($periodosColab) && count($periodosColab) === 1)
                    ? (string) array_key_first($periodosColab)
                    : ($periodo ?: null);
                if ($periodoUnicoColab) {
                    $limColab = SemesterHelper::getLimitesPeriodo($periodoUnicoColab, $periodosModels);
                    $limColabIni = $limColab['inicio']->format('Y-m-d');
                    $limColabFin = $limColab['fin']->format('Y-m-d');
                    if (!$inicioColab || $inicioColab < $limColabIni) {
                        $inicioColab = $limColabIni;
                    }
                    if (empty($planificacion->fecha_fin_vigencia)) {
                        $inicioPlan = $limColabIni;
                    }
                    if (!$finColab || $finColab > $limColabFin) {
                        $finColab = $limColabFin;
                    }
                }

                $esExterno = (bool) (
                    $colab->tipo_clase === 'actividad_externa' ||
                    ($planificacion->espacio && $planificacion->espacio->esEspacioExterno())
                );

                foreach ($fechas as $fechaStr) {
                    $periodoFecha = $periodoPorFecha[$fechaStr] ?? $periodo;

                    // No mezclar semestres: validar que el bloque colaborador pertenezca al semestre de la fecha
                    if ($periodo && $periodoFecha !== $periodo) {
                        continue;
                    }
                    if (!empty($periodosColab) && !isset($periodosColab[$periodoFecha])) {
                        continue;
                    }

                    // Respetar rango de fechas del colaborador y vigencia de su bloque
                    if ($inicioColab && $fechaStr < $inicioColab) {
                        continue;
                    }
                    if ($finColab && $fechaStr > $finColab) {
                        continue;
                    }
                    if ($inicioPlan && $fechaStr < $inicioPlan) {
                        continue;
                    }
                    if ($finPlan && $fechaStr > $finPlan) {
                        continue;
                    }

                    $fecha = Carbon::parse($fechaStr);
                    $diaFecha = ModulosHelper::normalizarDia($dias[$fecha->dayOfWeek]);

                    if ($diaFecha === $diaModulo) {
                        $runProfesor = $this->normalizeRun($profesorModel->run_profesor);

                        $claveClase = $fechaStr . '_' . 
                                      $planificacion->id_espacio . '_' . 
                                      $planificacion->id_modulo . '_' . 
                                      $runProfesor;

                        $estadoStr = 'Planificada';
                        $horaEntrada = null;
                        $horaSalida = null;
                        $motivo = null;
                        $observaciones = null;
                        $claseId = null;

                        // 1. Feriados
                        if (isset($fechasFeriado[$fechaStr])) {
                            $estadoStr = 'Feriado/Justificado';
                            $motivo = $fechasFeriado[$fechaStr];
                            $observaciones = 'Clase no realizada por día feriado o período sin actividades';

                            if (isset($this->clasesNoRealizadasCache[$claveClase])) {
                                $claseNoRealizada = $this->clasesNoRealizadasCache[$claveClase];
                                $claseId = $claseNoRealizada->id;
                                $clasesNoRealizadasUsadasIds[$claseNoRealizada->id] = true;
                                $estadoStr = match($claseNoRealizada->estado) {
                                    'no_realizada' => 'No Registrada',
                                    'realizada', 'registrada' => 'Realizada',
                                    'justificado'  => 'Feriado/Justificado',
                                    'recuperada'   => 'Recuperada',
                                    'pendiente'    => 'Pendiente de Recuperación',
                                    default        => 'Feriado/Justificado',
                                };
                                $motivo = $claseNoRealizada->motivo ?: $motivo;
                                $observaciones = $claseNoRealizada->observaciones ?: $observaciones;
                            }

                            if ($estado && $estadoStr !== $this->transformEstado($estado)) {
                                continue;
                            }

                            $clasesData->push([
                                'id'                => $claseId,
                                'fecha'             => clone $fecha,
                                'dia'               => ucfirst($diaFecha),
                                'periodo'           => $periodoFecha,
                                'profesor'          => $profesorModel->name,
                                'run_profesor'      => $runProfesor,
                                'ua'                => $this->resolverUa($colab->asignatura, $profesorModel),
                                'carrera'           => $this->resolverNombreUa($colab->asignatura, $profesorModel),
                                'asignatura'        => $asignaturaNombre,
                                'codigo_asignatura' => $asignaturaCodigo,
                                'id_asignatura'     => $idAsignatura,
                                'espacio'           => $planificacion->id_espacio,
                                'modulo'            => preg_replace('/^[A-Z]{2}\./', '', $planificacion->id_modulo),
                                'id_modulo_raw'     => $planificacion->id_modulo,
                                'hora_inicio'       => $planificacion->modulo->hora_inicio,
                                'hora_fin'          => $planificacion->modulo->hora_termino,
                                'estado'            => $estadoStr,
                                'hora_entrada'      => null,
                                'hora_salida'       => null,
                                'motivo'            => $motivo,
                                'observaciones'     => $observaciones,
                                'rol_bloque'        => 'colaborador',
                            ]);
                            continue;
                        }

                        $ahora = Carbon::now();
                        $fechaClase = Carbon::parse($fechaStr);
                        $horaFinModulo = Carbon::parse($planificacion->modulo->hora_termino);
                        $horaInicioModulo = Carbon::parse($planificacion->modulo->hora_inicio);
                        $numModulo = (int) preg_replace('/^[A-Z]{2}\./', '', $planificacion->id_modulo);
                        $minutosMargenIngreso = ModulosHelper::getMargenIngresoMinutos($planificacion->id_modulo);
                        $fechaHoraFinClase = $fechaClase->copy()->setTimeFromTimeString($horaFinModulo->format('H:i:s'));

                        $runsAceptados = [$runProfesor => $profesorModel->name];
                        if ($idAsignatura && isset($coDocentesPorAsignatura[$idAsignatura])) {
                            foreach ($coDocentesPorAsignatura[$idAsignatura] as $rCo => $nomCo) {
                                $runsAceptados[$rCo] = $nomCo;
                            }
                        }
                        if ($colab->asignatura && !empty($colab->asignatura->run_profesor)) {
                            $runTitular = $this->normalizeRun($colab->asignatura->run_profesor);
                            if (!isset($runsAceptados[$runTitular])) {
                                $runsAceptados[$runTitular] = 'Docente Responsable';
                            }
                        }

                        $matchReserva = $this->resolverReservaParaClase(
                            $fechaStr,
                            $planificacion->id_espacio,
                            $idAsignatura,
                            $runProfesor,
                            $runsAceptados,
                            $horaInicioModulo,
                            $horaFinModulo,
                            $minutosMargenIngreso,
                            $numModulo
                        );

                        if ($matchReserva) {
                            $reserva = $matchReserva['reserva'];
                            $reservasUsadasIds[$reserva->id_reserva] = true;
                            $estadoStr = 'Realizada';
                            $horaEntrada = $reserva->hora;
                            $horaSalida = $reserva->hora_salida;

                            if (!empty($matchReserva['nota'])) {
                                $observaciones = $matchReserva['nota'];
                            }

                            if ($matchReserva['ingreso_directo']) {
                                $horaInicioReserva = Carbon::parse($reserva->hora);
                                $diferencia = $horaInicioReserva->diffInMinutes($horaInicioModulo, false);
                                if ($diferencia > 15) {
                                    $obsAtraso = "Atraso de {$diferencia} minutos";
                                    $observaciones = $observaciones ? "{$observaciones} | {$obsAtraso}" : $obsAtraso;
                                }
                            }

                            if (isset($this->clasesNoRealizadasCache[$claveClase])) {
                                $claseNoRealizada = $this->clasesNoRealizadasCache[$claveClase];
                                $claseId = $claseNoRealizada->id;
                                $clasesNoRealizadasUsadasIds[$claseNoRealizada->id] = true;
                            }
                        }
                        elseif (isset($this->clasesNoRealizadasCache[$claveClase])) {
                            $claseNoRealizada = $this->clasesNoRealizadasCache[$claveClase];
                            $claseId = $claseNoRealizada->id;
                            $clasesNoRealizadasUsadasIds[$claseNoRealizada->id] = true;
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

                        // Respaldo por lista de asistencia del Portal Docente o Espacio Externo
                        if ($estadoStr === 'Planificada' || $estadoStr === 'No Registrada') {
                            $sesionAsist = $this->sesionesAsistenciaCache["{$fechaStr}_colab_{$colab->id}"]
                                ?? ($idAsignatura ? ($this->sesionesAsistenciaCache["{$fechaStr}_asig_{$idAsignatura}"] ?? null) : null)
                                ?? ($this->sesionesAsistenciaCache["{$fechaStr}_run_{$runProfesor}"] ?? null);

                            if ($sesionAsist) {
                                $estadoStr = 'Realizada';
                                $motivo = null;
                                $observaciones = 'Respaldada por lista de asistencia de estudiantes' . ($sesionAsist->id_espacio ? " ({$sesionAsist->id_espacio})" : '');
                            } elseif ($esExterno && $fechaHoraFinClase < $ahora) {
                                $estadoStr = 'Realizada';
                                $motivo = null;
                                $observaciones = "Actividad realizada en espacio externo ({$planificacion->id_espacio})";
                            } elseif ($estadoStr === 'Planificada' && $fechaHoraFinClase < $ahora) {
                                $estadoStr = 'No Registrada';
                                $motivo = 'Sin registro de acceso';
                                $observaciones = 'No se detectó ingreso durante el horario de clase';
                            }
                        }

                        if ($estadoStr === 'Planificada') {
                            continue;
                        }

                        if ($estado && $estadoStr !== $this->transformEstado($estado)) {
                            continue;
                        }

                        $clasesData->push([
                            'id'                => $claseId,
                            'fecha'             => clone $fecha,
                            'dia'               => ucfirst($diaFecha),
                            'periodo'           => $periodoFecha,
                            'profesor'          => $profesorModel->name,
                            'run_profesor'      => $runProfesor,
                            'ua'                => $this->resolverUa($colab->asignatura, $profesorModel),
                            'carrera'           => $this->resolverNombreUa($colab->asignatura, $profesorModel),
                            'asignatura'        => $asignaturaNombre,
                            'codigo_asignatura' => $asignaturaCodigo,
                            'id_asignatura'     => $idAsignatura,
                            'espacio'           => $planificacion->id_espacio,
                            'modulo'            => preg_replace('/^[A-Z]{2}\./', '', $planificacion->id_modulo),
                            'id_modulo_raw'     => $planificacion->id_modulo,
                            'hora_inicio'       => $planificacion->modulo->hora_inicio,
                            'hora_fin'          => $planificacion->modulo->hora_termino,
                            'estado'            => $estadoStr,
                            'hora_entrada'      => $horaEntrada,
                            'hora_salida'       => $horaSalida,
                            'motivo'            => $motivo,
                            'observaciones'     => $observaciones,
                            'hora_deteccion'    => $fechaHoraFinClase,
                            'rol_bloque'        => 'colaborador',
                        ]);
                    }
                }
            }

            unset($planificacionesColab);
            gc_collect_cycles();
        });

        // ── 7. RESCATAR REGISTROS HISTÓRICOS EN CLASES_NO_REALIZADAS QUE CAMBIARON DE HORARIO O COLABORADOR ──
        foreach ($todasClasesNoRealizadas as $cnr) {
            if (isset($clasesNoRealizadasUsadasIds[$cnr->id])) {
                continue;
            }

            $fechaCnr = Carbon::parse($cnr->fecha_clase);
            $fechaCnrStr = $fechaCnr->format('Y-m-d');
            $periodoFechaCnr = $periodoPorFecha[$fechaCnrStr] ?? $this->resolverPeriodoParaFecha($fechaCnrStr, $periodosModels);

            if ($periodo && $periodoFechaCnr !== $periodo) {
                continue;
            }
            if ($periodo && !empty($cnr->periodo) && $cnr->periodo !== $periodo) {
                continue;
            }
            if ($cnr->estado === 'no_realizada') {
                continue;
            }
            if ($periodo && $cnr->asignatura && !empty($cnr->asignatura->periodo) && $cnr->asignatura->periodo !== $periodo) {
                continue;
            }
            if (!empty($asignaturasValidasPeriodo) && !empty($cnr->id_asignatura) && $cnr->id_asignatura !== 'RESERVA' && !isset($asignaturasValidasPeriodo[$cnr->id_asignatura])) {
                continue;
            }

            $estadoStr = match($cnr->estado) {
                'no_realizada'            => 'No Registrada',
                'realizada', 'registrada' => 'Realizada',
                'justificado'             => 'Justificada',
                'recuperada'              => 'Recuperada',
                'pendiente'               => 'Pendiente de Recuperación',
                default                   => 'No Registrada',
            };

            if ($estado && $estadoStr !== $this->transformEstado($estado)) {
                continue;
            }

            $diaFecha = ModulosHelper::normalizarDia($dias[$fechaCnr->dayOfWeek] ?? '');
            $runProfNorm = $this->normalizeRun($cnr->run_profesor);
            $modNumStr = preg_replace('/^[A-Z]{2}\./', '', (string) $cnr->id_modulo);
            $horarioCanon = ModulosHelper::getHorarioModulo($diaFecha, (int) $modNumStr);

            $obsHist = $cnr->observaciones;
            if (empty($obsHist)) {
                $obsHist = 'Registro histórico conservado tras ajuste de horario/docente';
            }

            $clasesData->push([
                'id'                => $cnr->id,
                'fecha'             => clone $fechaCnr,
                'dia'               => ucfirst($diaFecha),
                'periodo'           => $periodoFechaCnr,
                'profesor'          => $cnr->profesor->name ?? $cnr->run_profesor,
                'run_profesor'      => $runProfNorm,
                'ua'                => $this->resolverUa($cnr->asignatura, $cnr->profesor),
                'carrera'           => $this->resolverNombreUa($cnr->asignatura, $cnr->profesor),
                'asignatura'        => $cnr->asignatura->nombre_asignatura ?? 'N/A',
                'codigo_asignatura' => $cnr->asignatura->codigo_asignatura ?? 'N/A',
                'id_asignatura'     => $cnr->id_asignatura,
                'espacio'           => $cnr->id_espacio,
                'modulo'            => $modNumStr,
                'id_modulo_raw'     => $cnr->id_modulo,
                'hora_inicio'       => $cnr->modulo->hora_inicio ?? ($horarioCanon['inicio'] ?? '08:10:00'),
                'hora_fin'          => $cnr->modulo->hora_termino ?? ($horarioCanon['fin'] ?? '09:00:00'),
                'estado'            => $estadoStr,
                'hora_entrada'      => null,
                'hora_salida'       => null,
                'motivo'            => $cnr->motivo,
                'observaciones'     => $obsHist,
                'hora_deteccion'    => $cnr->hora_deteccion ?? $fechaCnr,
                'rol_bloque'        => 'historico',
            ]);
        }

        // ── 8. RESCATAR RESERVAS ESPONTÁNEAS, DE LABORATORIO ADICIONAL, RECUPERACIONES Y HORARIOS ANTERIORES ──
        foreach ($todasReservas as $res) {
            if (isset($reservasUsadasIds[$res->id_reserva])) {
                continue;
            }
            // Solo incluir reservas de docentes (o con asignatura asociada) activas o finalizadas
            if (empty($res->run_profesor) && empty($res->id_asignatura)) {
                continue;
            }
            if (!empty($res->estado) && !in_array(strtolower($res->estado), ['activa', 'finalizada'], true)) {
                continue;
            }

            if ($search && !$this->reservaCoincideConBusqueda($res, $search)) {
                continue;
            }

            $esRecuperacion = strtolower($res->tipo_reserva ?? '') === 'recuperacion'
                || str_contains(mb_strtolower($res->observaciones ?? '', 'UTF-8'), 'recupera')
                || str_contains(mb_strtolower($res->nombre_actividad ?? '', 'UTF-8'), 'recupera');

            $estadoReservaStr = $esRecuperacion ? 'Recuperada' : 'Realizada';

            if ($estado && $estadoReservaStr !== $this->transformEstado($estado)) {
                continue;
            }

            $fechaRes = Carbon::parse($res->fecha_reserva);
            if ($fechaRes->dayOfWeek < 1 || $fechaRes->dayOfWeek > 6) {
                continue;
            }
            $fechaResStr = $fechaRes->format('Y-m-d');
            $periodoFechaRes = $periodoPorFecha[$fechaResStr] ?? $this->resolverPeriodoParaFecha($fechaResStr, $periodosModels);
            if ($periodo && $periodoFechaRes !== $periodo) {
                continue;
            }
            if ($periodo && $res->asignatura && !empty($res->asignatura->periodo) && $res->asignatura->periodo !== $periodo) {
                continue;
            }

            $diaFecha = ModulosHelper::normalizarDia($dias[$fechaRes->dayOfWeek]);
            $prefijosDias = ['DO', 'LU', 'MA', 'MI', 'JU', 'VI', 'SA'];
            $prefijoDia = $prefijosDias[$fechaRes->dayOfWeek];

            $rangoModulos = $this->inferirModulosDeReserva($res, $diaFecha);
            $runProfNorm = $this->normalizeRun($res->run_profesor ?? $res->run_solicitante);
            $nombreProfesor = $res->profesor->name ?? ($res->run_profesor ?? $res->run_solicitante);
            $nombreAsig = $res->asignatura->nombre_asignatura ?? ($res->nombre_actividad ?: 'Reserva Académica / Uso de Sala');
            $codigoAsig = $res->asignatura->codigo_asignatura ?? 'RESERVA';

            $etiquetaTipo = match(strtolower($res->tipo_reserva ?? '')) {
                'espontanea' => 'Reserva espontánea registrada',
                'recuperacion' => 'Recuperación de clase registrada',
                'directa', 'recurrente' => 'Reserva de sala registrada',
                default => 'Registro efectivo de uso de sala',
            };
            if (!empty($res->observaciones) && !str_contains($res->observaciones, 'RESERVA CREADA MANUALMENTE')) {
                $etiquetaTipo .= ' | ' . $res->observaciones;
            }

            foreach ($rangoModulos as $numMod) {
                $horarioCanon = ModulosHelper::getHorarioModulo($diaFecha, (int) $numMod);
                $idModRaw = "{$prefijoDia}.{$numMod}";
                $claveCnrRes = $fechaResStr . "_{$res->id_espacio}_{$idModRaw}_{$runProfNorm}";
                $cnrRes = $this->clasesNoRealizadasCache[$claveCnrRes] ?? null;

                $estadoFinalRes = $estadoReservaStr;
                $motivoFinalRes = $esRecuperacion ? 'Recuperación de clase' : null;
                $obsFinalRes = $etiquetaTipo;
                $idFinalRes = null;

                if ($cnrRes) {
                    $idFinalRes = $cnrRes->id;
                    $clasesNoRealizadasUsadasIds[$cnrRes->id] = true;
                    $estadoFinalRes = match($cnrRes->estado) {
                        'no_realizada' => 'No Registrada',
                        'realizada', 'registrada' => 'Realizada',
                        'justificado'  => 'Justificada',
                        'recuperada'   => 'Recuperada',
                        'pendiente'    => 'Pendiente de Recuperación',
                        default        => $estadoReservaStr,
                    };
                    $motivoFinalRes = $cnrRes->motivo ?: $motivoFinalRes;
                    $obsFinalRes = $cnrRes->observaciones ?: $obsFinalRes;
                }

                $horaDeteccionRes = $cnrRes?->hora_deteccion
                    ?? ($res->hora ? Carbon::parse($fechaResStr . ' ' . $res->hora) : $fechaRes);

                $clasesData->push([
                    'id'                => $idFinalRes,
                    'fecha'             => clone $fechaRes,
                    'dia'               => ucfirst($diaFecha),
                    'periodo'           => $periodoFechaRes,
                    'profesor'          => $nombreProfesor,
                    'run_profesor'      => $runProfNorm,
                    'ua'                => $this->resolverUa($res->asignatura, $res->profesor),
                    'carrera'           => $this->resolverNombreUa($res->asignatura, $res->profesor),
                    'asignatura'        => $nombreAsig,
                    'codigo_asignatura' => $codigoAsig,
                    'id_asignatura'     => $res->id_asignatura,
                    'espacio'           => $res->id_espacio,
                    'modulo'            => (string) $numMod,
                    'id_modulo_raw'     => $idModRaw,
                    'hora_inicio'       => $horarioCanon['inicio'] ?? $res->hora,
                    'hora_fin'          => $horarioCanon['fin'] ?? ($res->hora_salida ?? $res->hora),
                    'estado'            => $estadoFinalRes,
                    'hora_entrada'      => $res->hora,
                    'hora_salida'       => $res->hora_salida,
                    'motivo'            => $motivoFinalRes,
                    'observaciones'     => $obsFinalRes,
                    'hora_deteccion'    => $horaDeteccionRes,
                    'rol_bloque'        => 'reserva_directa',
                ]);
            }
        }

        $this->clasesNoRealizadasCache = [];
        $this->reservasCache = [];
        $this->reservasPorFechaCache = [];
        $this->sesionesAsistenciaCache = [];

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

                // Determinar si parte de este bloque de titular es cubierto por un docente colaborador
                // (Ej: bloque de 4 módulos donde el responsable dicta los 2 primeros y el colaborador los 2 siguientes)
                $modulosDelTitularExigibles = $bloqueItems->filter(function ($bItem) use ($modulosColaboradorPorAsig) {
                    if (($bItem['rol_bloque'] ?? '') !== 'titular' || empty($bItem['id_asignatura'])) {
                        return true;
                    }
                    $claveAsigMod = $bItem['id_asignatura'] . '_' . ($bItem['id_modulo_raw'] ?? '');
                    if (!isset($modulosColaboradorPorAsig[$claveAsigMod])) {
                        return true;
                    }
                    $fStr = $bItem['fecha']->format('Y-m-d');
                    foreach ($modulosColaboradorPorAsig[$claveAsigMod] as $runColab => $infoColab) {
                        if ($runColab !== $bItem['run_profesor']) {
                            $ini = $infoColab['fecha_inicio'] ? Carbon::parse($infoColab['fecha_inicio'])->format('Y-m-d') : null;
                            $fin = $infoColab['fecha_fin'] ? Carbon::parse($infoColab['fecha_fin'])->format('Y-m-d') : null;
                            if ((!$ini || $fStr >= $ini) && (!$fin || $fStr <= $fin)) {
                                return false;
                            }
                        }
                    }
                    return true;
                });

                $tieneModulosCompartidosConColaborador = $modulosDelTitularExigibles->count() < $bloqueItems->count();

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
                    $totalModulosBloque = $tieneModulosCompartidosConColaborador && $modulosDelTitularExigibles->isNotEmpty()
                        ? $modulosDelTitularExigibles->count()
                        : count($bloqueItems);

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

                        // Si este módulo específico está delegado a un colaborador y el titular ya completó sus módulos previos, no exigir permanencia al titular
                        $claveAsigMod = ($item['id_asignatura'] ?? '') . '_' . ($item['id_modulo_raw'] ?? '');
                        $moduloCubiertoPorColaborador = ($item['rol_bloque'] ?? '') === 'titular'
                            && !empty($item['id_asignatura'])
                            && isset($modulosColaboradorPorAsig[$claveAsigMod]);

                        if ($item['estado'] === 'Realizada' && !empty($item['hora_entrada']) && !$moduloCubiertoPorColaborador) {
                            $toleranciaMinutos = 10 * ($posicionN - 1);

                            $horarioCanon    = ModulosHelper::getHorarioModulo($item['dia'], (int) $item['modulo']);
                            $horaFinCanonica = $horarioCanon ? $horarioCanon['fin'] : $item['hora_fin'];
                            $horaMinimaSalida = Carbon::parse($horaFinCanonica)->subMinutes($toleranciaMinutos);

                            if ($horaSalidaReal->lt($horaMinimaSalida)) {
                                $horaPrimerModulo = Carbon::parse($bloqueItems->first()['hora_inicio'] ?? $horaSalidaBloque);
                                $minutosDesdeInicio = $horaPrimerModulo->diffInMinutes($horaSalidaReal);

                                if ($minutosDesdeInicio > 40) {
                                    $minutosAntes = $horaSalidaReal->diffInMinutes($horaMinimaSalida);
                                    if ($minutosAntes >= 5 && $modulosCompletados < $totalModulosBloque) {
                                        $item['estado'] = 'Realizada';
                                        $obsRetiro = "Retiro anticipado: Se retiró {$minutosAntes} min antes del mínimo (completó {$modulosCompletados} de {$totalModulosBloque} módulos)";
                                        $item['observaciones'] = !empty($item['observaciones']) 
                                            ? $item['observaciones'] . " | " . $obsRetiro 
                                            : $obsRetiro;
                                    }
                                }
                            }
                        } elseif ($moduloCubiertoPorColaborador && $horaSalidaReal->lt(Carbon::parse($item['hora_fin'])->subMinutes(10))) {
                            $obsComp = 'Módulo compartido/continuado por docente colaborador';
                            $item['observaciones'] = !empty($item['observaciones'])
                                ? $item['observaciones'] . ' | ' . $obsComp
                                : $obsComp;
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

        // Mostrar solo docentes que imparten clases en el período consultado (titulares con horario del período o colaboradores del período)
        if ($periodo) {
            $runsDocentesPeriodo = \App\Helpers\SemesterHelper::getRunsDocentesDelPeriodo($periodo, $periodosModels);
            if ($runsDocentesPeriodo !== null) {
                $totalAntes = $clasesData->count();
                $clasesData = $clasesData->filter(function ($item) use ($runsDocentesPeriodo) {
                    return isset($runsDocentesPeriodo[$this->normalizeRun($item['run_profesor'] ?? '')]);
                })->values();
                if ($clasesData->count() !== $totalAntes) {
                    \Illuminate\Support\Facades\Log::debug('[TodasClasesService] Clases de docentes fuera del período descartadas', [
                        'periodo' => $periodo,
                        'descartadas' => $totalAntes - $clasesData->count(),
                    ]);
                }
            }
        }

        if ($ua) {
            $uaStr = trim((string)$ua);
            $uaNorm = mb_strtolower($uaStr, 'UTF-8');

            \Illuminate\Support\Facades\Log::debug('[TodasClasesService] Filtrando por UA', [
                'ua_buscada' => $uaStr,
                'total_antes_filtro' => $clasesData->count(),
                'valores_ua_unicos' => $clasesData->pluck('ua')->unique()->values()->toArray(),
            ]);

            $clasesData = $clasesData->filter(function($item) use ($uaStr, $uaNorm) {
                $itemUa = trim((string)($item['ua'] ?? ''));
                if ($itemUa === $uaStr) {
                    return true;
                }
                $carreraNombre = mb_strtolower(trim((string)($item['carrera'] ?? '')), 'UTF-8');
                return str_contains($carreraNombre, $uaNorm);
            });

            \Illuminate\Support\Facades\Log::debug('[TodasClasesService] Resultado filtro UA', [
                'total_despues_filtro' => $clasesData->count(),
            ]);
        }

        return $clasesData->sortBy([
            ['fecha', 'desc'],
            ['espacio', 'asc'],
            ['modulo', 'asc'],
        ])->values();
    }

    /**
     * Busca la reserva que corresponde a una clase y módulo específico.
     * Soporta:
     *  1. Separación estricta de bloques en el mismo día (ej. 2 laboratorios en distintos horarios).
     *  2. Co-docencia (titular y colaborador compartiendo asignatura).
     *  3. Salas dobles/paralelas (ej. salas 215 y 212) o cambio de laboratorio/espacio para la misma clase.
     */
    private function resolverReservaParaClase(
        string $fechaStr,
        string $idEspacioPlanificado,
        $idAsignatura,
        string $runProfesorPrincipal,
        array $runsAceptados,
        Carbon $horaInicioModulo,
        Carbon $horaFinModulo,
        int $minutosMargenIngreso,
        int $numModulo
    ): ?array {
        $claveReservaExacta = "{$fechaStr}_{$idEspacioPlanificado}";

        // 1. Buscar en el espacio planificado exacto para el docente principal
        if (isset($this->reservasCache[$claveReservaExacta])) {
            $candidatasDocente = collect($this->reservasCache[$claveReservaExacta])->filter(function ($r) use ($runProfesorPrincipal) {
                return $this->normalizeRun($r->run_profesor) === $runProfesorPrincipal
                    || $this->normalizeRun($r->run_solicitante) === $runProfesorPrincipal;
            });

            $match = $this->seleccionarReservaEnVentana($candidatasDocente, $idAsignatura, $horaInicioModulo, $horaFinModulo, $minutosMargenIngreso, $numModulo);
            if ($match) {
                return [
                    'reserva' => $match['reserva'],
                    'ingreso_directo' => $match['ingreso_directo'],
                    'nota' => null,
                ];
            }

            // 2. Buscar en el espacio planificado exacto para un co-docente (responsable/colaborador) de la misma asignatura
            if (count($runsAceptados) > 1) {
                $candidatasCoDocente = collect($this->reservasCache[$claveReservaExacta])->filter(function ($r) use ($runProfesorPrincipal, $runsAceptados, $idAsignatura) {
                    $rProf = $this->normalizeRun($r->run_profesor);
                    $rSol = $this->normalizeRun($r->run_solicitante);
                    $runMatch = isset($runsAceptados[$rProf]) ? $rProf : (isset($runsAceptados[$rSol]) ? $rSol : null);
                    if (!$runMatch || $runMatch === $runProfesorPrincipal) {
                        return false;
                    }
                    return !$idAsignatura || empty($r->id_asignatura) || $r->id_asignatura == $idAsignatura;
                });

                $matchCo = $this->seleccionarReservaEnVentana($candidatasCoDocente, $idAsignatura, $horaInicioModulo, $horaFinModulo, $minutosMargenIngreso, $numModulo);
                if ($matchCo) {
                    $rCo = $this->normalizeRun($matchCo['reserva']->run_profesor ?: $matchCo['reserva']->run_solicitante);
                    $nombreCo = $runsAceptados[$rCo] ?? $rCo;
                    return [
                        'reserva' => $matchCo['reserva'],
                        'ingreso_directo' => $matchCo['ingreso_directo'],
                        'nota' => "Acceso registrado por co-docente ({$nombreCo})",
                    ];
                }
            }
        }

        // 3. Buscar en espacios alternativos o salas paralelas (ej. salas 215/212 o cambio de laboratorio) en la misma fecha y módulo
        if (isset($this->reservasPorFechaCache[$fechaStr])) {
            $candidatasOtroEspacio = collect($this->reservasPorFechaCache[$fechaStr])->filter(function ($r) use ($idEspacioPlanificado, $runProfesorPrincipal, $runsAceptados, $idAsignatura) {
                if ($r->id_espacio === $idEspacioPlanificado) {
                    return false;
                }
                $rProf = $this->normalizeRun($r->run_profesor);
                $rSol = $this->normalizeRun($r->run_solicitante);

                // Mismo docente en otra sala (sala paralela o cambio de sala)
                if ($rProf === $runProfesorPrincipal || $rSol === $runProfesorPrincipal) {
                    return !$idAsignatura || empty($r->id_asignatura) || $r->id_asignatura == $idAsignatura;
                }

                // Co-docente con la misma asignatura explícita en otra sala paralela
                if ($idAsignatura && $r->id_asignatura == $idAsignatura) {
                    return isset($runsAceptados[$rProf]) || isset($runsAceptados[$rSol]);
                }

                return false;
            });

            $matchAlt = $this->seleccionarReservaEnVentana($candidatasOtroEspacio, $idAsignatura, $horaInicioModulo, $horaFinModulo, $minutosMargenIngreso, $numModulo);
            if ($matchAlt) {
                $espacioUsado = $matchAlt['reserva']->id_espacio;
                return [
                    'reserva' => $matchAlt['reserva'],
                    'ingreso_directo' => $matchAlt['ingreso_directo'],
                    'nota' => "Realizada en espacio {$espacioUsado}",
                ];
            }
        }

        return null;
    }

    /**
     * Evalúa una colección de reservas candidatas verificando que pertenezcan realmente
     * a la ventana horaria del módulo evaluado (evita que un laboratorio de la mañana
     * contamine otro laboratorio de la misma asignatura en la tarde).
     */
    private function seleccionarReservaEnVentana(
        Collection $candidatas,
        $idAsignatura,
        Carbon $horaInicioModulo,
        Carbon $horaFinModulo,
        int $minutosMargenIngreso,
        int $numModulo
    ): ?array {
        if ($candidatas->isEmpty()) {
            return null;
        }

        $margenInicio = $horaInicioModulo->copy()->subMinutes($minutosMargenIngreso);
        $mejores = [];

        foreach ($candidatas as $r) {
            $horaAcceso = Carbon::parse($r->hora);
            $horaSalidaTemp = $r->hora_salida ? Carbon::parse($r->hora_salida) : null;

            // Si tiene modulo_inicio y modulo_fin definidos y no solapan en absoluto con este módulo ni con la hora, descartar
            $cubrePorModulo = !empty($r->modulo_inicio) && !empty($r->modulo_fin)
                && (int) $r->modulo_inicio <= $numModulo
                && (int) $r->modulo_fin >= $numModulo;

            $ingresoDirecto = ($horaAcceso >= $margenInicio && $horaAcceso <= $horaFinModulo);

            // Ingreso previo: entró antes del margen, pero su salida real o estimada cubre el módulo actual
            $finEfectivo = $horaSalidaTemp;
            if (!$finEfectivo || $finEfectivo->lte($horaAcceso)) {
                $cantModulos = max(1, (int) ($r->modulos ?? 1));
                $finEfectivo = $horaAcceso->copy()->addMinutes($cantModulos * 55);
            }

            $ingresoPrevio = ($horaAcceso < $margenInicio) && ($finEfectivo > $horaInicioModulo);

            if ($ingresoDirecto || $ingresoPrevio || $cubrePorModulo) {
                $mismaAsignatura = ($idAsignatura && $r->id_asignatura == $idAsignatura);
                $distanciaMinutos = abs($horaAcceso->diffInMinutes($horaInicioModulo, false));

                $mejores[] = [
                    'reserva' => $r,
                    'ingreso_directo' => $ingresoDirecto,
                    'prioridad' => ($mismaAsignatura ? 0 : 1000) + ($ingresoDirecto ? 0 : 200) + $distanciaMinutos,
                ];
            }
        }

        if (empty($mejores)) {
            return null;
        }

        usort($mejores, fn($a, $b) => $a['prioridad'] <=> $b['prioridad']);
        return $mejores[0];
    }

    /**
     * Infiere la lista de números de módulo cubiertos por una reserva no planificada.
     */
    private function inferirModulosDeReserva(Reserva $res, string $diaNormalizado): array
    {
        if (!empty($res->modulo_inicio) && !empty($res->modulo_fin) && (int) $res->modulo_fin >= (int) $res->modulo_inicio) {
            return range((int) $res->modulo_inicio, (int) $res->modulo_fin);
        }

        $horariosDia = ModulosHelper::getHorariosModulos()[$diaNormalizado] ?? [];
        if (empty($horariosDia) || empty($res->hora)) {
            return [1];
        }

        $horaEntrada = Carbon::parse($res->hora);
        $horaSalida = $res->hora_salida ? Carbon::parse($res->hora_salida) : null;
        $modulosDetectados = [];

        foreach ($horariosDia as $numMod => $h) {
            $ini = Carbon::parse($h['inicio']);
            $fin = Carbon::parse($h['fin']);
            $margen = $ini->copy()->subMinutes(15);

            if ($horaEntrada >= $margen && $horaEntrada <= $fin) {
                $modulosDetectados[] = (int) $numMod;
            } elseif ($horaSalida && $horaEntrada < $ini && $horaSalida > $ini->copy()->addMinutes(10)) {
                $modulosDetectados[] = (int) $numMod;
            }
        }

        if (empty($modulosDetectados)) {
            // Buscar el módulo más cercano a la hora de entrada
            $mejorMod = 1;
            $menorDiff = 9999;
            foreach ($horariosDia as $numMod => $h) {
                $diff = abs($horaEntrada->diffInMinutes(Carbon::parse($h['inicio']), false));
                if ($diff < $menorDiff) {
                    $menorDiff = $diff;
                    $mejorMod = (int) $numMod;
                }
            }
            $cant = max(1, (int) ($res->modulos ?? 1));
            return range($mejorMod, min(15, $mejorMod + $cant - 1));
        }

        $cant = max(1, (int) ($res->modulos ?? 1));
        if (count($modulosDetectados) < $cant) {
            $primero = min($modulosDetectados);
            return range($primero, min(15, $primero + $cant - 1));
        }

        return array_values(array_unique($modulosDetectados));
    }

    private function aplicarFiltroBusquedaClaseNoRealizada($query, string $search): void
    {
        $palabras = array_values(array_filter(preg_split('/\s+/', trim($search))));
        $searchTerm = '%' . trim($search) . '%';
        $cleanRun = preg_replace('/[^0-9kK]/', '', $search);

        $query->where(function($q) use ($searchTerm, $palabras, $cleanRun) {
            $q->whereHas('asignatura', function($aq) use ($searchTerm) {
                $aq->where('nombre_asignatura', 'like', $searchTerm)
                   ->orWhere('codigo_asignatura', 'like', $searchTerm);
            })
            ->orWhere('id_espacio', 'like', $searchTerm);

            if (!empty($cleanRun) && strlen($cleanRun) >= 3) {
                $q->orWhere('run_profesor', 'like', '%' . $cleanRun . '%')
                  ->orWhereRaw("REPLACE(REPLACE(run_profesor, '.', ''), '-', '') LIKE ?", ['%' . $cleanRun . '%']);
            }

            if (!empty($palabras)) {
                $q->orWhereHas('profesor', function($pq) use ($palabras) {
                    $pq->where(function($nameQ) use ($palabras) {
                        foreach ($palabras as $p) {
                            $nameQ->where('name', 'like', '%' . $p . '%');
                        }
                    });
                });
            }
        });
    }

    private function reservaCoincideConBusqueda(Reserva $res, string $search): bool
    {
        $term = mb_strtolower(trim($search), 'UTF-8');
        $cleanRun = preg_replace('/[^0-9kK]/', '', $search);
        $palabras = array_values(array_filter(preg_split('/\s+/', $term)));

        if (str_contains(mb_strtolower((string) $res->id_espacio, 'UTF-8'), $term)) {
            return true;
        }
        if ($res->asignatura) {
            if (str_contains(mb_strtolower((string) $res->asignatura->nombre_asignatura, 'UTF-8'), $term)
                || str_contains(mb_strtolower((string) $res->asignatura->codigo_asignatura, 'UTF-8'), $term)) {
                return true;
            }
        }
        if (!empty($res->nombre_actividad) && str_contains(mb_strtolower((string) $res->nombre_actividad, 'UTF-8'), $term)) {
            return true;
        }
        if (!empty($cleanRun) && strlen($cleanRun) >= 3) {
            $runRes = $this->normalizeRun($res->run_profesor ?: $res->run_solicitante);
            if (str_contains($runRes, $cleanRun)) {
                return true;
            }
        }
        if ($res->profesor && !empty($palabras)) {
            $nombreProf = mb_strtolower((string) $res->profesor->name, 'UTF-8');
            $todasCoinciden = true;
            foreach ($palabras as $p) {
                if (!str_contains($nombreProf, $p)) {
                    $todasCoinciden = false;
                    break;
                }
            }
            if ($todasCoinciden) {
                return true;
            }
        }

        return false;
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

    /**
     * Resolver la Unidad Académica (UA -> id_carrera)
     */
    private function resolverUa($asignatura, $profesor = null): string
    {
        if ($asignatura && !empty($asignatura->id_carrera)) {
            return (string) $asignatura->id_carrera;
        }
        if ($profesor && !empty($profesor->id_carrera)) {
            return (string) $profesor->id_carrera;
        }
        if ($asignatura && $asignatura->carrera && !empty($asignatura->carrera->id_carrera)) {
            return (string) $asignatura->carrera->id_carrera;
        }
        if ($profesor && $profesor->carrera && !empty($profesor->carrera->id_carrera)) {
            return (string) $profesor->carrera->id_carrera;
        }
        return 'N/A';
    }

    /**
     * Resolver el nombre de la Unidad Académica / Carrera
     */
    private function resolverNombreUa($asignatura, $profesor = null): string
    {
        if ($asignatura && $asignatura->carrera && !empty($asignatura->carrera->nombre)) {
            return (string) $asignatura->carrera->nombre;
        }
        if ($profesor && $profesor->carrera && !empty($profesor->carrera->nombre)) {
            return (string) $profesor->carrera->nombre;
        }
        if ($asignatura && $asignatura->carrera && $asignatura->carrera->areaAcademica && !empty($asignatura->carrera->areaAcademica->nombre_area_academica)) {
            return (string) $asignatura->carrera->areaAcademica->nombre_area_academica;
        }
        if ($profesor && $profesor->areaAcademica && !empty($profesor->areaAcademica->nombre_area_academica)) {
            return (string) $profesor->areaAcademica->nombre_area_academica;
        }
        return '';
    }

    /**
     * Determina a qué período académico ('YYYY-1' o 'YYYY-2') pertenece una fecha ('Y-m-d').
     */
    private function resolverPeriodoParaFecha(string $fechaYmd, $periodosModels = null): string
    {
        $anio = (int) substr($fechaYmd, 0, 4);
        $lim1 = \App\Helpers\SemesterHelper::getLimitesPeriodo($anio . '-1', $periodosModels);
        $lim2 = \App\Helpers\SemesterHelper::getLimitesPeriodo($anio . '-2', $periodosModels);

        if ($fechaYmd >= $lim2['inicio']->format('Y-m-d')) {
            return $anio . '-2';
        }
        if ($fechaYmd <= $lim1['fin']->format('Y-m-d')) {
            return $anio . '-1';
        }

        return substr($fechaYmd, 5, 5) >= '07-21' ? ($anio . '-2') : ($anio . '-1');
    }

    /**
     * Determina el semestre único en el que aplica una planificación de profesor colaborador,
     * evitando que colaboradores del 1° semestre se proyecten en el 2° semestre o viceversa.
     */
    private function resolverPeriodosColaborador($planificacion, $colab, array $periodosPorAsignatura, $periodosModels = null): array
    {
        if ($colab->asignatura && !empty($colab->asignatura->periodo)) {
            return [(string) $colab->asignatura->periodo => true];
        }

        if (!empty($colab->id_asignatura) && !empty($periodosPorAsignatura[$colab->id_asignatura])) {
            if (count($periodosPorAsignatura[$colab->id_asignatura]) === 1) {
                return $periodosPorAsignatura[$colab->id_asignatura];
            }
        }

        if (!empty($planificacion->fecha_fin_vigencia)) {
            $finPlanYmd = Carbon::parse($planificacion->fecha_fin_vigencia)->format('Y-m-d');
            $anioFin = (int) substr($finPlanYmd, 0, 4);
            $lim2 = \App\Helpers\SemesterHelper::getLimitesPeriodo($anioFin . '-2', $periodosModels);
            if ($finPlanYmd < $lim2['fin']->format('Y-m-d')) {
                return [$anioFin . '-1' => true];
            }
            $pFinPlan = $this->resolverPeriodoParaFecha($finPlanYmd, $periodosModels);
            return [$pFinPlan => true];
        }

        if (!empty($planificacion->fecha_inicio_vigencia)) {
            $pIniPlan = $this->resolverPeriodoParaFecha(Carbon::parse($planificacion->fecha_inicio_vigencia)->format('Y-m-d'), $periodosModels);
            return [$pIniPlan => true];
        }

        $iniColab = $colab->fecha_inicio ? Carbon::parse($colab->fecha_inicio)->format('Y-m-d') : null;
        $finColab = $colab->fecha_termino ? Carbon::parse($colab->fecha_termino)->format('Y-m-d') : null;
        $esRangoAnualGenerico = $iniColab && $finColab
            && str_ends_with($iniColab, '-01-01')
            && str_ends_with($finColab, '-12-31');

        if (!$esRangoAnualGenerico && ($iniColab || $finColab)) {
            $pIni = $iniColab ? $this->resolverPeriodoParaFecha($iniColab, $periodosModels) : null;
            $pFin = $finColab ? $this->resolverPeriodoParaFecha($finColab, $periodosModels) : null;
            if ($pIni && $pFin && $pIni === $pFin) {
                return [$pIni => true];
            }
            if ($pFin) {
                return [$pFin => true];
            }
            if ($pIni) {
                return [$pIni => true];
            }
        }

        if ($colab->asignatura && !empty($colab->asignatura->periodo)) {
            return [(string) $colab->asignatura->periodo => true];
        }

        if (!empty($colab->id_asignatura) && !empty($periodosPorAsignatura[$colab->id_asignatura])) {
            if (count($periodosPorAsignatura[$colab->id_asignatura]) === 1) {
                return $periodosPorAsignatura[$colab->id_asignatura];
            }
        }

        $fechaRef = $planificacion->updated_at ?? $planificacion->created_at ?? $colab->updated_at ?? $colab->created_at;
        if ($fechaRef) {
            $pRef = $this->resolverPeriodoParaFecha(Carbon::parse($fechaRef)->format('Y-m-d'), $periodosModels);
            return [$pRef => true];
        }

        if (!empty($colab->id_asignatura) && !empty($periodosPorAsignatura[$colab->id_asignatura])) {
            $ultimoPeriodo = (string) array_key_last($periodosPorAsignatura[$colab->id_asignatura]);
            return [$ultimoPeriodo => true];
        }

        return [];
    }
}
