<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Piso extends Model
{
    use HasFactory;
    protected $table = 'pisos'; 
    protected $primaryKey = 'id';  // Cambiamos el nombre del primaryKey
    public $incrementing = true;  // Esto es por defecto en Laravel cuando usas 'id' autoincremental
    protected $keyType = 'int';   // El tipo es 'int' porque el campo id es autoincremental

    protected $fillable = [
        'numero_piso',
        'id_facultad',
    ];

    public function facultad()
    {
        return $this->belongsTo(Facultad::class, 'id_facultad');
    }
}