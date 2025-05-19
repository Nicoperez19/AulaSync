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
          $modulos = [
            ['id_modulo' => 1, 'hora_inicio' => '08:10', 'hora_termino' => '09:00'],
            ['id_modulo' => 2, 'hora_inicio' => '09:10', 'hora_termino' => '10:00'],
            ['id_modulo' => 3, 'hora_inicio' => '10:10', 'hora_termino' => '11:00'],
            ['id_modulo' => 4, 'hora_inicio' => '11:10', 'hora_termino' => '12:00'],
            ['id_modulo' => 5, 'hora_inicio' => '12:10', 'hora_termino' => '13:00'],
            ['id_modulo' => 6, 'hora_inicio' => '13:10', 'hora_termino' => '14:00'],
            ['id_modulo' => 7, 'hora_inicio' => '14:10', 'hora_termino' => '15:00'],
            ['id_modulo' => 8, 'hora_inicio' => '15:10', 'hora_termino' => '16:00'],
            ['id_modulo' => 9, 'hora_inicio' => '16:10', 'hora_termino' => '17:00'],
            ['id_modulo' => 10, 'hora_inicio' => '17:10', 'hora_termino' => '18:00'],
            ['id_modulo' => 11, 'hora_inicio' => '18:10', 'hora_termino' => '19:00'],
            ['id_modulo' => 12, 'hora_inicio' => '19:10', 'hora_termino' => '20:00'],
            ['id_modulo' => 13, 'hora_inicio' => '20:10', 'hora_termino' => '21:00'],
            ['id_modulo' => 14, 'hora_inicio' => '21:10', 'hora_termino' => '22:00'],
            ['id_modulo' => 15, 'hora_inicio' => '22:10', 'hora_termino' => '23:00'],
                 
        ];
        foreach ($modulos as $modulo) {
            Modulo::create($modulo);
        }
    }
}
