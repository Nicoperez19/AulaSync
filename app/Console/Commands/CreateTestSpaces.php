<?php

namespace App\Console\Commands;

use App\Models\Espacio;
use App\Models\Piso;
use App\Models\Tenant;
use Illuminate\Console\Command;

class CreateTestSpaces extends Command
{
    protected $signature = 'spaces:create-test {domain}';
    protected $description = 'Create test spaces for a specific tenant';

    public function handle()
    {
        $domain = $this->argument('domain');
        $tenant = Tenant::where('domain', $domain)->first();

        if (!$tenant) {
            $this->error("Tenant with domain '$domain' not found");
            return 1;
        }

        $tenant->makeCurrent();

        // Get or create a floor
        $piso = Piso::where('id_facultad', $tenant->sede_id . '_FAC')->first();
        if (!$piso) {
            $piso = Piso::create([
                'numero_piso' => 1,
                'nombre_piso' => 'Piso 1',
                'id_facultad' => $tenant->sede_id . '_FAC'
            ]);
            $this->info("Created floor: {$piso->nombre_piso}");
        }

        // Create test spaces
        $espacios = [
            'CH-101' => 'Sala 101',
            'CH-102' => 'Sala 102',
            'CH-103' => 'Sala 103',
            'CH-104' => 'Lab 104',
            'CH-105' => 'Lab 105',
            'CH-201' => 'Auditorio 201',
            'CH-202' => 'Sala 202',
            'CH-203' => 'Sala 203',
        ];

        foreach ($espacios as $id => $nombre) {
            $existe = Espacio::where('id_espacio', $id)->exists();
            if (!$existe) {
                Espacio::create([
                    'id_espacio' => $id,
                    'nombre_espacio' => $nombre,
                    'tipo_espacio' => 'Sala de Clases',
                    'estado' => 'Disponible',
                    'puestos_disponibles' => 30,
                    'piso_id' => $piso->id
                ]);
                $this->info("Created space: $id - $nombre");
            } else {
                $this->line("Space already exists: $id");
            }
        }

        $this->info("Done! Created " . count($espacios) . " test spaces");
        return 0;
    }
}
