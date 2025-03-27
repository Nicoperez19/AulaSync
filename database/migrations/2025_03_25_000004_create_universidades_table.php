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
        Schema::create('universidades', function (Blueprint $table) {
            $table->string('id_universidad', 20)->primary();
            $table->string('nombre_universidad', 100);
            $table->string('direccion_universidad', 255);
            $table->string('telefono_universidad', 20)->nullable();
            $table->string('id_comuna', 20);
            $table->foreign('id_comuna')->references('id_comuna')->on('comunas')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('universidades');
    }
};
