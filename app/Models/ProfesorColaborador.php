<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProfesorColaborador extends Model
{
    use BelongsToTenant, HasFactory;

    protected $connection = 'tenant';

    protected $table = 'profesores_colaboradores';

    protected $fillable = [
        'run_profesor_colaborador',
        'id_asignatura',
        'nombre_asignatura_temporal',
        'descripcion',
        'cantidad_inscritos',
        'fecha_inicio',
        'fecha_termino',
        'estado',
        'tipo_clase',
    ];

    protected $casts = [
        'fecha_inicio' => 'date',
        'fecha_termino' => 'date',
    ];

    /**
     * Relación con Profesor
     */
    public function profesor(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Profesor::class, 'run_profesor_colaborador', 'run_profesor');
    }

    /**
     * Relación con Asignatura (opcional)
     */
    public function asignatura(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Asignatura::class, 'id_asignatura', 'id_asignatura');
    }

    /**
     * Relación con planificaciones
     */
    public function planificaciones(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(PlanificacionProfesorColaborador::class, 'id_profesor_colaborador');
    }

    /**
     * Scope para filtrar solo los profesores colaboradores activos
     */
    public function scopeActivos($query)
    {
        return $query->where('estado', 'activo');
    }

    /**
     * Scope para filtrar por vigencia de fechas
     */
    public function scopeVigentes($query, $fecha = null)
    {
        $fecha = $fecha ?? Carbon::today();

        return $query->where('fecha_inicio', '<=', $fecha)
            ->where('fecha_termino', '>=', $fecha);
    }

    /**
     * Scope para filtrar activos y vigentes
     */
    public function scopeActivosYVigentes($query, $fecha = null)
    {
        return $query->activos()->vigentes($fecha);
    }

    /**
     * Scope para filtrar los vencidos
     */
    public function scopeVencidos($query, $fecha = null)
    {
        $fecha = $fecha ?? Carbon::today();

        return $query->where('fecha_termino', '<', $fecha);
    }

    /**
     * Verificar si está vigente en una fecha
     */
    public function estaVigente($fecha = null)
    {
        $fecha = $fecha ?? Carbon::today();

        return $this->fecha_inicio <= $fecha && $this->fecha_termino >= $fecha;
    }

    /**
     * Obtener el nombre de la asignatura (temporal o de BD)
     */
    public function getNombreAsignaturaAttribute()
    {
        return $this->nombre_asignatura_temporal ?? $this->asignatura->nombre_asignatura ?? 'Sin asignatura';
    }
}
