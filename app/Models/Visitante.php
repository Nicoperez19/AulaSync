<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Visitante extends Model
{
    use HasFactory;

    protected $table = 'visitantes';

    protected $primaryKey = 'id';

    protected $fillable = [
        'run_solicitante',
        'nombre',
        'correo',
        'telefono',
        'tipo_solicitante',
        'activo',
        'fecha_registro',
    ];
}
