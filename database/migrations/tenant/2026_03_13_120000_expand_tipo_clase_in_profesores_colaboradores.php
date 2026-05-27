<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement("ALTER TABLE profesores_colaboradores MODIFY COLUMN tipo_clase ENUM('temporal', 'reforzamiento', 'recuperacion', 'actividad_externa', 'actividad_interna') NOT NULL DEFAULT 'temporal'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE profesores_colaboradores MODIFY COLUMN tipo_clase ENUM('temporal', 'reforzamiento', 'recuperacion') NOT NULL DEFAULT 'temporal'");
    }
};
