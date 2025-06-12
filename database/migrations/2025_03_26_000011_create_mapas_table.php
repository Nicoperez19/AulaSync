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
            $table->string('id_mapa',200)->primary();
            $table->string('nombre_mapa');
            $table->string('ruta_mapa');  //ruta de la imagen del mapa
            $table->string('ruta_canvas'); // ruta para canva
            $table->unsignedBigInteger('piso_id'); 
            $table->foreign('piso_id')->references('id')->on('pisos')->onDelete('cascade');
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
