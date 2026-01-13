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
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

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
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users',
                'run' => 'required|integer|digits_between:7,8|unique:users',
                'celular' => 'nullable|string|regex:/^9\d{8}$/',
                'password' => 'required|string|min:8',
                'roles' => 'required|array',
                'roles.*' => 'exists:roles,id',
                'permissions' => 'nullable|array',
                'permissions.*' => 'exists:permissions,id',
                'year_of_entry' => 'nullable|integer|min:2010|max:' . date('Y'),
                'year_of_graduation' => 'nullable|integer|min:2010|max:' . (date('Y') + 5),
                'career' => 'nullable|string|max:255',
                'current_semester' => 'nullable|integer|min:1|max:20',
                'is_active' => 'boolean'
            ]);

            // Verificar si el RUN ya existe
            if (User::where('run', $validated['run'])->exists()) {
                if ($request->ajax()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'El RUN ya está registrado en el sistema.'
                    ], 422);
                }
                return back()->withErrors(['run' => 'El RUN ya está registrado en el sistema.']);
            }

            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'run' => $validated['run'],
                'celular' => $validated['celular'] ?? null,
                'password' => Hash::make($validated['password']),
                'year_of_entry' => $validated['year_of_entry'] ?? null,
                'year_of_graduation' => $validated['year_of_graduation'] ?? null,
                'career' => $validated['career'] ?? null,
                'current_semester' => $validated['current_semester'] ?? null,
                'is_active' => $validated['is_active'] ?? true
            ]);

            $user->roles()->sync($validated['roles']);
            if (!empty($validated['permissions'])) {
                $user->permissions()->sync($validated['permissions']);
            }

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Usuario creado exitosamente.'
                ]);
            }

            return redirect()->route('users.index')
                ->with('success', 'Usuario creado exitosamente.');
        } catch (ValidationException $e) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'errors' => $e->errors(),
                    'message' => 'Error de validación.'
                ], 422);
            }
            throw $e;
        } catch (\Exception $e) {
            \Log::error('Error al crear usuario: ' . $e->getMessage());
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error al crear el usuario: ' . $e->getMessage()
                ], 500);
            }
            return back()->withErrors(['error' => 'Error al crear el usuario.']);
        }
    }

    public function show(string $id)
    {
    }
    public function edit($run)
    {
        try {
            $user = User::where('run', $run)->firstOrFail();
            $years = range(2010, date('Y'));
            $roles = Role::all();
            $permissions = Permission::all();
            return view('layouts.user.user_update', compact('user', 'years', 'roles', 'permissions'));
        } catch (\Exception $e) {
            Log::error('Error al cargar la vista de edición de usuario: ' . $e->getMessage());
            return redirect()->route('users.index')->withErrors(['error' => 'Hubo un problema al cargar los datos del usuario.']);
        }
    }

    public function update(Request $request, User $user)
    {
        try {
            // Convertir el RUN a entero antes de la validación
            if ($request->has('run')) {
                $request->merge(['run' => (int) $request->run]);
            }

            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
                'run' => 'required|integer|digits_between:7,8|unique:users,run,' . $user->id,
                'celular' => 'nullable|string|regex:/^9\d{8}$/',
                'password' => 'nullable|string|min:8',
                'roles' => 'required|array',
                'roles.*' => 'exists:roles,id',
                'permissions' => 'nullable|array',
                'permissions.*' => 'exists:permissions,id',
                'year_of_entry' => 'nullable|integer|min:2010|max:' . date('Y'),
                'year_of_graduation' => 'nullable|integer|min:2010|max:' . (date('Y') + 5),
                'career' => 'nullable|string|max:255',
                'current_semester' => 'nullable|integer|min:1|max:20',
                'is_active' => 'boolean'
            ]);

            $user->update([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'run' => $validated['run'],
                'celular' => $validated['celular'] ?? null,
                'year_of_entry' => $validated['year_of_entry'] ?? null,
                'year_of_graduation' => $validated['year_of_graduation'] ?? null,
                'career' => $validated['career'] ?? null,
                'current_semester' => $validated['current_semester'] ?? null,
                'is_active' => $validated['is_active'] ?? true
            ]);

            if (!empty($validated['password'])) {
                $user->update(['password' => Hash::make($validated['password'])]);
            }

            $user->roles()->sync($validated['roles']);
            if (!empty($validated['permissions'])) {
                $user->permissions()->sync($validated['permissions']);
            }

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Usuario actualizado exitosamente.'
                ]);
            }

            return redirect()->route('users.index')
                ->with('success', 'Usuario actualizado exitosamente.');
        } catch (ValidationException $e) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'errors' => $e->errors(),
                    'message' => 'Error de validación.'
                ], 422);
            }
            throw $e;
        } catch (\Exception $e) {
            \Log::error('Error al actualizar usuario: ' . $e->getMessage());
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error al actualizar el usuario: ' . $e->getMessage()
                ], 500);
            }
            return back()->withErrors(['error' => 'Error al actualizar el usuario.']);
        }
    }

    public function destroy(User $user)
    {
        try {
            $user->delete();
            return redirect()->route('users.index')
                ->with('success', 'Usuario eliminado exitosamente.');
        } catch (\Exception $e) {
            \Log::error('Error al eliminar usuario: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Error al eliminar el usuario.']);
        }
    }

    // Autocomplete de usuarios por email o nombre
    public function autocomplete(Request $request)
    {
        $q = $request->get('q', '');
        if (!$q) return response()->json([]);

        // Buscar en usuarios (tabla users)
        $usersQuery = User::where('email', 'like', "%{$q}%")
            ->orWhere('name', 'like', "%{$q}%")
            ->limit(10);
        $users = $usersQuery->get(['run', 'name', 'email'])
            ->map(function($u) {
                return ['id' => $u->run, 'nombre' => $u->name, 'email' => $u->email, 'fuente' => 'usuario'];
            })->toArray();
        Log::info('Autocomplete users count: ' . count($users));
        if (count($users) > 0) {
            Log::info('Autocomplete users sample: ' . json_encode(array_slice($users, 0, 3)));
        }

        // Buscar en profesores
        $profesores = [];
        try {
            if (class_exists('\App\\Models\\Profesor')) {
                // El modelo Profesor usa las columnas `name` y `email`
                $profQuery = \App\Models\Profesor::where('email', 'like', "%{$q}%")
                    ->orWhere('name', 'like', "%{$q}%")
                    ->limit(10);
                $profesores = $profQuery->get(['run_profesor as run', 'name as nombre', 'email'])
                    ->map(function($p) {
                        return ['id' => $p->run, 'nombre' => $p->nombre, 'email' => $p->email, 'fuente' => 'profesor'];
                    })->toArray();
                Log::info('Autocomplete profesores count: ' . count($profesores));
                if (count($profesores) > 0) {
                    Log::info('Autocomplete profesores sample: ' . json_encode(array_slice($profesores, 0, 3)));
                }
            }
        } catch (\Throwable $e) {
            // No bloquear si modelo no existe o falla la consulta
            Log::warning('Autocomplete profesores error: ' . $e->getMessage());
        }

        // Buscar en solicitantes
        $solicitantes = [];
        try {
            if (class_exists('\App\\Models\\Solicitante')) {
                // El modelo Solicitante usa `nombre` y `correo` (mapeamos correo->email)
                $solQuery = \App\Models\Solicitante::on('tenant')->where('correo', 'like', "%{$q}%")
                    ->orWhere('nombre', 'like', "%{$q}%")
                    ->limit(10);
                $solicitantes = $solQuery->get(['run_solicitante as run', 'nombre', 'correo'])
                    ->map(function($s) {
                        return ['id' => $s->run, 'nombre' => $s->nombre, 'email' => $s->correo, 'fuente' => 'solicitante'];
                    })->toArray();
                Log::info('Autocomplete solicitantes count: ' . count($solicitantes));
                if (count($solicitantes) > 0) {
                    Log::info('Autocomplete solicitantes sample: ' . json_encode(array_slice($solicitantes, 0, 3)));
                }
            }
        } catch (\Throwable $e) {
            Log::warning('Autocomplete solicitantes error: ' . $e->getMessage());
        }

        // Combinar resultados y evitar duplicados por run (prioridad: usuario, profesor, solicitante)
        $combined = [];
        $seen = [];
        foreach (array_merge($users, $profesores, $solicitantes) as $row) {
            if (empty($row['id'])) continue;
            if (in_array($row['id'], $seen)) continue;
            $seen[] = $row['id'];
            $combined[] = $row;
            if (count($combined) >= 10) break;
        }

        return response()->json($combined);
    }
}
