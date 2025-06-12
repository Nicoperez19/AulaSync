<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Universidad;

class UniversidadSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Universidad::create([
            'id_universidad' => 'UCSC',
            'nombre_universidad' => 'Universidad Católica de la Santisima Concepcion',
            'imagen_logo' => 'ucsc.png', 
        ]);
    }
}
