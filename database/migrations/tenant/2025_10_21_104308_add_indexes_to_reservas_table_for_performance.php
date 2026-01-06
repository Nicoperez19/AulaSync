<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('reservas', function (Blueprint $table) {
            // Índice compuesto para fecha_reserva, estado y hora (usado en la consulta principal)
            $table->index(['fecha_reserva', 'estado', 'hora'], 'idx_fecha_estado_hora');
            
            // Índice para filtrar por id_espacio (relación con espacios)
            $table->index('id_espacio', 'idx_id_espacio');
            
            // Índice para run_profesor (usado en filtros de tipo de usuario)
            $table->index('run_profesor', 'idx_run_profesor');
            
            // Índice para run_solicitante (usado en filtros de tipo de usuario)
            $table->index('run_solicitante', 'idx_run_solicitante');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('reservas', function (Blueprint $table) {
            // Eliminar los índices en el orden inverso
            $table->dropIndex('idx_fecha_estado_hora');
            $table->dropIndex('idx_id_espacio');
            $table->dropIndex('idx_run_profesor');
            $table->dropIndex('idx_run_solicitante');
        });
    }
};
