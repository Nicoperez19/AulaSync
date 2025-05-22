<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Piso extends Model
{
    use HasFactory;
    protected $table = 'pisos'; 
    protected $primaryKey = 'id';  
    public $incrementing = true;  
    protected $keyType = 'int';   

    protected $fillable = [
        'numero_piso',
        'id_facultad',
    ];

    public function facultad()
    {
        return $this->belongsTo(Facultad::class, 'id_facultad');
    }
}
