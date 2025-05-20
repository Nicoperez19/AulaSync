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
        'id_asignatura',//id_curso
        'codigo_asignatura',//cod_ramo
        'nombre_asignatura',//ramo_nombre
        'horas_directas',
        'horas_indirectas',
        'area_conocimiento',
        'periodo',
        'run', //usuario
        'id_carrera',
    ];
    public function carrera()
    {
        return $this->belongsTo(Carrera::class, 'id_carrera');
    }

    public function profesor()
    {
        return $this->belongsTo(User::class, 'run');  
    }
    
    public function secciones()
    {
        return $this->hasMany(Seccion::class, 'id_asignatura', 'id_asignatura');
    }

     public function planificaciones()
    {
        return $this->hasMany(Planificacion_Asignatura::class, 'id_asignatura', 'id_asignatura');
    }
}
