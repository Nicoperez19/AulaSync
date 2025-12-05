<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProfesorAtraso extends Model
{
    use HasFactory;

    protected $table = 'profesor_atrasos';

    protected $fillable = [
        'id_planificacion',
        'id_asignatura',
        'id_espacio',
        'id_modulo',
        'run_profesor',
        'fecha',
        'hora_programada',
        'hora_llegada',
        'minutos_atraso',
        'periodo',
        'observaciones',
        'justificado',
        'justificacion',
    ];

    protected $casts = [
        'fecha' => 'date',
        'justificado' => 'boolean',
        'minutos_atraso' => 'integer',
    ];

    /**
     * Obtener hora programada formateada
     */
    public function getHoraProgramadaFormateadaAttribute()
    {
        if (!$this->hora_programada) return '-';
        return substr($this->hora_programada, 0, 5);
    }

    /**
     * Obtener hora llegada formateada
     */
    public function getHoraLlegadaFormateadaAttribute()
    {
        if (!$this->hora_llegada) return '-';
        return substr($this->hora_llegada, 0, 5);
    }

    /**
     * Relación con la planificación
     */
    public function planificacion()
    {
        return $this->belongsTo(Planificacion_Asignatura::class, 'id_planificacion');
    }

    /**
     * Relación con la asignatura
     */
    public function asignatura()
    {
        return $this->belongsTo(Asignatura::class, 'id_asignatura');
    }

    /**
     * Relación con el espacio
     */
    public function espacio()
    {
        return $this->belongsTo(Espacio::class, 'id_espacio', 'id_espacio');
    }

    /**
     * Relación con el profesor
     */
    public function profesor()
    {
        return $this->belongsTo(Profesor::class, 'run_profesor', 'run_profesor');
    }

    /**
     * Scope para filtrar por fecha
     */
    public function scopeFecha($query, $fecha)
    {
        return $query->where('fecha', $fecha);
    }

    /**
     * Scope para filtrar por período
     */
    public function scopePeriodo($query, $periodo)
    {
        return $query->where('periodo', $periodo);
    }

    /**
     * Scope para filtrar por profesor
     */
    public function scopeProfesor($query, $runProfesor)
    {
        return $query->where('run_profesor', $runProfesor);
    }

    /**
     * Scope para atrasos no justificados
     */
    public function scopeNoJustificados($query)
    {
        return $query->where('justificado', false);
    }

    /**
     * Scope para atrasos justificados
     */
    public function scopeJustificados($query)
    {
        return $query->where('justificado', true);
    }

    /**
     * Obtener el nombre completo del profesor
     */
    public function getNombreProfesorAttribute()
    {
        return $this->profesor ? $this->profesor->nombre_completo : $this->run_profesor;
    }

    /**
     * Obtener el nombre del espacio
     */
    public function getNombreEspacioAttribute()
    {
        return $this->espacio ? $this->espacio->nombre : 'Espacio desconocido';
    }

    /**
     * Obtener el nombre de la asignatura
     */
    public function getNombreAsignaturaAttribute()
    {
        return $this->asignatura ? $this->asignatura->nombre : 'Asignatura desconocida';
    }
}
