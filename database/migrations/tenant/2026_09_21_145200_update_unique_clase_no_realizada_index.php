<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $conn = Schema::getConnection();

        // 1. Eliminar la clave única anterior si todavía existe (evitar error si ya fue eliminada)
        $oldIndex = $conn->select("SHOW INDEX FROM clases_no_realizadas WHERE Key_name = 'unique_clase_no_realizada'");
        if (!empty($oldIndex)) {
            $conn->statement('ALTER TABLE clases_no_realizadas DROP INDEX unique_clase_no_realizada');
        }

        // 2. Crear la nueva clave única especificando longitudes para evitar el error MySQL 1071 (max key length 3072 bytes en utf8mb4)
        $newIndex = $conn->select("SHOW INDEX FROM clases_no_realizadas WHERE Key_name = 'unique_clase_no_realizada_profesor'");
        if (empty($newIndex)) {
            $conn->statement('ALTER TABLE clases_no_realizadas ADD UNIQUE unique_clase_no_realizada_profesor (id_asignatura(50), id_espacio(50), id_modulo(50), fecha_clase, run_profesor(20))');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $conn = Schema::getConnection();

        $newIndex = $conn->select("SHOW INDEX FROM clases_no_realizadas WHERE Key_name = 'unique_clase_no_realizada_profesor'");
        if (!empty($newIndex)) {
            $conn->statement('ALTER TABLE clases_no_realizadas DROP INDEX unique_clase_no_realizada_profesor');
        }

        $oldIndex = $conn->select("SHOW INDEX FROM clases_no_realizadas WHERE Key_name = 'unique_clase_no_realizada'");
        if (empty($oldIndex)) {
            $conn->statement('ALTER TABLE clases_no_realizadas ADD UNIQUE unique_clase_no_realizada (id_asignatura(50), id_espacio(50), id_modulo(50), fecha_clase)');
        }
    }
};
