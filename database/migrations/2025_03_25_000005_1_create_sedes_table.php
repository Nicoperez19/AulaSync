<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

<<<<<<< HEAD
return new class extends Migration
{
=======
return new class extends Migration {
>>>>>>> Nperez
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('sedes', function (Blueprint $table) {
            $table->string('id_sede', 20)->primary();
            $table->string('nombre_sede', 100);
            $table->string('id_universidad');
            $table->foreign('id_universidad')->references('id_universidad')->on('universidades')->onDelete('cascade');
<<<<<<< HEAD
            $table->foreignId('comunas_id')->constrained();
=======
            $table->foreignId('comuna_id')->constrained('comunas');
>>>>>>> Nperez
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sedes');
    }
};
