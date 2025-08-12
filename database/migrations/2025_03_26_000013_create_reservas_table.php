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
        Schema::create('reservas', function (Blueprint $table) {
            $table->string('id_reserva', 20)->primary();
            $table->time('hora');
            $table->date('fecha_reserva');
            $table->string('id_espacio');
            $table->unsignedBigInteger('run_profesor'); 
            $table->string('run_solicitante')->nullable();
            $table->enum('tipo_reserva', ['clase', 'espontanea', 'directa'])->default('clase');
            $table->enum('estado', ['activa', 'finalizada'])->default('activa');
            $table->time('hora_salida')->nullable();
            $table->timestamps();
            
            $table->foreign('id_espacio')->references('id_espacio')->on('espacios')->onDelete('cascade');
            $table->foreign('run_profesor')->references('run_profesor')->on('profesors')->onDelete('cascade');
            $table->foreign('run_solicitante')->references('run_solicitante')->on('solicitantes')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reservas');
    }
};
