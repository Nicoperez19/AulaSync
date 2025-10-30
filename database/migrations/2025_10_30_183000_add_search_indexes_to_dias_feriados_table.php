<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('dias_feriados', function (Blueprint $table) {
            // Agregar índice en la columna nombre para búsquedas más rápidas
            $table->index('nombre');
            // Agregar índice en tipo para filtros
            $table->index('tipo');
        });

        // Agregar índice FULLTEXT para búsquedas de texto en MySQL
        // Solo si el motor de base de datos lo soporta
        if (DB::getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE dias_feriados ADD FULLTEXT INDEX dias_feriados_search_index (nombre, descripcion)');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Eliminar índice FULLTEXT primero (si existe)
        if (DB::getDriverName() === 'mysql') {
            try {
                DB::statement('ALTER TABLE dias_feriados DROP INDEX dias_feriados_search_index');
            } catch (\Exception $e) {
                // Ignorar si no existe
            }
        }

        Schema::table('dias_feriados', function (Blueprint $table) {
            $table->dropIndex(['nombre']);
            $table->dropIndex(['tipo']);
        });
    }
};
