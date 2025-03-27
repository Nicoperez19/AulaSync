<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Region extends Model
{
    use HasFactory;

    protected $table = 'regions';
    protected $primaryKey = 'id_region';
    protected $fillable = ['nombre_region'];

    public function provincias()
    {
        return $this->hasMany(Provincia::class, 'id_region', 'id_region');
    }    
}
