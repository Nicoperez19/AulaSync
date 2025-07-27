<div class="h-full flex flex-col">
    @if(count($pisos) > 0)
        <!-- Nav Pills de Pisos -->
        <div class="flex-shrink-0 mb-4">
            <div class="max-w-full overflow-hidden">
                <ul class="flex border-b border-gray-200 justify-start w-full" role="tablist">
                    @foreach ($pisos as $piso)
                        <li role="presentation">
                            <button type="button" wire:click="selectPiso({{ $piso->id }})"
                                class="px-6 py-3 text-sm font-semibold transition-all duration-300 border border-b-0 rounded-t-xl focus:outline-none whitespace-nowrap"
                                :class="$wire.selectedPiso == {{ $piso->id }} 
                                            ? 'bg-red-700 text-white border-red-700 shadow-md'
                                            : 'bg-white text-gray-700 border-gray-200 hover:bg-gray-100 hover:text-red-700'">
                                Piso {{ $piso->numero_piso }}
                            </button>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
        
        <!-- Contenido de espacios por piso -->
        <div class="flex-1 min-h-0 bg-white shadow-md rounded-xl overflow-hidden relative">
            <!-- Botón de pantalla completa -->
            <button id="fullscreenBtn" 
                    class="absolute top-4 right-4 z-20 p-2 bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors duration-200 group"
                    onclick="toggleFullscreen()"
                    title="Pantalla completa">
                <i class="fa-solid fa-expand text-gray-600 group-hover:text-gray-800"></i>
            </button>
            
            @if($moduloActual)
                @foreach ($pisos as $piso)
                    <div class="{{ $selectedPiso == $piso->id ? 'h-full' : 'hidden' }}"
                         x-show="$wire.selectedPiso == {{ $piso->id }}"
                         x-transition:enter="transition ease-out duration-300"
                         x-transition:enter-start="opacity-0 scale-95" 
                         x-transition:enter-end="opacity-100 scale-100"
                         x-transition:leave="transition ease-in duration-200"
                         x-transition:leave-start="opacity-100 scale-100"
                         x-transition:leave-end="opacity-0 scale-95">
                        
                        @php
                            $espaciosPiso = $espacios[$piso->id] ?? [];
                        @endphp
                        
                        @if(count($espaciosPiso) > 0)
                            <div class="h-full flex flex-col">
                                <!-- Tabla con scroll en 2 columnas -->
                                <div class="flex-1 overflow-auto">
                                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 h-full max-w-6xl mx-auto">
                                        <!-- Columna 1 -->
                                        <div class="overflow-auto">
        <table class="min-w-full divide-y divide-gray-200">
                                                <thead class="bg-gray-50 sticky top-0 z-10">
                                                    <tr>
                                                        <th class="w-16 px-2 py-2 font-medium text-left text-gray-500 uppercase text-xs">Espacio</th>
                                                        <th class="w-24 px-2 py-2 font-medium text-left text-gray-500 uppercase text-xs">Nombre</th>
                                                        <th class="w-16 px-2 py-2 font-medium text-center text-gray-500 uppercase text-xs">Estado</th>
                                                        <th class="px-2 py-2 font-medium text-left text-gray-500 uppercase text-xs">Clase Actual</th>
                    </tr>
                </thead>
                                                <tbody class="bg-white divide-y divide-gray-200">
                                                    @foreach(array_slice($espaciosPiso, 0, ceil(count($espaciosPiso) / 2)) as $espacio)
                                                    @php
                                                            $estado = $espacio['estado'];
                            $colorFila = '';
                            $colorTexto = '';
                            
                                                            switch($estado) {
                                case 'Ocupado':
                                    $colorFila = 'bg-red-50';
                                    $colorTexto = 'text-red-700';
                                    break;
                                case 'Reservado':
                                    $colorFila = 'bg-yellow-50';
                                    $colorTexto = 'text-yellow-700';
                                    break;
                                case 'Disponible':
                                default:
                                    $colorFila = 'bg-green-50';
                                    $colorTexto = 'text-green-700';
                                    break;
                            }
                        @endphp
                                                        <tr class="{{ $colorFila }} hover:{{ str_replace('bg-50', 'bg-100', $colorFila) }} transition-colors duration-200">
                                                            <td class="px-2 py-2 font-bold text-left text-blue-700 text-sm">
                                                                {{ $espacio['id_espacio'] }}
                                </td>
                                                            <td class="px-2 py-2 text-left text-sm">
                                                                {{ $espacio['nombre_espacio'] }}
                                </td>
                                                            <td class="px-2 py-2 text-center">
                                                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium
                                                                    {{ $estado === 'Ocupado' ? 'bg-red-100 text-red-800' : 
                                                                       ($estado === 'Reservado' ? 'bg-yellow-100 text-yellow-800' : 'bg-green-100 text-green-800') }}">
                                                                    <span class="inline-block w-2 h-2 rounded-full mr-1
                                                                        {{ $estado === 'Ocupado' ? 'bg-red-500' : 
                                                                           ($estado === 'Reservado' ? 'bg-yellow-500' : 'bg-green-500') }}">
                                                                    </span>
                                                                    {{ $estado }}
                                                                </span>
                                </td>
                                                            <td class="px-2 py-2 text-left">
                                                                @if($espacio['tiene_clase'] && $espacio['datos_clase'])
                                                                    <div class="text-xs">
                                                                        <div class="font-medium text-gray-900 truncate">
                                                                            {{ $espacio['datos_clase']['codigo_asignatura'] }}
                                                                        </div>
                                                                        <div class="text-gray-500 truncate">
                                                                            {{ $espacio['datos_clase']['nombre_asignatura'] }}
                                                                        </div>
                                                                        <div class="text-gray-400 truncate">
                                                                            {{ $espacio['datos_clase']['profesor']['name'] }}
                                                                        </div>
                                    </div>
                                                                @else
                                                                    <span class="text-gray-400 text-xs italic">Sin clase</span>
                                                                @endif
                                </td>
                            </tr>
                        @endforeach
                                                </tbody>
                                            </table>
                                        </div>

                                        <!-- Columna 2 -->
                                        <div class="overflow-auto">
                                            <table class="min-w-full divide-y divide-gray-200">
                                                <thead class="bg-gray-50 sticky top-0 z-10">
                                                    <tr>
                                                        <th class="w-16 px-2 py-2 font-medium text-left text-gray-500 uppercase text-xs">Espacio</th>
                                                        <th class="w-24 px-2 py-2 font-medium text-left text-gray-500 uppercase text-xs">Nombre</th>
                                                        <th class="w-16 px-2 py-2 font-medium text-center text-gray-500 uppercase text-xs">Estado</th>
                                                        <th class="px-2 py-2 font-medium text-left text-gray-500 uppercase text-xs">Clase Actual</th>
                                                    </tr>
                                                </thead>
                                                <tbody class="bg-white divide-y divide-gray-200">
                                                    @foreach(array_slice($espaciosPiso, ceil(count($espaciosPiso) / 2)) as $espacio)
                                                        @php
                                                            $estado = $espacio['estado'];
                                                            $colorFila = '';
                                                            $colorTexto = '';
                                                            
                                                            switch($estado) {
                                                                case 'Ocupado':
                                                                    $colorFila = 'bg-red-50';
                                                                    $colorTexto = 'text-red-700';
                                                                    break;
                                                                case 'Reservado':
                                                                    $colorFila = 'bg-yellow-50';
                                                                    $colorTexto = 'text-yellow-700';
                                                                    break;
                                                                case 'Disponible':
                                                                default:
                                                                    $colorFila = 'bg-green-50';
                                                                    $colorTexto = 'text-green-700';
                                                                    break;
                                                            }
                                                        @endphp
                                                        <tr class="{{ $colorFila }} hover:{{ str_replace('bg-50', 'bg-100', $colorFila) }} transition-colors duration-200">
                                                            <td class="px-2 py-2 font-bold text-left text-blue-700 text-sm">
                                                                {{ $espacio['id_espacio'] }}
                                                            </td>
                                                            <td class="px-2 py-2 text-left text-sm">
                                                                {{ $espacio['nombre_espacio'] }}
                                                            </td>
                                                            <td class="px-2 py-2 text-center">
                                                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium
                                                                    {{ $estado === 'Ocupado' ? 'bg-red-100 text-red-800' : 
                                                                       ($estado === 'Reservado' ? 'bg-yellow-100 text-yellow-800' : 'bg-green-100 text-green-800') }}">
                                                                    <span class="inline-block w-2 h-2 rounded-full mr-1
                                                                        {{ $estado === 'Ocupado' ? 'bg-red-500' : 
                                                                           ($estado === 'Reservado' ? 'bg-yellow-500' : 'bg-green-500') }}">
                                                                    </span>
                                                                    {{ $estado }}
                                                                </span>
                                                            </td>
                                                            <td class="px-2 py-2 text-left">
                                                                @if($espacio['tiene_clase'] && $espacio['datos_clase'])
                                                                    <div class="text-xs">
                                                                        <div class="font-medium text-gray-900 truncate">
                                                                            {{ $espacio['datos_clase']['codigo_asignatura'] }}
                                                                        </div>
                                                                        <div class="text-gray-500 truncate">
                                                                            {{ $espacio['datos_clase']['nombre_asignatura'] }}
                                                                        </div>
                                                                        <div class="text-gray-400 truncate">
                                                                            {{ $espacio['datos_clase']['profesor']['name'] }}
                                                                        </div>
                                                                    </div>
                    @else
                                                                    <span class="text-gray-400 text-xs italic">Sin clase</span>
                                                                @endif
                            </td>
                        </tr>
                                                    @endforeach
                </tbody>
            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @else
                            <div class="h-full flex items-center justify-center">
                                <div class="text-center">
                                    <i class="fa-solid fa-building text-4xl text-gray-300 mb-4"></i>
                                    <p class="text-gray-500">No hay espacios disponibles en este piso.</p>
                                </div>
                            </div>
                        @endif
                    </div>
                @endforeach
            @else
                <!-- Mensaje cuando no hay módulo actual -->
                <div class="h-full flex items-center justify-center">
                    <div class="text-center">
                        <i class="fa-solid fa-clock text-4xl text-gray-300 mb-4"></i>
                        <p class="text-gray-500 text-lg font-medium">No hay horario en el módulo actual</p>
                        <p class="text-gray-400 text-sm mt-2">Fuera del horario de clases programadas</p>
                    </div>
                </div>
            @endif
        </div>
    
    <!-- Leyenda de estados -->
        <div class="flex-shrink-0 mt-4 pt-3 border-t border-gray-200">
        <div class="flex items-center justify-center gap-4 text-xs">
            <div class="flex items-center gap-1">
                <span class="inline-block w-2 h-2 rounded-full bg-green-500"></span>
                <span class="text-gray-600">Disponible</span>
            </div>
            <div class="flex items-center gap-1">
                <span class="inline-block w-2 h-2 rounded-full bg-yellow-500"></span>
                <span class="text-gray-600">Reservado</span>
            </div>
            <div class="flex items-center gap-1">
                <span class="inline-block w-2 h-2 rounded-full bg-red-500"></span>
                <span class="text-gray-600">Ocupado</span>
            </div>
        </div>
    </div>
    @else
        <div class="h-full flex items-center justify-center">
            <div class="text-center">
                <i class="fa-solid fa-building text-4xl text-gray-300 mb-4"></i>
                <p class="text-gray-500">No hay pisos disponibles.</p>
            </div>
        </div>
    @endif

    <script>
        // Función para alternar pantalla completa
        function toggleFullscreen() {
            const container = document.querySelector('.h-full.flex.flex-col');
            const btn = document.getElementById('fullscreenBtn');
            const icon = btn.querySelector('i');
            
            if (!document.fullscreenElement) {
                // Entrar en pantalla completa
                if (container.requestFullscreen) {
                    container.requestFullscreen();
                } else if (container.webkitRequestFullscreen) {
                    container.webkitRequestFullscreen();
                } else if (container.msRequestFullscreen) {
                    container.msRequestFullscreen();
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
            const icon = btn.querySelector('i');
            
            if (!document.fullscreenElement) {
                icon.className = 'fa-solid fa-expand text-gray-600 group-hover:text-gray-800';
                btn.title = 'Pantalla completa';
            }
        });

        // Actualizar datos cada 30 segundos
        setInterval(function() {
            @this.actualizarAutomaticamente();
        }, 30000);
    </script>
</div> 