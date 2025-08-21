<?php

namespace App\Http\Controllers;

use App\Models\Reserva;
use Illuminate\Http\Request;
use App\Models\Universidad;
use App\Models\Espacio;

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
            'id' => 'required|exists:users,id',
        ]);

        $reserva = Reserva::create([
            'id_reserva' => Reserva::generarIdUnico(),
            'hora' => $request->hora,
            'fecha_reserva' => $request->fecha_reserva,
            'id_espacio' => $request->id_espacio,
            'id' => $request->id,
        ]);

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
}