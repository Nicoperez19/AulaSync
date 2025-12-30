<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PeriodoAcademico extends Model
{
    use HasFactory;

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
     * Obtener el período académico actual basado en la fecha
     */
    public static function obtenerPeriodoActual($fecha = null)
    {
        $fecha = $fecha ? Carbon::parse($fecha) : Carbon::now();

        return static::where('activo', true)
            ->where('fecha_inicio', '<=', $fecha)
            ->where('fecha_fin', '>=', $fecha)
            ->first();
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
