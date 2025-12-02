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
        Schema::table('users', function (Blueprint $table) {
            // Token QR personal cifrado con Argon2
            $table->string('qr_personal_token', 255)->nullable()->after('remember_token');
            // Fecha de creación del token
            $table->timestamp('qr_personal_created_at')->nullable()->after('qr_personal_token');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['qr_personal_token', 'qr_personal_created_at']);
        });
    }
};
