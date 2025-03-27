<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Universidad extends Model
{
    use HasFactory;

    protected $primaryKey = 'id_universidad';

    protected $fillable = [
        'id_universidad',
        'nombre_universidad',
        'direccion_universidad',
        'telefono_universidad',
        'id_comuna',
    ];

    public function comuna()
    {
        return $this->belongsTo(Comuna::class, 'id_comuna');
    }
}
