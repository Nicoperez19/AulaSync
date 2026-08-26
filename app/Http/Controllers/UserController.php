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
use App\Models\Sede;
use App\Mail\BienvenidaUsuarioMail;
use Illuminate\Support\Facades\Mail;

class UserController extends Controller
{
    public function index()
    {
        $years = range(2010, date('Y'));
        $sedes = Sede::all();
        return view('layouts.user.user_index', compact('years', 'sedes'));
    }

    public function create()
    {
        $years = range(2010, date('Y'));
        $roles = Role::all();
        $permissions = Permission::all();
        $sedes = Sede::all();
        return view('layouts.user.user_update', compact('years', 'roles', 'permissions', 'sedes'));
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users',
                'run' => 'required|integer|digits_between:7,8|unique:users',
                'celular' => 'nullable|string|max:15',
                'password' => 'nullable|string|min:6',
                'roles' => 'nullable|array',
                'roles.*' => 'exists:roles,id',
                'permissions' => 'nullable|array',
                'permissions.*' => 'exists:permissions,id',
                'year_of_entry' => 'nullable|integer|min:2010|max:' . date('Y'),
                'year_of_graduation' => 'nullable|integer|min:2010|max:' . (date('Y') + 5),
                'career' => 'nullable|string|max:255',
                'current_semester' => 'nullable|integer|min:1|max:20',
                'is_active' => 'boolean',
                'is_superuser' => 'boolean',
                'id_sede' => 'nullable|exists:sedes,id_sede'
            ], [
                'run.required' => 'El RUN es obligatorio.',
                'run.unique' => 'El RUN ya está registrado en el sistema.',
                'run.digits_between' => 'El RUN debe tener entre 7 y 8 dígitos (sin dígito verificador).',
                'email.required' => 'El correo es obligatorio.',
                'email.unique' => 'El correo electrónico ya está registrado.',
                'celular.max' => 'El teléfono/celular no debe superar los 15 dígitos.',
                'password.min' => 'La contraseña debe tener al menos 6 caracteres.',
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
                'password' => Hash::make((string)$validated['run']),
                'year_of_entry' => $validated['year_of_entry'] ?? null,
                'year_of_graduation' => $validated['year_of_graduation'] ?? null,
                'career' => $validated['career'] ?? null,
                'current_semester' => $validated['current_semester'] ?? null,
                'is_active' => $validated['is_active'] ?? true,
                'is_superuser' => $validated['is_superuser'] ?? false,
                'id_sede' => $validated['id_sede'] ?? null
            ]);



            if (!empty($validated['roles'])) { $user->roles()->sync($validated['roles']); } else { $usuarioRole = Role::where('name', 'Usuario')->first(); if ($usuarioRole) { $user->assignRole($usuarioRole); } }
            if (!empty($validated['permissions'])) {
                $user->permissions()->sync($validated['permissions']);
            }

            // Enviar correo de bienvenida al usuario registrado
            try {
                $user->load('roles');
                $roleName = $user->roles->pluck('name')->first() ?? 'Usuario';
                $plainPassword = (string)$validated['run'];
                Mail::to($user->email)->send(new BienvenidaUsuarioMail($user, $plainPassword, $roleName, route('login')));
            } catch (\Exception $e) {
                Log::error('Error al enviar correo de bienvenida al usuario ' . $user->email . ': ' . $e->getMessage());
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
            $sedes = Sede::all();
            return view('layouts.user.user_update', compact('user', 'years', 'roles', 'permissions', 'sedes'));
        } catch (\Exception $e) {
            Log::error('Error al cargar la vista de edición de usuario: ' . $e->getMessage());
            return redirect()->route('users.index')->withErrors(['error' => 'Hubo un problema al cargar los datos del usuario.']);
        }
    }

    public function update(Request $request, $run)
    {
        try {
            // Buscar el usuario por RUN
            $user = User::where('run', $run)->firstOrFail();
            
            // Validación condicional: wizard_password es obligatorio si se marca como superusuario
            $isMarkingAsSuperuser = $request->has('is_superuser') && $request->input('is_superuser') && !$user->is_superuser;
            
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'email' => ['required', 'string', 'email', 'max:255', \Illuminate\Validation\Rule::unique('users', 'email')->ignore($user->run, 'run')],
                'run' => ['required', 'integer', 'digits_between:7,8', \Illuminate\Validation\Rule::unique('users', 'run')->ignore($user->run, 'run')],
                'celular' => 'nullable|string|max:15',
                'password' => 'nullable|string|min:8',
                'wizard_password' => $isMarkingAsSuperuser ? 'required|string' : 'nullable|string',
                'roles' => 'nullable|array',
                'roles.*' => 'exists:roles,id',
                'permissions' => 'nullable|array',
                'permissions.*' => 'exists:permissions,id',
                'direccion' => 'nullable|string|max:255',
                'fecha_nacimiento' => 'nullable|date',
                'is_superuser' => 'boolean',
                'id_sede' => 'nullable|exists:sedes,id_sede'
            ], [
                'email.unique' => 'Este correo electrónico ya está registrado en el sistema.',
                'run.unique' => 'Este RUN ya está registrado en el sistema.',
                'run.required' => 'El RUN es obligatorio.',
                'run.integer' => 'El RUN debe ser un número válido.',
                'run.digits_between' => 'El RUN debe tener entre 7 y 8 dígitos.',
                'email.required' => 'El correo electrónico es obligatorio.',
                'email.email' => 'Debe ingresar un correo electrónico válido.',
                'name.required' => 'El nombre es obligatorio.',
                'password.min' => 'La contraseña debe tener al menos 8 caracteres.',
                'roles.required' => 'Debe seleccionar al menos un rol.',
                'celular.regex' => 'El celular debe comenzar con 9 y tener 9 dígitos.',
            ]);

            // Validar contraseña del wizard si fue proporcionada
            if (!empty($validated['wizard_password'])) {
                $expectedPassword = config('app.tenant_init_password', env('TENANT_INIT_PASSWORD'));
                $providedPassword = trim($validated['wizard_password']);
                

                
                if ($providedPassword !== $expectedPassword) {
                    Log::warning('Intento de edición de usuario con contraseña de wizard incorrecta', [
                        'run' => $user->run,
                        'ip' => $request->ip(),
                    ]);

                    if ($request->ajax()) {
                        return response()->json([
                            'success' => false,
                            'message' => 'Contraseña admin incorrecta.'
                        ], 422);
                    }
                    return back()->withErrors(['wizard_password' => 'Contraseña admin incorrecta.'])->withInput();
                }
            } elseif ($isMarkingAsSuperuser) {
                // Si está intentando marcar como superusuario pero no proporcionó contraseña
                if ($request->ajax()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'La contraseña admin es obligatoria para otorgar permisos de superusuario.'
                    ], 422);
                }
                return back()->withErrors(['wizard_password' => 'La contraseña admin es obligatoria para otorgar permisos de superusuario.'])->withInput();
            }

            $user->update([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'run' => $validated['run'],
                'celular' => $validated['celular'] ?? null,
                'direccion' => $validated['direccion'] ?? null,
                'fecha_nacimiento' => $validated['fecha_nacimiento'] ?? null,
                'is_superuser' => $validated['is_superuser'] ?? false,
                'id_sede' => $validated['id_sede'] ?? null
            ]);

            if (!empty($validated['password'])) {
                $user->update(['password' => Hash::make(!empty($validated['password']) ? $validated['password'] : (string)$validated['run'])]);
            }

            if (!empty($validated['roles'])) { $user->roles()->sync($validated['roles']); } else { $usuarioRole = Role::where('name', 'Usuario')->first(); if ($usuarioRole) { $user->assignRole($usuarioRole); } }
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
