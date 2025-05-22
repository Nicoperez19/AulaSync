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
        Schema::create('bloques', function (Blueprint $table) {
            $table->string('id_bloque',100)->primary();
            $table->string('id_espacio');
            $table->foreign('id_espacio')->references('id_espacio')->on('espacios')->onDelete('cascade');
            $table->integer('posicion_x');
            $table->integer('posicion_y');
            $table->boolean('estado')->default(true);
            $table->string('id_mapa'); 
            $table->foreign('id_mapa')->references('id_mapa')->on('mapas')->onDelete('cascade'); 
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bloques');
    }
};
