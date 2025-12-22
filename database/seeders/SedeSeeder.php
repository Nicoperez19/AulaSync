<?php

namespace Database\Seeders;

use App\Models\Sede;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SedeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $sedes = [
            [
                'id_sede' => 'CT',
                'nombre_sede' => 'Cañete',
                'id_universidad' => 'UCSC',
                'comuna_id' => '233',
                'prefijo_sala' => 'ct',
            ],
            [
                'id_sede' => 'LA',
                'nombre_sede' => 'Los Ángeles',
                'id_universidad' => 'UCSC',
                'comuna_id' => '238',
                'prefijo_sala' => 'la',
            ],
            [
                'id_sede' => 'TH',
                'nombre_sede' => 'Talcahuano',
                'id_universidad' => 'UCSC',
                'comuna_id' => '228',
                'prefijo_sala' => 'th',
            ],
            [
                'id_sede' => 'CH',
                'nombre_sede' => 'Chillán',
                'id_universidad' => 'UCSC',
                'comuna_id' => '198',
                'prefijo_sala' => 'ch',
            ], [
                'id_sede' => 'CCP',
                'nombre_sede' => 'Concepción',
                'id_universidad' => 'UCSC',
                'comuna_id' => '219',
                'prefijo_sala' => 'ccp',
            ],

        ];
        foreach ($sedes as $sede) {
            Sede::create($sede);
        }
    }
}
