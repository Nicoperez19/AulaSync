<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class NewMaintainersPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create permissions for new maintainers
        $permissions = [
            'mantenedor de configuracion',
            'mantenedor de escuelas',
            'mantenedor de jefes de carrera',
            'mantenedor de asistentes academicos',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        // Assign all permissions to Administrador role
        $adminRole = Role::where('name', 'Administrador')->first();
        if ($adminRole) {
            foreach ($permissions as $permission) {
                $perm = Permission::where('name', $permission)->first();
                if ($perm && !$adminRole->hasPermissionTo($perm)) {
                    $adminRole->givePermissionTo($perm);
                }
            }
        }

        $this->command->info('New maintainers permissions created and assigned to Administrador role successfully!');
    }
}
