<?php

namespace Database\Seeders;

use App\Models\Modulo;
use Illuminate\Database\Seeder;

class ModulosSeeder extends Seeder
{
    public function run(): void
    {
        $dias = [
            'LU' => 'lunes',
            'MA' => 'martes',
            'MI' => 'miércoles',
            'JU' => 'jueves',
            'VI' => 'viernes',
            'SA' => 'sábado',
        ];

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

        foreach ($dias as $codigoDia => $nombreDia) {
            foreach ($modulosBase as $index => $modulo) {
                $numeroModulo = $index + 1;
                $idModulo = $codigoDia . '.' . $numeroModulo;

                Modulo::create([
                    'id_modulo'     => $idModulo,
                    'dia'           => $nombreDia,
                    'hora_inicio'   => $modulo['hora_inicio'],
                    'hora_termino'  => $modulo['hora_termino'],
                ]);
            }
        }
    }
}
