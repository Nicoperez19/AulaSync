<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Llave extends Model
{
    use HasFactory;

    protected $table = 'llaves';

    protected $fillable = [
        'codigo_qr',
        'id_espacio',
        'estado',
    ];
    public function espacio()
    {
        return $this->belongsTo(Espacio::class, 'id_espacio', 'id_espacio');
    }
    public function usos()
    {
        return $this->hasMany(UsoEspacio::class);
    }
}
