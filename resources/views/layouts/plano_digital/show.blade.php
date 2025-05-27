<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <h2 class="text-xl font-semibold leading-tight">
                {{ "{$mapa->piso->facultad->nombre_facultad}, {$mapa->piso->facultad->sede->nombre_sede}" }}
            </h2>
        </div>
    </x-slot>

    <div class="p-6 space-y-6">

        <!-- Card: Navegación de Pisos y Plano -->
        <div class="w-full">
            <div class="bg-white rounded-t-xl shadow-md">
                <ul class="flex border-b border-gray-300 dark:border-gray-700" id="pills-tab" role="tablist">
                    @foreach ($pisos as $piso)
                        <li role="presentation">
                            <a href="{{ route('plano.show', $piso->id_mapa) }}"
                                class="px-10 py-4 text-lg font-semibold transition-all duration-300 rounded-t-xl border border-b-0
                                {{ $piso->id_mapa === $mapa->id_mapa
                                    ? 'bg-light-cloud-blue text-white border-light-cloud-blue'
                                    : 'bg-white text-gray-700 border-gray-300 hover:bg-gray-100 hover:text-light-cloud-blue' }}"
                                role="tab"
                                aria-selected="{{ $piso->id_mapa === $mapa->id_mapa ? 'true' : 'false' }}">
                                Piso {{ $piso->piso->numero_piso }}
                            </a>
                        </li>
                    @endforeach
                </ul>
                <!-- Card para el canvas y controles -->
                <div class="p-6 bg-white rounded-b-xl shadow-md dark:bg-gray-800">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Plano del Piso
                            {{ $mapa->piso->numero_piso }}</h3>
                        <button onclick="actualizarEstados(true)"
                            class="px-4 py-2 text-sm font-medium text-white bg-light-cloud-blue rounded-md hover:bg-opacity-90 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-light-cloud-blue transition-all duration-300">
                            <span id="boton-texto">Actualizar Estados</span>
                            <span id="boton-loading" class="hidden">
                                <svg class="w-5 h-5 text-white animate-spin" xmlns="http://www.w3.org/2000/svg"
                                    fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10"
                                        stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor"
                                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                    </path>
                                </svg>
                            </span>
                        </button>
                    </div>

                    <!-- Pills content -->
                    <div class="mb-6">
                        <div class="transition-opacity duration-150 ease-linear opacity-100">
                            <div class="relative" style="padding-top: 75%;">
                                <!-- Canvas para la imagen base -->
                                <canvas id="mapCanvas"
                                    class="absolute top-0 left-0 w-full h-full bg-white dark:bg-gray-800"></canvas>

                                <!-- Canvas para los indicadores -->
                                <canvas id="indicatorsCanvas"
                                    class="absolute top-0 left-0 w-full h-full pointer-events-auto"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Leyenda abajo como pequeño card -->
            <div class="mt-6 p-4 bg-white rounded-lg shadow-md dark:bg-gray-800 w-full max-w-md mx-auto">
                <h3 class="text-base font-semibold text-center mb-2">Leyenda</h3>
                <div class="flex flex-col gap-2 text-sm items-start">
                    <div class="flex items-center gap-2">
                        <div class="w-4 h-4 rounded-sm bg-red-500"></div>
                        <span>Espacio ocupado</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <div class="w-4 h-4 rounded-sm bg-blue-500"></div>
                        <span>Próximo a utilizar</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <div class="w-4 h-4 rounded-sm bg-green-500"></div>
                        <span>Disponible</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <div class="w-4 h-4 rounded-sm" style="background-color: #8B5E3C;"></div>
                        <span>Disponible (uso previsto)</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <x-modal name="detalles-bloque" :show="false" maxWidth="2xl">
        <x-slot name="header">
            <h1 id="modal-titulo" class="font-sans text-lg font-semibold text-white dark:text-white"></h1>
        </x-slot>
        <div class="p-4">
            <div class="space-y-4">
                <div>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Tipo de Espacio:</p>
                    <p id="modal-tipo-espacio" class="text-sm text-gray-900 dark:text-gray-100"></p>
                </div>
                <div>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Puestos Disponibles:</p>
                    <p id="modal-puestos" class="text-sm text-gray-900 dark:text-gray-100"></p>
                </div>

                <div id="modal-planificacion" class="hidden">
                    <p id="modal-asignatura" class="text-sm text-gray-900 dark:text-gray-100"></p>
                    <p id="modal-profesor" class="text-sm text-gray-900 dark:text-gray-100"></p>
                    <ul id="modal-modulos" class="mt-2 space-y-1"></ul>
                </div>

                <div id="modal-clase-proxima" class="hidden">
                    <p class="mb-2 text-sm font-medium text-gray-900 dark:text-gray-100">Próxima Clase:</p>
                    <p id="modal-asignatura-proxima" class="text-sm text-gray-900 dark:text-gray-100"></p>
                    <p id="modal-profesor-proximo" class="text-sm text-gray-900 dark:text-gray-100"></p>
                    <p id="modal-horario-proximo" class="text-sm text-gray-900 dark:text-gray-100"></p>
                </div>

                <div id="modal-reserva" class="hidden">
                    <p class="mb-2 text-sm font-medium text-gray-900 dark:text-gray-100">Reserva:</p>
                    <p id="modal-fecha-reserva" class="text-sm text-gray-900 dark:text-gray-100"></p>
                    <p id="modal-hora-reserva" class="text-sm text-gray-900 dark:text-gray-100"></p>
                </div>
            </div>
        </div>
    </x-modal>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const state = {
                mapImage: null,
                originalImageSize: null,
                indicators: @json($bloques),
                originalCoordinates: @json($bloques),
                isImageLoaded: false
            };

            const elements = {
                mapCanvas: document.getElementById('mapCanvas'),
                mapCtx: document.getElementById('mapCanvas').getContext('2d'),
                indicatorsCanvas: document.getElementById('indicatorsCanvas'),
                indicatorsCtx: document.getElementById('indicatorsCanvas').getContext('2d')
            };

            const config = {
                indicatorSize: 40,
                indicatorWidth: 60,
                indicatorHeight: 40,
                indicatorBorder: '#FFFFFF',
                indicatorTextColor: '#FFFFFF',
                fontSize: 12
            };

            // Obtener el ID del mapa de la URL actual
            const mapaId = window.location.pathname.split('/').pop();
            console.log('ID del mapa:', mapaId);

            // Función para mostrar notificaciones
            function mostrarNotificacion(mensaje, tipo) {
                const notificacion = document.createElement('div');
                notificacion.className = `fixed top-4 right-4 px-4 py-2 rounded-md text-white ${
                    tipo === 'success' ? 'bg-green-500' : 
                    tipo === 'error' ? 'bg-red-500' : 
                    'bg-blue-500'
                }`;
                notificacion.textContent = mensaje;
                document.body.appendChild(notificacion);

                // Eliminar la notificación después de 3 segundos
                setTimeout(() => {
                    notificacion.remove();
                }, 3000);
            }

            // Función para actualizar estados
            window.actualizarEstados = function(esManual = false) {
                console.log('Iniciando actualización de estados...');

                // Mostrar indicador de carga solo si es una actualización manual
                if (esManual) {
                    const botonTexto = document.getElementById('boton-texto');
                    const botonLoading = document.getElementById('boton-loading');
                    botonTexto.classList.add('hidden');
                    botonLoading.classList.remove('hidden');
                }

                // Hacer la petición al servidor para obtener solo los datos de los bloques
                fetch(`/plano/${mapaId}/bloques`)
                    .then(response => {
                        console.log('Respuesta recibida del servidor');
                        if (!response.ok) {
                            throw new Error('Error en la respuesta del servidor');
                        }
                        return response.json();
                    })
                    .then(bloquesData => {
                        console.log('Datos recibidos:', bloquesData);

                        // Verificar si hay cambios en los estados
                        const hayCambios = JSON.stringify(state.indicators) !== JSON.stringify(bloquesData);
                        console.log('¿Hay cambios en los estados?', hayCambios);

                        if (hayCambios) {
                            // Actualizar los estados de los bloques
                            state.indicators = bloquesData;
                            state.originalCoordinates = bloquesData;

                            // Forzar el redibujado de los indicadores
                            elements.indicatorsCtx.clearRect(0, 0, elements.indicatorsCanvas.width, elements
                                .indicatorsCanvas.height);
                            drawIndicators();

                            console.log('Estados actualizados y redibujados');
                            mostrarNotificacion('Estados actualizados correctamente', 'success');
                        } else {
                            console.log('No hay cambios en los estados');
                            mostrarNotificacion('No hay cambios en los estados', 'info');
                        }
                    })
                    .catch(error => {
                        console.error('Error en la actualización:', error);
                        mostrarNotificacion('Error al actualizar estados: ' + error.message, 'error');
                    })
                    .finally(() => {
                        // Restaurar botón solo si fue una actualización manual
                        if (esManual) {
                            const botonTexto = document.getElementById('boton-texto');
                            const botonLoading = document.getElementById('boton-loading');
                            botonTexto.classList.remove('hidden');
                            botonLoading.classList.add('hidden');
                        }
                    });
            };

            // Configurar actualización automática cada 30 segundos
            setInterval(() => actualizarEstados(false), 30000);

            // Inicializar los canvases
            function initCanvases() {
                const container = elements.mapCanvas.parentElement;
                const width = container.clientWidth;
                const height = container.clientHeight;

                elements.mapCanvas.width = width;
                elements.mapCanvas.height = height;
                elements.indicatorsCanvas.width = width;
                elements.indicatorsCanvas.height = height;

                drawCanvas();
                if (state.isImageLoaded) {
                    drawIndicators();
                }
            }

            // Dibujar la imagen base
            function drawCanvas() {
                elements.mapCtx.clearRect(0, 0, elements.mapCanvas.width, elements.mapCanvas.height);

                if (!state.mapImage) return;

                const canvasRatio = elements.mapCanvas.width / elements.mapCanvas.height;
                const imageRatio = state.mapImage.width / state.mapImage.height;
                let drawWidth, drawHeight, offsetX, offsetY;

                if (imageRatio > canvasRatio) {
                    drawWidth = elements.mapCanvas.width;
                    drawHeight = elements.mapCanvas.width / imageRatio;
                    offsetX = 0;
                    offsetY = (elements.mapCanvas.height - drawHeight) / 2;
                } else {
                    drawHeight = elements.mapCanvas.height;
                    drawWidth = elements.mapCanvas.height * imageRatio;
                    offsetX = (elements.mapCanvas.width - drawWidth) / 2;
                    offsetY = 0;
                }

                elements.mapCtx.drawImage(state.mapImage, offsetX, offsetY, drawWidth, drawHeight);
            }

            // Dibujar todos los indicadores
            function drawIndicators() {
                if (!state.isImageLoaded) return;

                elements.indicatorsCtx.clearRect(0, 0, elements.indicatorsCanvas.width, elements.indicatorsCanvas
                    .height);

                state.indicators.forEach(indicator => {
                    drawIndicator(indicator);
                });
            }

            // Dibujar un indicador individual
            function drawIndicator(indicator) {
                if (!state.isImageLoaded) return;

                const {
                    id,
                    estado,
                    nombre,
                    detalles
                } = indicator;

                // Calcular la posición actual basada en las coordenadas originales
                const position = calculatePosition(indicator);
                const width = config.indicatorWidth;
                const height = config.indicatorHeight;

                // Determinar el color según el estado
                let color;
                switch (estado) {
                    case 'red':
                        color = '#EF4444'; // Rojo para espacios en uso
                        break;
                    case 'blue':
                        color = '#3B82F6'; // Azul para espacios reservados
                        break;
                    case 'yellow':
                        color = '#F59E0B'; // Amarillo para espacios próximos
                        break;
                    default:
                        color = '#10B981'; // Verde para espacios disponibles
                }

                // Dibujar cuadrado
                elements.indicatorsCtx.fillStyle = color;
                elements.indicatorsCtx.fillRect(position.x - width / 2, position.y - height / 2, width, height);
                elements.indicatorsCtx.lineWidth = 2;
                elements.indicatorsCtx.strokeStyle = config.indicatorBorder;
                elements.indicatorsCtx.strokeRect(position.x - width / 2, position.y - height / 2, width, height);

                // Dibujar texto con el nuevo tamaño de fuente
                elements.indicatorsCtx.font = `bold ${config.fontSize}px Arial`;
                elements.indicatorsCtx.fillStyle = config.indicatorTextColor;
                elements.indicatorsCtx.textAlign = 'center';
                elements.indicatorsCtx.textBaseline = 'middle';
                elements.indicatorsCtx.fillText(id, position.x, position.y);
            }

            // Calcular la posición actual basada en las coordenadas originales
            function calculatePosition(indicator) {
                if (!state.isImageLoaded || !state.mapImage) {
                    return {
                        x: 0,
                        y: 0
                    };
                }

                const canvasRatio = elements.mapCanvas.width / elements.mapCanvas.height;
                const imageRatio = state.mapImage.width / state.mapImage.height;
                let drawWidth, drawHeight, offsetX, offsetY;

                if (imageRatio > canvasRatio) {
                    drawWidth = elements.mapCanvas.width;
                    drawHeight = elements.mapCanvas.width / imageRatio;
                    offsetX = 0;
                    offsetY = (elements.mapCanvas.height - drawHeight) / 2;
                } else {
                    drawHeight = elements.mapCanvas.height;
                    drawWidth = elements.mapCanvas.height * imageRatio;
                    offsetX = (elements.mapCanvas.width - drawWidth) / 2;
                    offsetY = 0;
                }

                // Encontrar las coordenadas originales del indicador
                const originalIndicator = state.originalCoordinates.find(i => i.id === indicator.id);
                if (!originalIndicator) return {
                    x: 0,
                    y: 0
                };

                // Calcular la nueva posición manteniendo la proporción
                const x = offsetX + (originalIndicator.x / state.originalImageSize.width) * drawWidth;
                const y = offsetY + (originalIndicator.y / state.originalImageSize.height) * drawHeight;

                return {
                    x,
                    y
                };
            }

            // Agregar evento de clic al canvas
            elements.indicatorsCanvas.addEventListener('click', function(event) {
                if (!state.isImageLoaded) return;

                const rect = elements.indicatorsCanvas.getBoundingClientRect();
                const clickX = event.clientX - rect.left;
                const clickY = event.clientY - rect.top;

                // Verificar si el clic fue en algún indicador
                state.indicators.forEach(indicator => {
                    const position = calculatePosition(indicator);
                    const width = config.indicatorWidth;
                    const height = config.indicatorHeight;

                    if (
                        clickX >= position.x - width / 2 &&
                        clickX <= position.x + width / 2 &&
                        clickY >= position.y - height / 2 &&
                        clickY <= position.y + height / 2
                    ) {
                        mostrarDetallesBloque(indicator);
                    }
                });
            });

            // Cargar la imagen del mapa
            const img = new Image();
            img.onload = function() {
                state.mapImage = img;
                state.originalImageSize = {
                    width: img.naturalWidth,
                    height: img.naturalHeight
                };
                state.isImageLoaded = true;
                initCanvases();
                drawCanvas();
                drawIndicators();
            };
            img.src = "{{ asset('storage/' . $mapa->ruta_mapa) }}";

            // Inicializar y manejar redimensionamiento
            initCanvases();
            window.addEventListener('resize', function() {
                initCanvases();
                drawIndicators();
            });
        });

        // Modificar la función mostrarDetallesBloque para usar el sistema de eventos de Alpine.js
        window.mostrarDetallesBloque = function(bloque) {
            // Si el bloque es un elemento HTML, obtener los datos del atributo data-bloque
            if (typeof bloque === 'object' && bloque.dataset) {
                bloque = JSON.parse(bloque.dataset.bloque);
            }

            const detalles = bloque.detalles;

            // Actualizar información básica
            document.getElementById('modal-titulo').textContent = bloque.nombre + ' - ' + bloque.id;
            document.getElementById('modal-tipo-espacio').textContent = detalles.tipo_espacio;
            document.getElementById('modal-puestos').textContent = detalles.puestos_disponibles;

            // Información de clase actual
            const planificacionDiv = document.getElementById('modal-planificacion');
            if (detalles.planificacion) {
                planificacionDiv.classList.remove('hidden');
                document.getElementById('modal-asignatura').textContent =
                    `Asignatura: ${detalles.planificacion.asignatura}`;
                document.getElementById('modal-profesor').textContent = `Profesor: ${detalles.planificacion.profesor}`;

                const modulosList = document.getElementById('modal-modulos');
                modulosList.innerHTML = '';
                detalles.planificacion.modulos.forEach(modulo => {
                    const li = document.createElement('li');
                    li.className = 'text-sm text-gray-900 dark:text-gray-100';
                    const horaInicio = modulo.hora_inicio.substring(0, 5);
                    const horaTermino = modulo.hora_termino.substring(0, 5);
                    li.textContent = `${modulo.dia}: ${horaInicio} - ${horaTermino}`;
                    modulosList.appendChild(li);
                });
            } else {
                planificacionDiv.classList.add('hidden');
            }

            // Información de clase próxima
            const claseProximaDiv = document.getElementById('modal-clase-proxima');
            if (detalles.planificacion_proxima) {
                claseProximaDiv.classList.remove('hidden');
                document.getElementById('modal-asignatura-proxima').textContent =
                    `Asignatura: ${detalles.planificacion_proxima.asignatura}`;
                document.getElementById('modal-profesor-proximo').textContent =
                    `Profesor: ${detalles.planificacion_proxima.profesor}`;
                const horaInicio = detalles.planificacion_proxima.hora_inicio.substring(0, 5);
                const horaTermino = detalles.planificacion_proxima.hora_termino.substring(0, 5);
                document.getElementById('modal-horario-proximo').textContent =
                    `Horario: ${horaInicio} - ${horaTermino}`;
            } else {
                claseProximaDiv.classList.add('hidden');
            }

            // Información de reserva
            const reservaDiv = document.getElementById('modal-reserva');
            if (detalles.reserva) {
                reservaDiv.classList.remove('hidden');
                document.getElementById('modal-fecha-reserva').textContent = `Fecha: ${detalles.reserva.fecha_reserva}`;
                document.getElementById('modal-hora-reserva').textContent = `Hora: ${detalles.reserva.hora}`;
            } else {
                reservaDiv.classList.add('hidden');
            }

            // Mostrar el modal usando el sistema de eventos de Alpine.js
            window.dispatchEvent(new CustomEvent('open-modal', {
                detail: 'detalles-bloque'
            }));
        };
    </script>
</x-app-layout>
