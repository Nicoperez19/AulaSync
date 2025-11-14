<?php

/**
 * Script para crear una reserva de prueba y marcar el espacio como ocupado
 * Clase: INTRODUCCIÓN A SAP EN PROCESOS ADMINISTRATIVOS Y LOGÍSTICOS
 * Profesor: OLIVARES AMSTEIN, EDUARDO RODOLFO
 * Espacio: TH-LAB
 * Módulos: 1-2 (08:10 - 10:00)
 * Fecha: Hoy
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Reserva;
use App\Models\Espacio;
use App\Models\Profesor;
use App\Models\Asignatura;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

try {
    echo "=== CREANDO RESERVA DE PRUEBA ===\n\n";
    
    $fechaHoy = Carbon::now()->format('Y-m-d');
    $horaActual = Carbon::now()->format('H:i:s');
    
    echo "Fecha: {$fechaHoy}\n";
    echo "Hora actual: {$horaActual}\n\n";
    
    // Buscar el espacio TH-LAB
    $espacio = Espacio::where('id_espacio', 'TH-LAB')->first();
    
    if (!$espacio) {
        echo "❌ ERROR: Espacio TH-LAB no encontrado\n";
        exit(1);
    }
    
    echo "✅ Espacio encontrado: {$espacio->id_espacio} - {$espacio->nombre_espacio}\n";
    echo "   Estado actual: {$espacio->estado}\n\n";
    
    // Buscar o crear el profesor
    $profesor = Profesor::where('name', 'LIKE', '%OLIVARES%')->first();
    
    if (!$profesor) {
        echo "⚠️  Profesor OLIVARES no encontrado, buscando cualquier profesor...\n";
        $profesor = Profesor::first();
    }
    
    if (!$profesor) {
        echo "❌ ERROR: No hay profesores en la base de datos\n";
        exit(1);
    }
    
    echo "✅ Profesor: {$profesor->name} (RUN: {$profesor->run_profesor})\n\n";
    
    // Buscar o crear la asignatura
    $asignatura = Asignatura::where('nombre_asignatura', 'LIKE', '%SAP%')->first();
    
    if (!$asignatura) {
        echo "⚠️  Asignatura de SAP no encontrada, buscando cualquier asignatura...\n";
        $asignatura = Asignatura::first();
    }
    
    if (!$asignatura) {
        echo "❌ ERROR: No hay asignaturas en la base de datos\n";
        exit(1);
    }
    
    echo "✅ Asignatura: {$asignatura->nombre_asignatura}\n";
    echo "   ID: {$asignatura->id_asignatura}\n\n";
    
    // Verificar si ya existe una reserva activa para este espacio hoy
    $reservaExistente = Reserva::where('id_espacio', 'TH-LAB')
        ->where('fecha_reserva', $fechaHoy)
        ->where('estado', 'activa')
        ->first();
    
    if ($reservaExistente) {
        echo "⚠️  Ya existe una reserva activa para TH-LAB hoy:\n";
        echo "   ID: {$reservaExistente->id_reserva}\n";
        echo "   Hora: {$reservaExistente->hora}\n";
        echo "   Estado: {$reservaExistente->estado}\n\n";
        echo "¿Desea eliminarla y crear una nueva? (s/n): ";
        
        $handle = fopen("php://stdin", "r");
        $line = fgets($handle);
        if (trim($line) != 's') {
            echo "Operación cancelada.\n";
            exit(0);
        }
        
        $reservaExistente->delete();
        echo "✅ Reserva anterior eliminada\n\n";
    }
    
    DB::beginTransaction();
    
    // Crear la reserva
    $reserva = Reserva::create([
        'id_reserva' => Reserva::generarIdUnico(),
        'hora' => '08:10:00',
        'fecha_reserva' => $fechaHoy,
        'id_espacio' => 'TH-LAB',
        'id_asignatura' => $asignatura->id_asignatura,
        'run_profesor' => $profesor->run_profesor,
        'modulos' => 2, // Módulos 1 y 2
        'tipo_reserva' => 'clase',
        'estado' => 'activa',
        'observaciones' => 'Reserva de prueba - Clase de 08:10 a 10:00',
    ]);
    
    echo "✅ Reserva creada:\n";
    echo "   ID: {$reserva->id_reserva}\n";
    echo "   Espacio: {$reserva->id_espacio}\n";
    echo "   Hora inicio: {$reserva->hora}\n";
    echo "   Módulos: {$reserva->modulos}\n";
    echo "   Tipo: {$reserva->tipo_reserva}\n";
    echo "   Estado: {$reserva->estado}\n\n";
    
    // Actualizar el estado del espacio a Ocupado
    $espacio->estado = 'Ocupado';
    $espacio->save();
    
    echo "✅ Espacio {$espacio->id_espacio} marcado como OCUPADO\n\n";
    
    DB::commit();
    
    echo "=== INFORMACIÓN DE LA CLASE ===\n";
    echo "Módulos: 1 - 2\n";
    echo "Horario: 08:10 - 10:00\n";
    echo "Espacio: TH-LAB\n";
    echo "Asignatura: {$asignatura->nombre_asignatura}\n";
    echo "Profesor: {$profesor->name}\n";
    echo "Estado actual: CLASE POR INICIAR / OCUPADO\n\n";
    
    echo "=== PRÓXIMOS PASOS ===\n";
    echo "1. Verifica en el plano digital que TH-LAB aparece como ocupado\n";
    echo "2. Espera hasta las 10:00 (fin de la clase)\n";
    echo "3. Ejecuta el comando: php artisan reservas:finalizar-expiradas\n";
    echo "4. Verifica que el espacio se libere automáticamente\n\n";
    
    echo "Para verificar el estado actual:\n";
    echo "php artisan tinker\n";
    echo ">>> Espacio::find('TH-LAB')\n";
    echo ">>> Reserva::where('id_espacio', 'TH-LAB')->where('estado', 'activa')->first()\n\n";
    
    echo "✅ SCRIPT COMPLETADO EXITOSAMENTE\n";
    
} catch (\Exception $e) {
    DB::rollBack();
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
    exit(1);
}
