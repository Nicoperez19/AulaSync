<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Runs on the CENTRAL database (mysql connection).
     */
    public function up(): void
    {
        Schema::connection('mysql')->create('support_ticket_replies', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('ticket_id');
            $table->string('user_id');              // FK a users.run (string)
            $table->text('message');
            $table->boolean('is_staff_reply')->default(false);
            $table->timestamps();

            $table->index('ticket_id');
            $table->foreign('ticket_id')
                  ->references('id')
                  ->on('support_tickets')
                  ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::connection('mysql')->dropIfExists('support_ticket_replies');
    }
};
