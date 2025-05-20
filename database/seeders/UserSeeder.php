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

        // $users = [
        //     ['run' => '22334455', 'name' => 'Juan Perez', 'email' => 'juanp@ucsc.cl', 'celular' => '912233445', 'direccion' => 'Calle Real 101', 'fecha_nacimiento' => '1990-02-25', 'anio_ingreso' => 2012, 'password' => bcrypt('password')],
        //     ['run' => '33445566', 'name' => 'Ana Lopez', 'email' => 'analopez@ucsc.cl', 'celular' => '976554433', 'direccion' => 'Avenida Central 222', 'fecha_nacimiento' => '1993-07-14', 'anio_ingreso' => 2013, 'password' => bcrypt('password')],
        //     ['run' => '44556677', 'name' => 'Carlos Soto', 'email' => 'carlossoto@ucsc.cl', 'celular' => '976877665', 'direccion' => 'Calle Las Palmas 321', 'fecha_nacimiento' => '1995-01-10', 'anio_ingreso' => 2014, 'password' => bcrypt('password')],
        //     ['run' => '55667788', 'name' => 'Maria Gonzalez', 'email' => 'mariagonzalez@ucsc.cl', 'celular' => '987654322', 'direccion' => 'Calle Ficticia 654', 'fecha_nacimiento' => '1989-11-22', 'anio_ingreso' => 2011, 'password' => bcrypt('password')],
        //     ['run' => '66778899', 'name' => 'Felipe Alvarez', 'email' => 'felipealvarez@ucsc.cl', 'celular' => '999887766', 'direccion' => 'Avenida Libertador 543', 'fecha_nacimiento' => '1991-04-12', 'anio_ingreso' => 2016, 'password' => bcrypt('password')],
        //     ['run' => '77889900', 'name' => 'Sofia Herrera', 'email' => 'sofiah@ucsc.cl', 'celular' => '922334455', 'direccion' => 'Calle Independencia 789', 'fecha_nacimiento' => '1994-09-05', 'anio_ingreso' => 2017, 'password' => bcrypt('password')],
        //     ['run' => '88990011', 'name' => 'Pedro Jimenez', 'email' => 'pedrojimenez@ucsc.cl', 'celular' => '933221144', 'direccion' => 'Calle Nueva 222', 'fecha_nacimiento' => '1992-10-13', 'anio_ingreso' => 2018, 'password' => bcrypt('password')],
        //     ['run' => '99001122', 'name' => 'Laura Martínez', 'email' => 'lauram@ucsc.cl', 'celular' => '955443322', 'direccion' => 'Avenida del Sol 333', 'fecha_nacimiento' => '1993-03-30', 'anio_ingreso' => 2015, 'password' => bcrypt('password')],
        //     ['run' => '10111223', 'name' => 'Juanita Rios', 'email' => 'juanita@ucsc.cl', 'celular' => '944554433', 'direccion' => 'Calle Vicuña 555', 'fecha_nacimiento' => '1996-06-17', 'anio_ingreso' => 2017, 'password' => bcrypt('password')],
        //     ['run' => '11223344', 'name' => 'Ricardo Díaz', 'email' => 'ricardod@ucsc.cl', 'celular' => '912566788', 'direccion' => 'Calle Las Rosas 444', 'fecha_nacimiento' => '1990-05-23', 'anio_ingreso' => 2012, 'password' => bcrypt('password')],
        //     ['run' => '13214432', 'name' => 'Catalina Silva', 'email' => 'catalinas@ucsc.cl', 'celular' => '944667788', 'direccion' => 'Avenida de la Paz 666', 'fecha_nacimiento' => '1991-12-18', 'anio_ingreso' => 2013, 'password' => bcrypt('password')],
        //     ['run' => '33445966', 'name' => 'Raul Fernández', 'email' => 'raulf@ucsc.cl', 'celular' => '922334556', 'direccion' => 'Calle Los Andes 777', 'fecha_nacimiento' => '1992-07-25', 'anio_ingreso' => 2014, 'password' => bcrypt('password')]
        // ];

        // $profesores = [
        //     [
        //         'run' => '12345678',
        //         'name' => 'Carlos López',
        //         'email' => 'clopez@ucsc.cl',
        //         'password' => bcrypt('password'),
        //         'celular' => '911111111',
        //         'direccion' => 'Calle Los Almendros 123',
        //         'fecha_nacimiento' => '1985-06-24',
        //         'anio_ingreso' => 2010,
        //     ],
        //     [
        //         'run' => '98765432',
        //         'name' => 'Maria Torres',
        //         'email' => 'mtorres@ucsc.cl',
        //         'password' => bcrypt('password'),
        //         'celular' => '922222222',
        //         'direccion' => 'Avenida Las Rosas 456',
        //         'fecha_nacimiento' => '1990-03-15',
        //         'anio_ingreso' => 2012,
        //     ],
        // ];

        // foreach ($profesores as $profesor) {
        //     $user = User::create($profesor);
        //     $user->assignRole('Profesor');
        // }

        // foreach ($users as $userData) {
        //     User::create($userData)->assignRole('Usuario');
        // }
    }
}
