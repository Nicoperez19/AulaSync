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

    }
}
