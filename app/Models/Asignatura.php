<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Asignatura extends Model
{
    use HasFactory;
    protected $primaryKey = 'id_asignatura';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $table = 'asignaturas';

    protected $fillable = [
<<<<<<< HEAD
        'id_asignatura',//codigo
        'nombre_asignatura',
=======
        'id_asignatura',//id_curso
        'codigo_asignatura',//cod_ramo
        'nombre_asignatura',//ramo_nombre
>>>>>>> Nperez
        'horas_directas',
        'horas_indirectas',
        'area_conocimiento',
        'periodo',
        'run', //usuario
        'id_carrera',
    ];
<<<<<<< HEAD
    public function carrera()
    {
        return $this->belongsTo(Carrera::class, 'id_carrera');
    }

    public function profesor()
    {
        return $this->belongsTo(User::class, 'run');  
    }
    
=======

    public function reservas()
    {
        return $this->hasMany(Reserva::class, 'id_asignatura', 'id_asignatura');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'run', 'run');
    }

    public function carrera()
    {
        return $this->belongsTo(Carrera::class, 'id_carrera', 'id_carrera');
    }

>>>>>>> Nperez
    public function secciones()
    {
        return $this->hasMany(Seccion::class, 'id_asignatura', 'id_asignatura');
    }
<<<<<<< HEAD
=======

    public function planificaciones()
    {
        return $this->hasMany(PlanificacionAsignatura::class, 'id_asignatura', 'id_asignatura');
    }
>>>>>>> Nperez
}
