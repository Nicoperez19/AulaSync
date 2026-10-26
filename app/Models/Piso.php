<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToTenant;

class Piso extends Model
{
    use HasFactory, BelongsToTenant;
    protected $connection = 'tenant';
    protected $table = 'pisos'; 
    protected $primaryKey = 'id';  
    public $incrementing = true;  
    protected $keyType = 'int';   

    protected $fillable = [
        'numero_piso',
        'nombre_piso',
        'id_facultad',
    ];

    public function facultad()
    {
        return $this->belongsTo(Facultad::class, 'id_facultad');
    }
       public function mapas()
    {
        return $this->hasMany(Mapa::class);
    }

    public function espacios()
    {
        return $this->hasMany(Espacio::class);
    }

    /**
     * Accesor para nombre_piso: asegura que Los Ángeles siempre tenga los nombres oficiales de sus edificios
     */
    public function getNombrePisoAttribute($value)
    {
        if ($this->id_facultad === 'IT_LA') {
            if ($this->numero_piso == 1 && (empty($value) || $value === 'Piso 1' || str_contains($value, '1er'))) {
                return 'CAUPOLICÁN 276';
            }
            if ($this->numero_piso == 2 && (empty($value) || $value === 'Piso 2' || $value === 'VILLAGRÁN' || str_contains($value, '220'))) {
                return 'VILLAGRÁN 220';
            }
            if ($this->numero_piso == 3 && (empty($value) || $value === 'Piso 3' || $value === 'VILLAGRÁN' || !str_contains($value, '251'))) {
                return 'VILLAGRÁN 251';
            }
        }
        return $value;
    }

    /**
     * Obtener el nombre del piso (si no tiene nombre_piso, generar uno automático)
     */
    public function getDisplayNameAttribute()
    {
        return $this->nombre_piso ?? 'Piso ' . $this->numero_piso;
    }
}
