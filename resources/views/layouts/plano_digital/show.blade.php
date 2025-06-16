<x-show-layout>
    <div class="flex h-screen overflow-hidden">
        <!-- Sidebar fijo a la izquierda, más compacto y con fondo azul claro -->
        <aside
            class="fixed top-0 left-0 z-40 flex flex-col justify-between w-64 h-screen pt-4 pb-4 border-r border-gray-200 bg-light-cloud-blue dark:border-gray-700 text-lg">
            <!-- Logo de la aplicación -->
            <div class="flex flex-col items-center gap-4">
                <a href="{{ route('dashboard')}}" class="mb-2">
                    <x-application-logo-navbar class="w-12 h-12" />
                </a>

                <!-- Leyenda -->
                <div class="w-full p-4 px-2 bg-white rounded-lg shadow">
                    <h3 class="mb-3 font-semibold text-center flex items-center justify-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2">
                            <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
                            <circle cx="12" cy="10" r="3"></circle>
                        </svg>
                        LEYENDA DE ESTADO
                    </h3>
                    <div class="flex flex-col items-start gap-2">
                        <div class="flex items-center gap-2 w-full">
                            <div class="w-4 h-4 bg-red-500 rounded-full"></div>
                            <span class="flex-1">Ocupado</span>
                        </div>
                        <div class="flex items-center gap-2 w-full">
                            <div class="w-4 h-4 bg-yellow-500 rounded-full animate-pulse"></div>
                            <span class="flex-1">Próximo</span>
                        </div>
                        <div class="flex items-center gap-2 w-full">
                            <div class="w-4 h-4 bg-green-500 rounded-full"></div>
                            <span class="flex-1">Disponible</span>
                        </div>
                        <div class="flex items-center gap-2 w-full">
                            <div class="w-4 h-4 bg-blue-500 rounded-full"></div>
                            <span class="flex-1">Previsto</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Información de hora y módulo actual -->
            <div class="w-full px-2 mt-4">
                <div class="p-4 text-white border border-blue-600 rounded-lg shadow bg-light-cloud-blue">
                    <div class="flex items-center justify-between pb-2 border-b border-blue-400">
                        <div class="flex items-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span id="hora-actual">--:--:--</span>
                        </div>
                    </div>
                    <div class="py-2 border-b border-blue-400">
                        <div class="flex items-center gap-2 mb-1">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                            </svg>
                            <span>Módulo: <span id="modulo-actual">No hay módulo programado</span></span>
                        </div>

                    </div>
                    <div class="pt-2">
                        <div class="flex items-center gap-2 mb-1">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                            <span>Horario: <span id="horario-actual">--:-- - --:--</span></span>
                        </div>

                    </div>
                </div>
            </div>

            <!-- Estado del QR -->
            <div class="w-full px-2 ">
                <div class="p-4 text-white border border-blue-600 rounded-lg shadow bg-light-cloud-blue">
                    <!-- QR Placeholder -->
                    <div class="mt-4 p-4 bg-white/10 rounded-lg text-center">
                        <span id="qr-status" class="text-yellow-400">Esperando</span>

                        <div class="qr-placeholder mb-2 mt-2">
                            <div class="w-32 h-32 mx-auto bg-white/20 rounded-lg flex items-center justify-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-16 h-16 text-white/40" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 4v1m6 11h2m-6 0h-2v4m0-11v2m0 5h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 20h14a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                            </div>
                        </div>
                        <p class="text-white/80">Escanee el código QR</p>

                    </div>

                    <!-- Información del usuario escaneado -->
                    <div class="mt-4 space-y-2">
                        <div class="flex items-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                            <span class="font-semibold">RUN:</span>
                            <span id="run-escaneado" class="flex-1 text-right">--</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                            <span class="font-semibold">Usuario:</span>
                            <span id="nombre-usuario" class="flex-1 text-right">--</span>
                        </div>
                    </div>

                    <!-- Input para el escáner QR (oculto) -->
                    <div class="mt-4">
                        <input type="text" id="qr-input"
                            class="absolute w-full px-2 py-1 border rounded opacity-0 focus:outline-none focus:ring-2 focus:ring-blue-500"
                            placeholder="Escanea un código QR" autofocus>
                    </div>
                </div>
            </div>
        </aside>

        <!-- Contenido principal ajustado con margen izquierdo -->
        <div class="flex-1 h-screen pt-4 pb-[2rem] ml-64 overflow-hidden">
            <div class="flex flex-col h-full">

                <!-- Card: Navegación de Pisos y Plano -->
                <div class="flex flex-col flex-1 min-h-0">
                    <div class="flex-1 bg-white shadow-md dark:bg-dark-eval-0">
                        <ul class="flex border-b border-gray-300 dark:border-gray-700" id="pills-tab" role="tablist">
                            @foreach ($pisos as $piso)
                                                    <li role="presentation">
                                                        <a href="{{ route('plano.show', $piso->id_mapa) }}"
                                                            class="px-4 py-3 text-sm font-semibold transition-all duration-300 rounded-t-xl border border-b-0
                                                                                                                                                                {{ $piso->id_mapa === $mapa->id_mapa
                                ? 'bg-light-cloud-blue text-white border-light-cloud-blue'
                                : 'bg-white text-gray-700 border-gray-300 hover:bg-gray-100 hover:text-light-cloud-blue' }}" role="tab"
                                                            aria-selected="{{ $piso->id_mapa === $mapa->id_mapa ? 'true' : 'false' }}">
                                                            Piso {{ $piso->piso->numero_piso }}
                                                        </a>
                                                    </li>
                            @endforeach
                        </ul>
                        <!-- Card para el canvas y controles -->
                        <div class="flex flex-col h-full">
                            <div class="p-4">
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                                    Plano del Piso {{ $mapa->piso->numero_piso }},
                                    {{ $mapa->piso->facultad->nombre_facultad }},
                                    Sede {{ $mapa->piso->facultad->sede->nombre_sede }}
                                </h3>
                            </div>


                            <div class="relative flex-1 min-h-0">
                                <!-- Canvas para la imagen base -->
                                <canvas id="mapCanvas"
                                    class="absolute inset-0 w-full h-full bg-white dark:bg-gray-800"></canvas>

                                <!-- Canvas para los indicadores -->
                                <canvas id="indicatorsCanvas"
                                    class="absolute inset-0 w-full h-full pointer-events-auto"></canvas>

                                <!-- Botón de pantalla completa -->
                                <button id="fullscreenBtn"
                                    class="absolute bottom-4 right-4 p-2 bg-light-cloud-blue text-white rounded-lg shadow-lg hover:bg-blue-600 transition-colors duration-200 z-10">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5v-4m0 4h-4m4 0l-5-5" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para mostrar información del espacio -->
    <x-modal name="data-space" :show="false" focusable>
        @slot('title')
        <h2 id="modalTitulo" class="text-lg font-medium text-white dark:text-gray-100">
            Cargar Archivo de Datos
        </h2>
        @endslot
        <div class="p-6">

            <h3 class="text-lg font-medium text-gray-900">Información del Espacio</h3>
            <br>
            <div class="space-y-4">
                <div>
                <h4 class="text-sm font-medium text-gray-700">Estado Actual: <span id="modalEstado" class="inline-block ml-1 text-sm text-gray-900"></span></h4>


                </div>
          
                <div id="modalPlanificacion" class="hidden">
                    <h4 class="text-sm font-medium text-gray-700">Planificación Actual</h4>
                    <p id="modalPlanificacionDetalles" class="mt-1 text-sm text-gray-900"></p>
                </div>
                <div id="modalProxima" class="hidden">
                    <h4 class="text-sm font-medium text-gray-700">Próxima Clase</h4>
                    <p id="modalProximaDetalles" class="mt-1 text-sm text-gray-900"></p>
                </div>
            </div>
        </div>
    </x-modal>

    <script>
        // Variables globales para el QR scanner
        let html5QrcodeScanner = null;
        let currentCameraId = null;

        // Variables globales para el estado de la solicitud
        let userId = null;
        let espacioId = null;
        let tieneClaseProgramada = false;
        let duracionSeleccionada = null;
        let noDisponibleReserva = false;

        // Variables globales para control de enfoque
        let qrScanTimeout = null;
        let qrScanAttempts = 0;
        let qrScanMaxAttempts = 30; // 3 segundos si fps=10

        // Variables para el control de pantalla completa
        let isFullscreen = false;
        let originalSidebarDisplay = '';
        let originalMainContentMargin = '';

        // Obtener el ID del mapa de la URL
        const mapaId = window.location.pathname.split('/').pop();

        // Configuración global para los indicadores
        const config = {
            indicatorSize: 30,
            indicatorWidth: 48,
            indicatorHeight: 30,
            indicatorBorder: '#FFFFFF',
            indicatorTextColor: '#FFFFFF',
            fontSize: 12
        };

        // Variables globales para el estado del mapa
        const state = {
            mapImage: null,
            originalImageSize: null,
            indicators: @json($bloques),
            originalCoordinates: @json($bloques),
            isImageLoaded: false,
            mouseX: 0,
            mouseY: 0,
            currentZoom: 1,
            isDragging: false,
            lastX: 0,
            lastY: 0,
            offsetX: 0,
            offsetY: 0,
            currentTime: new Date(),
            currentModule: null,
            currentDay: new Date().getDay(),
            updateInterval: null // Nueva variable para el intervalo de actualización
        };

        // Variables globales para los elementos del canvas
        let elements = {
            mapCanvas: null,
            mapCtx: null,
            indicatorsCanvas: null,
            indicatorsCtx: null
        };

        // Función para inicializar los elementos del canvas
        function initElements() {
            elements.mapCanvas = document.getElementById('mapCanvas');
            elements.mapCtx = elements.mapCanvas.getContext('2d');
            elements.indicatorsCanvas = document.getElementById('indicatorsCanvas');
            elements.indicatorsCtx = elements.indicatorsCanvas.getContext('2d');
        }

        function initCanvases() {
            const container = elements.mapCanvas.parentElement;
            const width = container.clientWidth;
            const height = container.clientHeight;

            elements.mapCanvas.width = width;
            elements.mapCanvas.height = height;
            elements.indicatorsCanvas.width = width;
            elements.indicatorsCanvas.height = height;

            drawCanvas();
            drawIndicators(); // Dibujar indicadores inmediatamente
        }

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

        // Función para calcular la posición de los indicadores
        function calculatePosition(indicator) {
            if (!state.isImageLoaded || !state.mapImage) return {
                x: 0,
                y: 0
            };

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

            const originalIndicator = state.originalCoordinates.find(i => i.id === indicator.id);
            if (!originalIndicator) return {
                x: 0,
                y: 0
            };

            const x = offsetX + (originalIndicator.x / state.originalImageSize.width) * drawWidth;
            const y = offsetY + (originalIndicator.y / state.originalImageSize.height) * drawHeight;

            return {
                x,
                y
            };
        }

        // Función para dibujar un indicador
        function dibujarIndicador(elements, position, finalWidth, finalHeight, color, id, isHovered, detalles,
            moduloActual) {
            // Configurar sombras para el efecto hover
            elements.indicatorsCtx.shadowColor = isHovered ? 'rgba(0, 0, 0, 0.3)' : 'transparent';
            elements.indicatorsCtx.shadowBlur = isHovered ? 10 : 0;
            elements.indicatorsCtx.shadowOffsetX = 0;
            elements.indicatorsCtx.shadowOffsetY = 0;

            // Dibujar el rectángulo del indicador
            elements.indicatorsCtx.fillStyle = color;
            elements.indicatorsCtx.fillRect(
                position.x - finalWidth / 2,
                position.y - finalHeight / 2,
                finalWidth,
                finalHeight
            );

            // Dibujar el borde del indicador
            elements.indicatorsCtx.lineWidth = 2;
            elements.indicatorsCtx.strokeStyle = config.indicatorBorder;
            elements.indicatorsCtx.strokeRect(
                position.x - finalWidth / 2,
                position.y - finalHeight / 2,
                finalWidth,
                finalHeight
            );

            // Dibujar el texto del indicador
            elements.indicatorsCtx.font = `bold ${config.fontSize}px Arial`;
            elements.indicatorsCtx.fillStyle = config.indicatorTextColor;
            elements.indicatorsCtx.textAlign = 'center';
            elements.indicatorsCtx.textBaseline = 'middle';
            elements.indicatorsCtx.fillText(id, position.x, position.y);

            // Restablecer las sombras
            elements.indicatorsCtx.shadowColor = 'transparent';
            elements.indicatorsCtx.shadowBlur = 0;

            // Agregar evento de clic al indicador
            elements.indicatorsCanvas.addEventListener('click', function (event) {
                const rect = elements.indicatorsCanvas.getBoundingClientRect();
                const x = event.clientX - rect.left;
                const y = event.clientY - rect.top;

                // Verificar si el clic fue dentro del indicador
                if (x >= position.x - finalWidth / 2 && x <= position.x + finalWidth / 2 &&
                    y >= position.y - finalHeight / 2 && y <= position.y + finalHeight / 2) {
                    const indicator = state.indicators.find(i => i.id === id);
                    if (indicator) {
                        mostrarModalEspacio(indicator);
                    }
                }
            });
        }

        // Función para dibujar los indicadores
        function drawIndicators() {
            if (!state.isImageLoaded) return;
            elements.indicatorsCtx.clearRect(0, 0, elements.indicatorsCanvas.width, elements.indicatorsCanvas.height);

            state.indicators.forEach(indicator => {
                const position = calculatePosition(indicator);
                const color = indicator.estado || '#10B981'; // Color por defecto verde si no hay estado

                dibujarIndicador(
                    elements,
                    position,
                    config.indicatorWidth,
                    config.indicatorHeight,
                    color,
                    indicator.id,
                    false,
                    indicator.detalles || {},
                    null
                );
            });
        }

        // Función para mostrar el modal con la información del espacio
        function mostrarModalEspacio(indicator) {
            const modalTitulo = document.getElementById('modalTitulo');
            const modalEstado = document.getElementById('modalEstado');
            const modalPlanificacion = document.getElementById('modalPlanificacion');
            const modalPlanificacionDetalles = document.getElementById('modalPlanificacionDetalles');
            const modalProxima = document.getElementById('modalProxima');
            const modalProximaDetalles = document.getElementById('modalProximaDetalles');

            // Configurar el título
            modalTitulo.textContent = `${indicator.nombre} (${indicator.id}) `;

            // Configurar el estado
            let estadoTexto = '';
            let estadoColor = '';
            switch (indicator.estado) {
                case 'red':
                    estadoTexto = 'Ocupado';
                    estadoColor = 'text-red-600';
                    break;
                case 'blue':
                    estadoTexto = 'Próximo a ocuparse';
                    estadoColor = 'text-blue-600';
                    break;
                default:
                    estadoTexto = 'Disponible';
                    estadoColor = 'text-green-600';
            }
            modalEstado.innerHTML = `<span class="${estadoColor} font-semibold">${estadoTexto}</span>`;

            const detalles = indicator.detalles || {};
        
            if (detalles.planificacion) {
                modalPlanificacion.classList.remove('hidden');
                modalPlanificacionDetalles.innerHTML = `
                    <p>Asignatura: ${detalles.planificacion.asignatura || 'No especificada'}</p>
                    <p>Profesor: ${detalles.planificacion.profesor || 'No especificado'}</p>
                    <p>Módulo: ${detalles.planificacion.modulo || 'No especificado'}</p>
                `;
            } else {
                modalPlanificacion.classList.add('hidden');
            }

            // Configurar la próxima clase si existe
            if (detalles.planificacion_proxima) {
                modalProxima.classList.remove('hidden');
                modalProximaDetalles.innerHTML = `
                    <p>Asignatura: ${detalles.planificacion_proxima.asignatura || 'No especificada'}</p>
                    <p>Profesor: ${detalles.planificacion_proxima.profesor || 'No especificado'}</p>
                    <p>Módulo: ${detalles.planificacion_proxima.modulo || 'No especificado'}</p>
                    <p>Horario: ${detalles.planificacion_proxima.hora_inicio} hrs. - ${detalles.planificacion_proxima.hora_termino} hrs.</p>
                `;
            } else {
                modalProxima.classList.add('hidden');
            }

            // Mostrar el modal usando Alpine.js
            window.dispatchEvent(new CustomEvent('open-modal', { detail: 'data-space' }));
        }

        // Definición de horarios por día y módulo
        const horariosModulos = {
            lunes: {
                1: {
                    inicio: '08:10:00',
                    fin: '09:00:00'
                },
                2: {
                    inicio: '09:10:00',
                    fin: '10:00:00'
                },
                3: {
                    inicio: '10:10:00',
                    fin: '11:00:00'
                },
                4: {
                    inicio: '11:10:00',
                    fin: '12:00:00'
                },
                5: {
                    inicio: '12:10:00',
                    fin: '13:00:00'
                },
                6: {
                    inicio: '13:10:00',
                    fin: '14:00:00'
                },
                7: {
                    inicio: '14:10:00',
                    fin: '15:00:00'
                },
                8: {
                    inicio: '15:10:00',
                    fin: '16:00:00'
                },
                9: {
                    inicio: '16:10:00',
                    fin: '17:00:00'
                },
                10: {
                    inicio: '17:10:00',
                    fin: '18:00:00'
                },
                11: {
                    inicio: '18:10:00',
                    fin: '19:00:00'
                },
                12: {
                    inicio: '19:10:00',
                    fin: '20:00:00'
                },
                13: {
                    inicio: '20:10:00',
                    fin: '21:00:00'
                },
                14: {
                    inicio: '21:10:00',
                    fin: '22:00:00'
                },
                15: {
                    inicio: '22:10:00',
                    fin: '23:00:00'
                }
            },
            martes: {
                1: {
                    inicio: '08:10:00',
                    fin: '09:00:00'
                },
                2: {
                    inicio: '09:10:00',
                    fin: '10:00:00'
                },
                3: {
                    inicio: '10:10:00',
                    fin: '11:00:00'
                },
                4: {
                    inicio: '11:10:00',
                    fin: '12:00:00'
                },
                5: {
                    inicio: '12:10:00',
                    fin: '13:00:00'
                },
                6: {
                    inicio: '13:10:00',
                    fin: '14:00:00'
                },
                7: {
                    inicio: '14:10:00',
                    fin: '15:00:00'
                },
                8: {
                    inicio: '15:10:00',
                    fin: '16:00:00'
                },
                9: {
                    inicio: '16:10:00',
                    fin: '17:00:00'
                },
                10: {
                    inicio: '17:10:00',
                    fin: '18:00:00'
                },
                11: {
                    inicio: '18:10:00',
                    fin: '19:00:00'
                },
                12: {
                    inicio: '19:10:00',
                    fin: '20:00:00'
                },
                13: {
                    inicio: '20:10:00',
                    fin: '21:00:00'
                },
                14: {
                    inicio: '21:10:00',
                    fin: '22:00:00'
                },
                15: {
                    inicio: '22:10:00',
                    fin: '23:00:00'
                }
            },
            miercoles: {
                1: {
                    inicio: '08:10:00',
                    fin: '09:00:00'
                },
                2: {
                    inicio: '09:10:00',
                    fin: '10:00:00'
                },
                3: {
                    inicio: '10:10:00',
                    fin: '11:00:00'
                },
                4: {
                    inicio: '11:10:00',
                    fin: '12:00:00'
                },
                5: {
                    inicio: '12:10:00',
                    fin: '13:00:00'
                },
                6: {
                    inicio: '13:10:00',
                    fin: '14:00:00'
                },
                7: {
                    inicio: '14:10:00',
                    fin: '15:00:00'
                },
                8: {
                    inicio: '15:10:00',
                    fin: '16:00:00'
                },
                9: {
                    inicio: '16:10:00',
                    fin: '17:00:00'
                },
                10: {
                    inicio: '17:10:00',
                    fin: '18:00:00'
                },
                11: {
                    inicio: '18:10:00',
                    fin: '19:00:00'
                },
                12: {
                    inicio: '19:10:00',
                    fin: '20:00:00'
                },
                13: {
                    inicio: '20:10:00',
                    fin: '21:00:00'
                },
                14: {
                    inicio: '21:10:00',
                    fin: '22:00:00'
                },
                15: {
                    inicio: '22:10:00',
                    fin: '23:00:00'
                }
            },
            jueves: {
                1: {
                    inicio: '08:10:00',
                    fin: '09:00:00'
                },
                2: {
                    inicio: '09:10:00',
                    fin: '10:00:00'
                },
                3: {
                    inicio: '10:10:00',
                    fin: '11:00:00'
                },
                4: {
                    inicio: '11:10:00',
                    fin: '12:00:00'
                },
                5: {
                    inicio: '12:10:00',
                    fin: '13:00:00'
                },
                6: {
                    inicio: '13:10:00',
                    fin: '14:00:00'
                },
                7: {
                    inicio: '14:10:00',
                    fin: '15:00:00'
                },
                8: {
                    inicio: '15:10:00',
                    fin: '16:00:00'
                },
                9: {
                    inicio: '16:10:00',
                    fin: '17:00:00'
                },
                10: {
                    inicio: '17:10:00',
                    fin: '18:00:00'
                },
                11: {
                    inicio: '18:10:00',
                    fin: '19:00:00'
                },
                12: {
                    inicio: '19:10:00',
                    fin: '20:00:00'
                },
                13: {
                    inicio: '20:10:00',
                    fin: '21:00:00'
                },
                14: {
                    inicio: '21:10:00',
                    fin: '22:00:00'
                },
                15: {
                    inicio: '22:10:00',
                    fin: '23:00:00'
                }
            },
            viernes: {
                1: {
                    inicio: '08:10:00',
                    fin: '09:00:00'
                },
                2: {
                    inicio: '09:10:00',
                    fin: '10:00:00'
                },
                3: {
                    inicio: '10:10:00',
                    fin: '11:00:00'
                },
                4: {
                    inicio: '11:10:00',
                    fin: '12:00:00'
                },
                5: {
                    inicio: '12:10:00',
                    fin: '13:00:00'
                },
                6: {
                    inicio: '13:10:00',
                    fin: '14:00:00'
                },
                7: {
                    inicio: '14:10:00',
                    fin: '15:00:00'
                },
                8: {
                    inicio: '15:10:00',
                    fin: '16:00:00'
                },
                9: {
                    inicio: '16:10:00',
                    fin: '17:00:00'
                },
                10: {
                    inicio: '17:10:00',
                    fin: '18:00:00'
                },
                11: {
                    inicio: '18:10:00',
                    fin: '19:00:00'
                },
                12: {
                    inicio: '19:10:00',
                    fin: '20:00:00'
                },
                13: {
                    inicio: '20:10:00',
                    fin: '21:00:00'
                },
                14: {
                    inicio: '21:10:00',
                    fin: '22:00:00'
                },
                15: {
                    inicio: '22:10:00',
                    fin: '23:00:00'
                }
            }
        };

        // Función para obtener el día actual en español
        function obtenerDiaActual() {
            const dias = ['domingo', 'lunes', 'martes', 'miercoles', 'jueves', 'viernes', 'sabado'];
            return dias[new Date().getDay()];
        }

        // Función para determinar el módulo actual
        function determinarModulo(hora) {
            const diaActual = obtenerDiaActual();
            const horariosDia = horariosModulos[diaActual];

            if (!horariosDia) return null;

            for (const [modulo, horario] of Object.entries(horariosDia)) {
                if (hora >= horario.inicio && hora < horario.fin) {
                    return parseInt(modulo);
                }
            }
            return null;
        }

        // Función para actualizar solo la hora
        function actualizarHora() {
            const ahora = new Date();
            const horaActual = ahora.toLocaleTimeString('es-ES', {
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit'
            });

            const horaActualElement = document.getElementById('hora-actual');
            if (horaActualElement) {
                horaActualElement.textContent = horaActual;
            }
        }

        // Función para formatear hora a HH:MM
        function formatearHora(horaCompleta) {
            return horaCompleta.slice(0, 5);
        }

        // Función para actualizar el módulo y los colores
        function actualizarModuloYColores() {
            const ahora = new Date();
            const horaActual = ahora.toLocaleTimeString('es-ES', {
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit'
            });

            // Determinar el módulo actual
            const moduloActual = determinarModulo(horaActual);
            const moduloActualElement = document.getElementById('modulo-actual');
            const moduloHorarioElement = document.getElementById('horario-actual');

            if (moduloActual && moduloActualElement && moduloHorarioElement) {
                moduloActualElement.textContent = moduloActual;

                // Obtener el horario del módulo actual
                const diaActual = obtenerDiaActual();
                const horarioModulo = horariosModulos[diaActual][moduloActual];

                // Mostrar solo horas y minutos
                const horarioTexto = `${formatearHora(horarioModulo.inicio)} - ${formatearHora(horarioModulo.fin)}`;
                moduloHorarioElement.textContent = horarioTexto;

                // Actualizar colores de los indicadores y canvas
                actualizarColoresIndicadores();
                drawIndicators();
            } else {
                if (moduloActualElement) moduloActualElement.textContent = 'No hay módulo programado';
                if (moduloHorarioElement) moduloHorarioElement.textContent = '-';

                // Actualizar colores de los indicadores y canvas
                actualizarColoresIndicadores();
                drawIndicators();
            }
        }

        // Función para actualizar los colores de los indicadores
        function actualizarColoresIndicadores() {
            // Aquí va la lógica para actualizar los colores
            // Por ejemplo, si hay un módulo actual, actualizar los colores según corresponda
            const ahora = new Date();
            const horaActual = ahora.toLocaleTimeString('es-ES', {
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit'
            });
            const moduloActual = determinarModulo(horaActual);

            if (moduloActual) {
                // Actualizar los colores según el módulo actual
                // ... tu lógica de colores aquí ...
            }
        }

        // Actualizar la hora cada segundo
        setInterval(actualizarHora, 1000);
        actualizarHora(); // Actualizar inmediatamente al cargar

        // Actualizar módulo y colores cada minuto
        setInterval(actualizarModuloYColores, 60000);
        actualizarModuloYColores(); // Actualizar inmediatamente al cargar

        // Asegurarse de que el modal esté actualizado cuando se abre
        document.addEventListener('DOMContentLoaded', function () {
            const modal = document.getElementById('modal-solicitar-espacio');
            if (modal) {
                modal.addEventListener('show.bs.modal', function () {
                    actualizarHora();
                    actualizarModuloYColores();
                });
            }
        });

        // Inicialización cuando el DOM está listo
        document.addEventListener("DOMContentLoaded", function () {
            const inputEscanner = document.getElementById('qr-input');
            inputEscanner.addEventListener('keydown', manejarInputEscanner);
            document.addEventListener('click', function () {
                inputEscanner.focus();
            });
            inputEscanner.focus();
            // Mostrar un mensaje indicando que el escáner está listo
            const qrStatus = document.getElementById('qr-status');
            qrStatus.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" class="inline-block w-4 h-4 text-yellow-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v2m0 5h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 20h14a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg> Listo para escanear';

            // Inicializar elementos
            initElements();

            const img = new Image();
            img.onload = function () {
                state.mapImage = img;
                state.originalImageSize = {
                    width: img.width,
                    height: img.height
                };
                state.isImageLoaded = true;
                initCanvases();
                drawIndicators();

                // Actualizar cada minuto
                state.updateInterval = setInterval(actualizarEstadoMapa, 60000);
            };
            img.src = "{{ asset('storage/' . $mapa->ruta_mapa) }}";

            window.addEventListener('resize', function () {
                initCanvases();
            });

            // Limpiar el intervalo cuando se desmonte el componente
            window.addEventListener('beforeunload', function () {
                if (state.updateInterval) {
                    clearInterval(state.updateInterval);
                }
            });

            // Inicializar el estado del QR
            actualizarEstadoQR(null);
        });

        window.mostrarDetallesBloque = function (bloque) {
            const titulo = document.getElementById('modal-titulo');
            const tipoEspacio = document.getElementById('modal-tipo-espacio');
            const puestos = document.getElementById('modal-puestos');
            const planificacion = document.getElementById('modal-planificacion');
            const asignatura = document.getElementById('modal-asignatura');
            const profesor = document.getElementById('modal-profesor');
            const modulos = document.getElementById('modal-modulos');
            const claseProxima = document.getElementById('modal-clase-proxima');
            const asignaturaProxima = document.getElementById('modal-asignatura-proxima');
            const profesorProximo = document.getElementById('modal-profesor-proximo');
            const horarioProximo = document.getElementById('modal-horario-proximo');
            const reserva = document.getElementById('modal-reserva');
            const fechaReserva = document.getElementById('modal-fecha-reserva');
            const horaReserva = document.getElementById('modal-hora-reserva');

            // Limpiar contenido anterior
            titulo.textContent = bloque.nombre;
            tipoEspacio.textContent = bloque.tipo_espacio || bloque.detalles?.tipo_espacio || 'No especificado';
            puestos.textContent = `${bloque.puestos_disponibles || bloque.detalles?.puestos_disponibles || 0} puestos`;
            planificacion.classList.add('hidden');
            claseProxima.classList.add('hidden');
            reserva.classList.add('hidden');
            modulos.innerHTML = '';

            // Verificar si hay una reserva activa
            fetch(`/api/reserva-activa/${bloque.id}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.reserva) {
                        // Si el espacio está ocupado, mostrar los detalles de la reserva
                        reserva.classList.remove('hidden');

                        if (data.reserva.tipo_reserva === 'Ocupación sin reserva') {
                            // Caso de espacio ocupado sin reserva activa
                            fechaReserva.textContent = 'Estado: Ocupado';
                            document.getElementById('modal-profesor-reserva').textContent =
                                `Profesor: ${data.reserva.profesor_nombre || 'Sin información'}`;
                            document.getElementById('modal-email-reserva').textContent =
                                `Email: ${data.reserva.profesor_email || 'Sin información'}`;
                        } else {
                            // Caso de reserva activa normal
                            fechaReserva.textContent =
                                `Fecha: ${new Date(data.reserva.fecha).toLocaleDateString()}`;
                            document.getElementById('modal-profesor-reserva').textContent =
                                `Profesor: ${data.reserva.profesor_nombre}`;
                            document.getElementById('modal-email-reserva').textContent =
                                `Email: ${data.reserva.profesor_email}`;
                        }

                        // Agregar botón para entregar llaves
                        const btnEntregarLlaves = document.createElement('button');
                        btnEntregarLlaves.className =
                            'mt-4 px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded hover:bg-blue-700';
                        btnEntregarLlaves.textContent = '¿Desea entregar las llaves?';
                        btnEntregarLlaves.onclick = function () {
                            // Cerrar el modal actual
                            window.dispatchEvent(new CustomEvent('close-modal', {
                                detail: 'detalles-bloque'
                            }));

                            // Esperar a que el modal se cierre
                            setTimeout(() => {
                                // Abrir el modal de salida
                                window.dispatchEvent(new CustomEvent('open-modal', {
                                    detail: 'salida-espacio'
                                }));

                                // Iniciar el escáner después de un breve delay
                                setTimeout(() => {
                                    initQRScannerSalidaProfesor();
                                }, 300);
                            }, 300);
                        };
                        reserva.appendChild(btnEntregarLlaves);
                    } else {
                        // Si no hay reserva activa, mostrar información de clase próxima si existe
                        if (bloque.estado === 'blue' && bloque.detalles?.planificacion_proxima) {
                            claseProxima.classList.remove('hidden');
                            asignaturaProxima.textContent = `Asignatura: ${bloque.clase_proxima.asignatura}`;
                            profesorProximo.textContent = `Profesor: ${bloque.clase_proxima.profesor}`;
                            horarioProximo.textContent =
                                `Horario: ${bloque.clase_proxima.hora_inicio} - ${bloque.clase_proxima.hora_termino}`;
                        }
                    }
                })
                .catch(error => {
                    // En caso de error, mostrar información de clase próxima si existe
                    if (bloque.estado === 'blue' && bloque.detalles?.planificacion_proxima) {
                        claseProxima.classList.remove('hidden');
                        asignaturaProxima.textContent = `Asignatura: ${bloque.clase_proxima.asignatura}`;
                        profesorProximo.textContent = `Profesor: ${bloque.clase_proxima.profesor}`;
                        horarioProximo.textContent =
                            `Horario: ${bloque.clase_proxima.hora_inicio} - ${bloque.clase_proxima.hora_termino}`;
                    }
                });

            // Abrir el modal
            window.dispatchEvent(new CustomEvent('open-modal', {
                detail: 'detalles-bloque'
            }));
        };

        function iniciarRegistroSalida(espacioId) {
            // Mostrar el modal de registro de salida
            window.dispatchEvent(new CustomEvent('open-modal', {
                detail: 'salida-espacio'
            }));

            // Iniciar el escáner de QR del profesor
            initQRScannerSalidaProfesor();
        }

        // Función para inicializar el escáner de QR para el profesor en la salida
        async function initQRScannerSalidaProfesor() {
            if (html5QrcodeScanner === null) {
                try {
                    document.getElementById('salida-profesor-cargando-msg').textContent =
                        'Cargando escáner, por favor espere...';
                    document.getElementById('salida-profesor-cargando-msg').classList.remove('hidden');
                    document.getElementById('salida-profesor-error-msg').classList.add('hidden');

                    const hasPermission = await requestCameraPermission();
                    if (!hasPermission) {
                        document.getElementById('salida-profesor-cargando-msg').textContent = '';
                        document.getElementById('salida-profesor-error-msg').textContent =
                            'Se requieren permisos de cámara para escanear códigos QR';
                        document.getElementById('salida-profesor-error-msg').classList.remove('hidden');
                        return;
                    }

                    currentCameraId = await getFirstCamera();
                    if (!currentCameraId) {
                        document.getElementById('salida-profesor-cargando-msg').textContent = '';
                        document.getElementById('salida-profesor-error-msg').textContent =
                            'No se encontró ninguna cámara disponible';
                        document.getElementById('salida-profesor-error-msg').classList.remove('hidden');
                        return;
                    }

                    const config = {
                        fps: 60,
                        qrbox: {
                            width: 300,
                            height: 300
                        },
                        aspectRatio: 1.0,
                        formatsToSupport: [Html5QrcodeSupportedFormats.QR_CODE],
                        rememberLastUsedCamera: true,
                        showTorchButtonIfSupported: true,
                        autoFocus: true,
                        disableFlip: false,
                        showZoomSliderIfSupported: true,
                        defaultZoomValueIfSupported: 2,
                        experimentalFeatures: {
                            useBarCodeDetectorIfSupported: true
                        }
                    };

                    html5QrcodeScanner = new Html5Qrcode("qr-reader-salida-profesor", {
                        verbose: false,
                        formatsToSupport: [Html5QrcodeSupportedFormats.QR_CODE]
                    });

                    // Configurar el estilo del contenedor del escáner
                    const scannerContainer = document.getElementById('qr-reader-salida-profesor');
                    scannerContainer.style.position = 'relative';
                    scannerContainer.style.width = '100%';
                    scannerContainer.style.maxWidth = '500px';
                    scannerContainer.style.margin = '0 auto';

                    // Agregar estilos para la previsualización
                    const style = document.createElement('style');
                    style.textContent = `
                        #qr-reader-salida-profesor video {
                            width: 100% !important;
                            height: auto !important;
                            border-radius: 8px;
                            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
                        }
                        #qr-reader-salida-profesor__scan_region {
                            position: relative;
                        }
                        #qr-reader-salida-profesor__scan_region::after {
                            content: '';
                            position: absolute;
                            top: 0;
                            left: 0;
                            right: 0;
                            bottom: 0;
                            border: 2px solid #4CAF50;
                            border-radius: 8px;
                            animation: pulse 2s infinite;
                        }
                        @keyframes pulse {
                            0% { opacity: 1; }
                            50% { opacity: 0.5; }
                            100% { opacity: 1; }
                        }
                    `;
                    document.head.appendChild(style);

                    document.getElementById('salida-profesor-placeholder').style.display = 'none';

                    await html5QrcodeScanner.start(
                        currentCameraId,
                        config,
                        onSalidaProfesorScanSuccess,
                        (error) => {
                            if (error.includes("QR code parse error")) return;
                        }
                    );

                    // Configurar el enfoque automático mejorado
                    const videoElement = scannerContainer.querySelector('video');
                    if (videoElement) {
                        videoElement.addEventListener('loadedmetadata', async () => {
                            if (videoElement.srcObject) {
                                const track = videoElement.srcObject.getVideoTracks()[0];
                                const capabilities = track.getCapabilities();

                                if (capabilities.focusMode) {
                                    // Configurar el enfoque automático con prioridad en objetos cercanos
                                    await track.applyConstraints({
                                        advanced: [{
                                            focusMode: 'continuous',
                                            exposureMode: 'continuous',
                                            whiteBalanceMode: 'continuous',
                                            focusDistance: 0.1, // Priorizar objetos cercanos
                                            pointsOfInterest: [{
                                                x: 0.5,
                                                y: 0.5
                                            }], // Enfocar en el centro
                                            exposureTime: 0, // Exposición automática
                                            colorTemperature: 0, // Temperatura de color automática
                                            iso: 0, // ISO automático
                                            brightness: 0, // Brillo automático
                                            contrast: 0, // Contraste automático
                                            saturation: 0, // Saturación automática
                                            sharpness: 0, // Nitidez automática
                                            zoom: 2 // Zoom inicial para mejor detección cercana
                                        }]
                                    });

                                    // Configurar el detector de proximidad
                                    const observer = new IntersectionObserver((entries) => {
                                        entries.forEach(entry => {
                                            if (entry.isIntersecting) {
                                                // Cuando el QR está visible, ajustar el enfoque
                                                track.applyConstraints({
                                                    advanced: [{
                                                        focusMode: 'continuous',
                                                        focusDistance: 0.1
                                                    }]
                                                });
                                            }
                                        });
                                    }, {
                                        threshold: 0.5 // Activar cuando el QR esté al menos 50% visible
                                    });

                                    // Observar el elemento de video
                                    observer.observe(videoElement);
                                }
                            }
                        });
                    }
                } catch (err) {
                    document.getElementById('salida-profesor-cargando-msg').textContent = '';
                    document.getElementById('salida-profesor-error-msg').textContent =
                        'Error al iniciar la cámara. Por favor, verifica los permisos y que la cámara no esté siendo usada por otra aplicación.';
                    document.getElementById('salida-profesor-error-msg').classList.remove('hidden');
                    document.getElementById('salida-profesor-placeholder').style.display = 'flex';
                }
            }
        }

        function onSalidaProfesorScanSuccess(decodedText) {
            if (html5QrcodeScanner) {
                html5QrcodeScanner.stop();
                html5QrcodeScanner = null;
            }

            // Extraer el RUN de la URL
            const runMatch = decodedText.match(/RUN=(\d+)-/);
            if (!runMatch) {
                mostrarErrorEscaneoSalida('El código QR no contiene un RUN válido');
                return;
            }
            const run = runMatch[1];

            window.profesorRunSalida =
                fetch(`/api/user/${run}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success && data.user) {
                            document.getElementById('profesor-nombre-salida').textContent = data.user.name || '';
                            document.getElementById('profesor-correo-salida').textContent = data.user.email || '';
                            document.getElementById('profesor-info-salida').classList.remove('hidden');
                            document.getElementById('profesor-scan-section-salida').classList.add('hidden');
                            document.getElementById('espacio-scan-section-salida').classList.remove('hidden');
                            initEspacioScannerSalida();
                        } else {
                            mostrarErrorEscaneoSalida('La persona no se encuentra registrada, contáctese con soporte.');
                        }
                    })
                    .catch(error => {
                        mostrarErrorEscaneoSalida(error.message || 'Error al obtener información del profesor');
                    });
        }

        // Función para inicializar el escáner de espacio en la salida
        async function initEspacioScannerSalida() {
            try {
                const hasPermission = await requestCameraPermission();
                if (!hasPermission) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Se requieren permisos de cámara para escanear códigos QR'
                    });
                    return;
                }

                currentCameraId = await getFirstCamera();
                if (!currentCameraId) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'No se encontró ninguna cámara disponible'
                    });
                    return;
                }

                const config = {
                    fps: 10,
                    aspectRatio: 1.0,
                    formatsToSupport: [Html5QrcodeSupportedFormats.QR_CODE],
                    rememberLastUsedCamera: true,
                    showTorchButtonIfSupported: true,
                    autoFocus: true
                };

                html5QrcodeScanner = new Html5Qrcode("qr-reader-salida-espacio");
                await html5QrcodeScanner.start(
                    currentCameraId,
                    config,
                    onSalidaEspacioScanSuccess,
                    (error) => {
                        if (error.includes("QR code parse error")) return;
                    }
                );
            } catch (err) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Error al iniciar la cámara. Por favor, verifica los permisos y que la cámara no esté siendo usada por otra aplicación.'
                });
            }
        }

        function onSalidaEspacioScanSuccess(decodedText) {
            if (html5QrcodeScanner) {
                html5QrcodeScanner.stop();
                html5QrcodeScanner = null;
            }

            if (!window.profesorRunSalida) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'No se encontró la información del profesor'
                });
                return;
            }

            registrarSalidaClase(window.profesorRunSalida, decodedText);
        }

        function registrarSalidaClase(run, espacioId) {
            fetch('/api/registrar-salida-clase', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({
                    run: run,
                    espacio_id: espacioId
                })
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const block = state.indicators.find(b => b.id === espacioId);
                        if (block) {
                            block.estado = 'green';
                            state.originalCoordinates = state.indicators.map(i => ({
                                ...i
                            }));
                            drawIndicators();
                        }
                        Swal.fire({
                            icon: 'success',
                            title: '¡Éxito!',
                            text: data.message,
                            showConfirmButton: false,
                            timer: 2000
                        }).then(() => {
                            window.dispatchEvent(new CustomEvent('close-modal', {
                                detail: 'detalles-bloque'
                            }));
                            location.reload();
                        });
                    } else {
                        throw new Error(data.message || 'Error al registrar la salida');
                    }
                })
                .catch(error => {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: error.message || 'Ocurrió un error al registrar la salida'
                    });
                });
        }

        function mostrarErrorEscaneoSalida(mensaje) {
            const errorMsg = document.getElementById('salida-profesor-error-msg');
            const cargandoMsg = document.getElementById('salida-profesor-cargando-msg');
            const btnReintentar = document.getElementById('btn-reintentar-salida-profesor');
            const qrPlaceholder = document.getElementById('salida-profesor-placeholder');

            if (errorMsg) {
                errorMsg.textContent = mensaje;
                errorMsg.classList.remove('hidden');
            }
            if (cargandoMsg) cargandoMsg.textContent = '';
            if (btnReintentar) btnReintentar.classList.remove('hidden');
            if (qrPlaceholder) qrPlaceholder.style.display = 'flex';
        }

        function reiniciarEscaneoSalidaProfesor() {
            document.getElementById('salida-profesor-error-msg').classList.add('hidden');
            document.getElementById('btn-reintentar-salida-profesor').classList.add('hidden');
            document.getElementById('salida-profesor-cargando-msg').textContent = 'Cargando escáner, por favor espere...';
            document.getElementById('salida-profesor-cargando-msg').classList.remove('hidden');
            initQRScannerSalidaProfesor();
        }

        // Función para solicitar permisos de cámara
        async function requestCameraPermission() {
            try {
                const stream = await navigator.mediaDevices.getUserMedia({
                    video: true
                });
                stream.getTracks().forEach(track => track.stop());
                return true;
            } catch (err) {
                return false;
            }
        }

        // Función para obtener la primera cámara disponible
        async function getFirstCamera() {
            try {
                const devices = await navigator.mediaDevices.enumerateDevices();
                const videoDevices = devices.filter(device => device.kind === 'videoinput');
                return videoDevices[0]?.deviceId || null;
            } catch (err) {
                return null;
            }
        }

        // Función para obtener el código del día
        function obtenerCodigoDia(dia) {
            const codigos = {
                'lunes': 'LU',
                'martes': 'MA',
                'miercoles': 'MI',
                'jueves': 'JU',
                'viernes': 'VI'
            };
            return codigos[dia] || '';
        }

        // Función para buscar módulo por código
        function buscarModuloPorCodigo(codigo) {
            // Separar el código en día y módulo (ejemplo: "JU.1")
            const [codigoDia, numeroModulo] = codigo.split('.');

            // Encontrar el día correspondiente al código
            const dia = Object.entries(horariosModulos).find(([_, value]) =>
                obtenerCodigoDia(value) === codigoDia
            )?.[0];

            if (!dia || !numeroModulo) return null;

            const modulo = horariosModulos[dia][parseInt(numeroModulo)];
            if (!modulo) return null;

            return {
                dia,
                modulo: parseInt(numeroModulo),
                horario: modulo
            };
        }

        // Función para mostrar información del módulo
        function mostrarInfoModulo(codigo) {
            const info = buscarModuloPorCodigo(codigo);
            if (!info) {
                return;
            }
        }

        // Función para actualizar el estado del QR y mostrar nombre
        async function actualizarEstadoQR(run) {
            const qrStatus = document.getElementById('qr-status');
            const runEscaneado = document.getElementById('run-escaneado');
            const nombreUsuario = document.getElementById('nombre-usuario');

            if (run) {
                runEscaneado.textContent = run;
                // Buscar usuario por RUN en la API
                try {
                    const response = await fetch(`/api/user/${run}`);
                    const data = await response.json();
                    if (data.success && data.user) {
                        qrStatus.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" class="inline-block w-4 h-4 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg> Usuario encontrado';
                        nombreUsuario.textContent = data.user.name;
                    } else {
                        qrStatus.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" class="inline-block w-4 h-4 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg> RUN no encontrado';
                        nombreUsuario.textContent = '--';
                    }
                } catch (e) {
                    qrStatus.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" class="inline-block w-4 h-4 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg> Error de conexión';
                    nombreUsuario.textContent = '--';
                }
            } else {
                qrStatus.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" class="inline-block w-4 h-4 text-yellow-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v2m0 5h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 20h14a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg> Esperando';
                runEscaneado.textContent = '--';
                nombreUsuario.textContent = '--';
            }
        }

        // Función para manejar el input del escáner
        let bufferQR = '';
        let lastScanTime = 0;

        function manejarInputEscanner(event) {
            const currentTime = new Date().getTime();
            if (currentTime - lastScanTime > 100) {
                bufferQR = '';
            }
            lastScanTime = currentTime;

            if (event.key.length === 1) {
                bufferQR += event.key;
            }

            const match = bufferQR.match(/RUN¿(\d+)'/);
            if (match) {
                const run = match[1];
                document.getElementById('run-escaneado').textContent = run;
                // Consultar la API para obtener el nombre
                fetch(`/api/user/${run}`)
                    .then(res => res.json())
                    .then(data => {
                        if (data.success && data.user) {
                            document.getElementById('nombre-usuario').textContent = data.user.name;
                            document.getElementById('qr-status').innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" class="inline-block w-4 h-4 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg> Usuario encontrado';
                        } else {
                            document.getElementById('nombre-usuario').textContent = '--';
                            document.getElementById('qr-status').innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" class="inline-block w-4 h-4 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg> RUN no encontrado';
                        }
                    })
                    .catch(() => {
                        document.getElementById('nombre-usuario').textContent = '--';
                        document.getElementById('qr-status').innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" class="inline-block w-4 h-4 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg> Error de conexión';
                    });
                bufferQR = '';
                event.target.value = '';
            }

            if (event.key === 'Escape' || bufferQR.length > 30) {
                bufferQR = '';
                event.target.value = '';
            }
        }

        // Función para manejar el modo pantalla completa
        function toggleFullscreen() {
            const mainContent = document.querySelector('.flex-1.h-screen');
            const sidebar = document.querySelector('aside');
            const fullscreenBtn = document.getElementById('fullscreenBtn');

            if (!isFullscreen) {
                // Guardar el estado original
                originalSidebarDisplay = sidebar.style.display;
                originalMainContentMargin = mainContent.style.marginLeft;

                // Ocultar sidebar y ajustar contenido principal
                sidebar.style.display = 'none';
                mainContent.style.marginLeft = '0';
                mainContent.style.width = '100%';

                // Cambiar el ícono del botón
                fullscreenBtn.innerHTML = `
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                `;
            } else {
                // Restaurar el estado original
                sidebar.style.display = originalSidebarDisplay;
                mainContent.style.marginLeft = originalMainContentMargin;
                mainContent.style.width = '';

                // Restaurar el ícono original
                fullscreenBtn.innerHTML = `
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5v-4m0 4h-4m4 0l-5-5" />
                    </svg>
                `;
            }

            isFullscreen = !isFullscreen;
            initCanvases(); // Redibujar los canvas para ajustarse al nuevo tamaño
        }

        // Agregar el evento click al botón de pantalla completa
        document.addEventListener('DOMContentLoaded', function () {
            const fullscreenBtn = document.getElementById('fullscreenBtn');
            fullscreenBtn.addEventListener('click', toggleFullscreen);
        });

        // Función para actualizar el estado del mapa
        async function actualizarEstadoMapa() {
            try {
                const response = await fetch(`/plano/${mapaId}/bloques`);
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                const data = await response.json();
                state.indicators = data;
                drawIndicators();
            } catch (error) {
                console.error('Error al actualizar el estado del mapa:', error);
            }
        }
    </script>
</x-show-layout>