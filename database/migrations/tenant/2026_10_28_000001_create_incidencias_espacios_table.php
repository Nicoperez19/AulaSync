<?php

use App\Support\IncidenciasSchema;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Sistema de reporte de fallas e incidencias en espacios.
     *
     * - incidencias_espacios: una fila por falla/incidencia (qué, dónde, quién, cuándo, estado actual).
     * - incidencias_espacios_eventos: bitácora inmutable con cada cambio (trazabilidad completa).
     *
     * Una incidencia con `bloquea_uso = true` es la razón de que un espacio esté en "Mantenimiento".
     * La definición del esquema vive en App\Support\IncidenciasSchema (compartida con el servicio).
     */
    public function up(): void
    {
        IncidenciasSchema::crear();
    }

    public function down(): void
    {
        Schema::connection('tenant')->dropIfExists('incidencias_espacios_eventos');
        Schema::connection('tenant')->dropIfExists('incidencias_espacios');
    }
};
