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
        'nombre_facultad',
        'ubicacion_facultad',
        'logo_facultad',
        'id_sede',
        'id_campus',
    ];

    public function sede() {
        return $this->belongsTo(Sede::class, 'id_sede');
    }
    
    public function campus() {
        return $this->belongsTo(Campus::class, 'id_campus');
    }

    public function areaAcademicas()
    {
        return $this->hasMany(AreaAcademica::class, 'id_facultad');
    }
    public function pisos()
    {
        return $this->hasMany(Piso::class, 'id_facultad');
    }
}
