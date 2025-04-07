<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Espacio extends Model
{
    use HasFactory;
    protected $primaryKey = 'id_espacio';

    protected $fillable = [
        'id_espacio',
        'id',
        'tipo_espacio',
        'estado',
        'puestos_disponibles',
    ];

    public function piso()
    {
        return $this->belongsTo(Piso::class, 'id');
    }

    public function facultad()
    {
        return $this->belongsTo(Facultad::class, 'id_facultad');
    }

    public function universidad()
    {
        return $this->belongsToThrough(Universidad::class, Facultad::class);
    }


}
