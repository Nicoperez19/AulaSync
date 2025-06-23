<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Comuna extends Model
{
    use HasFactory;

    protected $table = 'comunas';
    protected $primaryKey = 'id_comuna';
    protected $fillable = ['nombre_comuna', 'id_provincia'];
    public function provincia()
    {
        return $this->belongsTo(Provincia::class, 'provincias_id', 'id_provincia');
    }
}
