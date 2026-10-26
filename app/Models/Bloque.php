<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bloque extends Model
{
    use HasFactory;
    protected $connection = 'tenant';
    protected $table = 'bloques';
    protected $primaryKey = 'id_bloque';
    public $incrementing = false;
    protected $keyType = 'string';
    
    protected $fillable = [
        'id_bloque',
        'id_espacio',
        'posicion_x',
        'posicion_y',
        'estado',
        'id_mapa',
    ];

    public function mapa()
    {
        return $this->belongsTo(Mapa::class, 'id_mapa');
    }

    public function espacio()
    {
        return $this->belongsTo(Espacio::class, 'id_espacio');
    }
}
