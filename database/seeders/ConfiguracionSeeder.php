<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Configuracion;
use App\Models\Sede;

class ConfiguracionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create logo configuration for each sede
        $sedes = Sede::all();
        
        foreach ($sedes as $sede) {
            Configuracion::firstOrCreate(
                ['clave' => "logo_institucional_{$sede->id_sede}"],
                [
                    'valor' => '',
                    'descripcion' => "Logo institucional de la sede {$sede->nombre_sede}"
                ]
            );
        }

        // Create email configuration for each facultad (escuela)
        $facultades = \App\Models\Facultad::all();
        
        foreach ($facultades as $facultad) {
            Configuracion::firstOrCreate(
                ['clave' => "correo_administrativo_{$facultad->id_facultad}"],
                [
                    'valor' => '',
                    'descripcion' => "Correo administrativo de la escuela {$facultad->nombre_facultad}"
                ]
            );
            
            Configuracion::firstOrCreate(
                ['clave' => "nombre_remitente_{$facultad->id_facultad}"],
                [
                    'valor' => $facultad->nombre_facultad,
                    'descripcion' => "Nombre del remitente para correos de {$facultad->nombre_facultad}"
                ]
            );
        }

        // Create other configurations
        Configuracion::firstOrCreate(
            ['clave' => 'nombre_institucion'],
            [
                'valor' => 'Universidad del Desarrollo',
                'descripcion' => 'Nombre de la institución'
            ]
        );

        $this->command->info('Configuraciones iniciales creadas exitosamente!');
    }
}
