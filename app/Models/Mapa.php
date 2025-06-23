<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
<<<<<<< HEAD
=======
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
>>>>>>> Nperez

class Mapa extends Model
{
    use HasFactory;
<<<<<<< HEAD
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
<<<<<<< HEAD
=======

    protected $primaryKey = 'id_mapa';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id_mapa',
        'nombre_mapa',
        'ruta_mapa',
        'ruta_canvas',
        'piso_id'
    ];

    public function piso(): BelongsTo
    {
        return $this->belongsTo(Piso::class, 'piso_id');
    }

    public function bloques(): HasMany
    {
        return $this->hasMany(Bloque::class, 'id_mapa', 'id_mapa');
    }
>>>>>>> Nperez
=======
>>>>>>> 6c05e560f5edb88b89bd0fe7d8d71ecb8386c841
}
