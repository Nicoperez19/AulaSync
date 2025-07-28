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
        // Crear roles (usar firstOrCreate para evitar errores si ya existen)
        $roleAdmin = Role::firstOrCreate(['name' => 'Administrador']);
        $roleSupervisor = Role::firstOrCreate(['name' => 'Supervisor']);
        $roleUsuario = Role::firstOrCreate(['name' => 'Usuario']);
        
        // Crear permisos (usar firstOrCreate para evitar errores si ya existen)
        $permission1 = Permission::firstOrCreate(['name' => 'dashboard']);
        $permission2 = Permission::firstOrCreate(['name' => 'mantenedor de roles']);
        $permission3 = Permission::firstOrCreate(['name' => 'mantenedor de permisos']);
        $permission4 = Permission::firstOrCreate(['name' => 'mantenedor de universidades']);
        $permission5 = Permission::firstOrCreate(['name' => 'mantenedor de facultades']);
        $permission6 = Permission::firstOrCreate(['name' => 'mantenedor de areas academicas']);
        $permission7 = Permission::firstOrCreate(['name' => 'mantenedor de carreras']);
        $permission8 = Permission::firstOrCreate(['name' => 'mantenedor de pisos']);
        $permission9 = Permission::firstOrCreate(['name' => 'mantenedor de espacios']);
        $permission10 = Permission::firstOrCreate(['name' => 'mantenedor de reservas']);
        $permission11 = Permission::firstOrCreate(['name' => 'mantenedor de asignaturas']);
        $permission12 = Permission::firstOrCreate(['name' => 'mantenedor de mapas']);
        $permission13 = Permission::firstOrCreate(['name' => 'mantenedor de carga de datos']);
        $permission14 = Permission::firstOrCreate(['name' => 'reportes']);
        $permission15 = Permission::firstOrCreate(['name' => 'monitoreo de espacios']);
        $permission16 = Permission::firstOrCreate(['name' => 'horarios por espacios']);
        $permission17 = Permission::firstOrCreate(['name' => 'horarios profesores']);
        $permission18 = Permission::firstOrCreate(['name' => 'tablero academico']);
        $permission19 = Permission::firstOrCreate(['name' => 'mantenedor de usuarios']);
        $permission21 = Permission::firstOrCreate(['name' => 'visor de mapas']);
        $permission22 = Permission::firstOrCreate(['name' => 'visor de usuarios']);
      
        // Limpiar permisos existentes y asignar nuevos
        $roleAdmin->syncPermissions([
            $permission1, $permission2, $permission3, $permission4, $permission5,
            $permission6, $permission7, $permission8, $permission9, $permission10,
            $permission11, $permission12, $permission13, $permission14, $permission15,
            $permission16, $permission17, $permission18, $permission19, $permission21, $permission22
        ]);

        // Permisos para Supervisor (sin mantenedores)
        $roleSupervisor->syncPermissions([
            $permission1, $permission14, $permission15, $permission16, $permission17,
            $permission18, $permission21, $permission22
        ]);

        // Permisos para Usuario (monitoreo, tablero, horarios)
        $roleUsuario->syncPermissions([
            $permission15, $permission16, $permission17, $permission18, $permission21, $permission22
        ]);
    }
}
