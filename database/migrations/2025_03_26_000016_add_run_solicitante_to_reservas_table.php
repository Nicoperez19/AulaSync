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
            // Agregar campo para solicitantes (nullable porque puede ser profesor o solicitante)
            $table->string('run_solicitante')->nullable()->after('run_profesor');
            
            // Agregar índice para optimizar consultas
            $table->index('run_solicitante');
            
            // Hacer nullable el campo run_profesor ya que ahora puede ser solicitante
            $table->unsignedBigInteger('run_profesor')->nullable()->change();
            
            // Agregar foreign key para solicitantes
            $table->foreign('run_solicitante')->references('run_solicitante')->on('solicitantes')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('reservas', function (Blueprint $table) {
            // Remover foreign key
            $table->dropForeign(['run_solicitante']);
            
            // Remover índice
            $table->dropIndex(['run_solicitante']);
            
            // Remover campo
            $table->dropColumn('run_solicitante');
            
            // Restaurar run_profesor como no nullable
            $table->unsignedBigInteger('run_profesor')->nullable(false)->change();
        });
    }
}; 