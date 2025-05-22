<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Mapa extends Model
{
    use HasFactory;

    protected $fillable = [
        'id_mapa',
        'nombre_mapa',
        'ruta_mapa',
        'ruta_canvas',
        'piso_id'
    ];

    public function piso()
    {
        return $this->belongsTo(Piso::class, 'piso_id');
    }
}
