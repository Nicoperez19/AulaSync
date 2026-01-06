<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToTenant;

class AreaAcademica extends Model
{
    use HasFactory, BelongsToTenant;

    protected $connection = 'tenant';

    protected $table = 'area_academicas';
    protected $primaryKey = 'id_area_academica';

    public $incrementing = false; 
    protected $keyType = 'string'; 
    protected $fillable = [
        'id_area_academica',
        'nombre_area_academica',
        'tipo_area_academica',
        'id_facultad',
    ];

    public function facultad()
    {
        return $this->belongsTo(Facultad::class, 'id_facultad');
    }
      public function carreras()
    {
        return $this->hasMany(Carrera::class, 'id_area_academica', 'id_area_academica');
    }

    public function asistenteAcademico()
    {
        return $this->hasOne(AsistenteAcademico::class, 'id_area_academica', 'id_area_academica');
    }
}
