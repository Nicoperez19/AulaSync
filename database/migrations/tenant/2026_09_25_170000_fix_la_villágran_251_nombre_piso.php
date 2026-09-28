<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Corrige el nombre_piso de los 3 edificios de Los Ángeles.
     * En particular, asegura que el piso id=12 se llame "VILLAGRÁN 251"
     * y no "Piso 3" o NULL, que es lo que causaba que no apareciera en el menú.
     */
    public function up(): void
    {
        // Determinar la conexión activa (puede ser 'tenant' o la default en el servidor)
        $connection = config('database.default') === 'tenant' ? 'tenant' : DB::getDefaultConnection();

        // Solo aplica a bases de datos de Los Ángeles
        $isLA = DB::connection($connection)->table('facultades')->where('id_facultad', 'IT_LA')->exists()
             || DB::connection($connection)->table('espacios')->where('id_espacio', 'LIKE', 'LA-%')->exists()
             || str_contains(strtolower(DB::connection($connection)->getDatabaseName()), 'la');

        if (!$isLA) {
            return;
        }

        $edificios = [
            ['id' => 8,  'nombre_piso' => 'CAUPOLICÁN 276', 'numero_piso' => 1, 'id_facultad' => 'IT_LA'],
            ['id' => 10, 'nombre_piso' => 'VILLAGRÁN 220',  'numero_piso' => 2, 'id_facultad' => 'IT_LA'],
            ['id' => 12, 'nombre_piso' => 'VILLAGRÁN 251',  'numero_piso' => 3, 'id_facultad' => 'IT_LA'],
        ];

        foreach ($edificios as $edificio) {
            $existe = DB::connection($connection)->table('pisos')->where('id', $edificio['id'])->exists();

            if ($existe) {
                DB::connection($connection)->table('pisos')->where('id', $edificio['id'])->update([
                    'nombre_piso' => $edificio['nombre_piso'],
                    'numero_piso' => $edificio['numero_piso'],
                    'id_facultad' => $edificio['id_facultad'],
                    'updated_at'  => now(),
                ]);
            } else {
                DB::connection($connection)->table('pisos')->insert(array_merge($edificio, [
                    'created_at' => now(),
                    'updated_at' => now(),
                ]));
            }
        }

        // Eliminar pisos duplicados de LA que no sean los 3 correctos (9, 11, 13 si aún existen)
        DB::connection($connection)->table('pisos')
            ->where('id_facultad', 'IT_LA')
            ->whereNotIn('id', [8, 10, 12])
            ->delete();

        // Reasignar espacios que aún usen piso_id 9 → 8, 11 → 10, 13 → 12
        DB::connection($connection)->table('espacios')->where('piso_id', 9)->update(['piso_id' => 8]);
        DB::connection($connection)->table('espacios')->where('piso_id', 11)->update(['piso_id' => 10]);
        DB::connection($connection)->table('espacios')->where('piso_id', 13)->update(['piso_id' => 12]);
    }

    public function down(): void
    {
        // No se revierte: es una corrección de datos
    }
};
