<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */    
    protected $table = 'universidades'; 

    public function up(): void
    {
        Schema::create('universidades', function (Blueprint $table) {
            $table->string('id_universidad', 20)->primary();
            $table->string('nombre_universidad', 100);
            $table->string('imagen_logo')->nullable();
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
