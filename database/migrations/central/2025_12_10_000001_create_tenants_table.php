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
        Schema::create('tenants', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Nombre del tenant (ej: "Sede Principal")
            $table->string('domain')->unique(); // Subdominio (ej: "principal")
            $table->string('database')->nullable(); // Nombre de la base de datos específica del tenant
            $table->string('prefijo_espacios')->nullable(); // Prefijo para los espacios de este tenant
            $table->string('sede_id', 20)->nullable();
            $table->foreign('sede_id')->references('id_sede')->on('sedes')->onDelete('set null');
            $table->boolean('is_active')->default(true);
            $table->boolean('is_default')->default(false); // Marca si es el tenant por defecto
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tenants');
    }
};
