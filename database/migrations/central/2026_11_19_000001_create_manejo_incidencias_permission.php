<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    /**
     * Permiso para acceder al tablero de Fallas e Incidencias en espacios.
     * Por defecto solo lo recibe el rol Administrador; el resto se asigna desde Mantenedor de Roles.
     */
    public function up(): void
    {
        try {
            $permiso = Permission::firstOrCreate(['name' => 'manejo de incidencias', 'guard_name' => 'web']);

            $admin = Role::where('name', 'Administrador')->where('guard_name', 'web')->first();
            if ($admin && ! $admin->hasPermissionTo($permiso)) {
                $admin->givePermissionTo($permiso);
            }

            app(PermissionRegistrar::class)->forgetCachedPermissions();
        } catch (\Throwable $e) {
            // Si las tablas de permisos aún no existen, el seeder IncidenciasPermissionsSeeder cubre ese caso.
        }
    }

    public function down(): void
    {
        try {
            Permission::where('name', 'manejo de incidencias')->where('guard_name', 'web')->delete();
            app(PermissionRegistrar::class)->forgetCachedPermissions();
        } catch (\Throwable $e) {
            // Ignorar
        }
    }
};
