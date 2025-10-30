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
        Schema::create('dias_feriados', function (Blueprint $table) {
            $table->id('id_feriado');
            $table->date('fecha_inicio');
            $table->date('fecha_fin');
            $table->string('nombre', 255);
            $table->text('descripcion')->nullable();
            $table->enum('tipo', ['feriado', 'semana_reajuste', 'suspension_actividades'])->default('feriado');
            $table->boolean('activo')->default(true);
            $table->string('created_by', 20)->nullable();
            $table->timestamps();

            // Índices para mejorar el rendimiento
            $table->index(['fecha_inicio', 'fecha_fin']);
            $table->index('activo');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dias_feriados');
    }
};
