<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PlanificacionProfesorColaborador extends Model
{
    use HasFactory;

    protected $table = 'planificaciones_profesores_colaboradores';

    protected $fillable = [
        'id_profesor_colaborador',
        'id_modulo',
        'id_espacio',
    ];

    protected $casts = [
        'id_modulo' => 'string',
        'id_espacio' => 'string',
    ];

    /**
     * Relación con ProfesorColaborador
     */
    public function profesorColaborador()
    {
        return $this->belongsTo(ProfesorColaborador::class, 'id_profesor_colaborador');
    }

    /**
     * Relación con Modulo
     */
    public function modulo()
    {
        return $this->belongsTo(Modulo::class, 'id_modulo', 'id_modulo');
    }

    /**
     * Relación con Espacio
     */
    public function espacio()
    {
        return $this->belongsTo(Espacio::class, 'id_espacio', 'id_espacio');
    }

    /**
     * Scope para filtrar por día
     */
    public function scopePorDia($query, $dia)
    {
        return $query->whereHas('modulo', function($q) use ($dia) {
            $q->where('dia', $dia);
        });
    }

    /**
     * Scope para obtener planificaciones vigentes
     */
    public function scopeVigentes($query, $fecha = null)
    {
        return $query->whereHas('profesorColaborador', function($q) use ($fecha) {
            $q->activosYVigentes($fecha);
        });
    }
}
