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
            'id' => 1,
            'numero_piso' => 1,
            'id_facultad' => 1, // Facultad de Ciencias (U001)
        ]);

        Piso::create([
            'id' => 2,
            'numero_piso' => 1,
            'id_facultad' => 2, // Facultad de Ingeniería (U001)
        ]);

        Piso::create([
            'id' => 3,
            'numero_piso' => 2,
            'id_facultad' => 3, // Facultad de Derecho (U001)
        ]);

        Piso::create([
            'id' => 4,
            'numero_piso' => 3,
            'id_facultad' => 4, // Facultad de Medicina (U001)
        ]);

        Piso::create([
            'id' => 5,
            'numero_piso' => 1,
            'id_facultad' => 5, // Facultad de Psicología (U001)
        ]);

        Piso::create([
            'id' => 6,
            'numero_piso' => 1,
            'id_facultad' => 6, // Facultad de Arquitectura (U001)
        ]);

        // Facultades de la Universidad U002
        Piso::create([
            'id' => 7,
            'numero_piso' => 1,
            'id_facultad' => 7, // Facultad de Economía (U002)
        ]);

        Piso::create([
            'id' => 8,
            'numero_piso' => 1,
            'id_facultad' => 8, // Facultad de Artes (U002)
        ]);

        Piso::create([
            'id' => 9,
            'numero_piso' => 2,
            'id_facultad' => 9, // Facultad de Educación (U002)
        ]);

        Piso::create([
            'id' => 10,
            'numero_piso' => 1,
            'id_facultad' => 10, // Facultad de Humanidades (U002)
        ]);

        Piso::create([
            'id' => 11,
            'numero_piso' => 2,
            'id_facultad' => 11, // Facultad de Turismo (U002)
        ]);

        Piso::create([
            'id' => 12,
            'numero_piso' => 3,
            'id_facultad' => 12, // Facultad de Agronomía (U002)
        ]);

        for ($i = 13; $i <= 15; $i++) { // Agrega pisos con IDs incrementales
            Piso::create([
                'id' => $i,
                'numero_piso' => $i - 12,
                'id_facultad' => 1, // Ejemplo: para Facultad de Ciencias
            ]);
        }
    }
}