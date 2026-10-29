<?php

namespace App\Http\Controllers;

use App\Models\Configuracion;
use App\Models\Sede;
use App\Models\Tenant;
use App\Models\PeriodoAcademico;
use App\Models\DiaFeriado;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;

class ConfiguracionController extends Controller
{
    /**
     * Determinar la sede activa o seleccionada
     */
    protected function determinarSede(Request $request): ?Sede
    {
        if ($request->filled('id_sede')) {
            $sede = Sede::find($request->id_sede);
            if ($sede) return $sede;
        }

        $tenant = Tenant::current();
        if ($tenant && $tenant->sede) {
            return $tenant->sede;
        }

        if (session()->has('tenant_id')) {
            $sessionTenant = Tenant::find(session('tenant_id'));
            if ($sessionTenant && $sessionTenant->sede) {
                return $sessionTenant->sede;
            }
        }

        if (auth()->check() && auth()->user()->id_sede) {
            $userSede = Sede::find(auth()->user()->id_sede);
            if ($userSede) return $userSede;
        }

        return Sede::find('LA') ?? Sede::first();
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $sedes = Sede::all();
            $sede = $this->determinarSede($request);
            $anio = (int) ($request->get('anio') ?: date('Y'));

            // Resolver Logo Institucional
            $logoUrl = null;
            $hasCustomLogo = false;
            if ($sede) {
                if ($sede->logo && Storage::disk('public')->exists('sedes/logos/' . $sede->logo)) {
                    $logoUrl = asset('storage/sedes/logos/' . $sede->logo);
                    $hasCustomLogo = true;
                } elseif ($configLogo = Configuracion::where('clave', "logo_institucional_{$sede->id_sede}")->first()) {
                    if ($configLogo->valor && Storage::disk('public')->exists('images/logo/' . $configLogo->valor)) {
                        $logoUrl = asset('storage/images/logo/' . $configLogo->valor);
                        $hasCustomLogo = true;
                    }
                }
            }
            if (!$logoUrl) {
                if (file_exists(public_path('images/Logo-UCSC-Color-Horizontal.png'))) {
                    $logoUrl = asset('images/Logo-UCSC-Color-Horizontal.png');
                } elseif (file_exists(public_path('images/logo_IT_talcahuano.png'))) {
                    $logoUrl = asset('images/logo_IT_talcahuano.png');
                } else {
                    $logoUrl = asset('images/logo_instituto_tecnologico-01.png');
                }
            }

            // Períodos Académicos Definidos para la sede
            $idSede = $sede ? $sede->id_sede : 'default';
            $periodosDefinidosConfig = Configuracion::where('clave', "periodos_academicos_definidos_{$idSede}")->first();
            $periodosDefinidos = $periodosDefinidosConfig ? ($periodosDefinidosConfig->valor === 'true') : true;

            // Fechas de Semestres (Central DB y Tenant Config)
            $periodo1Model = PeriodoAcademico::where('anio', $anio)->where('semestre', 1)->first();
            $periodo2Model = PeriodoAcademico::where('anio', $anio)->where('semestre', 2)->first();

            $p1ConfigRaw = Configuracion::get("periodo_1_{$anio}_{$idSede}");
            $p1Config = $p1ConfigRaw ? json_decode($p1ConfigRaw, true) : null;

            $p2ConfigRaw = Configuracion::get("periodo_2_{$anio}_{$idSede}");
            $p2Config = $p2ConfigRaw ? json_decode($p2ConfigRaw, true) : null;

            $periodo1Inicio = $periodo1Model?->fecha_inicio?->format('Y-m-d')
                ?? $p1Config['fecha_inicio']
                ?? "{$anio}-03-02";
            $periodo1Fin = $periodo1Model?->fecha_fin?->format('Y-m-d')
                ?? $p1Config['fecha_fin']
                ?? "{$anio}-07-10";

            $periodo2Inicio = $periodo2Model?->fecha_inicio?->format('Y-m-d')
                ?? $p2Config['fecha_inicio']
                ?? "{$anio}-07-27";
            $periodo2Fin = $periodo2Model?->fecha_fin?->format('Y-m-d')
                ?? $p2Config['fecha_fin']
                ?? "{$anio}-12-18";

            // Semanas de Receso (de DiaFeriado o Configuracion)
            $receso1Feriado = DiaFeriado::where('tipo', 'semana_reajuste')
                ->where(function ($q) use ($anio) {
                    $q->where('nombre', 'like', "%{$anio}-1%")
                      ->orWhere('nombre', 'like', "%Primer Semestre {$anio}%");
                })->first();

            $receso2Feriado = DiaFeriado::where('tipo', 'semana_reajuste')
                ->where(function ($q) use ($anio) {
                    $q->where('nombre', 'like', "%{$anio}-2%")
                      ->orWhere('nombre', 'like', "%Segundo Semestre {$anio}%");
                })->first();

            $receso1Habilitado = $p1Config['receso_habilitado'] ?? ($receso1Feriado ? $receso1Feriado->activo : false);
            $receso1Inicio = $p1Config['receso_inicio'] ?? $receso1Feriado?->fecha_inicio?->format('Y-m-d') ?? '';
            $receso1Fin = $p1Config['receso_fin'] ?? $receso1Feriado?->fecha_fin?->format('Y-m-d') ?? '';

            $receso2Habilitado = $p2Config['receso_habilitado'] ?? ($receso2Feriado ? $receso2Feriado->activo : false);
            $receso2Inicio = $p2Config['receso_inicio'] ?? $receso2Feriado?->fecha_inicio?->format('Y-m-d') ?? '';
            $receso2Fin = $p2Config['receso_fin'] ?? $receso2Feriado?->fecha_fin?->format('Y-m-d') ?? '';

            $configuraciones = Configuracion::all();

            return view('configuracion.index', compact(
                'sedes',
                'sede',
                'anio',
                'logoUrl',
                'hasCustomLogo',
                'periodosDefinidos',
                'periodo1Inicio',
                'periodo1Fin',
                'periodo2Inicio',
                'periodo2Fin',
                'receso1Habilitado',
                'receso1Inicio',
                'receso1Fin',
                'receso2Habilitado',
                'receso2Inicio',
                'receso2Fin',
                'configuraciones'
            ));
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => 'Hubo un problema al cargar las configuraciones: ' . $e->getMessage()])->withInput();
        }
    }

    /**
     * Guardar la configuración general (Identidad Visual, Períodos Académicos y Recesos).
     */
    public function guardarGeneral(Request $request)
    {
        try {
            $validated = $request->validate([
                'id_sede' => 'required|exists:sedes,id_sede',
                'anio' => 'required|integer|min:2020|max:2035',
                'periodos_definidos' => 'nullable',
                'periodo1_inicio' => 'required|date',
                'periodo1_fin' => 'required|date|after_or_equal:periodo1_inicio',
                'periodo2_inicio' => 'required|date|after_or_equal:periodo1_fin',
                'periodo2_fin' => 'required|date|after_or_equal:periodo2_inicio',
                'receso1_habilitado' => 'nullable',
                'receso1_inicio' => 'nullable|date',
                'receso1_fin' => 'nullable|date',
                'receso2_habilitado' => 'nullable',
                'receso2_inicio' => 'nullable|date',
                'receso2_fin' => 'nullable|date',
                'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
                'eliminar_logo' => 'nullable',
            ], [
                'periodo1_inicio.required' => 'La fecha de inicio del primer semestre es obligatoria.',
                'periodo1_fin.required' => 'La fecha de término del primer semestre es obligatoria.',
                'periodo1_fin.after_or_equal' => 'La fecha de término del primer semestre debe ser posterior o igual a la de inicio.',
                'periodo2_inicio.required' => 'La fecha de inicio del segundo semestre es obligatoria.',
                'periodo2_inicio.after_or_equal' => 'El segundo semestre debe comenzar después del fin del primer semestre.',
                'periodo2_fin.required' => 'La fecha de término del segundo semestre es obligatoria.',
                'periodo2_fin.after_or_equal' => 'La fecha de término del segundo semestre debe ser posterior o igual a la de inicio.',
                'logo.image' => 'El logo debe ser un archivo de imagen.',
                'logo.max' => 'El logo no debe superar los 2MB.',
            ]);

            $sede = Sede::findOrFail($validated['id_sede']);
            $anio = (int) $validated['anio'];

            // 1. Manejo del Logo Institucional
            if ($request->filled('eliminar_logo') && $request->eliminar_logo == '1') {
                if ($sede->logo && Storage::disk('public')->exists('sedes/logos/' . $sede->logo)) {
                    Storage::disk('public')->delete('sedes/logos/' . $sede->logo);
                }
                $sede->update(['logo' => null]);

                $configLogo = Configuracion::where('clave', "logo_institucional_{$sede->id_sede}")->first();
                if ($configLogo) {
                    if ($configLogo->valor && Storage::disk('public')->exists('images/logo/' . $configLogo->valor)) {
                        Storage::disk('public')->delete('images/logo/' . $configLogo->valor);
                    }
                    $configLogo->delete();
                }
                Cache::forget("logo_institucional_path_{$sede->id_sede}");
            } elseif ($request->hasFile('logo')) {
                $file = $request->file('logo');
                $filename = $sede->id_sede . '_logo_' . time() . '.' . $file->getClientOriginalExtension();
                $file->storeAs('public/sedes/logos', $filename);

                if ($sede->logo && $sede->logo !== $filename && Storage::disk('public')->exists('sedes/logos/' . $sede->logo)) {
                    Storage::disk('public')->delete('sedes/logos/' . $sede->logo);
                }
                $sede->update(['logo' => $filename]);

                Configuracion::set(
                    "logo_institucional_{$sede->id_sede}",
                    $filename,
                    "Logo institucional de la sede {$sede->nombre_sede}"
                );
                Cache::forget("logo_institucional_path_{$sede->id_sede}");
            }

            // 2. Toggle de períodos académicos definidos
            $periodosDefinidos = $request->has('periodos_definidos') ? 'true' : 'false';
            Configuracion::set(
                "periodos_academicos_definidos_{$sede->id_sede}",
                $periodosDefinidos,
                "Indica si los períodos académicos están definidos para la sede"
            );

            // 3. Períodos Académicos en BD central
            PeriodoAcademico::updateOrCreate(
                ['anio' => $anio, 'semestre' => 1],
                [
                    'fecha_inicio' => $validated['periodo1_inicio'],
                    'fecha_fin' => $validated['periodo1_fin'],
                    'activo' => true,
                    'created_by' => auth()->user()->run ?? null,
                ]
            );

            PeriodoAcademico::updateOrCreate(
                ['anio' => $anio, 'semestre' => 2],
                [
                    'fecha_inicio' => $validated['periodo2_inicio'],
                    'fecha_fin' => $validated['periodo2_fin'],
                    'activo' => true,
                    'created_by' => auth()->user()->run ?? null,
                ]
            );

            PeriodoAcademico::sincronizarEstadosSegunFechas();

            // 4. Períodos en tabla Configuracion
            $receso1Habilitado = $request->has('receso1_habilitado');
            $receso1Inicio = $receso1Habilitado ? $request->input('receso1_inicio') : null;
            $receso1Fin = $receso1Habilitado ? $request->input('receso1_fin') : null;

            Configuracion::set(
                "periodo_1_{$anio}_{$sede->id_sede}",
                json_encode([
                    'nombre' => "Primer Semestre {$anio}",
                    'codigo' => "{$anio}-1",
                    'fecha_inicio' => $validated['periodo1_inicio'],
                    'fecha_fin' => $validated['periodo1_fin'],
                    'receso_habilitado' => $receso1Habilitado,
                    'receso_inicio' => $receso1Inicio,
                    'receso_fin' => $receso1Fin,
                ]),
                "Configuración del primer semestre {$anio}"
            );

            $receso2Habilitado = $request->has('receso2_habilitado');
            $receso2Inicio = $receso2Habilitado ? $request->input('receso2_inicio') : null;
            $receso2Fin = $receso2Habilitado ? $request->input('receso2_fin') : null;

            Configuracion::set(
                "periodo_2_{$anio}_{$sede->id_sede}",
                json_encode([
                    'nombre' => "Segundo Semestre {$anio}",
                    'codigo' => "{$anio}-2",
                    'fecha_inicio' => $validated['periodo2_inicio'],
                    'fecha_fin' => $validated['periodo2_fin'],
                    'receso_habilitado' => $receso2Habilitado,
                    'receso_inicio' => $receso2Inicio,
                    'receso_fin' => $receso2Fin,
                ]),
                "Configuración del segundo semestre {$anio}"
            );

            // 5. Semanas de Receso en DiaFeriado
            if ($receso1Habilitado && $receso1Inicio && $receso1Fin) {
                DiaFeriado::updateOrCreate(
                    ['nombre' => "Semana de Receso {$anio}-1", 'tipo' => 'semana_reajuste'],
                    [
                        'fecha_inicio' => $receso1Inicio,
                        'fecha_fin' => $receso1Fin,
                        'descripcion' => "Semana de receso académico - 1° Semestre {$anio}",
                        'activo' => true,
                        'created_by' => auth()->user()->run ?? null,
                    ]
                );
            } else {
                DiaFeriado::where('nombre', "Semana de Receso {$anio}-1")
                    ->where('tipo', 'semana_reajuste')
                    ->delete();
            }

            if ($receso2Habilitado && $receso2Inicio && $receso2Fin) {
                DiaFeriado::updateOrCreate(
                    ['nombre' => "Semana de Receso {$anio}-2", 'tipo' => 'semana_reajuste'],
                    [
                        'fecha_inicio' => $receso2Inicio,
                        'fecha_fin' => $receso2Fin,
                        'descripcion' => "Semana de receso académico - 2° Semestre {$anio}",
                        'activo' => true,
                        'created_by' => auth()->user()->run ?? null,
                    ]
                );
            } else {
                DiaFeriado::where('nombre', "Semana de Receso {$anio}-2")
                    ->where('tipo', 'semana_reajuste')
                    ->delete();
            }

            return redirect()->route('configuracion.index', ['id_sede' => $sede->id_sede, 'anio' => $anio])
                ->with('success', 'Configuración del sistema guardada exitosamente.');
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => 'Hubo un problema al guardar la configuración: ' . $e->getMessage()])->withInput();
        }
    }

    /**
     * Eliminar el logo institucional de la sede.
     */
    public function eliminarLogo(string $idSede)
    {
        try {
            $sede = Sede::findOrFail($idSede);
            if ($sede->logo && Storage::disk('public')->exists('sedes/logos/' . $sede->logo)) {
                Storage::disk('public')->delete('sedes/logos/' . $sede->logo);
            }
            $sede->update(['logo' => null]);

            $configLogo = Configuracion::where('clave', "logo_institucional_{$idSede}")->first();
            if ($configLogo) {
                if ($configLogo->valor && Storage::disk('public')->exists('images/logo/' . $configLogo->valor)) {
                    Storage::disk('public')->delete('images/logo/' . $configLogo->valor);
                }
                $configLogo->delete();
            }

            Cache::forget("logo_institucional_path_{$idSede}");

            if (request()->wantsJson() || request()->ajax()) {
                return response()->json(['success' => true, 'message' => 'Logo institucional eliminado correctamente.']);
            }

            return redirect()->route('configuracion.index', ['id_sede' => $idSede])
                ->with('success', 'Logo institucional eliminado correctamente.');
        } catch (\Exception $e) {
            if (request()->wantsJson() || request()->ajax()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
            }
            return back()->withErrors(['error' => 'Error al eliminar el logo: ' . $e->getMessage()]);
        }
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
            // Determine the actual key value
            $clave = $request->clave === 'other' ? $request->clave_custom : $request->clave;

            // Special handling for logo upload
            if ($clave === 'logo_institucional' && $request->hasFile('logo')) {
                $request->validate([
                    'clave' => 'required|string',
                    'id_sede' => 'required|exists:sedes,id_sede',
                    'clave_custom' => 'nullable|string|max:100|unique:configuracion,clave',
                    'logo' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
                    'descripcion' => 'nullable|string|max:255',
                ]);

                // Construir la clave con el ID de sede
                $claveCompleta = "logo_institucional_{$request->id_sede}";

                // Verificar si ya existe un logo para esta sede
                $existingLogo = Configuracion::where('clave', $claveCompleta)->first();
                if ($existingLogo) {
                    return redirect()->back()->withErrors(['error' => "Ya existe un logo para la sede {$request->id_sede}. Por favor, edítelo en lugar de crear uno nuevo."])->withInput();
                }

                // Upload logo
                $logoName = time() . '_' . $request->id_sede . '.' . $request->logo->extension();
                $request->logo->storeAs('images/logo', $logoName, 'public');
                
                Configuracion::create([
                    'clave' => $claveCompleta,
                    'valor' => $logoName,
                    'descripcion' => $request->descripcion ?? "Logo institucional de la sede {$request->id_sede}",
                ]);

                // Clear logo cache for this sede
                Cache::forget("logo_institucional_path_{$request->id_sede}");
            } 
            else {
                $request->validate([
                    'clave' => 'required|string',
                    'clave_custom' => 'nullable|string|max:100|unique:configuracion,clave',
                    'valor' => 'required|string',
                    'descripcion' => 'nullable|string|max:255',
                ]);

                Configuracion::create([
                    'clave' => $clave,
                    'valor' => $request->valor,
                    'descripcion' => $request->descripcion,
                ]);
            }

            return redirect()->route('configuracion.index')->with('success', 'Configuración creada exitosamente.');
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => 'Hubo un problema al crear la configuración. Por favor, intente nuevamente. ' . $e->getMessage()])->withInput();
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
        try {
            $configuracion = Configuracion::findOrFail($id);
            return view('configuracion.edit', compact('configuracion'));
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return redirect()->route('configuracion.index')->withErrors(['error' => 'Configuración no encontrada.']);
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Error al cargar la configuración: ' . $e->getMessage()]);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        try {
            $configuracion = Configuracion::findOrFail($id);

            // Special handling for logo upload
            if (str_starts_with($configuracion->clave, 'logo_institucional_')) {
                if ($request->hasFile('logo')) {
                    $request->validate([
                        'logo' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
                        'descripcion' => 'nullable|string|max:255',
                    ]);

                    // Delete old logo if exists
                    if ($configuracion->valor && Storage::disk('public')->exists('images/logo/' . $configuracion->valor)) {
                        Storage::disk('public')->delete('images/logo/' . $configuracion->valor);
                    }

                    // Extract sede ID from clave
                    $idSede = str_replace('logo_institucional_', '', $configuracion->clave);

                    // Upload new logo
                    $logoName = time() . '_' . $idSede . '.' . $request->logo->extension();
                    $request->logo->storeAs('images/logo', $logoName, 'public');
                    
                    $configuracion->update([
                        'valor' => $logoName,
                        'descripcion' => $request->descripcion,
                    ]);

                    // Clear logo cache for this sede
                    Cache::forget("logo_institucional_path_{$idSede}");
                } else {
                    // Only update description if no new logo is uploaded
                    $request->validate([
                        'descripcion' => 'nullable|string|max:255',
                    ]);

                    $configuracion->update([
                        'descripcion' => $request->descripcion,
                    ]);
                }
            } 
            // Special handling for administrative email
            else if (str_starts_with($configuracion->clave, 'correo_administrativo_')) {
                $request->validate([
                    'valor' => 'required|email|max:255',
                    'nombre_remitente' => 'nullable|string|max:255',
                    'descripcion' => 'nullable|string|max:255',
                ]);

                // Extract sede ID from clave
                $idSede = str_replace('logo_institucional_', '', $configuracion->clave);

                // If new logo is uploaded
                if ($request->hasFile('logo')) {
                    // Delete old logo file
                    if ($configuracion->valor && Storage::disk('public')->exists('images/logo/' . $configuracion->valor)) {
                        Storage::disk('public')->delete('images/logo/' . $configuracion->valor);
                    }

                    // Upload new logo
                    $logoName = time() . '_' . $idSede . '.' . $request->logo->extension();
                    $request->logo->storeAs('images/logo', $logoName, 'public');
                    
                    $configuracion->update([
                        'valor' => $logoName,
                        'descripcion' => $request->descripcion,
                    ]);
                } else {
                    $configuracion->update([
                        'descripcion' => $request->descripcion,
                    ]);
                }

                // Clear logo cache for this sede
                Cache::forget("logo_institucional_path_{$idSede}");
            }
            else {
                $request->validate([
                    'valor' => 'required|string',
                    'descripcion' => 'nullable|string|max:255',
                ]);

                $configuracion->update([
                    'valor' => $request->valor,
                    'descripcion' => $request->descripcion,
                ]);
            }

            return redirect()->route('configuracion.index')->with('success', 'Configuración actualizada exitosamente.');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return redirect()->route('configuracion.index')->withErrors(['error' => 'Configuración no encontrada.']);
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Error al actualizar la configuración: ' . $e->getMessage()]);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $configuracion = Configuracion::findOrFail($id);
            
            // Delete logo file if it's a logo configuration
            if (str_starts_with($configuracion->clave, 'logo_institucional_') && $configuracion->valor) {
                if (Storage::disk('public')->exists('images/logo/' . $configuracion->valor)) {
                    Storage::disk('public')->delete('images/logo/' . $configuracion->valor);
                }
                // Extract sede ID and clear cache
                $idSede = str_replace('logo_institucional_', '', $configuracion->clave);
                Cache::forget("logo_institucional_path_{$idSede}");
            }
            
            $configuracion->delete();
            return redirect()->route('configuracion.index')->with('success', 'Configuración eliminada exitosamente.');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['success' => false, 'message' => 'Configuración no encontrada.'], 404);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error al borrar la configuración: ' . $e->getMessage()], 500);
        }
    }
}
