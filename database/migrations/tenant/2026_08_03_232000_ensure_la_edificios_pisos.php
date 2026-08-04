<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Asegura que los 3 edificios de Los Ángeles (Caupolicán 276, Villagrán 220, Villagrán 251) existan en la tabla pisos.
     */
    public function up(): void
    {
        // Verificar si la base de datos actual pertenece a la facultad de Los Ángeles (IT_LA)
        $isLA = DB::table('facultades')->where('id_facultad', 'IT_LA')->exists()
             || DB::table('espacios')->where('id_espacio', 'LIKE', 'LA-%')->exists()
             || str_contains(strtolower(DB::connection()->getDatabaseName()), 'la');

        if (!$isLA) {
            return;
        }

        $edificiosLA = [
            ['id' => 8, 'numero_piso' => 1, 'nombre_piso' => 'CAUPOLICÁN 276', 'id_facultad' => 'IT_LA'],
            ['id' => 10, 'numero_piso' => 2, 'nombre_piso' => 'VILLAGRÁN 220', 'id_facultad' => 'IT_LA'],
            ['id' => 12, 'numero_piso' => 3, 'nombre_piso' => 'VILLAGRÁN 251', 'id_facultad' => 'IT_LA'],
        ];

        foreach ($edificiosLA as $edificio) {
            $pisoExistente = DB::table('pisos')->where('id', $edificio['id'])->first();

            if ($pisoExistente) {
                // Si existe pero no tiene el nombre correcto, actualizarlo
                DB::table('pisos')->where('id', $edificio['id'])->update([
                    'nombre_piso' => $edificio['nombre_piso'],
                    'numero_piso' => $edificio['numero_piso'],
                    'updated_at' => now(),
                ]);
            } else {
                // Si no existe (ej. VILLAGRÁN 251 no fue insertado), crearlo
                DB::table('pisos')->insert(array_merge($edificio, [
                    'created_at' => now(),
                    'updated_at' => now(),
                ]));
            }
        }

        // Asegurar que los nombres de los 3 edificios queden limpios sin "1er piso" / "2do piso" sobrantes
        DB::table('pisos')->where('id_facultad', 'IT_LA')->where('nombre_piso', 'LIKE', '%CAUPOLICÁN%')->update(['nombre_piso' => 'CAUPOLICÁN 276']);
        DB::table('pisos')->where('id_facultad', 'IT_LA')->where('nombre_piso', 'LIKE', '%VILLAGRÁN 220%')->update(['nombre_piso' => 'VILLAGRÁN 220']);
        DB::table('pisos')->where('id_facultad', 'IT_LA')->where('nombre_piso', 'LIKE', '%VILLAGRÁN 251%')->update(['nombre_piso' => 'VILLAGRÁN 251']);

        // Mapear cualquier espacio con piso_id antiguo (9, 11, 13) a su respectivo edificio (8, 10, 12)
        DB::table('espacios')->where('piso_id', 9)->update(['piso_id' => 8]);
        DB::table('espacios')->where('piso_id', 11)->update(['piso_id' => 10]);
        DB::table('espacios')->where('piso_id', 13)->update(['piso_id' => 12]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
    }
};
