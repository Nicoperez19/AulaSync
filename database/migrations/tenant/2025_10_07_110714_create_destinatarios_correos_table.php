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
        Schema::create('destinatarios_correos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id'); // Debe coincidir con el tipo de 'run' en users
            $table->string('rol')->nullable(); // Ej: "Jefe de Carrera", "Director", "Subdirector"
            $table->string('cargo')->nullable(); // Descripción adicional del cargo
            $table->boolean('activo')->default(true);
            $table->timestamps();

            // Foreign key referenciando 'run' en lugar de 'id'
            // $table->foreign('user_id')->references('run')->on('users')->onDelete('cascade'); // FK a tabla central

            // Índice para mejorar búsquedas
            $table->index(['user_id', 'activo']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('destinatarios_correos');
    }
};
