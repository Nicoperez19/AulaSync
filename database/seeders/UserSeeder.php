<?php

namespace Database\Seeders;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Profesor;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // RUNs de usuarios base del sistema que no deben ser modificados
        $baseUserRuns = ['19716146', '11111111', '99999999', '00000000', '18687107', '16600867'];

        // Crear usuarios base del sistema (siempre se crean/actualizan)
        User::updateOrCreate(
            ['run' => '19716146'],
            [
                'name' => 'Administrador',
                'email' => 'admin@ucsc.cl',
                'password' => bcrypt('password'),
                'celular' => '987654321',
                'direccion' => 'Calle Falsa 123',
                'fecha_nacimiento' => '1985-05-20',
            ]
        )->assignRole('Administrador');

        User::updateOrCreate(
            ['run' => '18687107'],
            [
                'name' => 'Geraldin',
                'email' => 'gcuevas@ucsc.cl',
                'password' => bcrypt('password'),
                'celular' => '987654321',
                'direccion' => 'Calle Falsa 123',
                'fecha_nacimiento' => '1985-05-20',
            ]
        )->assignRole('Supervisor');

       User::updateOrCreate(
            ['run' => '16600867'],
            [
                'name' => 'Romina',
                'email' => 'rlizana@ucsc.cl',
                'password' => bcrypt('password'),
                'celular' => '987654321',
                'direccion' => 'Calle Falsa 123',
                'fecha_nacimiento' => '1985-05-20',
            ]
        )->assignRole('Supervisor');

        User::updateOrCreate(
            ['run' => '11111111'],
            [
                'name' => 'Supervisor',
                'email' => 'supervisor@ucsc.cl',
                'password' => bcrypt('password'),
                'celular' => '912345678',
                'direccion' => 'Avenida Siempreviva 742',
                'fecha_nacimiento' => '1992-08-15',
            ]
        )->assignRole('Supervisor');

        User::updateOrCreate(
            ['run' => '99999999'],
            [
                'name' => 'Usuario',
                'email' => 'Usuario@ucsc.cl',
                'password' => bcrypt('password'),
                'celular' => '912345678',
                'direccion' => 'Avenida Siempreviva 742',
                'fecha_nacimiento' => '1992-08-15',
            ]
        )->assignRole('Usuario');

        User::updateOrCreate(
            ['run' => '00000000'],
            [
                'name' => 'Profesor',
                'email' => 'PlanoVirtualProfesor@ucsc.cl',
                'password' => bcrypt('password'),
                'celular' => '912345678',
                'direccion' => 'Avenida Siempreviva 742',
                'fecha_nacimiento' => '1992-08-15',
            ]
        )->assignRole('Profesor');

        // Crear usuarios automáticamente para todos los profesores
        $profesores = Profesor::all();
        
        foreach ($profesores as $profesor) {
            // Verificar si el RUN corresponde a un usuario base protegido
            if (in_array($profesor->run_profesor, $baseUserRuns)) {
                continue; // Saltar usuarios base para no modificarlos
            }

            // Saltar si el RUN está vacío o es nulo
            if (empty($profesor->run_profesor)) {
                continue;
            }

            // Usar updateOrCreate para evitar duplicados
            $newUser = User::updateOrCreate(
                ['run' => $profesor->run_profesor], // Condición de búsqueda
                [
                    'name' => $profesor->name,
                    'email' => $profesor->email,
                    'password' => bcrypt($profesor->run_profesor), // Contraseña es el mismo RUN
                    'celular' => $profesor->celular,
                    'direccion' => $profesor->direccion,
                    'fecha_nacimiento' => $profesor->fecha_nacimiento,
                    'id_universidad' => $profesor->id_universidad,
                    'id_facultad' => $profesor->id_facultad,
                    'id_carrera' => $profesor->id_carrera,
                    'id_area_academica' => $profesor->id_area_academica,
                ]
            );
            
            // Asignar rol Profesor si existe y el usuario no tiene ya ese rol
            if (\Spatie\Permission\Models\Role::where('name', 'Profesor')->exists()) {
                if (!$newUser->hasRole('Profesor')) {
                    $newUser->assignRole('Profesor');
                }
            }
        }
    }
}
