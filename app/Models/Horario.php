<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Horario extends Model
{
    use HasFactory;

    protected $table = 'horarios';
    protected $primaryKey = 'id_horario';
    public $incrementing = false;

    protected $fillable = [
        'id_horario',
        'nombre',
        'id_espacio',
        'id_modulo',
        'id_seccion',
    ];

    public function modulo()
    {
        return $this->belongsTo(Modulo::class, 'id_modulo', 'id_modulo');
    }

    public function espacio()
    {
        return $this->belongsTo(Espacio::class, 'id_espacio', 'id_espacio');
    }

    public function seccion()
    {
        return $this->belongsTo(Seccion::class, 'id_seccion', 'id');
    }
    public function planificaciones()
    {
        return $this->hasMany(Planificacion_Asignatura::class, 'id_horario', 'id_horario');
    }

}
