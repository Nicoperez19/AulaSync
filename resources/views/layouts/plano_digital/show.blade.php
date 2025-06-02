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

    <!-- Modal de solicitud de espacio -->
    <x-modal name="solicitar-espacio" :show="false" maxWidth="2xl">
        <x-slot name="header">
            <h1 class="font-sans text-lg font-semibold text-white dark:text-white">Solicitar Espacio</h1>
        </x-slot>
        <div class="p-4">
            <div class="space-y-4">
                <!-- Sección de escaneo de profesor -->
                <div id="profesor-scan-section"
                    class="flex flex-col items-center justify-center p-4 bg-gray-100 rounded-lg dark:bg-gray-700">
                    <div id="qr-reader" class="w-full max-w-md">
                        <div id="qr-placeholder"
                            class="flex flex-col items-center justify-center p-8 text-center bg-white rounded-lg shadow-lg dark:bg-gray-800">
                            <div class="mb-4">
                                <svg class="w-16 h-16 text-light-cloud-blue" xmlns="http://www.w3.org/2000/svg"
                                    fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 4v1m6 11h2m-6 0h-2v4m0-11v4m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" />
                                </svg>
                            </div>
                            <h3 class="mb-2 text-lg font-semibold text-gray-900 dark:text-white">Escaneo de Profesor
                            </h3>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Presione el botón para iniciar el
                                escaneo del profesor</p>
                            <button id="btn-iniciar-profesor" onclick="initQRScanner()"
                                class="px-4 py-2 mt-4 text-sm font-medium text-white transition-all duration-300 rounded-md bg-light-cloud-blue hover:bg-opacity-90 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-light-cloud-blue">
                                Iniciar Escaneo de Profesor
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Información del profesor -->
                <div id="profesor-info" class="hidden p-4 bg-white rounded-lg shadow dark:bg-gray-800">
                    <h3 class="mb-2 text-lg font-semibold text-gray-900 dark:text-white">Información del Profesor</h3>
                    <div class="space-y-2">
                        <p class="text-sm text-gray-600 dark:text-gray-400">Nombre: <span id="profesor-nombre"
                                class="font-medium text-gray-900 dark:text-white"></span></p>
                        <p class="text-sm text-gray-600 dark:text-gray-400">Correo: <span id="profesor-correo"
                                class="font-medium text-gray-900 dark:text-white"></span></p>
                    </div>
                </div>

                <!-- Sección de escaneo de espacio -->
                <div id="espacio-scan-section"
                    class="flex flex-col items-center justify-center hidden p-4 bg-gray-100 rounded-lg dark:bg-gray-700">
                    <div id="qr-reader-espacio" class="w-full max-w-md">
                        <div id="espacio-placeholder"
                            class="flex flex-col items-center justify-center p-8 text-center bg-white rounded-lg shadow-lg dark:bg-gray-800">
                            <div class="mb-4">
                                <svg class="w-16 h-16 text-light-cloud-blue" xmlns="http://www.w3.org/2000/svg"
                                    fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                </svg>
                            </div>
                            <h3 class="mb-2 text-lg font-semibold text-gray-900 dark:text-white">Escaneo de Espacio
                            </h3>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Presione el botón para iniciar el
                                escaneo del espacio</p>
                            <button id="btn-iniciar-espacio" onclick="initEspacioScanner()"
                                class="px-4 py-2 mt-4 text-sm font-medium text-white transition-all duration-300 rounded-md bg-light-cloud-blue hover:bg-opacity-90 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-light-cloud-blue">
                                Iniciar Escaneo de Espacio
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Información del espacio y verificación -->
                <div id="espacio-info" class="hidden p-4 bg-white rounded-lg shadow dark:bg-gray-800">
                    <h3 class="mb-2 text-lg font-semibold text-gray-900 dark:text-white">Información del Espacio</h3>
                    <div class="space-y-2">
                        <p class="text-sm text-gray-600 dark:text-gray-400">Nombre: <span id="espacio-nombre"
                                class="font-medium text-gray-900 dark:text-white"></span></p>
                        <p class="text-sm text-gray-600 dark:text-gray-400">Tipo: <span id="espacio-tipo"
                                class="font-medium text-gray-900 dark:text-white"></span></p>
                    </div>
                    <div id="verificacion-espacio" class="p-4 mt-4 rounded-lg">
                        <div class="flex items-center justify-center space-x-2">
                            <svg class="w-6 h-6 text-gray-400 animate-spin" xmlns="http://www.w3.org/2000/svg"
                                fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10"
                                    stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor"
                                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                </path>
                            </svg>
                            <span class="text-sm text-gray-600">Verificando disponibilidad...</span>
                        </div>
                    </div>
                </div>

                <!-- Sección de selección de duración -->
                <div id="duracion-section" class="hidden p-4 bg-white rounded-lg shadow dark:bg-gray-800">
                    <h3 class="mb-4 text-lg font-semibold text-gray-900 dark:text-white">Seleccione la duración de la
                        reserva</h3>
                    <div class="grid grid-cols-3 gap-4">
                        <button onclick="seleccionarDuracion(30)"
                            class="p-3 text-sm font-medium text-gray-700 transition-all duration-300 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-light-cloud-blue">
                            30 minutos
                        </button>
                        <button onclick="seleccionarDuracion(60)"
                            class="p-3 text-sm font-medium text-gray-700 transition-all duration-300 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-light-cloud-blue">
                            1 hora
                        </button>
                        <button onclick="seleccionarDuracion(90)"
                            class="p-3 text-sm font-medium text-gray-700 transition-all duration-300 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-light-cloud-blue">
                            1.5 horas
                        </button>
                        <button onclick="seleccionarDuracion(120)"
                            class="p-3 text-sm font-medium text-gray-700 transition-all duration-300 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-light-cloud-blue">
                            2 horas
                        </button>
                        <button onclick="seleccionarDuracion(180)"
                            class="p-3 text-sm font-medium text-gray-700 transition-all duration-300 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-light-cloud-blue">
                            3 horas
                        </button>
                        <button onclick="seleccionarDuracion(240)"
                            class="p-3 text-sm font-medium text-gray-700 transition-all duration-300 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-light-cloud-blue">
                            4 horas
                        </button>
                    </div>
                </div>

                <!-- Sección de confirmación -->
                <div id="confirmacion-section" class="hidden p-4 bg-white rounded-lg shadow dark:bg-gray-800">
                    <div class="text-center">
                        <div id="confirmacion-icono" class="mx-auto mb-4">
                            <!-- El ícono se llenará dinámicamente -->
                        </div>
                        <h3 id="confirmacion-titulo" class="mb-2 text-lg font-semibold text-gray-900 dark:text-white">
                        </h3>
                        <p id="confirmacion-mensaje" class="text-sm text-gray-600 dark:text-gray-400"></p>
                        <div id="confirmacion-detalles"
                            class="mt-4 space-y-2 text-sm text-gray-600 dark:text-gray-400">
                            <!-- Los detalles se llenarán dinámicamente -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </x-modal>

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
                    const btnIniciar = document.getElementById('btn-iniciar-profesor');
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

                    html5QrcodeScanner = new Html5Qrcode("qr-reader");
                    document.getElementById('qr-placeholder').style.display = 'none';

                    await html5QrcodeScanner.start(
                        currentCameraId,
                        config,
                        onScanSuccess,
                        (error) => {
                            // Solo mostrar errores críticos, ignorar errores de detección
                            if (error.includes("QR code parse error")) {
                                return;
                            }
                            console.warn(`Error en el escaneo: ${error}`);
                        }
                    );
                } catch (err) {
                    console.error('Error al iniciar el escáner:', err);
                    alert(
                        'Error al iniciar la cámara. Por favor, verifica los permisos y que la cámara no esté siendo usada por otra aplicación.'
                        );
                    document.getElementById('qr-placeholder').style.display = 'flex';
                    const btnIniciar = document.getElementById('btn-iniciar-profesor');
                    btnIniciar.disabled = false;
                    btnIniciar.textContent = 'Iniciar Escaneo de Profesor';
                }
            }
        }

        function onScanSuccess(decodedText, decodedResult) {
            if (html5QrcodeScanner) {
                html5QrcodeScanner.stop();
                html5QrcodeScanner = null;
            }

            fetch(`/api/user/${decodedText}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.user.roles.includes('profesor')) {
                        userId = data.user.id;
                        document.getElementById('profesor-nombre').textContent = data.user.name;
                        document.getElementById('profesor-correo').textContent = data.user.email;
                        document.getElementById('profesor-info').classList.remove('hidden');
                        document.getElementById('profesor-scan-section').classList.add('hidden');
                        document.getElementById('espacio-scan-section').classList.remove('hidden');
                    } else {
                        alert('El usuario escaneado no es un profesor');
                        setTimeout(initQRScanner, 2000);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error al obtener información del usuario');
                    setTimeout(initQRScanner, 2000);
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

            // Obtener información del espacio y verificar disponibilidad
            fetch(`/api/espacio/${espacioId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        document.getElementById('espacio-nombre').textContent = data.espacio.nombre;
                        document.getElementById('espacio-tipo').textContent = data.espacio.tipo;
                        document.getElementById('espacio-info').classList.remove('hidden');
                        document.getElementById('espacio-scan-section').classList.add('hidden');

                        // Verificar el estado del espacio y la programación del profesor
                        fetch(`/api/verificar-espacio/${userId}/${espacioId}`)
                            .then(response => response.json())
                            .then(data => {
                                if (data.estado === 'disponible') {
                                    if (data.tieneClaseProgramada) {
                                        // Si tiene clase programada, registrar ingreso
                                        registrarIngresoClase();
                                    } else {
                                        // Si no tiene clase programada, mostrar opciones de duración
                                        mostrarOpcionesDuracion();
                                    }
                                } else {
                                    // Si el espacio está ocupado, mostrar mensaje
                                    mostrarMensajeOcupado(data);
                                }
                            })
                            .catch(error => {
                                console.error('Error:', error);
                                mostrarError('Error al verificar el espacio');
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
        function registrarIngresoClase() {
            fetch('/api/registrar-ingreso-clase', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        user_id: userId,
                        espacio_id: espacioId
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        mostrarConfirmacionExito(data);
                    } else {
                        mostrarError(data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    mostrarError('Error al registrar el ingreso');
                });
        }

        // Función para mostrar opciones de duración
        function mostrarOpcionesDuracion() {
            document.getElementById('espacio-scan-section').classList.add('hidden');
            document.getElementById('duracion-section').classList.remove('hidden');
        }

        // Función para seleccionar duración
        function seleccionarDuracion(minutos) {
            duracionSeleccionada = minutos;
            registrarReservaEspontanea();
        }

        // Función para registrar reserva espontánea
        function registrarReservaEspontanea() {
            fetch('/api/registrar-reserva-espontanea', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        user_id: userId,
                        espacio_id: espacioId,
                        duracion: duracionSeleccionada
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        mostrarConfirmacionExito(data);
                    } else {
                        mostrarError(data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    mostrarError('Error al registrar la reserva');
                });
        }

        // Función para mostrar mensaje de espacio ocupado
        function mostrarMensajeOcupado(data) {
            const confirmacionSection = document.getElementById('confirmacion-section');
            const icono = document.getElementById('confirmacion-icono');
            const titulo = document.getElementById('confirmacion-titulo');
            const mensaje = document.getElementById('confirmacion-mensaje');
            const detalles = document.getElementById('confirmacion-detalles');

            icono.innerHTML = `
                <svg class="w-16 h-16 mx-auto text-red-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
            `;
            titulo.textContent = 'Espacio Ocupado';
            mensaje.textContent =
                `Este espacio está actualmente ocupado por el profesor ${data.profesor_nombre} hasta las ${data.hora_termino}`;
            detalles.innerHTML = `
                <p>Profesor: ${data.profesor_nombre}</p>
                <p>Hora de término: ${data.hora_termino}</p>
            `;

            document.getElementById('espacio-scan-section').classList.add('hidden');
            confirmacionSection.classList.remove('hidden');
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
            const state = {
                mapImage: null,
                originalImageSize: null,
                indicators: @json($bloques),
                originalCoordinates: @json($bloques),
                isImageLoaded: false,
                mouseX: 0,
                mouseY: 0
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

            function drawIndicators() {
                if (!state.isImageLoaded) return;
                elements.indicatorsCtx.clearRect(0, 0, elements.indicatorsCanvas.width, elements.indicatorsCanvas
                    .height);
                state.indicators.forEach(indicator => drawIndicator(indicator));
            }

            function drawIndicator(indicator) {
                if (!state.isImageLoaded) return;
                const {
                    id,
                    estado
                } = indicator;
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
                switch (estado) {
                    case 'red':
                        color = '#EF4444';
                        break;
                    case 'blue':
                        color = '#3B82F6';
                        break;
                    case 'yellow':
                        color = '#F59E0B';
                        break;
                    default:
                        color = '#10B981';
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
        });

        window.mostrarDetallesBloque = function(bloque) {
            if (typeof bloque === 'object' && bloque.dataset) {
                bloque = JSON.parse(bloque.dataset.bloque);
            }

            const detalles = bloque.detalles;
            document.getElementById('modal-titulo').textContent = bloque.nombre + ' - ' + bloque.id;
            document.getElementById('modal-tipo-espacio').textContent = detalles.tipo_espacio;
            document.getElementById('modal-puestos').textContent = detalles.puestos_disponibles;

            const planificacionDiv = document.getElementById('modal-planificacion');
            if (detalles.planificacion) {
                planificacionDiv.classList.remove('hidden');
                document.getElementById('modal-asignatura').textContent =
                    `Asignatura: ${detalles.planificacion.asignatura}`;
                document.getElementById('modal-profesor').textContent = `Profesor: ${detalles.planificacion.profesor}`;

                const modulosList = document.getElementById('modal-modulos');
                modulosList.innerHTML = '';
                const dias = ['lunes', 'martes', 'miércoles', 'jueves', 'viernes', 'sábado'];
                const hoy = dias[new Date().getDay()];
                const modulosHoy = detalles.planificacion.modulos.filter(modulo => modulo.dia && modulo.dia
                    .toLowerCase() === hoy);
                modulosHoy.forEach(modulo => {
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

            const reservaDiv = document.getElementById('modal-reserva');
            if (detalles.reserva) {
                reservaDiv.classList.remove('hidden');
                document.getElementById('modal-fecha-reserva').textContent = `Fecha: ${detalles.reserva.fecha_reserva}`;
                document.getElementById('modal-hora-reserva').textContent = `Hora: ${detalles.reserva.hora}`;
            } else {
                reservaDiv.classList.add('hidden');
            }

            window.dispatchEvent(new CustomEvent('open-modal', {
                detail: 'detalles-bloque'
            }));
        };

        function cambiarPiso(mapaId) {
            // Mostrar indicador de carga
            const loadingOverlay = document.createElement('div');
            loadingOverlay.className = 'fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50';
            loadingOverlay.innerHTML = `
                <div class="p-4 bg-white rounded-lg shadow-lg">
                    <svg class="w-8 h-8 text-light-cloud-blue animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                </div>
            `;
            document.body.appendChild(loadingOverlay);

            // Obtener los datos del nuevo plano
            fetch(`/plano/${mapaId}/data`)
                .then(response => response.json())
                .then(data => {
                    // Actualizar el número de piso
                    document.getElementById('numero-piso-actual').textContent = data.mapa.piso.numero;

                    // Actualizar los estados de los bloques
                    state.indicators = data.bloques;
                    state.originalCoordinates = data.bloques;

                    // Limpiar los canvases antes de cargar la nueva imagen
                    elements.mapCtx.clearRect(0, 0, elements.mapCanvas.width, elements.mapCanvas.height);
                    elements.indicatorsCtx.clearRect(0, 0, elements.indicatorsCanvas.width, elements.indicatorsCanvas
                        .height);

                    // Actualizar la imagen del mapa
                    const img = new Image();
                    img.crossOrigin = "anonymous"; // Permitir carga de imágenes de diferentes orígenes
                    img.onload = function() {
                        state.mapImage = img;
                        state.originalImageSize = {
                            width: img.naturalWidth,
                            height: img.naturalHeight
                        };
                        state.isImageLoaded = true;

                        // Reinicializar los canvases con las nuevas dimensiones
                        initCanvases();

                        // Dibujar la nueva imagen y los indicadores
                        drawCanvas();
                        drawIndicators();
                    };
                    img.onerror = function() {
                        console.error('Error al cargar la imagen:', data.mapa.ruta_mapa);
                        mostrarNotificacion('Error al cargar la imagen del plano', 'error');
                    };
                    img.src = data.mapa.ruta_mapa;

                    // Actualizar las clases activas en las pestañas
                    document.querySelectorAll('#pills-tab a').forEach(a => {
                        if (a.getAttribute('href').includes(mapaId)) {
                            a.classList.add('bg-light-cloud-blue', 'text-white', 'border-light-cloud-blue');
                            a.classList.remove('bg-white', 'text-gray-700', 'border-gray-300');
                        } else {
                            a.classList.remove('bg-light-cloud-blue', 'text-white', 'border-light-cloud-blue');
                            a.classList.add('bg-white', 'text-gray-700', 'border-gray-300');
                        }
                    });

                    // Actualizar la URL sin recargar la página
                    window.history.pushState({}, '', `/plano/${mapaId}`);
                })
                .catch(error => {
                    console.error('Error al cargar el plano:', error);
                    mostrarNotificacion('Error al cargar el plano', 'error');
                })
                .finally(() => {
                    // Eliminar el indicador de carga
                    loadingOverlay.remove();
                });
        }
    </script>
</x-app-layout>
