<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RecuperacionClase extends Model
{
    use HasFactory;

    protected $table = 'recuperacion_clases';
    protected $primaryKey = 'id_recuperacion';

    protected $fillable = [
        'id_licencia',
        'run_profesor',
        'id_asignatura',
        'id_espacio',
        'fecha_clase_original',
        'id_modulo_original',
        'fecha_reagendada',
        'id_modulo_reagendado',
        'id_espacio_reagendado',
        'estado',
        'notificado',
        'fecha_notificacion',
        'notas',
        'gestionado_por',
    ];

    protected $casts = [
        'fecha_clase_original' => 'date',
        'fecha_reagendada' => 'date',
        'notificado' => 'boolean',
        'fecha_notificacion' => 'datetime',
    ];

    /**
     * Relación con Licencia
     */
    public function licencia()
    {
        return $this->belongsTo(LicenciaProfesor::class, 'id_licencia', 'id_licencia');
    }

    /**
     * Relación con Profesor
     */
    public function profesor()
    {
        return $this->belongsTo(Profesor::class, 'run_profesor', 'run_profesor');
    }

    /**
     * Relación con Asignatura
     */
    public function asignatura()
    {
        return $this->belongsTo(Asignatura::class, 'id_asignatura', 'id_asignatura');
    }

    /**
     * Relación con Espacio original
     */
    public function espacio()
    {
        return $this->belongsTo(Espacio::class, 'id_espacio', 'id_espacio');
    }

    /**
     * Relación con Módulo original
     */
    public function moduloOriginal()
    {
        return $this->belongsTo(Modulo::class, 'id_modulo_original', 'id_modulo');
    }

    /**
     * Relación con Módulo reagendado
     */
    public function moduloReagendado()
    {
        return $this->belongsTo(Modulo::class, 'id_modulo_reagendado', 'id_modulo');
    }

    /**
     * Relación con Espacio reagendado
     */
    public function espacioReagendado()
    {
        return $this->belongsTo(Espacio::class, 'id_espacio_reagendado', 'id_espacio');
    }

    /**
     * Usuario que gestionó la recuperación
     */
    public function gestor()
    {
        return $this->belongsTo(User::class, 'gestionado_por', 'run');
    }

    /**
     * Verificar si está reagendada
     */
    public function estaReagendada()
    {
        return $this->fecha_reagendada !== null && $this->estado === 'reagendada';
    }

    /**
     * Scope para recuperaciones pendientes
     */
    public function scopePendientes($query)
    {
        return $query->where('estado', 'pendiente');
    }

    /**
     * Scope para recuperaciones reagendadas
     */
    public function scopeReagendadas($query)
    {
        return $query->where('estado', 'reagendada');
    }

    /**
     * Scope para recuperaciones no notificadas
     */
    public function scopeNoNotificadas($query)
    {
        return $query->where('notificado', false);
    }

    /**
     * Scope para recuperaciones de un profesor
     */
    public function scopeDelProfesor($query, $runProfesor)
    {
        return $query->where('run_profesor', $runProfesor);
    }

    /**
     * Marcar como notificado
     */
    public function marcarComoNotificado()
    {
        $this->update([
            'notificado' => true,
            'fecha_notificacion' => now(),
        ]);
    }
}
