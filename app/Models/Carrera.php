<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Carrera extends Model
{
    use HasFactory;
    protected $table = 'carreras';
    protected $primaryKey = 'id_carrera';

    protected $fillable = [
        'id_carrera',
        'nombre',
        'id_area_academica'
    ];

    public function areaAcademica()
    {
        return $this->belongsTo(AreaAcademica::class, 'id_area_academica');
    }

    public function asignaturas()
    {
        return $this->hasMany(Asignatura::class, 'id_carrera', 'id_carrera');
    }

    public function jefeCarrera()
    {
        return $this->hasOne(JefeCarrera::class, 'id_carrera', 'id_carrera');
    }
}
