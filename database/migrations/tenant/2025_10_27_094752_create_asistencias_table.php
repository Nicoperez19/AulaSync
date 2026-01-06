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
        Schema::create('asistencias', function (Blueprint $table) {
            $table->id();
            $table->string('id_reserva');
            $table->string('id_asignatura', 20)->nullable(); // Asignatura asociada
            $table->string('rut_asistente'); // RUT sin dígito verificador
            $table->string('nombre_asistente');
            $table->time('hora_llegada'); // Hora de llegada del estudiante
            $table->text('observaciones')->nullable(); // Observaciones del estudiante
            $table->timestamps();

            // Foreign keys
            $table->foreign('id_reserva')
                  ->references('id_reserva')
                  ->on('reservas')
                  ->onDelete('cascade');
            
            $table->foreign('id_asignatura')
                  ->references('id_asignatura')
                  ->on('asignaturas')
                  ->onDelete('set null');

            // Índices para mejorar rendimiento
            $table->index('id_reserva');
            $table->index('rut_asistente');
            $table->index('id_asignatura');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('asistencias');
    }
};
