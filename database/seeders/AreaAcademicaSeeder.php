<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\AreaAcademica;

class AreaAcademicaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
<<<<<<< HEAD
        AreaAcademica::create([
            'id_area_academica' => 'AA001', 
            'nombre_area_academica' => 'Departamento de Ciencias Computacionales',
            'tipo_area_academica' => 'departamento', 
            'id_facultad' => '6', 
        ]);

        AreaAcademica::create([
            'id_area_academica' => 'AA002', 
            'nombre_area_academica' => 'Escuela de Ingeniería',
            'tipo_area_academica' => 'escuela', 
            'id_facultad' => '6', 
        ]);

        AreaAcademica::create([
            'id_area_academica' => 'AA003', 
            'nombre_area_academica' => 'Departamento de Matemáticas',
            'tipo_area_academica' => 'departamento', 
            'id_facultad' => '6', 
        ]);

        AreaAcademica::create([
            'id_area_academica' => 'AA004', 
            'nombre_area_academica' => 'Escuela de Física',
            'tipo_area_academica' => 'escuela', 
            'id_facultad' => '6', 
        ]);

        AreaAcademica::create([
            'id_area_academica' => 'AA005', 
            'nombre_area_academica' => 'Departamento de Química',
            'tipo_area_academica' => 'departamento', 
            'id_facultad' => '6', 
        ]);

        AreaAcademica::create([
            'id_area_academica' => 'AA006', 
            'nombre_area_academica' => 'Escuela de Biología',
            'tipo_area_academica' => 'escuela', 
            'id_facultad' => '6', 
        ]);

        AreaAcademica::create([
            'id_area_academica' => 'AA007', 
            'nombre_area_academica' => 'Departamento de Psicología',
            'tipo_area_academica' => 'departamento', 
            'id_facultad' => '6', 
        ]);

        AreaAcademica::create([
            'id_area_academica' => 'AA008', 
            'nombre_area_academica' => 'Escuela de Filosofía',
            'tipo_area_academica' => 'escuela', 
            'id_facultad' => '6', 
        ]);

        AreaAcademica::create([
            'id_area_academica' => 'AA009', 
            'nombre_area_academica' => 'Departamento de Sociología',
            'tipo_area_academica' => 'departamento', 
            'id_facultad' => '6', 
        ]);

        AreaAcademica::create([
            'id_area_academica' => 'AA010', 
            'nombre_area_academica' => 'Escuela de Economía',
            'tipo_area_academica' => 'escuela', 
            'id_facultad' => '6', 
        ]);
=======
        $areas = [
            [
                'id_area_academica' => 'ESC_EDUSAL',
                'nombre_area_academica' => 'Escuela de Educación y Salud',
                'tipo_area_academica' => 'escuela',
                'id_facultad' => 'IT_TH',
            ],

            [
                'id_area_academica' => 'ESC_ADMSERV',
                'nombre_area_academica' => 'Escuela de Administración y Servicios',
                'tipo_area_academica' => 'escuela',
                'id_facultad' => 'IT_TH',
            ],

            [
                'id_area_academica' => 'ESC_INGPRO',
                'nombre_area_academica' => 'Escuela de Ingeniería, Procesos Industriales y Medio Ambiente',
                'tipo_area_academica' => 'escuela',
                'id_facultad' => 'IT_TH',
            ],
        ];
        foreach ($areas as $area) {
            AreaAcademica::create($area);
        }

>>>>>>> Nperez
    }
}
