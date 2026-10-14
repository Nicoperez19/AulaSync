<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AreaAcademica extends Model
{
    use BelongsToTenant, HasFactory;

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

    public function facultad(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Facultad::class, 'id_facultad');
    }

    public function carreras(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Carrera::class, 'id_area_academica', 'id_area_academica');
    }

    public function asistenteAcademico(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(AsistenteAcademico::class, 'id_area_academica', 'id_area_academica');
    }
}
