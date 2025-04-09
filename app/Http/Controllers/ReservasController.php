<?php

namespace App\Http\Controllers;

use App\Models\Reserva;
use Illuminate\Http\Request;

class ReservasController extends Controller
{
    // Mostrar todas las reservas
    public function index()
    {
        $reservas = Reserva::with('user')->paginate(10);
        return view('layouts.reservations.reservations_index', compact('reservas'));
    }

    public function create()
    {
        return view('reservas.create');
    }

    // Almacenar una nueva reserva en la base de datos
    public function store(Request $request)
    {
        $request->validate([
            'hora' => 'required',
            'fecha_reserva' => 'required|date',
            'id_espacio' => 'required|exists:espacios,id_espacio',
            'id' => 'required|exists:users,id',
        ]);

        $lastReserva = Reserva::orderBy('id_reserva', 'desc')->first();

        if ($lastReserva) {
            $lastIdNumber = intval(substr($lastReserva->id_reserva, 1)); // Quita la R y convierte a número
            $newIdNumber = str_pad($lastIdNumber + 1, 3, '0', STR_PAD_LEFT);
        } else {
            $newIdNumber = '001';
        }

        $newId = 'R' . $newIdNumber;

        Reserva::create([
            'id_reserva' => $newId,
            'hora' => $request->hora,
            'fecha_reserva' => $request->fecha_reserva,
            'id_espacio' => $request->id_espacio,
            'id' => $request->id,
        ]);

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
        $reserva->delete();

        return redirect()->route('reservas.index')->with('success', 'Reserva eliminada exitosamente.');
    }
}