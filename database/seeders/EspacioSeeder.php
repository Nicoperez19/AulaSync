<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Espacio;

class EspacioSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        $espacios = [
            [
                'id_espacio' => 'TH-30',
                'nombre_espacio' => 'Laboratorio Termodinámica',
                'piso_id' => 1,
                'tipo_espacio' => 'Laboratorio',
                'estado' => 'Disponible',
                'puestos_disponibles' => 80,
            ],
            [
                'id_espacio' => 'TH-LA9',
                'nombre_espacio' => 'Taller de Soldadura',
                'piso_id' => 1,
                'tipo_espacio' => 'Taller',
                'estado' => 'Disponible',
                'puestos_disponibles' => 5,
            ],
            [
                'id_espacio' => 'TH-40',
                'nombre_espacio' => 'Sala de Clases',
                'piso_id' => 1,
                'tipo_espacio' => 'Aula',
                'estado' => 'Disponible',
                'puestos_disponibles' => 40,
            ],
            [
                'id_espacio' => 'TH-50',
                'nombre_espacio' => 'Sala de Clases',
                'piso_id' => 1,
                'tipo_espacio' => 'Aula',
                'estado' => 'Disponible',
                'puestos_disponibles' => 40,
            ],
            [
                'id_espacio' => 'TH-60',
                'nombre_espacio' => 'Sala de Clases',
                'piso_id' => 1,
                'tipo_espacio' => 'Aula',
                'estado' => 'Disponible',
                'puestos_disponibles' => 40,
            ],
            [
                'id_espacio' => 'TH-C5',
                'nombre_espacio' => 'Sala de Estudio',
                'piso_id' => 1,
                'tipo_espacio' => 'Sala de Estudio',
                'estado' => 'Disponible',
                'puestos_disponibles' => null,
            ],
            [
                'id_espacio' => 'TH-C4',
                'nombre_espacio' => 'Sala de Estudio',
                'piso_id' => 1,
                'tipo_espacio' => 'Sala de Estudio',
                'estado' => 'Disponible',
                'puestos_disponibles' => null,
            ],
            [
                'id_espacio' => 'TH-C3',
                'nombre_espacio' => 'Sala de Estudio',
                'piso_id' => 1,
                'tipo_espacio' => 'Sala de Estudio',
                'estado' => 'Disponible',
                'puestos_disponibles' => 4,
            ],
            [
                'id_espacio' => 'TH-C2',
                'nombre_espacio' => 'Sala de Estudio',
                'piso_id' => 1,
                'tipo_espacio' => 'Sala de Estudio',
                'estado' => 'Disponible',
                'puestos_disponibles' => 4,
            ],
            [
                'id_espacio' => 'TH-C1',
                'nombre_espacio' => 'Sala de Estudio',
                'piso_id' => 1,
                'tipo_espacio' => 'Sala de Estudio',
                'estado' => 'Disponible',
                'puestos_disponibles' => 4,
            ],
            [
                'id_espacio' => 'TH-AUD',
                'nombre_espacio' => 'Auditorio',
                'piso_id' => 1,
                'tipo_espacio' => 'Auditorio',
                'estado' => 'Disponible',
                'puestos_disponibles' => 70,
            ],
            [
                'id_espacio' => 'TH-LAB',
                'nombre_espacio' => 'Laboratorio de Computación',
                'piso_id' => 1,
                'tipo_espacio' => 'Laboratorio',
                'estado' => 'Disponible',
                'puestos_disponibles' => 70,
            ],
            [
                'id_espacio' => 'TH-L01',
                'nombre_espacio' => 'Laboratorio de Hidráulica y Neumática',
                'piso_id' => 1,
                'tipo_espacio' => 'Taller',
                'estado' => 'Disponible',
                'puestos_disponibles' => 32,
            ],
            [
                'id_espacio' => 'TH-L02',
                'nombre_espacio' => 'Laboratorio de Electricidad y Electrica',
                'piso_id' => 1,
                'tipo_espacio' => 'Taller',
                'estado' => 'Disponible',
                'puestos_disponibles' => 30,
            ],
            [
                'id_espacio' => 'TH-L03',
                'nombre_espacio' => 'Laboratorio de Refrigeración',
                'piso_id' => 1,
                'tipo_espacio' => 'Taller',
                'estado' => 'Disponible',
                'puestos_disponibles' => 30,
            ],
            [
                'id_espacio' => 'TH-L04',
                'nombre_espacio' => 'Taller de Enfermeria',
                'piso_id' => 1,
                'tipo_espacio' => 'Taller',
                'estado' => 'Disponible',
                'puestos_disponibles' => 20,
            ],
            [
                'id_espacio' => 'TH-L05',
                'nombre_espacio' => 'Taller de Párvulos',
                'piso_id' => 1,
                'tipo_espacio' => 'Taller',
                'estado' => 'Disponible',
                'puestos_disponibles' => 30,
            ],

            [
                'id_espacio' => 'TH-SR1',
                'nombre_espacio' => 'Sala de Reuniones',
                'piso_id' => 2,
                'tipo_espacio' => 'Sala de Reuniones',
                'estado' => 'Disponible',
                'puestos_disponibles' => 13,
            ],

            [
                'id_espacio' => 'TH-01',
                'nombre_espacio' => 'Sala de Clases',
                'piso_id' => 2,
                'tipo_espacio' => 'Aula',
                'estado' => 'Disponible',
                'puestos_disponibles' => 35,
            ],
            [
                'id_espacio' => 'TH-02',
                'nombre_espacio' => 'Sala de Clases',
                'piso_id' => 2,
                'tipo_espacio' => 'Aula',
                'estado' => 'Disponible',
                'puestos_disponibles' => 37,
            ],
            [
                'id_espacio' => 'TH-03',
                'nombre_espacio' => 'Sala de Clases',
                'piso_id' => 2,
                'tipo_espacio' => 'Aula',
                'estado' => 'Disponible',
                'puestos_disponibles' => 35,
            ],
            [
                'id_espacio' => 'TH-04',
                'nombre_espacio' => 'Sala de Clases',
                'piso_id' => 2,
                'tipo_espacio' => 'Aula',
                'estado' => 'Disponible',
                'puestos_disponibles' => 35,
            ],
            [
                'id_espacio' => 'TH-05',
                'nombre_espacio' => 'Sala de Clases',
                'piso_id' => 2,
                'tipo_espacio' => 'Aula',
                'estado' => 'Disponible',
                'puestos_disponibles' => 60,
            ],
            [
                'id_espacio' => 'TH-06',
                'nombre_espacio' => 'Sala de Clases',
                'piso_id' => 2,
                'tipo_espacio' => 'Aula',
                'estado' => 'Disponible',
                'puestos_disponibles' => 70,
            ],
            [
                'id_espacio' => 'TH-07',
                'nombre_espacio' => 'Sala de Clases',
                'piso_id' => 2,
                'tipo_espacio' => 'Aula',
                'estado' => 'Disponible',
                'puestos_disponibles' => 75,
            ],
            [
                'id_espacio' => 'TH-08',
                'nombre_espacio' => 'Sala de Clases',
                'piso_id' => 2,
                'tipo_espacio' => 'Aula',
                'estado' => 'Disponible',
                'puestos_disponibles' => 35,
            ],
            [
                'id_espacio' => 'TH-09',
                'nombre_espacio' => 'Sala de Clases',
                'piso_id' => 2,
                'tipo_espacio' => 'Aula',
                'estado' => 'Disponible',
                'puestos_disponibles' => 35,
            ],
            [
                'id_espacio' => 'TH-10',
                'nombre_espacio' => 'Sala de Clases',
                'piso_id' => 2,
                'tipo_espacio' => 'Aula',
                'estado' => 'Disponible',
                'puestos_disponibles' => 23,
            ],

        ];
        foreach ($espacios as $espacio) {
            Espacio::create($espacio);
        }
    }
}