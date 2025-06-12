<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Reserva;

class ReservasSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $reservas = [
            [
                'id_reserva' => 'R001',
                'hora' => '09:00:00',
                'fecha_reserva' => '2025-04-10',
                'id_espacio' => 'A1',
                'id' => 1, // Relacionado con Juan Perez
            ],
            [
                'id_reserva' => 'R002',
                'hora' => '11:00:00',
                'fecha_reserva' => '2025-04-10',
                'id_espacio' => 'L1',
                'id' => 2, // Relacionado con Ana Lopez
            ],
            [
                'id_reserva' => 'R003',
                'hora' => '13:00:00',
                'fecha_reserva' => '2025-04-11',
                'id_espacio' => 'B1',
                'id' => 3, // Relacionado con Carlos Soto
            ],
            [
                'id_reserva' => 'R004',
                'hora' => '15:00:00',
                'fecha_reserva' => '2025-04-11',
                'id_espacio' => 'SR1',
                'id' => 4, // Relacionado con Maria Gonzalez
            ],
            [
                'id_reserva' => 'R005',
                'hora' => '17:00:00',
                'fecha_reserva' => '2025-04-12',
                'id_espacio' => 'O1',
                'id' => 5, // Relacionado con Felipe Alvarez
            ],
        ];

        foreach ($reservas as $reserva) {
            Reserva::create($reserva);
        }
    }
}