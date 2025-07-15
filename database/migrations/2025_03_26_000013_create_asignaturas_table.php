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
            $table->string('codigo_asignatura', 100);
            $table->string('nombre_asignatura', 100);
            $table->string('seccion', 50);
         
            $table->integer('horas_directas')->nullable();
            $table->integer('horas_indirectas')->nullable();
            $table->string('area_conocimiento', 100)->nullable();
            $table->string('periodo', 20)->nullable();

            $table->unsignedBigInteger('run_profesor');
            $table->string('id_carrera', 20);
            $table->foreign('run_profesor')->references('run_profesor')->on('profesors')->onDelete('cascade');
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
