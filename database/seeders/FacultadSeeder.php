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
            'logo_facultad' => 'logo_facultad_1.png', 
            'nombre' => 'Facultad de Ciencias',
            'ubicacion' => 'Edificio A, Campus Central',
            'id_universidad' => 'U001',
        ]);

        // Registros genéricos adicionales
        Facultad::create([
            'id_facultad' => 2,
            'logo_facultad' => 'logo_facultad_2.png',
            'nombre' => 'Facultad de Ingeniería',
            'ubicacion' => 'Edificio B, Campus Norte',
            'id_universidad' => 'U001',
        ]);

        Facultad::create([
            'id_facultad' => 3,
            'logo_facultad' => 'logo_facultad_3.png',
            'nombre' => 'Facultad de Derecho',
            'ubicacion' => 'Edificio C, Campus Sur',
            'id_universidad' => 'U001',
        ]);

        Facultad::create([
            'id_facultad' => 4,
            'logo_facultad' => 'logo_facultad_4.png',
            'nombre' => 'Facultad de Medicina',
            'ubicacion' => 'Edificio D, Campus Este',
            'id_universidad' => 'U001',
        ]);

        Facultad::create([
            'id_facultad' => 5,
            'logo_facultad' => 'logo_facultad_5.png',
            'nombre' => 'Facultad de Psicología',
            'ubicacion' => 'Edificio E, Campus Oeste',
            'id_universidad' => 'U001',
        ]);

        Facultad::create([
            'id_facultad' => 6,
            'logo_facultad' => 'logo_facultad_6.png',
            'nombre' => 'Facultad de Arquitectura',
            'ubicacion' => 'Edificio F, Campus Central',
            'id_universidad' => 'U001',
        ]);
    }
}
