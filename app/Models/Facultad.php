<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Facultad extends Model
{
 
    use HasFactory;
    protected $primaryKey = 'id_facultad';

    protected $fillable = [
        'id_facultad',
        'nombre',
        'ubicacion',
        'id_universidad',
    ];

    public function universidad()
    {
        return $this->belongsTo(Universidad::class, 'id_universidad');
    }
}

