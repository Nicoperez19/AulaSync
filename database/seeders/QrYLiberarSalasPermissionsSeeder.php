<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use App\Models\User;

class QrYLiberarSalasPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * 
     * Este seeder asigna los permisos de 'generar qr personal' y 'liberar salas forzadamente'
     * a los usuarios específicos: Christian, Geraldine y Romina.
     */
    public function run(): void
    {
        // Crear los permisos si no existen
        $permisoQr = Permission::firstOrCreate(['name' => 'generar qr personal', 'guard_name' => 'web']);
        $permisoLiberar = Permission::firstOrCreate(['name' => 'liberar salas forzadamente', 'guard_name' => 'web']);

        // RUNs de los usuarios que tendrán estos permisos
        $usuariosRuns = [
            '10044790', // Christian Villagra O.
            '18687107', // Geraldine Cuevas
            '16600867', // Romina Lizana
        ];

        foreach ($usuariosRuns as $run) {
            $user = User::where('run', $run)->first();
            
            if ($user) {
                // Asignar permisos directamente al usuario (además de los del rol)
                if (!$user->hasPermissionTo('generar qr personal')) {
                    $user->givePermissionTo($permisoQr);
                }
                
                if (!$user->hasPermissionTo('liberar salas forzadamente')) {
                    $user->givePermissionTo($permisoLiberar);
                }
                
                $this->command->info("Permisos asignados a: {$user->name} (RUN: {$run})");
            } else {
                $this->command->warn("Usuario con RUN {$run} no encontrado.");
            }
        }

        $this->command->info('Permisos de QR personal y liberar salas asignados correctamente.');
    }
}
