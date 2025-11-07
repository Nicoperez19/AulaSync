<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sede extends Model
{
    use HasFactory;
    protected $primaryKey = 'id_sede';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id_sede',
        'nombre_sede',
        'prefijo_sala',
        'id_universidad',
        'comunas_id',
    ];

    public function universidad()
    {
        return $this->belongsTo(Universidad::class, 'id_universidad', 'id_universidad');
    }

    public function comuna()
    {
        return $this->belongsTo(Comuna::class, 'comunas_id');
    }

    public function facultades()
    {
        return $this->hasMany(Facultad::class, 'id_sede', 'id_sede');
    }

    public function campuses()
    {
        return $this->hasMany(Campus::class, 'id_sede', 'id_sede');
    }
}
