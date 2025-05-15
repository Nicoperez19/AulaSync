<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AsignaturasSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('asignaturas')->insert([
            [
                'id_asignatura' => 'ASG001',
                'nombre' => 'Matemáticas Avanzadas',
                'horas_directas' => 40,
                'horas_indirectas' => 60,
                'area_conocimiento' => 'Ciencias Exactas',
                'periodo' => 'Primer Semestre',
                'id' => 3, // nuevo id
                'id_carrera' => '1', // Ingeniería Civil
            ],
            [
                'id_asignatura' => 'ASG002',
                'nombre' => 'Física General',
                'horas_directas' => 45,
                'horas_indirectas' => 55,
                'area_conocimiento' => 'Ciencias Exactas',
                'periodo' => 'Segundo Semestre',
                'id' => 4, // nuevo id
                'id_carrera' => '2', // Arquitectura
            ],
            [
                'id_asignatura' => 'ASG003',
                'nombre' => 'Anatomía Humana',
                'horas_directas' => 50,
                'horas_indirectas' => 70,
                'area_conocimiento' => 'Ciencias Médicas',
                'periodo' => 'Primer Semestre',
                'id' => 5, // nuevo id
                'id_carrera' => '3', // Medicina
            ],
            [
                'id_asignatura' => 'ASG004',
                'nombre' => 'Psicología Educacional',
                'horas_directas' => 30,
                'horas_indirectas' => 50,
                'area_conocimiento' => 'Ciencias Sociales',
                'periodo' => 'Segundo Semestre',
                'id' => 6, // nuevo id
                'id_carrera' => '10', // Educación
            ],
            [
                'id_asignatura' => 'ASG005',
                'nombre' => 'Estructuras y Cálculos',
                'horas_directas' => 60,
                'horas_indirectas' => 80,
                'area_conocimiento' => 'Ingeniería Civil',
                'periodo' => 'Primer Semestre',
                'id' => 7, // nuevo id
                'id_carrera' => '1', // Ingeniería Civil
            ],
            [
                'id_asignatura' => 'ASG006',
                'nombre' => 'Planificación Urbana',
                'horas_directas' => 35,
                'horas_indirectas' => 65,
                'area_conocimiento' => 'Arquitectura',
                'periodo' => 'Segundo Semestre',
                'id' => 3, // nuevo id
                'id_carrera' => '2', // Arquitectura
            ],
            [
                'id_asignatura' => 'ASG007',
                'nombre' => 'Medicina Preventiva',
                'horas_directas' => 55,
                'horas_indirectas' => 45,
                'area_conocimiento' => 'Medicina',
                'periodo' => 'Primer Semestre',
                'id' => 4, // nuevo id
                'id_carrera' => '3', // Medicina
            ],
            [
                'id_asignatura' => 'ASG008',
                'nombre' => 'Didáctica y Pedagogía',
                'horas_directas' => 40,
                'horas_indirectas' => 60,
                'area_conocimiento' => 'Educación',
                'periodo' => 'Segundo Semestre',
                'id' => 5, // nuevo id
                'id_carrera' => '10', // Educación
            ],
            [
                'id_asignatura' => 'ASG009',
                'nombre' => 'Cálculo Integral',
                'horas_directas' => 40,
                'horas_indirectas' => 60,
                'area_conocimiento' => 'Ciencias Exactas',
                'periodo' => 'Primer Semestre',
                'id' => 6, // nuevo id
                'id_carrera' => '1', // Ingeniería Civil
            ],
        ]);
    }
}