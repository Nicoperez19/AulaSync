<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Facultad extends Model
{
    use HasFactory;
    protected $table = 'facultades';

    protected $primaryKey = 'id_facultad';

    protected $fillable = [
        'id_facultad',
        'logo_facultad',
        'nombre_facultad',
        'ubicacion_facultad',
        'id_universidad',
    ];

    public function universidad()
    {
        return $this->belongsTo(Universidad::class, 'id_universidad');
    }

    public function areaAcademicas()
    {
        return $this->hasMany(AreaAcademica::class, 'id_facultad');
    }

    public function carreras()
    {
        return $this->hasMany(Carrera::class, 'id_facultad');
    }

    public function obtenerPisos()
    {
        return $this->hasMany(Piso::class, 'id_facultad');
    }
}
