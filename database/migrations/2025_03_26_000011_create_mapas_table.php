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
        Schema::create('mapas', function (Blueprint $table) {
            $table->string('id_mapa',20)->primary();
            $table->string('nombre_mapa');
            $table->string('ruta_mapa');  //ruta de la imagen del mapa
            $table->string('id_espacio'); 
            $table->foreign('id_espacio')->references('id_espacio')->on('espacios')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mapas');
    }
};
