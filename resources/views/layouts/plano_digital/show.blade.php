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
                            <button onclick="window.dispatchEvent(new CustomEvent('open-modal', { detail: 'solicitar-espacio' }))"
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
    <div id="modal-hora-actual" class="fixed bottom-4 right-4 bg-light-cloud-blue rounded-lg shadow-lg p-4 w-64 z-50 border border-blue-600">
        <div class="flex flex-col space-y-3">
            <div class="flex items-center justify-between border-b border-blue-400 pb-2">
                <div class="flex items-center space-x-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <h3 class="text-sm font-semibold text-white">Hora Actual</h3>
                </div>
                <span id="hora-actual" class="text-lg font-bold text-white"></span>
            </div>
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                    </svg>
                    <h3 class="text-sm font-semibold text-white">Módulo Actual</h3>
                </div>
                <span id="modulo-actual" class="text-sm font-medium text-white">-</span>
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
                <div id="profesor-scan-section" class="flex flex-col items-center justify-center p-4 bg-gray-100 dark:bg-gray-700 rounded-lg">
                    <div id="qr-reader" class="w-full max-w-md">
                        <!-- Vista previa mientras carga -->
                        <div id="qr-placeholder" class="flex flex-col items-center justify-center p-8 text-center bg-white dark:bg-gray-800 rounded-lg shadow-lg">
                            <div class="mb-4">
                                <svg class="w-16 h-16 text-light-cloud-blue" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v4m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" />
                                </svg>
                            </div>
                            <h3 class="mb-2 text-lg font-semibold text-gray-900 dark:text-white">Escaneo de Código QR</h3>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Presione el botón para iniciar el escaneo del profesor</p>
                            <button id="btn-iniciar-profesor" onclick="initQRScanner()" class="mt-4 px-4 py-2 text-sm font-medium text-white transition-all duration-300 rounded-md bg-light-cloud-blue hover:bg-opacity-90 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-light-cloud-blue">
                                Iniciar Escaneo de Profesor
                            </button>
                        </div>
                    </div>
                    <div id="qr-reader-results" class="mt-4 text-center"></div>
                </div>

                <!-- Información del profesor y verificación de espacio -->
                <div id="profesor-info" class="hidden p-4 bg-white dark:bg-gray-800 rounded-lg shadow">
                    <h3 class="mb-2 text-lg font-semibold text-gray-900 dark:text-white">Información del Profesor</h3>
                    <div class="space-y-2">
                        <p class="text-sm text-gray-600 dark:text-gray-400">Nombre: <span id="profesor-nombre" class="font-medium text-gray-900 dark:text-white"></span></p>
                        <p class="text-sm text-gray-600 dark:text-gray-400">Correo: <span id="profesor-correo" class="font-medium text-gray-900 dark:text-white"></span></p>
                    </div>
                    <div id="verificacion-espacio" class="mt-4 p-4 rounded-lg">
                        <div class="flex items-center justify-center space-x-2">
                            <svg class="w-6 h-6 text-gray-400 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <span class="text-sm text-gray-600">Verificando espacio...</span>
                        </div>
                    </div>
                </div>

                <!-- Sección de escaneo de llaves -->
                <div id="llaves-scan-section" class="hidden flex flex-col items-center justify-center p-4 bg-gray-100 dark:bg-gray-700 rounded-lg">
                    <div id="qr-reader-llaves" class="w-full max-w-md">
                        <div id="llaves-placeholder" class="flex flex-col items-center justify-center p-8 text-center bg-white dark:bg-gray-800 rounded-lg shadow-lg">
                            <div class="mb-4">
                                <svg class="w-16 h-16 text-light-cloud-blue" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
                                </svg>
                            </div>
                            <h3 class="mb-2 text-lg font-semibold text-gray-900 dark:text-white">Escaneo de Llaves</h3>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Presione el botón para iniciar el escaneo de las llaves</p>
                            <button id="btn-iniciar-llaves" onclick="initLlavesScanner()" class="mt-4 px-4 py-2 text-sm font-medium text-white transition-all duration-300 rounded-md bg-light-cloud-blue hover:bg-opacity-90 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-light-cloud-blue">
                                Iniciar Escaneo de Llaves
                            </button>
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

        // Funciones del QR scanner en el ámbito global
        async function requestCameraPermission() {
            try {
                const stream = await navigator.mediaDevices.getUserMedia({ video: true });
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
                        qrbox: 250,
                        aspectRatio: 1.0
                    };

                    html5QrcodeScanner = new Html5Qrcode("qr-reader");
                    document.getElementById('qr-placeholder').style.display = 'none';
                    
                    await html5QrcodeScanner.start(
                        currentCameraId,
                        config,
                        onScanSuccess,
                        (error) => {
                            console.warn(`Error en el escaneo: ${error}`);
                        }
                    );
                } catch (err) {
                    console.error('Error al iniciar el escáner:', err);
                    alert('Error al iniciar la cámara. Por favor, verifica los permisos y que la cámara no esté siendo usada por otra aplicación.');
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
            
            // Obtener información del profesor
            fetch(`/api/profesor/${decodedText}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        document.getElementById('profesor-nombre').textContent = data.profesor.name;
                        document.getElementById('profesor-correo').textContent = data.profesor.email;
                        document.getElementById('profesor-info').classList.remove('hidden');
                        document.getElementById('profesor-scan-section').classList.add('hidden');

                        // Guardar el ID del profesor para la verificación posterior
                        window.profesorId = data.profesor.id;
                        
                        // Mostrar mensaje para escanear el código del espacio
                        const verificacionDiv = document.getElementById('verificacion-espacio');
                        verificacionDiv.innerHTML = `
                            <div class="flex flex-col items-center justify-center space-y-2">
                                <svg class="w-8 h-8 text-light-cloud-blue" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                </svg>
                                <span class="text-sm font-medium text-gray-700">Por favor, escanee el código del espacio</span>
                            </div>
                        `;
                        
                        // Mostrar la sección de escaneo de espacio
                        document.getElementById('llaves-scan-section').classList.remove('hidden');
                    } else {
                        alert('No se encontró información del profesor');
                        setTimeout(initQRScanner, 2000);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error al obtener información del profesor');
                    setTimeout(initQRScanner, 2000);
                });
        }

        function onLlavesScanSuccess(decodedText, decodedResult) {
            if (html5QrcodeScanner) {
                html5QrcodeScanner.stop();
                html5QrcodeScanner = null;
            }
            
            // Verificar si el profesor tiene asignado este espacio
            const espacioId = decodedText;
            const profesorId = window.profesorId;
            
            // Obtener el día y hora actual
            const ahora = new Date();
            const dias = ['lunes', 'martes', 'miércoles', 'jueves', 'viernes', 'sábado'];
            const diaActual = dias[ahora.getDay()];
            const horaActual = ahora.toLocaleTimeString('es-ES', { hour: '2-digit', minute: '2-digit' });

            // Mostrar mensaje de carga
            const verificacionDiv = document.getElementById('verificacion-espacio');
            verificacionDiv.innerHTML = `
                <div class="flex items-center justify-center space-x-2">
                    <svg class="w-6 h-6 text-gray-400 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span class="text-sm text-gray-600">Verificando espacio...</span>
                </div>
            `;

            // Hacer la petición para verificar el espacio
            fetch(`/api/verificar-espacio/${profesorId}/${espacioId}?dia=${diaActual}&hora=${horaActual}`)
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`Error del servidor: ${response.status}`);
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.esValido) {
                        verificacionDiv.innerHTML = `
                            <div class="flex flex-col items-center justify-center space-y-2 text-green-600">
                                <svg class="w-8 h-8" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                                <span class="text-sm font-medium">Este espacio está asignado a su horario actual</span>
                            </div>
                        `;
                    } else {
                        verificacionDiv.innerHTML = `
                            <div class="flex flex-col items-center justify-center space-y-2 text-red-600">
                                <svg class="w-8 h-8" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                                <span class="text-sm font-medium">Este espacio no está asignado a su horario actual</span>
                                <p class="text-xs text-gray-500">Por favor, verifique su horario asignado</p>
                            </div>
                        `;
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    verificacionDiv.innerHTML = `
                        <div class="flex flex-col items-center justify-center space-y-2 text-red-600">
                            <svg class="w-8 h-8" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                            <span class="text-sm font-medium">Error al verificar el espacio</span>
                            <p class="text-xs text-gray-500">${error.message}</p>
                        </div>
                    `;
                });
        }

        // Función para inicializar el escáner de llaves
        async function initLlavesScanner() {
            try {
                // Ocultar el botón y mostrar mensaje de carga
                const btnIniciar = document.getElementById('btn-iniciar-llaves');
                btnIniciar.disabled = true;
                btnIniciar.innerHTML = `
                    <svg class="inline w-4 h-4 mr-2 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    Iniciando cámara...
                `;

                // Verificar permisos antes de iniciar
                const hasPermission = await requestCameraPermission();
                if (!hasPermission) {
                    alert('Se requieren permisos de cámara para escanear códigos QR');
                    return;
                }

                // Obtener la primera cámara disponible
                currentCameraId = await getFirstCamera();
                if (!currentCameraId) {
                    alert('No se encontró ninguna cámara disponible');
                    return;
                }

                const config = {
                    fps: 10,
                    qrbox: 250,
                    aspectRatio: 1.0
                };

                // Ocultar el placeholder
                document.getElementById('llaves-placeholder').style.display = 'none';

                // Iniciar el escáner
                html5QrcodeScanner = new Html5Qrcode("qr-reader-llaves");
                await html5QrcodeScanner.start(
                    currentCameraId,
                    config,
                    onLlavesScanSuccess,
                    (error) => {
                        console.warn(`Error en el escaneo de llaves: ${error}`);
                    }
                );
            } catch (err) {
                console.error('Error al iniciar el escáner de llaves:', err);
                alert('Error al iniciar la cámara. Por favor, verifica los permisos y que la cámara no esté siendo usada por otra aplicación.');
                // Mostrar el placeholder nuevamente si hay un error
                document.getElementById('llaves-placeholder').style.display = 'flex';
                // Restaurar el botón
                const btnIniciar = document.getElementById('btn-iniciar-llaves');
                btnIniciar.disabled = false;
                btnIniciar.textContent = 'Iniciar Escaneo de Llaves';
            }
        }

        // Inicialización del canvas y otras funcionalidades
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
                            elements.indicatorsCtx.clearRect(0, 0, elements.indicatorsCanvas.width, elements.indicatorsCanvas.height);
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
                elements.indicatorsCtx.clearRect(0, 0, elements.indicatorsCanvas.width, elements.indicatorsCanvas.height);
                state.indicators.forEach(indicator => drawIndicator(indicator));
            }

            function drawIndicator(indicator) {
                if (!state.isImageLoaded) return;
                const { id, estado } = indicator;
                const position = calculatePosition(indicator);
                const width = config.indicatorWidth;
                const height = config.indicatorHeight;

                let color;
                switch (estado) {
                    case 'red': color = '#EF4444'; break;
                    case 'blue': color = '#3B82F6'; break;
                    case 'yellow': color = '#F59E0B'; break;
                    default: color = '#10B981';
                }

                elements.indicatorsCtx.fillStyle = color;
                elements.indicatorsCtx.fillRect(position.x - width / 2, position.y - height / 2, width, height);
                elements.indicatorsCtx.lineWidth = 2;
                elements.indicatorsCtx.strokeStyle = config.indicatorBorder;
                elements.indicatorsCtx.strokeRect(position.x - width / 2, position.y - height / 2, width, height);

                elements.indicatorsCtx.font = `bold ${config.fontSize}px Arial`;
                elements.indicatorsCtx.fillStyle = config.indicatorTextColor;
                elements.indicatorsCtx.textAlign = 'center';
                elements.indicatorsCtx.textBaseline = 'middle';
                elements.indicatorsCtx.fillText(id, position.x, position.y);
            }

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
                const horaActual = ahora.toLocaleTimeString('es-ES', { hour: '2-digit', minute: '2-digit', second: '2-digit' });
                document.getElementById('hora-actual').textContent = horaActual;

                const dias = ['domingo', 'lunes', 'martes', 'miércoles', 'jueves', 'viernes', 'sábado'];
                const diaActual = dias[ahora.getDay()];
                const horaActualStr = ahora.toLocaleTimeString('es-ES', { hour: '2-digit', minute: '2-digit', second: '2-digit' });

                fetch(`/plano/${mapaId}/modulo-actual?hora=${horaActualStr}&dia=${diaActual}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.modulo) {
                            const horaInicio = data.modulo.hora_inicio.substring(0, 5);
                            const horaTermino = data.modulo.hora_termino.substring(0, 5);
                            document.getElementById('modulo-actual').textContent = `${horaInicio} - ${horaTermino}`;
                        } else {
                            document.getElementById('modulo-actual').textContent = 'Sin módulo';
                        }
                    })
                    .catch(error => {
                        console.error('Error al obtener el módulo actual:', error);
                        document.getElementById('modulo-actual').textContent = 'Error';
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
                        document.getElementById('llaves-scan-section').classList.add('hidden');
                        document.getElementById('qr-placeholder').style.display = 'flex';
                        document.getElementById('llaves-placeholder').style.display = 'flex';
                        const btnIniciarProfesor = document.getElementById('btn-iniciar-profesor');
                        btnIniciarProfesor.disabled = false;
                        btnIniciarProfesor.textContent = 'Iniciar Escaneo de Profesor';
                        const btnIniciarLlaves = document.getElementById('btn-iniciar-llaves');
                        btnIniciarLlaves.disabled = false;
                        btnIniciarLlaves.textContent = 'Iniciar Escaneo de Llaves';
                    } catch (err) {
                        console.error('Error al detener el escáner:', err);
                    }
                }
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
                document.getElementById('modal-asignatura').textContent = `Asignatura: ${detalles.planificacion.asignatura}`;
                document.getElementById('modal-profesor').textContent = `Profesor: ${detalles.planificacion.profesor}`;

                const modulosList = document.getElementById('modal-modulos');
                modulosList.innerHTML = '';
                const dias = ['lunes','martes','miércoles','jueves','viernes','sábado'];
                const hoy = dias[new Date().getDay()];
                const modulosHoy = detalles.planificacion.modulos.filter(modulo => modulo.dia && modulo.dia.toLowerCase() === hoy);
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
                document.getElementById('modal-asignatura-proxima').textContent = `Asignatura: ${detalles.planificacion_proxima.asignatura}`;
                document.getElementById('modal-profesor-proximo').textContent = `Profesor: ${detalles.planificacion_proxima.profesor}`;
                const horaInicio = detalles.planificacion_proxima.hora_inicio.substring(0, 5);
                const horaTermino = detalles.planificacion_proxima.hora_termino.substring(0, 5);
                document.getElementById('modal-horario-proximo').textContent = `Horario: ${horaInicio} - ${horaTermino}`;
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
    </script>
</x-app-layout>
