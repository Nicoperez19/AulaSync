<?php

namespace App\Http\Controllers;

use App\Models\Reserva;
use Illuminate\Http\Request;
use App\Models\Universidad;
use App\Models\Espacio;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class ReservasController extends Controller
{
    // Mostrar todas las reservas

    public function index()
    {
        $reservas = Reserva::with(['user', 'espacio.piso.facultad.universidad'])->paginate(10);
        $universidades = Universidad::all();
        $espaciosDisponibles = Espacio::where('estado', 'disponible')
            ->with('piso.facultad.universidad')
            ->get();

        return view('layouts.reservations.reservations_index', compact('reservas', 'universidades', 'espaciosDisponibles'));
    }

    public function create()
    {
        $universidades = Universidad::all();
        $espaciosDisponibles = Espacio::where('estado', 'disponible')
            ->with('piso.facultad.universidad')
            ->get();

        return view('layouts.reservations.reservations_create', compact('universidades', 'espaciosDisponibles'));
    }


    public function store(Request $request)
    {
        $request->validate([
            'hora' => 'required',
            'fecha_reserva' => 'required|date',
            'id_espacio' => 'required|exists:espacios,id_espacio',
            // ahora esperamos run del usuario en id_usuario
            'id_usuario' => 'required',
            'modulos' => 'nullable|integer|min:1|max:10'
        ]);

        // validar que exista el usuario/profesor/solicitante por run
        $run = $request->input('id_usuario');

        $user = \App\Models\User::where('run', $run)->first();
        $profesor = class_exists('\App\\Models\\Profesor') ? \App\Models\Profesor::where('run_profesor', $run)->first() : null;
        $solicitante = class_exists('\App\\Models\\Solicitante') ? \App\Models\Solicitante::where('run_solicitante', $run)->first() : null;

        if (!$user && !$profesor && !$solicitante) {
            return redirect()->route('reservas.index')->withErrors(['usuario' => 'Usuario no encontrado.']);
        }

        $reservaData = [
            'id_reserva' => Reserva::generarIdUnico(),
            'hora' => $request->hora,
            'fecha_reserva' => $request->fecha_reserva,
            'id_espacio' => $request->id_espacio,
            'modulos' => $request->input('modulos', 1),
            'estado' => 'activa',
        ];

        // Mapear run a la columna adecuada
        if ($profesor) {
            $reservaData['run_profesor'] = $run;
        } else {
            // Si es usuario o solicitante, lo registramos como solicitante
            $reservaData['run_solicitante'] = $run;
        }

        $reserva = Reserva::create($reservaData);

        // Marcar el espacio como ocupado para que el plano se actualice
        try {
            $espacio = Espacio::where('id_espacio', $request->id_espacio)->first();
            if ($espacio) {
                $espacio->estado = 'Ocupado';
                $espacio->save();
            }
        } catch (\Exception $e) {
            // No bloquear la creación de la reserva si falla el update del espacio
        }

        return redirect()->route('reservas.index')->with('success', 'Reserva creada exitosamente.');
    }


    public function edit($id_reserva)
    {
        $reserva = Reserva::findOrFail($id_reserva);
        return view('reservas.edit', compact('reserva'));
    }

    public function update(Request $request, $id_reserva)
    {
        $request->validate([
            'hora' => 'required',
            'fecha_reserva' => 'required|date',
            'id_espacio' => 'required|exists:espacios,id_espacio',
            'id' => 'required|exists:users,id',
        ]);

        $reserva = Reserva::findOrFail($id_reserva);
        $reserva->update($request->all());

        return redirect()->route('reservas.index')->with('success', 'Reserva actualizada exitosamente.');
    }

    // Eliminar una reserva
    public function destroy($id_reserva)
    {
        $reserva = Reserva::findOrFail($id_reserva);
        // Marcar el espacio relacionado como disponible
        if ($reserva->id_espacio) {
            $espacio = Espacio::where('id_espacio', $reserva->id_espacio)->first();
            if ($espacio) {
                $espacio->estado = 'disponible';
                $espacio->save();
            }
        }

        $reserva->delete();

        return redirect()->route('reservas.index')->with('success', 'Reserva eliminada exitosamente.');
    }
    public function getEspaciosDisponibles(Request $request)
{
    $request->validate([
        'universidad' => 'required|exists:universidades,id_universidad',
        'facultad' => 'required|exists:facultades,id_facultad'
    ]);

    $espacios = Espacio::with('piso')
        ->whereHas('piso.facultad', function($q) use ($request) {
            $q->where('id_facultad', $request->facultad)
              ->where('id_universidad', $request->universidad);
        })
        ->where('estado', 'disponible')
        ->get()
        ->map(function($espacio) {
            return [
                'id_espacio' => $espacio->id_espacio,
                'tipo_espacio' => $espacio->tipo_espacio,
                'puestos_disponibles' => $espacio->puestos_disponibles,
                'piso_numero' => $espacio->piso->numero_piso
            ];
        });

    return response()->json($espacios);
}

    /**
     * Verifica y actualiza los estados de espacios basándose en reservas expiradas
     * Este método se puede llamar antes de mostrar espacios disponibles
     */
    private function verificarEstadosEspacios()
    {
        try {
            $fechaActual = Carbon::now()->format('Y-m-d');
            
            // Finalizar reservas del día anterior que aún estén activas
            $reservasExpiradas = Reserva::where('estado', 'activa')
                ->where('fecha_reserva', '<', $fechaActual)
                ->get();
            
            foreach ($reservasExpiradas as $reserva) {
                // Finalizar la reserva
                $reserva->update(['estado' => 'finalizada']);
                
                // Liberar el espacio si no hay otras reservas activas para hoy
                if ($reserva->id_espacio) {
                    $reservasActivasHoy = Reserva::where('id_espacio', $reserva->id_espacio)
                        ->where('estado', 'activa')
                        ->where('fecha_reserva', $fechaActual)
                        ->count();
                    
                    if ($reservasActivasHoy == 0) {
                        Espacio::where('id_espacio', $reserva->id_espacio)
                            ->update(['estado' => 'disponible']);
                    }
                }
            }
            
            return true;
        } catch (\Exception $e) {
            \Log::error('Error al verificar estados de espacios: ' . $e->getMessage());
            return false;
        }
    }
}