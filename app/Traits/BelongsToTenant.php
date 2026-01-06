<?php

namespace App\Traits;

use App\Models\Tenant;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Config;

trait BelongsToTenant
{
    /**
     * Boot the trait
     * 
     * Note: Schema::hasColumn() is called on each query, which can be expensive.
     * For production, enable Laravel's schema caching with:
     * php artisan schema:cache
     * 
     * This caches column information and significantly improves performance.
     */
    public static function bootBelongsToTenant()
    {
        // Cambiar conexión a tenant si hay base de datos separada
        static::creating(function ($model) {
            $model->setTenantConnection();
        });
        
        // Aplicar scope global para filtrar por tenant
        static::addGlobalScope('tenant', function (Builder $builder) {
            $tenant = Tenant::current();
            
            if (!$tenant) {
                return;
            }
            
            $model = new static;
            $table = $model->getTable();
            $connection = $model->getConnectionName(); // Obtener nombre conexión
            // Helper para verificar columnas usando la conexión del modelo (importante para tenants)
            $hasColumn = function($column) use ($model, $table) {
               return $model->getConnection()->getSchemaBuilder()->hasColumn($table, $column);
            };
            
            // Filtrar por prefijo de espacio si el modelo tiene id_espacio
            if ($hasColumn('id_espacio')) {
                if ($tenant->prefijo_espacios) {
                    $builder->where('id_espacio', 'like', $tenant->prefijo_espacios . '%');
                }
            }
            
            // Filtrar por sede directamente si el modelo tiene sede_id
            if ($hasColumn('sede_id')) {
                if ($tenant->sede_id) {
                    $builder->where('sede_id', $tenant->sede_id);
                }
            }
            // Filtrar a través de profesor si el modelo tiene relación con profesor y run_profesor
            elseif (method_exists($model, 'profesor') && $hasColumn('run_profesor')) {
                if ($tenant->sede_id) {
                    $builder->whereHas('profesor', function ($query) use ($tenant) {
                        $query->where('sede_id', $tenant->sede_id);
                    });
                }
            }
            // Filtrar a través de espacio si el modelo tiene relación con espacio pero no tiene id_espacio directamente
            elseif (method_exists($model, 'espacio') && !$hasColumn('id_espacio')) {
                if ($tenant->prefijo_espacios || $tenant->sede_id) {
                    $builder->whereHas('espacio', function ($query) use ($tenant) {
                        if ($tenant->prefijo_espacios) {
                            $query->where('id_espacio', 'like', $tenant->prefijo_espacios . '%');
                        }
                    });
                }
            }
            // Filtrar a través de facultad si el modelo tiene relación con facultad
            elseif (method_exists($model, 'facultad')) {
                if ($tenant->sede_id) {
                    $builder->whereHas('facultad', function ($query) use ($tenant) {
                        $query->where('id_sede', $tenant->sede_id);
                    });
                }
            }
            // Filtrar a través de piso->facultad si el modelo tiene relación con piso
            elseif (method_exists($model, 'piso')) {
                if ($tenant->sede_id) {
                    $builder->whereHas('piso.facultad', function ($query) use ($tenant) {
                        $query->where('id_sede', $tenant->sede_id);
                    });
                }
            }
        });

        // Al crear un nuevo modelo, asignar automáticamente el tenant
        static::creating(function ($model) {
            $tenant = Tenant::current();
            
            if (!$tenant) {
                return;
            }
            
            $table = $model->getTable();
            $schema = $model->getConnection()->getSchemaBuilder();
            
            // Si el modelo tiene sede_id, asignarla
            if ($schema->hasColumn($table, 'sede_id') && !$model->sede_id) {
                $model->sede_id = $tenant->sede_id;
            }
            
            // Si el modelo tiene id_espacio y prefijo, asegurarse de que comience con el prefijo
            if ($schema->hasColumn($table, 'id_espacio') && $tenant->prefijo_espacios) {
                if (isset($model->id_espacio) && !str_starts_with($model->id_espacio, $tenant->prefijo_espacios)) {
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
            // Usar la conexión tenant para las consultas
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
