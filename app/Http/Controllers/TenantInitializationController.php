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

        // El paso 0 es la verificación de contraseña
        $step = $tenant->initialization_step ?? 0;
        $sede = $tenant->sede;
        
        // Verificar si existe un administrador para ESTA sede específica
        $existingAdmin = null;
        if ($step === 1 && $sede) {
            // Buscar administradores cuya facultad pertenezca a esta sede
            $facultadesDeEstaSede = \App\Models\Facultad::where('id_sede', $sede->id_sede)->pluck('id_facultad');
            
            $existingAdmin = User::role('Administrador')
                ->whereIn('id_facultad', $facultadesDeEstaSede)
                ->first();
        }

        return view('tenant.initialization.wizard', compact('tenant', 'sede', 'step', 'existingAdmin'));
    }

    /**
     * Verificar la contraseña de inicialización (paso 0)
     */
    public function verifyPassword(Request $request)
    {
        $tenant = Tenant::current();
        
        if (!$tenant) {
            return redirect()->route('sedes.selection');
        }

        $request->validate([
            'init_password' => 'required|string',
        ], [
            'init_password.required' => 'La contraseña es requerida.',
        ]);

        $correctPassword = config('multitenancy.init_password', env('TENANT_INIT_PASSWORD', 'aulasync2024'));
        
        if ($request->init_password !== $correctPassword) {
            return redirect()->back()->with('error', 'Contraseña incorrecta. Por favor, intente nuevamente.');
        }

        // Avanzar al paso 1
        $tenant->setInitializationStep(1);

        return redirect()->route('tenant.initialization.index')
            ->with('success', 'Contraseña verificada correctamente.');
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

        // Obtener la sede actual para asignar la facultad correcta
        $sede = Sede::find($tenant->sede_id);
        $facultad = \App\Models\Facultad::where('id_sede', $sede->id_sede)->first();

        // Crear el usuario administrador
        $user = User::create([
            'name' => $validated['name'],
            'run' => $validated['run'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'id_facultad' => $facultad ? $facultad->id_facultad : null,
            'id_universidad' => $facultad ? $facultad->id_universidad : ($sede ? $sede->id_universidad : null),
        ]);

        // Asignar rol de administrador
        $adminRole = Role::findByName('Administrador');
        if ($adminRole) {
            $user->assignRole($adminRole);
        }

        // Autenticar al usuario en la sesión para los pasos siguientes
        \Illuminate\Support\Facades\Auth::login($user);

        // Avanzar al siguiente paso
        $tenant->setInitializationStep(2);

        return redirect()->route('tenant.initialization.index')
            ->with('success', 'Cuenta de administrador creada exitosamente.');
    }

    /**
     * Procesar el paso 1: Login de administrador existente
     */
    public function loginAdmin(Request $request)
    {
        $tenant = Tenant::current();
        
        if (!$tenant) {
            return redirect()->route('sedes.selection');
        }

        $validated = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ], [
            'email.required' => 'El email es requerido.',
            'email.email' => 'Ingrese un email válido.',
            'password.required' => 'La contraseña es requerida.',
        ]);

        // Verificar credenciales
        $user = User::where('email', $validated['email'])->first();
        
        if (!$user || !Hash::check($validated['password'], $user->password)) {
            return redirect()->back()
                ->with('error', 'Credenciales incorrectas. Por favor, intente nuevamente.')
                ->withInput(['email' => $validated['email']]);
        }

        // Verificar que sea administrador
        if (!$user->hasRole('Administrador')) {
            return redirect()->back()
                ->with('error', 'Este usuario no tiene permisos de administrador.')
                ->withInput(['email' => $validated['email']]);
        }

        // Verificar que el usuario pertenezca a esta sede
        $sede = Sede::find($tenant->sede_id);
        $facultadesDeEstaSede = \App\Models\Facultad::where('id_sede', $sede->id_sede)->pluck('id_facultad');
        
        if (!$user->id_facultad || !$facultadesDeEstaSede->contains($user->id_facultad)) {
            return redirect()->back()
                ->with('error', 'Este administrador no pertenece a esta sede.')
                ->withInput(['email' => $validated['email']]);
        }

        // Autenticar al usuario en la sesión para los pasos siguientes
        \Illuminate\Support\Facades\Auth::login($user);

        // Avanzar al siguiente paso
        $tenant->setInitializationStep(2);

        return redirect()->route('tenant.initialization.index')
            ->with('success', 'Sesión de administrador verificada correctamente.');
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
            'prefijo_espacios' => 'required|string|max:10',
            'descripcion' => 'nullable|string|max:500',
            'direccion' => 'nullable|string|max:255',
            'telefono' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:100',
        ], [
            'prefijo_espacios.required' => 'El prefijo de espacios es requerido.',
            'prefijo_espacios.max' => 'El prefijo no puede tener más de 10 caracteres.',
        ]);

        $sede = $tenant->sede;
        
        // Actualizar sede (sin prefijo_espacios que va en tenant)
        $sedeData = $validated;
        unset($sedeData['prefijo_espacios']);
        $sede->update($sedeData);
        
        // También actualizar prefijo_sala en sede para compatibilidad
        $sede->update(['prefijo_sala' => strtoupper($validated['prefijo_espacios'])]);
        
        // Actualizar prefijo_espacios en tenant
        $tenant->update(['prefijo_espacios' => strtoupper($validated['prefijo_espacios'])]);

        // Avanzar al siguiente paso
        $tenant->setInitializationStep(4);

        return redirect()->route('tenant.initialization.index')
            ->with('success', 'Información de la sede confirmada.');
    }

    /**
     * Procesar el paso 4: Carga masiva de datos
     */
    public function uploadBulkData(Request $request)
    {
        $tenant = Tenant::current();
        
        if (!$tenant) {
            return redirect()->route('sedes.selection');
        }

        $validated = $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv|max:10240',
            'semestre_selector' => 'required|in:1,2',
        ], [
            'file.required' => 'El archivo es requerido.',
            'file.file' => 'Debe seleccionar un archivo válido.',
            'file.mimes' => 'El archivo debe ser Excel (.xlsx, .xls) o CSV.',
            'file.max' => 'El archivo no debe superar los 10MB.',
            'semestre_selector.required' => 'Debe seleccionar el semestre.',
            'semestre_selector.in' => 'Seleccione un semestre válido.',
        ]);

        try {
            // Redirigir a la ruta de carga masiva existente para procesar
            // Guardamos el archivo temporalmente y redirigimos
            $file = $request->file('file');
            $filename = 'init_' . time() . '_' . $file->getClientOriginalName();
            $file->storeAs('datos_subidos', $filename, 'public');
            
            // Guardar en sesión para el procesamiento posterior
            session([
                'init_bulk_file' => $filename,
                'init_bulk_semestre' => $validated['semestre_selector'],
            ]);
            
            // Avanzar al siguiente paso
            $tenant->setInitializationStep(5);

            return redirect()->route('tenant.initialization.index')
                ->with('success', 'Archivo cargado correctamente. Los datos serán procesados en segundo plano.');
                
        } catch (\Exception $e) {
            Log::error('Error en carga masiva durante inicialización: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Error al procesar el archivo: ' . $e->getMessage());
        }
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
     * Avanzar después de procesar la carga masiva exitosamente
     */
    public function completeBulkLoad(Request $request)
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
     * Procesar el paso 5: Guardar períodos académicos
     */
    public function storeAcademicPeriods(Request $request)
    {
        $tenant = Tenant::current();
        
        if (!$tenant) {
            return redirect()->route('sedes.selection');
        }

        $validated = $request->validate([
            'periodo1_inicio' => 'required|date',
            'periodo1_fin' => 'required|date|after:periodo1_inicio',
            'periodo2_inicio' => 'required|date|after:periodo1_fin',
            'periodo2_fin' => 'required|date|after:periodo2_inicio',
        ], [
            'periodo1_inicio.required' => 'La fecha de inicio del primer semestre es requerida.',
            'periodo1_fin.required' => 'La fecha de término del primer semestre es requerida.',
            'periodo1_fin.after' => 'La fecha de término debe ser posterior a la fecha de inicio.',
            'periodo2_inicio.required' => 'La fecha de inicio del segundo semestre es requerida.',
            'periodo2_inicio.after' => 'El segundo semestre debe comenzar después del primero.',
            'periodo2_fin.required' => 'La fecha de término del segundo semestre es requerida.',
            'periodo2_fin.after' => 'La fecha de término debe ser posterior a la fecha de inicio.',
        ]);

        $year = date('Y');
        
        // Guardar períodos en configuración
        Configuracion::set(
            "periodo_1_{$year}_{$tenant->sede_id}",
            json_encode([
                'nombre' => "Primer Semestre {$year}",
                'codigo' => "{$year}-1",
                'fecha_inicio' => $validated['periodo1_inicio'],
                'fecha_fin' => $validated['periodo1_fin'],
            ]),
            "Configuración del primer semestre {$year}"
        );

        Configuracion::set(
            "periodo_2_{$year}_{$tenant->sede_id}",
            json_encode([
                'nombre' => "Segundo Semestre {$year}",
                'codigo' => "{$year}-2",
                'fecha_inicio' => $validated['periodo2_inicio'],
                'fecha_fin' => $validated['periodo2_fin'],
            ]),
            "Configuración del segundo semestre {$year}"
        );

        Configuracion::set(
            "periodos_academicos_definidos_{$tenant->sede_id}",
            'true',
            "Indica si los períodos académicos están definidos para la sede"
        );

        // Avanzar al siguiente paso
        $tenant->setInitializationStep(6);

        return redirect()->route('tenant.initialization.index')
            ->with('success', 'Períodos académicos configurados correctamente.');
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
