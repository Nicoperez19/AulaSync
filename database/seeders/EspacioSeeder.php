<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Espacio;
use App\Models\Tenant;
use Illuminate\Support\Facades\Log;

class EspacioSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $tenant = Tenant::current();

        if (!$tenant) {
            $this->command->error("No se encontró tenant activo para el seeder.");
            return;
        }

        $sedeId = $tenant->sede_id;
        $file = __DIR__ . "/Data/Espacios/{$sedeId}.php";

        if (!file_exists($file)) {
            $this->command->warn("No hay archivo de definición de espacios para la sede: {$sedeId}");
            return;
        }

        $todosLosEspacios = require $file;

        // Crear mapeo de piso_id antiguo a nuevo basado en número y nombre de piso
        $pisoMap = $this->buildPisoMap($tenant);
        
        // Filtrar y procesar espacios
        $espacios = collect($todosLosEspacios)
            ->map(function ($espacio) use ($pisoMap, $sedeId) {
                // Asegurar que id_sede esté asignado
                $espacio['id_sede'] = $sedeId;
                
                // Mapear piso_id antiguo a nuevo ID real
                if (isset($espacio['piso_id']) && isset($pisoMap[$espacio['piso_id']])) {
                    $espacio['piso_id'] = $pisoMap[$espacio['piso_id']];
                }
                
                // Quitar id_sede ya que no existe en la tabla (está implícito por el tenant)
                unset($espacio['id_sede']);
                
                // Si capacidad_maxima es null, usar puestos_disponibles o 0
                if (!isset($espacio['capacidad_maxima']) || $espacio['capacidad_maxima'] === null) {
                    $espacio['capacidad_maxima'] = $espacio['puestos_disponibles'] ?? 0;
                }
                return $espacio;
            })
            ->all();
        
        $this->command->info("Creando espacios para sede: {$sedeId}");
        
        foreach ($espacios as $data) {
            // Verificar si el espacio ya existe para evitar duplicados en seeds repetidos
            $exists = \DB::connection('tenant')->table('espacios')->where('id_espacio', $data['id_espacio'])->exists();
            
            if (!$exists) {
                // Insertar directamente en la conexión tenant
                $espacioId = \DB::connection('tenant')->table('espacios')->insertGetId($data);
                
                // Generar QR para el espacio recién creado
                // Usamos el modelo para aprovechar la función generateQR
                $espacio = \App\Models\Espacio::on('tenant')->withoutGlobalScopes()->find($espacioId);
                if ($espacio) {
                    try {
                        $espacio->generateQR();
                    } catch (\Exception $e) {
                         // Ignorar error de QR si falla (ej. si no hay driver de imagen)
                    }
                }
            }
        }
        
        $this->command->info("Proceso completado para " . count($espacios) . " espacios potenciales.");
    }

    /**
     * Construir mapeo de IDs antiguos a IDs reales de pisos
     */
    private function buildPisoMap($tenant)
    {
        if (!$tenant) {
            return [];
        }

        // Usar DB directo para evitar global scopes
        $pisos = collect(\DB::connection('tenant')->table('pisos')->get());
        $this->command->info("Pisos encontrados para tenant {$tenant->sede_id}: " . $pisos->count());
        
        $map = [];

        // Mapeo para Talcahuano (TH)
        if ($tenant->sede_id === 'TH') {
            $piso1 = $pisos->where('numero_piso', 1)->first();
            $piso2 = $pisos->where('numero_piso', 2)->first();
            $map[1] = $piso1 ? $piso1->id : null;
            $map[2] = $piso2 ? $piso2->id : null;
        }
        // Mapeo para Cañete (CT)
        elseif ($tenant->sede_id === 'CT') {
            $piso1 = $pisos->where('numero_piso', 1)->first();
            $piso2 = $pisos->where('numero_piso', 2)->first();
            $piso3 = $pisos->where('numero_piso', 3)->where('nombre_piso', 'Taller Gastronómico')->first();
            // Fallback si no encuentra por nombre específico, intentar mapear por número lógico
            if (!$piso3) $piso3 = $pisos->where('numero_piso', 3)->first();

            $map[3] = $piso1 ? $piso1->id : null;
            $map[4] = $piso2 ? $piso2->id : null;
            $map[14] = $piso3 ? $piso3->id : null;
        }
        // Mapeo para Chillán (CH)
        elseif ($tenant->sede_id === 'CH') {
            $piso1 = $pisos->where('numero_piso', 1)->first();
            $piso2 = $pisos->where('numero_piso', 2)->first();
            $piso3 = $pisos->where('numero_piso', 3)->first();
            $map[5] = $piso1 ? $piso1->id : null;
            $map[6] = $piso2 ? $piso2->id : null;
            $map[7] = $piso3 ? $piso3->id : null; // Gimnasio
        }
        // Mapeo para Los Ángeles (LA) - 3 pisos únicos por localidad
        elseif ($tenant->sede_id === 'LA') {
            $pisoCaupolicán = $pisos->where('nombre_piso', 'CAUPOLICÁN 276')->first()->id ?? null;
            $pisoVillagrán220 = $pisos->where('nombre_piso', 'VILLAGRÁN 220')->first()->id ?? null;
            $pisoVillagrán251 = $pisos->where('nombre_piso', 'VILLAGRÁN 251')->first()->id ?? null;
            
            // Todos los espacios de Caupolicán 276 al mismo piso (8, 9)
            $map[8] = $pisoCaupolicán;
            $map[9] = $pisoCaupolicán;
            
            // Todos los espacios de Villagrán 220 al mismo piso (10)
            $map[10] = $pisoVillagrán220;
            
            // Todos los espacios de Villagrán 251 al mismo piso (11, 12)
            $map[11] = $pisoVillagrán251;
            $map[12] = $pisoVillagrán251;
        }

        return $map;
    }
}
