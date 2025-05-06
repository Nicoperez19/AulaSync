<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Validation\Rules;
use Illuminate\Support\Facades\Log;

class UserController extends Controller
{
    public function index()
    {
        $users = User::all();
        $years = range(2010, date('Y'));
        return view('layouts.user.user_index', compact('users', 'years'));
    }

    public function create()
    {
        $years = range(2010, date('Y'));
        return view('layouts.user.user_update', compact('years'));
    }

    public function store(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'run' => ['required', 'integer', 'regex:/^\d{7,8}$/'],
                'name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users,email'],
                'celular' => 'nullable|regex:/^9\d{8}$/',
                'direccion' => 'nullable|string|max:255',
                'fecha_nacimiento' => 'nullable|date',
                'anio_ingreso' => 'nullable|integer|min:1900|max:' . date('Y'),
            ]);

            $user = User::create([
                'run' => $validatedData['run'],
                'name' => $validatedData['name'],
                'email' => $validatedData['email'],
                'password' => bcrypt($validatedData['run']),
                'celular' => $validatedData['celular'] ?? null,
                'direccion' => $validatedData['direccion'] ?? null,
                'fecha_nacimiento' => $validatedData['fecha_nacimiento'] ?? null,
                'anio_ingreso' => $validatedData['anio_ingreso'] ?? null,
            ]);

            $role = Role::findByName('Usuario');
            $user->assignRole($role);

            event(new Registered($user));

            return redirect()->route('users.index')->with('success', 'Usuario creado exitosamente.');

        } catch (\Exception $e) {
            Log::error('Error al crear usuario: ' . $e->getMessage());
            return redirect()->back()->withErrors(['error' => 'Hubo un problema al crear el usuario.'])->withInput();
        }
    }

    public function show(string $id)
    {
    }
    public function edit($run)
    {
        try {
            $user = User::where('run', $run)->firstOrFail();
            $years = range(1900, date('Y'));
            $roles = Role::all();
            $permissions = Permission::all();
            return view('layouts.user.user_update', compact('user', 'roles', 'permissions'));
        } catch (\Exception $e) {
            Log::error('Error al cargar la vista de edición de usuario: ' . $e->getMessage());
            return redirect()->route('users.index')->withErrors(['error' => 'Hubo un problema al cargar los datos del usuario.']);
        }
    }


    public function update(Request $request, $run)
    {
        try {
            $user = User::where('run', $run)->firstOrFail();

            $rules = [
                'name' => 'nullable|string|max:255',
                'celular' => 'nullable|regex:/^9\d{8}$/',
                'direccion' => 'nullable|string|max:255',
                'fecha_nacimiento' => 'nullable|date',
                'anio_ingreso' => 'nullable|integer|min:1900|max:' . date('Y'),
                'password' => 'nullable|string|min:8',
                'roles' => 'nullable|array|exists:roles,id',
                'permissions' => 'nullable|array|exists:permissions,id',
            ];

            if ($request->run && $request->run != $user->run) {
                $rules['run'] = 'required|string|regex:/^\d{7,8}$/|unique:users,run';
            }

            if ($request->email && $request->email != $user->email) {
                $rules['email'] = 'required|string|email|max:255|unique:users,email';
            }

            $validatedData = $request->validate($rules);

            $roles = Role::whereIn('id', $validatedData['roles'])->pluck('name')->toArray();
            $permissions = Permission::whereIn('id', $validatedData['permissions'])->pluck('name')->toArray();

            $user->fill([
                'run' => $validatedData['run'] ?? $user->run,
                'name' => $validatedData['name'] ?? $user->name,
                'email' => $validatedData['email'] ?? $user->email,
                'celular' => $validatedData['celular'] ?? $user->celular,
                'direccion' => $validatedData['direccion'] ?? $user->direccion,
                'fecha_nacimiento' => $validatedData['fecha_nacimiento'] ?? $user->fecha_nacimiento,
                'anio_ingreso' => $validatedData['anio_ingreso'] ?? $user->anio_ingreso,
            ]);

            if (!empty($validatedData['password'])) {
                $user->password = bcrypt($validatedData['password']);
            }

            $user->syncRoles($roles);
            $user->syncPermissions($permissions);

            $user->save();

            return redirect()->route('users.index')->with('success', 'Usuario actualizado exitosamente.');

        } catch (\Exception $e) {
            Log::error('Error al actualizar usuario: ' . $e->getMessage());
            return redirect()->back()->withErrors(['error' => 'Hubo un problema al actualizar el usuario.'])->withInput();
        }
    }

    public function destroy($run)
    {
        try {
            $user = User::findOrFail($run);
            $user->delete();
            return redirect()->route('users.index')->with('success', 'Usuario eliminado exitosamente.');

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            Log::error('Usuario no encontrado: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Usuario no encontrado.'], 404);
        } catch (\Exception $e) {
            Log::error('Error al borrar el usuario: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al borrar el usuario: ' . $e->getMessage()], 500);
        }
    }
}
