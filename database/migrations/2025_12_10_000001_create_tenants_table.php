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
            $table->foreignId('sede_id')->nullable()->constrained('sedes', 'id_sede')->onDelete('cascade');
            $table->boolean('is_active')->default(true);
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
