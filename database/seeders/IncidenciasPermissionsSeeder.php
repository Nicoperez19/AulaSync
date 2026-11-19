<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class IncidenciasPermissionsSeeder extends Seeder
{
    /**
     * Crea el permiso "manejo de incidencias" y lo asigna al rol Administrador.
     * Se puede ejecutar varias veces sin duplicar nada:
     *   php artisan db:seed --class=IncidenciasPermissionsSeeder
     */
    public function run(): void
    {
        $permiso = Permission::firstOrCreate(['name' => 'manejo de incidencias', 'guard_name' => 'web']);

        $admin = Role::where('name', 'Administrador')->first();
        if ($admin && ! $admin->hasPermissionTo($permiso)) {
            $admin->givePermissionTo($permiso);
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $this->command?->info('Permiso "manejo de incidencias" creado y asignado al rol Administrador.');
    }
}
