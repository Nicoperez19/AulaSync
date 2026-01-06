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
        Schema::table('destinatarios_correos', function (Blueprint $table) {
            // Hacer user_id nullable
            $table->unsignedBigInteger('user_id')->nullable()->change();
            
            // Agregar campos para destinatarios sin usuario registrado
            $table->string('email')->nullable()->after('user_id');
            $table->string('nombre')->nullable()->after('email');
            
            // Agregar índice para búsquedas por email
            $table->index('email');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('destinatarios_correos', function (Blueprint $table) {
            // Revertir cambios
            $table->dropIndex(['email']);
            $table->dropColumn(['email', 'nombre']);
            $table->unsignedBigInteger('user_id')->nullable(false)->change();
        });
    }
};
