<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\TipoCorreoMasivo;
use App\Models\DestinatarioCorreo;
use App\Models\PlantillaCorreo;
use App\Models\User;
use Illuminate\Support\Str;

class CorreosMasivosManager extends Component
{
    use WithPagination;

    // Variables para la pestaña activa
    public $tab = 'tipos'; // 'tipos', 'destinatarios', 'plantillas'

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

        'destinatarioUserId' => 'required|exists:users,run',
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
        $this->validate([
            'destinatarioUserId' => $this->rules['destinatarioUserId'],
            'destinatarioRol' => $this->rules['destinatarioRol'],
            'destinatarioCargo' => $this->rules['destinatarioCargo'],
            'destinatarioActivo' => $this->rules['destinatarioActivo'],
        ]);

        if ($this->editingDestinatarioId) {
            $destinatario = DestinatarioCorreo::find($this->editingDestinatarioId);
            $destinatario->update([
                'user_id' => $this->destinatarioUserId,
                'rol' => $this->destinatarioRol,
                'cargo' => $this->destinatarioCargo,
                'activo' => $this->destinatarioActivo,
            ]);
            $message = 'Destinatario actualizado exitosamente';
        } else {
            // Verificar si ya existe este user_id
            $existente = DestinatarioCorreo::where('user_id', $this->destinatarioUserId)->first();
            if ($existente) {
                $this->addError('destinatarioUserId', 'Este usuario ya está registrado como destinatario');
                return;
            }

            DestinatarioCorreo::create([
                'user_id' => $this->destinatarioUserId,
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
        $this->destinatarioUserId = $destinatario->user_id;
        $this->destinatarioRol = $destinatario->rol;
        $this->destinatarioCargo = $destinatario->cargo;
        $this->destinatarioActivo = $destinatario->activo;
    }

    public function deleteDestinatario($id)
    {
        $destinatario = DestinatarioCorreo::with('user')->findOrFail($id);
        $this->dispatch('confirm-delete-destinatario', [
            'id' => $id,
            'nombre' => $destinatario->user->name ?? 'N/A',
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
        $this->reset(['destinatarioUserId', 'destinatarioRol', 'destinatarioCargo', 'destinatarioActivo', 'editingDestinatarioId']);
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