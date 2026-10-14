<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sede extends Model
{
    use HasFactory;

    protected $connection = 'mysql';

    protected $primaryKey = 'id_sede';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'id_sede',
        'nombre_sede',
        'prefijo_sala',
        'id_universidad',
        'comunas_id',
        'logo',
        'descripcion',
        'direccion',
        'telefono',
        'email',
    ];

    public function universidad(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Universidad::class, 'id_universidad', 'id_universidad');
    }

    public function comuna(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Comuna::class, 'comunas_id');
    }

    public function facultades(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Facultad::class, 'id_sede', 'id_sede');
    }

    public function campuses(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Campus::class, 'id_sede', 'id_sede');
    }

    /**
     * Obtener el tenant asociado a esta sede
     */
    public function tenant(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(Tenant::class, 'sede_id', 'id_sede');
    }

    /**
     * Obtener la URL del logo de la sede
     */
    public function getLogoUrl()
    {
        if ($this->logo) {
            return asset('storage/sedes/logos/'.$this->logo);
        }

        return asset('images/logo_default.png');
    }
}
