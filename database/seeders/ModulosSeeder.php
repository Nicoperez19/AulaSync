<?php

namespace Database\Seeders;

use App\Models\Modulo;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;


class ModulosSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $dias = ['lunes', 'martes', 'miércoles', 'jueves', 'viernes', 'sábado'];

        $modulosBase = [
            ['hora_inicio' => '08:10', 'hora_termino' => '09:00'],
            ['hora_inicio' => '09:10', 'hora_termino' => '10:00'],
            ['hora_inicio' => '10:10', 'hora_termino' => '11:00'],
            ['hora_inicio' => '11:10', 'hora_termino' => '12:00'],
            ['hora_inicio' => '12:10', 'hora_termino' => '13:00'],
            ['hora_inicio' => '13:10', 'hora_termino' => '14:00'],
            ['hora_inicio' => '14:10', 'hora_termino' => '15:00'],
            ['hora_inicio' => '15:10', 'hora_termino' => '16:00'],
            ['hora_inicio' => '16:10', 'hora_termino' => '17:00'],
            ['hora_inicio' => '17:10', 'hora_termino' => '18:00'],
            ['hora_inicio' => '18:10', 'hora_termino' => '19:00'],
            ['hora_inicio' => '19:10', 'hora_termino' => '20:00'],
            ['hora_inicio' => '20:10', 'hora_termino' => '21:00'],
            ['hora_inicio' => '21:10', 'hora_termino' => '22:00'],
            ['hora_inicio' => '22:10', 'hora_termino' => '23:00'],
        ];

        $id = 1;
        foreach ($dias as $dia) {
            foreach ($modulosBase as $index => $modulo) {
                Modulo::create([
                    'id_modulo' => $id++,
                    'dia' => $dia,
                    'hora_inicio' => $modulo['hora_inicio'],
                    'hora_termino' => $modulo['hora_termino'],
                ]);
            }
        }
    }

}
