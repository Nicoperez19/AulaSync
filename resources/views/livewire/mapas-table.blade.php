<div class="flex flex-col h-full">
    <div class="flex flex-col gap-4 mb-4 md:flex-row md:items-center md:justify-between">
        <div class="relative w-full md:w-1/2">
            <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                </svg>
            </div>
            <input type="text"
                   wire:model.live.debounce.300ms="search"
                   placeholder="Buscar por nombre de mapa, piso o facultad..."
                   class="w-full py-2 pl-10 pr-4 text-sm text-gray-900 bg-white border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white dark:placeholder-gray-400" />
            @if(!empty($search))
                <button wire:click="$set('search', '')" class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            @endif
        </div>
        <div class="flex items-center text-sm text-gray-500 dark:text-gray-400">
            <span>Total: <strong>{{ $mapas->count() }}</strong> mapas</span>
        </div>
    </div>

    <div class="overflow-x-auto border border-gray-200 rounded-lg shadow-md dark:border-gray-700">
        <table class="w-full text-sm text-center border-collapse table-auto min-w-max">
            <thead class="text-white bg-light-cloud-blue dark:bg-black dark:text-white">
                <tr>
                    <th class="px-4 py-2">Nombre Mapa</th>
                    <th class="px-4 py-2">Ver</th>
                    <th class="px-4 py-2">Editar</th>
                    <th class="px-4 py-2">Eliminar</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($mapas as $mapa)
                    <tr wire:key="mapa-row-{{ $mapa->id_mapa }}" class="{{ $loop->index % 2 === 0 ? 'bg-white' : 'bg-gray-50'  }}">
                        <td class="p-3 border border-white dark:border-white whitespace-nowrap">
                            {{ $mapa->nombre_mapa }}
                        </td>
                        <td class="p-3 border border-white dark:border-white whitespace-nowrap">
                            @if($mapa->ruta_mapa)
                                <img src="{{ asset('storage/' . $mapa->ruta_mapa) }}" alt="Mapa {{ $mapa->nombre_mapa }}" class="h-12 w-auto rounded shadow border inline-block align-middle mr-2">
                            @endif
                            <x-button variant="ghost" class="text-blue-500" wire:click="verMapa('{{ $mapa->id_mapa }}')">
                                Ver
                            </x-button>
                        </td>
                        <td class="p-3 border border-white dark:border-white whitespace-nowrap">
                            <a href="{{ route('mapas.edit', $mapa->id_mapa) }}" class="inline-flex items-center px-3 py-1 text-xs font-semibold text-white bg-yellow-500 rounded hover:bg-yellow-600">
                                <i class="fa-solid fa-pen mr-1"></i> Editar
                            </a>
                        </td>
                        <td class="p-3 border border-white dark:border-white whitespace-nowrap">
                            <x-button variant="ghost" class="text-red-600" wire:click="confirmarEliminarMapa('{{ $mapa->id_mapa }}')">
                                Eliminar
                            </x-button>
                        </td>
                        
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="p-6 text-gray-500 dark:text-gray-400">
                            No se encontraron mapas{{ !empty($search) ? ' para "' . $search . '"' : '' }}.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Modal con diseño de horario -->
    @if ($mostrarModal && $mapaSeleccionado)
        <div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-[9999] flex items-center justify-center p-8"
            wire:click="cerrarModal">
            <div
                class="flex flex-col w-full max-h-[90vh] mx-2 overflow-hidden bg-white rounded-lg shadow-lg max-w-6xl md:mx-8">

                <div id="modalHeader"
                    class="relative flex flex-col gap-4 p-6 bg-red-700 md:flex-row md:items-center md:justify-between">
                    <span
                        class="absolute top-0 left-0 w-32 h-32 -translate-x-1/2 -translate-y-1/2 bg-white rounded-full pointer-events-none bg-opacity-10"></span>
                    <span
                        class="absolute top-0 right-0 w-32 h-32 translate-x-1/2 -translate-y-1/2 bg-white rounded-full pointer-events-none bg-opacity-10"></span>

                    <div class="flex items-center flex-1 min-w-0 gap-4">
                        <div class="flex flex-col items-center justify-center flex-shrink-0">
                            <div class="p-3 mb-1 bg-white rounded-full bg-opacity-20">
                                <i class="text-2xl text-white fa-solid fa-calendar-days"></i>
                            </div>
                        </div>
                        <div class="flex flex-col min-w-0">
                            <h1 class="text-2xl font-bold text-white truncate">{{ $mapaSeleccionado->nombre_mapa }}</h1>
                            <div class="flex items-center gap-2 mt-1">
                                <span class="text-base truncate text-white/80">Visualización del Mapa</span>
                                <span class="text-base text-white/80">•</span>
                                <span class="text-base font-semibold text-white/80">{{ now()->format('Y') }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center self-start flex-shrink-0 gap-3 md:self-center">
                        <button
                            class="border border-white text-white px-3 py-1 rounded-lg font-semibold hover:bg-white hover:text-red-700 transition text-sm">
                            Exportar PDF
                        </button>
                        <button wire:click="cerrarModal"
                            class="ml-2 text-3xl font-bold text-white hover:text-gray-200 transition-colors duration-200 cursor-pointer"
                            title="Cerrar modal (Esc)"
                            aria-label="Cerrar modal">&times;</button>
                    </div>
                </div>

                <!-- Contenido del modal -->
                <div class="flex-1 p-4 bg-white overflow-y-auto">
                    <div class="flex justify-center mb-4">
                        <img src="{{ asset('storage/' . $mapaSeleccionado->ruta_mapa) }}"
                            alt="Mapa {{ $mapaSeleccionado->nombre_mapa }}"
                            class="h-auto max-w-full border rounded-lg shadow-md">
                    </div>

                    <!-- Información del mapa -->
                    <div class="p-3 rounded-lg bg-gray-50">
                        <h4 class="mb-2 text-base font-semibold text-gray-800">Información del Mapa</h4>
                        <div class="grid grid-cols-1 gap-2 text-sm md:grid-cols-2">
                            <div>
                                <span class="font-medium text-gray-600">Nombre:</span>
                                <span class="ml-2 text-gray-800">{{ $mapaSeleccionado->nombre_mapa ?? 'N/A' }}</span>
                            </div>
                            @if($mapaSeleccionado->piso)
                                <div>
                                    <span class="font-medium text-gray-600">Piso:</span>
                                    <span
                                        class="ml-2 text-gray-800">{{ $mapaSeleccionado->piso->numero_piso ?? 'N/A' }}</span>
                                </div>
                            @endif
                            @if($mapaSeleccionado->piso && $mapaSeleccionado->piso->facultad)
                                <div>
                                    <span class="font-medium text-gray-600">Facultad:</span>
                                    <span
                                        class="ml-2 text-gray-800">{{ $mapaSeleccionado->piso->facultad->nombre_facultad ?? 'N/A' }}</span>
                                </div>
                            @endif
                            <div>
                                <span class="font-medium text-gray-600">Fecha de creación:</span>
                                <span
                                    class="ml-2 text-gray-800">{{ $mapaSeleccionado->created_at ? $mapaSeleccionado->created_at->format('d/m/Y H:i') : 'N/A' }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Modal de confirmación para eliminar mapa (Diseño Institucional Unificado) -->
    @if($mostrarModalEliminar && $mapaAEliminar)
        <div class="fixed inset-0 bg-gray-900/60 backdrop-blur-xs overflow-y-auto h-full w-full z-[10000] flex items-center justify-center p-4"
             wire:click.self="cerrarEliminarModal">
            <div class="flex flex-col w-full max-w-lg overflow-hidden bg-white rounded-2xl shadow-2xl border border-gray-100 transform transition-all">

                <!-- Encabezado rojo institucional tipo banner con círculos decorativos -->
                <div id="modalHeaderEliminar" class="relative flex items-center justify-between p-6 bg-red-700 overflow-hidden">
                    <!-- Círculos decorativos -->
                    <span class="absolute top-0 left-0 w-32 h-32 -translate-x-1/2 -translate-y-1/2 bg-white rounded-full pointer-events-none bg-opacity-10"></span>
                    <span class="absolute top-0 right-0 w-32 h-32 translate-x-1/2 -translate-y-1/2 bg-white rounded-full pointer-events-none bg-opacity-10"></span>

                    <div class="flex items-center min-w-0 gap-4 z-10">
                        <div class="flex items-center justify-center flex-shrink-0">
                            <div class="p-3 bg-white rounded-full bg-opacity-20 shadow-inner">
                                <i class="text-2xl text-white fa-solid fa-trash-can"></i>
                            </div>
                        </div>
                        <div class="flex flex-col min-w-0">
                            <h2 class="text-xl font-bold text-white truncate">Confirmar Eliminación</h2>
                            <div class="flex items-center gap-2 mt-0.5">
                                <span class="text-xs truncate text-white/80">Gestión de Planos</span>
                                <span class="text-xs text-white/80">•</span>
                                <span class="text-xs font-semibold text-white/80">{{ now()->format('Y') }}</span>
                            </div>
                        </div>
                    </div>

                    <button wire:click="cerrarEliminarModal"
                        class="text-3xl font-bold text-white/80 hover:text-white transition-colors duration-150 cursor-pointer z-10 leading-none"
                        title="Cerrar modal (Esc)"
                        aria-label="Cerrar modal">&times;</button>
                </div>

                <!-- Contenido del modal -->
                <div class="p-6 bg-white space-y-4">
                    <!-- Advertencia visual -->
                    <div class="p-4 bg-amber-50 border border-amber-200 rounded-xl flex items-start gap-3">
                        <div class="p-2 bg-amber-100 text-amber-700 rounded-lg flex-shrink-0 mt-0.5">
                            <i class="fa-solid fa-triangle-exclamation text-lg"></i>
                        </div>
                        <div>
                            <h4 class="text-sm font-bold text-amber-900">¿Estás seguro de que deseas eliminar este mapa?</h4>
                            <p class="text-xs text-amber-800 mt-1 leading-relaxed">
                                Esta acción eliminará permanentemente la configuración del plano y desvinculará la imagen asignada. Esta acción no se puede deshacer.
                            </p>
                        </div>
                    </div>

                    <!-- Ficha del mapa a eliminar -->
                    <div class="p-4 bg-gray-50 border border-gray-200 rounded-xl">
                        <div class="flex items-center gap-4">
                            @if($mapaAEliminar->ruta_mapa)
                                <img src="{{ asset('storage/' . $mapaAEliminar->ruta_mapa) }}"
                                     alt="Vista previa mapa"
                                     class="w-20 h-16 object-cover rounded-lg border border-gray-300 shadow-xs flex-shrink-0">
                            @else
                                <div class="w-20 h-16 bg-gray-200 rounded-lg flex items-center justify-center text-gray-400 flex-shrink-0">
                                    <i class="fa-solid fa-map text-2xl"></i>
                                </div>
                            @endif
                            <div class="min-w-0 flex-1">
                                <h3 class="text-sm font-bold text-gray-900 truncate">{{ $mapaAEliminar->nombre_mapa }}</h3>
                                @if($mapaAEliminar->piso)
                                    <p class="text-xs text-gray-600 mt-1">
                                        <i class="fa-solid fa-building text-gray-400 mr-1"></i>
                                        <strong>Edificio / Piso:</strong> {{ $mapaAEliminar->piso->display_name ?? ('Piso ' . $mapaAEliminar->piso->numero_piso) }}
                                    </p>
                                @endif
                                @if($mapaAEliminar->piso && $mapaAEliminar->piso->facultad)
                                    <p class="text-xs text-gray-600 mt-0.5">
                                        <i class="fa-solid fa-graduation-cap text-gray-400 mr-1"></i>
                                        <strong>Facultad:</strong> {{ $mapaAEliminar->piso->facultad->nombre_facultad }}
                                    </p>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Footer con botones de acción -->
                <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex items-center justify-end gap-3 rounded-b-2xl">
                    <button type="button" wire:click="cerrarEliminarModal"
                        class="border border-gray-300 bg-white text-gray-700 hover:bg-gray-100 active:bg-gray-200 rounded-lg font-medium text-sm gap-2 inline-flex items-center justify-center shadow-xs px-4 py-2 cursor-pointer transition-all duration-150">
                        <i class="fa-solid fa-xmark"></i>
                        <span>Cancelar</span>
                    </button>
                    <button type="button" wire:click="eliminarMapa"
                        class="inline-flex items-center justify-center px-5 py-2 bg-red-700 hover:bg-red-800 text-white text-sm font-semibold rounded-lg shadow-sm hover:shadow transition-all duration-150 gap-2 cursor-pointer">
                        <i class="fa-solid fa-trash-can"></i>
                        <span>Sí, eliminar mapa</span>
                    </button>
                </div>
            </div>
        </div>
    @endif

    <script>
        // Escuchar eventos de Livewire
        document.addEventListener('livewire:init', () => {
            Livewire.on('modalClosed', () => {
                // El modal se cerró, limpiar cualquier estado adicional si es necesario
                console.log('Modal cerrado');
            });
            
            // Cerrar modales con la tecla Escape
            document.addEventListener('keydown', (event) => {
                if (event.key === 'Escape') {
                    if (@json($mostrarModal)) {
                        @this.call('cerrarModal');
                    }
                    if (@json($mostrarModalEliminar)) {
                        @this.call('cerrarEliminarModal');
                    }
                }
            });
        });
    </script>
</div>
