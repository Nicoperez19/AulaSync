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
            $table->unsignedBigInteger('id'); 
            $table->foreign('id')->references('id')->on('pisos')->onDelete('cascade');
            $table->enum('tipo_espacio', ['Aula', 'Laboratorio', 'Biblioteca', 'Sala de Reuniones', 'Oficinas']);
            $table->enum('estado', ['Disponible', 'Ocupado', 'Reservado']);
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
