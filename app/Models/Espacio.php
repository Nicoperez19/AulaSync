<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Espacio extends Model
{
    use HasFactory;
    protected $primaryKey = 'id_espacio';

    protected $fillable = [
        'id_espacio',
        'id_piso',
        'tipo_espacio',
        'estado',
        'puestos_disponibles',
    ];

    public function piso()
    {
        return $this->belongsTo(Piso::class, 'piso_id');
    }

    public function facultad()
    {
        return $this->belongsTo(Facultad::class, 'facultad_id');
    }

    public function universidad()
    {
        return $this->belongsToThrough(Universidad::class, Facultad::class);
    }

   
}
