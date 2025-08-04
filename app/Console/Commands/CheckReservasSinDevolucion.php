<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Reserva;

class CheckReservasSinDevolucion extends Command
{
    protected $signature = 'check:reservas-sin-devolucion';
    protected $description = 'Verificar reservas sin devolución';

    public function handle()
    {
        $this->info('=== VERIFICANDO RESERVAS SIN DEVOLUCIÓN ===');
        
        // Consulta simplificada (sin filtros de facultad/piso)
        $reservasSinDevolucion = Reserva::with(['profesor', 'solicitante', 'espacio.piso.facultad'])
            ->where('estado', 'activa')
            ->whereNull('hora_salida')
            ->latest('fecha_reserva')
            ->latest('hora')
            ->get();
        
        $this->info("Total reservas sin devolución: " . $reservasSinDevolucion->count());
        
        if ($reservasSinDevolucion->count() > 0) {
            foreach ($reservasSinDevolucion as $reserva) {
                $tipo = $reserva->run_profesor ? 'Profesor' : 'Solicitante';
                $nombre = $reserva->run_profesor ? 
                    ($reserva->profesor->name ?? 'N/A') : 
                    ($reserva->solicitante->nombre ?? 'N/A');
                $facultad = $reserva->espacio->piso->facultad->id_facultad ?? 'N/A';
                
                $this->line("- ID: {$reserva->id_reserva}, Tipo: $tipo, Nombre: $nombre, Espacio: {$reserva->espacio->id_espacio}, Facultad: $facultad");
            }
        } else {
            $this->info('No hay reservas activas sin devolución en este momento.');
        }
        
        // Mostrar también todas las reservas para contexto
        $this->info('Todas las reservas (para contexto):');
        $todasReservas = Reserva::with(['profesor', 'solicitante', 'espacio'])
            ->latest('fecha_reserva')
            ->latest('hora')
            ->limit(5)
            ->get();
        
        foreach ($todasReservas as $reserva) {
            $tipo = $reserva->run_profesor ? 'Profesor' : 'Solicitante';
            $nombre = $reserva->run_profesor ? 
                ($reserva->profesor->name ?? 'N/A') : 
                ($reserva->solicitante->nombre ?? 'N/A');
            
            $this->line("- ID: {$reserva->id_reserva}, Tipo: $tipo, Nombre: $nombre, Estado: {$reserva->estado}, Hora Salida: " . ($reserva->hora_salida ?? 'NULL'));
        }
        
        return 0;
    }
} 