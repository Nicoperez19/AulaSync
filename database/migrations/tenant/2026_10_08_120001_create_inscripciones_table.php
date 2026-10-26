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
        Schema::create('inscripciones', function (Blueprint $table) {
            $table->id();
            $table->string('id_asignatura', 20);
            $table->foreignId('estudiante_id')->constrained('estudiantes')->onDelete('cascade');
            $table->string('periodo', 20)->nullable();
            $table->timestamps();

            $table->foreign('id_asignatura')->references('id_asignatura')->on('asignaturas')->onDelete('cascade');
            $table->unique(['id_asignatura', 'estudiante_id'], 'uniq_inscripcion_asig_est');
            $table->index('periodo');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inscripciones');
    }
};
