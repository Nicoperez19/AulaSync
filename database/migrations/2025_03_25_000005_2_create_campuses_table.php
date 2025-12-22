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
        $connection = Schema::getConnection()->getName();
        
        Schema::create('campuses', function (Blueprint $table) use ($connection) {
            $table->string('id_campus', 20)->primary();
            $table->string('nombre_campus', 100);
            $table->string('id_sede', 20);
            
            // Solo crear FK en database central (sedes no existe en tenant databases)
            if ($connection !== 'tenant') {
                $table->foreign('id_sede')->references('id_sede')->on('sedes')->onDelete('cascade');
            }
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('campuses');
    }
};
