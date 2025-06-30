<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;

class CleanExpiredSessions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sessions:clean';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Limpiar sesiones expiradas del sistema';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Limpiando sesiones expiradas...');

        // Limpiar sesiones de archivos
        $sessionPath = storage_path('framework/sessions');
        $files = glob($sessionPath . '/*');
        $deletedCount = 0;

        foreach ($files as $file) {
            if (is_file($file)) {
                $lastModified = filemtime($file);
                $sessionLifetime = config('session.lifetime', 120) * 60; // Convertir a segundos
                
                if (time() - $lastModified > $sessionLifetime) {
                    unlink($file);
                    $deletedCount++;
                }
            }
        }

        $this->info("Se eliminaron {$deletedCount} sesiones expiradas.");
        
        // Limpiar sesiones de la base de datos si se usa
        if (config('session.driver') === 'database') {
            $this->cleanDatabaseSessions();
        }

        $this->info('Limpieza de sesiones completada.');
    }

    /**
     * Limpiar sesiones de la base de datos
     */
    private function cleanDatabaseSessions()
    {
        $table = config('session.table', 'sessions');
        $sessionLifetime = config('session.lifetime', 120);
        
        $deletedCount = \DB::table($table)
            ->where('last_activity', '<', time() - ($sessionLifetime * 60))
            ->delete();

        $this->info("Se eliminaron {$deletedCount} sesiones expiradas de la base de datos.");
    }
}
