<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToTenant;

class Facultad extends Model
{
    use HasFactory, BelongsToTenant;
    
    protected $connection = 'tenant';

    protected $table = 'facultades';
    protected $primaryKey = 'id_facultad';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = [
        'id_facultad',
        'nombre_facultad',
        'id_universidad',
        'id_sede',
        'id_campus',
    ];

    public function sede()
    {
        return $this->belongsTo(Sede::class, 'id_sede');
    }

    public function campus()
    {
        return $this->belongsTo(Campus::class, 'id_campus');
    }

    public function universidad()
    {
        return $this->belongsTo(Universidad::class, 'id_universidad');
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
