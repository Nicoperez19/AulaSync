<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Universidad extends Model
{
    use HasFactory;
    protected $table = 'universidades'; 
    public $incrementing = false; 
    protected $keyType = 'string'; 
    protected $primaryKey = 'id_universidad';

    protected $fillable = [
        'id_universidad',
        'nombre_universidad',
        'direccion_universidad',
        'telefono_universidad',
        'comunas_id',
        'imagen_logo',
    ];

    public function comuna()
    {
        return $this->belongsTo(Comuna::class, 'comunas_id');
    }
    public function facultades()
    {
        return $this->hasMany(Facultad::class, 'id_universidad');
    }
}
