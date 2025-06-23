<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Piso;

class PisoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Piso::create([
            'id' => 1,
            'numero_piso' => 1,
            'id_facultad' => 'IT_TH',
        ]);

        Piso::create([
            'id' => 2,
            'numero_piso' => 2,
            'id_facultad' => 'IT_TH',
        ]);
    }
}