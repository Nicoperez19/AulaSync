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
        Schema::create('clases_no_realizadas', function (Blueprint $table) {
            $table->id();
            $table->string('id_asignatura');
            $table->string('id_espacio');
            $table->string('id_modulo');
            $table->string('run_profesor');
            $table->date('fecha_clase');
            $table->string('periodo');
            $table->string('motivo')->default('No se registró ingreso en el primer módulo');
            $table->text('observaciones')->nullable();
            $table->enum('estado', ['pendiente', 'justificado', 'confirmado'])->default('pendiente');
            $table->timestamp('hora_deteccion')->useCurrent();
            $table->timestamps();
            
            // Índices para mejorar el rendimiento
            $table->index(['run_profesor', 'fecha_clase']);
            $table->index(['id_asignatura', 'fecha_clase']);
            $table->index(['periodo']);
            $table->index(['estado']);
            
            // Clave única para evitar duplicados
            $table->unique(['id_asignatura', 'id_espacio', 'id_modulo', 'fecha_clase'], 'unique_clase_no_realizada');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clases_no_realizadas');
    }
};
