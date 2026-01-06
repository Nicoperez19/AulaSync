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
        Schema::create('cursos_verano', function (Blueprint $table) {
            $table->id('id_curso_verano');
            $table->integer('anio');
            $table->date('fecha_inicio');
            $table->date('fecha_fin');
            $table->boolean('activo')->default(true);
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();

            // Índices
            $table->unique(['anio'], 'curso_verano_anio_unico');
            $table->index(['activo', 'fecha_inicio', 'fecha_fin'], 'curso_verano_activo_fechas');
            
            // Foreign key
            $table->foreign('created_by')->references('run')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cursos_verano');
    }
};
