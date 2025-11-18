<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AsistenteAcademico extends Model
{
    use HasFactory;

    protected $table = 'asistentes_academicos';

    protected $fillable = [
        'nombre',
        'email',
        'nombre_remitente',
        'telefono',
        'id_area_academica',
    ];

    public function areaAcademica()
    {
        return $this->belongsTo(AreaAcademica::class, 'id_area_academica', 'id_area_academica');
    }
}
