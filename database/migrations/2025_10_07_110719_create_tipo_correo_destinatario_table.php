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
        Schema::create('tipo_correo_destinatario', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tipo_correo_masivo_id')->constrained('tipos_correos_masivos')->onDelete('cascade');
            $table->foreignId('destinatario_correo_id')->constrained('destinatarios_correos')->onDelete('cascade');
            $table->boolean('habilitado')->default(true); // Si este destinatario está habilitado para este tipo de correo
            $table->timestamps();

            // Evitar duplicados
            $table->unique(['tipo_correo_masivo_id', 'destinatario_correo_id'], 'tipo_dest_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tipo_correo_destinatario');
    }
};
