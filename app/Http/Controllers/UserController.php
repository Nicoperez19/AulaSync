<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use App\Providers\RouteServiceProvider;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Validation\Rules;

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
                'roles' => 'required|array', // Validación de roles
            ]);

            $user = User::create([
                'run' => $validatedData['run'],
                'name' => $validatedData['name'],
                'email' => $validatedData['email'],
                'password' => Hash::make($validatedData['password']),
            ]);

            // Asignar roles y permisos
            $user->syncRoles($validatedData['roles']); // Sincronizar roles

            event(new Registered($user));

            return redirect()->route('users.index')->with('success', 'Usuario creado exitosamente.');

        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => 'Hubo un problema al crear el usuario. Por favor, intente nuevamente.'])->withInput();
        }
    }

    public function show(string $id)
    {
    }

    public function edit($id)
    {
        $user = User::findOrFail($id);
        $roles = Role::all();
        $permissions = Permission::all();
        return view('layouts.user.user_update', compact('user', 'roles', 'permissions'));
    }

    public function update(Request $request, $id, Role $role)
    {
        try {
            $user = User::findOrFail($id);

            // Validación de datos
            $validatedData = $request->validate([
                'run' => 'required|string|regex:/^\d{7,8}-[0-9K]$/|unique:users,run,' . $user->id,
                'name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users,email,' . $user->id],
                'password' => ['nullable', Rules\Password::defaults()],
                'roles' => 'required|array',
                'permissions' => 'nullable|array', 
            ]);

            // Actualizar datos básicos del usuario
            $user->update([
                'run' => $validatedData['run'],
                'name' => $validatedData['name'],
                'email' => $validatedData['email'],
            ]);

            // Si se proporciona una nueva contraseña, la actualiza
            if ($request->filled('password')) {
                $user->update([
                    'password' => Hash::make($validatedData['password']),
                ]);
            }

            $user->roles()->sync($request->roles);
            $user->permissions()->sync($request->permissions);  

            return redirect()->route('users.index')->with('success', 'Usuario actualizado exitosamente.');

        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => 'Hubo un problema al actualizar el usuario.'])->withInput();
        }
    }


    public function destroy($id)
    {
        try {
            $user = User::findOrFail($id);
            $user->delete();
            return redirect()->route('users.index')->with('success', 'Usuario eliminado exitosamente.');

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['success' => false, 'message' => 'Usuario no encontrado.'], 404);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error al borrar el usuario: ' . $e->getMessage()], 500);
        }
    }
}
