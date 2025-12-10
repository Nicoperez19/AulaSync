<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tenant extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'domain',
        'database',
        'prefijo_espacios',
        'sede_id',
        'is_active',
        'is_default',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_default' => 'boolean',
    ];

    /**
     * Relación con la sede
     */
    public function sede()
    {
        return $this->belongsTo(Sede::class, 'sede_id', 'id_sede');
    }

    /**
     * Obtener el tenant actual basado en el subdominio
     */
    public static function current()
    {
        return app('tenant');
    }

    /**
     * Establecer este tenant como el actual
     */
    public function makeCurrent()
    {
        app()->instance('tenant', $this);
        
        // Cambiar a la base de datos del tenant si está configurada
        if ($this->database) {
            config([
                'database.connections.tenant.database' => $this->database,
            ]);
            
            // Cambiar la conexión predeterminada a la del tenant
            app('db')->purge('tenant');
        }
        
        return $this;
    }

    /**
     * Verificar si este tenant está activo
     */
    public function isActive()
    {
        return $this->is_active;
    }
}
