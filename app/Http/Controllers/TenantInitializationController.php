<?php

namespace App\Http\Controllers;

use App\Models\Tenant;
use App\Models\Sede;
use App\Models\User;
use App\Models\Configuracion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Spatie\Permission\Models\Role;

class TenantInitializationController extends Controller
{
    /**
     * Mostrar el wizard de inicialización
     */
    public function index()
    {
        $tenant = Tenant::current();
        
        if (!$tenant) {
            return redirect()->route('login');
        }

        if ($tenant->is_initialized) {
            return redirect()->route('login');
        }

        $step = $tenant->initialization_step ?? 1;
        $sede = $tenant->sede;

        return view('tenant.initialization.wizard', compact('tenant', 'sede', 'step'));
    }

    /**
     * Procesar el paso 1: Crear cuenta de administrador
     */
    public function storeAdmin(Request $request)
    {
        $tenant = Tenant::current();
        
        if (!$tenant) {
            return redirect()->route('sedes.selection');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'run' => 'required|string|max:20|unique:users,run',
            'email' => 'required|email|max:255|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
        ], [
            'name.required' => 'El nombre es requerido.',
            'run.required' => 'El RUN es requerido.',
            'run.unique' => 'Este RUN ya está registrado.',
            'email.required' => 'El email es requerido.',
            'email.email' => 'Ingrese un email válido.',
            'email.unique' => 'Este email ya está registrado.',
            'password.required' => 'La contraseña es requerida.',
            'password.min' => 'La contraseña debe tener al menos 8 caracteres.',
            'password.confirmed' => 'Las contraseñas no coinciden.',
        ]);

        // Crear el usuario administrador
        $user = User::create([
            'name' => $validated['name'],
            'run' => $validated['run'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        // Asignar rol de administrador
        $adminRole = Role::findByName('Administrador');
        if ($adminRole) {
            $user->assignRole($adminRole);
        }

        // Avanzar al siguiente paso
        $tenant->setInitializationStep(2);

        return redirect()->route('tenant.initialization.index')
            ->with('success', 'Cuenta de administrador creada exitosamente.');
    }

    /**
     * Procesar el paso 2: Subir logo de la sede
     */
    public function storeLogo(Request $request)
    {
        $tenant = Tenant::current();
        
        if (!$tenant) {
            return redirect()->route('sedes.selection');
        }

        $validated = $request->validate([
            'logo' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ], [
            'logo.required' => 'El logo es requerido.',
            'logo.image' => 'El archivo debe ser una imagen.',
            'logo.mimes' => 'El logo debe ser de tipo: jpeg, png, jpg, gif o svg.',
            'logo.max' => 'El logo no debe superar los 2MB.',
        ]);

        $sede = $tenant->sede;
        
        if ($request->hasFile('logo')) {
            $file = $request->file('logo');
            $filename = $sede->id_sede . '_logo_' . time() . '.' . $file->getClientOriginalExtension();
            
            // Guardar el logo
            $file->storeAs('public/sedes/logos', $filename);
            
            // Actualizar la sede
            $sede->update(['logo' => $filename]);

            // También guardar en configuración para compatibilidad
            Configuracion::set(
                "logo_institucional_{$sede->id_sede}", 
                $filename, 
                "Logo institucional de la sede {$sede->nombre_sede}"
            );
        }

        // Avanzar al siguiente paso
        $tenant->setInitializationStep(3);

        return redirect()->route('tenant.initialization.index')
            ->with('success', 'Logo subido exitosamente.');
    }

    /**
     * Procesar el paso 3: Confirmar información de la sede
     */
    public function confirmSedeInfo(Request $request)
    {
        $tenant = Tenant::current();
        
        if (!$tenant) {
            return redirect()->route('sedes.selection');
        }

        $validated = $request->validate([
            'nombre_sede' => 'required|string|max:100',
            'descripcion' => 'nullable|string|max:500',
            'direccion' => 'nullable|string|max:255',
            'telefono' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:100',
        ]);

        $sede = $tenant->sede;
        $sede->update($validated);

        // Avanzar al siguiente paso
        $tenant->setInitializationStep(4);

        return redirect()->route('tenant.initialization.index')
            ->with('success', 'Información de la sede confirmada.');
    }

    /**
     * Procesar el paso 4: Carga masiva (opcional, solo avanzar)
     */
    public function skipBulkLoad(Request $request)
    {
        $tenant = Tenant::current();
        
        if (!$tenant) {
            return redirect()->route('sedes.selection');
        }

        // Avanzar al siguiente paso
        $tenant->setInitializationStep(5);

        return redirect()->route('tenant.initialization.index')
            ->with('info', 'La carga masiva puede realizarse posteriormente desde el Dashboard.');
    }

    /**
     * Procesar el paso 5: Períodos académicos (opcional, solo avanzar)
     */
    public function skipAcademicPeriods(Request $request)
    {
        $tenant = Tenant::current();
        
        if (!$tenant) {
            return redirect()->route('sedes.selection');
        }

        // Guardar en configuración que los períodos no están definidos
        Configuracion::set(
            "periodos_academicos_definidos_{$tenant->sede_id}",
            'false',
            "Indica si los períodos académicos están definidos para la sede"
        );

        Configuracion::set(
            "periodos_ultima_notificacion_{$tenant->sede_id}",
            now()->toDateString(),
            "Última fecha de notificación sobre períodos académicos"
        );

        // Avanzar al siguiente paso
        $tenant->setInitializationStep(6);

        return redirect()->route('tenant.initialization.index')
            ->with('info', 'Los períodos académicos pueden configurarse posteriormente.');
    }

    /**
     * Procesar el paso 6: Configurar plano digital (opcional, solo avanzar)
     */
    public function skipDigitalPlan(Request $request)
    {
        $tenant = Tenant::current();
        
        if (!$tenant) {
            return redirect()->route('sedes.selection');
        }

        // Avanzar al paso final
        $tenant->setInitializationStep(7);

        return redirect()->route('tenant.initialization.index')
            ->with('info', 'El plano digital puede configurarse posteriormente desde el menú de Mapas.');
    }

    /**
     * Finalizar la inicialización
     */
    public function complete(Request $request)
    {
        $tenant = Tenant::current();
        
        if (!$tenant) {
            return redirect()->route('sedes.selection');
        }

        // Marcar el tenant como inicializado
        $tenant->markAsInitialized();

        return redirect()->route('tenant.initialization.success');
    }

    /**
     * Mostrar página de éxito
     */
    public function success()
    {
        $tenant = Tenant::current();
        
        if (!$tenant || !$tenant->is_initialized) {
            return redirect()->route('tenant.initialization.index');
        }

        return view('tenant.initialization.success', compact('tenant'));
    }

    /**
     * Ir al paso anterior
     */
    public function previousStep(Request $request)
    {
        $tenant = Tenant::current();
        
        if (!$tenant) {
            return redirect()->route('sedes.selection');
        }

        $currentStep = $tenant->initialization_step;
        
        if ($currentStep > 1) {
            $tenant->setInitializationStep($currentStep - 1);
        }

        return redirect()->route('tenant.initialization.index');
    }
}
