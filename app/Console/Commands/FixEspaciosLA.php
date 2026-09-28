<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\Tenant;

class FixEspaciosLA extends Command
{
    protected $signature = 'fix:espacios-la';
    protected $description = 'Asegura pisos y puebla espacios faltantes de Los Ángeles';

    public function handle()
    {
        $this->info('Iniciando sincronización de pisos y espacios para Los Ángeles...');

        // 1. Detectar tenant de Los Ángeles
        $tenant = Tenant::where('sede_id', 'LA')->first();
        $dbName = $tenant ? $tenant->database : 'aulasync_la';

        $this->line("Conectando a base de datos: {$dbName}");
        config(['database.connections.tenant.database' => $dbName]);
        DB::purge('tenant');

        // 2. Asegurar pisos
        DB::connection('tenant')->table('pisos')->where('id_facultad', 'IT_LA')->where('numero_piso', 1)->update([
            'nombre_piso' => 'CAUPOLICÁN 276',
            'updated_at' => now(),
        ]);
        DB::connection('tenant')->table('pisos')->where('id_facultad', 'IT_LA')->where('numero_piso', 2)->update([
            'nombre_piso' => 'VILLAGRÁN 220',
            'updated_at' => now(),
        ]);
        DB::connection('tenant')->table('pisos')->where('id_facultad', 'IT_LA')->where('numero_piso', 3)->update([
            'nombre_piso' => 'VILLAGRÁN 251',
            'updated_at' => now(),
        ]);

        $piso251 = DB::connection('tenant')->table('pisos')->where('id_facultad', 'IT_LA')->where('numero_piso', 3)->first();
        if (!$piso251) {
            $piso251Id = DB::connection('tenant')->table('pisos')->insertGetId([
                'numero_piso' => 3,
                'nombre_piso' => 'VILLAGRÁN 251',
                'id_facultad' => 'IT_LA',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } else {
            $piso251Id = $piso251->id;
        }

        $pisoCaupolicanId = DB::connection('tenant')->table('pisos')->where('id_facultad', 'IT_LA')->where('numero_piso', 1)->value('id') ?? 8;
        $piso220Id = DB::connection('tenant')->table('pisos')->where('id_facultad', 'IT_LA')->where('numero_piso', 2)->value('id') ?? 10;

        $this->info("Pisos confirmados: Caupolicán ({$pisoCaupolicanId}), Villagrán 220 ({$piso220Id}), Villagrán 251 ({$piso251Id})");

        // 3. Cargar espacios desde el archivo
        $archivo = database_path('seeders/Data/Espacios/LA.php');
        if (!file_exists($archivo)) {
            $this->error("No se encontró el archivo de espacios en: {$archivo}");
            return 1;
        }

        $todosLosEspacios = require $archivo;
        $insertados = 0;
        $actualizados = 0;

        foreach ($todosLosEspacios as $e) {
            // Asignar piso_id según el espacio
            if (str_starts_with($e['id_espacio'], 'LA-4') || in_array($e['piso_id'] ?? null, [12, 13])) {
                $e['piso_id'] = $piso251Id;
            } elseif (str_starts_with($e['id_espacio'], 'LA-2') || str_starts_with($e['id_espacio'], 'LA-C')) {
                $e['piso_id'] = $piso220Id;
            } else {
                $e['piso_id'] = $pisoCaupolicanId;
            }

            $e['capacidad_maxima'] = $e['capacidad_maxima'] ?? $e['puestos_disponibles'] ?? 0;
            $e['updated_at'] = now();

            $existe = DB::connection('tenant')->table('espacios')->where('id_espacio', $e['id_espacio'])->first();
            if (!$existe) {
                $e['created_at'] = now();
                DB::connection('tenant')->table('espacios')->insert($e);
                $insertados++;
            } else {
                DB::connection('tenant')->table('espacios')->where('id_espacio', $e['id_espacio'])->update([
                    'piso_id' => $e['piso_id'],
                    'capacidad_maxima' => $e['capacidad_maxima'],
                    'updated_at' => now(),
                ]);
                $actualizados++;
            }
        }

        $this->info("✓ Espacios insertados: {$insertados}");
        $this->info("✓ Espacios actualizados: {$actualizados}");

        $totales = DB::connection('tenant')->table('espacios')
            ->select('piso_id', DB::raw('count(*) as total'))
            ->groupBy('piso_id')
            ->get();

        $rows = [];
        foreach ($totales as $t) {
            $rows[] = [$t->piso_id, $t->total];
        }
        $this->table(['Piso ID', 'Total Espacios'], $rows);

        $this->info('¡Los Ángeles sincronizado correctamente!');
        return 0;
    }
}
