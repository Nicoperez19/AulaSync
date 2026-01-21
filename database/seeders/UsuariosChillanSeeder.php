<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;

class UsuariosChillanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Crea usuarios administrativos y de apoyo para la sede de Chillán
     */
    public function run(): void
    {
        // Director de Sede Chillán
        User::updateOrCreate(
            ['run' => '13517954'],
            [
                'name' => 'Rodrigo Alejandro Cabrera Umaña',
                'email' => 'rocabrera@ucsc.cl',
                'password' => bcrypt('13517954'),
                'celular' => '',
                'direccion' => 'Arauco 449',
                'fecha_nacimiento' => null,
            ]
        )->assignRole('Supervisor');

        // Subdirector sede Chillán
        User::updateOrCreate(
            ['run' => '12267551'],
            [
                'name' => 'Jaime Antonio Ulloa Ulloa',
                'email' => 'jaime.ulloa@ucsc.cl',
                'password' => bcrypt('12267551'),
                'celular' => '',
                'direccion' => 'Arauco 449',
                'fecha_nacimiento' => null,
            ]
        )->assignRole('Supervisor');

        // Encargado de Informática sede Chillán
        User::updateOrCreate(
            ['run' => '17755948'],
            [
                'name' => 'Juan José Osses Figueroa',
                'email' => 'josses@ucsc.cl',
                'password' => bcrypt('17755948'),
                'celular' => '',
                'direccion' => 'Arauco 449',
                'fecha_nacimiento' => null,
            ]
        )->assignRole('Supervisor');

        // Asistente Administrativa
        User::updateOrCreate(
            ['run' => '19071950'],
            [
                'name' => 'Constanza Lyzbeth Salazar Becerra',
                'email' => 'constanza.salazar@ucsc.cl',
                'password' => bcrypt('19071950'),
                'celular' => '',
                'direccion' => 'Arauco 449',
                'fecha_nacimiento' => null,
            ]
        )->assignRole('Supervisor');

        // Asistente Administrativa
        User::updateOrCreate(
            ['run' => '16145668'],
            [
                'name' => 'Valesca Elizabeth Henríquez Echeverria',
                'email' => 'vhenriquez@ucsc.cl',
                'password' => bcrypt('16145668'),
                'celular' => '',
                'direccion' => 'Arauco 449',
                'fecha_nacimiento' => null,
            ]
        )->assignRole('Supervisor');
    }
}
