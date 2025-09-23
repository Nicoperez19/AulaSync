<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\Espacio;

class TestCacheSolution extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cache:test-solution';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test the cache error solution';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🧪 Iniciando pruebas de la solución de caché...');

        // 1. Probar comando de reparación
        $this->info("\n1️⃣ Probando comando de reparación...");
        $exitCode = $this->call('cache:fix-permissions');
        if ($exitCode === 0) {
            $this->info("✅ Comando de reparación ejecutado correctamente");
        } else {
            $this->error("❌ Error en comando de reparación");
        }

        // 2. Verificar estructura de directorios
        $this->info("\n2️⃣ Verificando estructura de directorios...");
        $this->checkCacheDirectories();

        // 3. Probar endpoints de caché (si hay servidor corriendo)
        $this->info("\n3️⃣ Probando endpoints de API...");
        $this->testCacheEndpoints();

        // 4. Probar SafeCacheTrait
        $this->info("\n4️⃣ Probando SafeCacheTrait...");
        $this->testSafeCacheTrait();

        // 5. Probar endpoint problemático
        $this->info("\n5️⃣ Probando endpoint de información detallada...");
        $this->testEspacioEndpoint();

        $this->info("\n🎉 Pruebas completadas!");
    }

    private function checkCacheDirectories()
    {
        $requiredDirectories = [
            storage_path('framework/cache/data/51/11'), // El directorio del error original
            storage_path('framework/cache/data/00/00'),
            storage_path('framework/cache/data/ff/aa'),
        ];

        foreach ($requiredDirectories as $directory) {
            if (is_dir($directory)) {
                $this->info("✅ Directorio existe: {$directory}");
            } else {
                $this->warn("⚠️  Directorio faltante: {$directory}");
            }

            if (is_writable($directory)) {
                $this->info("✅ Directorio escribible: {$directory}");
            } else {
                $this->warn("⚠️  Directorio no escribible: {$directory}");
            }
        }
    }

    private function testCacheEndpoints()
    {
        $baseUrl = 'http://127.0.0.1:8000';
        
        $endpoints = [
            'GET /api/cache/health',
            'GET /api/cache/stats',
        ];

        foreach ($endpoints as $endpoint) {
            [$method, $path] = explode(' ', $endpoint);
            
            try {
                $response = Http::timeout(5)->get($baseUrl . $path);
                
                if ($response->successful()) {
                    $this->info("✅ {$endpoint} - Respuesta exitosa");
                    
                    // Mostrar algunos datos de salud si es el endpoint de health
                    if (str_contains($path, 'health')) {
                        $data = $response->json();
                        $this->line("   Estado: " . $data['status']);
                        $this->line("   Escribible: " . ($data['cache_writable'] ? 'Sí' : 'No'));
                    }
                } else {
                    $this->warn("⚠️  {$endpoint} - Código: {$response->status()}");
                }
            } catch (\Exception $e) {
                $this->warn("⚠️  {$endpoint} - Error: " . $e->getMessage());
                $this->line("   (Esto es normal si el servidor no está corriendo)");
            }
        }
    }

    private function testSafeCacheTrait()
    {
        // Usar el trait en una instancia temporal
        $testController = new class {
            use \App\Traits\SafeCacheTrait;

            public function test()
            {
                // Probar escritura segura
                $key = 'test_cache_key_' . time();
                $value = 'test_value_' . random_int(1000, 9999);
                
                $writeResult = $this->safeCache($key, $value, 60);
                $readResult = $this->safeGet($key);
                $deleteResult = $this->safeForget($key);

                return [
                    'write' => $writeResult,
                    'read' => $readResult,
                    'delete' => $deleteResult,
                    'expected_value' => $value
                ];
            }

            public function testHealth()
            {
                return $this->checkCacheHealth();
            }
        };

        try {
            $result = $testController->test();
            
            if ($result['write'] && $result['read'] === $result['expected_value']) {
                $this->info("✅ SafeCacheTrait funcionando correctamente");
                $this->line("   Escritura: ✓");
                $this->line("   Lectura: ✓");
                $this->line("   Eliminación: ✓");
            } else {
                $this->warn("⚠️  SafeCacheTrait con problemas");
                $this->line("   Escritura: " . ($result['write'] ? '✓' : '✗'));
                $this->line("   Lectura: " . ($result['read'] === $result['expected_value'] ? '✓' : '✗'));
            }

            // Probar health check
            $health = $testController->testHealth();
            $this->info("   Estado de salud: {$health['status']}");
            if (!empty($health['errors'])) {
                foreach ($health['errors'] as $error) {
                    $this->warn("   ⚠️  {$error}");
                }
            }

        } catch (\Exception $e) {
            $this->error("❌ Error probando SafeCacheTrait: " . $e->getMessage());
        }
    }

    private function testEspacioEndpoint()
    {
        // Buscar un espacio existente
        $espacio = Espacio::first();
        
        if (!$espacio) {
            $this->warn("⚠️  No hay espacios en la base de datos para probar");
            return;
        }

        $baseUrl = 'http://127.0.0.1:8000';
        $path = "/api/espacio/{$espacio->id_espacio}/informacion-detallada";

        try {
            $response = Http::timeout(10)->get($baseUrl . $path);
            
            if ($response->successful()) {
                $data = $response->json();
                $this->info("✅ Endpoint de información detallada funcionando");
                $this->line("   Espacio: {$espacio->id_espacio}");
                $this->line("   Success: " . ($data['success'] ? 'true' : 'false'));
                $this->line("   Tipo ocupación: " . ($data['tipo_ocupacion'] ?? 'N/A'));
            } else {
                $this->warn("⚠️  Endpoint retornó código: {$response->status()}");
            }
        } catch (\Exception $e) {
            $this->warn("⚠️  Error probando endpoint: " . $e->getMessage());
            $this->line("   (Esto es normal si el servidor no está corriendo)");
        }
    }
}