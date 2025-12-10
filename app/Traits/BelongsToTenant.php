<?php

namespace App\Traits;

use App\Models\Tenant;
use Illuminate\Database\Eloquent\Builder;

trait BelongsToTenant
{
    /**
     * Boot the trait
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
            
            // Filtrar por prefijo de espacio si el modelo tiene id_espacio
            if (in_array('id_espacio', $model->getFillable())) {
                if ($tenant->prefijo_espacios) {
                    $builder->where('id_espacio', 'like', $tenant->prefijo_espacios . '%');
                }
            }
            
            // Filtrar por sede directamente si el modelo tiene sede_id
            if (in_array('sede_id', $model->getFillable()) || property_exists($model, 'sede_id')) {
                if ($tenant->sede_id) {
                    $builder->where('sede_id', $tenant->sede_id);
                }
            }
            // Filtrar a través de profesor si el modelo tiene relación con profesor
            elseif (method_exists($model, 'profesor') && in_array('run_profesor', $model->getFillable())) {
                if ($tenant->sede_id) {
                    $builder->whereHas('profesor', function ($query) use ($tenant) {
                        $query->where('sede_id', $tenant->sede_id);
                    });
                }
            }
            // Filtrar a través de espacio si el modelo tiene relación con espacio
            elseif (method_exists($model, 'espacio') && !in_array('id_espacio', $model->getFillable())) {
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
            
            // Si el modelo tiene sede_id, asignarla
            if ((in_array('sede_id', $model->getFillable()) || property_exists($model, 'sede_id')) && !$model->sede_id) {
                $model->sede_id = $tenant->sede_id;
            }
            
            // Si el modelo tiene id_espacio y prefijo, asegurarse de que comience con el prefijo
            if (in_array('id_espacio', $model->getFillable()) && $tenant->prefijo_espacios) {
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
