<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class IncidenciaEspacio extends Model
{
    use HasFactory, BelongsToTenant;

    protected $connection = 'tenant';
    protected $table = 'incidencias_espacios';

    /**
     * Catálogo de categorías de falla.
     * `bloquea_por_defecto`: si la falla normalmente impide usar la sala (se puede cambiar al reportar).
     */
    public const CATEGORIAS = [
        'proyector'       => ['label' => 'Proyector / audiovisual',      'icon' => 'fa-video',            'bloquea_por_defecto' => true],
        'climatizacion'   => ['label' => 'Aire acondicionado / calefacción', 'icon' => 'fa-fan',          'bloquea_por_defecto' => false],
        'mobiliario'      => ['label' => 'Sillas / mesas / pizarra',     'icon' => 'fa-chair',            'bloquea_por_defecto' => false],
        'insumos'         => ['label' => 'Falta de insumos (plumones, borrador)', 'icon' => 'fa-pen',     'bloquea_por_defecto' => false],
        'iluminacion'     => ['label' => 'Iluminación',                  'icon' => 'fa-lightbulb',        'bloquea_por_defecto' => false],
        'electricidad'    => ['label' => 'Electricidad / enchufes',      'icon' => 'fa-plug',             'bloquea_por_defecto' => true],
        'conectividad'    => ['label' => 'Internet / red',               'icon' => 'fa-wifi',             'bloquea_por_defecto' => false],
        'computadores'    => ['label' => 'Computadores',                 'icon' => 'fa-computer',         'bloquea_por_defecto' => false],
        'infraestructura' => ['label' => 'Infraestructura (puertas, ventanas, techo)', 'icon' => 'fa-door-open', 'bloquea_por_defecto' => true],
        'limpieza'        => ['label' => 'Limpieza',                     'icon' => 'fa-broom',            'bloquea_por_defecto' => false],
        'otro'            => ['label' => 'Otro',                         'icon' => 'fa-circle-question',  'bloquea_por_defecto' => false],
    ];

    public const SEVERIDADES = [
        'baja'    => 'Baja',
        'media'   => 'Media',
        'alta'    => 'Alta',
        'critica' => 'Crítica',
    ];

    public const ESTADOS = [
        'abierta'    => 'Abierta',
        'en_proceso' => 'En proceso',
        'resuelta'   => 'Resuelta',
        'descartada' => 'Descartada',
    ];

    /** Estados en los que la incidencia sigue pendiente. */
    public const ESTADOS_ABIERTOS = ['abierta', 'en_proceso'];

    protected $fillable = [
        'id_espacio',
        'categoria',
        'severidad',
        'descripcion',
        'bloquea_uso',
        'estado_espacio_previo',
        'estado',
        'reportado_por_run',
        'reportado_por_nombre',
        'reportado_en',
        'asignado_a',
        'resolucion',
        'resuelto_por_run',
        'resuelto_por_nombre',
        'resuelto_en',
    ];

    protected $casts = [
        'bloquea_uso'  => 'boolean',
        'reportado_en' => 'datetime',
        'resuelto_en'  => 'datetime',
    ];

    /** BD de sede en las que ya se verificó que las tablas existen (evita consultar el esquema en cada llamada). */
    protected static array $tablasVerificadas = [];

    /**
     * Garantiza que las tablas de incidencias existan en la BD de la sede activa.
     *
     * Las migraciones de tenant solo se aplican a las sedes en las que alguien ejecutó `tenants:migrate`;
     * si una sede quedó atrás, el tablero y el reporte fallaban solo en esa sede. Esto crea las tablas
     * la primera vez que se usan (idempotente) y es equivalente a la migración 2026_10_28_000001.
     */
    public static function asegurarTablas(): void
    {
        $conexion = config('database.connections.tenant');
        $clave = ($conexion['host'] ?? '') . '|' . ($conexion['database'] ?? '');

        if (empty($conexion['database'])) {
            return; // Sin sede activa no hay BD de tenant donde crear nada.
        }

        if (isset(static::$tablasVerificadas[$clave])) {
            return;
        }

        $schema = Schema::connection('tenant');

        if (! $schema->hasTable('incidencias_espacios')) {
            $schema->create('incidencias_espacios', function (Blueprint $table) {
                $table->id();
                $table->string('id_espacio')->index();
                $table->string('categoria', 40)->index();
                $table->string('severidad', 10)->default('media')->index();
                $table->text('descripcion');
                $table->boolean('bloquea_uso')->default(false)->index();
                $table->string('estado_espacio_previo', 20)->nullable();
                $table->string('estado', 15)->default('abierta')->index();
                $table->string('reportado_por_run', 20)->nullable()->index();
                $table->string('reportado_por_nombre')->nullable();
                $table->timestamp('reportado_en')->useCurrent()->index();
                $table->string('asignado_a')->nullable();
                $table->text('resolucion')->nullable();
                $table->string('resuelto_por_run', 20)->nullable();
                $table->string('resuelto_por_nombre')->nullable();
                $table->timestamp('resuelto_en')->nullable();
                $table->timestamps();

                $table->index(['id_espacio', 'estado']);
                $table->index(['estado', 'bloquea_uso']);
            });
        }

        if (! $schema->hasTable('incidencias_espacios_eventos')) {
            $schema->create('incidencias_espacios_eventos', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('incidencia_id')->index();
                $table->string('tipo', 30);
                $table->string('estado_anterior', 15)->nullable();
                $table->string('estado_nuevo', 15)->nullable();
                $table->text('detalle')->nullable();
                $table->string('usuario_run', 20)->nullable();
                $table->string('usuario_nombre')->nullable();
                $table->timestamps();

                $table->foreign('incidencia_id')->references('id')->on('incidencias_espacios')->onDelete('cascade');
            });
        }

        static::$tablasVerificadas[$clave] = true;
    }

    // Relaciones

    public function espacio()
    {
        return $this->belongsTo(Espacio::class, 'id_espacio', 'id_espacio');
    }

    public function eventos()
    {
        return $this->hasMany(IncidenciaEspacioEvento::class, 'incidencia_id')->orderBy('created_at')->orderBy('id');
    }

    // Accesores

    public function getCategoriaLabelAttribute(): string
    {
        return self::CATEGORIAS[$this->categoria]['label'] ?? ucfirst($this->categoria);
    }

    public function getCategoriaIconAttribute(): string
    {
        return self::CATEGORIAS[$this->categoria]['icon'] ?? 'fa-circle-question';
    }

    public function getSeveridadLabelAttribute(): string
    {
        return self::SEVERIDADES[$this->severidad] ?? ucfirst($this->severidad);
    }

    public function getEstadoLabelAttribute(): string
    {
        return self::ESTADOS[$this->estado] ?? ucfirst($this->estado);
    }

    public function estaAbierta(): bool
    {
        return in_array($this->estado, self::ESTADOS_ABIERTOS, true);
    }

    /** Horas transcurridas hasta la resolución (o hasta ahora si sigue abierta). */
    public function getHorasAbiertaAttribute(): float
    {
        $fin = $this->resuelto_en ?? now();

        return round($this->reportado_en->diffInMinutes($fin) / 60, 1);
    }

    // Scopes

    public function scopeAbiertas($query)
    {
        return $query->whereIn('estado', self::ESTADOS_ABIERTOS);
    }

    public function scopeBloqueantes($query)
    {
        return $query->where('bloquea_uso', true);
    }

    public function scopePorEspacio($query, $idEspacio)
    {
        return $query->where('id_espacio', $idEspacio);
    }
}
