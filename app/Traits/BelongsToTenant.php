<?php

namespace App\Traits;

use App\Models\Tenant;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Schema;

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
    protected static function bootBelongsToTenant()
    {
        // Aplicar scope global para filtrar por tenant
        static::addGlobalScope('tenant', function (Builder $builder) {
            $tenant = Tenant::current();
            
            if (!$tenant) {
                return;
            }
            
            $model = new static;
            $table = $model->getTable();
            
            // Filtrar por prefijo de espacio si el modelo tiene id_espacio
            if (Schema::hasColumn($table, 'id_espacio')) {
                if ($tenant->prefijo_espacios) {
                    $builder->where('id_espacio', 'like', $tenant->prefijo_espacios . '%');
                }
            }
            
            // Filtrar por sede directamente si el modelo tiene sede_id
            if (Schema::hasColumn($table, 'sede_id')) {
                if ($tenant->sede_id) {
                    $builder->where('sede_id', $tenant->sede_id);
                }
            }
            // Filtrar a través de profesor si el modelo tiene relación con profesor y run_profesor
            elseif (method_exists($model, 'profesor') && Schema::hasColumn($table, 'run_profesor')) {
                if ($tenant->sede_id) {
                    $builder->whereHas('profesor', function ($query) use ($tenant) {
                        $query->where('sede_id', $tenant->sede_id);
                    });
                }
            }
            // Filtrar a través de espacio si el modelo tiene relación con espacio pero no tiene id_espacio directamente
            elseif (method_exists($model, 'espacio') && !Schema::hasColumn($table, 'id_espacio')) {
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
            
            // Si el modelo tiene sede_id, asignarla
            if (Schema::hasColumn($table, 'sede_id') && !$model->sede_id) {
                $model->sede_id = $tenant->sede_id;
            }
            
            // Si el modelo tiene id_espacio y prefijo, asegurarse de que comience con el prefijo
            if (Schema::hasColumn($table, 'id_espacio') && $tenant->prefijo_espacios) {
                if (isset($model->id_espacio) && !str_starts_with($model->id_espacio, $tenant->prefijo_espacios)) {
                    $model->id_espacio = $tenant->prefijo_espacios . $model->id_espacio;
                }
            }
        });
    }

    /**
     * Obtener el tenant actual
     */
    public function tenant()
    {
        return Tenant::current();
    }
}
