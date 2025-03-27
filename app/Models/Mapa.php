<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Mapa extends Model
{
    use HasFactory;
    protected $table = 'mapas';
    protected $primaryKey = 'id_mapa';
    protected $fillable = [
        'id_mapa',
        'nombre_mapa',
        'ruta_mapa',
        'id_espacio',
    ];
    public function espacio()
    {
        return $this->belongsTo(Espacio::class, 'id_espacio', 'id_espacio');
    }
}
