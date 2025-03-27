<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reserva extends Model
{
    use HasFactory;

    protected $table = 'reservas'; 

    protected $primaryKey = 'id_reserva'; 

    protected $fillable = [
        'id_reserva', 
        'hora', 
        'fecha_reserva', 
        'id_espacio', 
        'id' //id del usuario, que quedo con ese nombre.
    ]; 

    public function espacio()
    {
        return $this->belongsTo(Espacio::class, 'id_espacio', 'id_espacio'); 
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'id', 'id'); 
    }
}
