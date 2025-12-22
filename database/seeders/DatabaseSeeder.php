<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     * 
     * IMPORTANTE: Este seeder ahora solo ejecuta datos centralizados.
     * Para seeders de tenants, usar TenantDatabaseSeeder o el comando:
     * php artisan tenants:setup --seed
     */
    public function run(): void
    {
        $this->command->warn('⚠️  IMPORTANTE: Este seeder solo carga datos centralizados.');
        $this->command->info('Para cargar datos de tenants, ejecuta: php artisan tenants:setup --seed');
        $this->command->newLine();
        
        // Llamar al seeder centralizado
        $this->call(CentralDatabaseSeeder::class);
    }
}
