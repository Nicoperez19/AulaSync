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
        // Primero verificamos y eliminamos la restricción de clave foránea si existe
        try {
            Schema::table('destinatarios_correos', function (Blueprint $table) {
                $table->dropForeign(['user_id']);
            });
        } catch (\Exception $e) {
            // La restricción ya no existe, continuar
        }

        Schema::table('destinatarios_correos', function (Blueprint $table) {
            // Hacer user_id nullable para permitir destinatarios externos (mantener tipo unsignedBigInteger)
            $table->unsignedBigInteger('user_id')->nullable()->change();

            // Agregar campos para destinatarios externos (no registrados) solo si no existen
            if (!Schema::hasColumn('destinatarios_correos', 'es_externo')) {
                $table->boolean('es_externo')->default(false)->after('user_id');
            }
            if (!Schema::hasColumn('destinatarios_correos', 'email_externo')) {
                $table->string('email_externo')->nullable()->after('es_externo');
            }
            if (!Schema::hasColumn('destinatarios_correos', 'nombre_externo')) {
                $table->string('nombre_externo')->nullable()->after('email_externo');
            }
        });

        Schema::table('destinatarios_correos', function (Blueprint $table) {
            // Recrear la restricción de clave foránea
            try {
                // $table->foreign('user_id')->references('run')->on('users')->onDelete('cascade'); // FK a tabla central
            } catch (\Exception $e) {
                // La restricción ya existe, continuar
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('destinatarios_correos', function (Blueprint $table) {
            // Eliminar la restricción de clave foránea
            $table->dropForeign(['user_id']);
        });

        Schema::table('destinatarios_correos', function (Blueprint $table) {
            $table->dropColumn(['es_externo', 'email_externo', 'nombre_externo']);

            // Revertir user_id a no nullable (si es posible) - mantener tipo unsignedBigInteger
            $table->unsignedBigInteger('user_id')->nullable(false)->change();
        });

        Schema::table('destinatarios_correos', function (Blueprint $table) {
            // Recrear la restricción de clave foránea
            // $table->foreign('user_id')->references('run')->on('users')->onDelete('cascade'); // FK a tabla central
        });
    }
};
