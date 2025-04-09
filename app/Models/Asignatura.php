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
        'id_asignatura',//codigo
        'nombre_asignatura',
        'horas_directas',
        'horas_indirectas',
        'area_conocimiento',
        'periodo',
        'id', //usuario
        'id_carrera',
    ];
    //profesor consulta 

    //cantidad_estudiantes consulta
    public function carrera()
    {
        return $this->belongsTo(Carrera::class, 'id_carrera');
    }

    public function profesor()
    {
        return $this->belongsTo(User::class, 'id');  // Relación con el campo 'id' de 'users'
    }
    
}
