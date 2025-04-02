<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Carrera;

class CarreraSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $carreras = [
            'Ingeniería Civil',
            'Arquitectura',
            'Medicina',
            'Psicología',
            'Derecho',
            'Contabilidad',
            'Marketing',
            'Biología',
            'Filosofía',
            'Educación'
        ];

        // Empezamos con el id_carrera en 1
        $id_carrera = 1;

        foreach ($carreras as $nombre) {
            Carrera::create([
                'id_carrera' => $id_carrera, // Usamos un número secuencial
                'nombre' => $nombre,
                'id_facultad' => 6,  // Aquí defines el id de la facultad correspondiente
            ]);
            $id_carrera++; // Incrementamos el id para la siguiente carrera
        }
    }
}
