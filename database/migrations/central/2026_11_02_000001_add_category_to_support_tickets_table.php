<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::connection('mysql')->table('support_tickets', function (Blueprint $table) {
            $table->string('category', 40)->default('other')->after('description')->index();
        });
    }

    public function down(): void
    {
        Schema::connection('mysql')->table('support_tickets', function (Blueprint $table) {
            $table->dropIndex(['category']);
            $table->dropColumn('category');
        });
    }
};
