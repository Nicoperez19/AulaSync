<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class SyncRolesProduccion extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'roles:sync-produccion';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sincroniza los roles y permisos locales con la configuración unificada';

    /**
     * Perfiles y permisos unificados.
     */
    protected array $perfilesProduccion = [
        'Administrador' => [
            'dashboard',
            'mantenedor de roles',
            'mantenedor de permisos',
            'mantenedor de universidades',
            'mantenedor de facultades',
            'mantenedor de areas academicas',
            'mantenedor de carreras',
            'mantenedor de pisos',
            'mantenedor de espacios',
            'mantenedor de reservas',
            'mantenedor de asignaturas',
            'mantenedor de mapas',
            'mantenedor de carga de datos',
            'reportes',
            'monitoreo de espacios',
            'horarios por espacios',
            'horarios profesores',
            'tablero academico',
            'mantenedor de usuarios',
            'mantenedor de campus',
            'mantenedor de sedes',
            'visor de mapas',
            'visor de usuarios',
            'mantenedor de profesores',
            'acciones rapidas',
            'gestionar licencias profesores',
            'gestionar recuperacion clases',
            'mantenedor de feriados',
        ],
        'Supervisor' => [
            'dashboard',
            'reportes',
            'monitoreo de espacios',
            'horarios por espacios',
            'horarios profesores',
            'tablero academico',
            'visor de mapas',
            'visor de usuarios',
            'acciones rapidas',
        ],
        'Usuario' => [
            'monitoreo de espacios',
            'horarios por espacios',
            'horarios profesores',
            'tablero academico',
            'visor de mapas',
            'visor de usuarios',
        ],
        'Profesor' => [
            'monitoreo de espacios',
            'horarios por espacios',
        ],
        'Control Docente' => [
            'mantenedor de reservas',
            'mantenedor de mapas',
            'monitoreo de espacios',
            'visor de mapas',
        ],
        'Director' => [
            'dashboard',
            'monitoreo de espacios',
            'tablero academico',
            'visor de mapas',
        ],
        'Estudiante en Práctica' => [
            'dashboard',
            'reportes',
            'monitoreo de espacios',
            'horarios por espacios',
            'horarios profesores',
            'tablero academico',
            'visor de mapas',
            'visor de usuarios',
        ],
    ];

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Iniciando sincronización de roles y permisos...');

        // Limpiar caché de permisos
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // 1. Asegurar existencia de todos los permisos
        $allPermissions = collect($this->perfilesProduccion)->flatten()->unique();
        $this->line("Verificando {$allPermissions->count()} permisos únicos...");

        foreach ($allPermissions as $permName) {
            Permission::firstOrCreate([
                'name' => $permName,
                'guard_name' => 'web',
            ]);
        }

        // 2. Sincronizar roles y sus permisos
        $tableData = [];
        foreach ($this->perfilesProduccion as $roleName => $permissions) {
            $role = Role::firstOrCreate([
                'name' => $roleName,
                'guard_name' => 'web',
            ]);

            $role->syncPermissions($permissions);

            $tableData[] = [
                $role->id,
                $roleName,
                count($permissions),
                implode(', ', array_slice($permissions, 0, 4)) . (count($permissions) > 4 ? '...' : ''),
            ];
        }

        // Limpiar caché nuevamente
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $this->table(['ID', 'Rol', 'Total Permisos', 'Permisos Principales'], $tableData);
        $this->info('¡Roles y permisos sincronizados exitosamente!');

        return Command::SUCCESS;
    }
}
