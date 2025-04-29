<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sede extends Model
{
    use HasFactory;

    protected $table = 'sedes';
    protected $primaryKey = 'id_sede';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id_sede',
        'nombre_sede',
        'direccion_sede',
        'comunas_id',
    ];

    public function campus()
    {
        return $this->hasMany(Campus::class, 'id_sede');
    }

    public function facultades()
    {
        return $this->hasMany(Facultad::class, 'id_sede');
    }
}
