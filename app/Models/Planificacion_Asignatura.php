<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Planificacion_Asignatura extends Model
{
    use HasFactory;

    protected $table = 'planificacion_asignaturas';

    protected $primaryKey = 'id';

    protected $fillable = [
        'id_asignatura',
        'id_horario',
        'id_modulo',
        'id_espacio',
        'inscritos',
    ];

    protected $casts = [
        'id_horario' => 'string',
        'id_asignatura' => 'string',
        'id_modulo' => 'string',
        'id_espacio' => 'string'
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
}
