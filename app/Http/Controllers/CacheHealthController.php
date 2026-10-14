<?php

namespace App\Http\Controllers;

use App\Traits\SafeCacheTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

class CacheHealthController extends Controller
{
    use SafeCacheTrait;

    /**
     * Verificar la salud del sistema de caché
     */
    public function healthCheck()
    {
        $health = $this->checkCacheHealth();

        Log::info('Cache health check realizado', $health);

        return response()->json([
            'status' => $health['status'],
            'cache_writable' => $health['writable'],
            'space_available' => $health['space_available'],
            'errors' => $health['errors'],
            'timestamp' => now()->toISOString(),
        ]);
    }

    /**
     * Limpiar caché manualmente
     */
    public function clearCache(Request $request)
    {
        try {
            $type = $request->get('type', 'all');

            switch ($type) {
                case 'espacios':
                    $this->clearEspacioCache();
                    $message = 'Caché de espacios limpiado';
                    break;
                case 'all':
                    Artisan::call('cache:clear');
                    $message = 'Todo el caché limpiado';
                    break;
                default:
                    return response()->json([
                        'success' => false,
                        'message' => 'Tipo de caché no válido',
                    ], 400);
            }

            Log::info("Cache manual clear: {$message}");

            return response()->json([
                'success' => true,
                'message' => $message,
                'timestamp' => now()->toISOString(),
            ]);

        } catch (\Exception $e) {
            Log::error('Error al limpiar caché manualmente: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error al limpiar caché: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Crear estructura de caché manualmente
     */
    public function createCacheStructure()
    {
        try {
            Artisan::call('cache:fix-permissions', [
                '--show-details' => true,
            ]);

            $output = Artisan::output();

            return response()->json([
                'success' => true,
                'message' => 'Estructura de caché creada/verificada',
                'output' => $output,
                'timestamp' => now()->toISOString(),
            ]);

        } catch (\Exception $e) {
            Log::error('Error al crear estructura de caché: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error al crear estructura: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Obtener estadísticas de caché
     */
    public function stats()
    {
        try {
            $cacheDir = storage_path('framework/cache/data');
            $stats = [
                'cache_directory' => $cacheDir,
                'directory_exists' => File::exists($cacheDir),
                'is_writable' => is_writable($cacheDir),
                'subdirectories' => 0,
                'cache_files' => 0,
                'total_size' => 0,
            ];

            if ($stats['directory_exists']) {
                $files = File::allFiles($cacheDir);
                $directories = File::directories($cacheDir);

                $stats['subdirectories'] = count($directories);
                $stats['cache_files'] = count($files);

                foreach ($files as $file) {
                    $stats['total_size'] += $file->getSize();
                }

                $stats['total_size_human'] = $this->formatBytes($stats['total_size']);
            }

            return response()->json([
                'success' => true,
                'stats' => $stats,
                'timestamp' => now()->toISOString(),
            ]);

        } catch (\Exception $e) {
            Log::error('Error al obtener estadísticas de caché: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error al obtener estadísticas: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Formatear bytes a formato legible
     */
    private function formatBytes($bytes, $precision = 2)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, $precision).' '.$units[$i];
    }
}
