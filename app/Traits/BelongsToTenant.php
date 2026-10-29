<?php

namespace App\Traits;

use App\Models\Scopes\TenantScope;
use App\Models\Tenant;
use Illuminate\Support\Facades\Config;

trait BelongsToTenant
{
    /**
     * Cache estático de columnas por tabla y clase para optimizar rendimiento de inserciones.
     */
    protected static array $tenantColumnCache = [];

    /**
     * Verificar si una columna existe en la tabla con caché.
     */
    protected static function hasTenantColumnCached($model, string $table, string $column): bool
    {
        $cacheKey = get_class($model) . '.' . $column;

        if (!isset(static::$tenantColumnCache[$cacheKey])) {
            try {
                static::$tenantColumnCache[$cacheKey] = $model->getConnection()->getSchemaBuilder()->hasColumn($table, $column);
            } catch (\Throwable $e) {
                return false;
            }
        }

        return static::$tenantColumnCache[$cacheKey];
    }

    /**
     * Boot the trait
     */
    public static function bootBelongsToTenant()
    {
        // Registrar el Global Scope formal de Tenant
        static::addGlobalScope(new TenantScope);

        // Cambiar conexión a tenant si hay base de datos separada
        static::creating(function ($model) {
            $model->setTenantConnection();
        });

        // Al crear un nuevo modelo, asignar automáticamente el tenant
        static::creating(function ($model) {
            $tenant = Tenant::current();

            if (!$tenant) {
                return;
            }

            $table = $model->getTable();

            // Usar helper con caché estático
            $hasColumn = fn(string $column) => static::hasTenantColumnCached($model, $table, $column);

            // 1. Asignar tenant_id o id_tenant directo si existe
            if ($hasColumn('tenant_id') && !$model->tenant_id) {
                $model->tenant_id = $tenant->id;
            } elseif ($hasColumn('id_tenant') && !$model->id_tenant) {
                $model->id_tenant = $tenant->id;
            }

            // 2. Asignar sede_id o id_sede si existe
            if ($hasColumn('sede_id') && !$model->sede_id) {
                $model->sede_id = $tenant->sede_id;
            } elseif ($hasColumn('id_sede') && !$model->id_sede) {
                $model->id_sede = $tenant->sede_id;
            }

            // 3. Si el modelo tiene id_espacio y prefijo, asegurarse de que comience con el prefijo
            if ($hasColumn('id_espacio') && $tenant->prefijo_espacios) {
                if (isset($model->id_espacio) && !str_starts_with(strtolower($model->id_espacio), strtolower($tenant->prefijo_espacios))) {
                    $model->id_espacio = $tenant->prefijo_espacios . $model->id_espacio;
                }
            }
        });
    }

    /**
     * Establecer la conexión del tenant si está activa
     */
    public function setTenantConnection()
    {
        $tenant = Tenant::current();
        if ($tenant && Config::get('multitenancy.separate_databases', false)) {
            $this->setConnection('tenant');
        }
        return $this;
    }

    /**
     * Override del método newQuery para usar conexión tenant automáticamente
     */
    public function newQuery()
    {
        $query = parent::newQuery();

        $tenant = Tenant::current();
        if ($tenant && Config::get('multitenancy.separate_databases', false)) {
            $query->getModel()->setConnection('tenant');
        }

        return $query;
    }

    /**
     * Obtener el tenant actual
     */
    public function tenant()
    {
        return Tenant::current();
    }
}
