<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Universidad extends Model
{
    use HasFactory;
    protected $table = 'universidades'; 
    public $incrementing = false; // No se usará el auto-incremento
    protected $keyType = 'string'; // El tipo de la clave primaria es string (no entero)
    protected $primaryKey = 'id_universidad';

    protected $fillable = [
        'nombre_universidad',
        'direccion_universidad',
        'telefono_universidad',
        'id_comuna',
        'imagen_logo',
    ];

    public function comuna()
    {
        return $this->belongsTo(Comuna::class, 'comunas_id'); // Cambiar 'id_comuna' por 'comunas_id'
    }
    public function facultades()
    {
        return $this->hasMany(Facultad::class, 'id_universidad');
    }
}
