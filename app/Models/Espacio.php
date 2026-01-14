<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Services\QRService;
use App\Traits\BelongsToTenant;

class Espacio extends Model
{
    use HasFactory, BelongsToTenant;

    protected $connection = 'tenant';
    protected $primaryKey = 'id_espacio';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id_espacio',
        'nombre_espacio',
        'piso_id',
        'tipo_espacio',
        'estado',
        'qr_espacio',
        'puestos_disponibles',
        'capacidad_maxima'
    ];

    /**
     * Obtener la capacidad utilizada del espacio
     * Calculada como: capacidad_maxima - puestos_disponibles
     */
    public function getCapacidadUtilizadaAttribute()
    {
        if (is_null($this->capacidad_maxima) || is_null($this->puestos_disponibles)) {
            return 0;
        }
        return max(0, $this->capacidad_maxima - $this->puestos_disponibles);
    }

    /**
     * Obtener el porcentaje de ocupación del espacio
     */
    public function getPorcentajeOcupacionAttribute()
    {
        if (is_null($this->capacidad_maxima) || $this->capacidad_maxima == 0) {
            return 0;
        }
        return round(($this->capacidad_utilizada / $this->capacidad_maxima) * 100, 1);
    }

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
}
