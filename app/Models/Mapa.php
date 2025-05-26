<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Mapa extends Model
{
    use HasFactory;

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
}
