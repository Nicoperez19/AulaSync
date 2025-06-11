<x-show-layout>
    <div class="flex">
        <!-- Sidebar fijo a la izquierda, más compacto y con fondo azul claro -->
        <aside
            class="w-48 min-h-screen bg-light-cloud-blue border-r border-gray-200 dark:border-gray-700 flex flex-col justify-between fixed left-0 top-0 z-40 pt-4 pb-4">
            <!-- Logo de la aplicación -->
            <div class="flex flex-col items-center gap-4">
                <a href="/" class="mb-2">
                    <x-application-logo-navbar class="w-12 h-12" />
                </a>
                <!-- Leyenda -->
                <div class="w-full px-2 bg-white p-4">
                    <h3 class="mb-1 text-sm font-semibold text-center">Leyenda</h3>
                    <div class="flex flex-col items-start gap-1 text-xs">
                        <div class="flex items-center gap-1">
                            <div class="w-3 h-3 bg-red-500 rounded-sm"></div>
                            <span>Ocupado</span>
                        </div>
                        <div class="flex items-center gap-1">
                            <div class="w-3 h-3 bg-blue-500 rounded-sm"></div>
                            <span>Próximo</span>
                        </div>
                        <div class="flex items-center gap-1">
                            <div class="w-3 h-3 bg-green-500 rounded-sm"></div>
                            <span>Disponible</span>
                        </div>
                        <div class="flex items-center gap-1">
                            <div class="w-3 h-3 bg-orange-500 rounded-sm"></div>
                            <span>Previsto</span>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Información de hora y módulo actual -->
            <div class="w-full px-2 mt-4">
                <div class="p-2 border border-blue-600 rounded-lg shadow bg-light-cloud-blue text-white text-xs">
                    <div class="flex items-center justify-between pb-1 border-b border-blue-400">
                        <div class="flex items-center gap-1">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span class="font-semibold">Hora</span>
                        </div>
                        <span id="hora-actual" class="font-bold"></span>
                    </div>
                    <div class="flex items-center justify-between py-1 border-b border-blue-400">
                        <div class="flex items-center gap-1">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                            </svg>
                            <span class="font-semibold">Módulo</span>
                        </div>
                        <span id="modulo-actual"></span>
                    </div>
                    <div class="flex items-center justify-between pt-1">
                        <div class="flex items-center gap-1">
                            <!-- Icono de calendario para Horario -->
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                            <span>Horario</span>
                        </div>
                        <span id="modulo-horario" class="font-semibold"></span>
                    </div>
                </div>
            </div>
        </aside>

        <!-- Contenido principal ajustado con margen izquierdo -->
        <div class="flex-1 ml-48">
            <div class="p-6 space-y-6">
                <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                    <h2 class="text-xl font-semibold leading-tight">

                    </h2>
                </div>
                <!-- Card: Navegación de Pisos y Plano -->
                <div class="w-full">
                    <div class="bg-white shadow-md dark:bg-dark-eval-0 rounded-t-xl">
                        <ul class="flex border-b border-gray-300 dark:border-gray-700" id="pills-tab" role="tablist">
                            @foreach ($pisos as $piso)
                                <li role="presentation">
                                    <a href="{{ route('plano.show', $piso->id_mapa) }}"
                                        class="px-4 py-3 text-sm font-semibold transition-all duration-300 rounded-t-xl border border-b-0
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
                        <div class="p-6 bg-white shadow-md rounded-b-xl dark:bg-gray-800">
                            <div class="flex items-center justify-between mb-4">
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                                    Plano del Piso
                                    {{ $mapa->piso->numero_piso }}<br>
                                    {{ $mapa->piso->facultad->nombre_facultad }},
                                    Sede {{ $mapa->piso->facultad->sede->nombre_sede }}
                                </h3>
                                <div class="flex gap-2">
                                    <button onclick="actualizarEstados(true)"
                                        class="px-4 py-2 text-sm font-medium text-white transition-all duration-300 rounded-md bg-light-cloud-blue hover:bg-opacity-90 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-light-cloud-blue">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="inline w-5 h-5 mr-2" fill="none"
                                            viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M4 4v5h.582M19.418 19A9 9 0 105 5.582" />
                                        </svg>
                                        Actualizar Estados
                                    </button>
                                    <button id="btn-solicitar-espacio" type="button"
                                        class="px-4 py-2 text-sm font-medium text-white transition-all duration-300 rounded-md bg-light-cloud-blue hover:bg-opacity-90 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-light-cloud-blue">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="inline w-5 h-5 mr-2" fill="none"
                                            viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M12 17v-6m0 0V7m0 4h4m-4 0H8m8 4a4 4 0 11-8 0 4 4 0 018 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M15 12h.01" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M17 16l2 2m0 0l-2 2m2-2H7" />
                                        </svg>
                                        Solicitar Espacio
                                    </button>
                                </div>
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
                </div>
            </div>
        </div>
    </div>

    <script src="https://unpkg.com/html5-qrcode"></script>
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

        // Obtener el ID del mapa de la URL
        const mapaId = window.location.pathname.split('/').pop();

        // Configuración global para los indicadores
        const config = {
            indicatorSize: 40,
            indicatorWidth: 60,
            indicatorHeight: 40,
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
            mouseY: 0
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
            if (!state.isImageLoaded || !state.mapImage) return { x: 0, y: 0 };

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
            if (!originalIndicator) return { x: 0, y: 0 };

            const x = offsetX + (originalIndicator.x / state.originalImageSize.width) * drawWidth;
            const y = offsetY + (originalIndicator.y / state.originalImageSize.height) * drawHeight;

            return { x, y };
        }

        // Función para dibujar un indicador
        function dibujarIndicador(elements, position, finalWidth, finalHeight, color, id, isHovered, detalles, moduloActual) {
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

        // Definición de horarios por día y módulo
        const horariosModulos = {
            lunes: {
                1: { inicio: '08:10:00', fin: '09:00:00' },
                2: { inicio: '09:10:00', fin: '10:00:00' },
                3: { inicio: '10:10:00', fin: '11:00:00' },
                4: { inicio: '11:10:00', fin: '12:00:00' },
                5: { inicio: '12:10:00', fin: '13:00:00' },
                6: { inicio: '13:10:00', fin: '14:00:00' },
                7: { inicio: '14:10:00', fin: '15:00:00' },
                8: { inicio: '15:10:00', fin: '16:00:00' },
                9: { inicio: '16:10:00', fin: '17:00:00' },
                10: { inicio: '17:10:00', fin: '18:00:00' },
                11: { inicio: '18:10:00', fin: '19:00:00' },
                12: { inicio: '19:10:00', fin: '20:00:00' },
                13: { inicio: '20:10:00', fin: '21:00:00' },
                14: { inicio: '21:10:00', fin: '22:00:00' },
                15: { inicio: '22:10:00', fin: '23:00:00' }
            },
            martes: {
                1: { inicio: '08:10:00', fin: '09:00:00' },
                2: { inicio: '09:10:00', fin: '10:00:00' },
                3: { inicio: '10:10:00', fin: '11:00:00' },
                4: { inicio: '11:10:00', fin: '12:00:00' },
                5: { inicio: '12:10:00', fin: '13:00:00' },
                6: { inicio: '13:10:00', fin: '14:00:00' },
                7: { inicio: '14:10:00', fin: '15:00:00' },
                8: { inicio: '15:10:00', fin: '16:00:00' },
                9: { inicio: '16:10:00', fin: '17:00:00' },
                10: { inicio: '17:10:00', fin: '18:00:00' },
                11: { inicio: '18:10:00', fin: '19:00:00' },
                12: { inicio: '19:10:00', fin: '20:00:00' },
                13: { inicio: '20:10:00', fin: '21:00:00' },
                14: { inicio: '21:10:00', fin: '22:00:00' },
                15: { inicio: '22:10:00', fin: '23:00:00' }
            },
            miercoles: {
                1: { inicio: '08:10:00', fin: '09:00:00' },
                2: { inicio: '09:10:00', fin: '10:00:00' },
                3: { inicio: '10:10:00', fin: '11:00:00' },
                4: { inicio: '11:10:00', fin: '12:00:00' },
                5: { inicio: '12:10:00', fin: '13:00:00' },
                6: { inicio: '13:10:00', fin: '14:00:00' },
                7: { inicio: '14:10:00', fin: '15:00:00' },
                8: { inicio: '15:10:00', fin: '16:00:00' },
                9: { inicio: '16:10:00', fin: '17:00:00' },
                10: { inicio: '17:10:00', fin: '18:00:00' },
                11: { inicio: '18:10:00', fin: '19:00:00' },
                12: { inicio: '19:10:00', fin: '20:00:00' },
                13: { inicio: '20:10:00', fin: '21:00:00' },
                14: { inicio: '21:10:00', fin: '22:00:00' },
                15: { inicio: '22:10:00', fin: '23:00:00' }
            },
            jueves: {
                1: { inicio: '08:10:00', fin: '09:00:00' },
                2: { inicio: '09:10:00', fin: '10:00:00' },
                3: { inicio: '10:10:00', fin: '11:00:00' },
                4: { inicio: '11:10:00', fin: '12:00:00' },
                5: { inicio: '12:10:00', fin: '13:00:00' },
                6: { inicio: '13:10:00', fin: '14:00:00' },
                7: { inicio: '14:10:00', fin: '15:00:00' },
                8: { inicio: '15:10:00', fin: '16:00:00' },
                9: { inicio: '16:10:00', fin: '17:00:00' },
                10: { inicio: '17:10:00', fin: '18:00:00' },
                11: { inicio: '18:10:00', fin: '19:00:00' },
                12: { inicio: '19:10:00', fin: '20:00:00' },
                13: { inicio: '20:10:00', fin: '21:00:00' },
                14: { inicio: '21:10:00', fin: '22:00:00' },
                15: { inicio: '22:10:00', fin: '23:00:00' }
            },
            viernes: {
                1: { inicio: '08:10:00', fin: '09:00:00' },
                2: { inicio: '09:10:00', fin: '10:00:00' },
                3: { inicio: '10:10:00', fin: '11:00:00' },
                4: { inicio: '11:10:00', fin: '12:00:00' },
                5: { inicio: '12:10:00', fin: '13:00:00' },
                6: { inicio: '13:10:00', fin: '14:00:00' },
                7: { inicio: '14:10:00', fin: '15:00:00' },
                8: { inicio: '15:10:00', fin: '16:00:00' },
                9: { inicio: '16:10:00', fin: '17:00:00' },
                10: { inicio: '17:10:00', fin: '18:00:00' },
                11: { inicio: '18:10:00', fin: '19:00:00' },
                12: { inicio: '19:10:00', fin: '20:00:00' },
                13: { inicio: '20:10:00', fin: '21:00:00' },
                14: { inicio: '21:10:00', fin: '22:00:00' },
                15: { inicio: '22:10:00', fin: '23:00:00' }
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
            const moduloHorarioElement = document.getElementById('modulo-horario');

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
            // Inicializar elementos
            initElements();

            const img = new Image();
            img.onload = function () {
                state.mapImage = img;
                state.originalImageSize = {
                    width: img.naturalWidth,
                    height: img.naturalHeight
                };
                state.isImageLoaded = true;
                initCanvases();
            };
            img.src = "{{ asset('storage/' . $mapa->ruta_mapa) }}";

            window.addEventListener('resize', function () {
                initCanvases();
            });
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
                            document.getElementById('modal-profesor-reserva').textContent = `Profesor: ${data.reserva.profesor_nombre || 'Sin información'}`;
                            document.getElementById('modal-email-reserva').textContent = `Email: ${data.reserva.profesor_email || 'Sin información'}`;
                        } else {
                            // Caso de reserva activa normal
                            fechaReserva.textContent = `Fecha: ${new Date(data.reserva.fecha).toLocaleDateString()}`;
                            document.getElementById('modal-profesor-reserva').textContent = `Profesor: ${data.reserva.profesor_nombre}`;
                            document.getElementById('modal-email-reserva').textContent = `Email: ${data.reserva.profesor_email}`;
                        }

                        // Agregar botón para entregar llaves
                        const btnEntregarLlaves = document.createElement('button');
                        btnEntregarLlaves.className = 'mt-4 px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded hover:bg-blue-700';
                        btnEntregarLlaves.textContent = '¿Desea entregar las llaves?';
                        btnEntregarLlaves.onclick = function () {
                            // Cerrar el modal actual
                            window.dispatchEvent(new CustomEvent('close-modal', { detail: 'detalles-bloque' }));

                            // Esperar a que el modal se cierre
                            setTimeout(() => {
                                // Abrir el modal de salida
                                window.dispatchEvent(new CustomEvent('open-modal', { detail: 'salida-espacio' }));

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
                            horarioProximo.textContent = `Horario: ${bloque.clase_proxima.hora_inicio} - ${bloque.clase_proxima.hora_termino}`;
                        }
                    }
                })
                .catch(error => {
                    // En caso de error, mostrar información de clase próxima si existe
                    if (bloque.estado === 'blue' && bloque.detalles?.planificacion_proxima) {
                        claseProxima.classList.remove('hidden');
                        asignaturaProxima.textContent = `Asignatura: ${bloque.clase_proxima.asignatura}`;
                        profesorProximo.textContent = `Profesor: ${bloque.clase_proxima.profesor}`;
                        horarioProximo.textContent = `Horario: ${bloque.clase_proxima.hora_inicio} - ${bloque.clase_proxima.hora_termino}`;
                    }
                });

            // Abrir el modal
            window.dispatchEvent(new CustomEvent('open-modal', { detail: 'detalles-bloque' }));
        };

        function iniciarRegistroSalida(espacioId) {
            // Mostrar el modal de registro de salida
            window.dispatchEvent(new CustomEvent('open-modal', { detail: 'salida-espacio' }));

            // Iniciar el escáner de QR del profesor
            initQRScannerSalidaProfesor();
        }

        // Función para inicializar el escáner de QR para el profesor en la salida
        async function initQRScannerSalidaProfesor() {
            if (html5QrcodeScanner === null) {
                try {
                    document.getElementById('salida-profesor-cargando-msg').textContent = 'Cargando escáner, por favor espere...';
                    document.getElementById('salida-profesor-cargando-msg').classList.remove('hidden');
                    document.getElementById('salida-profesor-error-msg').classList.add('hidden');

                    const hasPermission = await requestCameraPermission();
                    if (!hasPermission) {
                        document.getElementById('salida-profesor-cargando-msg').textContent = '';
                        document.getElementById('salida-profesor-error-msg').textContent = 'Se requieren permisos de cámara para escanear códigos QR';
                        document.getElementById('salida-profesor-error-msg').classList.remove('hidden');
                        return;
                    }

                    currentCameraId = await getFirstCamera();
                    if (!currentCameraId) {
                        document.getElementById('salida-profesor-cargando-msg').textContent = '';
                        document.getElementById('salida-profesor-error-msg').textContent = 'No se encontró ninguna cámara disponible';
                        document.getElementById('salida-profesor-error-msg').classList.remove('hidden');
                        return;
                    }

                    const config = {
                        fps: 60,
                        qrbox: { width: 300, height: 300 },
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
                                            pointsOfInterest: [{ x: 0.5, y: 0.5 }], // Enfocar en el centro
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
                    document.getElementById('salida-profesor-error-msg').textContent = 'Error al iniciar la cámara. Por favor, verifica los permisos y que la cámara no esté siendo usada por otra aplicación.';
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
                            state.originalCoordinates = state.indicators.map(i => ({ ...i }));
                            drawIndicators();
                        }
                        Swal.fire({
                            icon: 'success',
                            title: '¡Éxito!',
                            text: data.message,
                            showConfirmButton: false,
                            timer: 2000
                        }).then(() => {
                            window.dispatchEvent(new CustomEvent('close-modal', { detail: 'detalles-bloque' }));
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
                const stream = await navigator.mediaDevices.getUserMedia({ video: true });
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
    </script>
</x-show-layout>