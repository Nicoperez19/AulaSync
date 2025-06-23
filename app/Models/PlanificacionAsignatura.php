<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlanificacionAsignatura extends Model
{
    protected $table = 'planificacion_asignaturas';

    protected $fillable = [
        'id_asignatura',
        'id_horario',
        'id_modulo',
        'id_espacio'
    ];

    public function asignatura()
    {
        return $this->belongsTo(Asignatura::class, 'id_asignatura', 'id_asignatura');
    }

    public function horario()
    {
        return $this->belongsTo(Horario::class, 'id_horario', 'id_horario');
    }

    public function modulo()
    {
        return $this->belongsTo(Modulo::class, 'id_modulo', 'id_modulo');
    }

    public function espacio()
    {
        return $this->belongsTo(Espacio::class, 'id_espacio', 'id_espacio');
    }

    public function reservas()
    {
        return $this->hasMany(Reserva::class, 'id_espacio', 'id_espacio');
    }
} 