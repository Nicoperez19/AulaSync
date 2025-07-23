<?php

namespace Database\Seeders;
use Illuminate\Database\Seeder;
use App\Models\User;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::create([
            'run' => '19716146',
            'name' => 'Administrador',
            'email' => 'admin@ucsc.cl',
            'password' => bcrypt('password'),
            'celular' => '987654321',
            'direccion' => 'Calle Falsa 123',
            'fecha_nacimiento' => '1985-05-20',
            'anio_ingreso' => 2010,
        ])->assignRole('Administrador');

        User::create([
            'run' => '19812524',
            'name' => 'Nicolas Perez',
            'email' => 'nperez@ucsc.cl',
            'password' => bcrypt('password'),
            'celular' => '912345678',
            'direccion' => 'Avenida Siempreviva 742',
            'fecha_nacimiento' => '1992-08-15',
            'anio_ingreso' => 2015,
        ])->assignRole('Profesor');

       
    }
}
