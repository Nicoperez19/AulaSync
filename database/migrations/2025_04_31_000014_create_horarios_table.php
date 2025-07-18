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
            $table->string('id_horario')->primary();
            $table->string('nombre');
            $table->string('periodo'); // Ej: "2025-1"

            $table->unsignedBigInteger('run_profesor');
            $table->foreign('run_profesor')->references('run_profesor')->on('profesors')->onDelete('cascade');

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
