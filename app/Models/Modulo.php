<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Modulo extends Model
{
    use HasFactory;

    protected $table = 'modulos';
<<<<<<< HEAD

    protected $primaryKey = 'id_modulo';
=======
    protected $primaryKey = 'id_modulo';
    public $incrementing = false;
    protected $keyType = 'string';
>>>>>>> Nperez

    protected $fillable = [
        'id_modulo',
        'dia',
        'hora_inicio',
        'hora_termino',
<<<<<<< HEAD
        'fecha',
        'id_asignatura',
        'id_reserva',
        'id_horario'
    ];

    public function asignatura()
    {
        return $this->belongsTo(Asignatura::class, 'id_asignatura', 'id_asignatura');
    }
    public function reserva()
    {
        return $this->belongsTo(Reserva::class, 'id_reserva', 'id_reserva');
    }

    public function horario()
    {
        return $this->belongsTo(Horario::class, 'id_horario', 'id_horario');
=======
    ];

    public function planificaciones()
    {
        return $this->hasMany(Planificacion_Asignatura::class, 'id_modulo', 'id_modulo');
>>>>>>> Nperez
    }
}
