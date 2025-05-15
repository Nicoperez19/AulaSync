<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Horario extends Model
{
    use HasFactory;
    protected $table = 'horarios';
    protected $primaryKey = 'id_horario';
    protected $fillable = ['id_horario', 'nombre', 'id_espacio', 'id_modulo', 'id'];

    public function modulos()
    {
        return $this->hasMany(Modulo::class, 'id_horario', 'id_horario');
    }

    public function espacio()
    {
        return $this->belongsTo(Espacio::class, 'id_espacio', 'id_espacio');
    }

    
    public function seccion()
    {
        return $this->belongsTo(Seccion::class, 'id', 'id');
    }
}
