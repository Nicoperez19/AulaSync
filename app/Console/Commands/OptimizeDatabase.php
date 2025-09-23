<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;

class OptimizeDatabase extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:optimize-db';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Optimiza la base de datos y limpia cachés para mejorar el rendimiento';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Iniciando optimización de base de datos...');

        try {
            // Limpiar caché de Laravel
            $this->info('Limpiando caché de aplicación...');
            Cache::flush();
            Artisan::call('cache:clear');
            Artisan::call('config:cache');
            Artisan::call('route:cache');
            Artisan::call('view:cache');


            // OJO CONSTANZA O QUIEN ADMINISTRE ESTO:
            // Limpiar logs antiguos (más de 7 días) SI VAS A ACTIVAR ESTO ASEGURATE MUY BIEN DE LO QUE HACES
            // PORQUE PODRÍA BORRAR LOGS IMPORTANTES, ASIQUE CREA UNO DE RESPALDO ANTES O VARIOS EN DIFERENTES
            //ARCHIVOS. lO DEJO POR SI TE PUEDE INTERESAR PERO LO COMENTO

           // $this->info('Limpiando logs antiguos...');
            //$logPath = storage_path('logs');
            //if (is_dir($logPath)) {
             //   $files = glob($logPath . '/*.log');
            //    foreach ($files as $file) {
             //       if (filemtime($file) < (time() - 7 * 24 * 60 * 60)) {
             //           unlink($file);
              //      }
             //   }
           // }

            // Optimizar tablas de MySQL si es necesario
            if (config('database.default') === 'mysql') {
                $this->info('Optimizando tablas de MySQL...');
                
                $tables = [
                    'planificacion_asignaturas',
                    'reservas',
                    'espacios',
                    'pisos',
                    'modulos'
                ];

                foreach ($tables as $table) {
                    try {
                        DB::statement("OPTIMIZE TABLE {$table}");
                        $this->info("✓ Tabla {$table} optimizada");
                    } catch (\Exception $e) {
                        $this->warn("⚠ No se pudo optimizar la tabla {$table}: " . $e->getMessage());
                    }
                }

                // Analizar tablas para actualizar estadísticas
                $this->info('Analizando tablas para actualizar estadísticas...');
                foreach ($tables as $table) {
                    try {
                        DB::statement("ANALYZE TABLE {$table}");
                        $this->info("✓ Tabla {$table} analizada");
                    } catch (\Exception $e) {
                        $this->warn("⚠ No se pudo analizar la tabla {$table}: " . $e->getMessage());
                    }
                }
            }

            // Crear índices si no existen
            $this->info('Verificando índices de base de datos...');
            $this->createIndexesIfNotExists();

            $this->info('✅ Optimización completada exitosamente');

        } catch (\Exception $e) {
            $this->error('❌ Error durante la optimización: ' . $e->getMessage());
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }

    /**
     * Crear índices importantes si no existen
     */
    private function createIndexesIfNotExists()
    {
        $indexes = [
            'planificacion_asignaturas' => [
                'idx_id_espacio' => 'id_espacio',
                'idx_id_modulo' => 'id_modulo',
                'idx_id_asignatura' => 'id_asignatura',
            ],
            'reservas' => [
                'idx_fecha_estado' => 'fecha_reserva, estado',
                'idx_id_espacio' => 'id_espacio',
            ],
            'espacios' => [
                'idx_id_piso' => 'id_piso',
            ]
        ];

        foreach ($indexes as $table => $tableIndexes) {
            foreach ($tableIndexes as $indexName => $columns) {
                try {
                    // Verificar si el índice ya existe
                    $exists = DB::select("
                        SELECT COUNT(*) as count 
                        FROM information_schema.statistics 
                        WHERE table_schema = DATABASE() 
                        AND table_name = ? 
                        AND index_name = ?
                    ", [$table, $indexName]);

                    if ($exists[0]->count == 0) {
                        DB::statement("CREATE INDEX {$indexName} ON {$table} ({$columns})");
                        $this->info("✓ Índice {$indexName} creado en tabla {$table}");
                    }
                } catch (\Exception $e) {
                    $this->warn("⚠ No se pudo crear el índice {$indexName} en {$table}: " . $e->getMessage());
                }
            }
        }
    }
}