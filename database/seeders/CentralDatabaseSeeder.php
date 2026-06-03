<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

/**
 * Seeder para la base de datos central (aulasync)
 * Contiene información compartida entre todos los tenants:
 * - Sedes
 * - Tenants
 * - Usuarios y roles centralizados.
 */
class CentralDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Datos compartidos/centralizados
        $this->call(AdministracionChileSeeder::class);
        $this->call(UniversidadSeeder::class);
        $this->call(SedeSeeder::class);
        $this->call(CampusSeeder::class);
        $this->call(RoleSeeder::class);
        $this->call(UserSeeder::class);
        $this->call(ControlDocenteSeeder::class);
        $this->call(TenantSeeder::class);
        $this->call(FacultadSeeder::class);
        $this->call(AreaAcademicaSeeder::class);
        $this->call(CarreraSeeder::class);

        // Los siguientes seeders usan conexión 'tenant', se ejecutan en TenantDatabaseSeeder:
        // - FacultadSeeder
        // - AreaAcademicaSeeder
        // - CarreraSeeder

        // Módulos (compartidos) - Ahora en tenant
        // $this->call(ModulosSeeder::class);

        // Tipos de correos masivos - Ahora en tenant
        // $this->call(TiposCorreosMasivosSeeder::class);

        // Días feriados de Chile - Ahora en tenant
        // $this->call(DiasFeriadosSeeder::class);
    }
}
