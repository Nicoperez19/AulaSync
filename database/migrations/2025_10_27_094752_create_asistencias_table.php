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
            $table->string('rut_asistente'); // RUT sin dígito verificador
            $table->string('nombre_asistente');
            $table->time('hora_llegada');
            $table->time('hora_termino')->nullable();
            $table->text('contenido_visto')->nullable();
            $table->timestamps();

            // Foreign key
            $table->foreign('id_reserva')
                  ->references('id_reserva')
                  ->on('reservas')
                  ->onDelete('cascade');

            // Índices para mejorar rendimiento
            $table->index('id_reserva');
            $table->index('rut_asistente');
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
