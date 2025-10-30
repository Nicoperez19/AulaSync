<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class DiaFeriado extends Model
{
    use HasFactory;

    protected $table = 'dias_feriados';
    protected $primaryKey = 'id_feriado';

    protected $fillable = [
        'fecha_inicio',
        'fecha_fin',
        'nombre',
        'descripcion',
        'tipo',
        'activo',
        'created_by',
    ];

    protected $casts = [
        'fecha_inicio' => 'date',
        'fecha_fin' => 'date',
        'activo' => 'boolean',
    ];

    /**
     * Relación con el usuario que creó el registro
     */
    public function creador()
    {
        return $this->belongsTo(User::class, 'created_by', 'run');
    }

    /**
     * Verificar si una fecha específica es feriado o día sin actividades
     */
    public static function esFeriado($fecha)
    {
        $fechaCarbon = Carbon::parse($fecha);
        
        return static::where('activo', true)
            ->where('fecha_inicio', '<=', $fechaCarbon)
            ->where('fecha_fin', '>=', $fechaCarbon)
            ->exists();
    }

    /**
     * Obtener el feriado activo en una fecha específica
     */
    public static function obtenerFeriadoEnFecha($fecha)
    {
        $fechaCarbon = Carbon::parse($fecha);
        
        return static::where('activo', true)
            ->where('fecha_inicio', '<=', $fechaCarbon)
            ->where('fecha_fin', '>=', $fechaCarbon)
            ->first();
    }

    /**
     * Scope para feriados activos
     */
    public function scopeActivos($query)
    {
        return $query->where('activo', true);
    }

    /**
     * Scope para feriados en un rango de fechas
     */
    public function scopeEnRango($query, $fechaInicio, $fechaFin)
    {
        return $query->where(function ($q) use ($fechaInicio, $fechaFin) {
            $q->whereBetween('fecha_inicio', [$fechaInicio, $fechaFin])
              ->orWhereBetween('fecha_fin', [$fechaInicio, $fechaFin])
              ->orWhere(function ($q2) use ($fechaInicio, $fechaFin) {
                  $q2->where('fecha_inicio', '<=', $fechaInicio)
                     ->where('fecha_fin', '>=', $fechaFin);
              });
        });
    }

    /**
     * Verificar si el feriado está activo en una fecha específica
     */
    public function estaActivoEn($fecha)
    {
        $fechaCarbon = Carbon::parse($fecha);
        
        return $this->activo 
            && $fechaCarbon->between($this->fecha_inicio, $this->fecha_fin);
    }
}
