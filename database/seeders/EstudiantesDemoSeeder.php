<?php

namespace Database\Seeders;

use App\Models\Asignatura;
use App\Models\Estudiante;
use App\Models\Inscripcion;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EstudiantesDemoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $estudiantesData = [
            ['run' => '201234567', 'nombre' => 'Matías Alejandro Silva Muñoz', 'email' => 'matias.silva@alumnos.local'],
            ['run' => '202345678', 'nombre' => 'Camila Ignacia Rojas Valenzuela', 'email' => 'camila.rojas@alumnos.local'],
            ['run' => '203456789', 'nombre' => 'Nicolás Esteban Morales Castro', 'email' => 'nicolas.morales@alumnos.local'],
            ['run' => '204567890', 'nombre' => 'Valentina Paz Sepúlveda Díaz', 'email' => 'valentina.sepulveda@alumnos.local'],
            ['run' => '205678901', 'nombre' => 'Benjamín Ignacio Torres Herrera', 'email' => 'benjamin.torres@alumnos.local'],
            ['run' => '206789012', 'nombre' => 'Sofía Antonella Fuentes Lagos', 'email' => 'sofia.fuentes@alumnos.local'],
            ['run' => '207890123', 'nombre' => 'Lucas Gabriel Figueroa Riquelme', 'email' => 'lucas.figueroa@alumnos.local'],
            ['run' => '208901234', 'nombre' => 'Martina Isidora Parra Concha', 'email' => 'martina.parra@alumnos.local'],
            ['run' => '209012345', 'nombre' => 'Vicente Andrés Araya Pinto', 'email' => 'vicente.araya@alumnos.local'],
            ['run' => '210123456', 'nombre' => 'Fernanda Belén Navarro Soto', 'email' => 'fernanda.navarro@alumnos.local'],
        ];

        $estudiantesIds = [];
        foreach ($estudiantesData as $data) {
            $est = Estudiante::firstOrCreate(
                ['run' => $data['run']],
                [
                    'nombre' => $data['nombre'],
                    'email' => $data['email'],
                    'origen' => 'manual',
                ]
            );
            $estudiantesIds[] = $est->id;
        }

        // Asociar a asignaturas existentes en la sede
        $asignaturas = Asignatura::take(5)->get();

        foreach ($asignaturas as $asig) {
            foreach ($estudiantesIds as $estId) {
                Inscripcion::firstOrCreate(
                    [
                        'id_asignatura' => $asig->id_asignatura,
                        'estudiante_id' => $estId,
                    ],
                    [
                        'periodo' => $asig->periodo ?? '2026-1',
                    ]
                );
            }
        }
    }
}
