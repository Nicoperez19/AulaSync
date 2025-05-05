<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Seccion extends Model
{
    use HasFactory;

    protected $table = 'seccions'; 
    protected $fillable = [
        'numero',
        'id_asignatura',
    ];

    public function asignatura()
    {
        return $this->belongsTo(Asignatura::class, 'id_asignatura', 'id_asignatura');
    }

    public function horarios()
    {
        return $this->hasMany(Horario::class, 'id_seccion', 'id');
    }
}
