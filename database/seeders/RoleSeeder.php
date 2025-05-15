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
        $role1 = Role::create(['name' => 'Administrador']);
        $role2 = Role::create(['name' => 'Usuario']);
        $role3 = Role::create(['name' => 'Profesor']);
        $role4 = Role::create(['name' => 'Jefe Carrera']);
        $role5 = Role::create(['name' => 'Auxiliar']);

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
        $permission11 = Permission::create(['name' => 'mantenedor de asignaturas']);
        $permission12 = Permission::create(['name' => 'mantenedor de mapas']);
        $permission13 = Permission::create(['name' => 'mantenedor de carga de datos']);
        $permission21 = Permission::create(['name' => 'visor de mapas']);
        $permission22 = Permission::create(['name' => 'visor de usuarios']);

      
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
        $role1->givePermissionTo($permission11);
        $role1->givePermissionTo($permission12);
        $role1->givePermissionTo($permission13);
        $role1->givePermissionTo($permission21);
        $role1->givePermissionTo($permission22);
        

        $role5->givePermissionTo($permission21);
        $role5->givePermissionTo($permission22);

    
    }
}
