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
        Schema::table('vetos_sala_estudio', function (Blueprint $table) {
            // Índice compuesto para optimizar consultas frecuentes
            $table->index(['estado', 'fecha_veto'], 'idx_estado_fecha_veto');
            
            // Índice para tipo de veto
            $table->index('tipo_veto');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vetos_sala_estudio', function (Blueprint $table) {
            $table->dropIndex('idx_estado_fecha_veto');
            $table->dropIndex(['tipo_veto']);
        });
    }
};
