<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Añade índices compuestos para mejorar el rendimiento de las consultas de listado
     */
    public function up(): void
    {
        Schema::table('clases_no_realizadas', function (Blueprint $table) {
            // Índice compuesto para la consulta principal del listado
            // Cubre: periodo, fecha_clase, estado (los filtros más comunes)
            $table->index(['periodo', 'fecha_clase', 'estado'], 'idx_clases_periodo_fecha_estado');
            
            // Índice para ordenamiento por created_at (muy usado)
            $table->index(['created_at'], 'idx_clases_created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('clases_no_realizadas', function (Blueprint $table) {
            $table->dropIndex('idx_clases_periodo_fecha_estado');
            $table->dropIndex('idx_clases_created_at');
        });
    }
};
