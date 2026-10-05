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
        $fecha = $fecha ? Carbon::parse($fecha) : Carbon::now();
        $fechaStr = $fecha->toDateString();

        // 1. Buscar período vigente exactamente por fechas
        $periodo = static::whereDate('fecha_inicio', '<=', $fechaStr)
            ->whereDate('fecha_fin', '>=', $fechaStr)
            ->orderBy('anio', 'desc')
            ->orderBy('semestre', 'desc')
            ->first();

        if ($periodo) {
            // Auto-reparación: si el período en curso no estaba activo en BD, o hay anteriores activos, auto-sincronizar
            if (!$periodo->activo) {
                static::sincronizarEstadosSegunFechas($fecha);
                $periodo->activo = true;
            }
            return $periodo;
        }

        // 2. Si hoy es un día entre semestres (vacaciones) y no hay fecha exacta,
        // buscar el período marcado explícitamente como activo
        return static::where('activo', true)
            ->orderBy('anio', 'desc')
            ->orderBy('semestre', 'desc')
            ->first();
    }

    /**
     * Sincroniza automáticamente la columna 'activo' de todos los períodos según las fechas reales:
     * - El período en curso (fecha_inicio <= hoy <= fecha_fin) pasa a activo = true.
     * - Los períodos ya finalizados (fecha_fin < hoy) pasan a activo = false.
     * - Los períodos futuros (fecha_inicio > hoy) pasan a activo = false hasta su inicio.
     *
     * Esto garantiza que la base de datos siempre tenga activo exactamente el semestre
     * que corresponde, de forma 100% desatendida.
     *
     * @param string|Carbon|null $fecha
     * @return PeriodoAcademico|null El período que quedó activo
     */
    public static function sincronizarEstadosSegunFechas($fecha = null)
    {
        $fecha = $fecha ? Carbon::parse($fecha) : Carbon::now();
        $fechaStr = $fecha->toDateString();

        // 1. Desactivar todos los períodos cuya fecha de fin ya expiró
        static::whereDate('fecha_fin', '<', $fechaStr)
            ->where('activo', true)
            ->update(['activo' => false]);

        // 2. Desactivar períodos que aún no inician
        static::whereDate('fecha_inicio', '>', $fechaStr)
            ->where('activo', true)
            ->update(['activo' => false]);

        // 3. Activar el período que esté en curso
        $periodoEnCurso = static::whereDate('fecha_inicio', '<=', $fechaStr)
            ->whereDate('fecha_fin', '>=', $fechaStr)
            ->orderBy('anio', 'desc')
            ->orderBy('semestre', 'desc')
            ->first();

        if ($periodoEnCurso && !$periodoEnCurso->activo) {
            $periodoEnCurso->update(['activo' => true]);
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
        return !is_null($this->inicio_verano) && !is_null($this->fin_verano);
    }

    /**
     * Obtener el estado del período como texto
     */
    public function getEstadoTextoAttribute()
    {
        if (!$this->activo) {
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
        if (!$this->activo) {
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
