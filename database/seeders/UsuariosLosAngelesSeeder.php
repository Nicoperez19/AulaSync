<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;

class UsuariosLosAngelesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Crea usuarios administrativos y de apoyo para la sede de Los Ángeles
     */
    public function run(): void
    {
        User::updateOrCreate(
            ['run' => '11962582'],
            [
                'name' => 'Erica Acuña Jara',
                'email' => 'eacuna@ucsc.cl',
                'password' => bcrypt('11962582'),
                'celular' => '',
                'direccion' => 'Caupolicán 276',
                'fecha_nacimiento' => null,
            ]
        )->assignRole('Supervisor');

        User::updateOrCreate(
            ['run' => '12559533'],
            [
                'name' => 'Rodrigo Rivas Mora',
                'email' => 'rrivas@ucsc.cl',
                'password' => bcrypt('12559533'),
                'celular' => '',
                'direccion' => 'Caupolicán 276',
                'fecha_nacimiento' => null,
            ]
        )->assignRole('Supervisor');

        User::updateOrCreate(
            ['run' => '17537354'],
            [
                'name' => 'Marcelo Moraga Valdebenito',
                'email' => 'marcelo.moraga@ucsc.cl',
                'password' => bcrypt('17537354'),
                'celular' => '',
                'direccion' => 'Villagrán 251',
                'fecha_nacimiento' => null,
            ]
        )->assignRole('Supervisor');

        User::updateOrCreate(
            ['run' => '9328362'],
            [
                'name' => 'Patricia Soto Aburto',
                'email' => 'secla@ucsc.cl',
                'password' => bcrypt('9328362'),
                'celular' => '',
                'direccion' => 'Villagrán 251',
                'fecha_nacimiento' => null,
            ]
        )->assignRole('Supervisor');

        User::updateOrCreate(
            ['run' => '15627709'],
            [
                'name' => 'José Luis Benavides Herrera',
                'email' => 'labla@ucsc.cl',
                'password' => bcrypt('15627709'),
                'celular' => '',
                'direccion' => 'Caupolicán 276',
                'fecha_nacimiento' => null,
            ]
        )->assignRole('Supervisor');

        User::updateOrCreate(
            ['run' => '10263333'],
            [
                'name' => 'Ximena Mendoza Paredes',
                'email' => 'xmendoza@ucsc.cl',
                'password' => bcrypt('10263333'),
                'celular' => '',
                'direccion' => 'Caupolicán 276',
                'fecha_nacimiento' => null,
            ]
        )->assignRole('Supervisor');
    }
}
