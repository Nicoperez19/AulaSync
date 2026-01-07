<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Piso;
use App\Models\Tenant;

class PisoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Intentar obtener el tenant actual de varias formas
        $tenant = Tenant::current();
        
        if (!$tenant && app()->bound('tenant')) {
            $tenant = app('tenant');
        }
        
        if (!$tenant) {
            $this->command->error('No se pudo determinar el tenant actual');
            return;
        }
        
        $this->command->info("PisoSeeder ejecutándose para tenant: {$tenant->name} (Sede: {$tenant->sede_id})");
        
        // Mapeo de sede a id_facultad
        $sedeToFacultad = [
            'TH' => 'IT_TH',
            'CT' => 'IT_CT',
            'CH' => 'IT_CH',
            'LA' => 'IT_LA',
            'CCP' => 'IT_CCP',
        ];
        
        $idFacultad = $tenant ? $sedeToFacultad[$tenant->sede_id] ?? null : null;
        
        if (!$idFacultad) {
            $this->command->warn('No se pudo determinar la facultad para la sede actual');
            return;
        }
        
        $this->command->info("Creando pisos para sede {$tenant->sede_id} (Facultad: {$idFacultad})");
        
        // Definir todos los pisos por facultad (sin IDs, se asignarán automáticamente)
        $pisosPorFacultad = [
            'IT_TH' => [
            ['numero_piso' => 1],
            ['numero_piso' => 2],
            ],
            'IT_CT' => [
            ['numero_piso' => 1],
            ['numero_piso' => 2],
            ['numero_piso' => 3, 'nombre_piso' => 'Taller Gastronómico'],
            ],
            'IT_CH' => [
            ['numero_piso' => 1],
            ['numero_piso' => 2],
            ['numero_piso' => 3], // Gimnasio
            ],
            'IT_LA' => [
            ['numero_piso' => 1, 'nombre_piso' => 'CAUPOLICÁN 276 - 1er piso'],
            ['numero_piso' => 2, 'nombre_piso' => 'CAUPOLICÁN 276 - 2do piso'],
            ['numero_piso' => 1, 'nombre_piso' => 'VILLAGRÁN 220 - 1er piso'],
            ['numero_piso' => 2, 'nombre_piso' => 'VILLAGRÁN 220 - 2do piso'],
            ['numero_piso' => 1, 'nombre_piso' => 'VILLAGRÁN 251 - 1er piso'],
            ['numero_piso' => 2, 'nombre_piso' => 'VILLAGRÁN 251 - 2do piso'],
            ],
        ];
        
        $pisosACrear = $pisosPorFacultad[$idFacultad] ?? [];
        
        $creados = 0;
        foreach ($pisosACrear as $pisoData) {
            // Verificar si ya existe un piso con los mismos datos
            $exists = \DB::connection('tenant')->table('pisos')
                ->where('numero_piso', $pisoData['numero_piso'])
                ->where('id_facultad', $idFacultad)
                ->when(isset($pisoData['nombre_piso']), function($query) use ($pisoData) {
                    return $query->where('nombre_piso', $pisoData['nombre_piso']);
                })
                ->exists();
            
            if (!$exists) {
                \DB::connection('tenant')->table('pisos')->insert(array_merge($pisoData, [
                    'id_facultad' => $idFacultad,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]));
                $creados++;
            }
        }
        
        $this->command->info("Creados {$creados} pisos para {$idFacultad} (de " . count($pisosACrear) . " intentados)");
    }
}

// Código original comentado para referencia:
/*
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

        // IT_CT: 2 pisos
        Piso::create([
            'id' => 3,
            'numero_piso' => 1,
            'id_facultad' => 'IT_CT',
        ]);

        Piso::create([
            'id' => 4,
            'numero_piso' => 2,
            'id_facultad' => 'IT_CT',
        ]);

        // IT_CH: 2 pisos + 1 ubicación externa (gimnasio) modelado como tercer "piso".
        Piso::create([
            'id' => 5,
            'numero_piso' => 1,
            'id_facultad' => 'IT_CH',
        ]);

        Piso::create([
            'id' => 6,
            'numero_piso' => 2,
            'id_facultad' => 'IT_CH',
        ]);

        // Este registro representa la ubicación del gimnasio (no es un "piso" tradicional,
        // pero se crea aquí para poder relacionar el espacio). Revisar si se requiere
        // modelado distinto en la base de datos.
        Piso::create([
            'id' => 7,
            'numero_piso' => 3,
            'id_facultad' => 'IT_CH',
        ]);

        // IT_LA: múltiples localizaciones. Se crean pisos provisionales (2 por edificio)
        // IDs provisionales: 8..13.
        // Conteo y desglose (considerando ubicaciones distintas como pisos):
        // - EDIF. CAUPOLICÁN 276: 2 pisos
        // - EDIF. CALLE VILLAGRÁN 220: 2 pisos
        // - EDIF. CALLE VILLAGRÁN 251: 2 pisos
        // TOTAL IT_LA (pisos distintos/ubicaciones): 6
        // REVISAR manualmente las localizaciones y ajustar estos registros
        // si es necesario (ej. mapear edificio -> piso_id específico).
        Piso::create([
            'id' => 8,
            'numero_piso' => 1,
            'id_facultad' => 'IT_LA',
            'nombre_piso' => 'CAUPOLICÁN 276 - 1er piso',
        ]);

        Piso::create([
            'id' => 9,
            'numero_piso' => 2,
            'id_facultad' => 'IT_LA',
            'nombre_piso' => 'CAUPOLICÁN 276 - 2do piso',
        ]);

        Piso::create([
            'id' => 10,
            'numero_piso' => 1,
            'id_facultad' => 'IT_LA',
            'nombre_piso' => 'VILLAGRÁN 220 - 1er piso',
        ]);

        Piso::create([
            'id' => 11,
            'numero_piso' => 2,
            'id_facultad' => 'IT_LA',
            'nombre_piso' => 'VILLAGRÁN 220 - 2do piso',
        ]);

        Piso::create([
            'id' => 12,
            'numero_piso' => 1,
            'id_facultad' => 'IT_LA',
            'nombre_piso' => 'VILLAGRÁN 251 - 1er piso',
        ]);

        Piso::create([
            'id' => 13,
            'numero_piso' => 2,
            'id_facultad' => 'IT_LA',
            'nombre_piso' => 'VILLAGRÁN 251 - 2do piso',
        ]);
*/
