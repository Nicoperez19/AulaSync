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
        Schema::create('area_academicas', function (Blueprint $table) {
            $table->string('id_area_academica',20)->primary(); 
            $table->string('nombre_area_academica', 255);
            $table->enum('tipo_area_academica', ['departamento', 'escuela']); 
            $table->string('id_facultad', 20);
            $table->foreign('id_facultad')->references('id_facultad')->on('facultades')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('area_academicas');
    }
};
