<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Facultad;

class FacultadSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Primer registro real
        Facultad::create([
            'id_facultad' => 1, 
            'logo_facultad' => 'facultad_ingenieria.png', 
            'nombre_facultad' => 'Facultad de Ciencias',
            'ubicacion_facultad' => 'Edificio A, Campus Central',
            'id_universidad' => 'U001',
        ]);

        // Registros genéricos adicionales
        Facultad::create([
            'id_facultad' => 2,
            'logo_facultad' => 'facultad_ingenieria.png',
            'nombre_facultad' => 'Facultad de Ingeniería',
            'ubicacion_facultad' => 'Edificio B, Campus Norte',
            'id_universidad' => 'U001',
        ]);

        Facultad::create([
            'id_facultad' => 3,
            'logo_facultad' => 'facultad_ingenieria.png',
            'nombre_facultad' => 'Facultad de Derecho',
            'ubicacion_facultad' => 'Edificio C, Campus Sur',
            'id_universidad' => 'U001',
        ]);

        Facultad::create([
            'id_facultad' => 4,
            'logo_facultad' => 'facultad_ingenieria.png',
            'nombre_facultad' => 'Facultad de Medicina',
            'ubicacion_facultad' => 'Edificio D, Campus Este',
            'id_universidad' => 'U001',
        ]);

        Facultad::create([
            'id_facultad' => 5,
            'logo_facultad' => 'facultad_ingenieria.png',
            'nombre_facultad' => 'Facultad de Psicología',
            'ubicacion_facultad' => 'Edificio E, Campus Oeste',
            'id_universidad' => 'U001',
        ]);

        Facultad::create([
            'id_facultad' => 6,
            'logo_facultad' => 'facultad_ingenieria.png',
            'nombre_facultad' => 'Facultad de Arquitectura',
            'ubicacion_facultad' => 'Edificio F, Campus Central',
            'id_universidad' => 'U001',
        ]);
    }
}
