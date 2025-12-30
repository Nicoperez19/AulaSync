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
        Schema::create('periodos_academicos', function (Blueprint $table) {
            $table->id('id_periodo');
            $table->integer('anio');
            $table->tinyInteger('semestre')->comment('1 = Primer Semestre, 2 = Segundo Semestre');
            $table->date('fecha_inicio')->comment('Inicio de actividades académicas');
            $table->date('fecha_fin')->comment('Cierre de actividades académicas');
            $table->date('inicio_verano')->nullable()->comment('Inicio de cursos de verano (opcional)');
            $table->date('fin_verano')->nullable()->comment('Fin de cursos de verano (opcional)');
            $table->boolean('activo')->default(true);
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();

            // Índices
            $table->unique(['anio', 'semestre'], 'periodo_unico');
            $table->index(['activo', 'fecha_inicio', 'fecha_fin'], 'periodo_activo_fechas');
            
            // Foreign key
            $table->foreign('created_by')->references('run')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('periodos_academicos');
    }
};
