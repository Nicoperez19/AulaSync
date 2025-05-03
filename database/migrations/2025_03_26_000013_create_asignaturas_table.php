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
        Schema::create('asignaturas', function (Blueprint $table) {
            $table->string('id_asignatura',20)->primary();
            $table->string('nombre', 100);
         
            $table->integer('horas_directas');
            $table->integer('horas_indirectas');
            $table->string('area_conocimiento', 100);
            $table->string('periodo', 20);

            $table->unsignedBigInteger('id');
            $table->string('id_carrera', 20);

            $table->foreign('id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('id_carrera')->references('id_carrera')->on('carreras')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('asignaturas');
    }
};
