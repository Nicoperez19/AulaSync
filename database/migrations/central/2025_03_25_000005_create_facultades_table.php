<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $connection = Schema::getConnection()->getName();
        
        Schema::create('facultades', function (Blueprint $table) use ($connection) {
            $table->string('id_facultad', 20)->primary();
            $table->string('nombre_facultad', 100);

            $table->string('id_universidad', 20);
            $table->string('id_sede', 20);
            $table->string('id_campus', 20)->nullable();
            
            // Solo crear FKs en database central (sedes/universidades no existen en tenant databases)
            if ($connection !== 'tenant') {
                $table->foreign('id_universidad')->references('id_universidad')->on('universidades')->onDelete('cascade');
                $table->foreign('id_sede')->references('id_sede')->on('sedes')->onDelete('cascade');
            }
            
            // FK a campuses (sí existe en tenant database)
            $table->foreign('id_campus')->references('id_campus')->on('campuses')->nullOnDelete();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('facultades');
    }
};
