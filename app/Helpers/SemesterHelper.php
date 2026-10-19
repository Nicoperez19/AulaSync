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
     *    para que nunca se mezclen con el semestre activo.
     * 3. Corrige los registros de ProfesorColaborador que tenían fechas anuales genéricas (01-01 a 12-31),
     *    acotándolos al semestre específico al que pertenecen.
     */
    public static function asegurarSeparacionSemestres(?string $periodoActual = null, bool $forzar = false): void
    {
        try {
            $dbName = config('database.connections.tenant.database') ?? 'default';
            $periodoActual = $periodoActual ?: (self::getCurrentAcademicYear() . '-' . self::getCurrentSemester());
            $memKey = "{$dbName}_{$periodoActual}";

            if (!$forzar && isset(static::$reconciledTenants[$memKey])) {
                return;
            }
            static::$reconciledTenants[$memKey] = true;

            $cacheKey = "sem_sep_healed_v2_{$memKey}";
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

            // 2. Cerrar fecha_fin_vigencia en planificacion_asignaturas de semestres distintos al actual que ya terminaron
            if (\App\Models\Planificacion_Asignatura::tieneColumnaVigencia()) {
                $periodosEnHorarios = \Illuminate\Support\Facades\DB::connection('tenant')
                    ->table('horarios')
                    ->whereNotNull('periodo')
                    ->where('periodo', '!=', '')
                    ->where('periodo', '!=', $periodoActual)
                    ->distinct()
                    ->pluck('periodo');

                foreach ($periodosEnHorarios as $pHist) {
                    $limHist = self::getLimitesPeriodo($pHist, $periodosModels);
                    $finHistStr = $limHist['fin']->toDateString();
                    $iniHistStr = $limHist['inicio']->toDateString();

                    // Si ese semestre ya concluyó (o es anterior al semestre activo), cerrar su vigencia al fin de su semestre
                    if ($finHistStr < $hoyStr || strcmp($pHist, $periodoActual) < 0) {
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
                                ->whereNull('fecha_inicio_vigencia')
                                ->update(['fecha_inicio_vigencia' => $iniHistStr]);
                        }
                    }
                }
            }

            // 3. Corregir ProfesorColaborador con rangos anuales que cruzan ambos semestres (ej. 2026-01-01 a 2026-12-31)
            $colabsAnuales = \App\Models\ProfesorColaborador::query()
                ->whereNotNull('fecha_inicio')
                ->whereNotNull('fecha_termino')
                ->get()
                ->filter(function ($c) use ($periodosModels) {
                    $pIni = self::resolverPeriodoParaFecha($c->fecha_inicio, $periodosModels);
                    $pFin = self::resolverPeriodoParaFecha($c->fecha_termino, $periodosModels);
                    return $pIni !== $pFin;
                });

            if ($colabsAnuales->isNotEmpty()) {
                // Mapa de periodos por asignatura en horarios titulares
                $asigPeriodos = [];
                $rowsAsig = \Illuminate\Support\Facades\DB::connection('tenant')
                    ->table('planificacion_asignaturas as pa')
                    ->join('horarios as h', 'pa.id_horario', '=', 'h.id_horario')
                    ->whereNotNull('pa.id_asignatura')
                    ->whereNotNull('h.periodo')
                    ->select('pa.id_asignatura', 'h.periodo')
                    ->distinct()
                    ->get();
                foreach ($rowsAsig as $r) {
                    $asigPeriodos[$r->id_asignatura][$r->periodo] = true;
                }

                foreach ($colabsAnuales as $colab) {
                    $anioColab = Carbon::parse($colab->fecha_inicio)->year;
                    $lim1 = self::getLimitesPeriodo($anioColab . '-1', $periodosModels);
                    $lim2 = self::getLimitesPeriodo($anioColab . '-2', $periodosModels);

                    $planifsColab = \App\Models\PlanificacionProfesorColaborador::withoutGlobalScope('vigencia_activa')
                        ->where('id_profesor_colaborador', $colab->id)
                        ->get();

                    $tieneActivaEnSem2 = false;
                    $todasCerradasEnSem1 = $planifsColab->isNotEmpty();

                    foreach ($planifsColab as $pc) {
                        $finVig = $pc->fecha_fin_vigencia ? Carbon::parse($pc->fecha_fin_vigencia)->format('Y-m-d') : null;
                        if ($finVig === null || $finVig >= $lim2['inicio']->format('Y-m-d')) {
                            $todasCerradasEnSem1 = false;
                            $refUpdate = $pc->fecha_inicio_vigencia ?? $pc->updated_at ?? $pc->created_at;
                            if ($refUpdate && Carbon::parse($refUpdate)->format('Y-m-d') >= $lim2['inicio']->format('Y-m-d')) {
                                $tieneActivaEnSem2 = true;
                            }
                        }
                    }

                    $periodosAsig = ($colab->id_asignatura && isset($asigPeriodos[$colab->id_asignatura]))
                        ? array_keys($asigPeriodos[$colab->id_asignatura])
                        : [];

                    $semestreDestino = null;
                    if ($todasCerradasEnSem1) {
                        $semestreDestino = 1;
                    } elseif (count($periodosAsig) === 1) {
                        $partesP = explode('-', $periodosAsig[0]);
                        $semestreDestino = (int) ($partesP[1] ?? 2);
                    } elseif ($tieneActivaEnSem2) {
                        $semestreDestino = 2;
                    } else {
                        $fechaRefColab = $colab->updated_at ?? $colab->created_at;
                        if ($fechaRefColab && Carbon::parse($fechaRefColab)->format('Y-m-d') < $lim2['inicio']->format('Y-m-d')) {
                            $semestreDestino = 1;
                        } else {
                            $semestreDestino = 2;
                        }
                    }

                    if ($semestreDestino === 1) {
                        $colab->update([
                            'fecha_inicio' => $lim1['inicio']->toDateString(),
                            'fecha_termino' => $lim1['fin']->toDateString(),
                        ]);
                    } else {
                        $colab->update([
                            'fecha_inicio' => $lim2['inicio']->toDateString(),
                            'fecha_termino' => $lim2['fin']->toDateString(),
                        ]);
                    }
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

        if ($planificacion && !empty($planificacion->fecha_fin_vigencia)) {
            return self::resolverPeriodoParaFecha($planificacion->fecha_fin_vigencia, $periodosModels) === $periodo;
        }

        if ($planificacion && !empty($planificacion->fecha_inicio_vigencia)) {
            return self::resolverPeriodoParaFecha($planificacion->fecha_inicio_vigencia, $periodosModels) === $periodo;
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

        if ($colab->asignatura && !empty($colab->asignatura->periodo)) {
            return (string) $colab->asignatura->periodo === $periodo;
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