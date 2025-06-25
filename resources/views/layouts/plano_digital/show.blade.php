<x-show-layout>
    <div class="flex h-screen overflow-hidden">
        <!-- Sidebar  -->
        <aside
            class="fixed top-0 left-0 z-40 flex flex-col justify-between w-56 h-screen pt-2 pb-2 text-base border-r border-gray-200 md:w-48 sm:w-40 bg-light-cloud-blue dark:border-gray-700 md:text-sm sm:text-xs">

            <!-- Logo arriba -->
            <div class="flex flex-col items-center gap-2 md:gap-1">
                <a href="{{ route('dashboard') }}" class="mb-1">
                    <x-application-logo-navbar class="w-10 h-10 md:w-8 md:h-8 sm:w-6 sm:h-6" />
                </a>
            </div>

            <!-- Contenido central (centrado verticalmente) -->
            <div class="flex flex-col items-center justify-center flex-1">
                <!-- Información de hora y módulo actual -->
                <div class="w-full px-1 mt-2">
                    <div class="p-2 text-white border border-white rounded-md shadow-sm bg-light-cloud-blue">
                        <div class="flex items-center justify-between pb-4">
                            <div class="flex items-center gap-1">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <span id="hora-actual" class="text-2xl font-semibold">--:--:--</span>
                            </div>
                        </div>
                        <div class="py-1 border border-white">
                            <div class="flex items-center gap-1 mb-1">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                </svg>
                                <span class="text-xs">Módulo: <span id="modulo-actual">No hay módulo
                                        programado</span></span>
                            </div>
                        </div>
                        <div class="pt-1">
                            <div class="flex items-center gap-1 mb-1">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                                <span class="text-xs">Horario: <span id="horario-actual">--:-- - --:--</span></span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Estado del QR y usuario -->
                <div class="w-full px-1 ">
                    <div class="p-2 text-white border border-white rounded-md shadow-sm bg-light-cloud-blue">
                        <!-- QR Placeholder -->
                        <div class="p-2 mt-2 text-center rounded-md bg-white/10">
                            <div class="relative">
                                <span id="qr-status" class="text-xs text-yellow-400">Esperando</span>
                                <button id="infoButton"
                                    class="absolute top-0 right-0 p-1 m-4 text-white bg-white rounded-full hover:bg-gray-200 focus:outline-none">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 stroke-black" fill="none"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>

                                </button>
                            </div>
                            <div class="mt-1 mb-1 qr-placeholder">
                                <div class="flex items-center justify-center w-20 h-20 mx-auto rounded-md bg-white/20">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-10 h-10 text-white/40" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 4v1m6 11h2m-6 0h-2v4m0-11v2m0 5h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 20h14a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                </div>
                            </div>
                            <p class="text-xs text-white/80">Escanee el código QR</p>
                        </div>
                        <!-- Información del usuario escaneado -->
                        <div class="mt-2 space-y-1">
                            <div class="flex items-center gap-1">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                                <span class="text-xs font-semibold">RUN:</span>
                                <span id="run-escaneado" class="flex-1 text-xs text-right">--</span>
                            </div>
                            <div class="flex items-center gap-1">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                                <span class="text-xs font-semibold">Usuario:</span>
                                <span id="nombre-usuario" class="flex-1 text-xs text-right">--</span>


                            </div>

                            <div class="flex items-center gap-1">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                                <span class="text-xs font-semibold">Espacio:</span>
                                <span id="nombre-espacio" class="flex-1 text-xs text-right">--</span>


                            </div>


                        </div>
                        <!-- Input para el escáner QR (oculto) -->
                        <div class="mt-2">
                            <input type="text" id="qr-input"
                                class="absolute w-full px-1 py-1 border rounded opacity-0 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                placeholder="Escanea un código QR" autofocus>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Leyenda abajo del todo -->
            <div class="w-full p-2 px-1 mt-auto bg-white rounded-md shadow-sm">
                <h3 class="flex items-center justify-center gap-1 mb-2 text-sm font-semibold text-center md:text-xs">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 md:w-3 md:h-3" viewBox="0 0 24 24"
                        fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
                        <circle cx="12" cy="10" r="3"></circle>
                    </svg>
                    LEYENDA DE ESTADO
                </h3>
                <div class="flex flex-col items-start gap-1">
                    <div class="flex items-center w-full gap-1">
                        <div class="w-3 h-3 bg-red-500 rounded-full"></div>
                        <span class="flex-1 text-xs">Ocupado</span>
                    </div>
                    <div class="flex items-center w-full gap-1">
                        <div class="w-3 h-3 bg-yellow-500 rounded-full animate-pulse"></div>
                        <span class="flex-1 text-xs">Próximo</span>
                    </div>
                    <div class="flex items-center w-full gap-1">
                        <div class="w-3 h-3 bg-green-500 rounded-full"></div>
                        <span class="flex-1 text-xs">Disponible</span>
                    </div>
                    <div class="flex items-center w-full gap-1">
                        <div class="w-3 h-3 bg-blue-500 rounded-full"></div>
                        <span class="flex-1 text-xs">Previsto</span>
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


                            <div class="relative flex-1 min-h-0 h-[calc(100vh-180px)] w-[calc(100%+1rem)] -mx-2">
                                <!-- Canvas para la imagen base -->
                                <canvas id="mapCanvas"
                                    class="absolute inset-0 w-full h-full bg-white dark:bg-gray-800"></canvas>

                                <!-- Canvas para los indicadores -->
                                <canvas id="indicatorsCanvas"
                                    class="absolute inset-0 w-full h-full pointer-events-auto"></canvas>

                                <!-- Botón de pantalla completa -->
                                <button id="fullscreenBtn"
                                    class="absolute z-10 p-2 text-white transition-colors duration-200 rounded-lg shadow-lg bottom-4 right-4 bg-light-cloud-blue hover:bg-blue-600">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none"
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
        <!-- Encabezado rojo -->
        <div class="flex-1 p-4 text-center">
            <h2 id="modalTitulo" class="text-2xl font-bold text-center text-white"></h2>
            <div class="text-xs text-white/80" id="modalSubtitulo"></div>
        </div>
        @endslot
        <!-- Estado visual destacado, separado y más grande -->
        <h3 class="pt-4 mb-2 text-lg font-semibold text-gray-900">Información del Espacio</h3>

        <div class="flex flex-col gap-2 p-5 mb-4 bg-gray-100 shadow rounded-xl">
            <div id="estadoContainer" class="flex items-center justify-between mb-4 ">
                <span class="py-1 text-lg font-medium text-gray-700">Estado actual: </span>
                <span id="estadoPill"
                    class="inline-flex items-center px-4 py-2 text-base font-bold border rounded-full">
                    <span id="estadoIcon" class="w-3 h-3 mr-3 rounded-full"></span>
                    <span id="modalEstado" class="font-semibold"></span>
                </span>
            </div>
        </div>
        <!-- Planificación Actual -->
        <div id="planificacionContainer">
            <h3 class="mb-2 text-lg font-semibold text-gray-900">Planificación Actual</h3>
            <div class="flex flex-col gap-2 p-5 mb-4 bg-gray-100 shadow rounded-xl">
                <div class="flex items-center gap-2">
                    <span class="p-2 text-red-500 bg-red-100 rounded-full">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12h6m2 0a2 2 0 100-4 2 2 0 000 4zm-8 0a2 2 0 100-4 2 2 0 000 4z" />
                        </svg>
                    </span>
                    <div class="flex flex-col text-left">
                        <span class="text-xs text-gray-400">Asignatura</span>
                        <span id="modalPlanificacionAsignatura" class="font-medium text-gray-900"></span>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <span class="p-2 text-red-500 bg-red-100 rounded-full">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M5.121 17.804A13.937 13.937 0 0112 15c2.5 0 4.847.655 6.879 1.804M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                    </span>
                    <div class="flex flex-col text-left">
                        <span class="text-xs text-gray-400">Profesor</span>
                        <span id="modalPlanificacionProfesor" class="font-medium text-gray-900"></span>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <span class="p-2 text-red-500 bg-red-100 rounded-full">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M3 10h1m2 0h1m2 0h1m2 0h1m2 0h1m2 0h1" />
                        </svg>
                    </span>
                    <div class="flex flex-col text-left">
                        <span class="text-xs text-gray-400">Módulo</span>
                        <span id="modalPlanificacionModulo" class="font-medium text-gray-900"></span>
                    </div>
                    <span class="p-2 ml-4 text-red-500 bg-red-100 rounded-full">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3" />
                        </svg>
                    </span>
                    <div class="flex flex-col text-left">
                        <span class="text-xs text-gray-400">Horario</span>
                        <span id="modalPlanificacionHorario" class="font-medium text-gray-900"></span>
                    </div>
                </div>
            </div>
        </div>
        <!-- Próxima clase -->
        <div id="modalProxima" class="hidden mt-4">
            <h4 class="mb-2 text-sm font-medium text-gray-700">Próxima Clase</h4>
            <div id="modalProximaDetalles" class="p-4 bg-gray-100 shadow rounded-xl"></div>
        </div>
        </div>
    </x-modal>

    <!-- Modal para reconocimiento -->
    <x-modal name="reconocimiento" :show="false" focusable>
        @slot('title')
        <h2 class="text-lg font-medium text-white dark:text-gray-100">
            Reconocimiento
        </h2>
        @endslot
        <div class="p-6">
            <div class="flex flex-col items-center justify-center space-y-4">
                <div id="reconocimiento-icono" class="text-6xl">
                    <!-- El ícono se llenará dinámicamente -->
                </div>
                <h3 id="reconocimiento-titulo" class="text-xl font-medium text-gray-900"></h3>
                <div id="reconocimiento-detalles" class="space-y-2 text-sm text-gray-600">
                    <p id="reconocimiento-usuario"></p>
                    <p id="reconocimiento-espacio"></p>
                </div>
            </div>
        </div>
    </x-modal>

    <!-- Modal para instrucciones de uso -->
    <x-modal name="instrucciones-uso" :show="false" focusable>
        @slot('title')
        <h2 class="text-lg font-medium text-white dark:text-gray-100">
            Instrucciones de Uso
        </h2>
        @endslot
        <div class="p-6">
            <div class="space-y-4">
                <div class="flex items-start gap-3">
                    <div class="flex-shrink-0 w-8 h-8 text-blue-500">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-medium text-gray-900">Proceso de Uso del Espacio</h3>
                        <div class="mt-2 space-y-3 text-gray-600">
                            <p>Para registrar el uso del espacio que desea utilizar, siga estos pasos:</p>
                            <ol class="space-y-2 list-decimal list-inside">
                                <li>Escanee el QR del Carnet/Cédula de identidad</li>
                                <li>Luego escanee el QR que poseen las llaves de la sala a utilizar</li>
                            </ol>
                            <p class="mt-4">Para hacer la devolución:</p>
                            <ol class="space-y-2 list-decimal list-inside">
                                <li>Escanee nuevamente el Carnet/Cédula de identidad</li>
                                <li>Vuelva a escanear el QR de las llaves</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </x-modal>

    <!-- Modal para devolución de llaves -->
    <x-modal name="devolver-llaves" :show="false" focusable>
        @slot('title')
        <h2 class="text-lg font-medium text-white dark:text-gray-100">
            Devolver Llaves
        </h2>
        @endslot
        <div class="p-6">
            <div class="flex flex-col items-center justify-center">
                <div
                    class="w-full max-w-md p-2 text-white border border-white rounded-md shadow-sm bg-light-cloud-blue">
                    <!-- QR Placeholder -->
                    <div class="p-2 mt-2 text-center rounded-md bg-white/10">
                        <div class="relative">
                            <span id="qr-status-devolucion" class="text-xs text-yellow-400">Esperando escaneo...</span>
                        </div>
                        <div class="mt-1 mb-1 qr-placeholder">
                            <div class="flex items-center justify-center w-20 h-20 mx-auto rounded-md bg-white/20">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-10 h-10 text-white/40" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 4v1m6 11h2m-6 0h-2v4m0-11v2m0 5h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 20h14a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                            </div>
                        </div>
                        <p class="text-xs text-white/80">Escanee el código QR del usuario y luego del espacio</p>
                    </div>
                    <!-- Input para el escáner QR (oculto) -->
                    <div class="mt-2">
                        <input type="text" id="qr-input-devolucion"
                            class="absolute w-full px-1 py-1 border rounded opacity-0 focus:outline-none focus:ring-2 focus:ring-blue-500"
                            placeholder="Escanea un código QR" autofocus>
                    </div>
                </div>
            </div>
        </div>
    </x-modal>

    <script>
        // Polyfill para roundRect si no está disponible
        if (!CanvasRenderingContext2D.prototype.roundRect) {
            CanvasRenderingContext2D.prototype.roundRect = function (x, y, width, height, radius) {
                if (width < 2 * radius) radius = width / 2;
                if (height < 2 * radius) radius = height / 2;
                this.beginPath();
                this.moveTo(x + radius, y);
                this.arcTo(x + width, y, x + width, y + height, radius);
                this.arcTo(x + width, y + height, x, y + height, radius);
                this.arcTo(x, y + height, x, y, radius);
                this.arcTo(x, y, x + width, y, radius);
                this.closePath();
                return this;
            };
        }

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
            updateInterval: null, // Nueva variable para el intervalo de actualización
            hoveredIndicator: null // Variable para el indicador sobre el que está el mouse
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

        // Función para detectar qué indicador está siendo hover
        function getHoveredIndicator(mouseX, mouseY) {
            if (!state.isImageLoaded) return null;

            for (let i = state.indicators.length - 1; i >= 0; i--) {
                const indicator = state.indicators[i];
                const position = calculatePosition(indicator);

                // Verificar si el mouse está dentro del indicador (considerando el escalado)
                const scale = 1.2; // Mismo factor de escala que en dibujarIndicador
                const scaledWidth = config.indicatorWidth * scale;
                const scaledHeight = config.indicatorHeight * scale;

                if (mouseX >= position.x - scaledWidth / 2 &&
                    mouseX <= position.x + scaledWidth / 2 &&
                    mouseY >= position.y - scaledHeight / 2 &&
                    mouseY <= position.y + scaledHeight / 2) {
                    return indicator;
                }
            }
            return null;
        }

        // Función para manejar el movimiento del mouse
        function handleMouseMove(event) {
            const rect = elements.indicatorsCanvas.getBoundingClientRect();
            const mouseX = event.clientX - rect.left;
            const mouseY = event.clientY - rect.top;

            const hoveredIndicator = getHoveredIndicator(mouseX, mouseY);

            // Solo redibujar si el estado de hover cambió
            if (hoveredIndicator !== state.hoveredIndicator) {
                state.hoveredIndicator = hoveredIndicator;
                drawIndicators();

                // Cambiar el cursor
                elements.indicatorsCanvas.style.cursor = hoveredIndicator ? 'pointer' : 'default';
            }
        }

        // Función para manejar el clic del mouse
        function handleMouseClick(event) {
            const rect = elements.indicatorsCanvas.getBoundingClientRect();
            const mouseX = event.clientX - rect.left;
            const mouseY = event.clientY - rect.top;

            const clickedIndicator = getHoveredIndicator(mouseX, mouseY);

            if (clickedIndicator) {
                mostrarModalEspacio(clickedIndicator);
            }
        }

        // Función para manejar cuando el mouse sale del canvas
        function handleMouseLeave() {
            if (state.hoveredIndicator) {
                state.hoveredIndicator = null;
                drawIndicators();
                elements.indicatorsCanvas.style.cursor = 'default';
            }
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
            // Calcular el factor de escala para el efecto hover
            const scale = isHovered ? 1.2 : 1.0;
            const scaledWidth = finalWidth * scale;
            const scaledHeight = finalHeight * scale;

            // Calcular la posición para que el escalado sea desde el centro
            const offsetX = (scaledWidth - finalWidth) / 2;
            const offsetY = (scaledHeight - finalHeight) / 2;
            const drawX = position.x - scaledWidth / 2;
            const drawY = position.y - scaledHeight / 2;

            // Dibujar el rectángulo del indicador (diseño original)
            elements.indicatorsCtx.fillStyle = color;
            elements.indicatorsCtx.fillRect(drawX, drawY, scaledWidth, scaledHeight);

            // Dibujar el borde del indicador
            elements.indicatorsCtx.lineWidth = 2;
            elements.indicatorsCtx.strokeStyle = config.indicatorBorder;
            elements.indicatorsCtx.strokeRect(drawX, drawY, scaledWidth, scaledHeight);

            // Dibujar el texto del indicador (siempre en el centro original)
            elements.indicatorsCtx.font = `bold ${config.fontSize}px Arial`;
            elements.indicatorsCtx.fillStyle = config.indicatorTextColor;
            elements.indicatorsCtx.textAlign = 'center';
            elements.indicatorsCtx.textBaseline = 'middle';
            elements.indicatorsCtx.fillText(id, position.x, position.y);
        }

        // Función para dibujar los indicadores
        function drawIndicators() {
            if (!state.isImageLoaded) return;
            elements.indicatorsCtx.clearRect(0, 0, elements.indicatorsCanvas.width, elements.indicatorsCanvas.height);

            state.indicators.forEach(indicator => {
                const position = calculatePosition(indicator);
                const color = indicator.estado || '#10B981'; // Color por defecto verde si no hay estado
                const isHovered = state.hoveredIndicator && state.hoveredIndicator.id === indicator.id;

                dibujarIndicador(
                    elements,
                    position,
                    config.indicatorWidth,
                    config.indicatorHeight,
                    color,
                    indicator.id,
                    isHovered,
                    indicator.detalles || {},
                    null
                );
            });
        }

        // Función para mostrar el modal con la información del espacio
        function mostrarModalEspacio(indicator) {
            const modalTitulo = document.getElementById('modalTitulo');
            const modalEstado = document.getElementById('modalEstado');
            // NUEVOS ELEMENTOS DEL MODAL REDISEÑADO
            const modalPlanificacionAsignatura = document.getElementById('modalPlanificacionAsignatura');
            const modalPlanificacionProfesor = document.getElementById('modalPlanificacionProfesor');
            const modalPlanificacionModulo = document.getElementById('modalPlanificacionModulo');
            const modalPlanificacionHorario = document.getElementById('modalPlanificacionHorario');
            const modalProxima = document.getElementById('modalProxima');
            const modalProximaDetalles = document.getElementById('modalProximaDetalles');

            // Configurar el título
            modalTitulo.textContent = `${indicator.nombre} (${indicator.id}) `;

            // Configurar el estado
            let estadoTexto = '';
            let estadoColor = '';
            switch (indicator.estado) {
                case '#FF0000':
                    estadoTexto = 'Ocupado';
                    estadoColor = 'text-red-600';
                    break;
                case '#2563eb':
                    estadoTexto = 'Próximo a ocuparse';
                    estadoColor = 'text-blue-600';
                    break;
                case '#FFA500':
                    estadoTexto = 'Reservado';
                    estadoColor = 'text-yellow-500';
                    break;
                case '#059669':
                    estadoTexto = 'Disponible';
                    estadoColor = 'text-green-600';
                    break;
                default:
                    estadoTexto = 'Sin estado';
                    estadoColor = 'text-gray-600';
            }
            let usuarioOcupando = '';
            if (estadoTexto === 'Ocupado' && indicator.detalles?.usuario_ocupando) {
                usuarioOcupando = `<br><span class='text-xs text-gray-700'>Ocupado por: <b>${indicator.detalles.usuario_ocupando}</b></span>`;
            }
            modalEstado.innerHTML = `<span class=\"${estadoColor} font-semibold\">${estadoTexto}</span>${usuarioOcupando}`;

            const detalles = indicator.detalles || {};
            const infoClaseActual = indicator.informacion_clase_actual;

            // Mostrar la planificación actual en los nuevos campos
            if (infoClaseActual && (indicator.estado === '#FF0000' || indicator.estado === '#FFA500')) {
                modalPlanificacionAsignatura.textContent = infoClaseActual.asignatura || '';
                modalPlanificacionProfesor.textContent = infoClaseActual.profesor || '';
                modalPlanificacionModulo.textContent = infoClaseActual.modulo || '';
                modalPlanificacionHorario.textContent = `${infoClaseActual.hora_inicio} - ${infoClaseActual.hora_termino} hrs`;
            } else if (indicator.estado === '#FF0000') {
                modalPlanificacionAsignatura.textContent = 'No hay información sobre la ocupación actual.';
                modalPlanificacionProfesor.textContent = '';
                modalPlanificacionModulo.textContent = '';
                modalPlanificacionHorario.textContent = '';
            } else if (detalles.planificacion) {
                modalPlanificacionAsignatura.textContent = detalles.planificacion.asignatura || 'No especificada';
                modalPlanificacionProfesor.textContent = detalles.planificacion.profesor || 'No especificado';
                modalPlanificacionModulo.textContent = detalles.planificacion.modulo || 'No especificado';
                modalPlanificacionHorario.textContent = '';
            } else {
                modalPlanificacionAsignatura.textContent = '';
                modalPlanificacionProfesor.textContent = '';
                modalPlanificacionModulo.textContent = '';
                modalPlanificacionHorario.textContent = '';
            }

            // Configurar la próxima clase si existe
            if (detalles.planificacion_proxima) {
                modalProxima.classList.remove('hidden');
                modalProximaDetalles.innerHTML = `
                    <div class='flex flex-col gap-1'>
                        <div><span class='font-semibold'>Asignatura:</span> ${detalles.planificacion_proxima.asignatura || 'No especificada'}</div>
                        <div><span class='font-semibold'>Profesor:</span> ${detalles.planificacion_proxima.profesor || 'No especificado'}</div>
                        <div><span class='font-semibold'>Módulo:</span> ${detalles.planificacion_proxima.modulo || 'No especificado'}</div>
                        <div><span class='font-semibold'>Horario:</span> ${detalles.planificacion_proxima.hora_inicio} - ${detalles.planificacion_proxima.hora_termino} hrs.</div>
                    </div>
                `;
            } else {
                modalProxima.classList.add('hidden');
            }

            // Mostrar el modal usando Alpine.js
            window.dispatchEvent(new CustomEvent('open-modal', {
                detail: 'data-space'
            }));

            // Mostrar/ocultar botón Devolver Llaves según estado
            const btnDevolverContainer = document.getElementById('btnDevolverContainer');
            if (btnDevolverContainer) {
                if (estadoTexto === 'Ocupado') {
                    btnDevolverContainer.classList.remove('hidden');
                } else {
                    btnDevolverContainer.classList.add('hidden');
                }
            }

            // Estado visual
            const estadoPill = document.getElementById('estadoPill');
            const estadoIcon = document.getElementById('estadoIcon');
            const planificacionContainer = document.getElementById('planificacionContainer');
            // Determinar color y texto
            let pillColor = '', iconColor = '', texto = '', mostrarPlanificacion = true;
            switch (estadoTexto) {
                case 'Ocupado':
                    pillColor = 'border-red-500 bg-red-50 text-red-700';
                    iconColor = 'bg-red-500';
                    texto = 'Ocupado';
                    break;
                case 'Disponible':
                    pillColor = 'border-green-500 bg-green-50 text-green-700';
                    iconColor = 'bg-green-500';
                    texto = 'Disponible';
                    mostrarPlanificacion = false;
                    break;
                case 'Próximo a ocuparse':
                    pillColor = 'border-blue-500 bg-blue-50 text-blue-700';
                    iconColor = 'bg-blue-500';
                    texto = 'Previsto';
                    mostrarPlanificacion = false;
                    break;
                case 'Reservado':
                    pillColor = 'border-yellow-400 bg-yellow-50 text-yellow-700';
                    iconColor = 'bg-yellow-400';
                    texto = 'Reservado';
                    break;
                default:
                    pillColor = 'border-gray-400 bg-gray-50 text-gray-700';
                    iconColor = 'bg-gray-400';
                    texto = estadoTexto;
            }
            estadoPill.className = `inline-flex items-center px-4 py-2 text-base font-bold border rounded-full ${pillColor}`;
            estadoIcon.className = `w-3 h-3 mr-3 rounded-full ${iconColor}`;
            document.getElementById('modalEstado').textContent = texto;
            // Mostrar/ocultar planificación
            if (planificacionContainer) {
                planificacionContainer.style.display = mostrarPlanificacion ? '' : 'none';
            }
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

        function obtenerDiaActual() {
            const dias = ['domingo', 'lunes', 'martes', 'miercoles', 'jueves', 'viernes', 'sabado'];
            return dias[new Date().getDay()];
        }

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
            inputEscanner.addEventListener('keydown', handleScan);
            document.addEventListener('click', function () {
                inputEscanner.focus();
            });
            inputEscanner.focus();
            document.getElementById('qr-status').innerHTML = 'Por favor, escanee el código QR del usuario';

            // Inicializar elementos
            initElements();

            // Agregar event listeners para los eventos de mouse
            elements.indicatorsCanvas.addEventListener('mousemove', handleMouseMove);
            elements.indicatorsCanvas.addEventListener('click', handleMouseClick);
            elements.indicatorsCanvas.addEventListener('mouseleave', handleMouseLeave);

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

                // Sincronizar colores después de cargar la imagen
                sincronizarColoresDespuesCarga();

                // Actualizar cada minuto usando la nueva función de colores
                state.updateInterval = setInterval(actualizarColoresEspacios, 60000);
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
                            'mt-4 px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded hover:bg-blue-700 transition';
                        btnEntregarLlaves.textContent = '¿Desea entregar las llaves?';
                        btnEntregarLlaves.onclick = function () {
                            // Cerrar el modal actual

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

                    const elPlaceholder1 = document.getElementById('salida-profesor-placeholder');
                    if (elPlaceholder1) {
                        elPlaceholder1.style.display = 'none';
                    }

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
                    const elPlaceholder2 = document.getElementById('salida-profesor-placeholder');
                    if (elPlaceholder2) {
                        elPlaceholder2.style.display = 'flex';
                    }
                }
            }
        }

        function onSalidaProfesorScanSuccess(decodedText) {
            console.log('QR Salida Profesor:', decodedText);
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

        // Función para inicializar el escáner de QR para el espacio en la salida
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
            console.log('QR Salida Espacio:', decodedText);
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
                        qrStatus.innerHTML =
                            '<svg xmlns="http://www.w3.org/2000/svg" class="inline-block w-4 h-4 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg> Usuario encontrado. Ahora escanee el espacio';
                        nombreUsuario.textContent = data.user.name;
                    } else {
                        qrStatus.innerHTML =
                            '<svg xmlns="http://www.w3.org/2000/svg" class="inline-block w-4 h-4 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg> RUN no encontrado';
                        nombreUsuario.textContent = '--';
                    }
                } catch (e) {
                    qrStatus.innerHTML =
                        '<svg xmlns="http://www.w3.org/2000/svg" class="inline-block w-4 h-4 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg> Error de conexión';
                    nombreUsuario.textContent = '--';
                }
            } else {
                qrStatus.innerHTML =
                    '<svg xmlns="http://www.w3.org/2000/svg" class="inline-block w-4 h-4 text-yellow-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v2m0 5h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 20h14a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg> Esperando';
                runEscaneado.textContent = '--';
                nombreUsuario.textContent = '--';
            }
        }

        let bufferQR = '';
        let esperandoUsuario = true;
        let usuarioEscaneado = null;

        async function verificarUsuario(run) {
            try {
                const response = await fetch(`/api/verificar-usuario/${run}`);
                return await response.json();
            } catch (error) {
                console.error('Error:', error);
                return null;
            }
        }

        async function verificarEspacio(idEspacio) {
            try {
                const response = await fetch(`/api/verificar-espacio/${idEspacio}`);
                return await response.json();
            } catch (error) {
                console.error('Error:', error);
                return null;
            }
        }

        async function crearReserva(run, idEspacio) {
            try {
                const response = await fetch('/api/crear-reserva', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({ run, id_espacio: idEspacio })
                });
                return await response.json();
            } catch (error) {
                console.error('Error:', error);
                return null;
            }
        }

        async function handleScan(event) {
            if (event.key === 'Enter') {
                if (esperandoUsuario) {
                    const match = bufferQR.match(/RUN¿(\d+)/);
                    if (match) {
                        usuarioEscaneado = match[1];
                        const usuarioInfo = await verificarUsuario(usuarioEscaneado);

                        if (usuarioInfo && usuarioInfo.verificado) {
                            document.getElementById('qr-status').innerHTML = 'Usuario verificado. Escanee el espacio.';
                            document.getElementById('run-escaneado').textContent = usuarioInfo.usuario.run;
                            document.getElementById('nombre-usuario').textContent = usuarioInfo.usuario.nombre;
                            esperandoUsuario = false;
                        } else {
                            Swal.fire('Error', usuarioInfo?.mensaje || 'Error de verificación', 'error');
                            document.getElementById('qr-status').innerHTML = usuarioInfo?.mensaje || 'Error de verificación';
                        }
                    } else {
                        Swal.fire('Error', 'RUN inválido', 'error');
                        document.getElementById('qr-status').innerHTML = 'RUN inválido';
                    }
                } else {
                    const espacioProcesado = bufferQR.replace(/'/g, '-');
                    const espacioInfo = await verificarEspacio(espacioProcesado);

                    if (espacioInfo?.verificado) {
                        if (espacioInfo.disponible) {
                            Swal.fire({
                                title: `¿Desea utilizar el espacio ${espacioInfo.espacio.nombre}?`,
                                icon: 'question',
                                showCancelButton: true,
                                confirmButtonText: 'Sí, utilizar',
                                cancelButtonText: 'Cancelar'
                            }).then(async (result) => {
                                if (result.isConfirmed) {
                                    const reserva = await crearReserva(usuarioEscaneado, espacioProcesado);
                                    if (reserva?.success) {
                                        Swal.fire('¡Reserva exitosa!', '', 'success');
                                        document.getElementById('qr-status').innerHTML = 'Reserva exitosa';
                                        document.getElementById('nombre-espacio').textContent = espacioInfo.espacio.nombre;
                                        // Actualizar el color del indicador a 'Ocupado' (rojo)
                                        const block = state.indicators.find(b => b.id === espacioProcesado);
                                        if (block) {
                                            block.estado = 'red'; // o el valor que uses para 'Ocupado'
                                            state.originalCoordinates = state.indicators.map(i => ({ ...i }));
                                            drawIndicators();
                                        }
                                    } else {
                                        Swal.fire('Error', reserva?.mensaje || 'Error en reserva', 'error');
                                        document.getElementById('qr-status').innerHTML = reserva?.mensaje || 'Error en reserva';
                                    }
                                } else {
                                    Swal.fire('Reserva cancelada', '', 'info');
                                    document.getElementById('qr-status').innerHTML = 'Reserva cancelada';
                                }
                            });
                        } else {
                            Swal.fire({
                                title: `¿Desea devolver las llaves del espacio ${espacioInfo.espacio.nombre}?`,
                                icon: 'question',
                                showCancelButton: true,
                                confirmButtonText: 'Sí, devolver',
                                cancelButtonText: 'Cancelar'
                            }).then(async (result) => {
                                if (result.isConfirmed) {
                                    const response = await fetch('/api/devolver-llaves', {
                                        method: 'POST',
                                        headers: {
                                            'Content-Type': 'application/json',
                                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                                        },
                                        body: JSON.stringify({
                                            id_espacio: espacioProcesado,
                                            run: usuarioEscaneado
                                        })
                                    });
                                    const data = await response.json();
                                    if (data.success) {
                                        Swal.fire('Llaves devueltas exitosamente', '', 'success');
                                        document.getElementById('qr-status').innerHTML = 'Llaves devueltas exitosamente';
                                        document.getElementById('nombre-espacio').textContent = espacioInfo.espacio.nombre;
                                        // Actualizar el color del indicador a 'Disponible' (verde)
                                        const block = state.indicators.find(b => b.id === espacioProcesado);
                                        if (block) {
                                            block.estado = 'green'; // o el valor que uses para 'Disponible'
                                            state.originalCoordinates = state.indicators.map(i => ({ ...i }));
                                            drawIndicators();
                                        }
                                    } else {
                                        Swal.fire('Error', data.mensaje || 'Error al devolver las llaves', 'error');
                                        document.getElementById('qr-status').innerHTML = data.mensaje || 'Error al devolver las llaves';
                                    }
                                } else {
                                    Swal.fire('Operación cancelada', '', 'info');
                                    document.getElementById('qr-status').innerHTML = 'Operación cancelada';
                                }
                            });
                        }
                    } else {
                        Swal.fire('Error', espacioInfo?.mensaje || 'Error al verificar espacio', 'error');
                        document.getElementById('qr-status').innerHTML = espacioInfo?.mensaje || 'Error al verificar espacio';
                    }
                    esperandoUsuario = true;
                }
                bufferQR = '';
                event.target.value = '';
            } else if (event.key.length === 1) {
                bufferQR += event.key;
            }
        }

        document.addEventListener('DOMContentLoaded', function () {
            const inputEscanner = document.getElementById('qr-input');
            inputEscanner.addEventListener('keydown', handleScan);
            document.addEventListener('click', () => inputEscanner.focus());
            inputEscanner.focus();
            document.getElementById('qr-status').innerHTML = 'Escanee el código QR del usuario';
        });

        function mostrarErrorReconocimiento(mensaje) {
            document.getElementById('nombre-usuario').textContent = '--';
            document.getElementById('nombre-espacio').textContent = '--';
            document.getElementById('qr-status').innerHTML =
                '<svg xmlns="http://www.w3.org/2000/svg" class="inline-block w-4 h-4 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">' +
                '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg> ' + mensaje;

            document.getElementById('reconocimiento-icono').innerHTML =
                '<svg xmlns="http://www.w3.org/2000/svg" class="w-16 h-16 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">' +
                '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>';
            document.getElementById('reconocimiento-titulo').textContent = 'Error de Reconocimiento';
            document.getElementById('reconocimiento-usuario').textContent = mensaje;
            document.getElementById('reconocimiento-espacio').textContent = '';
            window.dispatchEvent(new CustomEvent('open-modal', { detail: 'reconocimiento' }));
        }

        function manejarInputEscanner(event) {
            const currentTime = new Date().getTime();
            if (currentTime - lastScanTime > 1000) { // 1 segundo
                bufferQR = '';
            }
            lastScanTime = currentTime;

            if (event.key.length === 1) {
                bufferQR += event.key;
                console.log('Buffer actual:', bufferQR);
            }

            // Verificar QR usuario (formato del Registro Civil)
            const matchUsuario = bufferQR.match(/RUN¿(\d+)'/);
            if (matchUsuario && !esperandoEspacio) {
                console.log('QR Usuario completo:', bufferQR);
                const run = matchUsuario[1];
                console.log('QR Usuario detectado:', {
                    codigoCompleto: bufferQR,
                    run: run
                });

                // Validar formato del RUN
                if (!/^\d{7,8}$/.test(run)) {
                    mostrarErrorReconocimiento('Formato de RUN inválido');
                    limpiarEstado();
                    bufferQR = '';
                    event.target.value = '';
                    return;
                }

                qrUsuario = run;
                document.getElementById('run-escaneado').textContent = run;

                fetch(`/api/user/${run}`)
                    .then(res => {
                        if (!res.ok) {
                            throw new Error('Error en la respuesta del servidor');
                        }
                        return res.json();
                    })
                    .then(data => {
                        if (data.success && data.user) {
                            document.getElementById('nombre-usuario').textContent = data.user.name;
                            document.getElementById('qr-status').innerHTML =
                                '<svg xmlns="http://www.w3.org/2000/svg" class="inline-block w-4 h-4 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">' +
                                '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg> Usuario encontrado. Ahora escanee el espacio';
                            esperandoEspacio = true;

                            // Actualizar el modal con la información del usuario
                            document.getElementById('reconocimiento-usuario').textContent = `Usuario: ${data.user.name}`;
                            document.getElementById('reconocimiento-espacio').textContent = 'Esperando escaneo del espacio...';
                            document.getElementById('reconocimiento-icono').innerHTML =
                                '<svg xmlns="http://www.w3.org/2000/svg" class="w-16 h-16 text-yellow-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">' +
                                '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>';
                            document.getElementById('reconocimiento-titulo').textContent = 'Usuario Reconocido';
                            window.dispatchEvent(new CustomEvent('open-modal', { detail: 'reconocimiento' }));
                        } else {
                            mostrarErrorReconocimiento('Usuario no encontrado');
                            limpiarEstado();
                        }
                    })
                    .catch(error => {
                        mostrarErrorReconocimiento('Error de conexión: ' + error.message);
                        limpiarEstado();
                    });

                bufferQR = '';
                event.target.value = '';
                return;
            }

            // Verificar QR espacio (formato TH'L01)
            const matchEspacio = bufferQR.match(/TH'([A-Z0-9]+)/);
            if (matchEspacio && esperandoEspacio) {
                console.log('QR Espacio completo:', bufferQR);
                // Convertir el formato TH'L01 a TH-L01
                const codigoEspacio = matchEspacio[1];
                const espacioOriginal = `TH-${codigoEspacio}`;
                console.log('Espacio procesado:', {
                    codigoCompleto: bufferQR,
                    codigoEspacio: codigoEspacio,
                    espacioOriginal: espacioOriginal
                });
                qrEspacio = espacioOriginal;

                fetch(`/api/space/${qrEspacio}`)
                    .then(res => {
                        if (!res.ok) {
                            throw new Error('Error en la respuesta del servidor');
                        }
                        return res.json();
                    })
                    .then(data => {
                        if (data.success && data.space) {
                            document.getElementById('nombre-espacio').textContent = data.space.name || qrEspacio;
                            document.getElementById('qr-status').innerHTML =
                                '<svg xmlns="http://www.w3.org/2000/svg" class="inline-block w-4 h-4 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">' +
                                '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg> Espacio encontrado. Ahora verificando programación...';

                            // Actualizar el modal con la información del espacio
                            document.getElementById('reconocimiento-espacio').textContent = `Espacio: ${data.space.name || qrEspacio}`;
                            document.getElementById('reconocimiento-icono').innerHTML =
                                '<svg xmlns="http://www.w3.org/2000/svg" class="w-16 h-16 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">' +
                                '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>';
                            document.getElementById('reconocimiento-titulo').textContent = 'Reconocimiento Exitoso';

                            return fetch(`/api/verificar-programacion/${qrEspacio}/${qrUsuario}`);
                        } else {
                            throw new Error('Espacio no encontrado');
                        }
                    })
                    .then(res => {
                        if (!res.ok) {
                            throw new Error('Error al verificar la programación');
                        }
                        return res.json();
                    })
                    .then(data => {
                        if (data.success) {
                            if (data.tieneProgramacion) {
                                window.sweetAlert({
                                    title: 'Clase programada',
                                    text: 'Ud. tiene una clase programada en este espacio, ¿desea solicitar la llave?',
                                    icon: 'info',
                                    showCancelButton: true,
                                    confirmButtonText: 'Solicitar llave',
                                    cancelButtonText: 'Cancelar',
                                    callback: function (confirmado) {
                                        if (confirmado) {
                                            guardarReserva(qrEspacio, qrUsuario, 'clase');
                                        }
                                        limpiarEstado();
                                    }
                                });
                            } else {
                                window.sweetAlert({
                                    title: 'Sin clase programada',
                                    text: 'Ud. no tiene una clase programada, ¿desea utilizar el espacio?',
                                    icon: 'question',
                                    showCancelButton: true,
                                    confirmButtonText: 'Sí, utilizar',
                                    cancelButtonText: 'Cancelar',
                                    callback: function (confirmado) {
                                        if (confirmado) {
                                            guardarReserva(qrEspacio, qrUsuario, 'espontanea');
                                        }
                                        limpiarEstado();
                                    }
                                });
                            }
                        } else {
                            throw new Error(data.message || 'Error al verificar la programación');
                        }
                    })
                    .catch(error => {
                        mostrarErrorReconocimiento(error.message || 'Error al reconocer el espacio');
                        limpiarEstado();
                    });

                bufferQR = '';
                event.target.value = '';
                return;
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
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
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
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
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

                // Preservar los colores de estado existentes
                data.forEach(nuevoIndicador => {
                    const indicadorExistente = state.indicators.find(ind => ind.id === nuevoIndicador.id);
                    if (indicadorExistente && indicadorExistente.estado) {
                        nuevoIndicador.estado = indicadorExistente.estado;
                    }
                });

                state.indicators = data;
                drawIndicators();
            } catch (error) {
                console.error('Error al actualizar el estado del mapa:', error);
            }
        }

        function guardarReserva(espacioId, usuarioRun, tipoReserva) {
            fetch('/api/registrar-ingreso-clase', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({
                    run: usuarioRun,
                    espacio_id: espacioId,
                    tipo_reserva: tipoReserva
                })
            })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        window.sweetAlert({
                            title: 'Reserva exitosa',
                            text: data.message || 'La reserva se guardó correctamente.',
                            icon: 'success'
                        });
                        // Forzar actualización de colores después de registrar reserva
                        if (typeof forzarActualizacionColores === 'function') {
                            setTimeout(() => {
                                forzarActualizacionColores();
                            }, 500);
                        }
                    } else {
                        window.sweetAlert({
                            title: 'Error',
                            text: data.message || 'No se pudo guardar la reserva.',
                            icon: 'error'
                        });
                    }
                })
                .catch(error => {
                    window.sweetAlert({
                        title: 'Error',
                        text: error.message || 'Ocurrió un error al guardar la reserva.',
                        icon: 'error'
                    });
                });
        }

        function getNextUpdateDelay() {
            const startHour = 7;
            const startMinute = 50;
            const now = new Date();
            const start = new Date(now.getFullYear(), now.getMonth(), now.getDate(), startHour, startMinute, 0, 0);

            if (now < start) {
                return start - now;
            }
            const minutesSinceStart = ((now.getHours() * 60 + now.getMinutes()) - (startHour * 60 + startMinute));
            const minutesToNext = 5 - (minutesSinceStart % 5);
            const nextUpdate = new Date(now.getTime() + minutesToNext * 60 * 1000);
            nextUpdate.setSeconds(0, 0);
            return nextUpdate - now;
        }

        // Variable para almacenar el estado anterior
        let estadoAnterior = {};

        function actualizarColoresEspacios() {
            console.log('Verificando cambios en estados de espacios...');
            fetch('/api/espacios/estados')
                .then(res => res.json())
                .then(({ espacios }) => {
                    const colores = {
                        Ocupado: "#FF0000",       // Rojo - Estado en tabla es "Ocupado"
                        Disponible: "#059669",    // Verde - Estado "Disponible" sin horario
                        Reservado: "#FFA500",     // Naranja - Clase en curso pero estado no es "Ocupado"
                        Proximo: "#3B82F6",       // Azul - Entre módulos (próxima clase en 10 min)
                        Default: "#CCCCCC"        // Gris - Estado por defecto
                    };

                    let hayCambios = false;

                    // Verificar si hay cambios en los estados
                    state.indicators.forEach(indicator => {
                        const espacioEstado = espacios.find(esp => esp.id === indicator.id);
                        if (espacioEstado) {
                            const nuevoColor = colores[espacioEstado.estado] || colores.Default;
                            const colorAnterior = estadoAnterior[indicator.id] || indicator.estado;

                            // Solo actualizar si el color ha cambiado
                            if (nuevoColor !== colorAnterior) {
                                console.log(`Cambio detectado - Espacio ${indicator.id}: ${espacioEstado.estado} (${colorAnterior} -> ${nuevoColor})`);
                                indicator.estado = nuevoColor;
                                estadoAnterior[indicator.id] = nuevoColor;
                                hayCambios = true;
                            }

                            // Actualizar información de la clase actual si existe
                            if (espacioEstado.informacion_clase_actual) {
                                indicator.informacion_clase_actual = espacioEstado.informacion_clase_actual;
                            }
                        }
                    });

                    // Solo redibujar si hubo cambios
                    if (hayCambios) {
                        console.log('Redibujando indicadores debido a cambios...');
                        drawIndicators();
                    } else {
                        console.log('No se detectaron cambios en los estados');
                    }
                })
                .catch(error => {
                    console.error('Error al verificar estados:', error);
                });
        }

        // Función para sincronizar colores después de cargar el mapa
        function sincronizarColoresDespuesCarga() {
            setTimeout(() => {
                console.log('Sincronizando colores después de cargar el mapa...');
                actualizarColoresEspacios();
            }, 2000); // Esperar 2 segundos después de cargar el mapa
        }

        // Función para forzar actualización (usar cuando se registre una reserva o cambio de estado)
        function forzarActualizacionColores() {
            console.log('Forzando actualización de colores...');
            estadoAnterior = {}; // Limpiar estado anterior para forzar actualización
            actualizarColoresEspacios();
        }

        // Llama una vez al cargar la página
        actualizarColoresEspacios();

        // Inicializar el estado anterior después de la primera carga
        setTimeout(() => {
            state.indicators.forEach(indicator => {
                estadoAnterior[indicator.id] = indicator.estado;
            });
            console.log('Estado inicial establecido');
        }, 1000);

        // Deshabilitar la función actualizarEstadoMapa automática para evitar conflictos
        // Solo se ejecutará cuando se registre una reserva manualmente

        // Agregar el manejador para el botón de información
        document.getElementById('infoButton').addEventListener('click', function () {
            window.dispatchEvent(new CustomEvent('open-modal', { detail: 'instrucciones-uso' }));
        });

        function dibujarIndicadores(bloques) {
            const ctx = indicatorsCanvas.getContext('2d');
            ctx.clearRect(0, 0, indicatorsCanvas.width, indicatorsCanvas.height);

            bloques.forEach(bloque => {
                const { x, y, width, height, estado, clase_proxima } = bloque;

                // Determinar el color basado en el estado y si hay clase próxima
                let color;
                if (estado === 'ocupado') {
                    color = 'rgba(239, 68, 68, 0.7)'; // Rojo
                } else if (estado === 'proximo') {
                    color = 'rgba(234, 179, 8, 0.7)'; // Amarillo
                } else if (clase_proxima && clase_proxima.hora_inicio) {
                    // Verificar si estamos en un espacio entre módulos
                    const horaActual = new Date();
                    const horaActualStr = horaActual.toLocaleTimeString('es-ES', { hour: '2-digit', minute: '2-digit' });
                    const [horaActualHora, horaActualMin] = horaActualStr.split(':').map(Number);

                    // Obtener la hora de inicio del módulo
                    const [horaInicioHora, horaInicioMin] = clase_proxima.hora_inicio.split(':').map(Number);

                    // Si estamos en los 10 minutos antes del inicio del módulo
                    if (horaActualHora === horaInicioHora &&
                        horaActualMin >= (horaInicioMin - 10) &&
                        horaActualMin < horaInicioMin) {
                        color = 'rgba(59, 130, 246, 0.7)'; // Azul para espacios entre módulos
                    } else {
                        color = 'rgba(34, 197, 94, 0.7)'; // Verde para otros casos
                    }
                } else {
                    color = 'rgba(34, 197, 94, 0.7)'; // Verde
                }

                // Dibujar el indicador
                ctx.fillStyle = color;
                ctx.beginPath();
                ctx.arc(x + width / 2, y + height / 2, 10, 0, Math.PI * 2);
                ctx.fill();

                // Agregar borde blanco
                ctx.strokeStyle = 'white';
                ctx.lineWidth = 2;
                ctx.stroke();
            });
        }

        // Mostrar/ocultar botón Devolver según estado y aplicar color visual
        function actualizarBotonDevolver(estado) {
            const btnContainer = document.getElementById('btnDevolverContainer');
            const estadoSpan = document.getElementById('modalEstado');
            if (!estadoSpan) return;
            // Limpiar clases previas
            estadoSpan.className = 'inline-block ml-1 text-sm';
            if (estado) {
                const estadoLower = estado.trim().toLowerCase();
                if (estadoLower === 'ocupado') {
                    btnContainer.classList.remove('hidden');
                    estadoSpan.classList.add('text-red-600', 'font-bold');
                } else if (estadoLower === 'disponible') {
                    btnContainer.classList.add('hidden');
                    estadoSpan.classList.add('text-green-600', 'font-bold');
                } else if (estadoLower === 'próximo' || estadoLower === 'proximo') {
                    btnContainer.classList.add('hidden');
                    estadoSpan.classList.add('text-yellow-500', 'font-bold');
                } else if (estadoLower === 'previsto') {
                    btnContainer.classList.add('hidden');
                    estadoSpan.classList.add('text-blue-600', 'font-bold');
                } else {
                    btnContainer.classList.add('hidden');
                }
            } else {
                btnContainer.classList.add('hidden');
            }
        }
        // Lógica para abrir el modal de devolución
        if (document.getElementById('btnDevolver')) {
            document.getElementById('btnDevolver').onclick = function () {
                window.dispatchEvent(new CustomEvent('open-modal', { detail: 'devolucion-qr' }));
                setTimeout(() => {
                    document.getElementById('qr-input-devolucion').focus();
                    document.getElementById('qr-status-devolucion').textContent = 'Escanee el código QR para devolver';
                }, 300);
            };
        }
        // Lógica de escaneo QR (puedes reutilizar la del sidebar, adaptando los IDs)
        document.getElementById('qr-input-devolucion').addEventListener('keydown', async function (event) {
            if (event.key === 'Enter') {
                const qr = event.target.value.trim();
                // Aquí va la lógica para procesar el QR y registrar la devolución
                document.getElementById('qr-status-devolucion').textContent = 'Procesando...';
                // Simulación de éxito
                setTimeout(() => {
                    document.getElementById('qr-status-devolucion').textContent = '¡Devolución registrada correctamente!';
                    event.target.value = '';
                }, 1000);
            }
        });
        // Llama a actualizarBotonDevolver cuando muestres el modal de detalles y pases el estado
        // Ejemplo: actualizarBotonDevolver(estadoActual);

        // Función para iniciar el proceso de devolución de llaves
        window.iniciarDevolucionLlaves = function () {
            // Cerrar el modal de detalles del espacio
            window.dispatchEvent(new CustomEvent('close-modal', { detail: 'data-space' }));
            // Abrir el modal de devolución de llaves
            window.dispatchEvent(new CustomEvent('open-modal', { detail: 'devolver-llaves' }));
            // Resetear el estado del proceso
            if (typeof resetearDevolucionQR === 'function') {
                resetearDevolucionQR();
            }
            // Enfocar el input del usuario
            setTimeout(() => {
                const input = document.getElementById('qr-input-devolucion');
                if (input) input.focus();
            }, 300);
        }

        // Función para resetear el proceso de devolución
        function resetearProcesoDevolucion() {
            document.getElementById('paso-espacio').classList.add('hidden');
            document.getElementById('qr-usuario-devolucion').value = '';
            document.getElementById('qr-espacio-devolucion').value = '';
            document.getElementById('status-usuario').textContent = '';
            document.getElementById('status-espacio').textContent = '';
            document.getElementById('info-devolucion').innerHTML = `
                <div class="flex items-center gap-2">
                    <span class="text-gray-600">ℹ️</span>
                    <span class="text-sm text-gray-700">Proceso de devolución de llaves</span>
                </div>
            `;
        }

        // Variables para el proceso de devolución
        let usuarioDevolucion = null;
        let espacioDevolucion = null;

        // Event listener para el QR del usuario en devolución
        document.addEventListener('DOMContentLoaded', function () {
            const qrUsuarioInput = document.getElementById('qr-usuario-devolucion');
            const qrEspacioInput = document.getElementById('qr-espacio-devolucion');

            if (qrUsuarioInput) {
                qrUsuarioInput.addEventListener('keypress', function (e) {
                    if (e.key === 'Enter') {
                        procesarQRUsuarioDevolucion(this.value);
                    }
                });
            }

            if (qrEspacioInput) {
                qrEspacioInput.addEventListener('keypress', function (e) {
                    if (e.key === 'Enter') {
                        procesarQREspacioDevolucion(this.value);
                    }
                });
            }
        });

        // Función para procesar el QR del usuario en devolución
        async function procesarQRUsuarioDevolucion(qrData) {
            const statusElement = document.getElementById('status-usuario');
            statusElement.textContent = 'Verificando usuario...';

            try {
                const response = await fetch(`/api/user/${qrData}`);
                const data = await response.json();

                if (data.success && data.user) {
                    usuarioDevolucion = data.user;
                    statusElement.innerHTML = `
                        <span class="text-green-600">✓ Usuario verificado: ${data.user.name}</span>
                    `;

                    // Mostrar paso 2
                    document.getElementById('paso-espacio').classList.remove('hidden');
                    document.getElementById('qr-espacio-devolucion').focus();

                    // Actualizar información
                    document.getElementById('info-devolucion').innerHTML = `
                        <div class="flex items-center gap-2">
                            <span class="text-blue-600">👤</span>
                            <span class="text-sm text-gray-700">Usuario: ${data.user.name}</span>
                        </div>
                    `;
                } else {
                    statusElement.innerHTML = `
                        <span class="text-red-600">✗ Usuario no encontrado</span>
                    `;
                    document.getElementById('qr-usuario-devolucion').focus();
                }
            } catch (error) {
                statusElement.innerHTML = `
                    <span class="text-red-600">✗ Error al verificar usuario</span>
                `;
                document.getElementById('qr-usuario-devolucion').focus();
            }
        }

        // Función para procesar el QR del espacio en devolución
        async function procesarQREspacioDevolucion(qrData) {
            const statusElement = document.getElementById('status-espacio');
            statusElement.textContent = 'Verificando espacio...';

            try {
                const espacioProcesado = qrData.replace(/'/g, '-');
                const espacioInfo = await verificarEspacio(espacioProcesado);

                if (espacioInfo?.verificado) {
                    espacioDevolucion = espacioInfo.espacio;
                    statusElement.innerHTML = `
                        <span class="text-green-600">✓ Espacio verificado: ${espacioInfo.espacio.nombre}</span>
                    `;

                    // Procesar la devolución
                    await procesarDevolucionCompleta();
                } else {
                    statusElement.innerHTML = `
                        <span class="text-red-600">✗ Espacio no encontrado</span>
                    `;
                    document.getElementById('qr-espacio-devolucion').focus();
                }
            } catch (error) {
                statusElement.innerHTML = `
                    <span class="text-red-600">✗ Error al verificar espacio</span>
                `;
                document.getElementById('qr-espacio-devolucion').focus();
            }
        }

        // Función para procesar la devolución completa
        async function procesarDevolucionCompleta() {
            if (!usuarioDevolucion || !espacioDevolucion) {
                return;
            }

            try {
                // Llamar a la API para registrar la devolución
                const response = await fetch('/api/reserva/devolver', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({
                        run: usuarioDevolucion.run,
                        espacio_id: espacioDevolucion.id
                    })
                });

                const data = await response.json();

                if (data.success) {
                    // Mostrar éxito
                    document.getElementById('info-devolucion').innerHTML = `
                        <div class="flex items-center gap-2">
                            <span class="text-green-600">✅</span>
                            <span class="text-sm text-green-700">Devolución exitosa</span>
                        </div>
                    `;

                    // Cerrar modal después de 2 segundos
                    setTimeout(() => {
                        window.dispatchEvent(new CustomEvent('close-modal', { detail: 'devolver-llaves' }));
                        // Actualizar el estado del espacio en el plano
                        actualizarEstadoEspacio(espacioDevolucion.id, 'Disponible');
                    }, 2000);
                } else {
                    document.getElementById('info-devolucion').innerHTML = `
                        <div class="flex items-center gap-2">
                            <span class="text-red-600">❌</span>
                            <span class="text-sm text-red-700">Error: ${data.message || 'Error en la devolución'}</span>
                        </div>
                    `;
                }
            } catch (error) {
                document.getElementById('info-devolucion').innerHTML = `
                    <div class="flex items-center gap-2">
                        <span class="text-red-600">❌</span>
                        <span class="text-sm text-red-700">Error en la comunicación con el servidor</span>
                    </div>
                `;
            }
        }

        // Función para actualizar el estado del espacio en el plano
        function actualizarEstadoEspacio(espacioId, nuevoEstado) {
            const indicador = state.indicators.find(i => i.id === espacioId);
            if (indicador) {
                // Actualizar el color según el nuevo estado
                switch (nuevoEstado) {
                    case 'Disponible':
                        indicador.estado = '#059669'; // Verde
                        break;
                    case 'Ocupado':
                        indicador.estado = '#FF0000'; // Rojo
                        break;
                    case 'Reservado':
                        indicador.estado = '#FFA500'; // Naranja
                        break;
                    default:
                        indicador.estado = '#059669'; // Verde por defecto
                }

                // Redibujar los indicadores
                drawIndicators();
            }
        }

        // Función para manejar cuando el mouse sale del canvas

        // ... existente ...
        // Lógica de escaneo QR para devolución de llaves (modal)
        let bufferQRDevolucion = '';
        let esperandoUsuarioDevolucion = true;
        let usuarioDevolucionQR = null;
        let espacioDevolucionQR = null;

        function resetearDevolucionQR() {
            bufferQRDevolucion = '';
            esperandoUsuarioDevolucion = true;
            usuarioDevolucionQR = null;
            espacioDevolucionQR = null;
            document.getElementById('qr-status-devolucion').textContent = 'Escanee el código QR del usuario';
            document.getElementById('qr-input-devolucion').value = '';
        }

        function handleScanDevolucion(event) {
            if (event.key === 'Enter') {
                if (esperandoUsuarioDevolucion) {
                    // Buscar RUN en el QR
                    const match = bufferQRDevolucion.match(/RUN¿(\d+)/);
                    if (match) {
                        usuarioDevolucionQR = match[1];
                        document.getElementById('qr-status-devolucion').textContent = 'Verificando usuario...';
                        fetch(`/api/user/${usuarioDevolucionQR}`)
                            .then(res => res.json())
                            .then(data => {
                                if (data.success && data.user) {
                                    document.getElementById('qr-status-devolucion').textContent = `Usuario verificado: ${data.user.name}. Ahora escanee el QR del espacio.`;
                                    esperandoUsuarioDevolucion = false;
                                } else {
                                    document.getElementById('qr-status-devolucion').textContent = 'Usuario no encontrado. Intente nuevamente.';
                                    resetearDevolucionQR();
                                }
                            })
                            .catch(() => {
                                document.getElementById('qr-status-devolucion').textContent = 'Error de conexión al verificar usuario.';
                                resetearDevolucionQR();
                            });
                    } else {
                        document.getElementById('qr-status-devolucion').textContent = 'QR de usuario inválido.';
                        resetearDevolucionQR();
                    }
                } else {
                    // Buscar código de espacio en el QR (ejemplo: TH'60)
                    const matchEspacio = bufferQRDevolucion.match(/TH'([A-Z0-9]+)/);
                    if (matchEspacio) {
                        espacioDevolucionQR = `TH-${matchEspacio[1]}`;
                        document.getElementById('qr-status-devolucion').textContent = 'Verificando espacio...';
                        fetch(`/api/verificar-espacio/${espacioDevolucionQR}`)
                            .then(res => res.json())
                            .then(data => {
                                if (data.verificado) {
                                    document.getElementById('qr-status-devolucion').textContent = 'Procesando devolución...';
                                    // Llamar a la API de devolución
                                    fetch('/api/reserva/devolver', {
                                        method: 'POST',
                                        headers: {
                                            'Content-Type': 'application/json',
                                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                                        },
                                        body: JSON.stringify({
                                            run: usuarioDevolucionQR,
                                            espacio_id: espacioDevolucionQR
                                        })
                                    })
                                        .then(res => res.json())
                                        .then(resp => {
                                            if (resp.success) {
                                                document.getElementById('qr-status-devolucion').textContent = '¡Devolución registrada correctamente!';
                                                setTimeout(() => {
                                                    window.dispatchEvent(new CustomEvent('close-modal', { detail: 'devolver-llaves' }));
                                                }, 1500);
                                            } else {
                                                document.getElementById('qr-status-devolucion').textContent = resp.message || 'Error al registrar devolución.';
                                                resetearDevolucionQR();
                                            }
                                        })
                                        .catch(() => {
                                            document.getElementById('qr-status-devolucion').textContent = 'Error al registrar devolución.';
                                            resetearDevolucionQR();
                                        });
                                } else {
                                    document.getElementById('qr-status-devolucion').textContent = 'Espacio no encontrado. Intente nuevamente.';
                                    resetearDevolucionQR();
                                }
                            })
                            .catch(() => {
                                document.getElementById('qr-status-devolucion').textContent = 'Error de conexión al verificar espacio.';
                                resetearDevolucionQR();
                            });
                    } else {
                        document.getElementById('qr-status-devolucion').textContent = 'QR de espacio inválido.';
                        resetearDevolucionQR();
                    }
                }
                bufferQRDevolucion = '';
                event.target.value = '';
            } else if (event.key.length === 1) {
                bufferQRDevolucion += event.key;
            }
        }

        document.addEventListener('DOMContentLoaded', function () {
            const inputDevolucion = document.getElementById('qr-input-devolucion');
            if (inputDevolucion) {
                inputDevolucion.addEventListener('keydown', handleScanDevolucion);
                document.addEventListener('click', () => inputDevolucion.focus());
                inputDevolucion.focus();
            }
            // Resetear estado cada vez que se abre el modal
            window.addEventListener('open-modal', function (e) {
                if (e.detail === 'devolver-llaves') {
                    resetearDevolucionQR();
                    setTimeout(() => {
                        inputDevolucion.focus();
                    }, 300);
                }
            });
        });
        // ... existente ...

        document.addEventListener('DOMContentLoaded', function () {
            const btnDevolver = document.getElementById('btnDevolver');
            const areaQR = document.getElementById('area-qr-devolucion');
            const inputQR = document.getElementById('qr-input-devolucion');
            const lineaDiv = document.getElementById('linea-divisoria-qr');
            if (btnDevolver && areaQR && inputQR && lineaDiv) {
                btnDevolver.addEventListener('click', function () {
                    areaQR.classList.remove('hidden');
                    lineaDiv.classList.remove('hidden');
                    setTimeout(() => { inputQR.focus(); }, 200);
                });
            }
        });
    </script>
</x-show-layout>