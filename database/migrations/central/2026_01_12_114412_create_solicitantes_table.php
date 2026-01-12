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
        Schema::create('solicitantes', function (Blueprint $table) {
            $table->id();
            $table->string('run_solicitante')->unique();
            $table->string('nombre');
            $table->string('correo')->unique();
            $table->string('telefono');
            $table->enum('tipo_solicitante', ['estudiante', 'personal', 'visitante', 'otro'])->default('visitante');
            $table->string('institucion_origen')->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamp('fecha_registro')->nullable();
            $table->timestamps();
            
            $table->index('run_solicitante');
            $table->index('activo');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('solicitantes');
    }
};
