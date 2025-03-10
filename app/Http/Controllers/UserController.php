<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use App\Providers\RouteServiceProvider;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules;
use App\Http\Controllers\Exception;

class UserController extends Controller
{

    public function index()
    {
        $users = User::all();
        return view('layouts.user.user_index', compact('users'));
    }

    public function create()
    {
    }


    public function store(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'run' => 'required|string|unique:users|regex:/^\d{7,8}-[0-9K]$/',
                'name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:' . User::class],
                'password' => ['required', Rules\Password::defaults()],
            ]);

            $user = User::create([
                'run' => $validatedData['run'],
                'name' => $validatedData['name'],
                'email' => $validatedData['email'],
                'password' => Hash::make($validatedData['password']),
            ]);

            event(new Registered($user));

            return redirect()->route('users.index')->with('success', 'Usuario creado exitosamente.');

        } catch (\Exception $e) {
            // dd($e->getMessage()); // Agregar esto para depurar
            return redirect()->back()->withErrors(['error' => 'Hubo un problema al crear el usuario. Por favor, intente nuevamente.'])->withInput();
        }
    }


    public function show(string $id)
    {
        //
    }


    public function edit(string $id)
    {
        //
    }

    public function update(Request $request, string $id)
    {
        //
    }


    public function destroy(string $id)
    {
        try {
            $user = User::findOrFail($id); // Usar findOrFail para lanzar una excepción si no se encuentra el usuario
            $user->delete();
            return redirect()->route('users.index')->with('success', 'Usuario eliminado exitosamente.');

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['success' => false, 'message' => 'Usuario no encontrado.'], 404);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error al borrar el usuario: ' . $e->getMessage()], 500);
        }
    }
}
