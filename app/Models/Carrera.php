<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Carrera extends Model
{
    use BelongsToTenant, HasFactory;

    protected $table = 'carreras';

    protected $primaryKey = 'id_carrera';

    protected $fillable = [
        'id_carrera',
        'nombre',
        'id_area_academica',
    ];

    public function areaAcademica(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(AreaAcademica::class, 'id_area_academica');
    }

    public function asignaturas(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Asignatura::class, 'id_carrera', 'id_carrera');
    }

    public function jefeCarrera(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(JefeCarrera::class, 'id_carrera', 'id_carrera');
    }
}
