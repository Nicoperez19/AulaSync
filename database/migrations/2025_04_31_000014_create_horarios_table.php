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
        Schema::create('horarios', function (Blueprint $table) {
            $table->bigIncrements('id_horario');
            $table->string('nombre'); 
            $table->string('periodo'); // Ej: "2025-1"
            $table->unsignedBigInteger('id_carrera')->nullable();
            $table->foreign('id_carrera')->references('id_carrera')->on('carreras')->onDelete('set null');
            $table->string('seccion')->nullable();
            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('horarios');
    }
};
