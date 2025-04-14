<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Espacio;

class EspacioSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Espacio::create([
            'id_espacio' => 'A1',
            'piso_id' => 1, // Referencia a piso con id 1
            'tipo_espacio' => 'Aula',
            'estado' => 'Disponible',
            'puestos_disponibles' => 20,
        ]);

        Espacio::create([
            'id_espacio' => 'L1',
            'piso_id' => 2, // Referencia a piso con id 2
            'tipo_espacio' => 'Laboratorio',
            'estado' => 'Ocupado',
            'puestos_disponibles' => 15,
        ]);

        Espacio::create([
            'id_espacio' => 'B1',
            'piso_id' => 3, // Referencia a piso con id 3
            'tipo_espacio' => 'Biblioteca',
            'estado' => 'Reservado',
            'puestos_disponibles' => null,
        ]);

        Espacio::create([
            'id_espacio' => 'SR1',
            'piso_id' => 4, // Referencia a piso con id 4
            'tipo_espacio' => 'Sala de Reuniones',
            'estado' => 'Disponible',
            'puestos_disponibles' => 10,
        ]);

        Espacio::create([
            'id_espacio' => 'O1',
            'piso_id' => 5, // Referencia a piso con id 5
            'tipo_espacio' => 'Oficinas',
            'estado' => 'Disponible',
            'puestos_disponibles' => 5,
        ]);
    }
}