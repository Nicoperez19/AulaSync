<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Tenant;

/**
 * Seeder para las bases de datos de cada tenant
 * Contiene solo los datos específicos de cada sede
 */
class TenantDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Intentar obtener el tenant actual desde el service container
        if (app()->bound('tenant')) {
            $tenant = app('tenant');
        } else {
            $tenant = null;
        }
        
        if (!$tenant) {
            $this->command->error('No hay tenant activo. Este seeder debe ejecutarse en contexto de un tenant.');
            return;
        }
        
        $this->command->info("Ejecutando seeder para tenant: {$tenant->name} (Sede: {$tenant->sede_id})");
        
        // Datos de referencia (Facultades, Áreas, Carreras) que ahora están en cada tenant
        $this->call(FacultadSeeder::class);
        $this->call(AreaAcademicaSeeder::class);
        $this->call(CarreraSeeder::class);
        
        // Pisos y espacios específicos de esta sede
        $this->call(PisoSeeder::class);
        $this->call(EspacioSeeder::class);
    }
}
