<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Modulo extends Model
{
    use HasFactory;

    protected $table = 'modulos';

    protected $primaryKey = 'id_modulo';

    protected $fillable = [
        'hora_inicio',
        'hora_termino',
    ];


  
}
