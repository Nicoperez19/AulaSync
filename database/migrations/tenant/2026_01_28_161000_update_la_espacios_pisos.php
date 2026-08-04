<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Recrea los pisos y espacios de Los Ángeles (aulasync_la) desde cero
     */
    public function up(): void
    {
        // Verificar que estamos en la base de datos de Los Ángeles
        $isLA = \DB::table('facultades')->where('id_facultad', 'IT_LA')->exists()
            || \DB::table('espacios')->where('id_espacio', 'LIKE', 'LA-%')->exists()
            || str_contains(strtolower(\DB::connection()->getDatabaseName()), 'la');

        if (!$isLA) {
            return;
        }

        // 1. Borrar todos los espacios existentes primero
        \DB::table('espacios')->delete();
        echo "✓ Eliminados todos los espacios\n";

        // 2. Borrar todos los pisos
        \DB::table('pisos')->delete();
        echo "✓ Eliminados todos los pisos\n";
        $pisos = [
            ['id' => 8, 'numero_piso' => 1, 'nombre_piso' => 'CAUPOLICÁN 276', 'id_facultad' => 'IT_LA', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 10, 'numero_piso' => 2, 'nombre_piso' => 'VILLAGRÁN 220', 'id_facultad' => 'IT_LA', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 12, 'numero_piso' => 3, 'nombre_piso' => 'VILLAGRÁN 251', 'id_facultad' => 'IT_LA', 'created_at' => now(), 'updated_at' => now()],
        ];

        foreach ($pisos as $piso) {
            \DB::table('pisos')->insert($piso);
        }
        echo "✓ Creados 3 pisos nuevos para Los Ángeles\n";

        // 3. Cargar espacios desde el archivo de configuración
        $sedeId = 'LA';
        $file = __DIR__ . "/../../seeders/Data/Espacios/{$sedeId}.php";

        if (!file_exists($file)) {
            echo "⚠ No hay archivo de definición de espacios para LA\n";
            return;
        }

        $todosLosEspacios = require $file;

        // 4. Mapeo de pisos antiguos a nuevos
        $pisoMap = [
            8 => 8,   // Caupolicán 276 (piso_id 8 → piso_id 8)
            9 => 8,   // Caupolicán 276 (piso_id 9 → piso_id 8)
            10 => 10, // Villagrán 220 (piso_id 10 → piso_id 10)
            11 => 12, // Villagrán 251 (piso_id 11 → piso_id 12)
            12 => 12, // Villagrán 251 (piso_id 12 → piso_id 12)
            13 => 12, // Villagrán 251 (piso_id 13 → piso_id 12) - espacios adicionales
        ];

        // 5. Procesar y crear espacios
        $espacios = collect($todosLosEspacios)
            ->map(function ($espacio) use ($pisoMap) {
                // Mapear piso_id antiguo a nuevo ID
                if (isset($espacio['piso_id']) && isset($pisoMap[$espacio['piso_id']])) {
                    $espacio['piso_id'] = $pisoMap[$espacio['piso_id']];
                }
                
                // Asegurar capacidad_maxima
                if (!isset($espacio['capacidad_maxima']) || $espacio['capacidad_maxima'] === null) {
                    $espacio['capacidad_maxima'] = $espacio['puestos_disponibles'] ?? 0;
                }
                
                $espacio['created_at'] = now();
                $espacio['updated_at'] = now();
                
                return $espacio;
            })
            ->all();

        // 6. Insertar espacios
        foreach ($espacios as $data) {
            \DB::table('espacios')->insert($data);
        }
        
        echo "✓ Creados " . count($espacios) . " espacios para Los Ángeles\n";
        echo "✓ Migración completada exitosamente para aulasync_la\n";
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $currentDb = \DB::connection()->getDatabaseName();
        
        if ($currentDb !== 'aulasync_la') {
            return;
        }

        // Eliminar los espacios y pisos creados
        \DB::table('espacios')->whereIn('piso_id', [8, 10, 12])->delete();
        \DB::table('pisos')->whereIn('id', [8, 10, 12])->delete();
        
        echo "✓ Migración revertida para aulasync_la\n";
    }
};
