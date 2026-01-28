<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Tenant;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Obtener el tenant de Los Ángeles
        $tenantLA = Tenant::where('sede_id', 'LA')->first();

        if (!$tenantLA) {
            return; // Si no existe el tenant LA, no hacer nada
        }

        // Conectarse a la base de datos del tenant
        $connection = 'tenant';

        // Obtener los pisos nuevos por nombre
        $pisoCaupolicán = \DB::connection($connection)->table('pisos')
            ->where('nombre_piso', 'CAUPOLICÁN 276')
            ->first();

        $pisoVillagrán220 = \DB::connection($connection)->table('pisos')
            ->where('nombre_piso', 'VILLAGRÁN 220')
            ->first();

        $pisoVillagrán251 = \DB::connection($connection)->table('pisos')
            ->where('nombre_piso', 'VILLAGRÁN 251')
            ->first();

        // Mapear espacios de Caupolicán 276 (piso_id 8 y 9)
        if ($pisoCaupolicán) {
            \DB::connection($connection)->table('espacios')
                ->whereIn('piso_id', [8, 9])
                ->update(['piso_id' => $pisoCaupolicán->id]);
        }

        // Mapear espacios de Villagrán 220 (piso_id 10)
        if ($pisoVillagrán220) {
            \DB::connection($connection)->table('espacios')
                ->where('piso_id', 10)
                ->update(['piso_id' => $pisoVillagrán220->id]);
        }

        // Mapear espacios de Villagrán 251 (piso_id 11 y 12)
        if ($pisoVillagrán251) {
            \DB::connection($connection)->table('espacios')
                ->whereIn('piso_id', [11, 12])
                ->update(['piso_id' => $pisoVillagrán251->id]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Obtener el tenant de Los Ángeles
        $tenantLA = Tenant::where('sede_id', 'LA')->first();

        if (!$tenantLA) {
            return;
        }

        $connection = 'tenant';

        // Obtener los pisos nuevos
        $pisoCaupolicán = \DB::connection($connection)->table('pisos')
            ->where('nombre_piso', 'CAUPOLICÁN 276')
            ->first();

        $pisoVillagrán220 = \DB::connection($connection)->table('pisos')
            ->where('nombre_piso', 'VILLAGRÁN 220')
            ->first();

        $pisoVillagrán251 = \DB::connection($connection)->table('pisos')
            ->where('nombre_piso', 'VILLAGRÁN 251')
            ->first();

        // Revertir los cambios (restaurar IDs antiguos)
        // Nota: esto es una aproximación; idealmente necesitarías datos históricos para ser exacto
        if ($pisoCaupolicán) {
            // Los primeros 2 espacios de Caupolicán vuelven a 8 y 9
            $espaciosCaupolicán = \DB::connection($connection)->table('espacios')
                ->where('piso_id', $pisoCaupolicán->id)
                ->get();

            foreach ($espaciosCaupolicán as $index => $espacio) {
                $nuevoId = $index % 2 === 0 ? 8 : 9;
                \DB::connection($connection)->table('espacios')
                    ->where('id_espacio', $espacio->id_espacio)
                    ->update(['piso_id' => $nuevoId]);
            }
        }

        if ($pisoVillagrán220) {
            \DB::connection($connection)->table('espacios')
                ->where('piso_id', $pisoVillagrán220->id)
                ->update(['piso_id' => 10]);
        }

        if ($pisoVillagrán251) {
            // Los espacios de Villagrán 251 vuelven a 11 y 12
            $espaciosVillagrán251 = \DB::connection($connection)->table('espacios')
                ->where('piso_id', $pisoVillagrán251->id)
                ->get();

            foreach ($espaciosVillagrán251 as $index => $espacio) {
                $nuevoId = $index % 2 === 0 ? 11 : 12;
                \DB::connection($connection)->table('espacios')
                    ->where('id_espacio', $espacio->id_espacio)
                    ->update(['piso_id' => $nuevoId]);
            }
        }
    }
};
