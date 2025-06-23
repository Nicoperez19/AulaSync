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
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->string('type'); // key_return, reservation, system, warning, info
            $table->string('title');
            $table->text('message');
            $table->json('data')->nullable(); // Datos adicionales en formato JSON
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('priority')->default('medium'); // low, medium, high, urgent
            $table->timestamp('read_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->string('action_url')->nullable(); // URL para acción
            $table->string('action_text')->nullable(); // Texto del botón de acción
            $table->timestamps();
            
            // Índices para mejorar el rendimiento
            $table->index(['user_id', 'read_at']);
            $table->index(['type', 'priority']);
            $table->index('expires_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
