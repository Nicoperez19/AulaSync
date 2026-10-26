<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Tenant;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class CrearUsuariosCaneteSolicitadosSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Asegurar que el tenant de Cañete esté activo e inicializado
        $tenantCT = Tenant::where('sede_id', 'CT')->first();
        if ($tenantCT) {
            $tenantCT->update([
                'is_active' => true,
                'is_initialized' => true,
                'initialized_at' => $tenantCT->initialized_at ?? now(),
            ]);
            $this->command?->info("Tenant de Sede Cañete (CT) verificado y activo.");
        }

        // 2. Roles requeridos
        $adminRole = Role::firstOrCreate(['name' => 'Administrador', 'guard_name' => 'web']);
        $usuarioRole = Role::firstOrCreate(['name' => 'Usuario', 'guard_name' => 'web']);
        $reportesPermission = Permission::firstOrCreate(['name' => 'reportes', 'guard_name' => 'web']);

        // 3. Crear Asistente Administrativa: Abigail Duran Ferreira
        $abigail = User::updateOrCreate(
            ['run' => '16980138'],
            [
                'name' => 'Abigail Duran Ferreira',
                'email' => 'aduran@ucsc.cl',
                'password' => Hash::make('16980138'),
                'id_sede' => 'CT',
                'is_superuser' => false,
            ]
        );
        $abigail->syncRoles([$adminRole]);
        $this->command?->info("Usuario creado/actualizado: Abigail Duran Ferreira (Administrador - Sede Cañete)");

        // 4. Crear Asistente Administrativa: Yenny Huaquiñir Núñez
        $yenny = User::updateOrCreate(
            ['run' => '13629586'],
            [
                'name' => 'Yenny Huaquiñir Núñez',
                'email' => 'yhuaquinir@ucsc.cl',
                'password' => Hash::make('13629586'),
                'id_sede' => 'CT',
                'is_superuser' => false,
            ]
        );
        $yenny->syncRoles([$adminRole]);
        $this->command?->info("Usuario creado/actualizado: Yenny Huaquiñir Núñez (Administrador - Sede Cañete)");

        // 5. Crear / Configurar Rol: Estudiante en Práctica
        $estudiantePracticaRole = Role::firstOrCreate(['name' => 'Estudiante en Práctica', 'guard_name' => 'web']);
        $estudiantePracticaRole->syncPermissions([
            'reportes',               // Lista de clases registradas y no registradas (Control de Clases)
            'monitoreo de espacios',  // Visualización del plano virtual
            'horarios por espacios',  // Consulta de horarios de aulas
            'horarios profesores',    // Consulta de horarios de docentes
            'tablero academico',      // Consulta de módulos y clases en tiempo real
            'visor de mapas',         // Visualizador de mapas
            'visor de usuarios',      // Visualizador de usuarios
        ]);
        $this->command?->info("Perfil/Rol 'Estudiante en Práctica' configurado.");

        // 6. Crear Estudiante en Práctica: Amaya Figueroa Vásquez
        $amaya = User::updateOrCreate(
            ['run' => '22258512'],
            [
                'name' => 'Amaya Figueroa Vásquez',
                'email' => 'afigueroa@ucsc.cl',
                'password' => Hash::make('22258512'),
                'id_sede' => 'CT',
                'is_superuser' => false,
            ]
        );
        $amaya->syncRoles([$estudiantePracticaRole]);
        $this->command?->info("Usuario creado/actualizado: Amaya Figueroa Vásquez (Estudiante en Práctica - Sede Cañete)");
    }
}
