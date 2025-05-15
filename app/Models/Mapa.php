<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Mapa extends Model
{
    use HasFactory;
    protected $table = 'mapas';
    protected $primaryKey = 'id_mapa';
    public $incrementing = false; 
    protected $keyType = 'string';
    protected $fillable = [
        'nombre_mapa',
        'ruta_mapa',//ruta de la imagen del mapa
        'ruta_canvas',//ruta del canvas del mapa
        'piso_id',
    ];
    public function piso()
    {
        return $this->belongsTo(Piso::class, 'piso_id', 'id');
    }
    public function bloques()
{
    return $this->hasMany(Bloque::class, 'id_mapa');
}
}
