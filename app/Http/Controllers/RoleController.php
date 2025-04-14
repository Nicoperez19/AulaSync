<?php

namespace App\Http\Controllers;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

use Illuminate\Http\Request;

class RoleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $roles = Role::all();
        $permissions = Permission::all();

        return view('layouts.rol.rol_index', compact('roles', 'permissions'));

    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'name' => 'required|string|max:255|unique:roles,name',
                'permissions' => 'required|array',
                'permissions.*' => 'exists:permissions,id',
            ]);

            $role = Role::create(['name' => $validatedData['name']]);
            $role->permissions()->sync($validatedData['permissions']);

            // Redirige con éxito
            return redirect()->route('roles.index')->with('success', 'Rol creado y permisos asignados correctamente.');
        } catch (\Exception $e) {
            // Redirige con error
            return redirect()->route('roles.index')->with('error', 'Hubo un problema al crear el rol: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $role = Role::findOrFail($id);
        $permissions = Permission::all();
        $rolePermissions = $role->permissions->pluck('id')->toArray();

        return view('layouts.rol.rol_update', compact('role', 'permissions', 'rolePermissions'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        try {
            $request->validate([
                'name_rol' => 'required|string|max:255|unique:roles,name,' . $id,
                'permissions' => 'array',
            ]);

            $role = Role::findOrFail($id);
            $role->name = $request->name_rol;
            $role->save();

            // Sincronizamos los permisos seleccionados
            $permissions = Permission::whereIn('id', $request->permissions ?? [])->pluck('name')->toArray();
            $role->syncPermissions($permissions);

            // Redirige con éxito
            return redirect()->route('roles.index')->with('success', 'Rol actualizado correctamente.');
        } catch (\Exception $e) {
            // Redirige con error
            return redirect()->route('roles.index')->with('error', 'Error al actualizar el rol: ' . $e->getMessage());
        }
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $role = Role::findOrFail($id);
            $role->delete();

            // Redirige con éxito
            return redirect()->route('roles.index')->with('success', 'Rol eliminado exitosamente.');
        } catch (\Exception $e) {
            // Redirige con error
            return redirect()->route('roles.index')->with('error', 'Error al borrar el rol: ' . $e->getMessage());
        }
    }
}
