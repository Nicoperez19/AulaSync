<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToTenant;

class Piso extends Model
{
    use HasFactory, BelongsToTenant;
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
     * Obtener el nombre del piso (si no tiene nombre_piso, generar uno automático)
     */
    public function getDisplayNameAttribute()
    {
        return $this->nombre_piso ?? 'Piso ' . $this->numero_piso;
    }
}
