<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Runs on the CENTRAL database (mysql connection).
     * Support tickets are shared across all tenants in the central DB.
     */
    public function up(): void
    {
        Schema::connection('mysql')->create('support_tickets', function (Blueprint $table) {
            $table->id();
            $table->string('user_id');              // FK a users.run (string)
            $table->string('id_sede')->nullable();  // Sede del solicitante (para filtrar por tenant)
            $table->string('title');
            $table->text('description');
            $table->enum('status', ['open', 'in_progress', 'closed'])->default('open');
            $table->enum('priority', ['low', 'medium', 'high'])->default('medium');
            $table->string('assigned_to')->nullable(); // run del técnico asignado
            $table->timestamp('closed_at')->nullable();
            $table->timestamps();

            $table->index('user_id');
            $table->index('id_sede');
            $table->index('status');
            $table->index('assigned_to');
        });
    }

    public function down(): void
    {
        Schema::connection('mysql')->dropIfExists('support_tickets');
    }
};
