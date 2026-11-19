<div class="p-6">

    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Administración de Correos Masivos</h1>
            <p class="text-sm text-gray-600 mt-1">Gestiona tipos de correos y destinatarios autorizados</p>
        </div>
    </div>

    <!-- Tabs -->
    <div class="border-b border-gray-200 mb-6">
        <nav class="-mb-px flex space-x-8" aria-label="Tabs">
            <button wire:click="$set('tab', 'tipos')"
                    class="@if($tab === 'tipos') border-indigo-500 text-indigo-600 @else border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 @endif whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-colors">
                <i class="fas fa-envelope mr-2"></i>
                Tipos de Correos
            </button>
            <button wire:click="$set('tab', 'destinatarios')"
                    class="@if($tab === 'destinatarios') border-indigo-500 text-indigo-600 @else border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 @endif whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-colors">
                <i class="fas fa-users mr-2"></i>
                Destinatarios
            </button>
            <button wire:click="$set('tab', 'plantillas')"
                    class="@if($tab === 'plantillas') border-indigo-500 text-indigo-600 @else border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 @endif whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-colors">
                <i class="fas fa-file-alt mr-2"></i>
                Plantillas
            </button>
            <button wire:click="$set('tab', 'enviar')"
                    class="@if($tab === 'enviar') border-indigo-500 text-indigo-600 @else border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 @endif whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-colors">
                <i class="fas fa-paper-plane mr-2"></i>
                Enviar Correos
            </button>
        </nav>
    </div>

    <!-- Contenido según Tab -->
    @if($tab === 'tipos')
        @include('livewire.partials.tipos-correos-tab')
    @elseif($tab === 'destinatarios')
        @include('livewire.partials.destinatarios-correos-tab')
    @elseif($tab === 'plantillas')
        @include('livewire.partials.plantillas-correos-tab')
    @else
        @include('livewire.partials.enviar-correos-tab')
    @endif

    <!-- Modal de Asignaciones -->
    @if($showAsignacionModal && $tipoSeleccionado)
        <div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50" wire:click="closeAsignacionModal">
            <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-1/2 shadow-lg rounded-md bg-white" wire:click.stop>

                <!-- Header del Modal -->
                <div class="flex justify-between items-start mb-4">
                    <div>
                        <h3 class="text-lg font-medium text-gray-900">
                            Asignar Destinatarios
                        </h3>
                        <p class="text-sm text-gray-600 mt-1">
                            <strong>Tipo de Correo:</strong> {{ $tipoSeleccionado->nombre }}
                        </p>
                    </div>
                    <button wire:click="closeAsignacionModal" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>

                <!-- Lista de Destinatarios -->
                <div class="max-h-96 overflow-y-auto">
                    <div class="space-y-2">
                        @forelse($todosDestinatarios as $destinatario)
                            @php
                                $asignado = $tipoSeleccionado->destinatarios->contains($destinatario->id);
                                $habilitado = $asignado
                                    ? $tipoSeleccionado->destinatarios->find($destinatario->id)->pivot->habilitado
                                    : false;
                            @endphp
                            <div class="flex items-center justify-between p-3 border rounded-lg hover:bg-gray-50 transition-colors">
                                <div class="flex-1">
                                    <div class="font-medium text-gray-900">
                                        {{ $destinatario->user->name ?? 'N/A' }}
                                    </div>
                                    <div class="text-sm text-gray-600">
                                        {{ $destinatario->user->email ?? 'N/A' }}
                                        @if($destinatario->rol)
                                            <span class="ml-2 text-indigo-600">• {{ $destinatario->rol }}</span>
                                        @endif
                                    </div>
                                </div>
                                <div class="flex items-center">
                                    <button
                                        wire:click="toggleAsignacion({{ $tipoSeleccionado->id }}, {{ $destinatario->id }})"
                                        class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 @if($habilitado) bg-indigo-600 @else bg-gray-200 @endif"
                                        role="switch"
                                        aria-checked="{{ $habilitado ? 'true' : 'false' }}">
                                        <span class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out @if($habilitado) translate-x-5 @else translate-x-0 @endif"></span>
                                    </button>
                                    <span class="ml-3 text-sm @if($habilitado) text-indigo-600 font-medium @else text-gray-500 @endif">
                                        {{ $habilitado ? 'Habilitado' : 'Deshabilitado' }}
                                    </span>
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-8 text-gray-500">
                                <i class="fas fa-inbox text-4xl mb-2"></i>
                                <p>No hay destinatarios registrados</p>
                            </div>
                        @endforelse
                    </div>
                </div>

                <!-- Footer del Modal -->
                <div class="mt-4 flex justify-end">
                    <button wire:click="closeAsignacionModal"
                            class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 transition-colors">
                        <i class="fas fa-check mr-2"></i>
                        Listo
                    </button>
                </div>
            </div>
        </div>
    @endif

    <!-- Modal de Vista Previa de Plantilla -->
    <div id="modalVistaPreviaPlantilla" class="fixed inset-0 bg-gray-900 bg-opacity-75 hidden z-[9999] overflow-y-auto" style="backdrop-filter: blur(4px);" onclick="cerrarModalVistaPrevia(event)">
        <div class="flex min-h-screen items-center justify-center p-4">
            <div class="relative bg-white rounded-lg shadow-2xl w-full max-w-5xl" style="max-height: 90vh; overflow-y: auto;" onclick="event.stopPropagation()">
                
                <!-- Header del Modal -->
                <div class="sticky top-0 bg-gradient-to-r from-indigo-600 to-purple-600 text-white px-6 py-4 rounded-t-lg z-10 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3">
                    <div class="flex items-center gap-3">
                        <div class="bg-white bg-opacity-20 rounded-lg p-2">
                            <i class="fas fa-eye text-2xl"></i>
                        </div>
                        <div>
                            <h3 class="text-xl font-bold" id="modalVistaPreviaTitulo">Vista Previa de Plantilla</h3>
                            <p class="text-sm opacity-90">Vista previa del correo con datos de ejemplo</p>
                        </div>
                    </div>
                    <div class="flex gap-2">
                        <a id="btnDescargarPdfPlantilla" href="#" target="_blank"
                           class="inline-flex items-center px-4 py-2 bg-white text-indigo-600 rounded-lg hover:bg-indigo-50 transition-all shadow-md text-sm font-medium">
                            <i class="fas fa-download mr-2"></i>
                            Descargar PDF
                        </a>
                        <button onclick="cerrarModalVistaPrevia()" 
                                class="text-white hover:bg-white hover:bg-opacity-20 rounded-lg p-2 transition-all">
                            <i class="fas fa-times text-xl"></i>
                        </button>
                    </div>
                </div>

                <!-- Contenido del Modal -->
                <div class="p-6 bg-gray-50">
                    <div id="contenidoVistaPreviaPlantilla" class="bg-white rounded-lg shadow-inner border border-gray-200 p-4 min-h-[500px]">
                        <div class="flex items-center justify-center h-full">
                            <div class="text-center">
                                <div class="inline-block animate-spin rounded-full h-12 w-12 border-4 border-indigo-200 border-t-indigo-600 mb-3"></div>
                                <p class="text-gray-700 font-medium">Cargando vista previa...</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Footer del Modal -->
                <div class="sticky bottom-0 bg-gradient-to-r from-gray-50 to-gray-100 px-6 py-4 rounded-b-lg border-t border-gray-200 flex flex-col sm:flex-row justify-between items-center gap-3">
                    <div class="text-sm text-gray-700 flex items-center gap-2">
                        <div class="bg-indigo-100 text-indigo-600 rounded-full p-1.5">
                            <i class="fas fa-info-circle text-sm"></i>
                        </div>
                        <span>Esta es una vista previa con datos de ejemplo</span>
                    </div>
                    <button onclick="cerrarModalVistaPrevia()" 
                            class="px-6 py-2.5 bg-gradient-to-r from-indigo-600 to-purple-600 text-white rounded-lg hover:from-indigo-700 hover:to-purple-700 transition-all shadow-md font-medium">
                        <i class="fas fa-times mr-2"></i>
                        Cerrar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div wire:ignore>
        <script>
        window.CorreosMasivosConfig = {
            componentId: @json($this->getId())
        };
        </script>
        @vite(['resources/js/views/livewire/correos-masivos-manager.js'])
    </div>
</div>
