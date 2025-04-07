<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $role1 = Role::create(['name' => 'admin']);
        $role2 = Role::create(['name' => 'Usuario']);

        $permission1 = Permission::create(['name' => 'dashboard']);
        $permission2 = Permission::create(['name' => 'mantenedor de roles']);
        $permission3 = Permission::create(['name' => 'mantenedor de permisos']);
        $permission4 = Permission::create(['name' => 'mantenedor de universidades']);
        $permission5 = Permission::create(['name' => 'mantenedor de facultades']);
        $permission6 = Permission::create(['name' => 'mantenedor de areas academicas']);
        $permission7 = Permission::create(['name' => 'mantenedor de carreras']);
        $permission8 = Permission::create(['name' => 'mantenedor de pisos']);
        $permission9 = Permission::create(['name' => 'mantenedor de espacios']);
        $permission10 = Permission::create(['name' => 'mantenedor de reservas']);

        $role1->givePermissionTo($permission1);
        $role1->givePermissionTo($permission2);
        $role1->givePermissionTo($permission3);
        $role1->givePermissionTo($permission4);
        $role1->givePermissionTo($permission5);
        $role1->givePermissionTo($permission6);
        $role1->givePermissionTo($permission7);
        $role1->givePermissionTo($permission8);
        $role1->givePermissionTo($permission9);
        $role1->givePermissionTo($permission10);



    }
}
