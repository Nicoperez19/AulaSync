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
            'run' => '12345678-9',
            'name' => 'Admin',
            'email' => 'admin@ucsc.cl',
            'password' => bcrypt('password'),
            'celular' => '987654321',
            'direccion' => 'Calle Falsa 123',
            'fecha_nacimiento' => '1985-05-20',
            'anio_ingreso' => 2010,
        ])->assignRole('Administrador');

        User::create([
            'run' => '19812524-5',
            'name' => 'Nicolas Perez',
            'email' => 'nperez@ucsc.cl',
            'password' => bcrypt('password'),
            'celular' => '912345678',
            'direccion' => 'Avenida Siempreviva 742',
            'fecha_nacimiento' => '1992-08-15',
            'anio_ingreso' => 2015,
        ])->assignRole('Usuario');

        $users = [
            ['run' => '22334455-6', 'name' => 'Juan Perez', 'email' => 'juanp@ucsc.cl', 'celular' => '912233445', 'direccion' => 'Calle Real 101', 'fecha_nacimiento' => '1990-02-25', 'anio_ingreso' => 2012, 'password' => bcrypt('password')],
            ['run' => '33445566-7', 'name' => 'Ana Lopez', 'email' => 'analopez@ucsc.cl', 'celular' => '976554433', 'direccion' => 'Avenida Central 222', 'fecha_nacimiento' => '1993-07-14', 'anio_ingreso' => 2013, 'password' => bcrypt('password')],
            ['run' => '44556677-8', 'name' => 'Carlos Soto', 'email' => 'carlossoto@ucsc.cl', 'celular' => '976877665', 'direccion' => 'Calle Las Palmas 321', 'fecha_nacimiento' => '1995-01-10', 'anio_ingreso' => 2014, 'password' => bcrypt('password')],
            ['run' => '55667788-9', 'name' => 'Maria Gonzalez', 'email' => 'mariagonzalez@ucsc.cl', 'celular' => '987654322', 'direccion' => 'Calle Ficticia 654', 'fecha_nacimiento' => '1989-11-22', 'anio_ingreso' => 2011, 'password' => bcrypt('password')],
            ['run' => '66778899-0', 'name' => 'Felipe Alvarez', 'email' => 'felipealvarez@ucsc.cl', 'celular' => '999887766', 'direccion' => 'Avenida Libertador 543', 'fecha_nacimiento' => '1991-04-12', 'anio_ingreso' => 2016, 'password' => bcrypt('password')],
            ['run' => '77889900-1', 'name' => 'Sofia Herrera', 'email' => 'sofiah@ucsc.cl', 'celular' => '922334455', 'direccion' => 'Calle Independencia 789', 'fecha_nacimiento' => '1994-09-05', 'anio_ingreso' => 2017, 'password' => bcrypt('password')],
            ['run' => '88990011-2', 'name' => 'Pedro Jimenez', 'email' => 'pedrojimenez@ucsc.cl', 'celular' => '933221144', 'direccion' => 'Calle Nueva 222', 'fecha_nacimiento' => '1992-10-13', 'anio_ingreso' => 2018, 'password' => bcrypt('password')],
            ['run' => '99001122-3', 'name' => 'Laura Martínez', 'email' => 'lauram@ucsc.cl', 'celular' => '955443322', 'direccion' => 'Avenida del Sol 333', 'fecha_nacimiento' => '1993-03-30', 'anio_ingreso' => 2015, 'password' => bcrypt('password')],
            ['run' => '10111223-4', 'name' => 'Juanita Rios', 'email' => 'juanita@ucsc.cl', 'celular' => '944554433', 'direccion' => 'Calle Vicuña 555', 'fecha_nacimiento' => '1996-06-17', 'anio_ingreso' => 2017, 'password' => bcrypt('password')],
            ['run' => '11223344-5', 'name' => 'Ricardo Díaz', 'email' => 'ricardod@ucsc.cl', 'celular' => '912566788', 'direccion' => 'Calle Las Rosas 444', 'fecha_nacimiento' => '1990-05-23', 'anio_ingreso' => 2012, 'password' => bcrypt('password')],
            ['run' => '22334455-6', 'name' => 'Catalina Silva', 'email' => 'catalinas@ucsc.cl', 'celular' => '944667788', 'direccion' => 'Avenida de la Paz 666', 'fecha_nacimiento' => '1991-12-18', 'anio_ingreso' => 2013, 'password' => bcrypt('password')],
            ['run' => '33445566-7', 'name' => 'Raul Fernández', 'email' => 'raulf@ucsc.cl', 'celular' => '922334556', 'direccion' => 'Calle Los Andes 777', 'fecha_nacimiento' => '1992-07-25', 'anio_ingreso' => 2014, 'password' => bcrypt('password')],
            ['run' => '44556677-8', 'name' => 'Victoria Pérez', 'email' => 'victoriap@ucsc.cl', 'celular' => '933445667', 'direccion' => 'Avenida Francia 888', 'fecha_nacimiento' => '1993-11-05', 'anio_ingreso' => 2016, 'password' => bcrypt('password')],
            ['run' => '55667788-9', 'name' => 'Lucas Bravo', 'email' => 'lucasb@ucsc.cl', 'celular' => '955554433', 'direccion' => 'Calle Principal 999', 'fecha_nacimiento' => '1994-02-17', 'anio_ingreso' => 2017, 'password' => bcrypt('password')],
            ['run' => '66778899-0', 'name' => 'Margarita Lozano', 'email' => 'margarital@ucsc.cl', 'celular' => '922556677', 'direccion' => 'Calle de la Luna 111', 'fecha_nacimiento' => '1995-08-30', 'anio_ingreso' => 2018, 'password' => bcrypt('password')],
            ['run' => '77889900-1', 'name' => 'Antonio Gómez', 'email' => 'antoniog@ucsc.cl', 'celular' => '933667788', 'direccion' => 'Avenida Litoral 222', 'fecha_nacimiento' => '1996-01-14', 'anio_ingreso' => 2019, 'password' => bcrypt('password')],
            ['run' => '88990011-2', 'name' => 'Cristina Morales', 'email' => 'cristinam@ucsc.cl', 'celular' => '944778899', 'direccion' => 'Calle del Sol 333', 'fecha_nacimiento' => '1997-04-20', 'anio_ingreso' => 2020, 'password' => bcrypt('password')],
            ['run' => '99001122-3', 'name' => 'Enrique Soto', 'email' => 'enriques@ucsc.cl', 'celular' => '955889900', 'direccion' => 'Calle Girasol 444', 'fecha_nacimiento' => '1998-05-25', 'anio_ingreso' => 2021, 'password' => bcrypt('password')]
        ];


        $profesores = [
            [
                'run' => '12345678-9',
                'name' => 'Carlos López',
                'email' => 'clopez@ucsc.cl',
                'password' => bcrypt('password'),
                'celular' => '911111111',
                'direccion' => 'Calle Los Almendros 123',
                'fecha_nacimiento' => '1985-06-24',
                'anio_ingreso' => 2010,
            ],
            [
                'run' => '98765432-1',
                'name' => 'Maria Torres',
                'email' => 'mtorres@ucsc.cl',
                'password' => bcrypt('password'),
                'celular' => '922222222',
                'direccion' => 'Avenida Las Rosas 456',
                'fecha_nacimiento' => '1990-03-15',
                'anio_ingreso' => 2012,
            ],
            [
                'run' => '19283746-5',
                'name' => 'Luis Gutierrez',
                'email' => 'lgutierrez@ucsc.cl',
                'password' => bcrypt('password'),
                'celular' => '933333333',
                'direccion' => 'Pasaje Los Pinos 789',
                'fecha_nacimiento' => '1988-11-05',
                'anio_ingreso' => 2008,
            ],
            [
                'run' => '56473829-0',
                'name' => 'Ana González',
                'email' => 'agonzalez@ucsc.cl',
                'password' => bcrypt('password'),
                'celular' => '944444444',
                'direccion' => 'Calle Las Palmas 321',
                'fecha_nacimiento' => '1992-04-20',
                'anio_ingreso' => 2015,
            ],
            [
                'run' => '28374659-2',
                'name' => 'Juan Perez',
                'email' => 'jperez@ucsc.cl',
                'password' => bcrypt('password'),
                'celular' => '955555555',
                'direccion' => 'Avenida Siempreviva 742',
                'fecha_nacimiento' => '1980-08-15',
                'anio_ingreso' => 2005,
            ],
        ];

        foreach ($profesores as $profesor) {
            $user = User::create($profesor);
            $user->assignRole('Profesor');
        }

        foreach ($users as $userData) {
            User::create($userData)->assignRole('Usuario');
        }
    }
}
