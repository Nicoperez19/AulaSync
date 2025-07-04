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
        Schema::create('usuarios_no_registrados', function (Blueprint $table) {
            $table->id();
            $table->string('run')->unique();
            $table->string('nombre');
            $table->string('email');
            $table->string('telefono');
            $table->integer('modulos_utilizacion');
            $table->string('qr_run')->nullable(); // Para el código QR generado
            $table->boolean('convertido_a_usuario')->default(false); // Si se convirtió en usuario registrado
            $table->string('id_usuario_registrado')->nullable(); // RUN del usuario si se convierte
            $table->timestamps();
            
            // Índices para mejorar el rendimiento
            $table->index('run');
            $table->index('email');
            $table->index('convertido_a_usuario');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('usuarios_no_registrados');
    }
};
