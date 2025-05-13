<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DataLoad extends Model
{
    use HasFactory;

    protected $fillable = [
        'nombre_archivo',
        'ruta_archivo',
        'tipo_carga',
        'registros_cargados',
        'estado',
        'observaciones',
        'user_id'
    ];

    protected $casts = [
        'registros_cargados' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
