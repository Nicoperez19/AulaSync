<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Garantiza que la tabla `notificaciones` exista en la BD de TODAS las sedes
 * (Concepción, Chillán, Cañete, Los Ángeles, Talcahuano) para el Centro de Notificaciones.
 * Es idempotente: si la tabla ya existe, no hace nada.
 *
 * Aplicar con: php artisan tenants:migrate
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('notificaciones')) {
            return;
        }

        Schema::create('notificaciones', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('run_usuario');
            $table->string('tipo');
            $table->string('titulo');
            $table->text('mensaje');
            $table->string('url')->nullable();
            $table->boolean('leida')->default(false);
            $table->timestamp('fecha_lectura')->nullable();
            $table->json('datos_adicionales')->nullable();
            $table->timestamps();

            $table->index(['run_usuario', 'leida']);
            $table->index('tipo');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        // Intencionalmente vacío: la tabla pertenece a la migración original.
    }
};
