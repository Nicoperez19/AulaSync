<?php

namespace App\Http\Controllers;

use App\Models\Reserva;
use Illuminate\Http\Request;

class ReservasController extends Controller
{
    // Mostrar todas las reservas
    public function index()
    {
        $reservas = Reserva::paginate(10);
        return view('layouts.reservations.reservations_index', compact('reservas'));
    }

    // Mostrar el formulario para crear una nueva reserva
    public function create()
    {
        return view('reservas.create');
    }

    // Almacenar una nueva reserva en la base de datos
    public function store(Request $request)
    {
        $request->validate([
            'id_reserva' => 'required|unique:reservas,id_reserva|max:20',
            'hora' => 'required',
            'fecha_reserva' => 'required|date',
            'id_espacio' => 'required|exists:espacios,id_espacio',
            'id' => 'required|exists:users,id',
        ]);

        Reserva::create($request->all());

        return redirect()->route('reservas.index')->with('success', 'Reserva creada exitosamente.');
    }

    // Mostrar el formulario para editar una reserva existente
    public function edit($id_reserva)
    {
        $reserva = Reserva::findOrFail($id_reserva);
        return view('reservas.edit', compact('reserva'));
    }

    // Actualizar una reserva existente en la base de datos
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