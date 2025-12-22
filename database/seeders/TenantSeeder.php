<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Tenant;
use App\Models\Sede;

class TenantSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Obtener todas las sedes existentes
        $sedes = Sede::all();

        foreach ($sedes as $sede) {
            // Crear un tenant por cada sede
            // El subdominio se puede derivar del prefijo_sala o del nombre de la sede
            $domain = $sede->prefijo_sala 
                ? strtolower($sede->prefijo_sala) 
                : strtolower(str_replace(' ', '-', $sede->prefijo_sala));

            // Generar nombre de base de datos único para cada sede
            $databaseName = 'aulasync_' . strtolower($sede->prefijo_sala);

            Tenant::updateOrCreate(
                ['domain' => $domain],
                [
                    'name' => $sede->nombre_sede,
                    'prefijo_espacios' => $sede->prefijo_sala,
                    'sede_id' => $sede->id_sede,
                    'is_active' => true,
                    'database' => $databaseName,
                ]
            );
        }
    }
}
