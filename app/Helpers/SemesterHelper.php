<?php

namespace App\Helpers;

use App\Models\PeriodoAcademico;
use Carbon\Carbon;

class SemesterHelper
{
    protected static array $reconciledTenants = [];

    /**
     * Obtiene el semestre actual basado en la fecha
     * Primero intenta usar los períodos académicos de la BD,
     * si no hay, usa la lógica por defecto (21 de julio)
     * 
     * @param Carbon|null $date
     * @return int
     */
    public static function getCurrentSemester($date = null)
    {
        $date = $date ?: Carbon::now();
        
        // Intentar obtener el período académico de la BD
        try {
            $periodo = PeriodoAcademico::obtenerPeriodoActual($date);
            if ($periodo) {
                return $periodo->semestre;
            }
        } catch (\Throwable $e) {
            // Continuar con lógica de calendario
        }

        // Fallback: lógica por defecto
        $month = $date->month;
        $day = $date->day;

        // Segundo semestre comienza el 21 de julio
        if ($month >= 7 && $day >= 21) {
            return 2;
        } elseif ($month >= 8) {
            return 2;
        } else {
            return 1;
        }
    }

    /**
     * Obtiene el año académico actual
     * 
     * @param Carbon|null $date
     * @return int
     */
    public static function getCurrentAcademicYear($date = null)
    {
        $date = $date ?: Carbon::now();
        
        // Intentar obtener el período académico de la BD
        try {
            $periodo = PeriodoAcademico::obtenerPeriodoActual($date);
            if ($periodo) {
                return $periodo->anio;
            }
        } catch (\Throwable $e) {
            // Continuar con año por calendario
        }

        // Fallback: lógica por defecto
        return $date->year;
    }

    /**
     * Obtiene el período actual en formato 'año-semestre'
     * 
     * @param Carbon|null $date
     * @return string
     */
    public static function getCurrentPeriod($date = null)
    {
        $year = self::getCurrentAcademicYear($date);
        $semester = self::getCurrentSemester($date);
        $periodo = $year . '-' . $semester;

        if ($date === null) {
            self::asegurarSeparacionSemestres($periodo);
        }

        return $periodo;
    }

    /**
     * Verifica si una fecha está en el segundo semestre
     * 
     * @param Carbon|null $date
     * @return bool
     */
    public static function isSecondSemester($date = null)
    {
        return self::getCurrentSemester($date) === 2;
    }

    /**
     * Obtiene la fecha de inicio del segundo semestre para un año específico
     * 
     * @param int $year
     * @return Carbon
     */
    public static function getSecondSemesterStartDate($year)
    {
        // Intentar obtener de la BD
        $periodo = PeriodoAcademico::where('anio', $year)
            ->where('semestre', 2)
            ->where('activo', true)
            ->first();
        
        if ($periodo) {
            return $periodo->fecha_inicio;
        }
        
        return Carbon::create($year, 7, 21);
    }

    /**
     * Obtiene la fecha de fin del segundo semestre para un año específico
     * 
     * @param int $year
     * @return Carbon
     */
    public static function getSecondSemesterEndDate($year)
    {
        // Intentar obtener de la BD
        $periodo = PeriodoAcademico::where('anio', $year)
            ->where('semestre', 2)
            ->where('activo', true)
            ->first();
        
        if ($periodo) {
            return $periodo->fecha_fin;
        }
        
        return Carbon::create($year + 1, 2, 28); // Fin de febrero del año siguiente
    }

    /**
     * Verifica si hay un período académico activo para la fecha actual
     * 
     * @param Carbon|null $date
     * @return bool
     */
    public static function hayPeriodoActivo($date = null)
    {
        return PeriodoAcademico::estaEnPeriodoActivo($date);
    }

    /**
     * Obtiene el período académico actual
     * 
     * @param Carbon|null $date
     * @return PeriodoAcademico|null
     */
    public static function getPeriodoActual($date = null)
    {
        return PeriodoAcademico::obtenerPeriodoActual($date);
    }

    /**
     * Verifica si el período del horario es válido (no ha terminado)
     * 
     * @param string $periodoHorario Formato: "año-semestre" ej: "2025-2"
     * @return bool
     */
    public static function esPeriodoValido($periodoHorario)
    {
        if (!$periodoHorario) {
            return false;
        }

        $partes = explode('-', $periodoHorario);
        if (count($partes) !== 2) {
            return false;
        }

        $anio = (int) $partes[0];
        $semestre = (int) $partes[1];

        // Buscar el período en la BD
        $periodo = PeriodoAcademico::where('anio', $anio)
            ->where('semestre', $semestre)
            ->where('activo', true)
            ->first();

        if (!$periodo) {
            // Si no hay período configurado, verificar si es el período actual por defecto
            $periodoActual = self::getCurrentPeriod();
            return $periodoHorario === $periodoActual;
        }

        // El período es válido si no ha finalizado
        return !$periodo->haFinalizado();
    }

    /**
     * Verifica si se necesita nueva programación (el período anterior terminó)
     * 
     * @return bool
     */
    public static function necesitaNuevaProgramacion()
    {
        $periodoActual = PeriodoAcademico::obtenerPeriodoActual();
        
        // Si no hay período activo, se necesita configurar uno
        if (!$periodoActual) {
            return true;
        }

        return false;
    }

    /**
     * Obtiene información sobre el estado del período para mostrar al usuario
     * 
     * @return array
     */
    public static function getEstadoPeriodo()
    {
        $periodoActual = PeriodoAcademico::obtenerPeriodoActual();
        $hoy = Carbon::now();

        if (!$periodoActual) {
            // Buscar el último período que terminó
            $ultimoPeriodo = PeriodoAcademico::where('activo', true)
                ->where('fecha_fin', '<', $hoy)
                ->orderBy('fecha_fin', 'desc')
                ->first();

            if ($ultimoPeriodo) {
                return [
                    'activo' => false,
                    'mensaje' => "El {$ultimoPeriodo->nombre_completo} finalizó el {$ultimoPeriodo->fecha_fin->format('d/m/Y')}. Se requiere configurar un nuevo período académico.",
                    'requiere_accion' => true,
                    'ultimo_periodo' => $ultimoPeriodo,
                ];
            }

            return [
                'activo' => false,
                'mensaje' => 'No hay períodos académicos configurados. Configure uno para habilitar la planificación.',
                'requiere_accion' => true,
                'ultimo_periodo' => null,
            ];
        }

        // Verificar si estamos cerca del fin del período
        $diasRestantes = $hoy->diffInDays($periodoActual->fecha_fin, false);

        if ($diasRestantes <= 14 && $diasRestantes > 0) {
            return [
                'activo' => true,
                'mensaje' => "El {$periodoActual->nombre_completo} finaliza en {$diasRestantes} días ({$periodoActual->fecha_fin->format('d/m/Y')}).",
                'requiere_accion' => false,
                'alerta' => true,
                'periodo_actual' => $periodoActual,
            ];
        }

        return [
            'activo' => true,
            'mensaje' => "Período activo: {$periodoActual->nombre_completo}",
            'requiere_accion' => false,
            'alerta' => false,
            'periodo_actual' => $periodoActual,
        ];
    }

    /**
     * Obtiene las fechas de inicio y término exactas para un código de período ('YYYY-1' o 'YYYY-2'),
     * garantizando que el 1° y 2° semestre nunca se solapen ni invadan el otro semestre.
     *
     * @param string $periodoCodigo Ej: '2026-1', '2026-2'
     * @param \Illuminate\Support\Collection|null $periodosModels Colección pre-cargada opcional de PeriodoAcademico
     * @return array{inicio: Carbon, fin: Carbon, model: PeriodoAcademico|null}
     */
    public static function getLimitesPeriodo(string $periodoCodigo, $periodosModels = null): array
    {
        $partes = explode('-', trim($periodoCodigo));
        $anio = (int)($partes[0] ?? Carbon::now()->year);
        $semestre = (int)($partes[1] ?? 1);

        try {
            if ($periodosModels === null) {
                $periodosAnio = PeriodoAcademico::where('anio', $anio)->get();
            } else {
                $periodosAnio = $periodosModels->where('anio', $anio);
            }
        } catch (\Throwable $e) {
            $periodosAnio = collect();
        }

        $model = $periodosAnio->first(fn($p) => (int)$p->semestre === $semestre);
        $otroModel = $periodosAnio->first(fn($p) => (int)$p->semestre === ($semestre === 1 ? 2 : 1));

        if ($semestre === 1) {
            $inicio = $model && $model->fecha_inicio
                ? Carbon::parse($model->fecha_inicio)->startOfDay()
                : Carbon::create($anio, 3, 1)->startOfDay();

            $fin = $model && $model->fecha_fin
                ? Carbon::parse($model->fecha_fin)->endOfDay()
                : Carbon::create($anio, 7, 20)->endOfDay();

            // Garantizar que el 1° semestre nunca invada el 2° semestre
            $limiteMaxSem1 = ($otroModel && $otroModel->fecha_inicio)
                ? Carbon::parse($otroModel->fecha_inicio)->subDay()->endOfDay()
                : Carbon::create($anio, 7, 31)->endOfDay();

            if ($fin->gt($limiteMaxSem1)) {
                $fin = $limiteMaxSem1->copy();
            }
        } else {
            $limiteMinSem2 = ($otroModel && $otroModel->fecha_fin && Carbon::parse($otroModel->fecha_fin)->month <= 8)
                ? Carbon::parse($otroModel->fecha_fin)->addDay()->startOfDay()
                : Carbon::create($anio, 7, 21)->startOfDay();

            $inicio = $model && $model->fecha_inicio
                ? Carbon::parse($model->fecha_inicio)->startOfDay()
                : $limiteMinSem2->copy();

            if ($inicio->lt(Carbon::create($anio, 7, 1)->startOfDay())) {
                $inicio = $limiteMinSem2->copy();
            }

            $fin = $model && $model->fecha_fin
                ? Carbon::parse($model->fecha_fin)->endOfDay()
                : Carbon::create($anio, 12, 31)->endOfDay();
        }

        return [
            'inicio' => $inicio,
            'fin' => $fin,
            'model' => $model,
        ];
    }

    /**
     * Obtiene los períodos disponibles para selección:
     * El semestre actual y los semestres efectivamente cargados con datos en el sistema.
     *
     * @return \Illuminate\Support\Collection
     */
    public static function getPeriodosDisponibles()
    {
        $periodoActualStr = self::getCurrentPeriod();
        $anioActual = self::getCurrentAcademicYear();
        $semestreActual = self::getCurrentSemester();

        // Obtener períodos únicos cargados en horarios
        $periodosCargados = \App\Models\Horario::select('periodo')
            ->whereNotNull('periodo')
            ->where('periodo', '!=', '')
            ->distinct()
            ->pluck('periodo')
            ->toArray();

        $periodosBase = [$periodoActualStr];
        if ($semestreActual === 2) {
            $periodosBase[] = $anioActual . '-1';
        }

        try {
            $periodosModels = PeriodoAcademico::all();
            foreach ($periodosModels as $pm) {
                $periodosBase[] = $pm->anio . '-' . $pm->semestre;
            }
        } catch (\Throwable $e) {
            $periodosModels = collect();
        }

        // Combinar con el período actual y descartar duplicados o vacíos
        $codigosValidos = array_unique(array_filter(array_merge($periodosBase, $periodosCargados)));

        return collect($codigosValidos)->map(function($codigo) use ($periodosModels, $periodoActualStr) {
            $partes = explode('-', $codigo);
            $anio = (int)($partes[0] ?? 0);
            $semestre = (int)($partes[1] ?? 0);
            $limites = self::getLimitesPeriodo($codigo, $periodosModels);
            $model = $limites['model'];
            $esActual = ($codigo === $periodoActualStr);

            return (object)[
                'codigo' => $codigo,
                'anio' => $anio,
                'semestre' => $semestre,
                'es_actual' => $esActual,
                'display_label' => $semestre . '° Semestre ' . $anio . ($esActual ? ' (Semestre actual)' : ''),
                'fecha_inicio' => $limites['inicio'],
                'fecha_fin' => $limites['fin'],
                'model' => $model,
            ];
        })->sortByDesc(function($p) {
            return ($p->anio * 10) + $p->semestre;
        })->values();
    }

    /**
     * Determina a qué código de período ('YYYY-1' o 'YYYY-2') pertenece una fecha ('Y-m-d' o Carbon).
     */
    public static function resolverPeriodoParaFecha($fecha, $periodosModels = null): string
    {
        $fechaYmd = $fecha instanceof Carbon ? $fecha->format('Y-m-d') : substr((string) $fecha, 0, 10);
        $anio = (int) substr($fechaYmd, 0, 4);
        if ($anio < 2000) {
            return self::getCurrentPeriod();
        }

        $lim1 = self::getLimitesPeriodo($anio . '-1', $periodosModels);
        $lim2 = self::getLimitesPeriodo($anio . '-2', $periodosModels);

        if ($fechaYmd >= $lim2['inicio']->format('Y-m-d')) {
            return $anio . '-2';
        }
        if ($fechaYmd <= $lim1['fin']->format('Y-m-d')) {
            return $anio . '-1';
        }

        return substr($fechaYmd, 5, 5) >= '07-21' ? ($anio . '-2') : ($anio . '-1');
    }

    /**
     * Auto-saneamiento en tiempo de ejecución por tenant:
     * 1. Garantiza que exista un único PeriodoAcademico activo según la fecha actual.
     * 2. Cierra la fecha_fin_vigencia de las Planificacion_Asignatura de semestres anteriores ya finalizados
     *    y limpia en el semestre actual cualquier planificación obsoleta/reemplazada.
     * 3. Corrige los registros de ProfesorColaborador y PlanificacionProfesorColaborador para que los de 2026-1
     *    queden estrictamente en 2026-1 y no se proyecten en 2026-2.
     * 4. Elimina registros huérfanos en clases_no_realizadas y profesor_atrasos que provengan de asignaturas de 2026-1
     *    en fechas de 2026-2.
     */
    public static function asegurarSeparacionSemestres(?string $periodoActual = null, bool $forzar = false): void
    {
        try {
            $dbName = config('database.connections.tenant.database') ?? 'default';
            $periodoActualReal = self::getCurrentAcademicYear() . '-' . self::getCurrentSemester();
            $periodoActual = $periodoActual ?: $periodoActualReal;
            $memKey = "{$dbName}_{$periodoActualReal}";

            if (!$forzar && isset(static::$reconciledTenants[$memKey])) {
                return;
            }
            static::$reconciledTenants[$memKey] = true;

            $cacheKey = "sem_sep_healed_v4_{$memKey}";
            if (!$forzar) {
                try {
                    if (\Illuminate\Support\Facades\Cache::get($cacheKey)) {
                        return;
                    }
                } catch (\Throwable $e) {
                }
            }

            // 1. Sincronizar PeriodoAcademico en BD principal para que solo haya 1 activo
            PeriodoAcademico::sincronizarEstadosSegunFechas();

            if ($dbName === 'default' || empty($dbName)) {
                return;
            }

            $periodosModels = PeriodoAcademico::all();
            $hoyStr = Carbon::today()->toDateString();
            $limActual = self::getLimitesPeriodo($periodoActualReal, $periodosModels);
            $iniActualStr = $limActual['inicio']->toDateString();
            $finActualStr = $limActual['fin']->toDateString();

            // 2. Separar y limpiar planificacion_asignaturas entre semestres anteriores y el semestre actual
            if (\App\Models\Planificacion_Asignatura::tieneColumnaVigencia()) {
                // 2a. Para semestres anteriores (ej. 2026-1), acotar su vigencia estrictamente a su semestre
                $periodosEnHorarios = \Illuminate\Support\Facades\DB::connection('tenant')
                    ->table('horarios')
                    ->whereNotNull('periodo')
                    ->where('periodo', '!=', '')
                    ->where('periodo', '!=', $periodoActualReal)
                    ->distinct()
                    ->pluck('periodo');

                foreach ($periodosEnHorarios as $pHist) {
                    $limHist = self::getLimitesPeriodo($pHist, $periodosModels);
                    $finHistStr = $limHist['fin']->toDateString();
                    $iniHistStr = $limHist['inicio']->toDateString();

                    if ($finHistStr < $hoyStr || strcmp($pHist, $periodoActualReal) < 0) {
                        $horariosIds = \Illuminate\Support\Facades\DB::connection('tenant')
                            ->table('horarios')
                            ->where('periodo', $pHist)
                            ->pluck('id_horario');

                        if ($horariosIds->isNotEmpty()) {
                            \Illuminate\Support\Facades\DB::connection('tenant')
                                ->table('planificacion_asignaturas')
                                ->whereIn('id_horario', $horariosIds)
                                ->where(function ($q) use ($finHistStr) {
                                    $q->whereNull('fecha_fin_vigencia')
                                      ->orWhere('fecha_fin_vigencia', '>', $finHistStr);
                                })
                                ->update(['fecha_fin_vigencia' => $finHistStr]);

                            \Illuminate\Support\Facades\DB::connection('tenant')
                                ->table('planificacion_asignaturas')
                                ->whereIn('id_horario', $horariosIds)
                                ->where(function ($q) use ($iniHistStr, $finHistStr) {
                                    $q->whereNull('fecha_inicio_vigencia')
                                      ->orWhere('fecha_inicio_vigencia', '>', $finHistStr);
                                })
                                ->update(['fecha_inicio_vigencia' => $iniHistStr]);
                        }
                    }
                }

                // 2b. En el semestre activo actual (ej. 2026-2):
                // - Eliminar planificaciones cerradas/reemplazadas (fecha_fin_vigencia IS NOT NULL) que quedaron de cargas anteriores (como 2026-1 cargado en 2026-2)
                // - Eliminar planificaciones cuya asignatura tenga explícitamente otro período (ej. 2026-1 migrada desde HOR_{run})
                // - Asegurar que las planificaciones activas del semestre actual cubran desde el inicio del semestre ($iniActualStr)
                $horariosActualesIds = \Illuminate\Support\Facades\DB::connection('tenant')
                    ->table('horarios')
                    ->where('periodo', $periodoActualReal)
                    ->pluck('id_horario');

                if ($horariosActualesIds->isNotEmpty()) {
                    $tieneActivasEnActual = \Illuminate\Support\Facades\DB::connection('tenant')
                        ->table('planificacion_asignaturas')
                        ->whereIn('id_horario', $horariosActualesIds)
                        ->whereNull('fecha_fin_vigencia')
                        ->exists();

                    if ($tieneActivasEnActual) {
                        \Illuminate\Support\Facades\DB::connection('tenant')
                            ->table('planificacion_asignaturas')
                            ->whereIn('id_horario', $horariosActualesIds)
                            ->whereNotNull('fecha_fin_vigencia')
                            ->delete();
                    }

                    $asigsOtroPeriodoIds = \Illuminate\Support\Facades\DB::connection('tenant')
                        ->table('asignaturas')
                        ->whereNotNull('periodo')
                        ->where('periodo', '!=', '')
                        ->where('periodo', '!=', $periodoActualReal)
                        ->pluck('id_asignatura');

                    if ($asigsOtroPeriodoIds->isNotEmpty()) {
                        \Illuminate\Support\Facades\DB::connection('tenant')
                            ->table('planificacion_asignaturas')
                            ->whereIn('id_horario', $horariosActualesIds)
                            ->whereIn('id_asignatura', $asigsOtroPeriodoIds)
                            ->delete();
                    }

                    \Illuminate\Support\Facades\DB::connection('tenant')
                        ->table('planificacion_asignaturas')
                        ->whereIn('id_horario', $horariosActualesIds)
                        ->whereNull('fecha_fin_vigencia')
                        ->update(['fecha_inicio_vigencia' => $iniActualStr]);

                    // Eliminar horarios del período actual que quedaron vacíos sin planificaciones
                    $horariosConPlanif = \Illuminate\Support\Facades\DB::connection('tenant')
                        ->table('planificacion_asignaturas')
                        ->whereIn('id_horario', $horariosActualesIds)
                        ->distinct()
                        ->pluck('id_horario');

                    \Illuminate\Support\Facades\DB::connection('tenant')
                        ->table('horarios')
                        ->where('periodo', $periodoActualReal)
                        ->whereNotIn('id_horario', $horariosConPlanif)
                        ->delete();
                }
            }

            // 3. Reconciliar ProfesorColaborador y PlanificacionProfesorColaborador para que colaboradores de 2026-1 no aparezcan en 2026-2
            $todosColabs = \App\Models\ProfesorColaborador::with('asignatura')->get();

            if ($todosColabs->isNotEmpty()) {
                $asigPeriodos = [];
                $rowsAsig = \Illuminate\Support\Facades\DB::connection('tenant')
                    ->table('planificacion_asignaturas as pa')
                    ->join('horarios as h', 'pa.id_horario', '=', 'h.id_horario')
                    ->whereNotNull('pa.id_asignatura')
                    ->whereNotNull('h.periodo')
                    ->select('pa.id_asignatura', 'h.periodo', 'pa.fecha_fin_vigencia')
                    ->get();

                foreach ($rowsAsig as $r) {
                    if ($r->periodo === $periodoActualReal && $r->fecha_fin_vigencia !== null) {
                        continue;
                    }
                    $asigPeriodos[$r->id_asignatura][$r->periodo] = true;
                }

                foreach ($todosColabs as $colab) {
                    $anioColab = $colab->fecha_inicio ? Carbon::parse($colab->fecha_inicio)->year : self::getCurrentAcademicYear();
                    $codSem1 = $anioColab . '-1';
                    $codSem2 = $anioColab . '-2';
                    $lim1 = self::getLimitesPeriodo($codSem1, $periodosModels);
                    $lim2 = self::getLimitesPeriodo($codSem2, $periodosModels);
                    $lim1IniStr = $lim1['inicio']->toDateString();
                    $lim1FinStr = $lim1['fin']->toDateString();
                    $lim2IniStr = $lim2['inicio']->toDateString();
                    $lim2FinStr = $lim2['fin']->toDateString();

                    $planifsColab = \App\Models\PlanificacionProfesorColaborador::withoutGlobalScope('vigencia_activa')
                        ->where('id_profesor_colaborador', $colab->id)
                        ->get();

                    // Un bloque colaborador solo está activo en 2° semestre si su fecha_fin_vigencia es NULL (o >= fin del 2° semestre)
                    // Si tiene fecha_fin_vigencia en octubre (ej. 2026-10-15), fue cerrado al cargar el 2° semestre y pertenece al 1° semestre
                    $tienePlanActivaSinFin = $planifsColab->contains(function ($pc) use ($lim2FinStr) {
                        if (empty($pc->fecha_fin_vigencia)) {
                            return true;
                        }
                        $finStr = Carbon::parse($pc->fecha_fin_vigencia)->format('Y-m-d');
                        return $finStr >= $lim2FinStr;
                    });

                    $periodosAsig = ($colab->id_asignatura && isset($asigPeriodos[$colab->id_asignatura]))
                        ? array_keys($asigPeriodos[$colab->id_asignatura])
                        : [];

                    $periodoModeloAsig = $colab->asignatura->periodo ?? null;

                    $esDeSem1 = false;
                    if ($planifsColab->isNotEmpty() && !$tienePlanActivaSinFin) {
                        $esDeSem1 = true;
                    } elseif ($periodoModeloAsig === $codSem1 && !in_array($codSem2, $periodosAsig, true)) {
                        $esDeSem1 = true;
                    } elseif (count($periodosAsig) === 1 && $periodosAsig[0] === $codSem1) {
                        $esDeSem1 = true;
                    } elseif ($planifsColab->isEmpty() && $colab->fecha_termino && Carbon::parse($colab->fecha_termino)->format('Y-m-d') <= $lim1FinStr) {
                        $esDeSem1 = true;
                    }

                    if ($esDeSem1) {
                        $colab->update([
                            'fecha_inicio' => $lim1IniStr,
                            'fecha_termino' => $lim1FinStr,
                        ]);

                        if (\App\Models\PlanificacionProfesorColaborador::tieneColumnaVigencia()) {
                            \App\Models\PlanificacionProfesorColaborador::withoutGlobalScope('vigencia_activa')
                                ->where('id_profesor_colaborador', $colab->id)
                                ->update([
                                    'fecha_inicio_vigencia' => $lim1IniStr,
                                    'fecha_fin_vigencia' => $lim1FinStr,
                                ]);
                        }
                    } else {
                        // Pertenece al 2° semestre: limpiar bloques cerrados obsoletos del 1° semestre que hayan quedado unidos al mismo colaborador
                        if (\App\Models\PlanificacionProfesorColaborador::tieneColumnaVigencia()) {
                            if ($tienePlanActivaSinFin) {
                                \App\Models\PlanificacionProfesorColaborador::withoutGlobalScope('vigencia_activa')
                                    ->where('id_profesor_colaborador', $colab->id)
                                    ->whereNotNull('fecha_fin_vigencia')
                                    ->where('fecha_fin_vigencia', '<', $lim2FinStr)
                                    ->delete();
                            }

                            \App\Models\PlanificacionProfesorColaborador::withoutGlobalScope('vigencia_activa')
                                ->where('id_profesor_colaborador', $colab->id)
                                ->whereNull('fecha_fin_vigencia')
                                ->update([
                                    'fecha_inicio_vigencia' => $lim2IniStr,
                                ]);
                        }

                        $iniActualColab = $colab->fecha_inicio ? Carbon::parse($colab->fecha_inicio)->format('Y-m-d') : null;
                        $finActualColab = $colab->fecha_termino ? Carbon::parse($colab->fecha_termino)->format('Y-m-d') : null;
                        if (!$iniActualColab || $iniActualColab < $lim2IniStr || ($iniActualColab === $lim2IniStr && $finActualColab === $lim2FinStr)) {
                            $colab->update([
                                'fecha_inicio' => $lim2IniStr,
                                'fecha_termino' => $lim2FinStr,
                            ]);
                        }
                    }
                }
            }

            // 4. Limpiar registros huérfanos en clases_no_realizadas y profesor_atrasos dentro del semestre actual que provengan de asignaturas de 2026-1
            $asigsValidasActual = \Illuminate\Support\Facades\DB::connection('tenant')
                ->table('planificacion_asignaturas as pa')
                ->join('horarios as h', 'pa.id_horario', '=', 'h.id_horario')
                ->where('h.periodo', $periodoActualReal)
                ->whereNull('pa.fecha_fin_vigencia')
                ->whereNotNull('pa.id_asignatura')
                ->distinct()
                ->pluck('pa.id_asignatura')
                ->toArray();

            $asigsColabActual = \Illuminate\Support\Facades\DB::connection('tenant')
                ->table('profesores_colaboradores')
                ->where('fecha_termino', '>=', $iniActualStr)
                ->whereNotNull('id_asignatura')
                ->distinct()
                ->pluck('id_asignatura')
                ->toArray();

            $asigsValidasActual = array_values(array_unique(array_merge($asigsValidasActual, $asigsColabActual)));

            if (!empty($asigsValidasActual)) {
                \Illuminate\Support\Facades\DB::connection('tenant')
                    ->table('clases_no_realizadas')
                    ->where('fecha_clase', '>=', $iniActualStr)
                    ->where(function ($q) use ($periodoActualReal, $asigsValidasActual) {
                        $q->where(function ($sq) use ($periodoActualReal) {
                            $sq->whereNotNull('periodo')
                               ->where('periodo', '!=', '')
                               ->where('periodo', '!=', $periodoActualReal);
                        })->orWhere(function ($sq) use ($asigsValidasActual) {
                            $sq->whereNotNull('id_asignatura')
                               ->where('id_asignatura', '!=', 'RESERVA')
                               ->whereNotIn('id_asignatura', $asigsValidasActual);
                        });
                    })
                    ->delete();

                if (\Illuminate\Support\Facades\Schema::connection('tenant')->hasTable('profesor_atrasos')) {
                    \Illuminate\Support\Facades\DB::connection('tenant')
                        ->table('profesor_atrasos')
                        ->where('fecha', '>=', $iniActualStr)
                        ->where(function ($q) use ($periodoActualReal, $asigsValidasActual) {
                            $q->where(function ($sq) use ($periodoActualReal) {
                                $sq->whereNotNull('periodo')
                                   ->where('periodo', '!=', '')
                                   ->where('periodo', '!=', $periodoActualReal);
                            })->orWhere(function ($sq) use ($asigsValidasActual) {
                                $sq->whereNotNull('id_asignatura')
                                   ->whereNotIn('id_asignatura', $asigsValidasActual);
                            });
                        })
                        ->delete();
                }
            }

            try {
                \Illuminate\Support\Facades\Cache::put($cacheKey, true, 600);
            } catch (\Throwable $e) {
            }
        } catch (\Throwable $e) {
            // Ignorar errores silenciosamente si la conexión tenant aún no está activa
        }
    }

    /**
     * Determina si un registro de ProfesorColaborador (y opcionalmente su PlanificacionProfesorColaborador)
     * pertenece estrictamente al período académico indicado (ej. '2026-1' o '2026-2').
     */
    public static function perteneceColaboradorAPeriodo($colab, string $periodo, $planificacion = null, $periodosModels = null): bool
    {
        if (!$colab || empty($periodo)) {
            return false;
        }

        $periodoActualReal = self::getCurrentAcademicYear() . '-' . self::getCurrentSemester();
        $limPeriodo = self::getLimitesPeriodo($periodo, $periodosModels);
        $finPeriodoStr = $limPeriodo['fin']->format('Y-m-d');

        // Si estamos consultando el semestre activo actual, descartar cualquier planificación colaboradora cerrada
        if ($periodo === $periodoActualReal && $planificacion && !empty($planificacion->fecha_fin_vigencia)) {
            $finPlanStr = Carbon::parse($planificacion->fecha_fin_vigencia)->format('Y-m-d');
            if ($finPlanStr < $finPeriodoStr) {
                return false;
            }
        }

        if ($colab->asignatura && !empty($colab->asignatura->periodo)) {
            return (string) $colab->asignatura->periodo === $periodo;
        }

        $iniColab = $colab->fecha_inicio ? Carbon::parse($colab->fecha_inicio)->format('Y-m-d') : null;
        $finColab = $colab->fecha_termino ? Carbon::parse($colab->fecha_termino)->format('Y-m-d') : null;
        $esRangoAnualGenerico = $iniColab && $finColab
            && str_ends_with($iniColab, '-01-01')
            && str_ends_with($finColab, '-12-31');

        if (!$esRangoAnualGenerico && ($iniColab || $finColab)) {
            $pIni = $iniColab ? self::resolverPeriodoParaFecha($iniColab, $periodosModels) : null;
            $pFin = $finColab ? self::resolverPeriodoParaFecha($finColab, $periodosModels) : null;
            if ($pIni && $pFin && $pIni === $pFin) {
                return $pIni === $periodo;
            }
            if ($pFin) {
                return $pFin === $periodo;
            }
            if ($pIni) {
                return $pIni === $periodo;
            }
        }

        if ($planificacion && !empty($planificacion->fecha_fin_vigencia)) {
            return self::resolverPeriodoParaFecha($planificacion->fecha_fin_vigencia, $periodosModels) === $periodo;
        }

        if ($planificacion && !empty($planificacion->fecha_inicio_vigencia)) {
            return self::resolverPeriodoParaFecha($planificacion->fecha_inicio_vigencia, $periodosModels) === $periodo;
        }

        $fechaRef = ($planificacion ? ($planificacion->updated_at ?? $planificacion->created_at) : null)
            ?? $colab->updated_at
            ?? $colab->created_at;

        if ($fechaRef) {
            return self::resolverPeriodoParaFecha($fechaRef, $periodosModels) === $periodo;
        }

        return false;
    }
}
