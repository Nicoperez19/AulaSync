<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('espacios', function (Blueprint $table) {
            $table->string('id_espacio')->primary();
            $table->string('nombre_espacio');
            $table->foreignId('piso_id')->constrained('pisos')->onDelete('cascade');
            $table->enum('tipo_espacio', ['Sala de Clases', 'Laboratorio', 'Biblioteca', 'Sala de Reuniones', 'Oficinas', 'Taller', 'Auditorio', 'Sala de Estudio']);
            $table->enum('estado', ['Disponible', 'Ocupado', 'Reservado']);
            $table->string('qr_espacio')->nullable();
            $table->integer('puestos_disponibles')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('espacios');
    }
};
