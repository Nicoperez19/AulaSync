<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CursoVerano extends Model
{
    use HasFactory;

    protected $table = 'cursos_verano';

    protected $primaryKey = 'id_curso_verano';

    protected $fillable = [
        'anio',
        'fecha_inicio',
        'fecha_fin',
        'activo',
        'created_by',
    ];

    protected $casts = [
        'fecha_inicio' => 'date',
        'fecha_fin' => 'date',
        'activo' => 'boolean',
        'anio' => 'integer',
    ];

    /**
     * Relación con el usuario que creó el registro
     */
    public function creador()
    {
        return $this->belongsTo(User::class, 'created_by', 'run');
    }

    /**
     * Obtener el curso de verano actual basado en la fecha
     */
    public static function obtenerCursoVeranoActual($fecha = null)
    {
        $fecha = $fecha ? Carbon::parse($fecha) : Carbon::now();

        return static::where('activo', true)
            ->where('fecha_inicio', '<=', $fecha)
            ->where('fecha_fin', '>=', $fecha)
            ->first();
    }

    /**
     * Verificar si actualmente estamos en cursos de verano
     */
    public static function estaEnCursosVerano($fecha = null)
    {
        return static::obtenerCursoVeranoActual($fecha) !== null;
    }

    /**
     * Obtener el nombre formateado
     */
    public function getNombreAttribute()
    {
        return "Cursos de Verano {$this->anio}";
    }

    /**
     * Verificar si ya finalizó
     */
    public function haFinalizado()
    {
        return Carbon::now()->gt($this->fecha_fin);
    }

    /**
     * Verificar si aún no ha comenzado
     */
    public function noHaIniciado()
    {
        return Carbon::now()->lt($this->fecha_inicio);
    }

    /**
     * Verificar si está actualmente en curso
     */
    public function estaEnCurso()
    {
        $hoy = Carbon::now();
        return $hoy->gte($this->fecha_inicio) && $hoy->lte($this->fecha_fin);
    }

    /**
     * Obtener el estado como texto
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
     * Obtener el color del estado
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
        
        return 'orange';
    }

    /**
     * Scope para cursos activos
     */
    public function scopeActivos($query)
    {
        return $query->where('activo', true);
    }

    /**
     * Scope para curso en curso
     */
    public function scopeEnCurso($query)
    {
        $hoy = Carbon::now();
        return $query->where('activo', true)
            ->where('fecha_inicio', '<=', $hoy)
            ->where('fecha_fin', '>=', $hoy);
    }
}
