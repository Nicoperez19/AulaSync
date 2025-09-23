<?php

namespace App\Traits;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\File;

trait SafeCacheTrait
{
    /**
     * Almacenar un valor en caché de forma segura
     *
     * @param string $key
     * @param mixed $value
     * @param int $ttl Tiempo de vida en segundos
     * @return bool
     */
    protected function safeCache($key, $value, $ttl = 3600)
    {
        try {
            // Verificar que el directorio de caché existe
            $this->ensureCacheDirectoryExists();
            
            return Cache::put($key, $value, $ttl);
        } catch (\Exception $e) {
            Log::warning("Error al guardar en caché la clave '{$key}': " . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtener un valor del caché de forma segura
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    protected function safeGet($key, $default = null)
    {
        try {
            return Cache::get($key, $default);
        } catch (\Exception $e) {
            Log::warning("Error al obtener del caché la clave '{$key}': " . $e->getMessage());
            return $default;
        }
    }

    /**
     * Recordar un valor en caché de forma segura
     *
     * @param string $key
     * @param int $ttl
     * @param callable $callback
     * @return mixed
     */
    protected function safeRemember($key, $ttl, $callback)
    {
        try {
            // Verificar que el directorio de caché existe
            $this->ensureCacheDirectoryExists();
            
            return Cache::remember($key, $ttl, $callback);
        } catch (\Exception $e) {
            Log::warning("Error al recordar en caché la clave '{$key}': " . $e->getMessage());
            
            // Si falla el caché, ejecutar el callback directamente
            try {
                return $callback();
            } catch (\Exception $callbackException) {
                Log::error("Error al ejecutar callback para la clave '{$key}': " . $callbackException->getMessage());
                return null;
            }
        }
    }

    /**
     * Olvida un valor del caché de forma segura
     *
     * @param string $key
     * @return bool
     */
    protected function safeForget($key)
    {
        try {
            return Cache::forget($key);
        } catch (\Exception $e) {
            Log::warning("Error al olvidar del caché la clave '{$key}': " . $e->getMessage());
            return false;
        }
    }

    /**
     * Limpiar caché de forma segura con patrón
     *
     * @param string $pattern
     * @return bool
     */
    protected function safeClearPattern($pattern)
    {
        try {
            // Si estamos usando file cache, podemos limpiar por patrón
            if (config('cache.default') === 'file') {
                $cacheDir = storage_path('framework/cache/data');
                if (File::exists($cacheDir)) {
                    $files = File::allFiles($cacheDir);
                    foreach ($files as $file) {
                        if (strpos($file->getFilename(), $pattern) !== false) {
                            File::delete($file->getRealPath());
                        }
                    }
                }
            }
            return true;
        } catch (\Exception $e) {
            Log::warning("Error al limpiar caché con patrón '{$pattern}': " . $e->getMessage());
            return false;
        }
    }

    /**
     * Asegurar que el directorio de caché existe
     *
     * @return void
     */
    private function ensureCacheDirectoryExists()
    {
        $cacheDir = storage_path('framework/cache/data');
        
        if (!File::exists($cacheDir)) {
            try {
                File::makeDirectory($cacheDir, 0755, true);
            } catch (\Exception $e) {
                Log::warning("No se pudo crear el directorio de caché: " . $e->getMessage());
            }
        }
    }

    /**
     * Verificar la salud del sistema de caché
     *
     * @return array
     */
    protected function checkCacheHealth()
    {
        $health = [
            'status' => 'ok',
            'writable' => false,
            'space_available' => false,
            'errors' => []
        ];

        try {
            // Verificar si podemos escribir en el directorio de caché
            $testKey = 'cache_health_test_' . time();
            $testValue = 'test_value';
            
            if ($this->safeCache($testKey, $testValue, 60)) {
                $health['writable'] = true;
                $this->safeForget($testKey);
            } else {
                $health['errors'][] = 'No se puede escribir en caché';
            }

            // Verificar espacio disponible
            $cacheDir = storage_path('framework/cache');
            if (File::exists($cacheDir)) {
                $freeBytes = disk_free_space($cacheDir);
                $health['space_available'] = $freeBytes > (50 * 1024 * 1024); // 50MB mínimo
                
                if (!$health['space_available']) {
                    $health['errors'][] = 'Espacio insuficiente en disco';
                }
            }

            // Determinar estado general
            if (!empty($health['errors'])) {
                $health['status'] = 'warning';
            }

            if (!$health['writable']) {
                $health['status'] = 'error';
            }

        } catch (\Exception $e) {
            $health['status'] = 'error';
            $health['errors'][] = $e->getMessage();
        }

        return $health;
    }

    /**
     * Limpiar caché relacionado con espacios
     *
     * @param string|null $espacioId
     * @return bool
     */
    protected function clearEspacioCache($espacioId = null)
    {
        try {
            if ($espacioId) {
                // Limpiar caché específico del espacio
                $patterns = [
                    "espacio_{$espacioId}",
                    "informacion_detallada_{$espacioId}",
                    "estado_espacio_{$espacioId}",
                    "reservas_espacio_{$espacioId}"
                ];

                foreach ($patterns as $pattern) {
                    $this->safeForget($pattern);
                }
            } else {
                // Limpiar todo el caché de espacios
                $this->safeClearPattern('espacio_');
                $this->safeClearPattern('informacion_detallada_');
                $this->safeClearPattern('estado_espacio_');
                $this->safeClearPattern('reservas_espacio_');
            }

            return true;
        } catch (\Exception $e) {
            Log::warning("Error al limpiar caché de espacios: " . $e->getMessage());
            return false;
        }
    }
}