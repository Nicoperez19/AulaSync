<?php

namespace App\Models\Scopes;

use App\Models\Tenant;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class TenantScope implements Scope
{
    /**
     * Cache estático de columnas por tabla y clase para optimizar rendimiento.
     */
    protected static array $columnCache = [];

    /**
     * Verificar si una columna existe en la tabla usando caché persistente y en memoria.
     */
    protected function hasColumnCached(Model $model, string $table, string $column): bool
    {
        $connectionName = $model->getConnectionName() ?: config('database.default');
        $cacheKey = "tenant_scope_cols_{$connectionName}_{$table}";

        if (!isset(static::$columnCache[$cacheKey])) {
            static::$columnCache[$cacheKey] = \Illuminate\Support\Facades\Cache::remember(
                $cacheKey,
                86400, // 24 horas
                function () use ($model, $table) {
                    try {
                        return $model->getConnection()->getSchemaBuilder()->getColumnListing($table);
                    } catch (\Throwable $e) {
                        return [];
                    }
                }
            );
        }

        return in_array($column, static::$columnCache[$cacheKey]);
    }

    /**
     * Aplicar el Global Scope del tenant a la consulta Eloquent.
     */
    public function apply(Builder $builder, Model $model): void
    {
        $tenant = Tenant::current();

        if (!$tenant) {
            return;
        }

        $table = $model->getTable();

        $hasColumn = fn(string $col) => $this->hasColumnCached($model, $table, $col);

        // 1. Si la tabla tiene tenant_id o id_tenant directo
        if ($hasColumn('tenant_id')) {
            $builder->where($table . '.tenant_id', $tenant->id);
            return;
        }
        if ($hasColumn('id_tenant')) {
            $builder->where($table . '.id_tenant', $tenant->id);
            return;
        }

        // 2. Si el modelo se filtra por prefijo de espacio (ej. TH-...)
        if ($hasColumn('id_espacio') && $tenant->prefijo_espacios) {
            $builder->whereRaw('LOWER(' . $table . '.id_espacio) LIKE ?', [strtolower($tenant->prefijo_espacios) . '%']);
            return;
        }

        // 3. Si el modelo tiene sede_id o id_sede directa
        if ($hasColumn('sede_id') && $tenant->sede_id) {
            $builder->where($table . '.sede_id', $tenant->sede_id);
            return;
        }
        if ($hasColumn('id_sede') && $tenant->sede_id) {
            $builder->where($table . '.id_sede', $tenant->sede_id);
            return;
        }

        // 4. Filtrar por relación con profesor (si tiene run_profesor)
        if (method_exists($model, 'profesor') && $hasColumn('run_profesor') && $tenant->sede_id) {
            $builder->whereHas('profesor', function ($query) use ($tenant) {
                $query->where('sede_id', $tenant->sede_id);
            });
            return;
        }

        // 5. Filtrar por relación con espacio si no tiene id_espacio directo
        if (method_exists($model, 'espacio') && !$hasColumn('id_espacio')) {
            if ($tenant->prefijo_espacios) {
                $builder->whereHas('espacio', function ($query) use ($tenant) {
                    $query->whereRaw('LOWER(espacios.id_espacio) LIKE ?', [strtolower($tenant->prefijo_espacios) . '%']);
                });
            }
            return;
        }

        // 6. Filtrar por relación con facultad directa
        if (method_exists($model, 'facultad') && $tenant->sede_id) {
            $builder->whereHas('facultad', function ($query) use ($tenant) {
                $query->where('id_sede', $tenant->sede_id);
            });
            return;
        }

        // 7. Filtrar por relación con área académica -> facultad
        if (method_exists($model, 'areaAcademica') && $tenant->sede_id) {
            $builder->whereHas('areaAcademica.facultad', function ($query) use ($tenant) {
                $query->where('id_sede', $tenant->sede_id);
            });
            return;
        }

        // 8. Filtrar por relación con carrera -> área académica -> facultad
        if (method_exists($model, 'carrera') && $tenant->sede_id) {
            $builder->whereHas('carrera.areaAcademica.facultad', function ($query) use ($tenant) {
                $query->where('id_sede', $tenant->sede_id);
            });
            return;
        }

        // 9. Filtrar por relación con piso -> facultad
        if (method_exists($model, 'piso') && $tenant->sede_id) {
            $builder->whereHas('piso.facultad', function ($query) use ($tenant) {
                $query->where('id_sede', $tenant->sede_id);
            });
            return;
        }

        // 10. Filtrar por relación con reserva origen (ej. vetos)
        if (method_exists($model, 'reservaOrigen') && $tenant->prefijo_espacios) {
            $builder->whereHas('reservaOrigen', function ($query) use ($tenant) {
                $query->whereRaw('LOWER(reservas.id_espacio) LIKE ?', [strtolower($tenant->prefijo_espacios) . '%']);
            });
            return;
        }
    }
}
