<div class="flex flex-col h-full">
    <div class="overflow-x-auto border border-gray-200 rounded-lg shadow-md dark:border-gray-700">
        <table class="w-full text-sm text-center border-collapse table-auto min-w-max">
            <thead class="text-white bg-light-cloud-blue dark:bg-black dark:text-white">
                <tr>
                    <th class="px-4 py-2">Nombre Mapa</th>
                    <th class="px-4 py-2">Ver</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($mapas as $mapa)
                    <tr class="{{ $loop->index % 2 === 0 ? 'bg-white' : 'bg-gray-50'  }}">
                        <td class="p-3 border border-white dark:border-white whitespace-nowrap">
                            {{ $mapa->nombre_mapa }}
                        </td>
                        <td class="p-3 border border-white dark:border-white whitespace-nowrap">
                            <x-button variant="ghost" class="text-blue-500" wire:click="verMapa('{{ $mapa->id_mapa }}')">
                                Ver
                            </x-button>
                        </td>
                    </tr>
                @endforeach
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

    <script>
        // Escuchar eventos de Livewire
        document.addEventListener('livewire:init', () => {
            Livewire.on('modalClosed', () => {
                // El modal se cerró, limpiar cualquier estado adicional si es necesario
                console.log('Modal cerrado');
            });
            
            // Agregar funcionalidad adicional para cerrar el modal con la tecla Escape
            document.addEventListener('keydown', (event) => {
                if (event.key === 'Escape' && @json($mostrarModal)) {
                    @this.cerrarModal();
                }
            });
        });
    </script>
</div>