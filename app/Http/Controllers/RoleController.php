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
        return view('layouts.rol.rol_index', compact('roles'));
      
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
        //
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
        $request->validate([
            'name_rol' => 'required|string|max:255|unique:roles,name,' . $id,
            'permissions' => 'array',
        ]);
    
        $role = Role::findOrFail($id);
        $role->name = $request->name_rol;
        $role->save();
    
        // Convertir IDs a nombres de permisos antes de asignarlos
        $permissions = Permission::whereIn('id', $request->permissions ?? [])->pluck('name')->toArray();
        
        $role->syncPermissions($permissions);
    
        return redirect()->route('roles.index')->with('success', 'Rol actualizado correctamente.');
    }
    
    
    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        {
            try {
                $roles = Role::findOrFail($id);
                $roles->delete();
                return redirect()->route('roles.index')->with('success', 'Rol eliminado exitosamente.');
    
            } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
                return response()->json(['success' => false, 'message' => 'Rol no encontrado.'], 404);
            } catch (\Exception $e) {
                return response()->json(['success' => false, 'message' => 'Error al borrar el rol: ' . $e->getMessage()], 500);
            }
        }
    }
}
