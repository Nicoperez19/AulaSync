<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Cambiar la columna estado a varchar para permitir los nuevos valores
        DB::statement("ALTER TABLE clases_no_realizadas MODIFY estado VARCHAR(20) DEFAULT 'no_realizada'");
        
        // Actualizar los estados existentes
        // pendiente -> no_realizada
        DB::table('clases_no_realizadas')
            ->where('estado', 'pendiente')
            ->update(['estado' => 'no_realizada']);
            
        // confirmado -> no_realizada (ya que técnicamente si está confirmado es que no se realizó)
        DB::table('clases_no_realizadas')
            ->where('estado', 'confirmado')
            ->update(['estado' => 'no_realizada']);
            
        // justificado permanece igual
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revertir a los estados anteriores
        DB::table('clases_no_realizadas')
            ->where('estado', 'no_realizada')
            ->update(['estado' => 'pendiente']);
            
        // Revertir la estructura de la columna
        DB::statement("ALTER TABLE clases_no_realizadas MODIFY estado ENUM('pendiente', 'justificado', 'confirmado') DEFAULT 'pendiente'");
    }
};
