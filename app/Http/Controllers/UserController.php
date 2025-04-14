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
                'roles' => 'required|array',
            ]);

            $user = User::create([
                'run' => $validatedData['run'],
                'name' => $validatedData['name'],
                'email' => $validatedData['email'],
                'password' => bcrypt($validatedData['password']),

            ]);

            $role = Role::findByName('Usuario');  // Asegúrate de que este rol exista en la base de datos
            $user->assignRole($role);

            event(new Registered($user));

            return redirect()->route('users.indexx')->with('success', 'Usuario creado exitosamente.');

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

    public function update(Request $request, $id)
    {
        try {

            $user = User::findOrFail($id);

            $rules = [
                'name' => 'nullable|string|max:255',
                'celular' => 'nullable|string|max:20',
                'direccion' => 'nullable|string|max:255',
                'fecha_nacimiento' => 'nullable|date',
                'anio_ingreso' => 'nullable|integer|min:1900|max:' . date('Y'),
                'password' => 'nullable|string|min:8',
                'roles' => 'nullable|array|exists:roles,id',
                'permissions' => 'nullable|array|exists:permissions,id',
            ];

            if ($request->run && $request->run != $user->run) {
                $rules['run'] = 'required|string|regex:/^\d{7,8}-[0-9K]$/|unique:users,run';
            }

            if ($request->email && $request->email != $user->email) {
                $rules['email'] = 'required|string|email|max:255|unique:users,email';
            }

            $validatedData = $request->validate($rules);


            $roles = Role::whereIn('id', $validatedData['roles'])->pluck('name')->toArray();

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
            $user->syncPermissions($validatedData['permissions'] ?? []);

            $user->save();


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
