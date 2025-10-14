<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\TipoCorreoMasivo;
use App\Models\DestinatarioCorreo;
use App\Models\PlantillaCorreo;
use App\Models\User;
use App\Mail\CorreoPersonalizado;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class CorreosMasivosManager extends Component
{
    use WithPagination;

    // Variables para la pestaña activa
    public $tab = 'tipos'; // 'tipos', 'destinatarios', 'plantillas', 'enviar'

    // Variables para Tipos de Correos
    public $tipoSearch = '';
    public $tipoNombre = '';
    public $tipoCodigo = '';
    public $tipoDescripcion = '';
    public $tipoFrecuencia = 'manual';
    public $tipoActivo = true;
    public $editingTipoId = null;

    // Variables para Destinatarios
    public $destinatarioSearch = '';
    public $destinatarioUserId = '';
    public $destinatarioEsExterno = false;
    public $destinatarioEmailExterno = '';
    public $destinatarioNombreExterno = '';
    public $destinatarioRol = '';
    public $destinatarioCargo = '';
    public $destinatarioActivo = true;
    public $editingDestinatarioId = null;
    public $filtroTipoUsuario = ''; // Para filtrar por rol (Profesor, Usuario, etc)

    // Variables para Plantillas
    public $plantillaSearch = '';
    public $plantillaNombre = '';
    public $plantillaAsunto = '';
    public $plantillaContenidoHtml = '';
    public $plantillaContenidoTexto = '';
    public $plantillaTipoCorreoId = null;
    public $plantillaActivo = true;
    public $editingPlantillaId = null;
    public $showPlantillaEditor = false;

    // Variables para Envío de Correos
    public $envioPlantillaId = null;
    public $envioDestinatariosSeleccionados = [];
    public $envioAsunto = '';
    public $envioContenido = '';
    public $envioDestinatariosExternos = ''; // Emails separados por comas

    // Variables para asignación
    public $selectedTipoId = null;
    public $selectedDestinatarioId = null;
    public $showAsignacionModal = false;

    protected $rules = [
        'tipoNombre' => 'required|string|max:255',
        'tipoCodigo' => 'required|string|max:255|unique:tipos_correos_masivos,codigo',
        'tipoDescripcion' => 'nullable|string',
        'tipoFrecuencia' => 'required|in:diario,semanal,mensual,manual',
        'tipoActivo' => 'boolean',

        'destinatarioUserId' => 'nullable|exists:users,run',
        'destinatarioEsExterno' => 'boolean',
        'destinatarioEmailExterno' => 'nullable|email|max:255',
        'destinatarioNombreExterno' => 'nullable|string|max:255',
        'destinatarioRol' => 'nullable|string|max:255',
        'destinatarioCargo' => 'nullable|string|max:255',
        'destinatarioActivo' => 'boolean',

        'plantillaNombre' => 'required|string|max:255',
        'plantillaAsunto' => 'required|string|max:255',
        'plantillaContenidoHtml' => 'required|string',
        'plantillaContenidoTexto' => 'nullable|string',
        'plantillaTipoCorreoId' => 'nullable|exists:tipos_correos_masivos,id',
        'plantillaActivo' => 'boolean',
    ];

    public function mount()
    {
        // Verificar que el usuario sea administrador
        if (!auth()->user()->hasRole('Administrador')) {
            abort(403, 'No tienes permisos para acceder a esta sección.');
        }
    }

    // ============ MÉTODOS PARA TIPOS DE CORREOS ============

    public function saveTipo()
    {
        // Si estamos editando, modificar regla de unique
        if ($this->editingTipoId) {
            $this->rules['tipoCodigo'] = 'required|string|max:255|unique:tipos_correos_masivos,codigo,' . $this->editingTipoId;
        }

        $this->validate([
            'tipoNombre' => $this->rules['tipoNombre'],
            'tipoCodigo' => $this->rules['tipoCodigo'],
            'tipoDescripcion' => $this->rules['tipoDescripcion'],
            'tipoFrecuencia' => $this->rules['tipoFrecuencia'],
            'tipoActivo' => $this->rules['tipoActivo'],
        ]);

        if ($this->editingTipoId) {
            $tipo = TipoCorreoMasivo::find($this->editingTipoId);
            $tipo->update([
                'nombre' => $this->tipoNombre,
                'codigo' => $this->tipoCodigo,
                'descripcion' => $this->tipoDescripcion,
                'frecuencia' => $this->tipoFrecuencia,
                'activo' => $this->tipoActivo,
            ]);
            $message = 'Tipo de correo actualizado exitosamente';
        } else {
            TipoCorreoMasivo::create([
                'nombre' => $this->tipoNombre,
                'codigo' => $this->tipoCodigo,
                'descripcion' => $this->tipoDescripcion,
                'tipo' => 'custom',
                'frecuencia' => $this->tipoFrecuencia,
                'activo' => $this->tipoActivo,
            ]);
            $message = 'Tipo de correo creado exitosamente';
        }

        $this->resetTipoForm();
        $this->dispatch('show-success', ['message' => $message]);
    }

    public function editTipo($id)
    {
        $tipo = TipoCorreoMasivo::findOrFail($id);

        $this->editingTipoId = $id;
        $this->tipoNombre = $tipo->nombre;
        $this->tipoCodigo = $tipo->codigo;
        $this->tipoDescripcion = $tipo->descripcion;
        $this->tipoFrecuencia = $tipo->frecuencia;
        $this->tipoActivo = $tipo->activo;
    }

    public function deleteTipo($id)
    {
        $tipo = TipoCorreoMasivo::findOrFail($id);

        // No permitir eliminar tipos del sistema
        if ($tipo->tipo === 'sistema') {
            $this->dispatch('show-error', ['message' => 'No se pueden eliminar tipos de correos del sistema']);
            return;
        }

        $this->dispatch('confirm-delete-tipo', ['id' => $id, 'nombre' => $tipo->nombre]);
    }

    public function confirmDeleteTipo($id)
    {
        TipoCorreoMasivo::destroy($id);
        $this->dispatch('show-success', ['message' => 'Tipo de correo eliminado exitosamente']);
    }

    public function resetTipoForm()
    {
        $this->reset(['tipoNombre', 'tipoCodigo', 'tipoDescripcion', 'tipoFrecuencia', 'tipoActivo', 'editingTipoId']);
        $this->resetValidation();
    }

    public function generarCodigo()
    {
        if ($this->tipoNombre) {
            $this->tipoCodigo = Str::slug($this->tipoNombre, '_');
        }
    }

    // ============ MÉTODOS PARA DESTINATARIOS ============

    public function saveDestinatario()
    {
        // Validación dinámica según el tipo de destinatario
        $rules = [
            'destinatarioEsExterno' => $this->rules['destinatarioEsExterno'],
            'destinatarioRol' => $this->rules['destinatarioRol'],
            'destinatarioCargo' => $this->rules['destinatarioCargo'],
            'destinatarioActivo' => $this->rules['destinatarioActivo'],
        ];

        if ($this->destinatarioEsExterno) {
            $rules['destinatarioEmailExterno'] = 'required|email|max:255';
            $rules['destinatarioNombreExterno'] = 'required|string|max:255';
        } else {
            $rules['destinatarioUserId'] = 'required|exists:users,run';
        }

        $this->validate($rules);

        if ($this->editingDestinatarioId) {
            $destinatario = DestinatarioCorreo::find($this->editingDestinatarioId);
            $destinatario->update([
                'user_id' => $this->destinatarioEsExterno ? null : $this->destinatarioUserId,
                'es_externo' => $this->destinatarioEsExterno,
                'email_externo' => $this->destinatarioEsExterno ? $this->destinatarioEmailExterno : null,
                'nombre_externo' => $this->destinatarioEsExterno ? $this->destinatarioNombreExterno : null,
                'rol' => $this->destinatarioRol,
                'cargo' => $this->destinatarioCargo,
                'activo' => $this->destinatarioActivo,
            ]);
            $message = 'Destinatario actualizado exitosamente';
        } else {
            // Verificar si ya existe
            if ($this->destinatarioEsExterno) {
                $existente = DestinatarioCorreo::where('email_externo', $this->destinatarioEmailExterno)->first();
                if ($existente) {
                    $this->addError('destinatarioEmailExterno', 'Este email ya está registrado como destinatario');
                    return;
                }
            } else {
                $existente = DestinatarioCorreo::where('user_id', $this->destinatarioUserId)->first();
                if ($existente) {
                    $this->addError('destinatarioUserId', 'Este usuario ya está registrado como destinatario');
                    return;
                }
            }

            DestinatarioCorreo::create([
                'user_id' => $this->destinatarioEsExterno ? null : $this->destinatarioUserId,
                'es_externo' => $this->destinatarioEsExterno,
                'email_externo' => $this->destinatarioEsExterno ? $this->destinatarioEmailExterno : null,
                'nombre_externo' => $this->destinatarioEsExterno ? $this->destinatarioNombreExterno : null,
                'rol' => $this->destinatarioRol,
                'cargo' => $this->destinatarioCargo,
                'activo' => $this->destinatarioActivo,
            ]);
            $message = 'Destinatario creado exitosamente';
        }

        $this->resetDestinatarioForm();
        $this->dispatch('show-success', ['message' => $message]);
    }

    public function editDestinatario($id)
    {
        $destinatario = DestinatarioCorreo::findOrFail($id);

        $this->editingDestinatarioId = $id;
        $this->destinatarioEsExterno = $destinatario->es_externo;
        $this->destinatarioUserId = $destinatario->user_id;
        $this->destinatarioEmailExterno = $destinatario->email_externo;
        $this->destinatarioNombreExterno = $destinatario->nombre_externo;
        $this->destinatarioRol = $destinatario->rol;
        $this->destinatarioCargo = $destinatario->cargo;
        $this->destinatarioActivo = $destinatario->activo;
    }

    public function deleteDestinatario($id)
    {
        $destinatario = DestinatarioCorreo::with('user')->findOrFail($id);
        $nombre = $destinatario->es_externo 
            ? ($destinatario->nombre_externo ?? $destinatario->email_externo)
            : ($destinatario->user->name ?? 'N/A');
            
        $this->dispatch('confirm-delete-destinatario', [
            'id' => $id,
            'nombre' => $nombre,
            'rol' => $destinatario->rol
        ]);
    }

    public function confirmDeleteDestinatario($id)
    {
        DestinatarioCorreo::destroy($id);
        $this->dispatch('show-success', ['message' => 'Destinatario eliminado exitosamente']);
    }

    public function resetDestinatarioForm()
    {
        $this->reset([
            'destinatarioUserId',
            'destinatarioEsExterno',
            'destinatarioEmailExterno',
            'destinatarioNombreExterno',
            'destinatarioRol',
            'destinatarioCargo',
            'destinatarioActivo',
            'editingDestinatarioId'
        ]);
        $this->resetValidation();
    }

    // ============ MÉTODOS PARA ASIGNACIÓN ============

    public function showAsignaciones($tipoId)
    {
        $this->selectedTipoId = $tipoId;
        $this->showAsignacionModal = true;
    }

    public function toggleAsignacion($tipoId, $destinatarioId)
    {
        $tipo = TipoCorreoMasivo::findOrFail($tipoId);
        $destinatario = DestinatarioCorreo::findOrFail($destinatarioId);

        // Verificar si ya existe la relación
        $exists = $tipo->destinatarios()->where('destinatario_correo_id', $destinatarioId)->exists();

        if ($exists) {
            // Obtener el estado actual y togglearlo
            $pivot = $tipo->destinatarios()->where('destinatario_correo_id', $destinatarioId)->first()->pivot;
            $nuevoEstado = !$pivot->habilitado;

            $tipo->destinatarios()->updateExistingPivot($destinatarioId, [
                'habilitado' => $nuevoEstado
            ]);
        } else {
            // Crear la relación habilitada
            $tipo->destinatarios()->attach($destinatarioId, ['habilitado' => true]);
        }

        $this->dispatch('show-success', ['message' => 'Asignación actualizada']);
    }

    public function closeAsignacionModal()
    {
        $this->showAsignacionModal = false;
        $this->selectedTipoId = null;
    }

    // ============ MÉTODOS PARA PLANTILLAS ============

    public function showPlantillaEditorModal($id = null)
    {
        if ($id) {
            $plantilla = PlantillaCorreo::findOrFail($id);
            $this->editingPlantillaId = $id;
            $this->plantillaNombre = $plantilla->nombre;
            $this->plantillaAsunto = $plantilla->asunto;
            $this->plantillaContenidoHtml = $plantilla->contenido_html;
            $this->plantillaContenidoTexto = $plantilla->contenido_texto;
            $this->plantillaTipoCorreoId = $plantilla->tipo_correo_masivo_id;
            $this->plantillaActivo = $plantilla->activo;
        }

        $this->showPlantillaEditor = true;
        
        // Emitir evento para inicializar TinyMCE
        $this->dispatch('plantilla-editor-opened');
    }    public function savePlantilla()
    {
        $this->validate([
            'plantillaNombre' => $this->rules['plantillaNombre'],
            'plantillaAsunto' => $this->rules['plantillaAsunto'],
            'plantillaContenidoHtml' => $this->rules['plantillaContenidoHtml'],
            'plantillaContenidoTexto' => $this->rules['plantillaContenidoTexto'],
            'plantillaTipoCorreoId' => $this->rules['plantillaTipoCorreoId'],
            'plantillaActivo' => $this->rules['plantillaActivo'],
        ]);

        $datos = [
            'nombre' => $this->plantillaNombre,
            'asunto' => $this->plantillaAsunto,
            'contenido_html' => $this->plantillaContenidoHtml,
            'contenido_texto' => $this->plantillaContenidoTexto,
            'tipo_correo_masivo_id' => $this->plantillaTipoCorreoId,
            'activo' => $this->plantillaActivo,
        ];

        if ($this->editingPlantillaId) {
            $plantilla = PlantillaCorreo::find($this->editingPlantillaId);
            $datos['actualizado_por'] = auth()->user()->run;
            $plantilla->update($datos);
            $message = 'Plantilla actualizada exitosamente';
        } else {
            $datos['creado_por'] = auth()->user()->run;
            PlantillaCorreo::create($datos);
            $message = 'Plantilla creada exitosamente';
        }

        $this->resetPlantillaForm();
        $this->dispatch('show-success', ['message' => $message]);
    }

    public function editPlantilla($id)
    {
        $this->showPlantillaEditorModal($id);
    }

    public function deletePlantilla($id)
    {
        $plantilla = PlantillaCorreo::findOrFail($id);
        $this->dispatch('confirm-delete-plantilla', ['id' => $id, 'nombre' => $plantilla->nombre]);
    }

    public function confirmDeletePlantilla($id)
    {
        PlantillaCorreo::destroy($id);
        $this->dispatch('show-success', ['message' => 'Plantilla eliminada exitosamente']);
    }

    public function resetPlantillaForm()
    {
        $this->reset([
            'plantillaNombre',
            'plantillaAsunto',
            'plantillaContenidoHtml',
            'plantillaContenidoTexto',
            'plantillaTipoCorreoId',
            'plantillaActivo',
            'editingPlantillaId',
            'showPlantillaEditor'
        ]);
        $this->resetValidation();
    }

    public function insertarVariable($variable)
    {
        $this->plantillaContenidoHtml .= '{{' . $variable . '}}';
    }

    // ============ MÉTODOS PARA ENVÍO DE CORREOS ============

    /**
     * Reemplaza las variables en el contenido del correo
     */
    private function reemplazarVariables(string $contenido, array $datos): string
    {
        $variables = [
            '{{nombre}}' => $datos['nombre'] ?? '',
            '{{email}}' => $datos['email'] ?? '',
            '{{fecha}}' => $datos['fecha'] ?? now()->format('d/m/Y'),
            '{{periodo}}' => $datos['periodo'] ?? now()->format('Y'),
            '{{total_clases}}' => $datos['total_clases'] ?? '0',
            '{{clases_no_realizadas}}' => $datos['clases_no_realizadas'] ?? '0',
            '{{porcentaje}}' => $datos['porcentaje'] ?? '0%',
        ];

        // Reemplazar cada variable en el contenido
        foreach ($variables as $variable => $valor) {
            $contenido = str_replace($variable, $valor, $contenido);
        }

        return $contenido;
    }

    public function cargarPlantillaParaEnvio($plantillaId)
    {
        $plantilla = PlantillaCorreo::findOrFail($plantillaId);
        
        $this->envioPlantillaId = $plantillaId;
        $this->envioAsunto = $plantilla->asunto;
        $this->envioContenido = $plantilla->contenido_html;
    }

    public function enviarCorreos()
    {
        $this->validate([
            'envioPlantillaId' => 'required|exists:plantillas_correos,id',
            'envioAsunto' => 'required|string|max:255',
            'envioContenido' => 'required|string',
        ]);

        // Validar que haya al menos un destinatario
        $destinatariosInternos = $this->envioDestinatariosSeleccionados ?? [];
        $emailsExternos = array_filter(
            array_map('trim', explode(',', $this->envioDestinatariosExternos)),
            fn($email) => filter_var($email, FILTER_VALIDATE_EMAIL)
        );

        if (empty($destinatariosInternos) && empty($emailsExternos)) {
            $this->addError('envioDestinatariosSeleccionados', 'Debe seleccionar al menos un destinatario o ingresar un email externo');
            return;
        }

        try {
            $emailsEnviados = 0;
            $emailsErrores = 0;
            $plantilla = PlantillaCorreo::find($this->envioPlantillaId);

            // Enviar a destinatarios internos
            foreach ($destinatariosInternos as $destinatarioId) {
                $destinatario = DestinatarioCorreo::with('user')->find($destinatarioId);
                if ($destinatario) {
                    $email = $destinatario->es_externo ? $destinatario->email_externo : $destinatario->user->email;
                    $nombre = $destinatario->es_externo 
                        ? $destinatario->nombre_externo 
                        : $destinatario->user->name;
                    
                    if ($email) {
                        try {
                            // Preparar datos para reemplazo de variables
                            $datosDestinatario = [
                                'nombre' => $nombre,
                                'email' => $email,
                                'fecha' => now()->format('d/m/Y'),
                                'periodo' => now()->format('Y'),
                                'total_clases' => '0',
                                'clases_no_realizadas' => '0',
                                'porcentaje' => '0%',
                            ];

                            // Reemplazar variables en asunto y contenido
                            $asuntoPersonalizado = $this->reemplazarVariables($this->envioAsunto, $datosDestinatario);
                            $contenidoPersonalizado = $this->reemplazarVariables($this->envioContenido, $datosDestinatario);

                            Mail::to($email)->send(new CorreoPersonalizado(
                                $asuntoPersonalizado,
                                $contenidoPersonalizado,
                                $nombre
                            ));
                            $emailsEnviados++;
                        } catch (\Exception $e) {
                            $emailsErrores++;
                            Log::error("Error al enviar correo a {$email}: " . $e->getMessage());
                        }
                    }
                }
            }

            // Enviar a emails externos
            foreach ($emailsExternos as $email) {
                try {
                    // Preparar datos para reemplazo de variables (solo datos básicos para externos)
                    $datosExternos = [
                        'nombre' => $email, // Usar email como nombre si no tiene
                        'email' => $email,
                        'fecha' => now()->format('d/m/Y'),
                        'periodo' => now()->format('Y'),
                        'total_clases' => '0',
                        'clases_no_realizadas' => '0',
                        'porcentaje' => '0%',
                    ];

                    // Reemplazar variables
                    $asuntoPersonalizado = $this->reemplazarVariables($this->envioAsunto, $datosExternos);
                    $contenidoPersonalizado = $this->reemplazarVariables($this->envioContenido, $datosExternos);

                    Mail::to($email)->send(new CorreoPersonalizado(
                        $asuntoPersonalizado,
                        $contenidoPersonalizado
                    ));
                    $emailsEnviados++;
                } catch (\Exception $e) {
                    $emailsErrores++;
                    Log::error("Error al enviar correo a {$email}: " . $e->getMessage());
                }
            }

            $this->resetEnvioForm();
            
            if ($emailsErrores > 0) {
                $this->dispatch('show-error', ['message' => "Se enviaron {$emailsEnviados} correos. {$emailsErrores} fallaron. Revisa los logs para más detalles."]);
            } else {
                $this->dispatch('show-success', ['message' => "Se enviaron {$emailsEnviados} correos exitosamente"]);
            }
        } catch (\Exception $e) {
            $this->dispatch('show-error', ['message' => 'Error al enviar correos: ' . $e->getMessage()]);
        }
    }

    public function guardarEmailsExternos()
    {
        $emailsExternos = array_filter(
            array_map('trim', explode(',', $this->envioDestinatariosExternos)),
            fn($email) => filter_var($email, FILTER_VALIDATE_EMAIL)
        );

        if (empty($emailsExternos)) {
            $this->dispatch('show-error', ['message' => 'No hay emails válidos para guardar']);
            return;
        }

        $guardados = 0;
        foreach ($emailsExternos as $email) {
            // Verificar si ya existe
            $existe = DestinatarioCorreo::where('email_externo', $email)->exists();
            if (!$existe) {
                DestinatarioCorreo::create([
                    'es_externo' => true,
                    'email_externo' => $email,
                    'nombre_externo' => $email, // Por defecto usar el email como nombre
                    'activo' => true,
                ]);
                $guardados++;
            }
        }

        if ($guardados > 0) {
            $this->dispatch('show-success', ['message' => "Se guardaron {$guardados} destinatarios externos"]);
        } else {
            $this->dispatch('show-error', ['message' => 'Todos los emails ya están registrados']);
        }
    }

    public function resetEnvioForm()
    {
        $this->reset([
            'envioPlantillaId',
            'envioDestinatariosSeleccionados',
            'envioAsunto',
            'envioContenido',
            'envioDestinatariosExternos'
        ]);
        $this->resetValidation();
    }

    // ============ RENDER ============

    public function render()
    {
        $tiposCorreos = TipoCorreoMasivo::query()
            ->when($this->tipoSearch, function($query) {
                $query->where(function($q) {
                    $q->where('nombre', 'like', '%' . $this->tipoSearch . '%')
                      ->orWhere('codigo', 'like', '%' . $this->tipoSearch . '%')
                      ->orWhere('descripcion', 'like', '%' . $this->tipoSearch . '%');
                });
            })
            ->orderBy('tipo', 'asc')
            ->orderBy('nombre', 'asc')
            ->paginate(10, ['*'], 'tiposPage');

        $destinatarios = DestinatarioCorreo::with('user')
            ->buscar($this->destinatarioSearch)
            ->when($this->filtroTipoUsuario, function($query) {
                $query->porTipoUsuario($this->filtroTipoUsuario);
            })
            ->orderBy('created_at', 'desc')
            ->paginate(10, ['*'], 'destinatariosPage');

        $plantillas = PlantillaCorreo::with(['tipoCorreo', 'creador'])
            ->when($this->plantillaSearch, function($query) {
                $query->where(function($q) {
                    $q->where('nombre', 'like', '%' . $this->plantillaSearch . '%')
                      ->orWhere('asunto', 'like', '%' . $this->plantillaSearch . '%');
                });
            })
            ->orderBy('created_at', 'desc')
            ->paginate(10, ['*'], 'plantillasPage');

        $usuarios = User::orderBy('name')->get();

        // Para el modal de asignaciones
        $tipoSeleccionado = $this->selectedTipoId
            ? TipoCorreoMasivo::with('destinatarios')->find($this->selectedTipoId)
            : null;

        $todosDestinatarios = DestinatarioCorreo::with('user')->activos()->get();
        
        $tiposCorreosParaPlantilla = TipoCorreoMasivo::activos()->orderBy('nombre')->get();

        return view('livewire.correos-masivos-manager', [
            'tiposCorreos' => $tiposCorreos,
            'destinatarios' => $destinatarios,
            'plantillas' => $plantillas,
            'usuarios' => $usuarios,
            'tipoSeleccionado' => $tipoSeleccionado,
            'todosDestinatarios' => $todosDestinatarios,
            'tiposCorreosParaPlantilla' => $tiposCorreosParaPlantilla,
        ]);
    }
}