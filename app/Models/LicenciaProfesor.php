<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToTenant;

class LicenciaProfesor extends Model
{
    use HasFactory, BelongsToTenant;
    
    protected $connection = 'tenant';
    protected $table = 'licencias_profesores';
    protected $primaryKey = 'id_licencia';

    protected $fillable = [
        'run_profesor',
        'fecha_inicio',
        'fecha_fin',
        'motivo',
        'observaciones',
        'estado',
        'genera_recuperacion',
        'created_by',
    ];

    protected $casts = [
        'fecha_inicio' => 'date',
        'fecha_fin' => 'date',
        'genera_recuperacion' => 'boolean',
    ];

    /**
     * Relación con Profesor
     */
    public function profesor()
    {
        return $this->belongsTo(Profesor::class, 'run_profesor', 'run_profesor');
    }

    /**
     * Relación con recuperaciones de clases
     */
    public function recuperaciones()
    {
        return $this->hasMany(RecuperacionClase::class, 'id_licencia', 'id_licencia');
    }

    /**
     * Usuario que creó la licencia
     */
    public function creador()
    {
        return $this->belongsTo(User::class, 'created_by', 'run');
    }

    /**
     * Verificar si la licencia está activa en una fecha específica
     */
    public function estaActivaEn($fecha)
    {
        return $this->estado === 'activa' 
            && $fecha >= $this->fecha_inicio 
            && $fecha <= $this->fecha_fin;
    }

    /**
     * Scope para licencias activas
     */
    public function scopeActivas($query)
    {
        return $query->where('estado', 'activa');
    }

    /**
     * Scope para licencias de un profesor específico
     */
    public function scopeDelProfesor($query, $runProfesor)
    {
        return $query->where('run_profesor', $runProfesor);
    }

    /**
     * Scope para licencias en un rango de fechas
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
}
