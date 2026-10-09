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
        Schema::create('asistencia_estudiantes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sesion_id')->constrained('sesiones_asistencia')->onDelete('cascade');
            $table->foreignId('estudiante_id')->constrained('estudiantes')->onDelete('cascade');
            $table->boolean('presente')->default(true);
            $table->boolean('inscrito')->default(true);
            $table->enum('metodo', ['checkbox', 'qr'])->default('checkbox');
            $table->string('observacion', 255)->nullable();
            $table->timestamps();

            $table->unique(['sesion_id', 'estudiante_id'], 'uniq_sesion_estudiante');
            $table->index('presente');
            $table->index('metodo');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('asistencia_estudiantes');
    }
};
