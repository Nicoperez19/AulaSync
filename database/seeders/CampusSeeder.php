<?php

namespace Database\Seeders;

use App\Models\Campus;
use Illuminate\Database\Seeder;

class CampusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $campus = [
            [
                'id_campus' => 'CSA',
                'nombre_campus' => 'Campus San Andrés',
                'id_sede' => 'CCP',
            ],
            [
                'id_campus' => 'CSD',
                'nombre_campus' => 'Campus Santo Domingo',
                'id_sede' => 'CCP',
            ],
        ];

        foreach ($campus as $campu) {
            Campus::create($campu);
        }
    }
}
