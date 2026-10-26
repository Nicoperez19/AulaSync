<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PeriodoAcademico extends Model
{
    use HasFactory;

    protected $connection = 'mysql';

    protected $table = 'periodos_academicos';

    protected $primaryKey = 'id_periodo';

    protected $fillable = [
        'anio',
        'semestre',
        'fecha_inicio',
        'fecha_fin',
        'inicio_verano',
        'fin_verano',
        'activo',
        'created_by',
    ];

    protected $casts = [
        'fecha_inicio' => 'date',
        'fecha_fin' => 'date',
        'inicio_verano' => 'date',
        'fin_verano' => 'date',
        'activo' => 'boolean',
        'anio' => 'integer',
        'semestre' => 'integer',
    ];

    /**
     * Relación con el usuario que creó el registro
     */
    public function creador()
    {
        return $this->belongsTo(User::class, 'created_by', 'run');
    }

    /**
     * Obtener el período académico actual basado en la fecha.
     * La fecha del calendario es la fuente de verdad absoluta:
     * El período cuyo rango (fecha_inicio <= fecha <= fecha_fin) contenga la fecha dada es el actual.
     */
    public static function obtenerPeriodoActual($fecha = null)
    {
        try {
            $esFechaActual = ($fecha === null);
            $fecha = $fecha ? Carbon::parse($fecha) : Carbon::now();
            $fechaStr = $fecha->toDateString();

            // 1. Buscar período vigente exactamente por fechas
            $periodo = static::whereDate('fecha_inicio', '<=', $fechaStr)
                ->whereDate('fecha_fin', '>=', $fechaStr)
                ->orderBy('anio', 'desc')
                ->orderBy('semestre', 'desc')
                ->first();

            if ($periodo) {
                // Si estamos consultando la fecha actual, garantizar que SOLO este período esté activo en BD
                if ($esFechaActual) {
                    $otrosActivos = static::where('id_periodo', '!=', $periodo->id_periodo)
                        ->where('activo', true)
                        ->exists();
                    if (!$periodo->activo || $otrosActivos) {
                        static::sincronizarEstadosSegunFechas($fecha);
                        $periodo->activo = true;
                    }
                }
                return $periodo;
            }

            // 2. Si es la fecha actual, desactivar períodos cuya fecha_fin ya expiró
            if ($esFechaActual) {
                static::whereDate('fecha_fin', '<', $fechaStr)
                    ->where('activo', true)
                    ->update(['activo' => false]);
            }

            // 3. Si es un día entre semestres o previo al inicio de clases, buscar el período del semestre correspondiente
            $semestreCalendario = ($fecha->month > 7 || ($fecha->month == 7 && $fecha->day >= 21)) ? 2 : 1;
            $periodoFallback = static::where('anio', $fecha->year)
                ->where('semestre', $semestreCalendario)
                ->whereDate('fecha_fin', '>=', $fechaStr)
                ->first();

            if (!$periodoFallback) {
                $periodoFallback = static::where('activo', true)
                    ->whereDate('fecha_fin', '>=', $fechaStr)
                    ->where('semestre', $semestreCalendario)
                    ->orderBy('anio', 'desc')
                    ->orderBy('semestre', 'desc')
                    ->first();
            }

            if ($periodoFallback && $esFechaActual) {
                static::where('id_periodo', '!=', $periodoFallback->id_periodo)
                    ->where('activo', true)
                    ->update(['activo' => false]);
                if (!$periodoFallback->activo) {
                    $periodoFallback->update(['activo' => true]);
                }
            }

            return $periodoFallback;
        } catch (\Throwable $e) {
            return null;
        }
    }

    /**
     * Sincroniza automáticamente la columna 'activo' de todos los períodos según las fechas reales:
     * - El período en curso (fecha_inicio <= hoy <= fecha_fin) pasa a activo = true.
     * - TODOS los demás períodos pasan a activo = false (garantizando un único semestre activo).
     *
     * @param  string|Carbon|null  $fecha
     * @return PeriodoAcademico|null El período que quedó activo
     */
    public static function sincronizarEstadosSegunFechas($fecha = null)
    {
        $fecha = $fecha ? Carbon::parse($fecha) : Carbon::now();
        $fechaStr = $fecha->toDateString();

        // 1. Buscar el período que esté actualmente en curso
        $periodoEnCurso = static::whereDate('fecha_inicio', '<=', $fechaStr)
            ->whereDate('fecha_fin', '>=', $fechaStr)
            ->orderBy('anio', 'desc')
            ->orderBy('semestre', 'desc')
            ->first();

        // 2. Si hoy está en transición entre semestres, elegir el semestre correspondiente del año actual no vencido
        if (!$periodoEnCurso) {
            $semestreCalendario = ($fecha->month > 7 || ($fecha->month == 7 && $fecha->day >= 21)) ? 2 : 1;
            $periodoEnCurso = static::where('anio', $fecha->year)
                ->where('semestre', $semestreCalendario)
                ->whereDate('fecha_fin', '>=', $fechaStr)
                ->orderBy('id_periodo', 'desc')
                ->first();
        }

        if ($periodoEnCurso) {
            // Desactivar TODOS los demás períodos para que jamás haya 2 semestres activos a la vez
            static::where('id_periodo', '!=', $periodoEnCurso->id_periodo)
                ->where('activo', true)
                ->update(['activo' => false]);

            if (!$periodoEnCurso->activo) {
                $periodoEnCurso->update(['activo' => true]);
            }
        } else {
            // Desactivar todos los períodos cuya fecha de fin ya expiró o que aún no inician
            static::where(function ($q) use ($fechaStr) {
                $q->whereDate('fecha_fin', '<', $fechaStr)
                  ->orWhereDate('fecha_inicio', '>', $fechaStr);
            })->where('activo', true)->update(['activo' => false]);
        }

        return $periodoEnCurso;
    }

    /**
     * Verificar si una fecha está dentro de algún período académico activo
     */
    public static function estaEnPeriodoActivo($fecha = null)
    {
        return static::obtenerPeriodoActual($fecha) !== null;
    }

    /**
     * Verificar si actualmente estamos en cursos de verano
     */
    public static function estaEnCursosVerano($fecha = null)
    {
        $fecha = $fecha ? Carbon::parse($fecha) : Carbon::now();

        return static::where('activo', true)
            ->whereNotNull('inicio_verano')
            ->whereNotNull('fin_verano')
            ->where('inicio_verano', '<=', $fecha)
            ->where('fin_verano', '>=', $fecha)
            ->exists();
    }

    /**
     * Obtener el nombre formateado del período
     */
    public function getNombreCompletoAttribute()
    {
        $ordinal = $this->semestre == 1 ? 'Primer' : 'Segundo';

        return "{$ordinal} Semestre {$this->anio}";
    }

    /**
     * Obtener el nombre corto del período
     */
    public function getNombreCortoAttribute()
    {
        return "{$this->semestre}° Sem. {$this->anio}";
    }

    /**
     * Verificar si el período ya finalizó
     */
    public function haFinalizado()
    {
        return Carbon::now()->gt($this->fecha_fin);
    }

    /**
     * Verificar si el período aún no ha comenzado
     */
    public function noHaIniciado()
    {
        return Carbon::now()->lt($this->fecha_inicio);
    }

    /**
     * Verificar si el período está actualmente en curso
     */
    public function estaEnCurso()
    {
        $hoy = Carbon::now();

        return $hoy->gte($this->fecha_inicio) && $hoy->lte($this->fecha_fin);
    }

    /**
     * Obtener todos los períodos ordenados por año y semestre
     */
    public static function obtenerTodos()
    {
        return static::orderBy('anio', 'desc')
            ->orderBy('semestre', 'desc')
            ->get();
    }

    /**
     * Verificar si hay cursos de verano configurados
     */
    public function tieneCursosVerano()
    {
        return ! is_null($this->inicio_verano) && ! is_null($this->fin_verano);
    }

    /**
     * Obtener el estado del período como texto
     */
    public function getEstadoTextoAttribute()
    {
        if (! $this->activo) {
            return 'Inactivo';
        }

        if ($this->noHaIniciado()) {
            return 'Por iniciar';
        }

        if ($this->haFinalizado()) {
            return 'Finalizado';
        }

        return 'En curso';
    }

    /**
     * Obtener el color del estado para la UI
     */
    public function getEstadoColorAttribute()
    {
        if (! $this->activo) {
            return 'gray';
        }

        if ($this->noHaIniciado()) {
            return 'yellow';
        }

        if ($this->haFinalizado()) {
            return 'red';
        }

        return 'green';
    }

    /**
     * Scope para períodos activos
     */
    public function scopeActivos($query)
    {
        return $query->where('activo', true);
    }

    /**
     * Scope para el período actual
     */
    public function scopeEnCurso($query)
    {
        $hoy = Carbon::now();

        return $query->where('activo', true)
            ->where('fecha_inicio', '<=', $hoy)
            ->where('fecha_fin', '>=', $hoy);
    }
}
