<div>
    <!-- Header con botón para crear nueva plantilla -->
    <div class="mb-6 flex justify-between items-center">
        <div>
            <h2 class="text-xl font-semibold text-gray-900">Plantillas de Correos</h2>
            <p class="text-sm text-gray-600 mt-1">Crea y edita plantillas HTML personalizadas para tus correos masivos</p>
        </div>
        <button wire:click="showPlantillaEditorModal" 
                class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500">
            <i class="fas fa-plus mr-2"></i>
            Nueva Plantilla
        </button>
    </div>

    <div class="bg-white rounded-lg shadow">
        
        <!-- Buscador -->
        <div class="p-4 border-b border-gray-200">
            <div class="relative">
                <input type="text" 
                       wire:model.live="plantillaSearch"
                       placeholder="Buscar plantillas por nombre o asunto..."
                       class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500">
                <i class="fas fa-search absolute left-3 top-3 text-gray-400"></i>
            </div>
        </div>

        <!-- Lista de Plantillas -->
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Nombre
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Asunto
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Tipo de Correo
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Estado
                        </th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Acciones
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($plantillas as $plantilla)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4">
                                <div class="text-sm font-medium text-gray-900">
                                    {{ $plantilla->nombre }}
                                </div>
                                <div class="text-xs text-gray-500">
                                    Creada por: {{ $plantilla->creador->name ?? 'N/A' }}
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm text-gray-900">
                                    {{ Str::limit($plantilla->asunto, 50) }}
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($plantilla->tipoCorreo)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800">
                                        {{ $plantilla->tipoCorreo->nombre }}
                                    </span>
                                @else
                                    <span class="text-sm text-gray-400">Sin asignar</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($plantilla->activo)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        <i class="fas fa-check-circle mr-1"></i> Activa
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                        <i class="fas fa-times-circle mr-1"></i> Inactiva
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <div class="flex justify-end gap-2">
                                    <button onclick="abrirVistaPreviaPlantilla({{ $plantilla->id }}, '{{ addslashes($plantilla->nombre) }}')"
                                            class="text-purple-600 hover:text-purple-900 transition-colors"
                                            title="Vista Previa">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button wire:click="editPlantilla({{ $plantilla->id }})"
                                            class="text-indigo-600 hover:text-indigo-900 transition-colors"
                                            title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button wire:click="deletePlantilla({{ $plantilla->id }})"
                                            class="text-red-600 hover:text-red-900 transition-colors"
                                            title="Eliminar">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center text-gray-500">
                                <i class="fas fa-file-alt text-4xl mb-2"></i>
                                <p>No hay plantillas registradas</p>
                                <p class="text-sm mt-2">Crea tu primera plantilla haciendo clic en "Nueva Plantilla"</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Paginación -->
        @if($plantillas->hasPages())
            <div class="px-6 py-3 border-t border-gray-200">
                {{ $plantillas->links() }}
            </div>
        @endif
    </div>
</div>

<!-- Modal Editor de Plantillas -->
@if($showPlantillaEditor)
    <div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50" wire:click="resetPlantillaForm">
        <div class="relative top-10 mx-auto p-5 border w-11/12 max-w-5xl shadow-lg rounded-md bg-white" wire:click.stop>
            
            <!-- Header del Modal -->
            <div class="flex justify-between items-start mb-4">
                <div>
                    <h3 class="text-lg font-medium text-gray-900">
                        <i class="fas fa-file-alt mr-2"></i>
                        {{ $editingPlantillaId ? 'Editar' : 'Nueva' }} Plantilla de Correo
                    </h3>
                    <p class="text-sm text-gray-600 mt-1">
                        Crea contenido HTML personalizado con variables dinámicas
                    </p>
                </div>
                <button wire:click="resetPlantillaForm" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>

            <!-- Formulario -->
            <form wire:submit.prevent="savePlantilla" class="space-y-4">
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- Nombre -->
                    <div>
                        <label for="plantilla-nombre" class="block text-sm font-medium text-gray-700 mb-2">
                            Nombre <span class="text-red-500">*</span>
                        </label>
                        <input type="text" 
                               wire:model="plantillaNombre"
                               id="plantilla-nombre"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500"
                               placeholder="Ej: Plantilla Informe Semanal">
                        @error('plantillaNombre')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Tipo de Correo -->
                    <div>
                        <label for="plantilla-tipo" class="block text-sm font-medium text-gray-700 mb-2">
                            Tipo de Correo
                        </label>
                        <select wire:model="plantillaTipoCorreoId"
                                id="plantilla-tipo"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="">Sin asignar</option>
                            @foreach($tiposCorreosParaPlantilla as $tipo)
                                <option value="{{ $tipo->id }}">{{ $tipo->nombre }}</option>
                            @endforeach
                        </select>
                        @error('plantillaTipoCorreoId')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Asunto -->
                <div>
                    <label for="plantilla-asunto" class="block text-sm font-medium text-gray-700 mb-2">
                        Asunto del Correo <span class="text-red-500">*</span>
                    </label>
                    <input type="text" 
                           wire:model="plantillaAsunto"
                           id="plantilla-asunto"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500"
                           placeholder="Asunto del correo electrónico">
                    @error('plantillaAsunto')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Variables Disponibles -->
                <div class="bg-blue-50 border border-blue-200 rounded-md p-3">
                    <p class="text-sm font-medium text-blue-900 mb-2">
                        <i class="fas fa-info-circle mr-1"></i>
                        Variables Disponibles
                    </p>
                    <p class="text-xs text-blue-800 mb-2">
                        Haz clic en una variable para insertarla en el contenido. Se reemplazarán automáticamente al enviar el correo:
                    </p>
                    <div class="grid grid-cols-2 md:grid-cols-3 gap-2">
                        <button type="button"
                                onclick="insertarVariableEnEditor('@{{ nombre }}')"
                                class="inline-flex items-center justify-between px-3 py-2 bg-white border border-blue-300 rounded text-xs text-blue-900 hover:bg-blue-100 transition-colors">
                            <span class="font-medium">Nombre</span>
                            <code class="ml-2 text-xs bg-blue-100 px-1 rounded">@{{nombre}}</code>
                        </button>
                        <button type="button"
                                onclick="insertarVariableEnEditor('@{{ email }}')"
                                class="inline-flex items-center justify-between px-3 py-2 bg-white border border-blue-300 rounded text-xs text-blue-900 hover:bg-blue-100 transition-colors">
                            <span class="font-medium">Email</span>
                            <code class="ml-2 text-xs bg-blue-100 px-1 rounded">@{{email}}</code>
                        </button>
                        <button type="button"
                                onclick="insertarVariableEnEditor('@{{ fecha }}')"
                                class="inline-flex items-center justify-between px-3 py-2 bg-white border border-blue-300 rounded text-xs text-blue-900 hover:bg-blue-100 transition-colors">
                            <span class="font-medium">Fecha</span>
                            <code class="ml-2 text-xs bg-blue-100 px-1 rounded">@{{fecha}}</code>
                        </button>
                        <button type="button"
                                onclick="insertarVariableEnEditor('@{{ periodo }}')"
                                class="inline-flex items-center justify-between px-3 py-2 bg-white border border-blue-300 rounded text-xs text-blue-900 hover:bg-blue-100 transition-colors">
                            <span class="font-medium">Período</span>
                            <code class="ml-2 text-xs bg-blue-100 px-1 rounded">@{{periodo}}</code>
                        </button>
                        <button type="button"
                                onclick="insertarVariableEnEditor('@{{ total_clases }}')"
                                class="inline-flex items-center justify-between px-3 py-2 bg-white border border-blue-300 rounded text-xs text-blue-900 hover:bg-blue-100 transition-colors">
                            <span class="font-medium">Total Clases</span>
                            <code class="ml-2 text-xs bg-blue-100 px-1 rounded">@{{total_clases}}</code>
                        </button>
                        <button type="button"
                                onclick="insertarVariableEnEditor('@{{ clases_no_realizadas }}')"
                                class="inline-flex items-center justify-between px-3 py-2 bg-white border border-blue-300 rounded text-xs text-blue-900 hover:bg-blue-100 transition-colors">
                            <span class="font-medium">Clases No Realizadas</span>
                            <code class="ml-2 text-xs bg-blue-100 px-1 rounded">@{{clases_no_realizadas}}</code>
                        </button>
                        <button type="button"
                                onclick="insertarVariableEnEditor('@{{ porcentaje }}')"
                                class="inline-flex items-center justify-between px-3 py-2 bg-white border border-blue-300 rounded text-xs text-blue-900 hover:bg-blue-100 transition-colors">
                            <span class="font-medium">Porcentaje</span>
                            <code class="ml-2 text-xs bg-blue-100 px-1 rounded">@{{porcentaje}}</code>
                        </button>
                    </div>
                </div>

                <!-- Contenido HTML -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Contenido del Correo <span class="text-red-500">*</span>
                    </label>
                    <p class="text-xs text-gray-500 mb-2">
                        Usa el editor enriquecido para crear el contenido. El logo y firma se agregarán automáticamente.
                    </p>
                    
                    <!-- Editor Quill Container -->
                    <div wire:ignore>
                        <div id="quill-editor" style="height: 400px; background-color: white; border: 1px solid #d1d5db; border-radius: 0.375rem;"></div>
                    </div>
                    
                    <textarea id="plantilla-html-editor"
                              wire:model="plantillaContenidoHtml"
                              class="hidden"></textarea>
                    
                    @error('plantillaContenidoHtml')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

<style>
    /* Estilos para el editor Quill */
    #quill-editor .ql-container {
        font-family: Arial, Helvetica, sans-serif;
        font-size: 14px;
        height: calc(100% - 42px);
    }
    
    #quill-editor .ql-toolbar {
        background-color: #f9fafb;
        border-top-left-radius: 0.375rem;
        border-top-right-radius: 0.375rem;
    }
    
    #quill-editor .ql-editor {
        min-height: 350px;
    }
    
    #quill-editor .ql-editor.ql-blank::before {
        font-style: normal;
        color: #9ca3af;
    }
</style>

                <!-- Activo -->
                <div>
                    <label class="flex items-center">
                        <input type="checkbox" 
                               wire:model="plantillaActivo"
                               class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                        <span class="ml-2 text-sm text-gray-700">Plantilla activa</span>
                    </label>
                </div>

                <!-- Botones -->
                <div class="flex justify-end gap-3 pt-4 border-t">
                    <button type="button"
                            wire:click="resetPlantillaForm"
                            class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 transition-colors">
                        <i class="fas fa-times mr-2"></i>
                        Cancelar
                    </button>
                    <button type="submit"
                            class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <i class="fas fa-save mr-2"></i>
                        {{ $editingPlantillaId ? 'Actualizar' : 'Crear' }} Plantilla
                    </button>
                </div>
            </form>
        </div>
    </div>
@endif

@script
<script>
(function() {
    // Variable global para el editor Quill
    let quillEditor = null;
    let initAttempts = 0;
    const maxAttempts = 10;

    // Función para insertar variables en el editor
    window.insertarVariableEnEditor = function(variable) {
        if (quillEditor) {
            const range = quillEditor.getSelection(true);
            quillEditor.insertText(range.index, variable, 'user');
            quillEditor.setSelection(range.index + variable.length);
        } else {
            console.log('⚠️ Editor Quill no está inicializado');
        }
    }

    // Función para inicializar Quill
    function inicializarQuillEditor() {
        const editorContainer = document.getElementById('quill-editor');
        
        if (!editorContainer) {
            console.error('❌ Contenedor #quill-editor no encontrado en el DOM');
            
            // Reintentar si no se ha alcanzado el máximo
            if (initAttempts < maxAttempts) {
                initAttempts++;
                console.log(`🔄 Reintentando (${initAttempts}/${maxAttempts})...`);
                setTimeout(() => inicializarQuillEditor(), 200);
            }
            return;
        }
        
        // Verificar que el contenedor sea visible
        const rect = editorContainer.getBoundingClientRect();
        if (rect.height === 0 || rect.width === 0) {
            console.warn('⚠️ Contenedor no visible aún, esperando...');
            if (initAttempts < maxAttempts) {
                initAttempts++;
                setTimeout(() => inicializarQuillEditor(), 200);
            }
            return;
        }
        
        // Si ya existe un editor, destruirlo primero
        if (quillEditor) {
            console.log('🔄 Destruyendo editor Quill existente...');
            editorContainer.innerHTML = '';
            quillEditor = null;
        }
        
        console.log('🚀 Inicializando Quill Editor...');
        
        // Configuración de la barra de herramientas
        const toolbarOptions = [
            [{ 'header': [1, 2, 3, false] }],
            ['bold', 'italic', 'underline', 'strike'],
            [{ 'color': [] }, { 'background': [] }],
            [{ 'align': [] }],
            [{ 'list': 'ordered'}, { 'list': 'bullet' }],
            [{ 'indent': '-1'}, { 'indent': '+1' }],
            ['link', 'image'],
            ['blockquote', 'code-block'],
            ['clean']
        ];

        try {
            // Inicializar Quill
            quillEditor = new Quill('#quill-editor', {
                theme: 'snow',
                modules: {
                    toolbar: toolbarOptions
                },
                placeholder: 'Escribe aquí el contenido de tu correo...',
            });

            // Cargar contenido inicial si existe
            const contenidoInicial = @this.plantillaContenidoHtml || '';
            if (contenidoInicial && contenidoInicial.trim() !== '') {
                console.log('📄 Cargando contenido inicial...');
                quillEditor.root.innerHTML = contenidoInicial;
            }

            // Sincronizar cambios con Livewire
            quillEditor.on('text-change', function() {
                const html = quillEditor.root.innerHTML;
                @this.set('plantillaContenidoHtml', html);
            });
            
            console.log('✅ Quill Editor inicializado correctamente');
            initAttempts = 0; // Resetear contador
        } catch (error) {
            console.error('❌ Error al inicializar Quill:', error);
        }
    }

    // Escuchar evento de Livewire cuando se abre el modal del editor
    $wire.on('plantilla-editor-opened', () => {
        console.log('📩 Evento plantilla-editor-opened recibido');
        initAttempts = 0; // Resetear contador de intentos
        
        // Esperar a que el modal esté completamente renderizado
        setTimeout(() => {
            inicializarQuillEditor();
        }, 500);
    });
})();
</script>
@endscript

