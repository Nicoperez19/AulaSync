<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;

class UsuariosCañeteSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Crea usuarios administrativos y de apoyo para la sede de Cañete
     */
    public function run(): void
    {
        // Subdirectora sede Cañete
        User::updateOrCreate(
            ['run' => '12526386'],
            [
                'name' => 'Ivonne Torres Sanhueza',
                'email' => 'itorres@ucss.cl',
                'password' => bcrypt('12526386'),
                'celular' => '',
                'direccion' => 'Avenida Presidente Frei',
                'fecha_nacimiento' => null,
            ]
        )->assignRole('Supervisor');

        // Directora de Sede Cañete
        User::updateOrCreate(
            ['run' => '11774643'],
            [
                'name' => 'Ana María Muñoz Fierro',
                'email' => 'ammunoz@ucss.cl',
                'password' => bcrypt('11774643'),
                'celular' => '',
                'direccion' => 'Avenida Presidente Frei',
                'fecha_nacimiento' => null,
            ]
        )->assignRole('Supervisor');

        // Encargado de Informática sede Cañete
        User::updateOrCreate(
            ['run' => '13581331'],
            [
                'name' => 'Benedicto Garrido Neculqueo',
                'email' => 'bgarrido@ucss.cl',
                'password' => bcrypt('13581331'),
                'celular' => '',
                'direccion' => 'Avenida Presidente Frei',
                'fecha_nacimiento' => null,
            ]
        )->assignRole('Supervisor');

        // Asistente Administrativa
        User::updateOrCreate(
            ['run' => '15200747'],
            [
                'name' => 'Nelly Verónica Santibaáez San Martín',
                'email' => 'nsantibanez@ucss.cl',
                'password' => bcrypt('15200747'),
                'celular' => '',
                'direccion' => 'Avenida Presidente Frei',
                'fecha_nacimiento' => null,
            ]
        )->assignRole('Supervisor');
    }
}
