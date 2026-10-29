<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Asignar permiso 'dashboard' al rol 'Estudiante en Práctica'
        try {
            $dashboardPermission = Permission::firstOrCreate(['name' => 'dashboard', 'guard_name' => 'web']);
            $rolEstudiantePractica = Role::where('name', 'Estudiante en Práctica')->where('guard_name', 'web')->first();
            if ($rolEstudiantePractica) {
                if (!$rolEstudiantePractica->hasPermissionTo('dashboard')) {
                    $rolEstudiantePractica->givePermissionTo($dashboardPermission);
                }
            }
        } catch (\Throwable $e) {
            // Log or ignore if tables not ready
        }

        // 2. Asegurar que los usuarios de Cañete tengan id_sede = 'CT'
        try {
            // Abigail Duran Ferreira
            User::where('run', '16980138')->update([
                'id_sede' => 'CT',
            ]);

            // Yenny Huaquiñir Núñez
            User::where('run', '13629586')->update([
                'id_sede' => 'CT',
            ]);

            // Amaya Figueroa Vásquez
            User::where('run', '22258512')->update([
                'id_sede' => 'CT',
                'id_carrera' => 1205, // TU. Administración
            ]);
        } catch (\Throwable $e) {
            // Log or ignore
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        try {
            $rolEstudiantePractica = Role::where('name', 'Estudiante en Práctica')->where('guard_name', 'web')->first();
            if ($rolEstudiantePractica) {
                $rolEstudiantePractica->revokePermissionTo('dashboard');
            }
        } catch (\Throwable $e) {
            // Ignore
        }
    }
};
