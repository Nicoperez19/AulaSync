<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Esta migración se ejecuta solo en la base de datos del tenant (aulasync_la, etc)
     */
    public function up(): void
    {
        // Obtener los pisos nuevos por nombre (directamente en la conexión del tenant)
        $pisoCaupolicán = \DB::table('pisos')
            ->where('nombre_piso', 'CAUPOLICÁN 276')
            ->first();

        $pisoVillagrán220 = \DB::table('pisos')
            ->where('nombre_piso', 'VILLAGRÁN 220')
            ->first();

        $pisoVillagrán251 = \DB::table('pisos')
            ->where('nombre_piso', 'VILLAGRÁN 251')
            ->first();

        // Mapear espacios de Caupolicán 276 (piso_id 8 y 9)
        if ($pisoCaupolicán) {
            \DB::table('espacios')
                ->whereIn('piso_id', [8, 9])
                ->update(['piso_id' => $pisoCaupolicán->id]);
        }

        // Mapear espacios de Villagrán 220 (piso_id 10)
        if ($pisoVillagrán220) {
            \DB::table('espacios')
                ->where('piso_id', 10)
                ->update(['piso_id' => $pisoVillagrán220->id]);
        }

        // Mapear espacios de Villagrán 251 (piso_id 11 y 12)
        if ($pisoVillagrán251) {
            \DB::table('espacios')
                ->whereIn('piso_id', [11, 12])
                ->update(['piso_id' => $pisoVillagrán251->id]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Obtener los pisos nuevos
        $pisoCaupolicán = \DB::table('pisos')
            ->where('nombre_piso', 'CAUPOLICÁN 276')
            ->first();

        $pisoVillagrán220 = \DB::table('pisos')
            ->where('nombre_piso', 'VILLAGRÁN 220')
            ->first();

        $pisoVillagrán251 = \DB::table('pisos')
            ->where('nombre_piso', 'VILLAGRÁN 251')
            ->first();

        // Revertir los cambios (restaurar IDs antiguos)
        if ($pisoCaupolicán) {
            $espaciosCaupolicán = \DB::table('espacios')
                ->where('piso_id', $pisoCaupolicán->id)
                ->get();

            foreach ($espaciosCaupolicán as $index => $espacio) {
                $nuevoId = $index % 2 === 0 ? 8 : 9;
                \DB::table('espacios')
                    ->where('id_espacio', $espacio->id_espacio)
                    ->update(['piso_id' => $nuevoId]);
            }
        }

        if ($pisoVillagrán220) {
            \DB::table('espacios')
                ->where('piso_id', $pisoVillagrán220->id)
                ->update(['piso_id' => 10]);
        }

        if ($pisoVillagrán251) {
            $espaciosVillagrán251 = \DB::table('espacios')
                ->where('piso_id', $pisoVillagrán251->id)
                ->get();

            foreach ($espaciosVillagrán251 as $index => $espacio) {
                $nuevoId = $index % 2 === 0 ? 11 : 12;
                \DB::table('espacios')
                    ->where('id_espacio', $espacio->id_espacio)
                    ->update(['piso_id' => $nuevoId]);
            }
        }
    }
};
