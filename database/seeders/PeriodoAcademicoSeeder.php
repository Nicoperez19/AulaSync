<?php

namespace Database\Seeders;

use App\Models\PeriodoAcademico;
use Illuminate\Database\Seeder;

class PeriodoAcademicoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $periodos = [
            [
                'anio' => 2025,
                'semestre' => 1,
                'fecha_inicio' => '2025-03-03',
                'fecha_fin' => '2025-07-11',
                'activo' => true,
            ],
            [
                'anio' => 2025,
                'semestre' => 2,
                'fecha_inicio' => '2025-07-28',
                'fecha_fin' => '2025-12-19',
                'activo' => true,
            ],
            [
                'anio' => 2026,
                'semestre' => 1,
                'fecha_inicio' => '2026-03-02',
                'fecha_fin' => '2026-07-10',
                'activo' => true,
            ],
            [
                'anio' => 2026,
                'semestre' => 2,
                'fecha_inicio' => '2026-07-27',
                'fecha_fin' => '2026-12-18',
                'activo' => true,
            ],
        ];

        foreach ($periodos as $periodo) {
            PeriodoAcademico::updateOrCreate(
                [
                    'anio' => $periodo['anio'],
                    'semestre' => $periodo['semestre'],
                ],
                [
                    'fecha_inicio' => $periodo['fecha_inicio'],
                    'fecha_fin' => $periodo['fecha_fin'],
                    'activo' => $periodo['activo'],
                    'created_by' => null,
                ]
            );
        }
    }
}
