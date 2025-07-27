<div>

    <div class="overflow-x-auto border border-gray-200 rounded-lg shadow-md dark:border-gray-700">
        <table class="w-full text-sm text-center border-collapse table-auto min-w-max">
            <thead class="text-white bg-light-cloud-blue dark:bg-black dark:text-white">
                <tr >
                    <th class="px-4 py-2">Nombre Mapa</th>
                    <th class="px-4 py-2">Ver</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($mapas as $mapa)
                    <tr class="{{ $loop->index % 2 === 0 ? 'bg-white' : 'bg-gray-50'  }}">
                        <td class="p-3 border border-white dark:border-white whitespace-nowrap">
                            {{ $mapa->nombre_mapa }}</td>
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

    <!-- Modal simplificado para mostrar solo el mapa -->
    @if ($mostrarModal && $mapaSeleccionado)
        <div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-[9999]"
            wire:click="cerrarModal">
            <div class="relative top-5 mx-auto p-5 border w-11/12 max-w-6xl shadow-lg rounded-md bg-white"
                wire:click.stop>
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-medium text-gray-900">{{ $mapaSeleccionado->nombre_mapa }}</h3>
                    <button wire:click="cerrarModal" class="text-gray-400 hover:text-gray-500">
                        <span class="sr-only">Cerrar</span>
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <div class="flex justify-center">
                    <img src="{{ asset('storage/' . $mapaSeleccionado->ruta_mapa) }}" alt="Mapa Original"
                        class="max-w-full max-h-[85vh] object-contain rounded-lg border shadow-md">
                </div>
            </div>
        </div>
    @endif
</div>
