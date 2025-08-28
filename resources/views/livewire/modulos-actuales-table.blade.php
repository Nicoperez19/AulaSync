<div class="h-full flex flex-col p-6">
    @if (count($pisos) > 0)
        <div class="flex-1 flex flex-col bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
            @if (count($this->getTodosLosEspacios()) > 0)
                <div class="flex flex-1 overflow-hidden min-h-0">

                    {{-- Columna izquierda - DISPONIBLES con carrusel --}}
                    <div class="flex-1 flex flex-col border-r border-gray-200 min-h-0">
                        <table class="w-full table-fixed flex-shrink-0">
                            <thead>
                                <tr class="bg-green-600 text-white border-b border-gray-200">
                                    <th class="px-3 py-1 text-left text-sm font-semibold uppercase">N° Sala</th>
                                    <th class="px-3 py-1 text-left text-sm font-semibold uppercase">Nombre</th>
                                    <th class="px-3 py-1 text-left text-sm font-semibold uppercase">Piso</th>
                                    <th class="px-3 py-1 text-left text-sm font-semibold uppercase">Estado</th>
                                </tr>
                            </thead>
                        </table>

                        <div id="disponibles-carrusel" class="flex-1 overflow-hidden min-h-0 transition-transform duration-700 ease-in-out relative" style="max-height: calc(100vh - 220px);">
                            @php
                                $espaciosDisponibles = collect($this->getTodosLosEspacios())
                                    ->filter(function($espacio) {
                                        return strtolower($espacio['estado']) === 'disponible';
                                    })
                                    ->values();
                                $totalDisponibles = $espaciosDisponibles->count();
                                $maxPorVista = 10;
                                $vistasDisponibles = ceil($totalDisponibles / $maxPorVista);
                            @endphp

                            @if ($totalDisponibles > 0)
                                @for ($vista = 0; $vista < $vistasDisponibles; $vista++)
                                    <div class="w-full overflow-y-auto" style="{{ $vista === 0 ? '' : 'display:none;' }}">
                                        <table class="w-full table-fixed">
                                            <tbody class="divide-y divide-gray-100">
                                                @php
                                                    $inicio = $vista * $maxPorVista;
                                                    $espaciosVista = $espaciosDisponibles->slice($inicio, $maxPorVista);
                                                @endphp

                                                @foreach ($espaciosVista as $espacio)
                                                    <tr class="bg-white hover:bg-green-50 transition-colors h-12">
                                                        <td class="px-3 py-2 text-sm font-semibold text-blue-700">{{ $espacio['id_espacio'] }}</td>
                                                        <td class="px-3 py-2 text-sm">{{ $espacio['nombre_espacio'] }}</td>
                                                        <td class="px-3 py-2 text-sm text-gray-500">{{ $espacio['piso'] }}</td>
                                                        <td class="px-3 py-2 text-sm text-green-600 font-medium">Disponible</td>
                                                    </tr>
                                                @endforeach

                                                {{-- Agregar filas vacías si no hay suficientes elementos --}}
                                                @for ($i = $espaciosVista->count(); $i < $maxPorVista; $i++)
                                                    <tr class="bg-gray-50 h-12">
                                                        <td class="px-3 py-2 text-sm text-gray-400">-</td>
                                                        <td class="px-3 py-2 text-sm text-gray-400">-</td>
                                                        <td class="px-3 py-2 text-sm text-gray-400">-</td>
                                                        <td class="px-3 py-2 text-sm text-gray-400">-</td>
                                                    </tr>
                                                @endfor
                                            </tbody>
                                        </table>
                                    </div>
                                @endfor
                            @else
                                {{-- Mensaje cuando no hay espacios disponibles --}}
                                <div class="flex-1 flex items-center justify-center">
                                    <div class="text-center">
                                        <i class="fas fa-info-circle text-3xl text-gray-400 mb-2"></i>
                                        <p class="text-gray-500 text-sm">No hay espacios disponibles en este momento</p>
                                    </div>
                                </div>
                            @endif
                        </div>

                        {{-- Indicadores del carrusel de disponibles --}}
                        @if ($vistasDisponibles > 1)
                            <div class="flex justify-between items-center p-2 bg-gray-50 border-t border-gray-200 flex-shrink-0">
                                <div class="text-xs text-gray-600">
                                    <span id="disponibles-info">{{ $totalDisponibles }}</span> espacios disponibles
                                </div>
                                <div class="flex items-center space-x-2">
                                    <div class="flex space-x-1">
                                        @for ($i = 0; $i < $vistasDisponibles; $i++)
                                            <div class="w-2 h-2 rounded-full bg-green-300 cursor-pointer disponibles-indicator {{ $i === 0 ? 'bg-green-600' : '' }}" data-slide="{{ $i }}"></div>
                                        @endfor
                                    </div>
                                </div>
                                <div class="text-xs text-gray-600">
                                    Página <span id="pagina-actual">1</span> de {{ $vistasDisponibles }}
                                </div>
                            </div>
                        @else
                            @if ($totalDisponibles > 0)
                                <div class="flex justify-center p-2 bg-gray-50 border-t border-gray-200 flex-shrink-0">
                                    <div class="text-xs text-gray-600">
                                        <span id="disponibles-info">{{ $totalDisponibles }}</span> espacios disponibles
                                    </div>
                                </div>
                            @endif
                        @endif
                    </div>

                    {{-- Columna derecha - CARRUSEL con Ocupados / Reservados --}}
                    <div class="flex-1 flex flex-col relative overflow-hidden min-h-0">
                        <table class="w-full table-fixed flex-shrink-0">
                            <thead>
                                <tr class="bg-blue-600 text-white border-b border-gray-200">
                                    <th class="px-3 py-1 text-left text-sm font-semibold uppercase">N° Sala</th>
                                    <th class="px-3 py-1 text-left text-sm font-semibold uppercase">Nombre</th>
                                    <th class="px-3 py-1 text-left text-sm font-semibold uppercase">Piso</th>
                                    <th class="px-3 py-1 text-left text-sm font-semibold uppercase">Estado</th>
                                    <th class="px-3 py-1 text-left text-sm font-semibold uppercase">Responsable</th>
                                </tr>
                            </thead>
                        </table>

                        <div id="tabla-carrusel" class="flex-1 overflow-hidden min-h-0 transition-transform duration-700 ease-in-out relative" style="max-height: calc(100vh - 220px);">
                            @foreach ($this->getTodosLosEspacios() as $espacio)
                                @if (in_array(strtolower($espacio['estado']), ['ocupado', 'reservado']))
                                    <div class="w-full overflow-y-auto" style="display:none;">
                                        <table class="w-full table-fixed">
                                            <tbody>
                                                <tr class="bg-white hover:bg-blue-50 transition-colors h-12">
                                                    <td class="px-3 py-2 text-sm font-semibold text-blue-700">{{ $espacio['id_espacio'] }}</td>
                                                    <td class="px-3 py-2 text-sm">{{ $espacio['nombre_espacio'] }}</td>
                                                    <td class="px-3 py-2 text-sm text-gray-500">{{ $espacio['piso'] }}</td>
                                                    <td class="px-3 py-2 text-sm
                                                        {{ strtolower($espacio['estado']) === 'ocupado' ? 'text-red-600' : 'text-yellow-600' }} font-medium">
                                                        {{ ucfirst($espacio['estado']) }}
                                                    </td>
                                                    <td class="px-3 py-2 text-sm">
                                                        @if ($espacio['tiene_clase'] && $espacio['datos_clase'])
                                                            {{ $this->getPrimerApellido($espacio['datos_clase']['profesor']['name']) }}
                                                        @elseif ($espacio['tiene_reserva_profesor'] && $espacio['datos_profesor'])
                                                            {{ $this->getPrimerApellido($espacio['datos_profesor']['nombre']) }}
                                                        @elseif ($espacio['tiene_reserva_solicitante'] && $espacio['datos_solicitante'])
                                                            {{ $this->getPrimerApellidoSolicitante($espacio['datos_solicitante']['nombre']) }}
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    </div>

                </div>
            @endif
        </div>
    @endif

    <script>
        // Carrusel automático para DISPONIBLES
        let indexDisponibles = 0;
        let autoPlayDisponibles = null;
        const contenedorDisponibles = document.getElementById("disponibles-carrusel");

        function actualizarIndicadoresDisponibles() {
            const indicadores = document.querySelectorAll('.disponibles-indicator');
            const paginaActual = document.getElementById('pagina-actual');

            indicadores.forEach((indicador, index) => {
                if (index === indexDisponibles) {
                    indicador.classList.remove('bg-green-300');
                    indicador.classList.add('bg-green-600');
                } else {
                    indicador.classList.remove('bg-green-600');
                    indicador.classList.add('bg-green-300');
                }
            });

            // Actualizar el número de página actual
            if (paginaActual) {
                paginaActual.textContent = indexDisponibles + 1;
            }
        }

        function mostrarSlideDisponibles(slideIndex) {
            if (!contenedorDisponibles || !contenedorDisponibles.children.length) return;

            const tablasDisponibles = contenedorDisponibles.children;

            // Ocultar todas las tablas
            for (let i = 0; i < tablasDisponibles.length; i++) {
                if (tablasDisponibles[i] && tablasDisponibles[i].style) {
                    tablasDisponibles[i].style.display = "none";
                }
            }

            // Mostrar la tabla seleccionada
            if (tablasDisponibles[slideIndex] && tablasDisponibles[slideIndex].style) {
                tablasDisponibles[slideIndex].style.display = "block";
            }

            indexDisponibles = slideIndex;
            actualizarIndicadoresDisponibles();
        }

        function inicializarTabla() {
            // Obtener referencias a los elementos
            contenedorDisponibles = document.getElementById('contenedor-disponibles');
            indicadoresDisponibles = document.getElementById('indicadores-disponibles');
            btnAnteriorDisponibles = document.getElementById('btn-anterior-disponibles');
            btnSiguienteDisponibles = document.getElementById('btn-siguiente-disponibles');

            // Configurar eventos de navegación
            if (btnAnteriorDisponibles) {
                btnAnteriorDisponibles.addEventListener('click', () => {
                    mostrarSlideDisponibles(indexDisponibles - 1);
                });
            }

            if (btnSiguienteDisponibles) {
                btnSiguienteDisponibles.addEventListener('click', () => {
                    mostrarSlideDisponibles(indexDisponibles + 1);
                });
            }

            // Mostrar el primer slide
            mostrarSlideDisponibles(0);

            // Iniciar autoplay
            iniciarAutoPlayDisponibles();
        }

        function iniciarAutoPlayDisponibles() {
            if (autoPlayDisponibles) {
                clearInterval(autoPlayDisponibles);
            }

            const tablasDisponibles = contenedorDisponibles.children;
            if (tablasDisponibles.length > 1) {
                autoPlayDisponibles = setInterval(() => {
                    const newIndex = (indexDisponibles + 1) % tablasDisponibles.length;
                    mostrarSlideDisponibles(newIndex);
                }, 4000); // Cambia cada 4 segundos
            }
        }

        // Verificar que el contenedor de disponibles existe y tiene elementos
        if (contenedorDisponibles && contenedorDisponibles.children.length > 0) {
            const tablasDisponibles = contenedorDisponibles.children;

            // Mostrar solo la primera tabla al inicio
            for (let i = 1; i < tablasDisponibles.length; i++) {
                if (tablasDisponibles[i]) {
                    tablasDisponibles[i].style.display = "none";
                }
            }

            // Configurar navegación manual
            const prevButton = document.getElementById('prev-disponibles');
            const nextButton = document.getElementById('next-disponibles');
            const indicadores = document.querySelectorAll('.disponibles-indicator');

            if (prevButton) {
                prevButton.addEventListener('click', () => {
                    detenerAutoPlayDisponibles();
                    const newIndex = indexDisponibles === 0 ? tablasDisponibles.length - 1 : indexDisponibles - 1;
                    mostrarSlideDisponibles(newIndex);
                    setTimeout(iniciarAutoPlayDisponibles, 10000); // Reiniciar autoplay después de 10 segundos
                });
            }

            if (nextButton) {
                nextButton.addEventListener('click', () => {
                    detenerAutoPlayDisponibles();
                    const newIndex = (indexDisponibles + 1) % tablasDisponibles.length;
                    mostrarSlideDisponibles(newIndex);
                    setTimeout(iniciarAutoPlayDisponibles, 10000); // Reiniciar autoplay después de 10 segundos
                });
            }

            indicadores.forEach((indicador, index) => {
                indicador.addEventListener('click', () => {
                    detenerAutoPlayDisponibles();
                    mostrarSlideDisponibles(index);
                    setTimeout(iniciarAutoPlayDisponibles, 10000); // Reiniciar autoplay después de 10 segundos
                });
            });

            // Solo iniciar el carrusel automático si hay más de una tabla
            if (tablasDisponibles.length > 1) {
                iniciarAutoPlayDisponibles();
            }
        }

        // Inicializar cuando el DOM esté listo
        document.addEventListener('DOMContentLoaded', function() {
            // Inicializar el carrusel de disponibles
            if (contenedorDisponibles && contenedorDisponibles.children.length > 0) {
                const tablasDisponibles = contenedorDisponibles.children;

                // Mostrar solo la primera tabla al inicio
                for (let i = 1; i < tablasDisponibles.length; i++) {
                    if (tablasDisponibles[i]) {
                        tablasDisponibles[i].style.display = "none";
                    }
                }

                // Configurar navegación manual
                const prevButton = document.getElementById('prev-disponibles');
                const nextButton = document.getElementById('next-disponibles');
                const indicadores = document.querySelectorAll('.disponibles-indicator');

                if (prevButton) {
                    prevButton.addEventListener('click', () => {
                        detenerAutoPlayDisponibles();
                        const newIndex = indexDisponibles === 0 ? tablasDisponibles.length - 1 : indexDisponibles - 1;
                        mostrarSlideDisponibles(newIndex);
                        setTimeout(iniciarAutoPlayDisponibles, 10000);
                    });
                }

                if (nextButton) {
                    nextButton.addEventListener('click', () => {
                        detenerAutoPlayDisponibles();
                        const newIndex = (indexDisponibles + 1) % tablasDisponibles.length;
                        mostrarSlideDisponibles(newIndex);
                        setTimeout(iniciarAutoPlayDisponibles, 10000);
                    });
                }

                indicadores.forEach((indicador, index) => {
                    indicador.addEventListener('click', () => {
                        detenerAutoPlayDisponibles();
                        mostrarSlideDisponibles(index);
                        setTimeout(iniciarAutoPlayDisponibles, 10000);
                    });
                });

                // Iniciar autoplay si hay más de una tabla
                if (tablasDisponibles.length > 1) {
                    iniciarAutoPlayDisponibles();
                }
            }
        });
    </script>    {{-- Actualización automática del componente Livewire --}}
    <script>
        // Actualizar el componente cada 30 segundos para mostrar reservas nuevas
        setInterval(() => {
            @this.actualizarAutomaticamente();
        }, 30000);
    </script>
</div>
