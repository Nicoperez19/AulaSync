<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('visitantes', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('run_solicitante', 255)->index();
            $table->string('nombre', 255);
            $table->string('correo', 255);
            $table->string('telefono', 255);
            $table->enum('tipo_solicitante', ['estudiante', 'personal', 'visitante', 'otro'])->default('otro')->index();
            $table->tinyInteger('activo')->default(1)->index();
            $table->timestamp('fecha_registro')->default(DB::raw('current_timestamp()'));
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('visitantes');
    }
};
