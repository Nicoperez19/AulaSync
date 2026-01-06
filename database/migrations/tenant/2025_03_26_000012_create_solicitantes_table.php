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
            $table->string('run_solicitante')->unique(); // RUN del solicitante (viene del QR)
            $table->string('nombre'); // Nombre completo del solicitante
            $table->string('correo'); // Correo electrónico
            $table->string('telefono'); // Teléfono de contacto
            $table->enum('tipo_solicitante', ['estudiante', 'personal', 'visitante', 'otro'])->default('otro');
            $table->boolean('activo')->default(true); // Si el solicitante está activo
            $table->timestamp('fecha_registro')->useCurrent(); // Fecha de registro
            $table->timestamps();
            
            // Índices para optimizar consultas
            $table->index('run_solicitante');
            $table->index('tipo_solicitante');
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