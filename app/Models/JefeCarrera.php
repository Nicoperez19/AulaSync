<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JefeCarrera extends Model
{
    use HasFactory;

    protected $table = 'jefes_carrera';

    protected $fillable = [
        'nombre',
        'email',
        'telefono',
        'id_carrera',
    ];

    public function carrera()
    {
        return $this->belongsTo(Carrera::class, 'id_carrera', 'id_carrera');
    }
}
