<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Universidad;
use Carbon\Carbon;

class UniversidadSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
      
        Universidad::create([
            'id_universidad' => 'U001',
            'nombre_universidad' => 'Universidad de Chiile',
            'direccion_universidad' => 'Av. Libertador Bernardo O’Higgins 1058, Santiago',
            'telefono_universidad' => '+56 2 2978 2000',
            'comunas_id' => 83, // Santiago
            'imagen_logo' => 'logos/u_chile.png',
            
        ]);

        // Usando un bucle para crear universidades genéricas
        for ($i = 2; $i <= 10; $i++) {
            Universidad::create([
                'id_universidad' => 'U00' . $i,
                'nombre_universidad' => 'Universidad Genérica ' . $i,
                'direccion_universidad' => 'Calle Falsa 123, Ciudad Genérica',
                'telefono_universidad' => '+56 9 0000 000' . $i,
                'comunas_id' => rand(1, 200), // Asignando comunas aleatorias
                'imagen_logo' => null,
               
            ]);
        }
    }
}
