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
        'id_facultad',
    ];

    public function facultad()
    {
        return $this->belongsTo(Facultad::class, 'id_facultad');
    }
}
