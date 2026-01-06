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
        Schema::create('plantillas_correos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tipo_correo_masivo_id')->nullable()->constrained('tipos_correos_masivos')->onDelete('set null');
            $table->string('nombre'); // Nombre de la plantilla
            $table->string('asunto'); // Asunto del correo
            $table->text('contenido_html'); // Contenido HTML del correo
            $table->text('contenido_texto')->nullable(); // Versión en texto plano
            $table->json('variables_disponibles')->nullable(); // Variables que se pueden usar: {{nombre}}, {{fecha}}, etc.
            $table->boolean('activo')->default(true);
            $table->unsignedBigInteger('creado_por')->nullable(); // RUN del usuario que creó
            $table->unsignedBigInteger('actualizado_por')->nullable(); // RUN del usuario que actualizó
            $table->timestamps();

            // Foreign keys
            // $table->foreign('creado_por')->references('run')->on('users')->onDelete('set null'); // FK a tabla central
            // $table->foreign('actualizado_por')->references('run')->on('users')->onDelete('set null'); // FK a tabla central

            // Índices
            $table->index(['tipo_correo_masivo_id', 'activo']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('plantillas_correos');
    }
};
