<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bloque extends Model
{
    use HasFactory;
    protected $table = 'bloques';
    protected $primaryKey = 'id_bloque';
<<<<<<< HEAD
    protected $fillable = [
        'id_bloque',
        'color_bloque',
        'pos_x',
        'pos_y',
        'id_mapa',
    ];
=======
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

>>>>>>> Nperez
    public function mapa()
    {
        return $this->belongsTo(Mapa::class, 'id_mapa');
    }
<<<<<<< HEAD
=======

    public function espacio()
    {
        return $this->belongsTo(Espacio::class, 'id_espacio');
    }
>>>>>>> Nperez
}
