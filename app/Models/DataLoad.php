<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DataLoad extends Model
{
    use HasFactory;

    protected $connection = 'tenant';

    protected $fillable = [
        'nombre_archivo',
        'ruta_archivo',
        'tipo_carga',
        'registros_cargados',
        'estado',
        'user_run',
    ];

    protected $casts = [
        'registros_cargados' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_run', 'run');
    }
    
}
