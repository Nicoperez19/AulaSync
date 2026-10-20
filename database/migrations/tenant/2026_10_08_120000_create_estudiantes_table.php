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
        Schema::create('estudiantes', function (Blueprint $table) {
            $table->id();
            $table->string('run', 20)->unique();
            $table->string('nombre', 150);
            $table->string('email', 150)->nullable();
            $table->string('id_carrera', 20)->nullable();
            $table->enum('origen', ['carga_masiva', 'manual'])->default('manual');
            $table->timestamps();

            $table->index('nombre');
            $table->index('id_carrera');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('estudiantes');
    }
};
