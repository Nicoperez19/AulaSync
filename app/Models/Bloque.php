<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bloque extends Model
{
    use HasFactory;
    protected $table = 'bloques';
    protected $primaryKey = 'id_bloque';
    protected $fillable = [
        'id_bloque',
        'color_bloque',
        'id_mapa',
    ];
    public function mapa()
    {
        return $this->belongsTo(Mapa::class, 'id_mapa');
    }
}
