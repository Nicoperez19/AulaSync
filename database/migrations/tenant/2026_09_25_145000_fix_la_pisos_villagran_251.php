<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Asegura que los pisos de Los Ángeles tengan sus nombres oficiales correspondientes a sus edificios:
     * Piso 1 -> CAUPOLICÁN 276
     * Piso 2 -> VILLAGRÁN 220
     * Piso 3 -> VILLAGRÁN 251
     */
    public function up(): void
    {
        $isLA = DB::table('facultades')->where('id_facultad', 'IT_LA')->exists()
             || DB::table('espacios')->where('id_espacio', 'LIKE', 'LA-%')->exists()
             || str_contains(strtolower(DB::connection()->getDatabaseName()), 'la');

        if (!$isLA) {
            return;
        }

        // 1. Actualizar por numero_piso directamente para IT_LA sin importar su ID
        DB::table('pisos')->where('id_facultad', 'IT_LA')->where('numero_piso', 1)->update([
            'nombre_piso' => 'CAUPOLICÁN 276',
            'updated_at' => now(),
        ]);

        DB::table('pisos')->where('id_facultad', 'IT_LA')->where('numero_piso', 2)->update([
            'nombre_piso' => 'VILLAGRÁN 220',
            'updated_at' => now(),
        ]);

        DB::table('pisos')->where('id_facultad', 'IT_LA')->where('numero_piso', 3)->update([
            'nombre_piso' => 'VILLAGRÁN 251',
            'updated_at' => now(),
        ]);

        // 2. Si no existe un piso con numero_piso = 3 para IT_LA, crearlo
        $existePiso3 = DB::table('pisos')->where('id_facultad', 'IT_LA')->where('numero_piso', 3)->exists();
        if (!$existePiso3) {
            DB::table('pisos')->insert([
                'id' => 12,
                'numero_piso' => 3,
                'nombre_piso' => 'VILLAGRÁN 251',
                'id_facultad' => 'IT_LA',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No destructivo
    }
};
