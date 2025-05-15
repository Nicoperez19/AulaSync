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
            $table->string('id_bloque',20)->primary();
            $table->string('color_bloque', 100);
            $table->integer('pos_x');
            $table->integer('pos_y');
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
