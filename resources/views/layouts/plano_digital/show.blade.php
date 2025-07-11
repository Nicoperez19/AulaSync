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
            <div class="flex flex-col items-center justify-center flex-1 p-1">
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
                                <span id="qr-status" class="text-xs text-white">Esperando</span>
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
                        <div class="w-3 h-3 bg-orange-500 rounded-full"></div>
                        <span class="flex-1 text-xs">Reservado</span>
                    </div>
                    <div class="flex items-center w-full gap-1">
                        <div class="w-3 h-3 bg-blue-500 rounded-full"></div>
                        <span class="flex-1 text-xs">Próximo</span>
                    </div>
                    <div class="flex items-center w-full gap-1">
                        <div class="w-3 h-3 bg-green-500 rounded-full"></div>
                        <span class="flex-1 text-xs">Disponible</span>
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
                        <ul class="flex border-b dark:border-gray-700" id="pills-tab" role="tablist">
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

                                <!-- Botón de actualización manual -->
                                <button id="refreshBtn"
                                    class="absolute z-10 p-2 text-white transition-colors duration-200 bg-green-600 rounded-lg shadow-lg bottom-4 right-16 hover:bg-green-700"
                                    onclick="forzarActualizacionEstados()"
                                    title="Actualizar estados de espacios">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
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
        
        <!-- Botón de solicitud (solo visible cuando el espacio está disponible) -->
        <div id="btnSolicitarContainer" class="hidden mt-6">
            <button id="btnSolicitarLlaves" 
                class="w-full px-4 py-3 text-white transition-colors duration-200 bg-green-600 rounded-lg hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500"
                onclick="iniciarSolicitud()">
                <div class="flex items-center justify-center space-x-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
                    </svg>
                    <span class="font-medium">Solicitar Llaves</span>
                </div>
            </button>
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


    <!-- Modal para devolución de llaves (rediseñado) -->
    <x-modal name="devolver-llaves" :show="false" focusable>
        @slot('title')
        <div class="px-6 py-3 text-white bg-red-700 rounded-t">
            <h2 class="text-lg font-semibold text-center">Devolver Llaves</h2>
        </div>
        @endslot
        <div class="flex flex-col items-center justify-center p-8 bg-white">
            <div class="flex flex-col items-center mb-6">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-20 h-20 mb-4 text-black" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v2m0 5h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 20h14a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
                <span id="qr-status-devolucion" class="mb-2 text-base text-black">Esperando escaneo del usuario...</span>
                <span class="text-sm text-black">Escanee el código QR del usuario y luego del espacio</span>
            </div>
            <input type="text" id="qr-input-devolucion"
                class="absolute w-full px-1 py-1 border rounded opacity-0 focus:outline-none focus:ring-2 focus:ring-blue-500"
                placeholder="Escanea un código QR" autofocus>
        </div>
    </x-modal>

    <!-- Modal para solicitud de llaves -->
    <x-modal name="solicitar-llaves" :show="false" focusable>
        @slot('title')
        <h2 class="text-lg font-medium text-white dark:text-gray-100">
            Solicitar Llaves
        </h2>
        @endslot
        <div class="p-6">
            <div class="flex flex-col items-center justify-center">
                <div
                    class="w-full max-w-md p-2 text-white border border-white rounded-md shadow-sm bg-light-cloud-blue">
                    <!-- QR Placeholder -->
                    <div class="p-2 mt-2 text-center rounded-md bg-white/10">
                        <div class="relative">
                            <span id="qr-status-solicitud" class="text-xs text-white">Esperando escaneo...</span>
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
                        <input type="text" id="qr-input-solicitud"
                            class="absolute w-full px-1 py-1 border rounded opacity-0 focus:outline-none focus:ring-2 focus:ring-blue-500"
                            placeholder="Escanea un código QR" autofocus>
                    </div>
                </div>
            </div>
        </div>
    </x-modal>

    <!-- Modal para registro de usuario no registrado -->
    <x-modal name="registro-usuario" :show="false" focusable>
        @slot('title')
        <h2 class="text-lg font-medium text-white dark:text-gray-100">
            Registro de Usuario
        </h2>
        @endslot
        <div class="p-6">
            <div class="space-y-4">
                <div class="text-center">
                    <div class="flex items-center justify-center w-16 h-16 mx-auto mb-4 text-yellow-500 bg-yellow-100 rounded-full">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-medium text-gray-900">Usuario No Registrado</h3>
                    <p class="mt-2 text-sm text-gray-600">
                        El RUN <span id="run-no-registrado" class="font-semibold"></span> no está registrado en el sistema.
                        Complete los siguientes datos para continuar con la solicitud.
                    </p>
                </div>

                <form id="form-registro-usuario" class="space-y-4">
                    <div>
                        <label for="nombre-usuario" class="block text-sm font-medium text-gray-700">Nombre Completo *</label>
                        <input type="text" id="nombre-usuario" name="nombre" required
                            class="block w-full px-3 py-2 mt-1 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                    </div>

                    <div>
                        <label for="email-usuario" class="block text-sm font-medium text-gray-700">Correo Electrónico *</label>
                        <input type="email" id="email-usuario" name="email" required
                            class="block w-full px-3 py-2 mt-1 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                    </div>

                    <div>
                        <label for="telefono-usuario" class="block text-sm font-medium text-gray-700">Teléfono *</label>
                        <input type="tel" id="telefono-usuario" name="telefono" required
                            class="block w-full px-3 py-2 mt-1 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                    </div>

                    <div>
                        <label for="modulos-utilizacion" class="block text-sm font-medium text-gray-700">Módulos de Utilización *</label>
                        <select id="modulos-utilizacion" name="modulos_utilizacion" required
                            class="block w-full px-3 py-2 mt-1 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                            <option value="">Seleccione la cantidad de módulos</option>
                            <option value="1">1 módulo</option>
                            <option value="2">2 módulos</option>
                            <option value="3">3 módulos</option>
                            <option value="4">4 módulos</option>
                            <option value="5">5 módulos</option>
                            <option value="6">6 módulos</option>
                            <option value="7">7 módulos</option>
                            <option value="8">8 módulos</option>
                            <option value="9">9 módulos</option>
                            <option value="10">10 módulos</option>
                            <option value="11">11 módulos</option>
                            <option value="12">12 módulos</option>
                            <option value="13">13 módulos</option>
                            <option value="14">14 módulos</option>
                            <option value="15">15 módulos</option>
                        </select>
                    </div>

                    <div class="flex pt-4 space-x-3">
                        <button type="button" onclick="cancelarRegistro()"
                            class="flex-1 px-4 py-2 text-gray-700 bg-gray-200 border border-gray-300 rounded-md hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-gray-500">
                            Cancelar
                        </button>
                        <button type="submit"
                            class="flex-1 px-4 py-2 text-white bg-blue-600 border border-transparent rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            Registrar y Continuar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </x-modal>

    <!-- Modal para seleccionar cantidad de módulos -->
    <x-modal name="seleccionar-modulos" :show="false" focusable>
        @slot('title')
        <h2 class="text-lg font-medium text-center text-black">
            Seleccionar Módulos
        </h2>
        @endslot
        <div class="p-6 text-center">
            <p class="mb-4 text-base text-gray-800">¿Por cuántos módulos desea reservar?</p>
            <div class="mb-2">
                <input type="number" id="input-cantidad-modulos" min="1" max="1" value="1"
                    class="w-24 px-2 py-1 text-lg text-center border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div class="mb-4 text-sm text-gray-600">
                Disponibles: <span id="max-modulos-disponibles">1</span> módulos consecutivos antes de la próxima clase/reserva.
            </div>
            <button id="btn-confirmar-modulos"
                class="px-6 py-2 text-white bg-blue-600 rounded hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                Reservar
            </button>
        </div>
    </x-modal>

    <script>
        // ========================================
        // VARIABLE PHP PARA EL ID DEL MAPA
        // ========================================
        @php
            $mapaIdValue = $mapa->id_mapa ?? 1;
        @endphp

        // ========================================
        // POLYFILL PARA ROUNDRECT (COMPATIBILIDAD)
        // ========================================
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

        // ========================================
        // VARIABLES GLOBALES PARA EL ESCÁNER QR
        // ========================================
        let bufferQR = '';               // Buffer para almacenar el código QR escaneado
        let esperandoUsuario = true;     // Flag para indicar si estamos esperando escanear usuario o espacio
        let usuarioEscaneado = null;     // Usuario que se escaneó
        let qrUsuario = null;            // QR del usuario procesado
        let qrEspacio = null;            // QR del espacio procesado
        let esperandoEspacio = false;    // Flag para indicar si estamos esperando escanear el espacio
        let lastScanTime = 0;            // Último tiempo de escaneo para evitar duplicados

        // ========================================
        // VARIABLES GLOBALES PARA EL MODO DE OPERACIÓN
        // ========================================
        let modoOperacion = 'solicitud'; // 'solicitud' o 'devolucion'
        let bufferQRDevolucion = '';     // Buffer específico para devolución
        let esperandoUsuarioDevolucion = true; // Flag para devolución
        let usuarioEscaneadoDevolucion = null; // Usuario escaneado para devolución
        let espacioEscaneadoDevolucion = null; // Espacio escaneado para devolución

        // ========================================
        // VARIABLES GLOBALES PARA EL FLUJO DE SOLICITUD
        // ========================================
        let bufferQRSolicitud = '';      // Buffer específico para solicitud
        let esperandoUsuarioSolicitud = true; // Flag para solicitud
        let usuarioEscaneadoSolicitud = null; // Usuario escaneado para solicitud
        let espacioEscaneadoSolicitud = null; // Espacio escaneado para solicitud

        // ========================================
        // VARIABLES GLOBALES PARA USUARIOS NO REGISTRADOS
        // ========================================
        let usuarioNoRegistrado = null;  // Datos del usuario no registrado
        let espacioPendiente = null;     // Espacio pendiente después del registro
        let modoOperacionActual = null;  // 'solicitud' o 'devolucion'

        // ========================================
        // VARIABLES GLOBALES PARA EL ESTADO DE LA SOLICITUD
        // ========================================
        let userId = null;               // ID del usuario actual
        let espacioId = null;            // ID del espacio actual
        let tieneClaseProgramada = false; // Si el usuario tiene clase programada
        let duracionSeleccionada = null;  // Duración seleccionada para la reserva
        let noDisponibleReserva = false;  // Si la reserva no está disponible

        // ========================================
        // VARIABLES PARA CONTROL DE ENFOQUE
        // ========================================
        let qrScanTimeout = null;        // Timeout para el escaneo QR
        let qrScanAttempts = 0;          // Intentos de escaneo
        let qrScanMaxAttempts = 30;      // Máximo de intentos (3 segundos si fps=10)

        // ========================================
        // VARIABLES PARA CONTROL DE PANTALLA COMPLETA
        // ========================================
        let isFullscreen = false;        // Estado de pantalla completa
        let originalSidebarDisplay = ''; // Estado original del sidebar
        let originalMainContentMargin = ''; // Margen original del contenido principal

        // ========================================
        // OBTENER ID DEL MAPA DESDE EL CONTROLADOR
        // ========================================
        const mapaId = @json($mapaIdValue);

        // ========================================
        // CONFIGURACIÓN GLOBAL PARA LOS INDICADORES
        // ========================================
        const config = {
            indicatorSize: 35,           // Tamaño del indicador
            indicatorWidth: 37,          // Ancho del indicador
            indicatorHeight: 37,         // Alto del indicador
            indicatorBorder: '#FFFFFF',  // Color del borde
            indicatorTextColor: '#FFFFFF', // Color del texto
            fontSize: 12                 // Tamaño de fuente
        };

        // ========================================
        // VARIABLES GLOBALES PARA EL ESTADO DEL MAPA
        // ========================================
        const state = {
            mapImage: null,              // Imagen del mapa
            originalImageSize: null,     // Tamaño original de la imagen
            indicators: @json($bloques) || [], // Indicadores/bloques del mapa
            originalCoordinates: @json($bloques) || [], // Coordenadas originales
            isImageLoaded: false,        // Si la imagen está cargada
            mouseX: 0,                   // Posición X del mouse
            mouseY: 0,                   // Posición Y del mouse
            currentZoom: 1,              // Zoom actual
            isDragging: false,           // Si se está arrastrando
            lastX: 0,                    // Última posición X
            lastY: 0,                    // Última posición Y
            offsetX: 0,                  // Offset X
            offsetY: 0,                  // Offset Y
            currentTime: new Date(),     // Tiempo actual
            currentModule: null,         // Módulo actual
            currentDay: new Date().getDay(), // Día actual
            updateInterval: null,        // Intervalo de actualización
            hoveredIndicator: null,      // Indicador sobre el que está el mouse
            lastLocalChange: null        // Timestamp del último cambio local
        };

        // ========================================
        // VARIABLES GLOBALES PARA LOS ELEMENTOS DEL CANVAS
        // ========================================
        let elements = {
            mapCanvas: null,             // Canvas del mapa
            mapCtx: null,                // Contexto del canvas del mapa
            indicatorsCanvas: null,      // Canvas de los indicadores
            indicatorsCtx: null          // Contexto del canvas de indicadores
        };

        // ========================================
        // FUNCIONES DE VERIFICACIÓN DE USUARIO Y ESPACIO
        // ========================================
        
        // Función para verificar si un usuario existe en el sistema
        async function verificarUsuario(run) {
            try {
                const response = await fetch(`/api/verificar-usuario/${run}`);
                return await response.json();
            } catch (error) {
                console.error('Error:', error);
                return null;
            }
        }

        // Función para verificar si un espacio existe y está disponible
        async function verificarEspacio(idEspacio) {
            try {
                const response = await fetch(`/api/verificar-espacio/${idEspacio}`);
                return await response.json();
            } catch (error) {
                console.error('Error:', error);
                return null;
            }
        }

        // Función para crear una reserva
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

        // Función para registrar usuario no registrado
        async function registrarUsuarioNoRegistrado(datosUsuario) {
            try {
                const response = await fetch('/api/registrar-usuario-no-registrado', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify(datosUsuario)
                });
                return await response.json();
            } catch (error) {
                console.error('Error:', error);
                return null;
            }
        }

        // ========================================
        // FUNCIÓN PRINCIPAL PARA MANEJAR EL ESCANEO QR
        // ========================================
        // Esta función procesa los códigos QR escaneados y maneja la lógica de reservas
        async function handleScan(event) {
            if (event.key === 'Enter') {
            if (esperandoUsuario) {
                    // Procesar QR de usuario (formato: RUN¿12345678')
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
                    // Procesar QR de espacio (formato: TH'L01)
                const espacioProcesado = bufferQR.replace(/'/g, '-');
                const espacioInfo = await verificarEspacio(espacioProcesado);
                
                if (espacioInfo?.verificado) {
                        if (espacioInfo.disponible) {
                            // Reservar directamente, sin confirmación
                            const reserva = await crearReserva(usuarioEscaneado, espacioProcesado);
                            if (reserva?.success) {
                                Swal.fire('¡Reserva exitosa!', '', 'success');
                                document.getElementById('qr-status').innerHTML = 'Reserva exitosa';
                                document.getElementById('nombre-espacio').textContent = espacioInfo.espacio.nombre;
                                // Actualizar el color del indicador a 'Ocupado' (rojo)
                                const block = state.indicators.find(b => b.id === espacioProcesado);
                                if (block) {
                                    block.estado = '#FF0000'; // Rojo
                                    state.originalCoordinates = state.indicators.map(i => ({ ...i }));
                                    drawIndicators();
                                }
                            } else {
                                Swal.fire('Error', reserva?.mensaje || 'Error en reserva', 'error');
                                document.getElementById('qr-status').innerHTML = reserva?.mensaje || 'Error en reserva';
                            }
                        } else {
                            // Espacio ocupado - mostrar mensaje informativo
                            Swal.fire('Espacio Ocupado', `El espacio ${espacioInfo.espacio.nombre} está actualmente ocupado.`, 'info');
                            document.getElementById('qr-status').innerHTML = 'Espacio ocupado';
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

        // ========================================
        // FUNCIÓN PARA INICIALIZAR LOS ELEMENTOS DEL CANVAS
        // ========================================
        function initElements() {
            elements.mapCanvas = document.getElementById('mapCanvas');
            elements.mapCtx = elements.mapCanvas.getContext('2d');
            elements.indicatorsCanvas = document.getElementById('indicatorsCanvas');
            elements.indicatorsCtx = elements.indicatorsCanvas.getContext('2d');
        }

        // ========================================
        // FUNCIÓN PARA DETECTAR QUÉ INDICADOR ESTÁ SIENDO HOVER
        // ========================================
        function getHoveredIndicator(mouseX, mouseY) {
            if (!state.isImageLoaded) return null;

            // Recorrer los indicadores de atrás hacia adelante (para detectar el superior)
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

        // ========================================
        // FUNCIÓN PARA MANEJAR EL MOVIMIENTO DEL MOUSE
        // ========================================
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

        // ========================================
        // FUNCIÓN PARA MANEJAR EL CLIC DEL MOUSE
        // ========================================
        function handleMouseClick(event) {
            const rect = elements.indicatorsCanvas.getBoundingClientRect();
            const mouseX = event.clientX - rect.left;
            const mouseY = event.clientY - rect.top;

            const clickedIndicator = getHoveredIndicator(mouseX, mouseY);

            if (clickedIndicator) {
                mostrarModalEspacio(clickedIndicator);
            }
        }

        // ========================================
        // FUNCIÓN PARA MANEJAR CUANDO EL MOUSE SALE DEL CANVAS
        // ========================================
        function handleMouseLeave() {
            if (state.hoveredIndicator) {
                state.hoveredIndicator = null;
                drawIndicators();
                elements.indicatorsCanvas.style.cursor = 'default';
            }
        }

        // ========================================
        // FUNCIÓN PARA INICIALIZAR LOS CANVAS
        // ========================================
        function initCanvases() {
            const container = elements.mapCanvas.parentElement;
            const width = container.clientWidth;
            const height = container.clientHeight;

            // Establecer dimensiones de los canvas
            elements.mapCanvas.width = width;
            elements.mapCanvas.height = height;
            elements.indicatorsCanvas.width = width;
            elements.indicatorsCanvas.height = height;

            drawCanvas();
            drawIndicators(); // Dibujar indicadores inmediatamente
        }

        // ========================================
        // FUNCIÓN PARA DIBUJAR EL CANVAS DEL MAPA
        // ========================================
        function drawCanvas() {
            elements.mapCtx.clearRect(0, 0, elements.mapCanvas.width, elements.mapCanvas.height);
            if (!state.mapImage) return;

            // Calcular proporciones para mantener el aspect ratio
            const canvasRatio = elements.mapCanvas.width / elements.mapCanvas.height;
            const imageRatio = state.mapImage.width / state.mapImage.height;
            let drawWidth, drawHeight, offsetX, offsetY;

            if (imageRatio > canvasRatio) {
                // La imagen es más ancha que el canvas
                drawWidth = elements.mapCanvas.width;
                drawHeight = elements.mapCanvas.width / imageRatio;
                offsetX = 0;
                offsetY = (elements.mapCanvas.height - drawHeight) / 2;
            } else {
                // La imagen es más alta que el canvas
                drawHeight = elements.mapCanvas.height;
                drawWidth = elements.mapCanvas.height * imageRatio;
                offsetX = (elements.mapCanvas.width - drawWidth) / 2;
                offsetY = 0;
            }

            elements.mapCtx.drawImage(state.mapImage, offsetX, offsetY, drawWidth, drawHeight);
        }

        // ========================================
        // FUNCIÓN PARA CALCULAR LA POSICIÓN DE LOS INDICADORES
        // ========================================
        function calculatePosition(indicator) {
            if (!state.isImageLoaded || !state.mapImage) return { x: 0, y: 0 };

            // Calcular proporciones del canvas
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

            // Buscar las coordenadas originales del indicador
            const originalIndicator = state.originalCoordinates.find(i => i.id === indicator.id);
            if (!originalIndicator) return { x: 0, y: 0 };

            // Calcular la posición escalada
            const x = offsetX + (originalIndicator.x / state.originalImageSize.width) * drawWidth;
            const y = offsetY + (originalIndicator.y / state.originalImageSize.height) * drawHeight;

            return { x, y };
        }

        // ========================================
        // FUNCIÓN PARA DIBUJAR UN INDICADOR
        // ========================================
        function dibujarIndicador(elements, position, finalWidth, finalHeight, color, id, isHovered, detalles, moduloActual) {
            // Calcular el factor de escala para el efecto hover
            const scale = isHovered ? 1.2 : 1.0;
            const scaledWidth = finalWidth * scale;
            const scaledHeight = finalHeight * scale;

            // Calcular la posición para que el escalado sea desde el centro
            const offsetX = (scaledWidth - finalWidth) / 2;
            const offsetY = (scaledHeight - finalHeight) / 2;
            const drawX = position.x - scaledWidth / 2;
            const drawY = position.y - scaledHeight / 2;

            // Dibujar el rectángulo del indicador
            elements.indicatorsCtx.fillStyle = color;
            elements.indicatorsCtx.fillRect(drawX, drawY, scaledWidth, scaledHeight);

            // Dibujar el borde del indicador
            elements.indicatorsCtx.lineWidth = 2;
            elements.indicatorsCtx.strokeStyle = config.indicatorBorder;
            elements.indicatorsCtx.strokeRect(drawX, drawY, scaledWidth, scaledHeight);

            // Dibujar el texto del indicador (dividir por guion si existe)
            elements.indicatorsCtx.font = `bold ${config.fontSize}px Arial`;
            elements.indicatorsCtx.fillStyle = config.indicatorTextColor;
            elements.indicatorsCtx.textAlign = 'center';
            elements.indicatorsCtx.textBaseline = 'middle';

            let lines = [];
            if (id.includes('-')) {
                lines = id.split('-');
            } else {
                lines = [id];
            }

            const lineHeight = config.fontSize + 2;
            const totalTextHeight = lines.length * lineHeight;
            const startY = position.y - (totalTextHeight / 2) + (lineHeight / 2);

            lines.forEach((line, index) => {
                const y = startY + (index * lineHeight);
                elements.indicatorsCtx.fillText(line, position.x, y);
            });
        }

        // ========================================
        // FUNCIÓN PARA DIBUJAR TODOS LOS INDICADORES
        // ========================================
        function drawIndicators() {
            if (!state.isImageLoaded) {
                console.log('⚠️ Imagen no cargada, no se pueden dibujar indicadores');
                return;
            }
            
            // Limpiar el canvas de indicadores
            elements.indicatorsCtx.clearRect(0, 0, elements.indicatorsCanvas.width, elements.indicatorsCanvas.height);

            // Verificar que state.indicators existe y es un array
            if (!state.indicators || !Array.isArray(state.indicators)) {
                console.warn('⚠️ state.indicators no está definido o no es un array:', state.indicators);
                return;
            }

            console.log(`🎨 Dibujando ${state.indicators.length} indicadores...`);

            // Dibujar cada indicador
            state.indicators.forEach((indicator, index) => {
                const position = calculatePosition(indicator);
                
                // Normalizar el color según el estado
                let color = indicator.estado;
                console.log(`📍 Indicador ${indicator.id} (${index + 1}/${state.indicators.length}): estado original = "${color}"`);
                
                // Si el estado es un color hexadecimal, usarlo directamente
                if (color && color.startsWith('#')) {
                    console.log(`🎨 Indicador ${indicator.id}: usando color hexadecimal ${color}`);
                } else {
                    // Convertir estados textuales a colores
                    const estadoLower = color ? color.toLowerCase() : '';
                    console.log(`🔍 Indicador ${indicator.id}: estado en minúsculas = "${estadoLower}"`);
                    
                    switch (estadoLower) {
                        case 'disponible':
                        case 'available':
                            color = '#059669'; // Verde
                            console.log(`🟢 Indicador ${indicator.id}: aplicando verde (disponible)`);
                            break;
                        case 'ocupado':
                        case 'rojo':
                        case 'red':
                        case 'occupied':
                            color = '#FF0000'; // Rojo
                            console.log(`🔴 Indicador ${indicator.id}: aplicando rojo (ocupado)`);
                            break;
                        case 'reservado':
                        case 'amarillo':
                        case 'yellow':
                        case 'reserved':
                            color = '#FFA500'; // Naranja
                            console.log(`🟠 Indicador ${indicator.id}: aplicando naranja (reservado)`);
                            break;
                        case 'proximo':
                        case 'azul':
                        case 'blue':
                        case 'next':
                            color = '#3B82F6'; // Azul
                            console.log(`🔵 Indicador ${indicator.id}: aplicando azul (próximo)`);
                            break;
                        default:
                            // Si no hay estado o es desconocido, usar verde por defecto
                            color = '#059669'; // Verde
                            console.log(`🟢 Indicador ${indicator.id}: aplicando verde por defecto`);
                    }
                }
                
                console.log(`✅ Indicador ${indicator.id}: color final = "${color}"`);

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
            
            console.log('✅ Todos los indicadores han sido dibujados');
            
            // Mostrar resumen de estados cada 10 actualizaciones para no saturar la consola
            if (!state.contadorActualizaciones) {
                state.contadorActualizaciones = 0;
            }
            state.contadorActualizaciones++;
            
            if (state.contadorActualizaciones % 10 === 0) {
                mostrarResumenEstados();
            }
        }

        // ========================================
        // FUNCIÓN PARA MOSTRAR EL MODAL CON LA INFORMACIÓN DEL ESPACIO
        // ========================================
        function mostrarModalEspacio(indicator) {
            // Obtener elementos del modal
            const modalTitulo = document.getElementById('modalTitulo');
            const modalEstado = document.getElementById('modalEstado');
            const modalPlanificacionAsignatura = document.getElementById('modalPlanificacionAsignatura');
            const modalPlanificacionProfesor = document.getElementById('modalPlanificacionProfesor');
            const modalPlanificacionModulo = document.getElementById('modalPlanificacionModulo');
            const modalPlanificacionHorario = document.getElementById('modalPlanificacionHorario');
            const modalProxima = document.getElementById('modalProxima');
            const modalProximaDetalles = document.getElementById('modalProximaDetalles');

            // Configurar el título del modal
            modalTitulo.textContent = `${indicator.nombre} (${indicator.id}) `;

            // Configurar el estado del espacio
            let estadoTexto = '';
            let estadoColor = '';
            switch (indicator.estado) {
                case '#FF0000':
                    estadoTexto = 'Ocupado';
                    estadoColor = 'text-red-600';
                    break;
                case '#3B82F6':
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

            // Agregar información del ocupante si está ocupado o reservado
            let usuarioOcupando = '';
            let informacionUsuario = '';
            
            if ((estadoTexto === 'Ocupado' || estadoTexto === 'Reservado') && indicator.detalles?.usuario_ocupando) {
                usuarioOcupando = `<br><span class='text-xs text-gray-700'>${estadoTexto === 'Ocupado' ? 'Ocupado por:' : 'Reservado por:'} <b>${indicator.detalles.usuario_ocupando}</b></span>`;
                
                // Agregar información adicional del usuario si está disponible
                if (indicator.detalles?.reserva) {
                    const reserva = indicator.detalles.reserva;
                    const usuarioInfo = indicator.detalles.usuario_info;
                    
                    informacionUsuario = `
                        <div class='p-3 mt-2 rounded-lg bg-gray-50'>
                            <h4 class='mb-2 text-sm font-semibold text-gray-800'>Información del Usuario</h4>
                            <div class='space-y-1 text-xs text-gray-600'>
                                ${usuarioInfo ? `
                                    <div><span class='font-medium'>Nombre:</span> ${usuarioInfo.nombre}</div>
                                    <div><span class='font-medium'>Email:</span> ${usuarioInfo.email}</div>
                                    <div><span class='font-medium'>RUN:</span> ${usuarioInfo.run}</div>
                                ` : ''}
                                <div><span class='font-medium'>Fecha de reserva:</span> ${reserva.fecha_reserva || 'No especificada'}</div>
                                <div><span class='font-medium'>Hora de entrada:</span> ${reserva.hora ? reserva.hora.substring(0, 5) : 'No especificada'}</div>
                                ${reserva.hora_salida ? `<div><span class='font-medium'>Hora de salida:</span> ${reserva.hora_salida.substring(0, 5)}</div>` : ''}
                            </div>
                        </div>
                    `;
                }
            }
            
            modalEstado.innerHTML = `<span class="${estadoColor} font-semibold">${estadoTexto}</span>${usuarioOcupando}`;

            const detalles = indicator.detalles || {};
            const infoClaseActual = indicator.informacion_clase_actual;

            // Mostrar la planificación actual en los campos del modal
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

            // Agregar información del usuario si está ocupado o reservado
            if (informacionUsuario) {
                // Buscar el contenedor de planificación para insertar después
                const planificacionContainer = document.getElementById('planificacionContainer');
                if (planificacionContainer) {
                    // Crear un contenedor para la información del usuario si no existe
                    let usuarioContainer = document.getElementById('usuarioContainer');
                    if (!usuarioContainer) {
                        usuarioContainer = document.createElement('div');
                        usuarioContainer.id = 'usuarioContainer';
                        planificacionContainer.parentNode.insertBefore(usuarioContainer, planificacionContainer.nextSibling);
                    }
                    usuarioContainer.innerHTML = informacionUsuario;
                    usuarioContainer.style.display = '';
                }
            } else {
                // Ocultar el contenedor de usuario si no hay información
                const usuarioContainer = document.getElementById('usuarioContainer');
                if (usuarioContainer) {
                    usuarioContainer.style.display = 'none';
                }
            }

            // Mostrar el modal usando Alpine.js
            window.dispatchEvent(new CustomEvent('open-modal', {
                detail: 'data-space'
            }));

            // Configurar el estado visual del modal
            const estadoPill = document.getElementById('estadoPill');
            const estadoIcon = document.getElementById('estadoIcon');
            const planificacionContainer = document.getElementById('planificacionContainer');
            
            // Determinar color y texto según el estado
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
            
            // Aplicar estilos al pill de estado
            estadoPill.className = `inline-flex items-center px-4 py-2 text-base font-bold border rounded-full ${pillColor}`;
            estadoIcon.className = `w-3 h-3 mr-3 rounded-full ${iconColor}`;
            document.getElementById('modalEstado').textContent = texto;
            
            // Mostrar/ocultar planificación según el estado
            if (planificacionContainer) {
                planificacionContainer.style.display = mostrarPlanificacion ? '' : 'none';
            }
        }

        // ========================================
        // DEFINICIÓN DE HORARIOS POR DÍA Y MÓDULO
        // ========================================
        // Esta estructura define los horarios de cada módulo para cada día de la semana
        // Formato: {inicio: 'HH:MM:SS', fin: 'HH:MM:SS'}
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

        // ========================================
        // FUNCIÓN PARA OBTENER EL DÍA ACTUAL
        // ========================================
        function obtenerDiaActual() {
            const dias = ['domingo', 'lunes', 'martes', 'miercoles', 'jueves', 'viernes', 'sabado'];
            return dias[new Date().getDay()];
        }


        function moduloActualNum(hora) {
            const diaActual = obtenerDiaActual();
            const horariosDia = horariosModulos[diaActual];

            if (!horariosDia) return null;

            // Buscar en qué módulo estamos según la hora actual
            for (const [modulo, horario] of Object.entries(horariosDia)) {
                if (hora >= horario.inicio && hora < horario.fin) {
                    return parseInt(modulo);
                }
            }
            return null;
        }
        // ========================================
        // FUNCIÓN PARA DETERMINAR EL MÓDULO ACTUAL
        // ========================================
        function determinarModulo(hora) {
            const diaActual = moduloActualNum();
            const horariosDia = horariosModulos[diaActual];

            if (!horariosDia) return null;

            // Buscar en qué módulo estamos según la hora actual
            for (const [modulo, horario] of Object.entries(horariosDia)) {
                if (hora >= horario.inicio && hora < horario.fin) {
                    return parseInt(modulo);
                }
            }
            return null;
        }

        // ========================================
        // FUNCIÓN PARA ACTUALIZAR SOLO LA HORA
        // ========================================
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

        // ========================================
        // FUNCIÓN PARA FORMATEAR HORA A HH:MM
        // ========================================
        function formatearHora(horaCompleta) {
            return horaCompleta.slice(0, 5);
        }

        // ========================================
        // FUNCIÓN PARA ACTUALIZAR EL MÓDULO Y LOS COLORES
        // ========================================
        async function actualizarModuloYColores() {
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
            } else {
                if (moduloActualElement) moduloActualElement.textContent = 'No hay módulo programado';
                if (moduloHorarioElement) moduloHorarioElement.textContent = '-';
            }

            // Actualizar colores de los indicadores desde el servidor
            console.log('🔄 Actualizando módulo y colores de espacios...');
            await actualizarColoresEspacios();
        }

        // ========================================
        // FUNCIONES FALTANTES PARA LA DEVOLUCIÓN Y COLORES
        // ========================================
        
        // Función para procesar la devolución de llaves
        async function procesarDevolucion(run, idEspacio) {
            try {
                const response = await fetch('/api/devolver-llaves', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        id_espacio: idEspacio,
                        run: run
                    })
                });
                return await response.json();
            } catch (error) {
                console.error('Error al procesar devolución:', error);
                return { success: false, mensaje: 'Error de conexión' };
            }
        }

        // Función para resetear el estado de devolución
        function resetearEstadoDevolucion() {
                    esperandoUsuarioDevolucion = true;
                    usuarioEscaneadoDevolucion = null;
                    espacioEscaneadoDevolucion = null;
                    bufferQRDevolucion = '';
        }

        // Función para iniciar el proceso de devolución
        function iniciarDevolucion() {
            // Cerrar el modal actual
            window.dispatchEvent(new CustomEvent('close-modal', {
                detail: 'data-space'
            }));
            
            // Abrir el modal de devolución
            setTimeout(() => {
                window.dispatchEvent(new CustomEvent('open-modal', {
                    detail: 'devolver-llaves'
                }));
                
                // Resetear estado de devolución
                resetearEstadoDevolucion();
                
                // Configurar el input de devolución
                const inputDevolucion = document.getElementById('qr-input-devolucion');
                if (inputDevolucion) {
                    inputDevolucion.value = '';
                    inputDevolucion.focus();
                }
                
                // Actualizar el estado del QR
                document.getElementById('qr-status-devolucion').innerHTML = 'Esperando escaneo del usuario...';
            }, 300);
        }

        // ========================================
        // AJUSTE: Cerrar modal de devolución de llaves al finalizar
        // ========================================
        function cerrarModalDevolverLlaves() {
            window.dispatchEvent(new CustomEvent('close-modal', { detail: 'devolver-llaves' }));
        }

        // Función para manejar el escaneo de devolución
        async function handleScanDevolucion(event) {
            if (event.key === 'Enter') {
                if (esperandoUsuarioDevolucion) {
                    // Procesar QR de usuario para devolución (formato: RUN¿12345678')
                    const match = bufferQRDevolucion.match(/RUN¿(\d+)/);
                    if (match) {
                        usuarioEscaneadoDevolucion = match[1];
                        const usuarioInfo = await verificarUsuario(usuarioEscaneadoDevolucion);

                        if (usuarioInfo && usuarioInfo.verificado) {
                            document.getElementById('qr-status-devolucion').innerHTML = 'Usuario verificado. Escanee el espacio para devolver.';
                            esperandoUsuarioDevolucion = false;
                        } else {
                            Swal.fire('Error', usuarioInfo?.mensaje || 'Error de verificación', 'error');
                            document.getElementById('qr-status-devolucion').innerHTML = usuarioInfo?.mensaje || 'Error de verificación';
                        }
                    } else {
                        Swal.fire('Error', 'RUN inválido', 'error');
                        document.getElementById('qr-status-devolucion').innerHTML = 'RUN inválido';
                    }
                } else {
                    // Procesar QR de espacio para devolución (formato: TH'L01)
                    const espacioProcesado = bufferQRDevolucion.replace(/'/g, '-');
                    const espacioInfo = await verificarEspacio(espacioProcesado);

                    if (espacioInfo?.verificado) {
                        espacioEscaneadoDevolucion = espacioProcesado;
                        
                        // Procesar devolución directamente
                        const resultado = await procesarDevolucion(usuarioEscaneadoDevolucion, espacioProcesado);
                        
                        if (resultado.success) {
                            cerrarModalesDespuesDeSwal(['devolver-llaves', 'data-space']);
                            document.getElementById('qr-status-devolucion').innerHTML = 'Devolución exitosa';
                            // Actualizar el color del indicador a 'Disponible' (verde)
                            const block = state.indicators.find(b => b.id === espacioProcesado);
                            if (block) {
                                block.estado = '#059669'; // Verde
                                state.originalCoordinates = state.indicators.map(i => ({ ...i }));
                                drawIndicators();
                            }
                            // Cerrar el modal después de la devolución exitosa
                            setTimeout(() => {
                                cerrarModalDevolverLlaves();
                            }, 1000);
                        } else {
                            Swal.fire('Error', resultado.mensaje || 'Error al procesar la devolución', 'error');
                            document.getElementById('qr-status-devolucion').innerHTML = resultado.mensaje || 'Error al procesar la devolución';
                        }
                    } else {
                        Swal.fire('Error', espacioInfo?.mensaje || 'Error al verificar espacio', 'error');
                        document.getElementById('qr-status-devolucion').innerHTML = espacioInfo?.mensaje || 'Error al verificar espacio';
                    }
                    // Resetear el estado de devolución
                    resetearEstadoDevolucion();
                }
                bufferQRDevolucion = '';
                event.target.value = '';
            } else if (event.key.length === 1) {
                bufferQRDevolucion += event.key;
            }
        }

        // ========================================
        // Función utilitaria para cerrar modales después de un SweetAlert
        function cerrarModalesDespuesDeSwal(modales = []) {
            return Swal.fire('¡Devolución exitosa!', 'Las llaves han sido devueltas correctamente.', 'success').then(() => {
                modales.forEach(nombre => {
                    window.dispatchEvent(new CustomEvent('close-modal', { detail: nombre }));
                    setTimeout(() => {
                        document.querySelectorAll(`[data-modal="${nombre}"]`).forEach(el => el.classList.add('hidden'));
                    }, 200);
                });
            });
        }

        // ========================================
        // Función para sincronizar colores después de cargar la imagen
        async function sincronizarColoresDespuesCarga() {
            // Obtener datos actualizados del servidor después de cargar la imagen
            console.log('Sincronizando colores después de cargar la imagen');
            await actualizarColoresEspacios();
        }

        // Función para actualizar colores de espacios
        async function actualizarColoresEspacios() {
            try {
                console.log('🔄 Iniciando actualización de colores para mapaId:', mapaId);
                
                // Verificar si hay cambios locales recientes (menos de 10 segundos)
                if (state.lastLocalChange && (Date.now() - state.lastLocalChange) < 10000) {
                    console.log('⏭️ Ignorando actualización del servidor debido a cambios locales recientes');
                    return;
                }
                
                // Obtener datos actualizados del servidor
                const response = await fetch(`/plano/${encodeURIComponent(mapaId)}/bloques`);
                console.log('📡 Respuesta del servidor:', response.status, response.statusText);
                
                if (response.ok) {
                    const data = await response.json();
                    console.log('📊 Datos recibidos del servidor:', data);
                    
                    // Verificar que data.bloques existe y es un array
                    if (!data.bloques || !Array.isArray(data.bloques)) {
                        console.error('❌ La respuesta no contiene data.bloques válido:', data);
                        return;
                    }
                    
                    // Verificar si hay cambios en los indicadores antes de actualizar
                    let hayCambios = false;
                    let cambiosDetectados = [];
                    
                    if (state.indicators && state.indicators.length > 0) {
                        data.bloques.forEach((nuevoBloque, index) => {
                            const bloqueActual = state.indicators[index];
                            if (!bloqueActual || 
                                bloqueActual.estado !== nuevoBloque.estado ||
                                bloqueActual.nombre !== nuevoBloque.nombre) {
                                hayCambios = true;
                                cambiosDetectados.push({
                                    id: nuevoBloque.id,
                                    estadoAnterior: bloqueActual?.estado,
                                    estadoNuevo: nuevoBloque.estado
                                });
                                console.log(`🔄 Cambio detectado en bloque ${nuevoBloque.id}:`, {
                                    estadoAnterior: bloqueActual?.estado,
                                    estadoNuevo: nuevoBloque.estado,
                                    nombreAnterior: bloqueActual?.nombre,
                                    nombreNuevo: nuevoBloque.nombre
                                });
                            }
                        });
                    } else {
                        hayCambios = true; // Primera carga
                    }
                    
                    if (hayCambios) {
                        // Actualizar los indicadores con los nuevos datos
                        state.indicators = data.bloques;
                        console.log('✅ Indicadores actualizados:', state.indicators);
                        
                        // Redibujar los indicadores con los nuevos colores
                        drawIndicators();
                        console.log('🎨 Colores actualizados desde el servidor');
                        
                        // Mostrar información sobre los cambios si es una actualización manual
                        if (cambiosDetectados.length > 0) {
                            console.log(`📊 Se actualizaron ${cambiosDetectados.length} espacios:`, cambiosDetectados);
                        }
                    } else {
                        console.log('✅ No hay cambios en los estados de los espacios');
                    }
                } else {
                    console.error('❌ Error en la respuesta del servidor:', response.status, response.statusText);
                    const errorText = await response.text();
                    console.error('📄 Detalles del error:', errorText);
                    throw new Error(`Error del servidor: ${response.status} ${response.statusText}`);
                }
            } catch (error) {
                console.error('💥 Error al actualizar colores de espacios:', error);
                throw error; // Re-lanzar el error para que se maneje en la función llamadora
            }
        }

        // ========================================
        // FUNCIÓN PARA MOSTRAR ESTADO DE ACTUALIZACIÓN
        // ========================================
        function mostrarEstadoActualizacion(mensaje, tipo = 'info') {
            // Crear o actualizar el elemento de estado
            let estadoElement = document.getElementById('estado-actualizacion');
            if (!estadoElement) {
                estadoElement = document.createElement('div');
                estadoElement.id = 'estado-actualizacion';
                estadoElement.className = 'fixed top-4 right-4 z-50 px-4 py-2 rounded-lg shadow-lg text-white text-sm font-medium transition-all duration-300';
                document.body.appendChild(estadoElement);
            }

            // Configurar colores según el tipo
            const colores = {
                'info': 'bg-blue-500',
                'success': 'bg-green-500',
                'warning': 'bg-yellow-500',
                'error': 'bg-red-500'
            };

            estadoElement.className = `fixed top-4 right-4 z-50 px-4 py-2 rounded-lg shadow-lg text-white text-sm font-medium transition-all duration-300 ${colores[tipo] || colores.info}`;
            estadoElement.textContent = mensaje;

            // Ocultar después de 3 segundos
            setTimeout(() => {
                estadoElement.style.opacity = '0';
                setTimeout(() => {
                    if (estadoElement.parentNode) {
                        estadoElement.parentNode.removeChild(estadoElement);
                    }
                }, 300);
            }, 3000);
        }

        // ========================================
        // FUNCIÓN PARA FORZAR ACTUALIZACIÓN DE ESTADOS
        // ========================================
        async function forzarActualizacionEstados() {
            console.log('🚀 Forzando actualización de estados...');
            mostrarEstadoActualizacion('🔄 Actualizando estados de espacios...', 'info');
            
            // Limpiar el timestamp de cambios locales para permitir actualización
            state.lastLocalChange = null;
            
            try {
                await actualizarColoresEspacios();
                mostrarEstadoActualizacion('✅ Estados actualizados correctamente', 'success');
            } catch (error) {
                console.error('Error al forzar actualización:', error);
                mostrarEstadoActualizacion('❌ Error al actualizar estados', 'error');
            }
        }

        // ========================================
        // CONFIGURACIÓN DE INTERVALOS DE ACTUALIZACIÓN
        // ========================================
        // Actualizar la hora cada segundo
        setInterval(actualizarHora, 1000);
        actualizarHora(); // Actualizar inmediatamente al cargar

        // Actualizar módulo y colores cada 5 segundos (casi inmediato)
        setInterval(actualizarModuloYColores, 5000);
        actualizarModuloYColores(); // Actualizar inmediatamente al cargar

        // ========================================
        // EVENT LISTENER PARA ACTUALIZAR MODAL
        // ========================================
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

        // ========================================
        // INICIALIZACIÓN PRINCIPAL UNIFICADA
        // ========================================
        document.addEventListener("DOMContentLoaded", function () {
            // Configurar el input del escáner QR
            const inputEscanner = document.getElementById('qr-input');
            if (inputEscanner) {
                inputEscanner.addEventListener('keydown', handleScan);
                document.addEventListener('click', function () {
                    inputEscanner.focus();
                });
                inputEscanner.focus();
                document.getElementById('qr-status').innerHTML = 'Por favor, escanee el código QR del usuario';
            }
            // Inicializar elementos del canvas
            initElements();
            // Agregar event listeners para los eventos de mouse en el canvas
            if (elements.indicatorsCanvas) {
                elements.indicatorsCanvas.addEventListener('mousemove', handleMouseMove);
                elements.indicatorsCanvas.addEventListener('click', handleMouseClick);
                elements.indicatorsCanvas.addEventListener('mouseleave', handleMouseLeave);
            }
            // Cargar la imagen del mapa
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
                sincronizarColoresDespuesCarga();
                state.updateInterval = setInterval(actualizarColoresEspacios, 5000);
            };
            img.src = "{{ asset('storage/' . $mapa->ruta_mapa) }}";
            window.addEventListener('resize', function () {
                initCanvases();
            });
            window.addEventListener('beforeunload', function () {
                if (state.updateInterval) {
                    clearInterval(state.updateInterval);
                }
            });
            actualizarEstadoQR(null);
            // Configurar el input de devolución
            const inputDevolucion = document.getElementById('qr-input-devolucion');
            if (inputDevolucion) {
                inputDevolucion.addEventListener('keydown', handleScanDevolucion);
                document.addEventListener('click', () => inputDevolucion.focus());
                inputDevolucion.focus();
            }
            // Configurar el input de solicitud
            const inputSolicitud = document.getElementById('qr-input-solicitud');
            if (inputSolicitud) {
                inputSolicitud.addEventListener('keydown', handleScanSolicitud);
                document.addEventListener('click', () => inputSolicitud.focus());
                inputSolicitud.focus();
            }
            
            // Configurar el formulario de registro de usuario
            const formRegistro = document.getElementById('form-registro-usuario');
            if (formRegistro) {
                formRegistro.addEventListener('submit', procesarRegistroUsuario);
            }
            // Resetear estado cada vez que se abre el modal
            window.addEventListener('open-modal', function (e) {
                if (e.detail === 'devolver-llaves') {
                    resetearDevolucionQR();
                    setTimeout(() => {
                        if (inputDevolucion) inputDevolucion.focus();
                    }, 300);
                }
                if (e.detail === 'solicitar-llaves') {
                    resetearSolicitudQR();
                    setTimeout(() => {
                        if (inputSolicitud) inputSolicitud.focus();
                    }, 300);
                }
            });
            // Configurar botón devolver
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
            
            // Configurar botón de actualización manual
            const refreshBtn = document.getElementById('refreshBtn');
            if (refreshBtn) {
                refreshBtn.addEventListener('click', async function() {
                    // Agregar efecto visual de carga
                    const icon = refreshBtn.querySelector('svg');
                    icon.style.transform = 'rotate(360deg)';
                    icon.style.transition = 'transform 0.5s ease-in-out';
                    
                    await forzarActualizacionEstados();
                    
                    // Restaurar el ícono
                    setTimeout(() => {
                        icon.style.transform = 'rotate(0deg)';
                    }, 500);
                });
            }

            // Hacer funciones disponibles globalmente para debugging
            window.mostrarResumenEstados = mostrarResumenEstados;
            window.forzarActualizacionEstados = forzarActualizacionEstados;
            window.actualizarColoresEspacios = actualizarColoresEspacios;
            
            console.log('🔧 Funciones de debugging disponibles:');
            console.log('  - window.mostrarResumenEstados() - Muestra resumen de estados');
            console.log('  - window.forzarActualizacionEstados() - Fuerza actualización');
            console.log('  - window.actualizarColoresEspacios() - Actualiza colores');
        });

        // ========================================
        // FUNCIÓN PARA ACTUALIZAR EL ESTADO DEL QR
        // ========================================
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

        

        // ========================================
        // FUNCIÓN PARA MANEJAR EL ESCANEO DE SALIDA
        // ========================================
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

            // Procesar el RUN del profesor
            fetch(`/api/user/${run}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.user) {
                        document.getElementById('profesor-nombre-salida').textContent = data.user.name || '';
                        document.getElementById('profesor-correo-salida').textContent = data.user.email || '';
                        document.getElementById('profesor-info-salida').classList.remove('hidden');
                        document.getElementById('profesor-scan-section-salida').classList.add('hidden');
                        document.getElementById('espacio-scan-section-salida').classList.remove('hidden');
                        // Aquí puedes agregar la lógica para escanear el espacio
                    } else {
                        mostrarErrorEscaneoSalida('La persona no se encuentra registrada, contáctese con soporte.');
                    }
                })
                .catch(error => {
                    mostrarErrorEscaneoSalida(error.message || 'Error al obtener información del profesor');
                });
        }

        // ========================================
        // FUNCIÓN PARA MOSTRAR ERRORES DE ESCANEO
        // ========================================
        function mostrarErrorEscaneoSalida(mensaje) {
            const errorMsg = document.getElementById('salida-profesor-error-msg');
            const cargandoMsg = document.getElementById('salida-profesor-cargando-msg');
            
            if (errorMsg) {
                errorMsg.textContent = mensaje;
                errorMsg.classList.remove('hidden');
            }
            if (cargandoMsg) cargandoMsg.textContent = '';
        }
     
        // ========================================
        // FUNCIONES PARA MANEJAR USUARIOS NO REGISTRADOS
        // ========================================
        
        // Función para mostrar el modal de registro de usuario
        function mostrarModalRegistroUsuario(run) {
            document.getElementById('run-no-registrado').textContent = run;
            
            // Cerrar modal actual y abrir modal de registro
            window.dispatchEvent(new CustomEvent('close-modal', {
                detail: 'solicitar-llaves'
            }));
            
            setTimeout(() => {
                window.dispatchEvent(new CustomEvent('open-modal', {
                    detail: 'registro-usuario'
                }));
            }, 300);
        }

        // Función para cancelar el registro
        function cancelarRegistro() {
            // Cerrar modal de registro
            window.dispatchEvent(new CustomEvent('close-modal', {
                detail: 'registro-usuario'
            }));
            
            // Resetear variables
            usuarioNoRegistrado = null;
            espacioPendiente = null;
            modoOperacionActual = null;
            
            // Volver al modal de solicitud
            setTimeout(() => {
                window.dispatchEvent(new CustomEvent('open-modal', {
                    detail: 'solicitar-llaves'
                }));
                
                // Resetear estado de solicitud
                resetearEstadoSolicitud();
                
                // Configurar el input de solicitud
                const inputSolicitud = document.getElementById('qr-input-solicitud');
                if (inputSolicitud) {
                    inputSolicitud.value = '';
                    inputSolicitud.focus();
                }
                
                // Actualizar el estado del QR
                document.getElementById('qr-status-solicitud').innerHTML = 'Esperando escaneo del usuario...';
            }, 300);
        }

        // Función para procesar el formulario de registro
        async function procesarRegistroUsuario(event) {
            event.preventDefault();
            
            const formData = new FormData(event.target);
            const datosUsuario = {
                run: usuarioNoRegistrado.run,
                nombre: formData.get('nombre'),
                email: formData.get('email'),
                telefono: formData.get('telefono'),
                modulos_utilizacion: parseInt(formData.get('modulos_utilizacion'))
            };

            try {
                const resultado = await registrarUsuarioNoRegistrado(datosUsuario);
                
                if (resultado && resultado.success) {
                    Swal.fire({
                        title: '¡Usuario registrado exitosamente!',
                        text: 'El usuario ha sido registrado y puede continuar con la solicitud.',
                        icon: 'success',
                        confirmButtonText: 'Continuar'
                    }).then(() => {
                        // Cerrar modal de registro
                        window.dispatchEvent(new CustomEvent('close-modal', {
                            detail: 'registro-usuario'
                        }));
                        
                        // Continuar con el flujo original según el modo de operación
                        if (modoOperacionActual === 'solicitud') {
                            // Volver al modal de solicitud y continuar
                            setTimeout(() => {
                                window.dispatchEvent(new CustomEvent('open-modal', {
                                    detail: 'solicitar-llaves'
                                }));
                                
                                // Actualizar estado para continuar con el espacio
                                esperandoUsuarioSolicitud = false;
                                usuarioEscaneadoSolicitud = datosUsuario.run;
                                
                                // Actualizar el estado del QR
                                document.getElementById('qr-status-solicitud').innerHTML = 'Usuario registrado. Escanee el espacio para solicitar.';
                                
                                // Configurar el input de solicitud
                                const inputSolicitud = document.getElementById('qr-input-solicitud');
                                if (inputSolicitud) {
                                    inputSolicitud.value = '';
                                    inputSolicitud.focus();
                                }
                            }, 300);
                        }
                        
                        // Resetear variables
                        usuarioNoRegistrado = null;
                        espacioPendiente = null;
                        modoOperacionActual = null;
                    });
                } else {
                    Swal.fire('Error', resultado?.mensaje || 'Error al registrar usuario', 'error');
                }
            } catch (error) {
                console.error('Error al procesar registro:', error);
                Swal.fire('Error', 'Error al procesar el registro', 'error');
            }
        }

        // ========================================
        // FIN DEL SCRIPT - CÓDIGO LIMPIO Y UNIFICADO
        // ========================================

        // ========================================
        // FUNCIONES PARA CÁMARA Y PERMISOS
        // ========================================
        

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
        
              // Asegurar que indicators sea siempre un array
        if (!state.indicators || !Array.isArray(state.indicators)) {
            state.indicators = [];
        }
        if (!state.originalCoordinates || !Array.isArray(state.originalCoordinates)) {
            state.originalCoordinates = [];
        }

        // Log para debuggear la inicialización de indicators
        console.log('Estado inicial de indicators:', state.indicators);
        console.log('Tipo de indicators:', typeof state.indicators);
        console.log('Es array:', Array.isArray(state.indicators));

        // ========================================

        // Función para resetear el estado de devolución QR
        function resetearDevolucionQR() {
            const inputDevolucion = document.getElementById('qr-input-devolucion');
            if (inputDevolucion) {
                inputDevolucion.value = '';
            }
            resetearEstadoDevolucion();
        }

        // ========================================

        // ========================================
        // FUNCIONES PARA EL FLUJO DE SOLICITUD
        // ========================================
        
        // Función para resetear el estado de solicitud
        function resetearEstadoSolicitud() {
            esperandoUsuarioSolicitud = true;
            usuarioEscaneadoSolicitud = null;
            espacioEscaneadoSolicitud = null;
            bufferQRSolicitud = '';
        }

        // Función para iniciar el proceso de solicitud
        function iniciarSolicitud() {
            // Cerrar el modal actual
            window.dispatchEvent(new CustomEvent('close-modal', {
                detail: 'data-space'
            }));
            
            // Abrir el modal de solicitud
            setTimeout(() => {
                window.dispatchEvent(new CustomEvent('open-modal', {
                    detail: 'solicitar-llaves'
                }));
                
                // Resetear estado de solicitud
                resetearEstadoSolicitud();
                
                // Configurar el input de solicitud
                const inputSolicitud = document.getElementById('qr-input-solicitud');
                if (inputSolicitud) {
                    inputSolicitud.value = '';
                    inputSolicitud.focus();
                }
                
                // Actualizar el estado del QR
                document.getElementById('qr-status-solicitud').innerHTML = 'Esperando escaneo del usuario...';
            }, 300);
        }

        // Función para manejar el escaneo de solicitud
        async function handleScanSolicitud(event) {
            if (event.key === 'Enter') {
                if (esperandoUsuarioSolicitud) {
                    // Procesar QR de usuario para solicitud (formato: RUN¿12345678')
                    const match = bufferQRSolicitud.match(/RUN¿(\d+)/);
                    if (match) {
                        usuarioEscaneadoSolicitud = match[1];
                        const usuarioInfo = await verificarUsuario(usuarioEscaneadoSolicitud);

                        if (usuarioInfo && usuarioInfo.verificado) {
                            if (usuarioInfo.tipo_usuario === 'registrado') {
                                document.getElementById('qr-status-solicitud').innerHTML = 'Usuario registrado verificado. Escanee el espacio para solicitar.';
                            } else if (usuarioInfo.tipo_usuario === 'no_registrado') {
                                document.getElementById('qr-status-solicitud').innerHTML = 'Usuario no registrado verificado. Escanee el espacio para solicitar.';
                            }
                            esperandoUsuarioSolicitud = false;
                        } else if (usuarioInfo && usuarioInfo.usuario_no_registrado && usuarioInfo.tipo_usuario === 'nuevo') {
                            // Usuario completamente nuevo - mostrar modal de registro
                            usuarioNoRegistrado = {
                                run: usuarioInfo.run_escaneado
                            };
                            modoOperacionActual = 'solicitud';
                            mostrarModalRegistroUsuario(usuarioInfo.run_escaneado);
                        } else {
                            Swal.fire('Error', usuarioInfo?.mensaje || 'Error de verificación', 'error');
                            document.getElementById('qr-status-solicitud').innerHTML = usuarioInfo?.mensaje || 'Error de verificación';
                        }
                    } else {
                        Swal.fire('Error', 'RUN inválido', 'error');
                        document.getElementById('qr-status-solicitud').innerHTML = 'RUN inválido';
                    }
                } else {
                    // Procesar QR de espacio para solicitud (formato: TH'L01)
                    const espacioProcesado = bufferQRSolicitud.replace(/'/g, '-');
                    const espacioInfo = await verificarEspacio(espacioProcesado);

                    if (espacioInfo?.verificado) {
                        espacioEscaneadoSolicitud = espacioProcesado;
                        if (espacioInfo.disponible) {
                            // Procesar solicitud directamente, sin confirmación
                            const reserva = await crearReserva(usuarioEscaneadoSolicitud, espacioProcesado);
                            if (reserva?.success) {
                                Swal.fire('¡Solicitud exitosa!', 'Las llaves han sido asignadas correctamente.', 'success');
                                document.getElementById('qr-status-solicitud').innerHTML = 'Solicitud exitosa';
                                // Actualizar el color del indicador a 'Ocupado' (rojo)
                                const block = state.indicators.find(b => b.id === espacioProcesado);
                                if (block) {
                                    block.estado = '#FF0000'; // Rojo
                                    const originalBlock = state.originalCoordinates.find(b => b.id === espacioProcesado);
                                    if (originalBlock) {
                                        originalBlock.estado = '#FF0000';
                                    }
                                    state.lastLocalChange = Date.now();
                                    setTimeout(() => {
                                        drawIndicators();
                                    }, 100);
                                }
                                setTimeout(() => {
                                    window.dispatchEvent(new CustomEvent('close-modal', {
                                        detail: 'solicitar-llaves'
                                    }));
                                }, 2000);
                            } else {
                                // Manejar diferentes tipos de errores con mensajes específicos
                                let titulo = 'Error';
                                let mensaje = reserva?.mensaje || 'Error al procesar la solicitud';
                                let icono = 'error';

                                if (reserva?.tipo) {
                                    switch (reserva.tipo) {
                                        case 'reserva_activa':
                                            titulo = 'Reserva Activa';
                                            icono = 'warning';
                                            if (reserva.reserva_activa) {
                                                mensaje = `Ya tienes una reserva activa en el espacio '${reserva.reserva_activa.espacio}' desde las ${reserva.reserva_activa.hora_inicio}. Debes finalizarla antes de solicitar una nueva.`;
                                            }
                                            break;
                                        case 'reserva_diaria':
                                            titulo = 'Límite Diario';
                                            icono = 'warning';
                                            break;
                                        case 'limite_excedido':
                                            titulo = 'Límite Excedido';
                                            icono = 'warning';
                                            break;
                                        case 'mismo_espacio':
                                            titulo = 'Espacio Ya Reservado';
                                            icono = 'warning';
                                            break;
                                        case 'devolucion':
                                            titulo = 'Espacio Ocupado';
                                            icono = 'question';
                                            mensaje = `¿Desea devolver las llaves del espacio '${reserva.espacio}'?`;
                                            break;
                                    }
                                }
                                Swal.fire(titulo, mensaje, icono);
                                document.getElementById('qr-status-solicitud').innerHTML = mensaje;
                            }
                        } else {
                            Swal.fire('Error', 'El espacio no está disponible para solicitar.', 'error');
                            document.getElementById('qr-status-solicitud').innerHTML = 'Espacio no disponible';
                        }
                    } else {
                        Swal.fire('Error', espacioInfo?.mensaje || 'Error al verificar espacio', 'error');
                        document.getElementById('qr-status-solicitud').innerHTML = espacioInfo?.mensaje || 'Error al verificar espacio';
                    }
                    resetearEstadoSolicitud();
                }
                bufferQRSolicitud = '';
                event.target.value = '';
            } else if (event.key.length === 1) {
                bufferQRSolicitud += event.key;
            }
        }

        // Función para resetear el estado de solicitud QR
        function resetearSolicitudQR() {
            const inputSolicitud = document.getElementById('qr-input-solicitud');
            if (inputSolicitud) {
                inputSolicitud.value = '';
            }
            resetearEstadoSolicitud();
        }

        // ========================================

        // ========================================
        // FUNCIÓN DE PRUEBA PARA FORZAR COLOR ROJO
        // ========================================
        // function testRedColor() {
        //     console.log('=== PRUEBA DE COLOR ROJO ===');
        //     console.log('Indicadores antes del cambio:', state.indicators);
        //     // Forzar color rojo en todos los indicadores
        //     state.indicators.forEach(indicator => {
        //         console.log(`Cambiando indicador ${indicator.id} de "${indicator.estado}" a "#FF0000"`);
        //         indicator.estado = '#FF0000';
        //     });
        //     // Actualizar también las coordenadas originales
        //     state.originalCoordinates.forEach(indicator => {
        //         indicator.estado = '#FF0000';
        //     });
        //     // Registrar el cambio local
        //     state.lastLocalChange = Date.now();
        //     console.log('Indicadores después del cambio:', state.indicators);
        //     // Forzar la redibujada
        //     drawIndicators();
        //     console.log('=== FIN PRUEBA DE COLOR ROJO ===');
        //     // Mostrar alerta
        //     Swal.fire('Prueba de Color', 'Todos los indicadores han sido forzados a color rojo. Revisa la consola para más detalles.', 'info');
        // }

        // ========================================
        // FUNCIÓN PARA MOSTRAR RESUMEN DE ESTADOS
        // ========================================
        function mostrarResumenEstados() {
            if (!state.indicators || !Array.isArray(state.indicators)) {
                console.log('⚠️ No hay indicadores disponibles para mostrar resumen');
                return;
            }

            const resumen = {
                total: state.indicators.length,
                disponibles: 0,
                ocupados: 0,
                reservados: 0,
                proximos: 0,
                sinEstado: 0
            };

            state.indicators.forEach(indicator => {
                const estado = indicator.estado;
                if (estado === '#059669' || estado === 'disponible') {
                    resumen.disponibles++;
                } else if (estado === '#FF0000' || estado === 'ocupado') {
                    resumen.ocupados++;
                } else if (estado === '#FFA500' || estado === 'reservado') {
                    resumen.reservados++;
                } else if (estado === '#3B82F6' || estado === 'proximo') {
                    resumen.proximos++;
                } else {
                    resumen.sinEstado++;
                }
            });

            console.log('📊 RESUMEN DE ESTADOS DE ESPACIOS:');
            console.log(`📈 Total de espacios: ${resumen.total}`);
            console.log(`🟢 Disponibles: ${resumen.disponibles}`);
            console.log(`🔴 Ocupados: ${resumen.ocupados}`);
            console.log(`🟠 Reservados: ${resumen.reservados}`);
            console.log(`🔵 Próximos: ${resumen.proximos}`);
            console.log(`⚪ Sin estado: ${resumen.sinEstado}`);
            console.log('📊 FIN RESUMEN');

            return resumen;
        }

        // ========================================
        // FUNCIÓN PARA MOSTRAR ESTADO DE ACTUALIZACIÓN
        // ========================================

        // Obtener módulo actual
        const moduloActual = moduloActualNum(); // o determinarModulo(horaActual)
        const moduloParaHora = moduloActualNum('08:30:00'); // o determinarModulo('08:30:00')

   
        // ========================================
        // LÓGICA PARA RESERVA POR MÓDULOS
        // ========================================
        let maxModulosDisponibles = 1;
        let espacioParaReserva = null;
        let runParaReserva = null;

        // Función para calcular módulos disponibles consecutivos
        async function calcularModulosDisponibles(idEspacio) {
            try {
                // Obtener hora y día actual
                const ahora = new Date();
                const horaActual = ahora.toLocaleTimeString('es-ES', {
                    hour: '2-digit',
                    minute: '2-digit',
                    second: '2-digit'
                });
                const diaActual = obtenerDiaActual();
                
                const response = await fetch(`/api/espacio/${idEspacio}/modulos-disponibles?hora_actual=${horaActual}&dia_actual=${diaActual}`);
                
                if (response.ok) {
                    const data = await response.json();
                    console.log('Respuesta del endpoint modulos-disponibles:', data);
                    
                    if (data.success) {
                        return data.max_modulos || 1;
                    } else {
                        console.warn('No hay módulos disponibles:', data.mensaje);
                        return 1;
                    }
                } else {
                    console.error('Error en la respuesta del servidor:', response.status);
                    return 1;
                }
            } catch (error) {
                console.error('Error al calcular módulos disponibles:', error);
                return 1;
            }
        }

        // Mostrar modal de módulos
        async function mostrarModalSeleccionarModulos(idEspacio, run) {
            maxModulosDisponibles = await calcularModulosDisponibles(idEspacio);
            document.getElementById('max-modulos-disponibles').textContent = maxModulosDisponibles;
            const inputModulos = document.getElementById('input-cantidad-modulos');
            inputModulos.max = maxModulosDisponibles;
            inputModulos.value = 1;
            espacioParaReserva = idEspacio;
            runParaReserva = run;
            window.dispatchEvent(new CustomEvent('open-modal', { detail: 'seleccionar-modulos' }));
        }

        // Confirmar reserva con módulos
        document.addEventListener('DOMContentLoaded', function () {
            const btnConfirmarModulos = document.getElementById('btn-confirmar-modulos');
            if (btnConfirmarModulos) {
                btnConfirmarModulos.addEventListener('click', async function () {
                    const cantidad = parseInt(document.getElementById('input-cantidad-modulos').value);
                    if (!espacioParaReserva || !runParaReserva) return;
                    // Llama a crearReserva con la cantidad de módulos
                    const response = await fetch('/api/crear-reserva', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        },
                        body: JSON.stringify({
                            run: runParaReserva,
                            id_espacio: espacioParaReserva,
                            modulos: cantidad
                        })
                    });
                    const data = await response.json();
                    if (data.success) {
                        Swal.fire('¡Reserva exitosa!', data.mensaje, 'success');
                        window.dispatchEvent(new CustomEvent('close-modal', { detail: 'seleccionar-modulos' }));
                    } else {
                        Swal.fire('Error', data.mensaje || 'No se pudo reservar', 'error');
                    }
                });
            }
        });
    </script>
</x-show-layout>