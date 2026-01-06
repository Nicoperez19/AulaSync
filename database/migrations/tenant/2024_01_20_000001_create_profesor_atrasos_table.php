<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Tabla para registrar los atrasos de profesores.
     * Un atraso se registra cuando el profesor llega después de los 15 minutos
     * de gracia pero aún así realiza la clase.
     */
    public function up(): void
    {
        Schema::create('profesor_atrasos', function (Blueprint $table) {
            $table->id();
            
            // Datos de la planificación
            $table->unsignedBigInteger('id_planificacion');
            $table->unsignedBigInteger('id_asignatura');
            $table->unsignedBigInteger('id_espacio');
            $table->string('id_modulo', 20); // Formato: LU.3, MA.5, etc.
            
            // Datos del profesor
            $table->string('run_profesor', 20);
            
            // Datos de la fecha y hora
            $table->date('fecha');
            $table->time('hora_programada')->comment('Hora de inicio del módulo');
            $table->time('hora_llegada')->comment('Hora real de llegada del profesor');
            $table->integer('minutos_atraso')->comment('Minutos de atraso');
            
            // Periodo académico
            $table->string('periodo', 20)->nullable();
            
            // Campos adicionales
            $table->text('observaciones')->nullable();
            $table->boolean('justificado')->default(false);
            $table->text('justificacion')->nullable();
            
            $table->timestamps();
            
            // Índices
            $table->index(['run_profesor', 'fecha']);
            $table->index(['fecha', 'id_espacio']);
            $table->index('periodo');
            
            // Único: no duplicar atrasos para la misma clase en la misma fecha
            $table->unique(['id_planificacion', 'fecha'], 'unique_atraso_planificacion_fecha');
            
            // Foreign keys (comentadas por si las tablas no tienen las FK esperadas)
            // $table->foreign('id_planificacion')->references('id')->on('planificacion_asignaturas')->onDelete('cascade');
            // $table->foreign('id_asignatura')->references('id')->on('asignaturas')->onDelete('cascade');
            // $table->foreign('id_espacio')->references('id')->on('espacios')->onDelete('cascade');
            // $table->foreign('run_profesor')->references('run')->on('profesores')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('profesor_atrasos');
    }
};
