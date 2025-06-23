<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
<<<<<<< HEAD
=======
use App\Services\QRService;
>>>>>>> Nperez

class Espacio extends Model
{
    use HasFactory;
    protected $primaryKey = 'id_espacio';
<<<<<<< HEAD
    public $incrementing = false; 
    protected $keyType = 'string';
 
    protected $fillable = [
        'id_espacio',
        'piso_id',
        'tipo_espacio',
        'estado',
        'puestos_disponibles',
    ];

    public function piso()
    {
        return $this->belongsTo(Piso::class, 'piso_id'); 
    }
    public function reservas()
    {
        return $this->hasMany(Reserva::class, 'id_espacio');
    }
    
=======
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id_espacio',
        'nombre_espacio',
        'piso_id',
        'tipo_espacio',
        'estado',
        'qr_espacio',
        'puestos_disponibles'
    ];

    public function reservas()
    {
        return $this->hasMany(Reserva::class, 'id_espacio', 'id_espacio');
    }

    public function piso()
    {
        return $this->belongsTo(Piso::class, 'piso_id');
    }

    public function planificaciones()
    {
        return $this->hasMany(Planificacion_Asignatura::class, 'id_espacio', 'id_espacio');
    }

    public function generateQR()
    {
        $qrService = new QRService();
        $qrFileName = $qrService->generateQRForEspacio($this->id_espacio); // o cualquier valor que desees codificar
        $this->qr_espacio = $qrFileName;
        $this->save();
        return $this;
    }
>>>>>>> Nperez
}
