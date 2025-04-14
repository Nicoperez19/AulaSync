<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Espacio extends Model
{
    use HasFactory;
    protected $primaryKey = 'id_espacio';
    public $incrementing = false; 
    protected $keyType = 'string';
    protected $fillable = [
        'id_espacio',
        'id',
        'tipo_espacio',
        'estado',
        'puestos_disponibles',
    ];

    public function piso()
    {
        return $this->belongsTo(Piso::class, 'id');  // Corregir el campo de clave foránea
    }
    public function reservas()
    {
        return $this->hasMany(Reserva::class, 'id_espacio');
    }
    
}
