<div class="h-full flex flex-col">
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
            <div class="relative top-0 mx-auto p-5 border w-full h-full shadow-lg bg-white"
                wire:click.stop>
                <!-- Botón de pantalla completa -->
                <button id="fullscreenBtn"
                    class="absolute top-4 right-4 z-20 p-2 bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors duration-200 group"
                    onclick="toggleFullscreen()" title="Pantalla completa">
                    <i class="fa-solid fa-expand text-gray-600 group-hover:text-gray-800"></i>
                </button>

                <div class="flex justify-between items-center mb-4">
                    <div>
                        <h3 class="text-lg font-medium text-gray-900">{{ $mapaSeleccionado->nombre_mapa }}</h3>
                        <div class="flex items-center gap-4 mt-2 text-sm text-gray-600">
                            <div class="flex items-center gap-1">
                                <i class="fa-solid fa-clock"></i>
                                <span id="currentTime">{{ now()->format('H:i:s') }}</span>
                            </div>
                            @if(isset($moduloActual))
                                <div class="flex items-center gap-1">
                                    <i class="fa-solid fa-book"></i>
                                    <span>Módulo: {{ $moduloActual }}</span>
                                </div>
                            @endif
                        </div>
                    </div>
                    <button wire:click="cerrarModal" class="text-gray-400 hover:text-gray-500">
                        <span class="sr-only">Cerrar</span>
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <div class="flex justify-center h-full">
                    <img src="{{ asset('storage/' . $mapaSeleccionado->ruta_mapa) }}" alt="Mapa Original"
                        class="max-w-full max-h-full object-contain rounded-lg border shadow-md">
                </div>
            </div>
        </div>
    @endif

    <script>
        // Función para alternar pantalla completa
        function toggleFullscreen() {
            const modal = document.querySelector('.fixed.inset-0');
            const btn = document.getElementById('fullscreenBtn');
            const icon = btn.querySelector('i');

            if (!document.fullscreenElement) {
                // Entrar en pantalla completa
                if (modal.requestFullscreen) {
                    modal.requestFullscreen();
                } else if (modal.webkitRequestFullscreen) {
                    modal.webkitRequestFullscreen();
                } else if (modal.msRequestFullscreen) {
                    modal.msRequestFullscreen();
                }
                icon.className = 'fa-solid fa-compress text-gray-600 group-hover:text-gray-800';
                btn.title = 'Salir de pantalla completa';
            } else {
                // Salir de pantalla completa
                if (document.exitFullscreen) {
                    document.exitFullscreen();
                } else if (document.webkitExitFullscreen) {
                    document.webkitExitFullscreen();
                } else if (document.msExitFullscreen) {
                    document.msExitFullscreen();
                }
                icon.className = 'fa-solid fa-expand text-gray-600 group-hover:text-gray-800';
                btn.title = 'Pantalla completa';
            }
        }

        // Escuchar cambios en el estado de pantalla completa
        document.addEventListener('fullscreenchange', function() {
            const btn = document.getElementById('fullscreenBtn');
            if (btn) {
                const icon = btn.querySelector('i');
                if (!document.fullscreenElement) {
                    icon.className = 'fa-solid fa-expand text-gray-600 group-hover:text-gray-800';
                    btn.title = 'Pantalla completa';
                }
            }
        });

        // Actualizar la hora cada segundo
        function updateTime() {
            const timeElement = document.getElementById('currentTime');
            if (timeElement) {
                const now = new Date();
                const timeString = now.toLocaleTimeString('es-ES', {
                    hour: '2-digit',
                    minute: '2-digit',
                    second: '2-digit'
                });
                timeElement.textContent = timeString;
            }
        }

        // Actualizar la hora cada segundo cuando el modal esté abierto
        setInterval(updateTime, 1000);
    </script>
</div>
