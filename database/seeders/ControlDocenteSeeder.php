<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;

class ControlDocenteSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * 
     * Crea usuarios de Control Docente para cada sede (LA, TH, CT, CH)
     * que pueden registrar reservas desde el Plano Digital.
     */
    public function run(): void
    {
        // Definir profesores de control docente para cada sede
        $controlDocenteUsers = [
            [
                'run' => '10000002',
                'name' => 'Control Docente Los Ángeles',
                'email' => 'control.docente.la@ucsc.cl',
                'id_sede' => 'LA',
                'ciudad' => 'Los Ángeles',
            ],
            [
                'run' => '10000001',
                'name' => 'Control Docente Talcahuano',
                'email' => 'control.docente.th@ucsc.cl',
                'id_sede' => 'TH',
                'ciudad' => 'Talcahuano',
            ],
            [
                'run' => '10000003',
                'name' => 'Control Docente Cañete',
                'email' => 'control.docente.ct@ucsc.cl',
                'id_sede' => 'CT',
                'ciudad' => 'Cañete',
            ],
            [
                'run' => '10000004',
                'name' => 'Control Docente Chillán',
                'email' => 'control.docente.ch@ucsc.cl',
                'id_sede' => 'CH',
                'ciudad' => 'Chillán',
            ],
        ];

        // Crear o actualizar cada usuario de control docente
        foreach ($controlDocenteUsers as $userData) {
            $user = User::updateOrCreate(
                ['run' => $userData['run']],
                [
                    'name' => $userData['name'],
                    'email' => $userData['email'],
                    'password' => bcrypt($userData['run']), // Contraseña es el mismo RUN
                    'celular' => '912345678',
                    'direccion' => 'Campus ' . $userData['ciudad'],
                    'fecha_nacimiento' => '1992-08-15',
                    'id_sede' => $userData['id_sede'],
                    'id_universidad' => 'UCSC',
                ]
            );

            // Asignar rol Control Docente (creado por RoleSeeder)
            if (!$user->hasRole('Control Docente')) {
                $user->assignRole('Control Docente');
            }
        }
    }
}
