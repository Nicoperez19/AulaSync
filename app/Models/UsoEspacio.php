<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UsoEspacio extends Model
{
    use HasFactory;

    protected $fillable = [
        'llave_id',
        'run',
        'id_espacio',
        'inicio_uso',
        'fin_uso',
        'estado',
    ];

 
    public function llave()
    {
        return $this->belongsTo(Llave::class);
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'run', 'run');
    }

    public function espacio()
    {
        return $this->belongsTo(Espacio::class, 'id_espacio', 'id_espacio');
    }
}
