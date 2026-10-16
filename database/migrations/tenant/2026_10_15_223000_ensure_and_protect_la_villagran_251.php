<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Protege y preserva el edificio VILLAGRÁN 251 de Los Ángeles:
     * 1. Asegura el piso con ID 12 y número de piso 3.
     * 2. Asegura que las salas LA-4xx apunten al piso 12.
     * 3. NO toca ni modifica la tabla mapas para preservar intacto el mapa recién cargado.
     */
    public function up(): void
    {
        $connection = config('database.default') === 'tenant' ? 'tenant' : DB::getDefaultConnection();

        $isLA = DB::connection($connection)->table('facultades')->where('id_facultad', 'IT_LA')->exists()
             || DB::connection($connection)->table('espacios')->where('id_espacio', 'LIKE', 'LA-%')->exists()
             || str_contains(strtolower(DB::connection($connection)->getDatabaseName()), 'la');

        if (!$isLA) {
            return;
        }

        // 1. Asegurar piso 12 para VILLAGRÁN 251
        $piso12 = DB::connection($connection)->table('pisos')->where('id', 12)->first();
        if ($piso12) {
            DB::connection($connection)->table('pisos')->where('id', 12)->update([
                'numero_piso' => 3,
                'nombre_piso' => 'VILLAGRÁN 251',
                'id_facultad' => 'IT_LA',
                'updated_at'  => now(),
            ]);
        } else {
            DB::connection($connection)->table('pisos')->insert([
                'id'          => 12,
                'numero_piso' => 3,
                'nombre_piso' => 'VILLAGRÁN 251',
                'id_facultad' => 'IT_LA',
                'created_at'  => now(),
                'updated_at'  => now(),
            ]);
        }

        // 2. Asociar todos los espacios de Villagrán 251 (LA-4xx) al piso 12
        DB::connection($connection)->table('espacios')
            ->where('id_espacio', 'LIKE', 'LA-4%')
            ->update(['piso_id' => 12]);

        // NOTA: La tabla 'mapas' NO se toca para no sobreescribir ni descalibrar el mapa cargado.
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No destructivo para proteger los edificios de producción
    }
};
