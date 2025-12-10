<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Campus extends Model
{
    use HasFactory;
    protected $connection = 'tenant';
    protected $primaryKey = 'id_campus';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = [
        'id_campus',
        'nombre_campus',
        'id_sede',
    ];

    public function sede()
    {
        return $this->belongsTo(Sede::class, 'id_sede', 'id_sede');
    }

    public function facultades()
    {
        return $this->hasMany(Facultad::class, 'id_campus', 'id_campus');
    }
}
