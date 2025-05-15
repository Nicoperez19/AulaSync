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

    
        // Facultades adicionales para la universidad U002
        Facultad::create([
            'id_facultad' => 7,
            'logo_facultad' => 'facultad_economia.png',
            'nombre_facultad' => 'Facultad de Economía',
            'ubicacion_facultad' => 'Edificio G, Campus Central',
            'id_universidad' => 'U002',
        ]);

        Facultad::create([
            'id_facultad' => 8,
            'logo_facultad' => 'facultad_artes.png',
            'nombre_facultad' => 'Facultad de Artes',
            'ubicacion_facultad' => 'Edificio H, Campus Norte',
            'id_universidad' => 'U002',
        ]);

        Facultad::create([
            'id_facultad' => 9,
            'logo_facultad' => 'facultad_educacion.png',
            'nombre_facultad' => 'Facultad de Educación',
            'ubicacion_facultad' => 'Edificio I, Campus Sur',
            'id_universidad' => 'U002',
        ]);

        Facultad::create([
            'id_facultad' => 10,
            'logo_facultad' => 'facultad_humanidades.png',
            'nombre_facultad' => 'Facultad de Humanidades',
            'ubicacion_facultad' => 'Edificio J, Campus Este',
            'id_universidad' => 'U002',
        ]);

        Facultad::create([
            'id_facultad' => 11,
            'logo_facultad' => 'facultad_turismo.png',
            'nombre_facultad' => 'Facultad de Turismo',
            'ubicacion_facultad' => 'Edificio K, Campus Oeste',
            'id_universidad' => 'U002',
        ]);

        Facultad::create([
            'id_facultad' => 12,
            'logo_facultad' => 'facultad_agronomia.png',
            'nombre_facultad' => 'Facultad de Agronomía',
            'ubicacion_facultad' => 'Edificio L, Campus Central',
            'id_universidad' => 'U002',
        ]);
    }

}
