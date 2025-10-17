<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    <!-- Formulario de Envío (Izquierda - 2 columnas) -->
    <div class="lg:col-span-2">
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h3 class="text-lg font-medium text-gray-900 flex items-center">
                        <i class="fas fa-paper-plane mr-2 text-indigo-600"></i>
                        Enviar Correo Masivo
                    </h3>
                    <p class="text-sm text-gray-600 mt-1">Selecciona una plantilla y destinatarios para enviar</p>
                </div>
            </div>

            <form wire:submit.prevent="enviarCorreos">
                <!-- Seleccionar Plantilla -->
                <div class="mb-6">
                    <label for="envio-plantilla" class="block text-sm font-medium text-gray-700 mb-2">
                        Seleccionar Plantilla <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <select wire:model.live="envioPlantillaId"
                                id="envio-plantilla"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="">Seleccionar plantilla...</option>
                            @foreach($plantillas as $plantilla)
                                <option value="{{ $plantilla->id }}">
                                    {{ $plantilla->nombre }} - {{ $plantilla->asunto }}
                                </option>
                            @endforeach
                        </select>
                        @if($envioPlantillaId)
                            <button type="button"
                                    wire:click="cargarPlantillaParaEnvio({{ $envioPlantillaId }})"
                                    class="absolute right-2 top-2 px-3 py-1 text-xs bg-indigo-100 text-indigo-700 rounded hover:bg-indigo-200">
                                <i class="fas fa-sync-alt mr-1"></i>Cargar
                            </button>
                        @endif
                    </div>
                    @error('envioPlantillaId')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Asunto -->
                <div class="mb-6">
                    <label for="envio-asunto" class="block text-sm font-medium text-gray-700 mb-2">
                        Asunto <span class="text-red-500">*</span>
                    </label>
                    <input type="text"
                           wire:model="envioAsunto"
                           id="envio-asunto"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500"
                           placeholder="Asunto del correo">
                    @error('envioAsunto')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Contenido -->
                <div class="mb-6">
                    <label for="envio-contenido" class="block text-sm font-medium text-gray-700 mb-2">
                        Contenido <span class="text-red-500">*</span>
                    </label>

                    <!-- Variables Disponibles -->
                    <div class="mb-3 p-3 bg-blue-50 border border-blue-200 rounded-lg">
                        <p class="text-xs font-medium text-blue-900 mb-2">
                            <i class="fas fa-magic mr-1"></i>
                            Variables disponibles (se reemplazan automáticamente):
                        </p>
                        <div class="flex flex-wrap gap-2">
                            <span class="inline-flex items-center px-2 py-1 bg-white border border-blue-300 rounded text-xs text-blue-900">
                                <code>@{{nombre}}</code>
                            </span>
                            <span class="inline-flex items-center px-2 py-1 bg-white border border-blue-300 rounded text-xs text-blue-900">
                                <code>@{{email}}</code>
                            </span>
                            <span class="inline-flex items-center px-2 py-1 bg-white border border-blue-300 rounded text-xs text-blue-900">
                                <code>@{{fecha}}</code>
                            </span>
                            <span class="inline-flex items-center px-2 py-1 bg-white border border-blue-300 rounded text-xs text-blue-900">
                                <code>@{{periodo}}</code>
                            </span>
                        </div>
                    </div>

                    <div class="border border-gray-300 rounded-md">
                        <textarea wire:model="envioContenido"
                                  id="envio-contenido"
                                  rows="12"
                                  class="w-full px-3 py-2 border-0 rounded-md focus:ring-indigo-500 focus:border-indigo-500"
                                  placeholder="Contenido HTML del correo...&#10;&#10;Puedes usar variables como: @{{nombre}}, @{{email}}, @{{fecha}}, etc."></textarea>
                    </div>
                    @error('envioContenido')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                    <p class="mt-2 text-xs text-gray-500">
                        <i class="fas fa-info-circle mr-1"></i>
                        Puedes usar HTML y las variables se reemplazarán automáticamente por los datos del destinatario
                    </p>
                </div>

                <!-- Destinatarios Externos (Emails adicionales) -->
                <div class="mb-6">
                    <label for="envio-externos" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-envelope mr-1"></i>
                        Destinatarios Externos (Opcional)
                    </label>
                    <textarea wire:model="envioDestinatariosExternos"
                              id="envio-externos"
                              rows="3"
                              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500"
                              placeholder="email1@ejemplo.com, email2@ejemplo.com, ..."></textarea>
                    <p class="mt-1 text-xs text-gray-500">
                        Separa múltiples emails con comas. Estos correos se enviarán además de los destinatarios seleccionados.
                    </p>
                    <div class="mt-2">
                        <button type="button"
                                wire:click="guardarEmailsExternos"
                                class="text-sm text-indigo-600 hover:text-indigo-800 font-medium">
                            <i class="fas fa-save mr-1"></i>
                            Guardar estos emails como destinatarios externos
                        </button>
                    </div>
                </div>

                <!-- Botones de Acción -->
                <div class="flex gap-3 pt-4 border-t border-gray-200">
                    <button type="submit"
                            class="flex-1 px-6 py-3 bg-gradient-to-r from-indigo-600 to-purple-600 text-white rounded-md hover:from-indigo-700 hover:to-purple-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 font-medium shadow-md transition-all">
                        <i class="fas fa-paper-plane mr-2"></i>
                        Enviar Correos
                    </button>
                    <button type="button"
                            wire:click="resetEnvioForm"
                            class="px-6 py-3 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 transition-colors">
                        <i class="fas fa-times mr-2"></i>
                        Limpiar
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Lista de Destinatarios (Derecha - 1 columna) -->
    <div class="lg:col-span-1">
        <div class="bg-white rounded-lg shadow p-6 sticky top-4">
            <h3 class="text-lg font-medium text-gray-900 mb-4 flex items-center">
                <i class="fas fa-users mr-2 text-indigo-600"></i>
                Destinatarios Registrados
            </h3>

            <!-- Buscador Mini -->
            <div class="mb-4">
                <div class="relative">
                    <input type="text"
                           wire:model.live="destinatarioSearch"
                           placeholder="Buscar..."
                           class="w-full pl-8 pr-3 py-2 text-sm border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500">
                    <i class="fas fa-search absolute left-2.5 top-2.5 text-gray-400 text-sm"></i>
                </div>
            </div>

            <!-- Lista con checkboxes -->
            <div class="space-y-2 max-h-[600px] overflow-y-auto">
                @forelse($destinatarios as $destinatario)
                    <label class="flex items-start p-3 border border-gray-200 rounded-lg hover:bg-gray-50 cursor-pointer transition-colors">
                        <input type="checkbox"
                               wire:model="envioDestinatariosSeleccionados"
                               value="{{ $destinatario->id }}"
                               class="mt-1 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                        <div class="ml-3 flex-1">
                            <div class="text-sm font-medium text-gray-900 flex items-center gap-1">
                                @if($destinatario->es_externo)
                                    <i class="fas fa-external-link-alt text-purple-600 text-xs"></i>
                                @endif
                                {{ $destinatario->nombre_completo }}
                            </div>
                            <div class="text-xs text-gray-500 mt-0.5">
                                @if($destinatario->es_externo)
                                    {{ $destinatario->email_externo }}
                                @else
                                    {{ $destinatario->user->email ?? 'N/A' }}
                                @endif
                            </div>
                            @if($destinatario->rol)
                                <div class="text-xs text-indigo-600 mt-0.5">
                                    {{ $destinatario->rol }}
                                </div>
                            @endif
                        </div>
                    </label>
                @empty
                    <div class="text-center py-8 text-gray-500">
                        <i class="fas fa-inbox text-3xl mb-2"></i>
                        <p class="text-sm">No hay destinatarios</p>
                    </div>
                @endforelse
            </div>

            <!-- Resumen de Selección -->
            @if(count($envioDestinatariosSeleccionados) > 0)
                <div class="mt-4 p-3 bg-indigo-50 border border-indigo-200 rounded-lg">
                    <p class="text-sm font-medium text-indigo-900">
                        <i class="fas fa-check-circle mr-1"></i>
                        {{ count($envioDestinatariosSeleccionados) }} destinatario(s) seleccionado(s)
                    </p>
                    <button type="button"
                            wire:click="$set('envioDestinatariosSeleccionados', [])"
                            class="mt-2 text-xs text-indigo-600 hover:text-indigo-800">
                        <i class="fas fa-times-circle mr-1"></i>
                        Limpiar selección
                    </button>
                </div>
            @endif

            @error('envioDestinatariosSeleccionados')
                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>
    </div>

</div>
