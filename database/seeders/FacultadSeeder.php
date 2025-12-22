<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Facultad;

class FacultadSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $facultades = [
            [
                'id_facultad' => 'IT_TH',
                'nombre_facultad' => 'Instituto Tecnológico',
                'id_universidad' => 'UCSC',
                'id_sede' => 'TH',
                'id_campus' => null,
            ],[
                'id_facultad' => 'IT_CT',
                'nombre_facultad' => 'Instituto Tecnológico',
                'id_universidad' => 'UCSC',
                'id_sede' => 'CT',
                'id_campus' => null,
            ],[
                'id_facultad' => 'IT_LA',
                'nombre_facultad' => 'Instituto Tecnológico',
                'id_universidad' => 'UCSC',
                'id_sede' => 'LA',
                'id_campus' => null,
            ],
            [
                'id_facultad' => 'IT_CH',
                'nombre_facultad' => 'Instituto Tecnológico',
                'id_universidad' => 'UCSC',
                'id_sede' => 'CH',
                'id_campus' => null,
            ],
            // [
            //     'id_facultad' => 'FACEA',
            //     'nombre_facultad' => 'Facultad de Ciencias Económicas y Administrativas',
            //     'id_universidad' => 'UCSC',
            //     'id_sede' => 'CCP',
            //     'id_campus' => 'CSA',
            // ],
        ];
        
        foreach ($facultades as $facultad) {
            Facultad::create($facultad);
        }
    }

}
