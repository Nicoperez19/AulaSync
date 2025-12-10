<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Sede;
use App\Models\Universidad;
use App\Models\Comuna;
use App\Models\Tenant;
use Illuminate\Support\Facades\DB;

class TenantDemoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * 
     * This seeder creates demo tenants for testing multi-tenancy.
     * Run this on the landlord database.
     */
    public function run(): void
    {
        $this->command->info('Creating demo tenants...');
        
        // Ensure we have a universidad
        $universidad = Universidad::firstOrCreate(
            ['id_universidad' => 'DEMO_UNI'],
            [
                'nombre_universidad' => 'Universidad Demo',
                'imagen_logo' => null
            ]
        );
        
        $this->command->info("Universidad created: {$universidad->nombre_universidad}");
        
        // Ensure we have a comuna
        $comuna = Comuna::first();
        if (!$comuna) {
            $this->command->warn('No comuna found. Please seed regiones, provincias, and comunas first.');
            return;
        }
        
        // Create sample sedes
        $sedes = [
            [
                'id_sede' => 'SEDE_MAIPU',
                'nombre_sede' => 'Maipú',
                'domain' => 'maipu',
            ],
            [
                'id_sede' => 'SEDE_SANTIAGO',
                'nombre_sede' => 'Santiago',
                'domain' => 'santiago',
            ],
            [
                'id_sede' => 'SEDE_TALCAHUANO',
                'nombre_sede' => 'Talcahuano',
                'domain' => 'talcahuano',
            ],
        ];
        
        foreach ($sedes as $sedeData) {
            // Create or update sede
            $sede = Sede::updateOrCreate(
                ['id_sede' => $sedeData['id_sede']],
                [
                    'nombre_sede' => $sedeData['nombre_sede'],
                    'id_universidad' => $universidad->id_universidad,
                    'comuna_id' => $comuna->id_comuna,
                ]
            );
            
            $this->command->info("Sede created: {$sede->nombre_sede}");
            
            // Check if tenant already exists
            if (Tenant::where('sede_id', $sede->id_sede)->exists()) {
                $this->command->warn("Tenant for {$sede->nombre_sede} already exists. Skipping...");
                continue;
            }
            
            // Create tenant
            $databaseName = 'tenant_' . strtolower(str_replace(['_', ' '], '', $sedeData['id_sede']));
            
            try {
                // Create database
                DB::statement("CREATE DATABASE IF NOT EXISTS `{$databaseName}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
                
                // Create tenant record
                $tenant = Tenant::create([
                    'name' => $sede->nombre_sede,
                    'domain' => $sedeData['domain'],
                    'database' => $databaseName,
                    'sede_id' => $sede->id_sede,
                ]);
                
                $this->command->info("Tenant created with ID {$tenant->id}: {$tenant->name} ({$tenant->domain})");
                
                // Run migrations for this tenant
                $this->command->info("Running migrations for {$tenant->name}...");
                $this->call('tenants:artisan', [
                    'artisanCommand' => 'migrate --path=database/migrations/tenant',
                    '--tenant' => $tenant->id
                ]);
                
                $this->command->info("✓ Tenant {$tenant->name} setup complete!");
                
            } catch (\Exception $e) {
                $this->command->error("Error creating tenant for {$sede->nombre_sede}: " . $e->getMessage());
            }
        }
        
        $this->command->info('Demo tenants created successfully!');
        $this->command->info('You can now access:');
        $this->command->info('  - maipu.localhost (Maipú tenant)');
        $this->command->info('  - santiago.localhost (Santiago tenant)');
        $this->command->info('  - talcahuano.localhost (Talcahuano tenant)');
    }
}
