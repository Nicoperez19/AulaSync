<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Hacer la columna run_profesor nullable para permitir reservas de solicitantes
        DB::statement("ALTER TABLE `reservas` MODIFY `run_profesor` BIGINT UNSIGNED NULL;");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revertir a NOT NULL (si la tabla contiene valores NULL esto puede fallar)
        DB::statement("ALTER TABLE `reservas` MODIFY `run_profesor` BIGINT UNSIGNED NOT NULL;");
    }
};
