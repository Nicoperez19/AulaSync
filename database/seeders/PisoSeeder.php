<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Piso;

class PisoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Facultades de la Universidad U001
        Piso::create([
            'id_piso' => 'P001-1',
            'numero_piso' => 1,
            'id_facultad' => 1, // Facultad de Ciencias (U001)
        ]);

        Piso::create([
            'id_piso' => 'P002-2',
            'numero_piso' => 1,
            'id_facultad' => 2, // Facultad de Ingeniería (U001)
        ]);

        Piso::create([
            'id_piso' => 'P003-3',
            'numero_piso' => 1,
            'id_facultad' => 3, // Facultad de Derecho (U001)
        ]);

        Piso::create([
            'id_piso' => 'P004-4',
            'numero_piso' => 1,
            'id_facultad' => 4, // Facultad de Medicina (U001)
        ]);

        Piso::create([
            'id_piso' => 'P005-5',
            'numero_piso' => 1,
            'id_facultad' => 5, // Facultad de Psicología (U001)
        ]);

        Piso::create([
            'id_piso' => 'P006-6',
            'numero_piso' => 1,
            'id_facultad' => 6, // Facultad de Arquitectura (U001)
        ]);

        // Facultades de la Universidad U002
        Piso::create([
            'id_piso' => 'P007-7',
            'numero_piso' => 1,
            'id_facultad' => 7, // Facultad de Economía (U002)
        ]);

        Piso::create([
            'id_piso' => 'P008-8',
            'numero_piso' => 1,
            'id_facultad' => 8, // Facultad de Artes (U002)
        ]);

        Piso::create([
            'id_piso' => 'P009-9',
            'numero_piso' => 1,
            'id_facultad' => 9, // Facultad de Educación (U002)
        ]);

        Piso::create([
            'id_piso' => 'P010-10',
            'numero_piso' => 1,
            'id_facultad' => 10, // Facultad de Humanidades (U002)
        ]);

        Piso::create([
            'id_piso' => 'P011-11',
            'numero_piso' => 1,
            'id_facultad' => 11, // Facultad de Turismo (U002)
        ]);

        Piso::create([
            'id_piso' => 'P012-12',
            'numero_piso' => 1,
            'id_facultad' => 12, // Facultad de Agronomía (U002)
        ]);

        // Opcional: Añadir múltiples pisos para cada facultad si es necesario
        for ($i = 2; $i <= 3; $i++) { // Agrega pisos 2 y 3 a cada facultad
            Piso::create([
                'id_piso' => "P013-{$i}",
                'numero_piso' => $i,
                'id_facultad' => 1, // Ejemplo: para Facultad de Ciencias
            ]);
        }
    }
}