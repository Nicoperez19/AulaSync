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
        Schema::create('planificaciones_profesores_colaboradores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_profesor_colaborador')
                ->constrained('profesores_colaboradores')
                ->onDelete('cascade')
                ->name('fk_planif_prof_colab_id');
            $table->string('id_modulo');
            $table->string('id_espacio');
            $table->timestamps();

            // Foreign keys con nombres cortos
            $table->foreign('id_modulo', 'fk_planif_prof_colab_modulo')
                ->references('id_modulo')
                ->on('modulos')
                ->onDelete('cascade');
            
            $table->foreign('id_espacio', 'fk_planif_prof_colab_espacio')
                ->references('id_espacio')
                ->on('espacios')
                ->onDelete('cascade');

            // Índices para búsquedas rápidas
            $table->index(['id_profesor_colaborador', 'id_modulo'], 'idx_prof_colab_modulo');
            $table->index('id_espacio', 'idx_prof_colab_espacio');
            
            // Evitar duplicados en la misma combinación
            $table->unique(['id_profesor_colaborador', 'id_modulo', 'id_espacio'], 
                'unique_prof_colab_mod_esp');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('planificaciones_profesores_colaboradores');
    }
};
