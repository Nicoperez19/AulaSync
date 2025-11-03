<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Configuracion;

class ConfiguracionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create initial configuration entries
        Configuracion::firstOrCreate(
            ['clave' => 'logo_institucional'],
            [
                'valor' => '',
                'descripcion' => 'Logo institucional que se muestra en todo el sistema'
            ]
        );

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
