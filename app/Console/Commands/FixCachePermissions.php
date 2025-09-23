<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Artisan;

class FixCachePermissions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cache:fix-permissions 
                            {--clear : Clear cache before fixing permissions}
                            {--show-details : Show detailed output}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fix cache directory permissions and structure';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔧 Iniciando corrección de permisos de caché...');

        // Limpiar caché si se solicita
        if ($this->option('clear')) {
            $this->info('🧹 Limpiando caché...');
            Artisan::call('cache:clear');
            Artisan::call('config:clear');
            Artisan::call('route:clear');
            Artisan::call('view:clear');
        }

        // Verificar y crear directorios de caché
        $this->createCacheDirectories();

        // Verificar permisos
        $this->checkPermissions();

        $this->info('✅ Corrección de permisos completada.');
        
        return 0;
    }

    /**
     * Crear directorios de caché necesarios
     */
    private function createCacheDirectories()
    {
        $cacheDirectories = [
            storage_path('framework/cache'),
            storage_path('framework/cache/data'),
            storage_path('framework/sessions'),
            storage_path('framework/views'),
            storage_path('logs'),
            storage_path('app/public'),
        ];

        foreach ($cacheDirectories as $directory) {
            if (!File::exists($directory)) {
                try {
                    File::makeDirectory($directory, 0755, true);
                    $this->info("📁 Creado directorio: {$directory}");
                } catch (\Exception $e) {
                    $this->error("❌ Error creando directorio {$directory}: " . $e->getMessage());
                }
            } else {
                if ($this->option('show-details')) {
                    $this->info("✅ Directorio existe: {$directory}");
                }
            }
        }

        // Crear subdirectorios comunes de caché
        $this->createCacheSubdirectories();
    }

    /**
     * Crear subdirectorios comunes de caché
     */
    private function createCacheSubdirectories()
    {
        $cacheDataPath = storage_path('framework/cache/data');
        
        // Crear algunos subdirectorios comunes basados en hashes frecuentes
        $commonHashes = [
            '00', '01', '02', '03', '04', '05', '06', '07', '08', '09',
            '0a', '0b', '0c', '0d', '0e', '0f', '10', '11', '12', '13',
            '14', '15', '16', '17', '18', '19', '1a', '1b', '1c', '1d',
            '1e', '1f', '20', '21', '22', '23', '24', '25', '26', '27',
            '28', '29', '2a', '2b', '2c', '2d', '2e', '2f', '30', '31',
            '32', '33', '34', '35', '36', '37', '38', '39', '3a', '3b',
            '3c', '3d', '3e', '3f', '40', '41', '42', '43', '44', '45',
            '46', '47', '48', '49', '4a', '4b', '4c', '4d', '4e', '4f',
            '50', '51', '52', '53', '54', '55', '56', '57', '58', '59',
            '5a', '5b', '5c', '5d', '5e', '5f'
        ];

        foreach ($commonHashes as $hash) {
            $hashDir = $cacheDataPath . DIRECTORY_SEPARATOR . $hash;
            if (!File::exists($hashDir)) {
                try {
                    File::makeDirectory($hashDir, 0755, true);
                    if ($this->option('show-details')) {
                        $this->info("📁 Creado subdirectorio de caché: {$hash}");
                    }
                } catch (\Exception $e) {
                    if ($this->option('show-details')) {
                        $this->warn("⚠️  No se pudo crear subdirectorio {$hash}: " . $e->getMessage());
                    }
                }
            }

            // Crear segundo nivel de subdirectorios para los hashes más comunes
            if (in_array($hash, ['51', '11', '00', '01', 'ff', 'aa'])) {
                foreach (['00', '11', '22', '33', '44', '55', '66', '77', '88', '99', 'aa', 'bb', 'cc', 'dd', 'ee', 'ff'] as $subHash) {
                    $subHashDir = $hashDir . DIRECTORY_SEPARATOR . $subHash;
                    if (!File::exists($subHashDir)) {
                        try {
                            File::makeDirectory($subHashDir, 0755, true);
                            if ($this->option('show-details')) {
                                $this->info("📁 Creado subdirectorio nivel 2: {$hash}/{$subHash}");
                            }
                        } catch (\Exception $e) {
                            // Silenciar errores en subdirectorios de nivel 2
                        }
                    }
                }
            }
        }
    }

    /**
     * Verificar permisos de directorios
     */
    private function checkPermissions()
    {
        $checkDirectories = [
            storage_path(),
            storage_path('framework'),
            storage_path('framework/cache'),
            storage_path('framework/cache/data'),
            storage_path('framework/sessions'),
            storage_path('framework/views'),
            storage_path('logs'),
        ];

        foreach ($checkDirectories as $directory) {
            if (File::exists($directory)) {
                $permissions = substr(sprintf('%o', fileperms($directory)), -4);
                
                if ($this->option('show-details')) {
                    $this->info("📋 {$directory}: permisos {$permissions}");
                }

                // Verificar si es escribible
                if (!is_writable($directory)) {
                    $this->warn("⚠️  Directorio no escribible: {$directory}");
                    
                    try {
                        chmod($directory, 0755);
                        $this->info("✅ Permisos corregidos para: {$directory}");
                    } catch (\Exception $e) {
                        $this->error("❌ No se pudieron corregir permisos para {$directory}: " . $e->getMessage());
                    }
                } else {
                    if ($this->option('show-details')) {
                        $this->info("✅ Directorio escribible: {$directory}");
                    }
                }
            } else {
                $this->warn("⚠️  Directorio no existe: {$directory}");
            }
        }
    }
}