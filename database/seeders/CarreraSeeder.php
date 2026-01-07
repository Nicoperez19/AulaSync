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
        $areasAcademicas = [
            'ESC_EDUSAL' => 'ESC_EDUSAL',
            'ESC_ADMSERV' => 'ESC_ADMSERV',
            'ESC_INGPRO' => 'ESC_INGPRO',
        ];
        $carreras = [
            1200 => ['Técnico Universitario en Construcción', 'ESC_INGPRO'],
            1201 => ['Técnico Universitario en Alimentos', 'ESC_EDUSAL'],
            1205 => ['Técnico Universitario en Administración', 'ESC_ADMSERV'],
            1207 => ['Ingeniería de Ejecución en Administración de Empresas', 'ESC_ADMSERV'],
            1227 => ['Técnico Universitario en Prevención de Riesgos', 'ESC_INGPRO'],
            1237 => ['Técnico Universitario en Electromecánica', 'ESC_INGPRO'],
            1243 => ['Técnico Universitario en Enfermería', 'ESC_EDUSAL'],
            1266 => ['Técnico Universitario en Refrigeración y Climatización Industrial', 'ESC_INGPRO'],
            1274 => ['Técnico Universitario en Electricidad Industrial', 'ESC_INGPRO'],
            1284 => ['Ingeniería de Ejecución en Informática', 'ESC_INGPRO'],
            1287 => ['Técnico Universitario en Educación de Párvulos', 'ESC_EDUSAL'],
            1297 => ['Técnico Universitario en Logística', 'ESC_ADMSERV'],
            1318 => ['Programa de Continuidad de Estudios en Construcción Civil', 'ESC_INGPRO'],
            1321 => ['Programa de Continuidad de Estudios en Ingeniería de Ejecución en Administración de Empresas', 'ESC_ADMSERV'],
            1352 => ['Programa de Continuidad de Estudios en Ingeniería de Ejecución Industrial', 'ESC_INGPRO'],
            2200 => ['Técnico Universitario en Construcción', 'ESC_INGPRO'],
            2207 => ['Técnico Universitario en Administración', 'ESC_ADMSERV'],
            2219 => ['Técnico Universitario en Administración Pública', 'ESC_ADMSERV'],
            2228 => ['Técnico Universitario en Automatización Industrial', 'ESC_INGPRO'],
            2266 => ['Técnico Universitario en Refrigeración y Climatización Industrial', 'ESC_INGPRO'],
            2297 => ['Técnico Universitario en Logística', 'ESC_ADMSERV'],
        ];

        foreach ($carreras as $id_carrera => [$nombre, $areaAcademicaClave]) {
            Carrera::updateOrCreate(
                ['id_carrera' => $id_carrera],
                [
                    'nombre' => $nombre,
                    'id_area_academica' => $areasAcademicas[$areaAcademicaClave],
                ]
            );
        }
    }
}
