<?php

namespace Database\Seeders;

use App\Models\AsistenteAcademico;
use App\Models\AreaAcademica;
use Illuminate\Database\Seeder;

class AsistenteAcademicoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Asistentes académicas con sus escuelas específicas
        $asistentes = [
            [
                'nombre' => 'Geraldin Cuevas',
                'email' => 'gcuevas@ucsc.cl',
                'nombre_remitente' => 'Asistencia Académica - Escuela de Ingeniería',
                'telefono' => null,
                'id_area_academica' => 'ESC_INGPRO', // Escuela de Ingeniería, Procesos Industriales y Medio Ambiente
            ],
            [
                'nombre' => 'Romina Lizana',
                'email' => 'rlizana@ucsc.cl',
                'nombre_remitente' => 'Asistencia Académica - Escuela de Educación y Salud',
                'telefono' => null,
                'id_area_academica' => 'ESC_EDUSAL', // Escuela de Educación y Salud
            ],
        ];

        foreach ($asistentes as $asistenteData) {
            // Verificar que la escuela existe
            $escuela = AreaAcademica::find($asistenteData['id_area_academica']);
            
            if (!$escuela) {
                $this->command->warn("⚠ Escuela '{$asistenteData['id_area_academica']}' no encontrada para {$asistenteData['nombre']}");
                continue;
            }

            AsistenteAcademico::updateOrCreate(
                ['email' => $asistenteData['email']],
                [
                    'nombre' => $asistenteData['nombre'],
                    'nombre_remitente' => $asistenteData['nombre_remitente'],
                    'telefono' => $asistenteData['telefono'],
                    'id_area_academica' => $asistenteData['id_area_academica'],
                ]
            );

            $this->command->info("✓ Asistente '{$asistenteData['nombre']}' asignada a '{$escuela->nombre_area_academica}'");
        }

        $this->command->info('Asistentes académicas creadas exitosamente.');
    }
}
