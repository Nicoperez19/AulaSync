<?php

namespace Database\Seeders;

use App\Models\DiaFeriado;
use Illuminate\Database\Seeder;

class DiasFeriadosSeeder extends Seeder
{
    /**
     * Seed Chilean legal holidays for 2025.
     * These dates can be adjusted annually.
     */
    public function run(): void
    {
        $feriados2025 = [
            // Feriados fijos
            [
                'fecha_inicio' => '2025-01-01',
                'fecha_fin' => '2025-01-01',
                'nombre' => 'Año Nuevo',
                'descripcion' => 'Feriado nacional - Inicio del año calendario',
                'tipo' => 'feriado',
                'activo' => true,
            ],
            [
                'fecha_inicio' => '2025-04-18',
                'fecha_fin' => '2025-04-18',
                'nombre' => 'Viernes Santo',
                'descripcion' => 'Feriado religioso',
                'tipo' => 'feriado',
                'activo' => true,
            ],
            [
                'fecha_inicio' => '2025-04-19',
                'fecha_fin' => '2025-04-19',
                'nombre' => 'Sábado Santo',
                'descripcion' => 'Feriado religioso',
                'tipo' => 'feriado',
                'activo' => true,
            ],
            [
                'fecha_inicio' => '2025-05-01',
                'fecha_fin' => '2025-05-01',
                'nombre' => 'Día del Trabajador',
                'descripcion' => 'Feriado nacional - Día Internacional del Trabajo',
                'tipo' => 'feriado',
                'activo' => true,
            ],
            [
                'fecha_inicio' => '2025-05-21',
                'fecha_fin' => '2025-05-21',
                'nombre' => 'Día de las Glorias Navales',
                'descripcion' => 'Feriado nacional - Combate Naval de Iquique',
                'tipo' => 'feriado',
                'activo' => true,
            ],
            [
                'fecha_inicio' => '2025-06-29',
                'fecha_fin' => '2025-06-29',
                'nombre' => 'San Pedro y San Pablo',
                'descripcion' => 'Feriado religioso',
                'tipo' => 'feriado',
                'activo' => true,
            ],
            [
                'fecha_inicio' => '2025-07-16',
                'fecha_fin' => '2025-07-16',
                'nombre' => 'Día de la Virgen del Carmen',
                'descripcion' => 'Feriado religioso - Patrona de Chile',
                'tipo' => 'feriado',
                'activo' => true,
            ],
            [
                'fecha_inicio' => '2025-08-15',
                'fecha_fin' => '2025-08-15',
                'nombre' => 'Asunción de la Virgen',
                'descripcion' => 'Feriado religioso',
                'tipo' => 'feriado',
                'activo' => true,
            ],
            [
                'fecha_inicio' => '2025-09-18',
                'fecha_fin' => '2025-09-18',
                'nombre' => 'Día de la Independencia Nacional',
                'descripcion' => 'Feriado nacional - Primera Junta Nacional de Gobierno',
                'tipo' => 'feriado',
                'activo' => true,
            ],
            [
                'fecha_inicio' => '2025-09-19',
                'fecha_fin' => '2025-09-19',
                'nombre' => 'Día de las Glorias del Ejército',
                'descripcion' => 'Feriado nacional',
                'tipo' => 'feriado',
                'activo' => true,
            ],
            [
                'fecha_inicio' => '2025-10-12',
                'fecha_fin' => '2025-10-12',
                'nombre' => 'Encuentro de Dos Mundos',
                'descripcion' => 'Feriado nacional - Día de la Raza',
                'tipo' => 'feriado',
                'activo' => true,
            ],
            [
                'fecha_inicio' => '2025-10-31',
                'fecha_fin' => '2025-10-31',
                'nombre' => 'Día de las Iglesias Evangélicas y Protestantes',
                'descripcion' => 'Feriado religioso',
                'tipo' => 'feriado',
                'activo' => true,
            ],
            [
                'fecha_inicio' => '2025-11-01',
                'fecha_fin' => '2025-11-01',
                'nombre' => 'Día de Todos los Santos',
                'descripcion' => 'Feriado religioso',
                'tipo' => 'feriado',
                'activo' => true,
            ],
            [
                'fecha_inicio' => '2025-12-08',
                'fecha_fin' => '2025-12-08',
                'nombre' => 'Inmaculada Concepción',
                'descripcion' => 'Feriado religioso',
                'tipo' => 'feriado',
                'activo' => true,
            ],
            [
                'fecha_inicio' => '2025-12-25',
                'fecha_fin' => '2025-12-25',
                'nombre' => 'Navidad',
                'descripcion' => 'Feriado religioso - Nacimiento de Jesús',
                'tipo' => 'feriado',
                'activo' => true,
            ],
            
            // Recesos académicos típicos
            [
                'fecha_inicio' => '2025-07-14',
                'fecha_fin' => '2025-07-25',
                'nombre' => 'Receso de Invierno',
                'descripcion' => 'Periodo de receso académico entre semestres',
                'tipo' => 'semana_reajuste',
                'activo' => true,
            ],
            [
                'fecha_inicio' => '2025-09-15',
                'fecha_fin' => '2025-09-22',
                'nombre' => 'Receso Fiestas Patrias',
                'descripcion' => 'Periodo extendido de fiestas patrias',
                'tipo' => 'suspension_actividades',
                'activo' => true,
            ],
        ];

        foreach ($feriados2025 as $feriado) {
            // Usar updateOrCreate para evitar duplicados
            DiaFeriado::updateOrCreate(
                [
                    'fecha_inicio' => $feriado['fecha_inicio'],
                    'fecha_fin' => $feriado['fecha_fin'],
                    'nombre' => $feriado['nombre'],
                ],
                [
                    'descripcion' => $feriado['descripcion'],
                    'tipo' => $feriado['tipo'],
                    'activo' => $feriado['activo'],
                    'created_by' => null, // Sistema
                ]
            );
        }
    }
}
