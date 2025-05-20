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
        Schema::create('planificacion__asignaturas', function (Blueprint $table) {
            $table->id();
            $table->string('id_asignatura');
            $table->unsignedBigInteger('id_horario');
            $table->unsignedBigInteger('id_modulo');
            $table->string('id_espacio');

            $table->foreign('id_asignatura')->references('id_asignatura')->on('asignaturas')->onDelete('cascade');
            $table->foreign('id_horario')->references('id_horario')->on('horarios')->onDelete('cascade');
            $table->foreign('id_modulo')->references('id_modulo')->on('modulos')->onDelete('cascade');
            $table->foreign('id_espacio')->references('id_espacio')->on('espacios')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('planificacion__asignaturas');
    }
};
