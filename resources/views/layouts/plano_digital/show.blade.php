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
            <div class="bg-white shadow-md dark:bg-dark-eval-0 rounded-t-xl">
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
                <div class="p-6 bg-white shadow-md rounded-b-xl dark:bg-gray-800">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Plano del Piso
                            {{ $mapa->piso->numero_piso }}</h3>
                        <div class="flex gap-2">
                            <button onclick="actualizarEstados(true)"
                                class="px-4 py-2 text-sm font-medium text-white transition-all duration-300 rounded-md bg-light-cloud-blue hover:bg-opacity-90 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-light-cloud-blue">
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
                            <button
                                onclick="window.dispatchEvent(new CustomEvent('open-modal', { detail: 'solicitar-espacio' }))"
                                class="px-4 py-2 text-sm font-medium text-white transition-all duration-300 rounded-md bg-light-cloud-blue hover:bg-opacity-90 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-light-cloud-blue">
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
            <!-- Leyenda abajo como pequeño card -->
            <div class="w-full max-w-md p-4 mx-auto mt-6 bg-white rounded-lg shadow-md dark:bg-gray-800">
                <h3 class="mb-2 text-base font-semibold text-center">Leyenda</h3>
                <div class="flex flex-col items-start gap-2 text-sm">
                    <div class="flex items-center gap-2">
                        <div class="w-4 h-4 bg-red-500 rounded-sm"></div>
                        <span>Espacio ocupado</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <div class="w-4 h-4 bg-blue-500 rounded-sm"></div>
                        <span>Próximo a utilizar</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <div class="w-4 h-4 bg-green-500 rounded-sm"></div>
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

    <!-- Modal fijo de hora y módulo actual -->
    <div id="modal-hora-actual"
        class="fixed z-50 w-64 p-4 border border-blue-600 rounded-lg shadow-lg bottom-4 right-4 bg-light-cloud-blue">
        <div class="flex flex-col space-y-3">
            <div class="flex items-center justify-between pb-2 border-b border-blue-400">
                <div class="flex items-center space-x-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-white" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <h3 class="text-sm font-semibold text-white">Hora Actual</h3>
                </div>
                <span id="hora-actual" class="text-lg font-bold text-white"></span>
            </div>
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-white" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                    </svg>
                    <h3 class="text-sm font-semibold text-white">
                        Módulo: <span id="modulo-actual" class="text-sm font-medium text-white">-</span>
                    </h3>
                </div>
                <span id="modulo-horario" class="text-sm font-semibold text-white">-</span>
            </div>
        </div>
    </div>

    <!-- Modal de detalles del bloque -->
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

    <!-- Modal de solicitud de espacio (REHECHO) -->
    <x-modal name="solicitar-espacio" :show="false" maxWidth="2xl">
        <x-slot name="header">
            <h1 class="font-sans text-lg font-semibold text-white dark:text-white">Solicitar Espacio</h1>
        </x-slot>
        <div class="p-6 space-y-6">
            <!-- Paso 1: Escaneo de profesor -->
            <div id="profesor-scan-section" class="flex flex-col items-center justify-center">
                <div id="qr-reader" class="w-full max-w-xs mb-4"></div>
                <div id="qr-placeholder" class="flex flex-col items-center justify-center w-full">
                    <h3 class="mb-2 text-lg font-semibold text-gray-900 dark:text-white">Escanear QR del Profesor</h3>
                    <p id="qr-cargando-msg" class="text-sm text-gray-600 dark:text-gray-400">Cargando escáner...</p>
                    <p id="qr-error-msg" class="text-sm text-red-600 dark:text-red-400 mt-2 hidden"></p>
                    <button id="btn-reintentar" onclick="reiniciarEscaneo()" class="hidden px-4 py-2 mt-4 text-sm font-medium text-white bg-blue-500 rounded hover:bg-blue-600">Volver a Escanear</button>
                </div>
            </div>
            <!-- Paso 2: Información del profesor -->
            <div id="profesor-info" class="hidden p-4 bg-blue-50 rounded shadow">
                <h3 class="mb-2 text-lg font-semibold text-blue-900">Información del Profesor</h3>
                <div class="space-y-2">
                    <p class="text-sm text-blue-800">Nombre: <span id="profesor-nombre" class="font-medium"></span></p>
                    <p class="text-sm text-blue-800">Correo: <span id="profesor-correo" class="font-medium"></span></p>
                </div>
            </div>
            <!-- Paso 3: Escaneo de espacio -->
            <div id="espacio-scan-section" class="hidden flex flex-col items-center justify-center">
                <div id="qr-reader-espacio" class="w-full max-w-xs mb-4"></div>
                <div id="espacio-placeholder" class="flex flex-col items-center justify-center w-full">
                    <h3 class="mb-2 text-lg font-semibold text-gray-900 dark:text-white">Escanear QR del Espacio</h3>
                    <button id="btn-iniciar-espacio" onclick="initEspacioScanner()" class="px-4 py-2 mt-4 text-sm font-medium text-white bg-blue-500 rounded hover:bg-blue-600">Iniciar Escaneo de Espacio</button>
                </div>
            </div>
            <!-- Paso 4: Información del espacio -->
            <div id="espacio-info" class="hidden p-4 bg-green-50 rounded shadow">
                <h3 class="mb-2 text-lg font-semibold text-green-900">Información del Espacio</h3>
                <div class="space-y-2">
                    <p class="text-sm text-green-800">Nombre: <span id="espacio-nombre" class="font-medium"></span></p>
                    <p class="text-sm text-green-800">Tipo: <span id="espacio-tipo" class="font-medium"></span></p>
                </div>
                <div id="verificacion-espacio" class="p-4 mt-4 rounded-lg bg-white flex items-center justify-center">
                    <svg class="w-6 h-6 text-gray-400 animate-spin mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                    <span class="text-sm text-gray-600">Verificando disponibilidad...</span>
                </div>
            </div>
            <!-- Paso 5: Selección de módulo -->
            <div id="duracion-section" class="hidden p-4 bg-yellow-50 rounded shadow">
                <h3 class="mb-4 text-lg font-semibold text-yellow-900">Seleccione el módulo a reservar</h3>
                <select id="select-modulo" class="w-full p-3 border border-yellow-300 rounded text-yellow-800 bg-white">
                    <option value="">Cargando módulos...</option>
                </select>
                <div id="modulos-extra-section" class="hidden mt-4">
                    <label class="block mb-2 text-yellow-900 font-semibold">¿Cuántos módulos consecutivos desea reservar?</label>
                    <select id="select-cantidad-modulos" class="w-full p-3 border border-yellow-300 rounded text-yellow-800 bg-white">
                        <option value="">Seleccione cantidad...</option>
                    </select>
                </div>
                <button id="btn-confirmar-modulo" class="mt-4 px-4 py-2 bg-yellow-400 text-white rounded font-semibold hover:bg-yellow-500 w-full" disabled>
                    Confirmar módulo(s)
                </button>
            </div>
            <!-- Paso 6: Confirmación -->
            <div id="confirmacion-section" class="hidden p-4 bg-white rounded shadow text-center">
                <div id="confirmacion-icono" class="mx-auto mb-4"></div>
                <h3 id="confirmacion-titulo" class="mb-2 text-lg font-semibold"></h3>
                <p id="confirmacion-mensaje" class="text-sm mb-2"></p>
                <div id="confirmacion-detalles" class="mt-2 space-y-1 text-sm"></div>
            </div>
        </div>
    </x-modal>

    <!-- Modal de Registro de Salida -->
    <div id="modal-salida" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75" aria-hidden="true"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div class="inline-block overflow-hidden text-left align-bottom transition-all transform bg-white rounded-lg shadow-xl dark:bg-gray-800 sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <div class="px-4 pt-5 pb-4 bg-white dark:bg-gray-800 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="w-full mt-3 text-center sm:mt-0 sm:text-left">
                            <h3 class="text-lg font-medium leading-6 text-gray-900 dark:text-gray-100" id="modal-title">
                                Registrar Salida
                            </h3>
                            <div class="mt-4">
                                <div id="qr-reader-salida" class="w-full max-w-sm mx-auto"></div>
                                <div id="salida-placeholder" class="flex items-center justify-center w-full h-64 bg-gray-100 dark:bg-gray-700">
                                    <p class="text-gray-500 dark:text-gray-400">Cámara no disponible</p>
                                </div>
                                <p id="salida-cargando-msg" class="mt-2 text-sm text-gray-500 dark:text-gray-400"></p>
                                <p id="salida-error-msg" class="mt-2 text-sm text-red-600 dark:text-red-400 hidden"></p>
                                <button id="btn-reintentar-salida" onclick="reiniciarEscaneoSalida()" class="hidden px-4 py-2 mt-4 text-sm font-medium text-white bg-blue-600 rounded hover:bg-blue-700">
                                    Reintentar
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="px-4 py-3 bg-gray-50 dark:bg-gray-700 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button type="button" onclick="closeModal()" class="inline-flex justify-center w-full px-4 py-2 text-base font-medium text-white bg-gray-600 border border-transparent rounded-md shadow-sm hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 sm:ml-3 sm:w-auto sm:text-sm">
                        Cerrar
                    </button>
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
        let duracionSeleccionada = null;

        // Variable global para el estado del mapa
        const state = {
            mapImage: null,
            originalImageSize: null,
            indicators: @json($bloques),
            originalCoordinates: @json($bloques),
            isImageLoaded: false,
            mouseX: 0,
            mouseY: 0
        };

        // Función global para dibujar indicadores
        function drawIndicators() {
            if (!state.isImageLoaded) return;
            const elements = {
                indicatorsCanvas: document.getElementById('indicatorsCanvas'),
                indicatorsCtx: document.getElementById('indicatorsCanvas').getContext('2d')
            };
            elements.indicatorsCtx.clearRect(0, 0, elements.indicatorsCanvas.width, elements.indicatorsCanvas.height);
            state.indicators.forEach(indicator => drawIndicator(indicator));
        }

        // Función global para dibujar un indicador individual
        function drawIndicator(indicator) {
            if (!state.isImageLoaded) return;
            const elements = {
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

            const { id, estado, detalles } = indicator;
            const position = calculatePosition(indicator);
            const width = config.indicatorWidth;
            const height = config.indicatorHeight;

            const mouseX = state.mouseX;
            const mouseY = state.mouseY;
            const isHovered = mouseX >= position.x - width / 2 &&
                mouseX <= position.x + width / 2 &&
                mouseY >= position.y - height / 2 &&
                mouseY <= position.y + height / 2;

            const hoverScale = 1.2;
            const finalWidth = isHovered ? width * hoverScale : width;
            const finalHeight = isHovered ? height * hoverScale : height;

            let color;
            // Determinar el color basado en el estado y detalles
            if (estado === 'red' || (detalles && detalles.estado === 'Ocupado')) {
                color = '#EF4444'; // Rojo para ocupado
            } else if (estado === 'blue' || (detalles && detalles.es_proximo)) {
                color = '#3B82F6'; // Azul para próximo
            } else if (detalles && detalles.hay_clase_actual) {
                color = '#EF4444'; // Rojo si hay clase actual
            } else {
                color = '#10B981'; // Verde para disponible
            }

            if (isHovered) {
                elements.indicatorsCtx.shadowColor = 'rgba(0, 0, 0, 0.3)';
                elements.indicatorsCtx.shadowBlur = 10;
                elements.indicatorsCtx.shadowOffsetX = 0;
                elements.indicatorsCtx.shadowOffsetY = 0;
            } else {
                elements.indicatorsCtx.shadowColor = 'transparent';
                elements.indicatorsCtx.shadowBlur = 0;
            }

            elements.indicatorsCtx.fillStyle = color;
            elements.indicatorsCtx.fillRect(position.x - finalWidth / 2, position.y - finalHeight / 2,
                finalWidth, finalHeight);
            elements.indicatorsCtx.lineWidth = 2;
            elements.indicatorsCtx.strokeStyle = config.indicatorBorder;
            elements.indicatorsCtx.strokeRect(position.x - finalWidth / 2, position.y - finalHeight / 2,
                finalWidth, finalHeight);

            elements.indicatorsCtx.font = `bold ${config.fontSize}px Arial`;
            elements.indicatorsCtx.fillStyle = config.indicatorTextColor;
            elements.indicatorsCtx.textAlign = 'center';
            elements.indicatorsCtx.textBaseline = 'middle';
            elements.indicatorsCtx.fillText(id, position.x, position.y);

            elements.indicatorsCtx.shadowColor = 'transparent';
            elements.indicatorsCtx.shadowBlur = 0;
        }

        // Función global para calcular la posición
        function calculatePosition(indicator) {
            if (!state.isImageLoaded || !state.mapImage) return { x: 0, y: 0 };
            const elements = {
                mapCanvas: document.getElementById('mapCanvas'),
                indicatorsCanvas: document.getElementById('indicatorsCanvas')
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
            if (!originalIndicator) return { x: 0, y: 0 };

            const x = offsetX + (originalIndicator.x / state.originalImageSize.width) * drawWidth;
            const y = offsetY + (originalIndicator.y / state.originalImageSize.height) * drawHeight;

            return { x, y };
        }

        // Funciones del QR scanner en el ámbito global
        async function requestCameraPermission() {
            try {
                const stream = await navigator.mediaDevices.getUserMedia({
                    video: true
                });
                stream.getTracks().forEach(track => track.stop());
                return true;
            } catch (err) {
                console.error('Error al solicitar permisos de cámara:', err);
                return false;
            }
        }

        async function getFirstCamera() {
            try {
                const devices = await Html5Qrcode.getCameras();
                if (devices && devices.length > 0) {
                    return devices[0].id;
                }
                return null;
            } catch (err) {
                console.error('Error al obtener cámaras:', err);
                return null;
            }
        }

        // Función para inicializar el escáner de profesor
        async function initQRScanner() {
            if (html5QrcodeScanner === null) {
                try {
                    document.getElementById('qr-cargando-msg').textContent = 'Cargando escáner, por favor espere...';
                    document.getElementById('qr-cargando-msg').classList.remove('hidden');
                    document.getElementById('qr-error-msg').classList.add('hidden');
                    const hasPermission = await requestCameraPermission();
                    if (!hasPermission) {
                        document.getElementById('qr-cargando-msg').textContent = '';
                        document.getElementById('qr-error-msg').textContent = 'Se requieren permisos de cámara para escanear códigos QR';
                        document.getElementById('qr-error-msg').classList.remove('hidden');
                        return;
                    }
                    currentCameraId = await getFirstCamera();
                    if (!currentCameraId) {
                        document.getElementById('qr-cargando-msg').textContent = '';
                        document.getElementById('qr-error-msg').textContent = 'No se encontró ninguna cámara disponible';
                        document.getElementById('qr-error-msg').classList.remove('hidden');
                        return;
                    }
                    const config = {
                        fps: 10,
                        qrbox: { width: 250, height: 250 },
                        aspectRatio: 1.0,
                        formatsToSupport: [Html5QrcodeSupportedFormats.QR_CODE],
                        rememberLastUsedCamera: true,
                        showTorchButtonIfSupported: true
                    };
                    html5QrcodeScanner = new Html5Qrcode("qr-reader");
                    document.getElementById('qr-placeholder').style.display = 'none';
                    await html5QrcodeScanner.start(
                        currentCameraId,
                        config,
                        onScanSuccess,
                        (error) => {
                            if (error.includes("QR code parse error")) return;
                            console.warn(`Error en el escaneo: ${error}`);
                        }
                    );
                } catch (err) {
                    console.error('Error al iniciar el escáner:', err);
                    document.getElementById('qr-cargando-msg').textContent = '';
                    document.getElementById('qr-error-msg').textContent = 'Error al iniciar la cámara. Por favor, verifica los permisos y que la cámara no esté siendo usada por otra aplicación.';
                    document.getElementById('qr-error-msg').classList.remove('hidden');
                    document.getElementById('qr-placeholder').style.display = 'flex';
                }
            }
        }

        function reiniciarEscaneo() {
            document.getElementById('qr-error-msg').classList.add('hidden');
            document.getElementById('btn-reintentar').classList.add('hidden');
            document.getElementById('qr-cargando-msg').textContent = 'Cargando escáner, por favor espere...';
            document.getElementById('qr-cargando-msg').classList.remove('hidden');
            initQRScanner();
        }

        function mostrarErrorEscaneo(mensaje) {
            const errorMsg = document.getElementById('qr-error-msg');
            const cargandoMsg = document.getElementById('qr-cargando-msg');
            const btnReintentar = document.getElementById('btn-reintentar');
            const qrPlaceholder = document.getElementById('qr-placeholder');
            if (errorMsg) {
                errorMsg.textContent = mensaje;
                errorMsg.classList.remove('hidden');
            }
            if (cargandoMsg) cargandoMsg.textContent = '';
            if (btnReintentar) btnReintentar.classList.remove('hidden');
            if (qrPlaceholder) qrPlaceholder.style.display = 'flex';
        }

        function onScanSuccess(decodedText, decodedResult) {
            if (html5QrcodeScanner) {
                html5QrcodeScanner.stop();
                html5QrcodeScanner = null;
            }
            fetch(`/api/user/${decodedText}`)
                .then(response => {
                    if (!response.ok) {
                        return response.json().then(data => {
                            throw new Error(data.message || 'Error al buscar el profesor');
                        });
                    }
                    return response.json();
                })
                .then(data => {
                    console.log('Respuesta del backend:', data);
                    if (data.success && data.user) {
                        userId = data.user.id;
                        const nombre = document.getElementById('profesor-nombre');
                        const correo = document.getElementById('profesor-correo');
                        const info = document.getElementById('profesor-info');
                        const scanSection = document.getElementById('profesor-scan-section');
                        const espacioScan = document.getElementById('espacio-scan-section');
                        const errorMsg = document.getElementById('qr-error-msg');
                        const btnReintentar = document.getElementById('btn-reintentar');
                        if (nombre) nombre.textContent = data.user.name || '';
                        if (correo) correo.textContent = data.user.email || '';
                        if (info) info.classList.remove('hidden');
                        if (scanSection) scanSection.classList.add('hidden');
                        if (espacioScan) espacioScan.classList.remove('hidden');
                        if (errorMsg) errorMsg.classList.add('hidden');
                        if (btnReintentar) btnReintentar.classList.add('hidden');
                    } else {
                        mostrarErrorEscaneo('La persona no se encuentra registrada, contáctese con soporte.');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    mostrarErrorEscaneo(error.message || 'Error al obtener información del profesor');
                });
        }

        // Función para inicializar el escáner de espacio
        async function initEspacioScanner() {
            try {
                const btnIniciar = document.getElementById('btn-iniciar-espacio');
                btnIniciar.disabled = true;
                btnIniciar.innerHTML = `
                    <svg class="inline w-4 h-4 mr-2 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    Iniciando cámara...
                `;

                const hasPermission = await requestCameraPermission();
                if (!hasPermission) {
                    alert('Se requieren permisos de cámara para escanear códigos QR');
                    return;
                }

                currentCameraId = await getFirstCamera();
                if (!currentCameraId) {
                    alert('No se encontró ninguna cámara disponible');
                    return;
                }

                const config = {
                    fps: 10,
                    qrbox: {
                        width: 250,
                        height: 250
                    },
                    aspectRatio: 1.0,
                    formatsToSupport: [Html5QrcodeSupportedFormats.QR_CODE],
                    rememberLastUsedCamera: true,
                    showTorchButtonIfSupported: true
                };

                document.getElementById('espacio-placeholder').style.display = 'none';
                html5QrcodeScanner = new Html5Qrcode("qr-reader-espacio");
                await html5QrcodeScanner.start(
                    currentCameraId,
                    config,
                    onEspacioScanSuccess,
                    (error) => {
                        // Solo mostrar errores críticos, ignorar errores de detección
                        if (error.includes("QR code parse error")) {
                            return;
                        }
                        console.warn(`Error en el escaneo de espacio: ${error}`);
                    }
                );
            } catch (err) {
                console.error('Error al iniciar el escáner de espacio:', err);
                alert(
                    'Error al iniciar la cámara. Por favor, verifica los permisos y que la cámara no esté siendo usada por otra aplicación.'
                    );
                document.getElementById('espacio-placeholder').style.display = 'flex';
                const btnIniciar = document.getElementById('btn-iniciar-espacio');
                btnIniciar.disabled = false;
                btnIniciar.textContent = 'Iniciar Escaneo de Espacio';
            }
        }

        // Función para manejar el escaneo exitoso del espacio
        function onEspacioScanSuccess(decodedText, decodedResult) {
            if (html5QrcodeScanner) {
                html5QrcodeScanner.stop();
                html5QrcodeScanner = null;
            }

            espacioId = decodedText;
            console.log('ID del espacio enviado a la API:', espacioId);

            // Obtener información del espacio y verificar disponibilidad
            fetch(`/api/espacio/${espacioId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        document.getElementById('espacio-nombre').textContent = data.espacio.nombre;
                        document.getElementById('espacio-tipo').textContent = data.espacio.tipo;
                        document.getElementById('espacio-info').classList.remove('hidden');
                        document.getElementById('espacio-scan-section').classList.add('hidden');

                        // Verificar si el usuario tiene clase programada en este espacio y horario
                        const ahora = new Date();
                        const dias = ['domingo', 'lunes', 'martes', 'miércoles', 'jueves', 'viernes', 'sábado'];
                        const diaActual = dias[ahora.getDay()];
                        const horaActual = ahora.toTimeString().substring(0,5); // formato HH:MM
                        fetch(`/api/verificar-clase-usuario?run=${userId}&espacio=${espacioId}&dia=${diaActual}&hora=${horaActual}`)
                            .then(resp => resp.json())
                            .then(res => {
                                const verificacionDiv = document.getElementById('verificacion-espacio');
                                if (verificacionDiv) {
                                    if (res.tiene_clase) {
                                        verificacionDiv.innerHTML = `
                                            <div class="flex flex-col items-center space-y-4">
                                                <span class="text-base font-semibold text-green-600">${res.mensaje}</span>
                                                <button onclick="registrarIngresoClase('${userId}', '${espacioId}')" class="px-4 py-2 text-sm font-medium text-white bg-green-600 rounded hover:bg-green-700">
                                                    Registrar Ingreso
                                                </button>
                                            </div>`;
                                    } else {
                                        verificacionDiv.innerHTML = `
                                            <div class="flex flex-col items-center space-y-4">
                                                <span class="text-base font-semibold text-red-600">${res.mensaje}</span>
                                                <button onclick="mostrarOpcionesModulo()" class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded hover:bg-blue-700">
                                                    Reservar Espacio
                                                </button>
                                            </div>`;
                                    }
                                }
                            });
                    } else {
                        alert('No se encontró información del espacio');
                        setTimeout(initEspacioScanner, 2000);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error al obtener información del espacio');
                    setTimeout(initEspacioScanner, 2000);
                });
        }

        // Función para registrar ingreso a clase programada
        function registrarIngresoClase(run, espacioId) {
            fetch('/api/registrar-ingreso-clase', {
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
                    // Actualizar el estado visual del espacio
                    const block = state.indicators.find(b => b.id === espacioId);
                    if (block) {
                        block.estado = 'red';
                        state.originalCoordinates = state.indicators.map(i => ({...i}));
                        drawIndicators();
                        
                        // Esperar a que se complete la actualización visual
                        setTimeout(() => {
                            Swal.fire({
                                icon: 'success',
                                title: '¡Éxito!',
                                text: data.message,
                                showConfirmButton: false,
                                timer: 2000
                            }).then(() => {
                                // Actualizar los estados después de cerrar el modal
                                actualizarEstados(true);
                                // Cerrar el modal
                                const modal = document.querySelector('[x-data="modal"]');
                                if (modal) {
                                    modal.dispatchEvent(new CustomEvent('close'));
                                }
                            });
                        }, 500);
                    }
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: data.message
                    });
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Ocurrió un error al registrar el ingreso'
                });
            });
        }

        // Función para mostrar opciones de módulo
        function mostrarOpcionesModulo() {
            document.getElementById('espacio-scan-section').classList.add('hidden');
            const duracionSection = document.getElementById('duracion-section');
            duracionSection.classList.remove('hidden');

            const select = document.getElementById('select-modulo');
            const btnConfirmar = document.getElementById('btn-confirmar-modulo');
            select.innerHTML = '<option value="">Cargando módulos...</option>';
            btnConfirmar.disabled = true;
            document.getElementById('modulos-extra-section').classList.add('hidden');

            // Obtener hora y día actual
            const ahora = new Date();
            const horaActual = ahora.toTimeString().substring(0,5); // HH:MM
            const dias = ['domingo', 'lunes', 'martes', 'miércoles', 'jueves', 'viernes', 'sábado'];
            const diaActual = dias[ahora.getDay()];

            fetch(`/api/espacio/${espacioId}/modulos-disponibles?hora_actual=${horaActual}&dia_actual=${diaActual}`)
                .then(resp => resp.json())
                .then(data => {
                    select.innerHTML = '';
                    if (data.success && data.modulos.length > 0) {
                        select.innerHTML = '<option value="">Seleccione un módulo</option>';
                        data.modulos.forEach(modulo => {
                            const texto = `Módulo ${modulo.numero} (${modulo.hora_inicio} - ${modulo.hora_termino})`;
                            const option = document.createElement('option');
                            option.value = modulo.id_modulo;
                            option.textContent = texto;
                            select.appendChild(option);
                        });
                        btnConfirmar.disabled = true;
                        select.onchange = function() {
                            const id_modulo = select.value;
                            if (!id_modulo) {
                                document.getElementById('modulos-extra-section').classList.add('hidden');
                                btnConfirmar.disabled = true;
                                return;
                            }
                            // Buscar el índice del módulo seleccionado en data.modulos
                            const index = data.modulos.findIndex(m => m.id_modulo === id_modulo);
                            if (index === -1) return;
                            // Calcular cuántos módulos consecutivos hay desde el seleccionado
                            let consecutivos = 1;
                            for (let i = index + 1; i < data.modulos.length; i++) {
                                consecutivos++;
                            }
                            // Llenar el select de cantidad de módulos
                            const selectCantidad = document.getElementById('select-cantidad-modulos');
                            selectCantidad.innerHTML = '<option value="">Seleccione cantidad...</option>';
                            for (let i = 1; i <= consecutivos; i++) {
                                let texto = i === 1 ? `Solo este módulo` : `Este y ${i-1} más (${i} módulos)`;
                                selectCantidad.innerHTML += `<option value="${i}">${texto}</option>`;
                            }
                            document.getElementById('modulos-extra-section').classList.remove('hidden');
                            btnConfirmar.disabled = false;
                        };
                    } else {
                        // Si no hay módulos disponibles, verificar si es próximo
                        if (data.es_proximo && data.siguiente_modulo) {
                            const block = state.indicators.find(b => b.id === espacioId);
                            if (block) {
                                block.estado = 'blue';
                                block.detalles = {
                                    ...block.detalles,
                                    es_proximo: true,
                                    siguiente_modulo: data.siguiente_modulo
                                };
                                state.originalCoordinates = state.indicators.map(i => ({...i}));
                                drawIndicators();
                            }
                        }
                        select.innerHTML = `<option value="">${data.mensaje || 'No hay módulos disponibles'}</option>`;
                        btnConfirmar.disabled = true;
                    }
                })
                .catch(() => {
                    select.innerHTML = '<option value="">Error al consultar módulos disponibles</option>';
                    btnConfirmar.disabled = true;
                });
        }

        // Función para registrar reserva espontánea
        function registrarReservaEspontanea(modulos) {
            fetch('/api/registrar-reserva-espontanea', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        user_id: userId,
                        espacio_id: espacioId,
                        modulos: modulos // array de id_modulo
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        mostrarConfirmacionExito(data);
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: data.message
                        });
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Error al registrar la reserva'
                    });
                });
        }

        // Función para mostrar confirmación de éxito
        function mostrarConfirmacionExito(data) {
            const confirmacionSection = document.getElementById('confirmacion-section');
            const icono = document.getElementById('confirmacion-icono');
            const titulo = document.getElementById('confirmacion-titulo');
            const mensaje = document.getElementById('confirmacion-mensaje');
            const detalles = document.getElementById('confirmacion-detalles');

            icono.innerHTML = `
                <svg class="w-16 h-16 mx-auto text-green-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
            `;
            titulo.textContent = 'Reserva Exitosa';
            mensaje.textContent = data.mensaje;
            detalles.innerHTML = `
                <p>Espacio: ${data.espacio_nombre}</p>
                <p>Hora de término: ${data.hora_termino}</p>
            `;

            document.getElementById('duracion-section').classList.add('hidden');
            confirmacionSection.classList.remove('hidden');
        }

        // Función para mostrar error
        function mostrarError(errorMensaje) {
            const confirmacionSection = document.getElementById('confirmacion-section');
            const icono = document.getElementById('confirmacion-icono');
            const titulo = document.getElementById('confirmacion-titulo');
            const mensaje = document.getElementById('confirmacion-mensaje');

            icono.innerHTML = `
                <svg class="w-16 h-16 mx-auto text-red-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
            `;
            titulo.textContent = 'Error';
            mensaje.textContent = errorMensaje;

            confirmacionSection.classList.remove('hidden');
        }

        // Inicialización del canvas y otras funcionalidades
        document.addEventListener("DOMContentLoaded", function() {
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

            const mapaId = window.location.pathname.split('/').pop();
            console.log('ID del mapa:', mapaId);

            function mostrarNotificacion(mensaje, tipo) {
                const notificacion = document.createElement('div');
                notificacion.className = `fixed top-4 right-4 px-4 py-2 rounded-md text-white ${
                    tipo === 'success' ? 'bg-green-500' : 
                    tipo === 'error' ? 'bg-red-500' : 
                    'bg-blue-500'
                }`;
                notificacion.textContent = mensaje;
                document.body.appendChild(notificacion);
                setTimeout(() => notificacion.remove(), 3000);
            }

            window.actualizarEstados = function(esManual = false) {
                if (esManual) {
                    const botonTexto = document.getElementById('boton-texto');
                    const botonLoading = document.getElementById('boton-loading');
                    botonTexto.classList.add('hidden');
                    botonLoading.classList.remove('hidden');
                }

                fetch(`/plano/${mapaId}/bloques`)
                    .then(response => {
                        if (!response.ok) throw new Error('Error en la respuesta del servidor');
                        return response.json();
                    })
                    .then(bloquesData => {
                        // Lógica para pintar azul si el bloque está próximo a ser utilizado
                        bloquesData.forEach(bloque => {
                            if (bloque.detalles && bloque.detalles.es_proximo) {
                                bloque.estado = 'blue';
                            }
                        });
                        const hayCambios = JSON.stringify(state.indicators) !== JSON.stringify(bloquesData);
                        if (hayCambios) {
                            state.indicators = bloquesData;
                            state.originalCoordinates = bloquesData;
                            elements.indicatorsCtx.clearRect(0, 0, elements.indicatorsCanvas.width, elements
                                .indicatorsCanvas.height);
                            drawIndicators();
                            mostrarNotificacion('Estados actualizados correctamente', 'success');
                        } else {
                            mostrarNotificacion('No hay cambios en los estados', 'info');
                        }
                    })
                    .catch(error => {
                        console.error('Error en la actualización:', error);
                        mostrarNotificacion('Error al actualizar estados: ' + error.message, 'error');
                    })
                    .finally(() => {
                        if (esManual) {
                            const botonTexto = document.getElementById('boton-texto');
                            const botonLoading = document.getElementById('boton-loading');
                            botonTexto.classList.remove('hidden');
                            botonLoading.classList.add('hidden');
                        }
                    });
            };

            setInterval(() => actualizarEstados(false), 30000);

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

            elements.indicatorsCanvas.addEventListener('click', function(event) {
                if (!state.isImageLoaded) return;

                const rect = elements.indicatorsCanvas.getBoundingClientRect();
                const clickX = event.clientX - rect.left;
                const clickY = event.clientY - rect.top;

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

            initCanvases();
            window.addEventListener('resize', function() {
                initCanvases();
                drawIndicators();
            });

            function actualizarHoraYModulo() {
                const ahora = new Date();
                const horaActual = ahora.toLocaleTimeString('es-ES', {
                    hour: '2-digit',
                    minute: '2-digit',
                    second: '2-digit'
                });
                document.getElementById('hora-actual').textContent = horaActual;

                const dias = ['domingo', 'lunes', 'martes', 'miércoles', 'jueves', 'viernes', 'sábado'];
                const diaActual = dias[ahora.getDay()];
                const horaActualStr = ahora.toLocaleTimeString('es-ES', {
                    hour: '2-digit',
                    minute: '2-digit',
                    second: '2-digit'
                });

                function determinarModulo(hora) {
                    const [horas, minutos] = hora.split(':').map(Number);
                    const tiempoEnMinutos = horas * 60 + minutos;

                    const rangosModulos = [{
                            inicio: 8 * 60 + 10,
                            fin: 9 * 60,
                            numero: 1
                        }, // 08:10 - 09:00
                        {
                            inicio: 9 * 60 + 10,
                            fin: 10 * 60,
                            numero: 2
                        }, // 09:10 - 10:00
                        {
                            inicio: 10 * 60 + 10,
                            fin: 11 * 60,
                            numero: 3
                        }, // 10:10 - 11:00
                        {
                            inicio: 11 * 60 + 10,
                            fin: 12 * 60,
                            numero: 4
                        }, // 11:10 - 12:00
                        {
                            inicio: 12 * 60 + 10,
                            fin: 13 * 60,
                            numero: 5
                        }, // 12:10 - 13:00
                        {
                            inicio: 13 * 60 + 10,
                            fin: 14 * 60,
                            numero: 6
                        }, // 13:10 - 14:00  <--- AGREGA ESTA LÍNEA
                        {
                            inicio: 14 * 60 + 10,
                            fin: 15 * 60,
                            numero: 7
                        }, 
                        {
                            inicio: 15 * 60 + 10,
                            fin: 16 * 60,
                            numero: 7
                        },
                        {
                            inicio: 16 * 60 + 10,
                            fin: 17 * 60,
                            numero: 8
                        },
                        {
                            inicio: 17 * 60 + 10,
                            fin: 18 * 60,
                            numero: 9
                        },
                        {
                            inicio: 18 * 60 + 10,
                            fin: 19 * 60,
                            numero: 10
                        },
                        {
                            inicio: 19 * 60 + 10,
                            fin: 20 * 60,
                            numero: 11
                        },
                        {
                            inicio: 20 * 60 + 10,
                            fin: 21 * 60,
                            numero: 12
                        },
                        {
                            inicio: 21 * 60 + 10,
                            fin: 22 * 60,
                            numero: 13
                        },
                        {
                            inicio: 22 * 60 + 10,
                            fin: 23 * 60,
                            numero: 14
                        },
                        {
                            inicio: 23 * 60 + 10,
                            fin: 24 * 60,
                            numero: 15
                        },
                    ];

                    for (const rango of rangosModulos) {
                        if (tiempoEnMinutos >= rango.inicio && tiempoEnMinutos <= rango.fin) {
                            return rango.numero;
                        }
                    }
                    return null;
                }

                fetch(`/plano/${mapaId}/modulo-actual?hora=${horaActualStr}&dia=${diaActual}`)
                    .then(response => response.json())
                    .then(data => {
                        const moduloElement = document.getElementById('modulo-actual');
                        if (data.modulo) {
                            const horaInicio = data.modulo.hora_inicio.substring(0, 5);
                            const horaTermino = data.modulo.hora_termino.substring(0, 5);
                            const numeroModulo = determinarModulo(horaInicio);
                            if (numeroModulo) {
                                document.getElementById('modulo-actual').textContent = numeroModulo;
                                document.getElementById('modulo-horario').textContent = `${horaInicio} - ${horaTermino}`;
                            } else {
                                document.getElementById('modulo-actual').textContent = 'No hay módulos disponibles';
                                document.getElementById('modulo-horario').textContent = '-';
                            }
                        } else {
                            document.getElementById('modulo-actual').textContent = 'No hay módulos disponibles';
                            document.getElementById('modulo-horario').textContent = '-';
                        }
                    })
                    .catch(error => {
                        console.error('Error al obtener el módulo actual:', error);
                        document.getElementById('modulo-actual').textContent = 'Error';
                        document.getElementById('modulo-horario').textContent = '-';
                    });
            }

            setInterval(actualizarHoraYModulo, 1000);
            actualizarHoraYModulo();

            window.addEventListener('close-modal', async function(event) {
                if (event.detail === 'solicitar-espacio' && html5QrcodeScanner) {
                    try {
                        await html5QrcodeScanner.stop();
                        html5QrcodeScanner = null;
                        document.getElementById('profesor-info').classList.add('hidden');
                        document.getElementById('profesor-scan-section').classList.remove('hidden');
                        document.getElementById('espacio-scan-section').classList.add('hidden');
                        document.getElementById('qr-placeholder').style.display = 'flex';
                        document.getElementById('espacio-placeholder').style.display = 'flex';
                        const btnIniciarProfesor = document.getElementById('btn-iniciar-profesor');
                        btnIniciarProfesor.disabled = false;
                        btnIniciarProfesor.textContent = 'Iniciar Escaneo de Profesor';
                        const btnIniciarEspacio = document.getElementById('btn-iniciar-espacio');
                        btnIniciarEspacio.disabled = false;
                        btnIniciarEspacio.textContent = 'Iniciar Escaneo de Espacio';
                    } catch (err) {
                        console.error('Error al detener el escáner:', err);
                    }
                }
            });

            // Agregar el evento mousemove al canvas de indicadores
            elements.indicatorsCanvas.addEventListener('mousemove', function(event) {
                const rect = elements.indicatorsCanvas.getBoundingClientRect();
                state.mouseX = event.clientX - rect.left;
                state.mouseY = event.clientY - rect.top;
                drawIndicators(); // Redibujar los indicadores para actualizar el estado de hover
            });

            // Agregar el evento mouseleave para limpiar el estado de hover
            elements.indicatorsCanvas.addEventListener('mouseleave', function() {
                state.mouseX = -1;
                state.mouseY = -1;
                drawIndicators();
            });

            // Escuchar el evento de apertura del modal para iniciar el escáner
            window.addEventListener('open-modal', function(event) {
                if (event.detail === 'solicitar-espacio') {
                    setTimeout(initQRScanner, 300); // Pequeño delay para asegurar que el modal esté visible
                }
            });
        });

        window.mostrarDetallesBloque = function(bloque) {
            // Si el espacio está ocupado, iniciar directamente el proceso de registro de salida
            if (bloque.estado === 'red' || (bloque.detalles && bloque.detalles.estado === 'Ocupado')) {
                iniciarRegistroSalida(bloque.id);
                return;
            }

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

            // Mostrar información según el estado
            if (bloque.clase_proxima) {
                claseProxima.classList.remove('hidden');
                asignaturaProxima.textContent = `Asignatura: ${bloque.clase_proxima.asignatura}`;
                profesorProximo.textContent = `Profesor: ${bloque.clase_proxima.profesor}`;
                horarioProximo.textContent = `Horario: ${bloque.clase_proxima.hora_inicio} - ${bloque.clase_proxima.hora_termino}`;
            }

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
                        fps: 10,
                        qrbox: { width: 250, height: 250 },
                        aspectRatio: 1.0,
                        formatsToSupport: [Html5QrcodeSupportedFormats.QR_CODE],
                        rememberLastUsedCamera: true,
                        showTorchButtonIfSupported: true
                    };

                    html5QrcodeScanner = new Html5Qrcode("qr-reader-salida-profesor");
                    document.getElementById('salida-profesor-placeholder').style.display = 'none';
                    
                    await html5QrcodeScanner.start(
                        currentCameraId,
                        config,
                        onSalidaProfesorScanSuccess,
                        (error) => {
                            if (error.includes("QR code parse error")) return;
                            console.warn(`Error en el escaneo: ${error}`);
                        }
                    );
                } catch (err) {
                    console.error('Error al iniciar el escáner:', err);
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

            // Guardar el RUN del profesor en una variable global
            window.profesorRunSalida = decodedText;

            fetch(`/api/user/${decodedText}`)
                .then(response => {
                    if (!response.ok) {
                        return response.json().then(data => {
                            throw new Error(data.message || 'Error al buscar el profesor');
                        });
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success && data.user) {
                        // Mostrar información del profesor
                        document.getElementById('profesor-nombre-salida').textContent = data.user.name || '';
                        document.getElementById('profesor-correo-salida').textContent = data.user.email || '';
                        document.getElementById('profesor-info-salida').classList.remove('hidden');
                        document.getElementById('profesor-scan-section-salida').classList.add('hidden');
                        document.getElementById('espacio-scan-section-salida').classList.remove('hidden');
                    } else {
                        mostrarErrorEscaneoSalida('La persona no se encuentra registrada, contáctese con soporte.');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    mostrarErrorEscaneoSalida(error.message || 'Error al obtener información del profesor');
                });
        }

        function onSalidaEspacioScanSuccess(decodedText) {
            if (html5QrcodeScanner) {
                html5QrcodeScanner.stop();
                html5QrcodeScanner = null;
            }

            // Asegurarnos de que tenemos el RUN del profesor
            if (!window.profesorRunSalida) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'No se encontró la información del profesor'
                });
                return;
            }

            console.log('ID del espacio:', decodedText);
            registrarSalidaClase(window.profesorRunSalida, decodedText);
        }

        function registrarSalidaClase(run, espacioId) {
            console.log('Enviando datos:', { run, espacio_id: espacioId });

            const data = {
                run: run,
                espacio_id: espacioId
            };

            console.log('Datos a enviar:', data);

            fetch('/api/registrar-salida-clase', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify(data)
            })
            .then(response => {
                console.log('Respuesta del servidor:', response);
                if (!response.ok) {
                    return response.json().then(data => {
                        throw new Error(data.message || 'Error al registrar la salida');
                    });
                }
                return response.json();
            })
            .then(data => {
                console.log('Datos recibidos:', data);
                if (data.success) {
                    // Actualizar el estado visual del espacio
                    const block = state.indicators.find(b => b.id === espacioId);
                    if (block) {
                        block.estado = 'green';
                        state.originalCoordinates = state.indicators.map(i => ({...i}));
                        drawIndicators();
                    }
                    
                    Swal.fire({
                        icon: 'success',
                        title: '¡Éxito!',
                        text: data.message,
                        showConfirmButton: false,
                        timer: 2000
                    }).then(() => {
                        // Esperar 1 segundo adicional antes de cerrar todo
                        setTimeout(() => {
                            // Limpiar la variable global
                            window.profesorRunSalida = null;
                            // Cerrar el modal de salida
                            window.dispatchEvent(new CustomEvent('close-modal', { detail: 'salida-espacio' }));
                            // Actualizar los estados
                            actualizarEstados(true);
                        }, 1000);
                    });
                } else {
                    throw new Error(data.message || 'Error al registrar la salida');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: error.message || 'Ocurrió un error al registrar la salida'
                });
            });
        }

        // Función para inicializar el escáner de espacio en la salida
        async function initEspacioScannerSalida() {
            try {
                const btnIniciar = document.getElementById('btn-iniciar-espacio-salida');
                btnIniciar.disabled = true;
                btnIniciar.innerHTML = `
                    <svg class="inline w-4 h-4 mr-2 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    Iniciando cámara...
                `;

                const hasPermission = await requestCameraPermission();
                if (!hasPermission) {
                    alert('Se requieren permisos de cámara para escanear códigos QR');
                    return;
                }

                currentCameraId = await getFirstCamera();
                if (!currentCameraId) {
                    alert('No se encontró ninguna cámara disponible');
                    return;
                }

                const config = {
                    fps: 10,
                    qrbox: { width: 250, height: 250 },
                    aspectRatio: 1.0,
                    formatsToSupport: [Html5QrcodeSupportedFormats.QR_CODE],
                    rememberLastUsedCamera: true,
                    showTorchButtonIfSupported: true
                };

                document.getElementById('salida-espacio-placeholder').style.display = 'none';
                html5QrcodeScanner = new Html5Qrcode("qr-reader-salida-espacio");
                
                await html5QrcodeScanner.start(
                    currentCameraId,
                    config,
                    onSalidaEspacioScanSuccess,
                    (error) => {
                        if (error.includes("QR code parse error")) return;
                        console.warn(`Error en el escaneo: ${error}`);
                    }
                );
            } catch (err) {
                console.error('Error al iniciar el escáner de espacio:', err);
                alert('Error al iniciar la cámara. Por favor, verifica los permisos y que la cámara no esté siendo usada por otra aplicación.');
                document.getElementById('salida-espacio-placeholder').style.display = 'flex';
                const btnIniciar = document.getElementById('btn-iniciar-espacio-salida');
                btnIniciar.disabled = false;
                btnIniciar.textContent = 'Iniciar Escaneo de Espacio';
            }
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
    </script>
</x-app-layout>