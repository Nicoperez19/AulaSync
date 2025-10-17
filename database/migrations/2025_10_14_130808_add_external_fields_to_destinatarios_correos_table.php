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
            // Hacer user_id nullable para permitir destinatarios externos
            $table->string('user_id')->nullable()->change();
            
            // Agregar campos para destinatarios externos (no registrados)
            $table->boolean('es_externo')->default(false)->after('user_id');
            $table->string('email_externo')->nullable()->after('es_externo');
            $table->string('nombre_externo')->nullable()->after('email_externo');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('destinatarios_correos', function (Blueprint $table) {
            $table->dropColumn(['es_externo', 'email_externo', 'nombre_externo']);
            
            // Revertir user_id a no nullable (si es posible)
            $table->string('user_id')->nullable(false)->change();
        });
    }
};
