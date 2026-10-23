<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Registra los casos en que un profesor no devolvió la llave (no hizo check-out)
     * antes del cierre del día. El comando 'espacios:liberar' (00:00 cada noche) inserta
     * un registro aquí por cada reserva activa que cierra automáticamente.
     * No se usan logs de sistema — toda la trazabilidad queda en esta tabla.
     */
    public function up(): void
    {
        Schema::connection('tenant')->create('llaves_no_devueltas', function (Blueprint $table) {
            $table->id();

            // Referencia a la reserva que se cerró automáticamente
            $table->string('id_reserva')->nullable()->index();

            // Espacio y responsable
            $table->string('id_espacio')->index();
            $table->string('run_profesor')->nullable()->index();

            // Información de la clase asociada (si existía)
            $table->string('id_asignatura')->nullable();

            // Datos horarios
            $table->date('fecha_clase')->index();
            $table->time('hora_entrada')->nullable()
                  ->comment('Hora en que el profesor registró entrada');
            $table->time('hora_termino_esperada')->nullable()
                  ->comment('Hora estimada de término del módulo o clase');

            // Cuándo el sistema cerró el espacio automáticamente
            $table->timestamp('cerrado_en')
                  ->comment('Momento en que el sistema liberó el espacio al cambio de día');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::connection('tenant')->dropIfExists('llaves_no_devueltas');
    }
};
