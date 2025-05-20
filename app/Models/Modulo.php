<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Modulo extends Model
{
    use HasFactory;

    protected $table = 'modulos';
    protected $primaryKey = 'id_modulo';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id_modulo',
        'dia',
        'hora_inicio',
        'hora_termino',
    ];

    public function planificaciones()
    {
        return $this->hasMany(Planificacion_Asignatura::class, 'id_modulo', 'id_modulo');
    }
}
