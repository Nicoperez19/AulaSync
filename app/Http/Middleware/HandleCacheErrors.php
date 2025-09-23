<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\File;
use Symfony\Component\HttpFoundation\Response;

class HandleCacheErrors
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        try {
            // Verificar que el directorio de caché existe antes de procesar la request
            $this->ensureCacheDirectoryExists();
            
            return $next($request);
            
        } catch (\Exception $e) {
            // Si es un error relacionado con caché, intentar reparar
            if ($this->isCacheRelatedError($e)) {
                Log::warning('Error de caché detectado, intentando reparar', [
                    'error' => $e->getMessage(),
                    'url' => $request->url()
                ]);
                
                $this->repairCacheIssue($e);
                
                // Intentar procesar la request nuevamente
                try {
                    return $next($request);
                } catch (\Exception $secondaryError) {
                    Log::error('Error secundario después de reparación de caché', [
                        'primary_error' => $e->getMessage(),
                        'secondary_error' => $secondaryError->getMessage(),
                        'url' => $request->url()
                    ]);
                    
                    // Si aún falla, retornar respuesta sin caché
                    return $this->handleWithoutCache($request, $next);
                }
            }
            
            // Si no es un error de caché, re-lanzar
            throw $e;
        }
    }

    /**
     * Determinar si el error está relacionado con caché
     */
    private function isCacheRelatedError(\Exception $e): bool
    {
        $cacheErrorPatterns = [
            'file_put_contents',
            'Failed to open stream',
            'No such file or directory',
            'storage/framework/cache',
            'Permission denied'
        ];

        $errorMessage = $e->getMessage();
        
        foreach ($cacheErrorPatterns as $pattern) {
            if (stripos($errorMessage, $pattern) !== false) {
                return true;
            }
        }
        
        return false;
    }

    /**
     * Intentar reparar el problema de caché
     */
    private function repairCacheIssue(\Exception $e): void
    {
        try {
            // 1. Crear directorios faltantes
            $this->ensureCacheDirectoryExists();
            
            // 2. Si es un error de permisos, intentar arreglarlos
            if (stripos($e->getMessage(), 'Permission denied') !== false) {
                $this->fixPermissions();
            }
            
            // 3. Si es un error de directorio faltante, crearlo
            if (stripos($e->getMessage(), 'No such file or directory') !== false) {
                $this->createMissingDirectory($e);
            }
            
        } catch (\Exception $repairError) {
            Log::error('Error al intentar reparar problema de caché: ' . $repairError->getMessage());
        }
    }

    /**
     * Crear directorios de caché faltantes
     */
    private function createMissingDirectory(\Exception $e): void
    {
        // Extraer la ruta del directorio del mensaje de error
        $message = $e->getMessage();
        if (preg_match('/file_put_contents\(([^)]+)\)/', $message, $matches)) {
            $filePath = $matches[1];
            $directory = dirname($filePath);
            
            if (!File::exists($directory)) {
                try {
                    File::makeDirectory($directory, 0755, true);
                    Log::info("Directorio de caché creado: {$directory}");
                } catch (\Exception $createError) {
                    Log::warning("No se pudo crear directorio: {$directory} - " . $createError->getMessage());
                }
            }
        }
    }

    /**
     * Asegurar que los directorios de caché existen
     */
    private function ensureCacheDirectoryExists(): void
    {
        $cacheDirectories = [
            storage_path('framework/cache'),
            storage_path('framework/cache/data'),
            storage_path('framework/sessions'),
            storage_path('framework/views'),
        ];

        foreach ($cacheDirectories as $directory) {
            if (!File::exists($directory)) {
                try {
                    File::makeDirectory($directory, 0755, true);
                } catch (\Exception $e) {
                    // Log pero no fallar
                    Log::warning("No se pudo crear directorio de caché: {$directory} - " . $e->getMessage());
                }
            }
        }
    }

    /**
     * Intentar arreglar permisos de directorio
     */
    private function fixPermissions(): void
    {
        $directories = [
            storage_path('framework/cache'),
            storage_path('framework/cache/data'),
            storage_path('framework/sessions'),
        ];

        foreach ($directories as $directory) {
            if (File::exists($directory)) {
                try {
                    chmod($directory, 0755);
                } catch (\Exception $e) {
                    Log::warning("No se pudieron corregir permisos para: {$directory}");
                }
            }
        }
    }

    /**
     * Manejar request sin usar caché
     */
    private function handleWithoutCache(Request $request, Closure $next): Response
    {
        // Temporalmente deshabilitar caché para esta request
        config(['cache.default' => 'array']);
        
        try {
            return $next($request);
        } catch (\Exception $e) {
            // Si aún falla, log y retornar error genérico
            Log::error('Error crítico después de deshabilitar caché', [
                'error' => $e->getMessage(),
                'url' => $request->url(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Error temporal del sistema. Por favor, intente nuevamente.',
                'code' => 'CACHE_ERROR'
            ], 503);
        }
    }
}