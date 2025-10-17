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
        Schema::create('recuperacion_clases', function (Blueprint $table) {
            $table->id('id_recuperacion');
            $table->unsignedBigInteger('id_licencia'); // Referencia a la licencia
            $table->unsignedBigInteger('run_profesor');
            $table->string('id_asignatura');
            $table->string('id_espacio')->nullable();
            $table->date('fecha_clase_original'); // Fecha de la clase que se debe recuperar
            $table->string('id_modulo_original')->nullable(); // Módulo original
            $table->date('fecha_reagendada')->nullable(); // Nueva fecha (null = no reagendada)
            $table->string('id_modulo_reagendado')->nullable(); // Nuevo módulo
            $table->string('id_espacio_reagendado')->nullable(); // Nuevo espacio si cambia
            $table->enum('estado', ['pendiente', 'reagendada', 'obviada', 'realizada'])->default('pendiente');
            $table->boolean('notificado')->default(false); // Si se envió notificación
            $table->timestamp('fecha_notificacion')->nullable();
            $table->text('notas')->nullable();
            $table->unsignedBigInteger('gestionado_por')->nullable(); // Usuario que gestionó
            $table->timestamps();

            // Foreign keys
            $table->foreign('id_licencia')->references('id_licencia')->on('licencias_profesores')->onDelete('cascade');
            $table->foreign('run_profesor')->references('run_profesor')->on('profesors')->onDelete('cascade');
            $table->foreign('id_asignatura')->references('id_asignatura')->on('asignaturas')->onDelete('cascade');
            $table->foreign('id_espacio')->references('id_espacio')->on('espacios')->onDelete('set null');
            $table->foreign('id_modulo_original')->references('id_modulo')->on('modulos')->onDelete('set null');
            $table->foreign('id_modulo_reagendado')->references('id_modulo')->on('modulos')->onDelete('set null');
            $table->foreign('id_espacio_reagendado')->references('id_espacio')->on('espacios')->onDelete('set null');
            $table->foreign('gestionado_por')->references('run')->on('users')->onDelete('set null');
            
            // Índices
            $table->index(['run_profesor', 'estado']);
            $table->index('fecha_clase_original');
            $table->index('fecha_reagendada');
            $table->index('id_licencia');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recuperacion_clases');
    }
};
