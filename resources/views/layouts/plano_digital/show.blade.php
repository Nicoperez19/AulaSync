<x-show-layout>
    <style>
        @keyframes parpadeo {
            0% {
                opacity: 0.3;
            }

            50% {
                opacity: 1;
            }

            100% {
                opacity: 0.3;
            }
        }

        .parpadeo {
            animation: parpadeo 2s ease-in-out infinite;
        }
    </style>
    <div class="flex h-screen overflow-hidden">
        <aside
            class="fixed top-0 left-0 z-40 flex flex-col justify-between w-56 h-screen pt-2 text-base border-r border-gray-200 md:w-56 bg-light-cloud-blue dark:border-gray-700 md:text-sm sm:text-xs">

            <div class="flex flex-col items-center gap-2 md:gap-1">
                <a href="{{ auth()->user()->hasRole('Usuario') ? route('espacios.show') : route('dashboard') }}" class="mb-1">
                    <x-application-logo-navbar class="w-10 h-10 md:w-8 md:h-8 sm:w-6 sm:h-6" />
                </a>
                
            </div>

            <div class="flex flex-col items-center justify-center w-full max-w-md p-1 mx-auto ">
                <div class="w-full mt-6">
                    <div class="p-4 text-white bg-red-700 rounded ">
                        <div class="flex items-center justify-between pb-4">
                            <div
                                class="flex items-center gap-1 bg-red-700 rounded">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <span id="hora-actual" class="text-2xl font-semibold">--:--:--</span>
                            </div>
                        </div>

                        <div class="py-1">
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

                <!-- Tarjeta de QR y Usuario -->
                <div class="w-full mt-20">
                    <div class="mt-4 mb-4 text-white bg-light-cloud-blue">
                        <div class="flex items-center gap-3 p-3 mb-3 rounded-md bg-red-500/80">

                            <div class="bg-red-400 rounded shadow-[0_0_10px_2px_rgba(255,255,255,0.4)]">
                                <svg xmlns="http://www.w3.org/2000/svg" class="text-white w-7 h-7" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 4v1m6 11h2m-6 0h-2v4m0-11v2m0 5h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 20h14a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                            </div>
                            <div>
                                <span id="qr-status" class="block text-sm font-semibold parpadeo">Esperando</span>
                                <span class="text-xs text-white/80 parpadeo">Escanea el código QR</span>
                            </div>
                        </div>

                        <hr class="pb-4 my-2 border-white/30">


                        <!-- Información del usuario (oculta inicialmente) -->
                        <div id="info-usuario" class="hidden px-4 py-3 space-y-2 text-sm bg-white rounded-lg shadow-md">
                            <h3 class="mb-2 text-xs font-semibold tracking-wide text-gray-800 uppercase">Información de
                            usuario</h3>

                            <div class="flex items-center gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-gray-600" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                                <span class="font-bold text-gray-800">RUN:</span>
                                <span id="run-escaneado" class="ml-auto text-gray-700">--</span>
                            </div>

                            <div class="flex items-center gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-gray-600" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                                <span class="font-bold text-gray-800">Usuario:</span>
                                <span id="nombre-usuario" class="ml-auto text-gray-700">--</span>
                            </div>
                        </div>

                        <input type="text" id="qr-input"
                            class="absolute w-full px-1 py-1 text-transparent bg-transparent border-0 opacity-0 focus:outline-none focus:border-0 focus:ring-0"
                            autocomplete="off" autocorrect="off" autocapitalize="off" spellcheck="false" autofocus>



                    </div>
                </div>
            </div>

            <!-- Leyenda abajo del todo -->
            <div class="flex flex-col items-center justify-center w-full max-w-md p-1 mx-auto">
                <div class="w-full mt-6">
                    <div class="p-4 text-white bg-red-700 rounded">
                        <h3 class="flex items-center justify-center gap-1 mb-2 text-sm font-semibold text-center text-white md:text-xs">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 md:w-3 md:h-3" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
                                <circle cx="12" cy="10" r="3"></circle>
                            </svg>
                            LEYENDA DE ESTADO
                        </h3>
                        <div class="flex flex-col items-start gap-1">
                            <div class="flex items-center w-full gap-1">
                                <div class="w-3 h-3 bg-red-500 border-2 border-white rounded-full"></div>
                                <span class="flex-1 text-xs text-white">Ocupado</span>
                            </div>
                            <div class="flex items-center w-full gap-1">
                                <div class="w-3 h-3 bg-orange-500 border-2 border-white rounded-full"></div>
                                <span class="flex-1 text-xs text-white">Reservado</span>
                            </div>
                            <div class="flex items-center w-full gap-1">
                                <div class="w-3 h-3 bg-blue-500 border-2 border-white rounded-full"></div>
                                <span class="flex-1 text-xs text-white">Próximo</span>
                            </div>
                            <div class="flex items-center w-full gap-1">
                                <div class="w-3 h-3 bg-purple-600 border-2 border-white rounded-full"></div>
                                <span class="flex-1 text-xs text-white">Clase sin asistentes</span>
                            </div>
                            <div class="flex items-center w-full gap-1">
                                <div class="w-3 h-3 bg-green-500 border-2 border-white rounded-full"></div>
                                <span class="flex-1 text-xs text-white">Disponible</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Enlace Volver -->
                <div class="w-full mt-4">
                    <a href="{{ auth()->user()->hasRole('Usuario') ? route('espacios.show') : route('dashboard') }}"
                       class="flex items-center justify-center gap-2 px-4 py-2 text-sm font-medium text-white bg-gray-600 rounded-lg hover:bg-gray-700 transition-colors duration-200 shadow-md"
                       onclick="qrInputManager.setActiveInput('main')">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="size-5">
                            <path d="M11.47 3.841a.75.75 0 0 1 1.06 0l8.69 8.69a.75.75 0 1 0 1.06-1.061l-8.689-8.69a2.25 2.25 0 0 0-3.182 0l-8.69 8.69a.75.75 0 1 0 1.061 1.06l8.69-8.689Z" />
                            <path d="m12 5.432 8.159 8.159c.03.03.06.058.091.086v6.198c0 1.035-.84 1.875-1.875 1.875H15a.75.75 0 0 1-.75-.75v-4.5a.75.75 0 0 0-.75-.75h-3a.75.75 0 0 0-.75.75V21a.75.75 0 0 1-.75.75H5.625a1.875 1.875 0 0 1-1.875-1.875v-6.198a2.29 2.29 0 0 0 .091-.086L12 5.432Z" />
                        </svg>
                        Volver
                    </a>
                </div>
            </div>
        </aside>

        <div class="flex-1 h-screen pt-4 pb-[2rem] ml-52 overflow-hidden">
            <div class="flex flex-col h-full">

                <div class="flex flex-col flex-1 min-h-0">
                    <div class="flex-1 bg-white shadow-md dark:bg-dark-eval-0">
                        <ul class="flex" id="pills-tab" role="tablist">
                            @foreach ($pisos as $piso)
                                @if ($piso['id_mapa'])
                                    <li role="presentation">
                                        <a href="{{ route('plano.show', $piso['id_mapa']) }}"
                                            class="px-4 py-2 text-sm font-semibold transition-all duration-300 rounded-t-xl border border-b-0
                                            {{ $piso['id_mapa'] === $mapa->id_mapa
                                                ? 'bg-light-cloud-blue text-white border-light-cloud-blue'
                                    : 'bg-white text-gray-700 border-gray-300 hover:bg-gray-100 hover:text-light-cloud-blue' }}" role="tab"
                                            aria-selected="{{ $piso['id_mapa'] === $mapa->id_mapa ? 'true' : 'false' }}">
                                            Piso {{ $piso['numero'] }}
                                        </a>
                                    </li>
                                @endif
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


                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para mostrar información del espacio -->
    <div id="modal-espacio-info" class="fixed inset-0 z-[9999] flex items-center justify-center bg-black bg-opacity-50 hidden">
        <div class="flex flex-col w-full max-w-4xl max-h-screen mx-2 overflow-hidden bg-white rounded-lg shadow-lg md:mx-8">
            <!-- Encabezado con diseño tipo banner -->
            <div class="relative flex flex-col gap-6 p-8 bg-gradient-to-r bg-light-cloud-blue md:flex-row md:items-center md:justify-between">
                <!-- Círculos decorativos -->
                <span class="absolute top-0 left-0 w-32 h-32 -translate-x-1/2 -translate-y-1/2 bg-white rounded-full pointer-events-none bg-opacity-10"></span>
                <span class="absolute top-0 right-0 w-32 h-32 translate-x-1/2 -translate-y-1/2 bg-white rounded-full pointer-events-none bg-opacity-10"></span>

                <div class="flex items-center flex-1 min-w-0 gap-5">
                    <div class="flex flex-col items-center justify-center flex-shrink-0">
                        <div class="p-4 mb-2 bg-white rounded-full bg-opacity-20">
                            <i class="text-3xl text-white fa-solid fa-building"></i>
                        </div>
                    </div>
                    <div class="flex flex-col min-w-0">
                        <h1 id="modalTitulo" class="text-3xl font-bold text-white truncate">Información del Espacio</h1>
                        <div class="flex items-center gap-2 mt-1">
                            <span id="modalSubtitulo" class="text-lg truncate text-white/80">Estado y detalles</span>
                        </div>
                    </div>
                </div>

                <div class="flex items-center self-start flex-shrink-0 gap-3 md:self-center">
                    <button onclick="cerrarModalEspacio(); qrInputManager.setActiveInput('main')"
                        class="ml-2 text-3xl font-bold text-white hover:text-gray-200 transition-colors duration-200 cursor-pointer"
                        title="Cerrar modal (Esc)"
                        aria-label="Cerrar modal">&times;</button>
                </div>
            </div>

            <!-- Contenido del modal -->
            <div class="p-6 bg-gray-50 overflow-y-auto max-h-[70vh] flex-1">
                <!-- Estado del espacio -->
                <div class="p-6 mb-6 bg-white border-l-4 border-blue-500 shadow-sm rounded-xl">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-xl font-semibold text-gray-800">
                            <i class="mr-2 text-blue-500 fas fa-info-circle"></i>
                            Estado Actual
                        </h3>
                        <span id="estadoPill" class="inline-flex items-center px-4 py-2 text-sm font-bold border rounded-full">
                            <span id="estadoIcon" class="w-3 h-3 mr-2 rounded-full"></span>
                            <span id="modalEstado" class="font-semibold"></span>
                        </span>
                    </div>
                    <div id="estadoDetalles" class="text-sm text-gray-600">
                        <!-- Información adicional del estado se insertará aquí -->
                    </div>
                    
                    <div class="mt-4 flex justify-end">
                        <button class="btn-desocupar group relative px-4 py-2 text-sm font-semibold text-white bg-red-600 rounded hover:bg-red-700 hidden transition-all duration-200" data-tipo="espacio" title="Desocupar sala">
                            <div class="flex items-center space-x-2">
                                <x-heroicon-s-logout class="w-4 h-4" />
                                <span>Desocupar</span>
                            </div>
                            <!-- Tooltip -->
                            <div class="absolute bottom-full left-1/2 transform -translate-x-1/2 mb-2 px-2 py-1 text-xs text-white bg-gray-900 rounded opacity-0 group-hover:opacity-100 transition-opacity duration-200 pointer-events-none whitespace-nowrap">
                                Desocupar sala
                                <div class="absolute top-full left-1/2 transform -translate-x-1/2 w-0 h-0 border-l-4 border-r-4 border-t-4 border-transparent border-t-gray-900"></div>
                            </div>
                        </button>
                    </div>
                </div>

                <!-- Información del ocupante actual / último ocupante -->
                <div id="ocupanteContainer" class="p-6 mb-6 bg-white border-l-4 border-green-500 shadow-sm rounded-xl" style="display: none;">
                    <h3 id="ocupanteTitulo" class="mb-4 text-xl font-semibold text-gray-800">
                        <i class="mr-2 text-green-500 fas fa-user"></i>
                        Ocupante Actual
                    </h3>
                    <div id="ocupanteInfo" class="space-y-3">
                        <!-- La información se insertará dinámicamente -->
                    </div>
                </div>

                <!-- Información de la clase actual -->
                <div id="claseActualContainer" class="p-6 mb-6 bg-white border-l-4 border-orange-500 shadow-sm rounded-xl" style="display: none;">
                    <h3 class="mb-4 text-xl font-semibold text-gray-800">
                        <i class="mr-2 text-orange-500 fas fa-chalkboard-teacher"></i>
                        Clase Actual
                    </h3>
                    <div id="claseActualInfo" class="space-y-3">
                        <!-- La información se insertará dinámicamente -->
                    </div>
                </div>

                <!-- Próxima clase programada -->
                <div id="proximaClaseContainer" class="p-6 mb-6 bg-white border-l-4 border-purple-500 shadow-sm rounded-xl" style="display: none;">
                    <h3 class="mb-4 text-xl font-semibold text-gray-800">
                        <i class="mr-2 text-purple-500 fas fa-clock"></i>
                        Próxima Clase
                    </h3>
                    <div id="proximaClaseInfo" class="space-y-3">
                        <!-- La información se insertará dinámicamente -->
                    </div>
                    <div class="mt-4 flex justify-end">
                        <button class="btn-desocupar group relative px-4 py-2 text-sm font-semibold text-white bg-red-600 rounded hover:bg-red-700 hidden transition-all duration-200" data-tipo="reserva" title="Desocupar reserva">
                            <div class="flex items-center space-x-2">
                                <x-heroicon-s-logout class="w-4 h-4" />
                                <span>Desocupar reserva</span>
                            </div>
                            <!-- Tooltip -->
                            <div class="absolute bottom-full left-1/2 transform -translate-x-1/2 mb-2 px-2 py-1 text-xs text-white bg-gray-900 rounded opacity-0 group-hover:opacity-100 transition-opacity duration-200 pointer-events-none whitespace-nowrap">
                                Desocupar reserva
                                <div class="absolute top-full left-1/2 transform -translate-x-1/2 w-0 h-0 border-l-4 border-r-4 border-t-4 border-transparent border-t-gray-900"></div>
                            </div>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

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
                <svg xmlns="http://www.w3.org/2000/svg" class="w-20 h-20 mb-4 text-black" fill="none"
                    viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 4v1m6 11h2m-6 0h-2v4m0-11v2m0 5h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 20h14a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
                <span id="qr-status-devolucion" class="mb-2 text-base text-black">Esperando escaneo del
                    usuario...</span>
                <span class="text-sm text-black">Escanee el código QR del usuario y luego del espacio</span>
            </div>
            <input type="text" id="qr-input-devolucion"
                class="absolute w-full px-1 py-1 border rounded opacity-0 focus:outline-none focus:ring-2 focus:ring-blue-500"
                placeholder="Escanea un código QR" autocomplete="off" autofocus>
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
                            placeholder="Escanea un código QR" autocomplete="off" autofocus>
                    </div>
                </div>
            </div>
        </div>
    </x-modal>



    <!-- Modal para confirmar si hubo asistentes -->
    <div id="modal-confirmar-asistentes" class="fixed inset-0 z-[9999] flex items-center justify-center bg-black bg-opacity-50 hidden">
        <div class="flex flex-col w-full max-w-md mx-2 overflow-hidden bg-white rounded-lg shadow-lg md:mx-8">
            <!-- Encabezado -->
            <div class="p-6 bg-gradient-to-r from-blue-500 to-blue-600">
                <div class="flex items-center justify-center mb-2">
                    <svg class="w-12 h-12 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                </div>
                <h3 class="text-xl font-bold text-center text-white">Devolución anticipada</h3>
                <p class="mt-2 text-sm text-center text-blue-100">Clase finalizada antes del primer módulo</p>
            </div>

            <!-- Contenido -->
            <div class="p-6 bg-gray-50">
                <div class="mb-6 text-center">
                    <p class="mb-2 text-base font-medium text-gray-800">¿Hubo asistentes en la clase?</p>
                    <p class="text-sm text-gray-600">Esta información ayuda a mejorar las estadísticas de uso</p>
                </div>

                <!-- Información de la clase -->
                <div class="p-4 mb-6 bg-white border-l-4 border-blue-500 rounded-lg shadow-sm">
                    <div class="space-y-2 text-sm">
                        <div class="flex justify-between">
                            <span class="font-medium text-gray-600">Espacio:</span>
                            <span id="asistentes-espacio" class="font-semibold text-gray-800"></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="font-medium text-gray-600">Asignatura:</span>
                            <span id="asistentes-asignatura" class="font-semibold text-gray-800"></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="font-medium text-gray-600">Módulos programados:</span>
                            <span id="asistentes-modulos" class="font-semibold text-gray-800"></span>
                        </div>
                    </div>
                </div>

                <!-- Botones de acción -->
                <div class="grid grid-cols-2 gap-3">
                    <button id="btn-sin-asistentes"
                        class="flex items-center justify-center px-6 py-3 text-white transition-colors duration-200 bg-red-600 rounded-lg hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                        <span class="font-semibold">Sin asistentes</span>
                    </button>
                    <button id="btn-con-asistentes"
                        class="flex items-center justify-center px-6 py-3 text-white transition-colors duration-200 bg-green-600 rounded-lg hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        <span class="font-semibold">Con asistentes</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para seleccionar cantidad de módulos -->
    <div id="modal-seleccionar-modulos" class="fixed inset-0 z-[9999] flex items-center justify-center bg-black bg-opacity-50 hidden">
        <div class="flex flex-col w-full max-w-2xl max-h-screen mx-2 overflow-hidden bg-white rounded-lg shadow-lg md:mx-8">
            <!-- Encabezado azul con diseño tipo banner -->
            <div class="relative flex flex-col gap-6 p-8 bg-light-cloud-blue md:flex-row md:items-center md:justify-between">
                <!-- Círculos decorativos -->
                <span class="absolute top-0 left-0 w-32 h-32 -translate-x-1/2 -translate-y-1/2 bg-white rounded-full pointer-events-none bg-opacity-10"></span>
                <span class="absolute top-0 right-0 w-32 h-32 translate-x-1/2 -translate-y-1/2 bg-white rounded-full pointer-events-none bg-opacity-10"></span>

                <div class="flex items-center flex-1 min-w-0 gap-5">
                    <div class="flex flex-col items-center justify-center flex-shrink-0">
                        <div class="p-4 mb-2 bg-white rounded-full bg-opacity-20">
                            <i class="text-3xl text-white fa-solid fa-clock"></i>
                        </div>
                    </div>
                    <div class="flex flex-col min-w-0">
                        <h1 class="text-3xl font-bold text-white truncate">Seleccionar Módulos</h1>
                        <div class="flex items-center gap-2 mt-1">
                            <span class="text-lg truncate text-white/80">Reserva de Espacio</span>
                        </div>
                    </div>
                </div>

                <div class="flex items-center self-start flex-shrink-0 gap-3 md:self-center">
                    <button onclick="cerrarModalModulos()"
                        class="ml-2 text-3xl font-bold text-white hover:text-gray-200 transition-colors duration-200 cursor-pointer"
                        title="Cerrar modal (Esc)"
                        aria-label="Cerrar modal">&times;</button>
                </div>
            </div>

            <!-- Contenido del modal -->
            <div class="p-6 bg-gray-50 overflow-y-auto max-h-[70vh] flex-1">
              <!-- Selección de módulos -->
                <div class="p-4 mb-6 bg-white rounded-lg shadow-sm">
                    <h3 class="mb-4 text-lg font-semibold text-gray-800">Configuración de Reserva</h3>
                    <div class="mb-4 text-center">
                        <p class="mb-4 text-base text-gray-700">¿Por cuántos módulos desea reservar?</p>
                        <div class="flex items-center justify-center gap-4">
                            <input type="number" id="input-cantidad-modulos" min="1" max="1" value="1"
                                class="w-24 px-4 py-3 text-xl font-semibold text-center border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <span class="text-sm text-gray-600">
                                de <span id="max-modulos-disponibles" class="font-semibold text-blue-600">1</span> disponibles
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Información detallada de módulos -->
                <div id="info-modulos-disponibles" class="mb-6"></div>

                <!-- Botones de acción -->
                <div class="flex justify-end gap-3 pt-4 border-t border-gray-200">
                    <x-button id="btn-confirmar-modulos" variant='add'>
                        Confirmar Reserva
                    </x-button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para registro de solicitante -->
    <x-modal name="registro-solicitante" :show="false" @close-modal.window="handleClose($event, 'registro-solicitante')">
        @slot('title')
            <div id="modalHeader"
                class="relative flex flex-col gap-6 p-8 bg-red-700 md:flex-row md:items-center md:justify-between">
                <!-- Círculos decorativos -->
                <span
                    class="absolute top-0 left-0 w-32 h-32 -translate-x-1/2 -translate-y-1/2 bg-white rounded-full pointer-events-none bg-opacity-10"></span>
                <span
                    class="absolute top-0 right-0 w-32 h-32 translate-x-1/2 -translate-y-1/2 bg-white rounded-full pointer-events-none bg-opacity-10"></span>

                <div class="flex items-center flex-1 min-w-0 gap-5">
                    <div class="flex flex-col items-center justify-center flex-shrink-0">
                        <div class="p-4 mb-2 bg-white rounded-full bg-opacity-20">
                            <i class="text-3xl text-white fa-solid fa-user-plus"></i>
                        </div>
                    </div>
                    <div class="flex flex-col min-w-0">
                        <h1 class="text-3xl font-bold text-white truncate">Registro de Solicitante</h1>
                        <div class="flex items-center gap-2 mt-1">
                            <span class="text-lg truncate text-white/80">Usuario No Registrado</span>
                            <span class="text-lg text-white/80">•</span>
                            <span class="text-lg font-semibold text-white/80">2025</span>
                        </div>
                    </div>
                </div>

                <div class="flex items-center self-start flex-shrink-0 gap-3 md:self-center">
                    <button onclick="cerrarModalRegistroSolicitante(); qrInputManager.setActiveInput('main')"
                        class="ml-2 text-3xl font-bold text-white hover:text-gray-200 transition-colors duration-200 cursor-pointer"
                        title="Cerrar modal (Esc)"
                        aria-label="Cerrar modal">&times;</button>
                </div>
            </div>
        @endslot
        <div class="p-6">
            <div class="space-y-4">
                <div class="text-center">
                    <h3 class="text-lg font-medium text-gray-900">Usuario No Registrado</h3>
                    <p class="mt-2 text-sm text-gray-600">
                        El RUN <span id="run-solicitante-no-registrado" class="font-semibold"></span> no está registrado
                        como profesor.
                        Complete los siguientes datos para continuar con la solicitud como solicitante.
                    </p>
                </div>

                <form id="form-registro-solicitante" class="space-y-4">
                    <div>
                        <label for="nombre-solicitante" class="block text-sm font-medium text-gray-700">Nombre Completo
                            *</label>
                        <input type="text" id="nombre-solicitante" name="nombre" required autocomplete="name"
                            class="block w-full px-3 py-2 mt-1 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                            autofocus>
                    </div>

                    <div>
                        <label for="email-solicitante" class="block text-sm font-medium text-gray-700">Correo
                            Electrónico *</label>
                        <input type="email" id="email-solicitante" name="email" required autocomplete="email"
                            class="block w-full px-3 py-2 mt-1 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                    </div>

                    <div>
                        <label for="telefono-solicitante" class="block text-sm font-medium text-gray-700">Teléfono
                            *</label>
                        <div class="flex mt-1">
                            <div class="flex items-center px-3 py-2 bg-gray-100 border border-r-0 border-gray-300 rounded-l-md">
                                <span class="text-sm font-medium text-gray-600">+56</span>
                            </div>
                            <input type="tel" id="telefono-solicitante" name="telefono" required autocomplete="tel"
                                pattern="[0-9]{9}"
                                title="Ingrese un número de teléfono válido (9 dígitos, sin el +56)"
                                placeholder="912345678"
                                maxlength="9"
                                class="flex-1 px-3 py-2 border border-gray-300 rounded-r-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                        </div>
                        <p class="mt-1 text-xs text-gray-500">Formato: 9 dígitos (ej: 912345678). No incluir +56</p>
                    </div>

                    <div>
                        <label for="tipo-solicitante" class="block text-sm font-medium text-gray-700">Tipo de
                            Solicitante *</label>
                        <select id="tipo-solicitante" name="tipo_solicitante" required
                            class="block w-full px-3 py-2 mt-1 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                            <option value="">Seleccione el tipo</option>
                            <option value="estudiante">Estudiante</option>
                            <option value="personal">Personal Administrativo</option>
                            <option value="visitante">Visitante</option>
                            <option value="otro">Otro</option>
                        </select>
                    </div>



                    <div class="flex pt-4 space-x-3">
                        <button type="submit"
                            class="w-full px-4 py-2 text-white bg-blue-600 border border-transparent rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            Registrar y Continuar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </x-modal>

    <script>
        // Escuchar cuando se abra el modal de registro para establecer el foco correcto
        document.addEventListener('open-modal', (event) => {
            if (event.detail === 'registro-solicitante') {
                // Esperar a que el modal esté completamente visible
                setTimeout(() => {
                    const nombreInput = document.getElementById('nombre-solicitante');
                    if (nombreInput) {
                        nombreInput.focus();
                    }
                }, 150);
            }
        });
        // Función para manejar el cierre de modales
        function handleClose(event, modalName) {
            if (event.detail === modalName) {
                // Cerrar el modal específico
                const modal = document.querySelector(`[x-data*="modalComponent"][x-data*="${modalName}"]`);
                if (modal) {
                    const modalInstance = Alpine.$data(modal);
                    if (modalInstance && typeof modalInstance.show !== 'undefined') {
                        modalInstance.show = false;
                    }
                }
            }
        }

        @php
            $mapaIdValue = $mapa->id_mapa ?? 1;
        @endphp

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

        // Variable global para horarios de módulos (se carga desde JSON)
        let horariosModulos = {};

        // Cargar horarios de módulos desde archivo JSON
        async function cargarHorariosModulos() {
            try {
                const response = await fetch('/js/horarios-modulos.json');
                if (!response.ok) {
                    throw new Error('No se pudo cargar el archivo de horarios');
                }
                horariosModulos = await response.json();
                console.log('✅ Horarios de módulos cargados correctamente');
            } catch (error) {
                console.error('❌ Error al cargar horarios de módulos:', error);
                console.log('⚠️ Usando horarios de fallback');
            }
        }

        // SISTEMA GLOBAL DE MANEJO DE AUTOFOCUS PARA TODOS LOS INPUTS QR
        class QRInputManager {
            constructor() {
                this.qrInputs = {
                    main: document.getElementById('qr-input'),
                    devolucion: document.getElementById('qr-input-devolucion'),
                    solicitud: document.getElementById('qr-input-solicitud')
                };
                this.activeInput = null;
                this.modalStates = new Map();
                this.init();
            }

            init() {
                // Configurar estado inicial
                this.setActiveInput('main');
                this.setupEventListeners();
            }

            setActiveInput(inputType) {
                // Desactivar todos los inputs primero
                Object.values(this.qrInputs).forEach(input => {
                    if (input) {
                        input.blur();
                        input.removeAttribute('autofocus');
                    }
                });

                // Activar el input especificado
                const targetInput = this.qrInputs[inputType];
                if (targetInput) {
                    this.activeInput = inputType;
                    targetInput.setAttribute('autofocus', '');
                    setTimeout(() => {
                        targetInput.focus();
                    }, 100);
                }
            }

            desactivarTodosLosInputs() {
                Object.values(this.qrInputs).forEach(input => {
                    if (input) {
                        input.blur();
                        input.removeAttribute('autofocus');
                    }
                });
                this.activeInput = null;
            }

            restaurarInputActivo() {
                if (this.activeInput && this.qrInputs[this.activeInput]) {
                    const input = this.qrInputs[this.activeInput];
                    input.setAttribute('autofocus', '');
                    setTimeout(() => {
                        input.focus();
                    }, 100);
                }
            }

            setupEventListeners() {
                // Event listeners para modales Bootstrap
                document.addEventListener('show.bs.modal', (event) => {
                    this.desactivarTodosLosInputs();
                });

                document.addEventListener('hide.bs.modal', (event) => {
                    this.restaurarInputActivo();
                });

                // Event listeners para modales personalizados (Livewire)
                document.addEventListener('show-modal', (event) => {
                    this.desactivarTodosLosInputs();

                    // Cambiar el input activo según el tipo de modal
                    if (event.detail === 'devolver-llaves') {
                        this.setActiveInput('devolucion');
                    } else if (event.detail === 'solicitar-llaves') {
                        this.setActiveInput('solicitud');
                    }
                });

                document.addEventListener('close-modal', (event) => {
                    this.restaurarInputActivo();
                });

                // Event listeners para Sweet Alerts
                document.addEventListener('swal:open', (event) => {
                    this.desactivarTodosLosInputs();
                });

                document.addEventListener('swal:close', (event) => {
                    this.restaurarInputActivo();
                });

                // Interceptar SweetAlert2 si está disponible
                if (typeof Swal !== 'undefined') {
                    const originalFire = Swal.fire;
                    Swal.fire = (...args) => {
                        this.desactivarTodosLosInputs();
                        const result = originalFire.apply(this, args);

                        if (result && typeof result.then === 'function') {
                            result.then(() => {
                                this.restaurarInputActivo();
                            }).catch(() => {
                                this.restaurarInputActivo();
                            });
                        }

                        return result;
                    };
                }

                // Event listeners para modales personalizados específicos
                document.addEventListener('click', (event) => {
                    // Detectar clics en botones de cerrar modal
                    if (event.target.matches('[onclick*="cerrarModal"]') ||
                        event.target.matches('[onclick*="cerrarModalRegistro"]') ||
                        event.target.matches('[onclick*="cerrarModalModulos"]')) {
                        setTimeout(() => {
                            this.restaurarInputActivo();
                        }, 200);
                    }

                    // Detectar clics en diferentes áreas para cambiar el input activo
                    if (event.target.closest('#modal-devolver-llaves')) {
                        this.setActiveInput('devolucion');
                    } else if (event.target.closest('#modal-solicitar-llaves')) {
                        this.setActiveInput('solicitud');
                    } else if (event.target.closest('aside')) {
                        this.setActiveInput('main');
                    }
                });
            }
        }

        // Inicializar el gestor de inputs QR
        const qrInputManager = new QRInputManager();

            // Debug flag: set to false to disable all QR debug logs quickly
        const QR_DEBUG = false;

            let bufferQR = '';
        let ordenEscaneo = 'usuario';
        let usuarioEscaneado = null;
        let espacioEscaneado = null;
        let procesandoDevolucion = false;
        let runSolicitantePendiente = null;
        let maxModulosDisponibles = 1;
        let espacioParaReserva = null;
        let runParaReserva = null;
        let usuarioInfo = null; // Variable global para almacenar la información del usuario
        const mapaId = @json($mapaIdValue);

        const config = {
            indicatorSize: 35,
            indicatorWidth: 37,
            indicatorHeight: 37,
            indicatorBorder: '#FFFFFF',
            indicatorTextColor: '#FFFFFF',
            fontSize: 12
        };

        const state = {
            mapImage: null,
            originalImageSize: null,
            indicators: @json($bloques) || [],
            originalCoordinates: @json($bloques) || [],
            isImageLoaded: false,
            mouseX: 0,
            mouseY: 0,
            updateInterval: null,
            hoveredIndicator: null,
            lastLocalChange: null,
            ultimoCambioLocal: null,
            currentIndicatorId: null
        };

        let elements = {
            mapCanvas: null,
            mapCtx: null,
            indicatorsCanvas: null,
            indicatorsCtx: null
        };

        function getQrStatus() {
            return document.getElementById('qr-status');
        }

        function getInfoUsuario() {
            return document.getElementById('info-usuario');
        }

        function getMensajeInicial() {
            return document.getElementById('mensaje-inicial');
        }

        function mostrarInfo(tipo, nombre, run = null) {
            // Ocultar mensaje inicial
            const mensajeInicial = getMensajeInicial();
            if (mensajeInicial) {
                mensajeInicial.classList.add('hidden');
            }

            if (tipo === 'usuario') {
                // Mostrar información del usuario
                const infoUsuario = getInfoUsuario();
                if (infoUsuario) {
                    infoUsuario.classList.remove('hidden');
                }

                // Actualizar datos del usuario
                document.getElementById('run-escaneado').textContent = run;
                document.getElementById('nombre-usuario').textContent = nombre;

                // Quitar parpadeo del estado QR cuando se procesa usuario
                const qrStatus = getQrStatus();
                if (qrStatus) {
                    qrStatus.classList.remove('parpadeo');
                }
            }
        }

        function limpiarEstadoCompleto() {
            // Resetear variables globales
            ordenEscaneo = 'usuario';
            usuarioEscaneado = null;
            espacioEscaneado = null;
            espacioParaReserva = null;
            runParaReserva = null;
            usuarioInfo = null; // Limpiar información del usuario

            // Limpiar buffers
            bufferQR = '';

            // Ocultar información del usuario
            const infoUsuario = getInfoUsuario();
            if (infoUsuario) infoUsuario.classList.add('hidden');

            // Mostrar mensaje inicial
            const mensajeInicial = getMensajeInicial();
            if (mensajeInicial) {
                mensajeInicial.classList.remove('hidden');
            }

            // Limpiar datos
            document.getElementById('run-escaneado').textContent = '--';
            document.getElementById('nombre-usuario').textContent = '--';

            // Restaurar parpadeo del estado QR
            const qrStatus = getQrStatus();
            if (qrStatus) {
                qrStatus.classList.add('parpadeo');
                qrStatus.innerHTML = 'Esperando... Escanea el código QR';
            }

            // Limpiar cualquier input de QR que pueda tener datos
            const qrInput = document.getElementById('qr-input');
            if (qrInput) {
                qrInput.value = '';
            }

            // Restaurar el input QR activo después de limpiar el estado
            setTimeout(() => {
                if (qrInputManager) {
                    qrInputManager.restaurarInputActivo();
                }
            }, 100);
        }

        function resetearFlujoPorError(mensajeError) {
            // Solo limpiar el estado de lectura, NO cerrar modales ni resetear toda la interfaz
            limpiarEstadoLectura(mensajeError);

            // Restaurar el input QR activo después de resetear el flujo
            setTimeout(() => {
                if (qrInputManager) {
                    qrInputManager.restaurarInputActivo();
                }
            }, 100);
        }

        function limpiarEstadoSilencioso() {
            // Limpiar buffer y input sin mostrar mensajes
            bufferQR = '';
            lastBufferLength = 0;
            if (processingTimeout) {
                clearTimeout(processingTimeout);
                processingTimeout = null;
            }
            if (errorTimeout) {
                clearTimeout(errorTimeout);
                errorTimeout = null;
            }
            const inputEscanner = document.getElementById('qr-input');
            if (inputEscanner) {
                inputEscanner.value = '';
            }

            // OCULTAR el bloque de información del usuario silenciosamente
            const infoUsuario = getInfoUsuario();
            if (infoUsuario) {
                infoUsuario.classList.add('hidden');
            }

            // Limpiar los datos mostrados
            const runEscaneado = document.getElementById('run-escaneado');
            const nombreUsuario = document.getElementById('nombre-usuario');
            if (runEscaneado) runEscaneado.textContent = '--';
            if (nombreUsuario) nombreUsuario.textContent = '--';

            // Restaurar el input QR activo después de limpiar el estado silenciosamente
            setTimeout(() => {
                if (qrInputManager) {
                    qrInputManager.restaurarInputActivo();
                }
            }, 100);
        }

        function limpiarEstadoLectura(mensajeError = null) {
            // Solo limpiar el estado de lectura del QR, NO toda la interfaz

            // Limpiar buffer y timeouts
            bufferQR = '';
            lastBufferLength = 0;
            if (processingTimeout) {
                clearTimeout(processingTimeout);
                processingTimeout = null;
            }
            if (errorTimeout) {
                clearTimeout(errorTimeout);
                errorTimeout = null;
            }

            // Limpiar input
            const inputEscanner = document.getElementById('qr-input');
            if (inputEscanner) {
                inputEscanner.value = '';
            }

            // OCULTAR el bloque de información del usuario cuando hay error
            const infoUsuario = getInfoUsuario();
            if (infoUsuario) {
                infoUsuario.classList.add('hidden');
            }

            // Limpiar los datos mostrados en el bloque de información
            const runEscaneado = document.getElementById('run-escaneado');
            const nombreUsuario = document.getElementById('nombre-usuario');
            if (runEscaneado) runEscaneado.textContent = '--';
            if (nombreUsuario) nombreUsuario.textContent = '--';

            // Mostrar mensaje de error temporal si se proporciona
            if (mensajeError) {
                const qrStatus = getQrStatus();
                if (qrStatus) {
                    qrStatus.classList.remove('parpadeo');
                    qrStatus.innerHTML = `Error: ${mensajeError}`;
                    qrStatus.style.color = '#FFFFFF';

                    // Restaurar estado normal después de 1.5 segundos
                    setTimeout(() => {
                        qrStatus.classList.add('parpadeo');
                        qrStatus.innerHTML = 'Esperando... Escanea el código QR';
                        qrStatus.style.color = '';
                    }, 1500);
                }
            } else {
                // Si no hay mensaje de error, solo restaurar el estado normal
                const qrStatus = getQrStatus();
                if (qrStatus) {
                    qrStatus.classList.add('parpadeo');
                    qrStatus.innerHTML = 'Esperando... Escanea el código QR';
                }
            }

            // Resetear orden de escaneo
            ordenEscaneo = 'usuario';

            // Restaurar el input QR activo después de limpiar el estado de lectura
            setTimeout(() => {
                if (qrInputManager) {
                    qrInputManager.restaurarInputActivo();
                }
            }, 100);
        }



        async function verificarUsuario(run) {
            try {
                const response = await fetch(`/api/verificar-usuario/${run}`);
                if (!response.ok) {
                    // Error en respuesta del servidor
                    return null;
                }
                const result = await response.json();
                return result;
            } catch (error) {
                // Error al verificar usuario
                return null;
            }
        }



        async function verificarEspacio(idEspacio) {
            try {
                const response = await fetch(`/api/verificar-espacio/${idEspacio}`);
                if (!response.ok) {
                    // Error en respuesta del servidor
                    return null;
                }
                const result = await response.json();
                return result;
            } catch (error) {
                // Error al verificar espacio
                return null;
            }
        }

        async function verificarClasesProfesor(run) {
            try {
                const response = await fetch(`/api/verificar-clases-programadas/${run}`);
                const result = await response.json();

                const data = result.original || result;
                const tieneClases = data.success && data.tiene_clases;

                return tieneClases;
            } catch (error) {
                return false;
            }
        }

        async function crearReserva(run, idEspacio, tipoUsuario = 'profesor') {
            try {
                const response = await fetch('/api/crear-reserva-profesor', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        run_usuario: run,
                        id_espacio: idEspacio,
                        tipo_usuario: tipoUsuario
                    })
                });
                return await response.json();
            } catch (error) {
                // Error
                return null;
            }
        }

        async function registrarAsistenciaProfesor(run, idEspacio) {
            try {
                const response = await fetch('/api/registrar-uso-espacio', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        run: run,
                        espacio_id: idEspacio
                    })
                });
                return await response.json();
            } catch (error) {
                // Error al registrar asistencia
                return null;
            }
        }



        async function registrarSolicitante(datosSolicitante) {
            try {
                const response = await fetch('/api/registrar-solicitante', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify(datosSolicitante)
                });

                if (!response.ok) {
                    // Si la respuesta no es exitosa, intentar obtener el error del servidor
                    const errorData = await response.json().catch(() => ({}));
                    return {
                        success: false,
                        mensaje: errorData.mensaje || `Error del servidor: ${response.status} ${response.statusText}`
                    };
                }

                return await response.json();
            } catch (error) {
                console.error('Error en registrarSolicitante:', error);
                return {
                    success: false,
                    mensaje: 'Error de conexión con el servidor'
                };
            }
        }



        async function crearReservaSolicitante(runSolicitante, idEspacio) {
            try {
                // Validar horario académico antes de enviar la solicitud
                const ahora = new Date();
                const horaActual = ahora.toLocaleTimeString('es-ES', {
                    hour: '2-digit',
                    minute: '2-digit',
                    second: '2-digit'
                });

                // Verificar si estamos dentro del horario académico (08:10 - 23:00)
                const hora = parseInt(horaActual.split(':')[0]);
                const minutos = parseInt(horaActual.split(':')[1]);
                const horaEnMinutos = hora * 60 + minutos;

                const inicioAcademico = 8 * 60 + 10; // 08:10
                const finAcademico = 23 * 60; // 23:00

                if (horaEnMinutos < inicioAcademico || horaEnMinutos >= finAcademico) {
                    return {
                        success: false,
                        mensaje: 'No se pueden crear reservas fuera del horario académico (08:10 - 23:00).'
                    };
                }

                const response = await fetch('/api/crear-reserva-solicitante', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        run_solicitante: runSolicitante,
                        id_espacio: idEspacio,
                        modulos: 1 // Por defecto 1 módulo para solicitantes
                    })
                });

                if (!response.ok) {
                    const errorData = await response.json().catch(() => ({}));
                    return {
                        success: false,
                        mensaje: errorData.mensaje || `Error del servidor: ${response.status} ${response.statusText}`
                    };
                }

                return await response.json();
            } catch (error) {
                console.error('Error en crearReservaSolicitante:', error);
                return {
                    success: false,
                    mensaje: 'Error de conexión con el servidor'
                };
            }
        }

        async function devolverEspacio(runUsuario, idEspacio, tipoDesocupacion = 'normal') {
            try {
                const requestBody = {
                    run_usuario: runUsuario,
                    id_espacio: idEspacio,
                    tipo_desocupacion: tipoDesocupacion
                };

                // Si es una desocupación forzosa, agregar el RUN del administrador
                if (tipoDesocupacion === 'forzosa') {
                    requestBody.run_administrador = '{{ auth()->user()->run ?? "admin" }}';
                }

                const response = await fetch('/api/devolver-espacio', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify(requestBody)
                });
                return await response.json();
            } catch (error) {
                // Error
                return null;
            }
        }

        async function verificarEstadoEspacioYReserva(runUsuario, idEspacio) {
            try {
                const response = await fetch('/api/verificar-estado-espacio-reserva', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        run: runUsuario,
                        id_espacio: idEspacio
                    })
                });
                const result = await response.json();
                return result;
            } catch (error) {
                return {
                    tipo: 'error',
                    mensaje: 'Error de conexión al verificar el estado del espacio'
                };
            }
        }

        let lastBufferLength = 0;
        let processingTimeout = null;
        let errorTimeout = null;

    async function handleScan(event) {
                    // Solo procesar cuando se presiona Enter
        if (event.key !== 'Enter') {
            // Acumular caracteres en el buffer
            if (event.key.length === 1) {
                bufferQR += event.key;

                    // Detectar cuando el escaneo se completó (buffer dejó de crecer)
                    if (bufferQR.length > lastBufferLength) {
                        lastBufferLength = bufferQR.length;

                        // Limpiar timeout anterior
                        if (processingTimeout) {
                            clearTimeout(processingTimeout);
                        }

                        // Procesar automáticamente después de 500ms sin nuevos caracteres
                        processingTimeout = setTimeout(async () => {
                            await procesarQRCompleto();
                        }, 500);

                        // Timeout de seguridad para detectar lecturas erróneas (60 segundos)
                        if (errorTimeout) {
                            clearTimeout(errorTimeout);
                        }
                        errorTimeout = setTimeout(() => {
                            if (bufferQR && bufferQR.trim() !== '' && bufferQR.length > 10) {
                                                        // Timeout de seguridad: Lectura errónea detectada
                        limpiarEstadoLectura('Timeout de lectura - QR inválido');
                        // Restaurar autofocus del qr-input después de timeout de lectura
                        setTimeout(() => {
                            if (qrInputManager) {
                                qrInputManager.setActiveInput('main');
                            }
                        }, 100);
                    }
                }, 60000);
                    }
                }
                return;
            }

                    // Validar que hay contenido en el buffer antes de procesar
        if (!bufferQR || bufferQR.trim() === '') {
            // Buffer vacío al presionar Enter - ignorando
            return;
        }

        await procesarQRCompleto();
        }

        async function procesarQRCompleto() {
            // Validar que el buffer no esté vacío
            if (!bufferQR || bufferQR.trim() === '') {
                // Buffer QR vacío - ignorando procesamiento
                return;
            }

            // Validar que el buffer tenga un tamaño mínimo razonable
            if (bufferQR.length < 5) {
                // Buffer QR muy corto - ignorando procesamiento
                limpiarEstadoLectura(); // Solo limpiar lectura, no toda la interfaz
                return;
            }

                    // Procesando QR completo

        // Validar orden de escaneo
        if (ordenEscaneo === 'usuario') {
            // PASO 1: Escanear usuario (obligatorio primero)
            await procesarUsuario();
        } else if (ordenEscaneo === 'espacio') {
            // PASO 2: Escanear espacio (solo después del usuario)
            const resultado = await procesarEspacio();

            // Si la devolución fue exitosa, no continuar con más procesamiento
            if (resultado === 'devolucion_exitosa') {
                return;
            }
        } else {
            // Error: orden incorrecto
            limpiarEstadoLectura('Orden de escaneo incorrecto');
        }

            // NO limpiar el buffer aquí - dejarlo para que las funciones individuales lo manejen
            // Solo limpiar timeouts
            if (processingTimeout) {
                clearTimeout(processingTimeout);
                processingTimeout = null;
            }
            if (errorTimeout) {
                clearTimeout(errorTimeout);
                errorTimeout = null;
            }

            // Restaurar el input QR activo después de procesar
            setTimeout(() => {
                if (qrInputManager) {
                    qrInputManager.restaurarInputActivo();
                }
            }, 100);
        }

        async function procesarUsuario() {
                    // Extraer RUN del QR (buscar "RUN" seguido de números)
        const runMatch = bufferQR.match(/RUN[^0-9]*(\d+)/);
        let run = null;

        if (!runMatch) {
            // Intentar otros formatos de RUN
            const runMatchAlt = bufferQR.match(/(\d{7,8})/);
            if (!runMatchAlt) {
                // Solo mostrar error si el buffer tiene contenido significativo y no es ruido
                if (bufferQR.length > 8) {
                    // Lectura errónea: No se pudo extraer RUN del QR
                    limpiarEstadoLectura('QR de usuario inválido');
                    // Restaurar autofocus del qr-input después de error de QR inválido
                    setTimeout(() => {
                        if (qrInputManager) {
                            qrInputManager.setActiveInput('main');
                        }
                    }, 100);
                } else {
                    // Error silencioso para buffers cortos
                    limpiarEstadoSilencioso();
                }
                return;
            }
            run = runMatchAlt[1];
        } else {
            run = runMatch[1];
        }

        // RUN extraído

                    // Verificar usuario en la base de datos
                    usuarioInfo = await verificarUsuario(run);

        if (!usuarioInfo) {
            // Error al verificar usuario - resetear flujo
            limpiarEstadoLectura('Usuario no encontrado en el sistema');
            // Restaurar autofocus del qr-input después de error de usuario no encontrado
            setTimeout(() => {
                if (qrInputManager) {
                    qrInputManager.setActiveInput('main');
                }
            }, 100);
            return;
        }

            if (usuarioInfo.verificado) {
                // Usuario verificado

                if (usuarioInfo.tipo_usuario === 'profesor') {
                            // Es profesor - verificar si tiene clases programadas
        const tieneClases = await verificarClasesProfesor(run);

        if (tieneClases === true) {
            // Profesor CON clases - solo registra solicitud
            document.getElementById('qr-status').innerHTML = 'Profesor con clases verificado. Escanee el espacio para registrar asistencia.';
            mostrarInfo('usuario', usuarioInfo.usuario.nombre, usuarioInfo.usuario.run);
            usuarioEscaneado = run;
            ordenEscaneo = 'espacio';
        } else {
            // Profesor SIN clases - solicita con módulos
            document.getElementById('qr-status').innerHTML = 'Profesor sin clases. Escanee el espacio para solicitar.';
            mostrarInfo('usuario', usuarioInfo.usuario.nombre, usuarioInfo.usuario.run);
            usuarioEscaneado = run;
            ordenEscaneo = 'espacio';
        }
                } else if (usuarioInfo.tipo_usuario === 'solicitante_registrado') {
                            // Es solicitante registrado - solicita con módulos
        document.getElementById('qr-status').innerHTML = 'Solicitante verificado. Escanee el espacio para solicitar.';
        mostrarInfo('usuario', usuarioInfo.usuario.nombre, usuarioInfo.usuario.run);
        usuarioEscaneado = run;
        ordenEscaneo = 'espacio';
                } else {
                            // Otro tipo de usuario - mostrar error
                }

                // Limpiar buffer después de procesar usuario exitosamente
                bufferQR = '';
                lastBufferLength = 0;
                const inputEscanner = document.getElementById('qr-input');
                if (inputEscanner) {
                    inputEscanner.value = '';
                }

                // Restaurar autofocus del qr-input después de procesar usuario exitosamente
                setTimeout(() => {
                    if (qrInputManager) {
                        qrInputManager.setActiveInput('main');
                    }
                }, 100);

            } else {
                        // Usuario no encontrado - mostrar modal de registro de solicitante
        runSolicitantePendiente = run;
        document.getElementById('run-solicitante-no-registrado').textContent = run;

        // Cerrar modal actual si está abierto
        window.dispatchEvent(new CustomEvent('close-modal', {
            detail: 'data-space'
        }));

        // Abrir modal de registro de solicitante
        setTimeout(() => {
            // Desactivar todos los inputs QR para permitir escribir cómodamente
            qrInputManager.desactivarTodosLosInputs();

            window.dispatchEvent(new CustomEvent('open-modal', {
                detail: 'registro-solicitante'
            }));

            // Restaurar autofocus del qr-input después de abrir modal de registro
            setTimeout(() => {
                if (qrInputManager) {
                    qrInputManager.setActiveInput('main');
                }
            }, 300);
        }, 300);

        // Limpiar buffer después de abrir modal
        bufferQR = '';
        lastBufferLength = 0;
        const inputEscanner = document.getElementById('qr-input');
        if (inputEscanner) {
            inputEscanner.value = '';
        }
            }
        }

        async function procesarEspacio() {
                    // Extraer código de espacio - múltiples formatos posibles
        let espacio = null;

        // Patrón 1: TH seguido de cualquier cosa (formato estándar)
        const espacioMatch = bufferQR.match(/(TH[^A-Z0-9]*[A-Z0-9]+)/i);
        if (espacioMatch) {
            espacio = espacioMatch[1];
        } else {
            // Patrón 2: 2-3 letras + números (formato compacto)
            const espacioMatchAlt = bufferQR.match(/([A-Z]{2,3}[0-9]+)/i);
            if (espacioMatchAlt) {
                espacio = espacioMatchAlt[1];
            } else {
                // Patrón 3: Letras + caracteres especiales + letras/números
                const espacioMatchSpecial = bufferQR.match(/([A-Z]+['\-]?[A-Z0-9]+)/i);
                if (espacioMatchSpecial) {
                    espacio = espacioMatchSpecial[1];
                } else {
                    // Patrón 4: Formato simple letras + números
                    const espacioMatchSimple = bufferQR.match(/([A-Z]+[0-9]+)/i);
                    if (espacioMatchSimple) {
                        espacio = espacioMatchSimple[1];
                    } else {
                        // Solo mostrar error si el buffer tiene contenido significativo
                        if (bufferQR.length > 8) {
                            limpiarEstadoLectura('QR de espacio inválido');
                        } else {
                            limpiarEstadoSilencioso();
                        }
                        return;
                    }
                }
            }
        }

        // Normalizar el formato del espacio para que coincida con la BD (TH-C1)
        if (espacio) {
            espacio = espacio.toUpperCase().replace(/'/g, '-');
        }

                    // Verificar estado del espacio y reservas del usuario

        // Agregar timeout para evitar que se cuelgue
        const timeoutPromise = new Promise((_, reject) =>
            setTimeout(() => reject(new Error('Timeout en verificación de espacio')), 10000)
        );

        const resultadoVerificacion = await Promise.race([
            verificarEstadoEspacioYReserva(usuarioEscaneado, espacio),
            timeoutPromise
        ]).catch(error => {
            // Error en verificación de espacio
            // Restaurar autofocus del qr-input después de timeout en verificación
            setTimeout(() => {
                if (qrInputManager) {
                    qrInputManager.setActiveInput('main');
                }
            }, 100);
            return {
                tipo: 'error',
                mensaje: 'Timeout al verificar el estado del espacio'
            };
        });

                    if (resultadoVerificacion.tipo === 'error') {
            // Error al verificar estado - resetear flujo
            // Error al verificar estado del espacio
            limpiarEstadoLectura('Error al verificar el estado del espacio');
            // Restaurar autofocus del qr-input después de error en verificación de espacio
            setTimeout(() => {
                if (qrInputManager) {
                    qrInputManager.setActiveInput('main');
                }
            }, 100);
            return;
        }

            if (resultadoVerificacion.tipo === 'devolucion') {
                        // Procesando devolución...
        // Evitar procesamiento múltiple
        if (procesandoDevolucion) {
            return 'devolucion_en_proceso';
        }

        procesandoDevolucion = true;

        // El usuario tiene una reserva activa en este espacio - procesar devolución automáticamente

        // Mostrar mensaje de devolución en proceso
        document.getElementById('qr-status').innerHTML = 'Procesando devolución...';

        const devolucion = await devolverEspacio(usuarioEscaneado, espacio);

        if (devolucion && devolucion.success) {
            // Actualizar indicador en el mapa
            const block = state.indicators.find(b => b.id === espacio);
            if (block) {
                block.estado = '#00FF00'; // Verde = Disponible
                state.originalCoordinates = state.indicators.map(i => ({ ...i }));
                drawIndicators();
            }

            // Verificar si es devolución en el primer módulo
            if (devolucion.devolucion_primer_modulo && devolucion.info_clase) {
                // Mostrar modal para preguntar si hubo asistentes
                mostrarModalAsistentes(devolucion.info_clase, devolucion.id_reserva, espacio);
                
                // Limpiar estado después de mostrar el modal
                setTimeout(() => {
                    limpiarEstadoLectura();
                    if (qrInputManager) {
                        qrInputManager.setActiveInput('main');
                    }
                }, 500);
            } else {
                // Devolución normal - mostrar Sweet Alert de éxito
                Swal.fire({
                    title: '¡Devolución Exitosa!',
                    text: 'Las llaves han sido devueltas correctamente.',
                    icon: 'success',
                    confirmButtonText: 'Aceptar',
                    confirmButtonColor: '#059669',
                    timer: 1500,
                    timerProgressBar: true,
                    showConfirmButton: false
                });

                // Mostrar mensaje de éxito
                document.getElementById('qr-status').innerHTML = 'Devolución exitosa';

                // Limpiar solo el estado de lectura después de un delay
                setTimeout(() => {
                    limpiarEstadoLectura();
                    // Restaurar autofocus del qr-input después de devolución exitosa
                    if (qrInputManager) {
                        qrInputManager.setActiveInput('main');
                    }
                }, 2000);
            }

            // IMPORTANTE: Detener completamente el procesamiento aquí
            procesandoDevolucion = false;
            return 'devolucion_exitosa';
        } else {
            // Mostrar error específico de devolución
            const mensajeError = devolucion?.mensaje || 'Error al devolver las llaves';

            // Resetear el estado para permitir nuevo escaneo
            procesandoDevolucion = false;
            ordenEscaneo = 'usuario';
            // Restaurar autofocus del qr-input después de error en devolución
            setTimeout(() => {
                if (qrInputManager) {
                    qrInputManager.setActiveInput('main');
                }
            }, 100);
            return;
        }
            }

            if (resultadoVerificacion.tipo === 'reserva_existente') {
                // Procesando reserva existente...

                // Mostrar Sweet Alert de reserva existente
                Swal.fire({
                    title: 'Reserva Activa',
                    text: resultadoVerificacion.mensaje,
                    icon: 'warning',
                    confirmButtonText: 'Entendido',
                    confirmButtonColor: '#F59E0B',
                    timer: 2000,
                    timerProgressBar: true,
                    showConfirmButton: false
                });

                // Limpiar estado después del Sweet Alert
                setTimeout(() => {
                    limpiarEstadoLectura();
                    // Restaurar autofocus del qr-input después de mostrar reserva existente
                    if (qrInputManager) {
                        qrInputManager.setActiveInput('main');
                    }
                }, 2500);

                ordenEscaneo = 'usuario';
                return;
            }

            if (resultadoVerificacion.tipo === 'espacio_ocupado') {
                // Procesando espacio ocupado...
                // Verificar si el ocupante es el mismo usuario que acaba de escanear
                if (resultadoVerificacion.ocupante && resultadoVerificacion.ocupante.run === usuarioEscaneado) {
                    // Es el mismo usuario, no mostrar mensaje de ocupado
                    ordenEscaneo = 'usuario';
                    return;
                }

                let mensajeDetallado = resultadoVerificacion.mensaje;
                if (resultadoVerificacion.ocupante) {
                    const ocupante = resultadoVerificacion.ocupante;
                    const tipoUsuario = ocupante.tipo === 'profesor' ? 'Profesor' : 'Solicitante';
                    mensajeDetallado = `
                        <div class="text-left">
                            <p class="mb-2"><strong>${resultadoVerificacion.mensaje}</strong></p>
                            <div class="p-3 bg-gray-100 rounded-lg">
                                <p><strong>${tipoUsuario}:</strong> ${ocupante.nombre}</p>
                                <p><strong>RUN:</strong> ${ocupante.run}</p>
                                <p><strong>Hora de inicio:</strong> ${ocupante.hora_inicio}</p>
                                <p><strong>Fecha:</strong> ${ocupante.fecha}</p>
                            </div>
                        </div>
                    `;
                }



                // Limpiar estado después de mostrar el mensaje
                setTimeout(() => {
                    limpiarEstadoLectura();
                    // Restaurar autofocus del qr-input después de mostrar espacio ocupado
                    if (qrInputManager) {
                        qrInputManager.setActiveInput('main');
                    }
                }, 1000);

                ordenEscaneo = 'usuario';
                return;
            }

                    // Si llegamos aquí, el espacio está disponible para crear una nueva reserva
        // Verificar el tipo de usuario para determinar el flujo
                    usuarioInfo = await verificarUsuario(usuarioEscaneado);

        if (!usuarioInfo || !usuarioInfo.verificado) {
            ordenEscaneo = 'usuario';
            // Restaurar autofocus del qr-input después de error en verificación de usuario
            setTimeout(() => {
                if (qrInputManager) {
                    qrInputManager.setActiveInput('main');
                }
            }, 100);
            return;
        }

        // Determinar el flujo según el tipo de usuario
        if (usuarioInfo.tipo_usuario === 'profesor') {
            // Verificar si tiene clases programadas
            const tieneClases = await verificarClasesProfesor(usuarioEscaneado);

            if (tieneClases === true) {
                // CASO 1: Profesor CON clases - registrar asistencia usando endpoint específico
                const resultado = await registrarAsistenciaProfesor(usuarioEscaneado, espacio);
                if (resultado && resultado.success) {
                    // Mostrar mensaje de proceso
                    document.getElementById('qr-status').innerHTML = 'Registrando asistencia...';

                    // Actualizar indicador en el mapa
                    const block = state.indicators.find(b => b.id === espacio);
                    if (block) {
                        block.estado = '#FF0000'; // Rojo = Ocupado
                        state.originalCoordinates = state.indicators.map(i => ({ ...i }));
                        drawIndicators();
                    }

                    // Mostrar Sweet Alert de éxito para asistencia registrada
                    Swal.fire({
                        title: '¡Asistencia Registrada!',
                        text: 'El profesor ha registrado su asistencia correctamente.',
                        icon: 'success',
                        confirmButtonText: 'Aceptar',
                        confirmButtonColor: '#059669',
                        timer: 1500,
                        timerProgressBar: true,
                        showConfirmButton: false
                    });

                    // Mostrar mensaje de asistencia registrada
                    document.getElementById('qr-status').innerHTML = 'Asistencia registrada';
                    document.getElementById('qr-status').classList.remove('parpadeo');

                    // Limpiar solo el estado de lectura después de un delay
                    setTimeout(() => {
                        // Mantener usuarioEscaneado para continuar el flujo
                        ordenEscaneo = 'espacio'; // Ya escaneó usuario, ahora espera espacio
                        espacioParaReserva = null;
                        runParaReserva = null;

                        // Limpiar solo buffers de lectura
                        bufferQR = '';

                        // Resetear solo interfaz de lectura
                        limpiarEstadoLectura();

                        // Mantener información del usuario visible
                        const qrStatus = document.getElementById('qr-status');
                        if (qrStatus) {
                            qrStatus.classList.remove('parpadeo');
                            qrStatus.innerHTML = 'Usuario verificado. Escanee el espacio.';
                        }

                        // Restaurar autofocus del qr-input después de registrar asistencia
                        if (qrInputManager) {
                            qrInputManager.setActiveInput('main');
                        }
                    }, 2000);
                } else {
                    // Error en registro de asistencia - restaurar autofocus
                    setTimeout(() => {
                        if (qrInputManager) {
                            qrInputManager.setActiveInput('main');
                        }
                    }, 100);
                }
            } else {
                // CASO 2: Profesor SIN clases - solicita con módulos (máx 2)
                await mostrarModalSeleccionarModulos(espacio, usuarioEscaneado, 2); // Máximo 2 módulos
                return; // No continuar, esperar selección de módulos
            }
        } else if (usuarioInfo.tipo_usuario === 'solicitante_registrado') {
            // CASO 3: Solicitante registrado - solicita con módulos (máx 2)
            await mostrarModalSeleccionarModulos(espacio, usuarioEscaneado, 2); // Máximo 2 módulos
            return; // No continuar, esperar selección de módulos
        } else {
            ordenEscaneo = 'usuario';
            // Restaurar autofocus del qr-input después de error en tipo de usuario
            setTimeout(() => {
                if (qrInputManager) {
                    qrInputManager.setActiveInput('main');
                }
            }, 100);
            return;
        }

        // Limpiar buffer después de procesar espacio exitosamente
        bufferQR = '';
        lastBufferLength = 0;
        const inputEscanner = document.getElementById('qr-input');
        if (inputEscanner) {
            inputEscanner.value = '';
        }

        // Resetear para siguiente usuario
        setTimeout(() => {
            limpiarEstadoLectura();
            // Restaurar autofocus del qr-input después de procesar espacio exitosamente
            if (qrInputManager) {
                qrInputManager.setActiveInput('main');
            }
        }, 3000);

        return 'procesamiento_completado';
        }

        function initElements() {
            elements.mapCanvas = document.getElementById('mapCanvas');
            elements.mapCtx = elements.mapCanvas.getContext('2d');
            elements.indicatorsCanvas = document.getElementById('indicatorsCanvas');
            elements.indicatorsCtx = elements.indicatorsCanvas.getContext('2d');
        }

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

        function handleMouseClick(event) {
            const rect = elements.indicatorsCanvas.getBoundingClientRect();
            const mouseX = event.clientX - rect.left;
            const mouseY = event.clientY - rect.top;

            const clickedIndicator = getHoveredIndicator(mouseX, mouseY);

            if (clickedIndicator) {
                mostrarModalEspacio(clickedIndicator);
            }
        }

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

            // Establecer dimensiones de los canvas
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

        function calculatePosition(indicator) {
            if (!state.isImageLoaded || !state.mapImage) return {
                x: 0,
                y: 0
            };

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
            if (!originalIndicator) return {
                x: 0,
                y: 0
            };

            // Calcular la posición escalada
            const x = offsetX + (originalIndicator.x / state.originalImageSize.width) * drawWidth;
            const y = offsetY + (originalIndicator.y / state.originalImageSize.height) * drawHeight;

            return {
                x,
                y
            };
        }

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

        function drawIndicators() {
            if (!state.isImageLoaded || !elements.indicatorsCanvas) {
                return;
            }

            const ctx = elements.indicatorsCanvas.getContext('2d');
            ctx.clearRect(0, 0, elements.indicatorsCanvas.width, elements.indicatorsCanvas.height);

            state.indicators.forEach((indicator, index) => {
                const position = calculatePosition(indicator);

                const size = config.indicatorSize;

                // Determinar color basado en el estado
                let color = indicator.estado;

                // Convertir estado a minúsculas para comparación
                const estadoLower = indicator.estado.toLowerCase();

                if (estadoLower === 'disponible' || estadoLower === 'libre') {
                    color = '#059669'; // Verde
                } else if (estadoLower === 'ocupado') {
                    color = '#FF0000'; // Rojo
                } else if (estadoLower === 'reservado') {
                    color = '#F59E0B'; // Naranja
                } else if (estadoLower === 'proximo') {
                    color = '#3B82F6'; // Azul
                } else if (estadoLower === 'clasesinasistentes') {
                    color = '#9333EA'; // Púrpura/Morado - Clase sin asistentes
                } else {
                    color = '#059669'; // Verde por defecto
                }

                // Verificar si este indicador está siendo hover
                const isHovered = state.hoveredIndicator && state.hoveredIndicator.id === indicator.id;

                // Usar la función dibujarIndicador existente
                dibujarIndicador(
                    elements,
                    position,
                    size,
                    size,
                    color,
                    indicator.id,
                    isHovered,
                    indicator.detalles || {},
                    null
                );
            });
        }

        async function mostrarModalEspacio(indicator) {
            console.log('🔍 DEBUG - mostrarModalEspacio llamada para:', indicator.id);
            console.log('🔍 Estado del indicator:', indicator.estado);
            console.log('🔍 Indicator completo:', indicator);

                    // Mostrar el modal inmediatamente
        const modal = document.getElementById('modal-espacio-info');
        if (modal) {
            modal.classList.remove('hidden');
            // Desactivar todos los inputs QR cuando se abre el modal
            qrInputManager.desactivarTodosLosInputs();
            console.log('✅ Modal mostrado correctamente');
        } else {
            console.error('❌ No se encontró el modal de espacio');
            return;
        }

                // Guardar indicador actual en estado para usos posteriores
                state.currentIndicatorId = indicator.id;

            // Obtener elementos del modal una sola vez
            const elements = {
                modalTitulo: document.getElementById('modalTitulo'),
                modalSubtitulo: document.getElementById('modalSubtitulo'),
                modalEstado: document.getElementById('modalEstado'),
                estadoDetalles: document.getElementById('estadoDetalles'),
                ocupanteContainer: document.getElementById('ocupanteContainer'),
                ocupanteInfo: document.getElementById('ocupanteInfo'),
                claseActualContainer: document.getElementById('claseActualContainer'),
                claseActualInfo: document.getElementById('claseActualInfo'),
                proximaClaseContainer: document.getElementById('proximaClaseContainer'),
                proximaClaseInfo: document.getElementById('proximaClaseInfo'),
                tipoEspacio: document.getElementById('tipoEspacio'),
                capacidadEspacio: document.getElementById('capacidadEspacio'),
                pisoEspacio: document.getElementById('pisoEspacio'),
                ultimaActualizacion: document.getElementById('ultimaActualizacion'),
                estadoPill: document.getElementById('estadoPill'),
                estadoIcon: document.getElementById('estadoIcon')
            };

            // Configurar información básica del modal inmediatamente (sin esperar)
            configurarInformacionBasica(elements, indicator);

            // Configurar estado inmediatamente
            configurarEstado(elements, indicator);

                    // Mostrar loading optimizado
        mostrarLoadingOptimizado(elements);

        // Cargar información detallada en paralelo con timeout
        const dataPromise = cargarInformacionDetallada(indicator.id);
        const timeoutPromise = new Promise((_, reject) =>
            setTimeout(() => reject(new Error('Timeout')), 5000)
        );

        try {
            const data = await Promise.race([dataPromise, timeoutPromise]);
            console.log('🔍 DEBUG - Datos recibidos de la API:', data);

            if (data.success) {
                console.log('✅ API respondió correctamente, llamando renderizarInformacionOcupante');
                // Renderizar información optimizada, pasando también el estado del indicator
                renderizarInformacionOcupante(elements, data, indicator);
            } else {
                console.error('❌ API respondió con error:', data);
                mostrarErrorCarga(elements, 'No se pudo cargar la información');
            }
        } catch (error) {
            console.error('❌ Error al cargar información:', error);
            // Error al cargar información
            mostrarErrorCarga(elements, 'Error de conexión');
        }
        }

        // Función para configurar información básica
        function configurarInformacionBasica(elements, indicator) {
            if (elements.modalTitulo) {
                elements.modalTitulo.textContent = `${indicator.nombre} (${indicator.id})`;
            }

            if (elements.modalSubtitulo) {
                elements.modalSubtitulo.textContent = `${indicator.tipo || 'Espacio'}`;
            }

            if (elements.tipoEspacio) {
                elements.tipoEspacio.textContent = indicator.tipo || 'No especificado';
            }
            if (elements.capacidadEspacio) {
                elements.capacidadEspacio.textContent = indicator.capacidad || 'No especificada';
            }
            if (elements.pisoEspacio) {
                elements.pisoEspacio.textContent = indicator.piso || 'No especificado';
            }
            if (elements.ultimaActualizacion) {
                elements.ultimaActualizacion.textContent = new Date().toLocaleString('es-CL');
            }
        }

        // Función para configurar estado
        function configurarEstado(elements, indicator) {
            const estadoReal = indicator.estado;
            // Estado real del espacio

            const estadoConfig = {
                'disponible': { texto: 'Disponible', pill: 'border-green-500 bg-green-50 text-green-700', icon: 'bg-green-500' },
                'Disponible': { texto: 'Disponible', pill: 'border-green-500 bg-green-50 text-green-700', icon: 'bg-green-500' },
                '#059669': { texto: 'Disponible', pill: 'border-green-500 bg-green-50 text-green-700', icon: 'bg-green-500' },
                'ocupado': { texto: 'Ocupado', pill: 'border-red-500 bg-red-50 text-red-700', icon: 'bg-red-500' },
                'Ocupado': { texto: 'Ocupado', pill: 'border-red-500 bg-red-50 text-red-700', icon: 'bg-red-500' },
                '#FF0000': { texto: 'Ocupado', pill: 'border-red-500 bg-red-50 text-red-700', icon: 'bg-red-500' },
                'reservado': { texto: 'Reservado', pill: 'border-yellow-400 bg-yellow-50 text-yellow-700', icon: 'bg-yellow-400' },
                'Reservado': { texto: 'Reservado', pill: 'border-yellow-400 bg-yellow-50 text-yellow-700', icon: 'bg-yellow-400' },
                '#FFA500': { texto: 'Reservado', pill: 'border-yellow-400 bg-yellow-50 text-yellow-700', icon: 'bg-yellow-400' },
                '#F59E0B': { texto: 'Reservado', pill: 'border-yellow-400 bg-yellow-50 text-yellow-700', icon: 'bg-yellow-400' },
                'proximo': { texto: 'Próximo a ocuparse', pill: 'border-blue-500 bg-blue-50 text-blue-700', icon: 'bg-blue-500' },
                'Próximo': { texto: 'Próximo a ocuparse', pill: 'border-blue-500 bg-blue-50 text-blue-700', icon: 'bg-blue-500' },
                '#3B82F6': { texto: 'Próximo a ocuparse', pill: 'border-blue-500 bg-blue-50 text-blue-700', icon: 'bg-blue-500' }
            };

            const config = estadoConfig[estadoReal] || {
                texto: 'Sin estado',
                pill: 'border-gray-400 bg-gray-50 text-gray-700',
                icon: 'bg-gray-400'
            };

            if (elements.estadoPill) {
                elements.estadoPill.className = `inline-flex items-center px-4 py-2 text-sm font-bold border rounded-full ${config.pill}`;
            }

            if (elements.estadoIcon) {
                elements.estadoIcon.className = `w-3 h-3 mr-2 rounded-full ${config.icon}`;
            }

            if (elements.modalEstado) {
                elements.modalEstado.textContent = config.texto;
            }
        }

        // Función para mostrar loading optimizado
        function mostrarLoadingOptimizado(elements) {
            if (elements.ocupanteContainer) {
                elements.ocupanteContainer.style.display = 'block';
                if (elements.ocupanteInfo) {
                    elements.ocupanteInfo.innerHTML = `
                        <div class="flex items-center justify-center py-4">
                            <div class="w-6 h-6 border-b-2 border-blue-500 rounded-full animate-spin"></div>
                            <span class="ml-2 text-sm text-gray-600">Cargando...</span>
                        </div>
                    `;
                }
            }
        }

        // Función para cargar información detallada con cache optimizado para solicitantes
        async function cargarInformacionDetallada(espacioId) {
                    // Cache específico para espacios con solicitantes
        const cacheKey = `espacio_${espacioId}`;
        const solicitanteCacheKey = `solicitante_espacio_${espacioId}`;

        const cached = sessionStorage.getItem(cacheKey);
        const cacheTime = sessionStorage.getItem(`${cacheKey}_time`);
        const solicitanteCached = sessionStorage.getItem(solicitanteCacheKey);
        const solicitanteCacheTime = sessionStorage.getItem(`${solicitanteCacheKey}_time`);

        // Cache válido por 30 segundos para espacios normales
        if (cached && cacheTime && (Date.now() - parseInt(cacheTime)) < 30000) {
            return JSON.parse(cached);
        }

        // Cache específico para solicitantes (válido por 5 minutos)
        if (solicitanteCached && solicitanteCacheTime && (Date.now() - parseInt(solicitanteCacheTime)) < 300000) {
            return JSON.parse(solicitanteCached);
        }

            const response = await fetch(`/api/espacio/${espacioId}/informacion-detallada`, {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            });

            const data = await response.json();

                    // Guardar en cache según el tipo de ocupación
        if (data.success && data.tipo_ocupacion === 'solicitante') {
            // Cache específico para solicitantes (5 minutos)
            sessionStorage.setItem(solicitanteCacheKey, JSON.stringify(data));
            sessionStorage.setItem(`${solicitanteCacheKey}_time`, Date.now().toString());
        } else {
            // Cache normal para otros tipos (30 segundos)
            sessionStorage.setItem(cacheKey, JSON.stringify(data));
            sessionStorage.setItem(`${cacheKey}_time`, Date.now().toString());
        }

            return data;
        }

        // Función para mostrar error de carga
        function mostrarErrorCarga(elements, mensaje) {
            if (elements.ocupanteInfo) {
                elements.ocupanteInfo.innerHTML = `
                    <div class="flex items-center justify-center py-4">
                        <i class="mr-2 text-red-500 fas fa-exclamation-triangle"></i>
                        <span class="text-sm text-red-600">${mensaje}</span>
                    </div>
                `;
            }
        }

        // Función para renderizar información del ocupante optimizada
        function renderizarInformacionOcupante(elements, data, indicator) {
            // Debug: Log de los datos recibidos
            console.log('🔍 DEBUG - renderizarInformacionOcupante llamada');
            console.log('🔍 Estado del indicator:', indicator?.estado);
            console.log('🔍 Datos recibidos:', data);
            console.log('🔍 Tipo de ocupación:', data.tipo_ocupacion);

            // Verificar si el espacio está disponible PRIMERO, sin importar el tipo_ocupacion
            const espacioDisponible = indicator && (
                indicator.estado === 'Disponible' ||
                indicator.estado === 'disponible' ||
                indicator.estado === '#059669' ||
                indicator.estado === '#10b981'
            );

            console.log('🔍 ¿Espacio disponible?:', espacioDisponible);

            // Si el espacio está disponible, forzar renderizado como libre
            if (espacioDisponible) {
                console.log('🟢 Espacio disponible - Forzando renderizado libre');
                renderizarInformacionLibre(elements, data, indicator);
                return;
            }

            // Solo si el espacio NO está disponible, mostrar según tipo de ocupación
            console.log('🔴 Espacio ocupado - Renderizando según tipo');

            // IMPORTANTE: Si el espacio está ocupado, SIEMPRE mostrar el botón desocupar
            // independientemente del tipo de ocupación o si tenemos datos del ocupante
            const btnsDesocupar = document.querySelectorAll('.btn-desocupar[data-tipo="espacio"]');
            console.log('🔍 Botones desocupar encontrados:', btnsDesocupar.length);
            
            btnsDesocupar.forEach((btn, index) => {
                btn.classList.remove('hidden');
                console.log(`🔧 Botón desocupar ${index + 1} activado - Espacio ocupado`);
            });

            // Asegurar que tengamos un RUN para el botón desocupar
            let runParaDesocupar = data.run_profesor || data.run_solicitante;
            
            // Si no tenemos RUN específico, usar el ID del espacio para desocupación forzosa
            if (!runParaDesocupar && indicator?.id) {
                runParaDesocupar = `FORCE_${indicator.id}`;
                console.log('⚠️ Usando desocupación forzosa para espacio:', indicator.id);
            }

            console.log('🔍 RUN para desocupar:', runParaDesocupar);

            // Agregar el input hidden con el RUN (o identificador de forzado)
            const runInput = document.querySelector('#run-ocupante-modal');
            if (runInput) {
                runInput.value = runParaDesocupar || 'unknown';
                console.log('🔍 Input RUN actualizado:', runInput.value);
            } else {
                // Crear el input si no existe
                const newInput = document.createElement('input');
                newInput.type = 'hidden';
                newInput.id = 'run-ocupante-modal';
                newInput.value = runParaDesocupar || 'unknown';
                if (elements.ocupanteInfo) {
                    elements.ocupanteInfo.appendChild(newInput);
                    console.log('🔍 Input RUN creado:', newInput.value);
                }
            }

            // Mostrar información según el tipo de ocupación
            if (data.tipo_ocupacion === 'profesor') {
                console.log('📚 Renderizando como profesor');
                renderizarInformacionProfesor(elements, data, indicator);
            } else if (data.tipo_ocupacion === 'solicitante') {
                console.log('👤 Renderizando como solicitante');
                renderizarInformacionSolicitante(elements, data, indicator);
            } else if (data.tipo_ocupacion === 'ocupado_sin_info') {
                console.log('❓ Renderizando como ocupado sin info');
                renderizarInformacionOcupadoSinInfo(elements, data, indicator);
            } else {
                console.log('🆓 Renderizando como libre');
                renderizarInformacionLibre(elements, data, indicator);
            }
        }        // Handler para los botones Desocupar usando delegación de eventos
        document.addEventListener('DOMContentLoaded', function () {
            // Usar delegación de eventos para manejar todos los botones .btn-desocupar
            document.addEventListener('click', async function (event) {
                if (!event.target.matches('.btn-desocupar')) return;
                
                const espacioId = state.currentIndicatorId || null;
                const tipoDesocupacion = event.target.dataset.tipo || 'espacio';

                // Obtener información del usuario autenticado (administrador)
                const administradorRun = '{{ auth()->user()->run ?? "admin" }}';

                // Tomar el último RUN mostrado en el modal (el usuario que ocupa el espacio)
                let runOcupante = null;
                const runEl = document.querySelector('#run-ocupante-modal');
                if (runEl && runEl.value && runEl.value.trim() !== '') {
                    runOcupante = runEl.value.trim();
                } else {
                    // Si no se encuentra el RUN, mostrar error
                    Swal.fire({
                        title: 'Error',
                        text: 'No se encontró información del ocupante del espacio',
                        icon: 'error',
                        confirmButtonText: 'Entendido'
                    });
                    return;
                }

                try {
                const res = await fetch('/api/devolver-espacio', {
                    method: 'POST',
                    headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        run_usuario: runOcupante,
                        run_administrador: administradorRun,
                        id_espacio: espacioId,
                        tipo_desocupacion: 'forzosa'
                    })
                });

                const json = await res.json();
                if (json.success) {
                    Swal.fire({
                    title: 'Espacio desocupado',
                    text: json.mensaje || 'Espacio desocupado correctamente',
                    icon: 'success',
                    confirmButtonText: 'Aceptar',
                    timer: 1500,
                    timerProgressBar: true,
                    showConfirmButton: false
                    });

                    // Cerrar modal primero
                    cerrarModalEspacio();

                    // Forzar actualización inmediata del estado del espacio
                    const indicatorActual = state.indicators.find(b => b.id === espacioId);
                    if (indicatorActual) {
                        indicatorActual.estado = 'libre';
                        indicatorActual.color = '#10b981'; // Verde para libre
                    }

                    // Resetear el timestamp para permitir actualización inmediata
                    state.ultimoCambioLocal = 0;

                    // Actualizar colores del mapa con actualización forzada
                    await actualizarColoresEspacios(true);

                    // Redibujar los indicadores inmediatamente
                    drawIndicators();
                } else {
                    Swal.fire({
                    title: 'Error',
                    text: json.mensaje || 'No se pudo desocupar el espacio',
                    icon: 'error',
                    confirmButtonText: 'Entendido'
                    });
                }
                } catch (e) {
                console.error(e);
                Swal.fire({
                    title: 'Error',
                    text: 'Error al desocupar el espacio',
                    icon: 'error',
                    confirmButtonText: 'Entendido'
                });
                }
            });
        });

        // Función para renderizar información de profesor
        function renderizarInformacionProfesor(elements, data, indicator) {
            const tituloEl = document.getElementById('ocupanteTitulo');
            // Ajustar título según existencia de próxima clase
            if (tituloEl) tituloEl.textContent = data.proxima_clase ? 'Ocupante Actual' : 'Último Ocupante';

            if (elements.ocupanteContainer && elements.ocupanteInfo) {
                // Mostrar el contenedor
                elements.ocupanteContainer.style.display = 'block';

                elements.ocupanteInfo.innerHTML = `
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <div class="flex items-center">
                            <i class="mr-3 text-blue-500 fas fa-user-tie"></i>
                            <div>
                                <div class="font-medium text-gray-800">${data.nombre || 'No especificado'}</div>
                                <div class="text-sm text-gray-600">Profesor</div>
                            </div>
                        </div>
                        <div class="flex items-center">
                            <i class="mr-3 text-green-500 fas fa-book"></i>
                            <div>
                                <div class="font-medium text-gray-800">${data.asignatura || 'Sin asignatura'}</div>
                                <div class="text-sm text-gray-600">Asignatura</div>
                            </div>
                        </div>
                        ${data.hora_inicio ? `
                        <div class="flex items-center">
                            <i class="mr-3 text-orange-500 fas fa-clock"></i>
                            <div>
                                <div class="font-medium text-gray-800">${data.hora_inicio}</div>
                                <div class="text-sm text-gray-600">Hora inicio</div>
                            </div>
                        </div>
                        ` : ''}
                        ${data.hora_salida ? `
                        <div class="flex items-center">
                            <i class="mr-3 text-red-500 fas fa-clock"></i>
                            <div>
                                <div class="font-medium text-gray-800">${data.hora_salida}</div>
                                <div class="text-sm text-gray-600">Hora salida</div>
                            </div>
                        </div>
                        ` : ''}
                    </div>
                `;
            }

            // Mostrar información de la clase actual
            if (elements.claseActualContainer && elements.claseActualInfo) {
                elements.claseActualContainer.style.display = 'block';
                elements.claseActualInfo.innerHTML = `
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <div class="flex items-center">
                            <i class="mr-3 text-blue-500 fas fa-chalkboard"></i>
                            <div>
                                <div class="font-medium text-gray-800">${data.asignatura || 'Sin asignatura'}</div>
                                <div class="text-sm text-gray-600">Asignatura actual</div>
                            </div>
                        </div>
                        <div class="flex items-center">
                            <i class="mr-3 text-green-500 fas fa-user-tie"></i>
                            <div>
                                <input type="hidden" id="run-ocupante-modal" value="${data.run_profesor || data.run_solicitante || ''}">
                                <div class="font-medium text-gray-800">${data.nombre || 'No especificado'}</div>
                                <div class="text-sm text-gray-600">Profesor a cargo</div>
                            </div>
                        </div>
                    </div>
                `;
            }

            // Mostrar próxima clase si existe
            if (data.proxima_clase && elements.proximaClaseContainer && elements.proximaClaseInfo) {
                elements.proximaClaseContainer.style.display = 'block';
                elements.proximaClaseInfo.innerHTML = `
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <div class="flex items-center">
                            <i class="mr-3 text-purple-500 fas fa-calendar"></i>
                            <div>
                                <div class="font-medium text-gray-800">${data.proxima_clase.asignatura || 'Sin asignatura'}</div>
                                <div class="text-sm text-gray-600">Próxima asignatura</div>
                            </div>
                        </div>
                        <div class="flex items-center">
                            <i class="mr-3 text-purple-500 fas fa-user-tie"></i>
                            <div>
                                <div class="font-medium text-gray-800">${data.proxima_clase.profesor || 'No especificado'}</div>
                                <div class="text-sm text-gray-600">Próximo profesor</div>
                            </div>
                        </div>
                    </div>
                `;
            }

            // NOTA: El botón desocupar se maneja centralmente en renderizarInformacionOcupante()
        }

        // Función para renderizar información de solicitante optimizada
        function renderizarInformacionSolicitante(elements, data, indicator) {
            const tituloEl = document.getElementById('ocupanteTitulo');
            // Ajustar título si no hay próxima clase
            if (tituloEl) tituloEl.textContent = data.proxima_clase ? 'Ocupante Actual' : 'Último Ocupante';

            if (elements.ocupanteContainer && elements.ocupanteInfo) {
                // Crear HTML optimizado usando template literal
                const html = `
                    <input type="hidden" id="run-ocupante-modal" value="${data.run_solicitante || ''}">
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <div class="flex items-center">
                            <i class="mr-3 text-green-500 fas fa-user"></i>
                            <div>
                                <div class="font-medium text-gray-800">Solicitante</div>
                                <div class="text-sm text-gray-600">${data.nombre || 'No especificado'}</div>
                            </div>
                        </div>
                        <div class="flex items-center">
                            <i class="mr-3 text-blue-500 fas fa-id-card"></i>
                            <div>
                                <div class="font-medium text-gray-800">RUN</div>
                                <div class="text-sm text-gray-600">${data.run_solicitante || 'No especificado'}</div>
                            </div>
                        </div>
                        <div class="flex items-center">
                            <i class="mr-3 text-orange-500 fas fa-envelope"></i>
                            <div>
                                <div class="font-medium text-gray-800">Correo</div>
                                <div class="text-sm text-gray-600">${data.correo || 'No especificado'}</div>
                            </div>
                        </div>
                        <div class="flex items-center">
                            <i class="mr-3 text-red-500 fas fa-phone"></i>
                            <div>
                                <div class="font-medium text-gray-800">Teléfono</div>
                                <div class="text-sm text-gray-600">${data.telefono || 'No especificado'}</div>
                            </div>
                        </div>
                        <div class="flex items-center">
                            <i class="mr-3 text-purple-500 fas fa-tag"></i>
                            <div>
                                <div class="font-medium text-gray-800">Tipo solicitante</div>
                                <div class="text-sm text-gray-600">${data.tipo_solicitante || 'No especificado'}</div>
                            </div>
                        </div>
                        ${data.activo !== undefined ? `
                        <div class="flex items-center">
                            <i class="fas fa-check-circle mr-3 ${data.activo ? 'text-green-500' : 'text-red-500'}"></i>
                            <div>
                                <div class="font-medium text-gray-800">Estado</div>
                                <div class="text-sm text-gray-600">${data.activo ? 'Activo' : 'Inactivo'}</div>
                            </div>
                        </div>
                        ` : ''}
                        ${data.fecha_registro ? `
                        <div class="flex items-center">
                            <i class="mr-3 text-indigo-500 fas fa-calendar"></i>
                            <div>
                                <div class="font-medium text-gray-800">Fecha registro</div>
                                <div class="text-sm text-gray-600">${new Date(data.fecha_registro).toLocaleDateString('es-CL')}</div>
                            </div>
                        </div>
                        ` : ''}
                    </div>
                `;

                // Aplicar HTML de una sola vez
                elements.ocupanteInfo.innerHTML = html;

                // Mostrar el contenedor
                elements.ocupanteContainer.style.display = 'block';
            }

            // Ocultar secciones de planificación para solicitantes
            if (elements.proximaClaseContainer) {
                elements.proximaClaseContainer.style.display = 'none';
            }
            if (elements.claseActualContainer) {
                elements.claseActualContainer.style.display = 'none';
            }

            // NOTA: El botón desocupar se maneja centralmente en renderizarInformacionOcupante()
        }

        // Función para renderizar información ocupado sin info
        function renderizarInformacionOcupadoSinInfo(elements, data, indicator) {
            const tituloEl = document.getElementById('ocupanteTitulo');
            // Ajustar título según si hay próxima clase
            if (tituloEl) tituloEl.textContent = data.proxima_clase ? 'Ocupante Actual' : 'Último Ocupante';

            if (elements.ocupanteContainer && elements.ocupanteInfo) {
                // Siempre mostrar el contenedor cuando llegamos aquí
                elements.ocupanteContainer.style.display = 'block';

                elements.ocupanteInfo.innerHTML = `
                    <input type="hidden" id="run-ocupante-modal" value="${data.run_profesor || data.run_solicitante || ''}">
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <div class="flex items-center">
                            <i class="mr-3 text-gray-500 fas fa-user"></i>
                            <div>
                                <div class="font-medium text-gray-800">${data.nombre || 'No especificado'}</div>
                                <div class="text-sm text-gray-600">Ocupante</div>
                            </div>
                        </div>
                        <div class="flex items-center">
                            <i class="mr-3 text-gray-500 fas fa-info-circle"></i>
                            <div>
                                <div class="font-medium text-gray-800">${data.tipo_reserva || 'Ocupado'}</div>
                                <div class="text-sm text-gray-600">Tipo</div>
                            </div>
                        </div>
                        ${data.hora_inicio ? `
                        <div class="flex items-center">
                            <i class="mr-3 text-gray-500 fas fa-clock"></i>
                            <div>
                                <div class="font-medium text-gray-800">${data.hora_inicio}</div>
                                <div class="text-sm text-gray-600">Hora inicio</div>
                            </div>
                        </div>
                        ` : ''}
                        ${data.hora_salida ? `
                        <div class="flex items-center">
                            <i class="mr-3 text-gray-500 fas fa-clock"></i>
                            <div>
                                <div class="font-medium text-gray-800">${data.hora_salida}</div>
                                <div class="text-sm text-gray-600">Hora salida</div>
                            </div>
                        </div>
                        ` : ''}
                        ${data.detalles ? `
                        <div class="flex items-center">
                            <i class="mr-3 text-gray-500 fas fa-info"></i>
                            <div>
                                <div class="font-medium text-gray-800">${data.detalles}</div>
                                <div class="text-sm text-gray-600">Detalles</div>
                            </div>
                        </div>
                        ` : ''}
                    </div>
                `;
            }

            // Ocultar secciones de planificación
            if (elements.proximaClaseContainer) {
                elements.proximaClaseContainer.style.display = 'none';
            }

            // NOTA: El botón desocupar se maneja centralmente en renderizarInformacionOcupante()
        }

        // Función para renderizar información libre
        function renderizarInformacionLibre(elements, data, indicator) {
            const tituloEl = document.getElementById('ocupanteTitulo');

            // Determinar si el espacio está realmente disponible según el indicator
            const espacioDisponible = indicator && (
                indicator.estado === 'Disponible' ||
                indicator.estado === 'disponible' ||
                indicator.estado === '#059669' ||
                indicator.estado === '#10b981'
            );

            console.log('🔍 DEBUG renderizarInformacionLibre - Estado del espacio disponible:', espacioDisponible, 'Estado indicator:', indicator?.estado);

            // IMPORTANTE: Si el indicator dice que está ocupado, aunque la API diga "libre",
            // mantener el botón desocupar visible para permitir desocupación forzosa
            // Esto sucede cuando hay reservas vencidas o estados inconsistentes
            if (!espacioDisponible) {
                console.log('🔧 CASO ESPECIAL: API dice libre pero indicator dice ocupado - Manteniendo botón desocupar');
                
                // Asegurar que el botón desocupar esté visible para espacios "ocupados" según indicator
                const btnsDesocupar = document.querySelectorAll('.btn-desocupar[data-tipo="espacio"]');
                btnsDesocupar.forEach(btn => {
                    btn.classList.remove('hidden');
                    console.log('🔧 Botón desocupar forzado a visible - Indicator dice ocupado');
                });

                // Crear o actualizar el input RUN para desocupación forzosa
                const runInput = document.querySelector('#run-ocupante-modal');
                const runValue = data.run_profesor || data.run_solicitante || `FORCE_${indicator.id}`;
                
                if (runInput) {
                    runInput.value = runValue;
                } else {
                    const newInput = document.createElement('input');
                    newInput.type = 'hidden';
                    newInput.id = 'run-ocupante-modal';
                    newInput.value = runValue;
                    if (elements.ocupanteInfo) {
                        elements.ocupanteInfo.appendChild(newInput);
                    }
                }
                console.log('🔍 RUN para desocupación forzosa configurado:', runValue);
                
                // Mostrar información del último ocupante o información mínima
                if (tituloEl) tituloEl.textContent = 'Último Ocupante';
                
                if (elements.ocupanteContainer) {
                    elements.ocupanteContainer.style.display = 'block';
                    if (elements.ocupanteInfo) {
                        const infoHtml = `
                            <input type="hidden" id="run-ocupante-modal" value="${runValue}">
                            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                                <div class="flex items-center">
                                    <i class="mr-3 text-orange-500 fas fa-exclamation-triangle"></i>
                                    <div>
                                        <div class="font-medium text-gray-800">Estado inconsistente</div>
                                        <div class="text-sm text-gray-600">El espacio requiere desocupación manual</div>
                                    </div>
                                </div>
                                ${data.hora_inicio ? `
                                <div class="flex items-center">
                                    <i class="mr-3 text-gray-500 fas fa-clock"></i>
                                    <div>
                                        <div class="font-medium text-gray-800">${data.hora_inicio}</div>
                                        <div class="text-sm text-gray-600">Última hora de inicio</div>
                                    </div>
                                </div>
                                ` : ''}
                            </div>
                        `;
                        elements.ocupanteInfo.innerHTML = infoHtml;
                    }
                }
                
                // Ocultar próxima clase en este caso especial
                if (elements.proximaClaseContainer) {
                    elements.proximaClaseContainer.style.display = 'none';
                }
                
                // IMPORTANTE: Retornar aquí para evitar que el código posterior oculte el botón
                return;
            }

            // Si el espacio está disponible, NO mostrar información de ocupante ni botón desocupar
            if (espacioDisponible) {
                console.log('🟢 Espacio disponible - Ocultando ocupante, mostrando próxima clase');
                if (tituloEl) tituloEl.textContent = 'Ocupante Actual';

                // Ocultar completamente el contenedor de ocupante
                if (elements.ocupanteContainer) {
                    elements.ocupanteContainer.style.display = 'none';
                }

                // Ocultar botón desocupar
                const btnsDesocupar = document.querySelectorAll('.btn-desocupar');
                btnsDesocupar.forEach(btn => btn.classList.add('hidden'));

                // SIEMPRE intentar mostrar próxima clase/reserva si existe
                if (data.proxima_clase && elements.proximaClaseContainer && elements.proximaClaseInfo) {
                    console.log('📅 Mostrando próxima clase:', data.proxima_clase);
                    elements.proximaClaseContainer.style.display = 'block';
                    elements.proximaClaseInfo.innerHTML = `
                        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                            <div class="flex items-center">
                                <i class="mr-3 text-purple-500 fas fa-calendar"></i>
                                <div>
                                    <div class="font-medium text-gray-800">${data.proxima_clase.asignatura || 'Sin asignatura'}</div>
                                    <div class="text-sm text-gray-600">Próxima asignatura</div>
                                </div>
                            </div>
                            <div class="flex items-center">
                                <i class="mr-3 text-purple-500 fas fa-user-tie"></i>
                                <div>
                                    <div class="font-medium text-gray-800">${data.proxima_clase.profesor || 'No especificado'}</div>
                                    <div class="text-sm text-gray-600">Próximo profesor</div>
                                </div>
                            </div>
                            ${data.proxima_clase.hora_inicio ? `
                            <div class="flex items-center">
                                <i class="mr-3 text-green-500 fas fa-clock"></i>
                                <div>
                                    <div class="font-medium text-gray-800">${data.proxima_clase.hora_inicio}</div>
                                    <div class="text-sm text-gray-600">Hora de inicio</div>
                                </div>
                            </div>
                            ` : ''}
                            ${data.proxima_clase.modulo ? `
                            <div class="flex items-center">
                                <i class="mr-3 text-blue-500 fas fa-bookmark"></i>
                                <div>
                                    <div class="font-medium text-gray-800">${data.proxima_clase.modulo}</div>
                                    <div class="text-sm text-gray-600">Módulo</div>
                                </div>
                            </div>
                            ` : ''}
                        </div>
                    `;
                } else {
                    // No hay próxima clase - ocultar el contenedor
                    console.log('❌ No hay próxima clase para mostrar');
                    if (elements.proximaClaseContainer) {
                        elements.proximaClaseContainer.style.display = 'none';
                    }
                }

                return; // Salir aquí para espacios disponibles
            }

            // Si llegamos aquí, el espacio NO está disponible, por lo tanto puede mostrar último ocupante
            if (!data.proxima_clase) {
                // No mostrar información si es solo informativo o no hay datos relevantes
                const esInformativo = data.tipo_reserva === 'info' ||
                                    data.estado === 'info' ||
                                    (!data.run_profesor && !data.run_solicitante && !data.hora_inicio);

                if (tituloEl) tituloEl.textContent = 'Último Ocupante';

                // Solo mostrar contenedor si hay datos del último ocupante Y no es solo informativo
                if (elements.ocupanteContainer && !esInformativo &&
                    (data.nombre || data.detalles || data.hora_inicio || data.hora_salida || data.run_solicitante)) {
                    elements.ocupanteContainer.style.display = 'block';
                    // Reutilizar la plantilla de "ocupado sin info" para mostrar detalles mínimos
                    elements.ocupanteInfo.innerHTML = `
                        <input type="hidden" id="run-ocupante-modal" value="${data.run_profesor || data.run_solicitante || ''}">
                        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                            <div class="flex items-center">
                                <i class="mr-3 text-gray-500 fas fa-user"></i>
                                <div>
                                    <div class="font-medium text-gray-800">${data.nombre || 'Último ocupante'}</div>
                                    <div class="text-sm text-gray-600">Último registro</div>
                                </div>
                            </div>
                            ${data.run_solicitante ? `
                            <div class="flex items-center">
                                <i class="mr-3 text-blue-500 fas fa-id-card"></i>
                                <div>
                                    <div class="font-medium text-gray-800">${data.run_solicitante}</div>
                                    <div class="text-sm text-gray-600">RUN</div>
                                </div>
                            </div>
                            ` : ''}
                            ${data.hora_inicio ? `
                            <div class="flex items-center">
                                <i class="mr-3 text-gray-500 fas fa-clock"></i>
                                <div>
                                    <div class="font-medium text-gray-800">${data.hora_inicio}</div>
                                    <div class="text-sm text-gray-600">Hora inicio</div>
                                </div>
                            </div>
                            ` : ''}
                            ${data.hora_salida ? `
                            <div class="flex items-center">
                                <i class="mr-3 text-gray-500 fas fa-clock"></i>
                                <div>
                                    <div class="font-medium text-gray-800">${data.hora_salida}</div>
                                    <div class="text-sm text-gray-600">Hora salida</div>
                                </div>
                            </div>
                            ` : ''}
                        </div>
                    `;
                } else {
                    // No hay datos históricos válidos; ocultar contenedor
                    if (elements.ocupanteContainer) elements.ocupanteContainer.style.display = 'none';
                    if (tituloEl) tituloEl.textContent = 'Ocupante Actual';
                }
            } else {
                // Hay próxima clase, restaurar título y ocultar contenedor de último ocupante
                if (tituloEl) tituloEl.textContent = 'Ocupante Actual';
                if (elements.ocupanteContainer) elements.ocupanteContainer.style.display = 'none';
            }

            // Mostrar próxima clase si existe
            if (data.proxima_clase && elements.proximaClaseContainer && elements.proximaClaseInfo) {
                elements.proximaClaseContainer.style.display = 'block';
                elements.proximaClaseInfo.innerHTML = `
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <div class="flex items-center">
                            <i class="mr-3 text-purple-500 fas fa-calendar"></i>
                            <div>
                                <div class="font-medium text-gray-800">${data.proxima_clase.asignatura || 'Sin asignatura'}</div>
                                <div class="text-sm text-gray-600">Próxima asignatura</div>
                            </div>
                        </div>
                        <div class="flex items-center">
                            <i class="mr-3 text-purple-500 fas fa-user-tie"></i>
                            <div>
                                <div class="font-medium text-gray-800">${data.proxima_clase.profesor || 'No especificado'}</div>
                                <div class="text-sm text-gray-600">Próximo profesor</div>
                            </div>
                        </div>
                    </div>
                `;
            }

            // Solo ocultar botón desocupar si el espacio está realmente disponible
            if (espacioDisponible) {
                console.log('🔍 Espacio realmente disponible - Ocultando botón desocupar');
                const btnsDesocupar = document.querySelectorAll('.btn-desocupar');
                btnsDesocupar.forEach(btn => btn.classList.add('hidden'));
            } else {
                console.log('🔍 Espacio ocupado según indicator - Manteniendo botón desocupar visible');
            }
        }

        // Funciones para el modal de confirmación de asistentes
        function mostrarModalAsistentes(infoClase, idReserva, idEspacio) {
            const modal = document.getElementById('modal-confirmar-asistentes');
            if (!modal) {
                console.error('❌ Modal no encontrado en el DOM');
                return;
            }

            console.log('🎯 Mostrando modal de asistentes', { infoClase, idReserva, idEspacio });

            // Llenar información en el modal
            document.getElementById('asistentes-espacio').textContent = idEspacio;
            document.getElementById('asistentes-asignatura').textContent = infoClase.asignatura || 'No especificada';
            document.getElementById('asistentes-modulos').textContent = `${infoClase.total_modulos} módulos`;

            // Mostrar el modal
            modal.classList.remove('hidden');
            modal.style.display = 'flex';

            // Configurar eventos de los botones
            const btnSinAsistentes = document.getElementById('btn-sin-asistentes');
            const btnConAsistentes = document.getElementById('btn-con-asistentes');

            // Remover listeners antiguos si existen
            const newBtnSin = btnSinAsistentes.cloneNode(true);
            const newBtnCon = btnConAsistentes.cloneNode(true);
            btnSinAsistentes.parentNode.replaceChild(newBtnSin, btnSinAsistentes);
            btnConAsistentes.parentNode.replaceChild(newBtnCon, btnConAsistentes);

            // Agregar nuevos listeners
            newBtnSin.addEventListener('click', () => {
                registrarAsistencia(idReserva, false);
                cerrarModalAsistentes();
            });

            newBtnCon.addEventListener('click', () => {
                registrarAsistencia(idReserva, true);
                cerrarModalAsistentes();
            });
        }

        function cerrarModalAsistentes() {
            const modal = document.getElementById('modal-confirmar-asistentes');
            if (modal) {
                modal.classList.add('hidden');
                modal.style.display = 'none';
            }

            // Mostrar mensaje de éxito de devolución
            Swal.fire({
                title: '¡Devolución Exitosa!',
                text: 'Las llaves han sido devueltas correctamente.',
                icon: 'success',
                confirmButtonText: 'Aceptar',
                confirmButtonColor: '#059669',
                timer: 1500,
                timerProgressBar: true,
                showConfirmButton: false
            });

            // Restaurar el input QR activo
            setTimeout(() => {
                if (qrInputManager) {
                    qrInputManager.setActiveInput('main');
                }
            }, 300);
        }

        async function registrarAsistencia(idReserva, huboAsistentes) {
            try {
                const response = await fetch('/api/registrar-asistencia-clase', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        id_reserva: idReserva,
                        hubo_asistentes: huboAsistentes
                    })
                });

                const data = await response.json();
                
                if (!data.success) {
                    console.error('Error al registrar asistencia:', data.mensaje);
                }
            } catch (error) {
                console.error('Error en registrarAsistencia:', error);
            }
        }

        function cerrarModalEspacio() {
            const modal = document.getElementById('modal-espacio-info');
            if (modal) {
                modal.classList.add('hidden');
            }
            // Restaurar el input QR activo usando el gestor
            setTimeout(() => {
                qrInputManager.restaurarInputActivo();
            }, 200);
        }

        function cerrarModalesDespuesDeSwal(modales = []) {
            // Solo cerrar modales sin mostrar SweetAlert (ya se muestra en otro lugar)
            modales.forEach(nombre => {
                window.dispatchEvent(new CustomEvent('close-modal', {
                    detail: nombre
                }));
                setTimeout(() => {
                    document.querySelectorAll(`[data-modal="${nombre}"]`).forEach(el => el
                        .classList.add('hidden'));
                }, 200);
            });

            // Restaurar el input QR activo después de cerrar todos los modales
            setTimeout(() => {
                qrInputManager.restaurarInputActivo();
            }, 300);
        }

        async function actualizarColoresEspacios(forzarActualizacion = false) {
            // Verificar si ha habido cambios locales recientes (salvo si se fuerza)
            const tiempoTranscurrido = Date.now() - (state.ultimoCambioLocal || 0);
            if (!forzarActualizacion && tiempoTranscurrido < 10000) { // 10 segundos
                return;
            }

            try {
                const response = await fetch(`/plano/${mapaId}/bloques?t=${Date.now()}`); // Cache busting
                if (!response.ok) {
                    console.error('Error al obtener bloques actualizados:', response.status);
                    return;
                }

                const data = await response.json();
                if (!data.bloques || !Array.isArray(data.bloques)) {
                    console.error('Respuesta de bloques inválida:', data);
                    return;
                }

                let cambiosDetectados = [];

                data.bloques.forEach(nuevoBloque => {
                    const bloqueExistente = state.indicators.find(b => b.id === nuevoBloque.id);
                    if (bloqueExistente && bloqueExistente.estado !== nuevoBloque.estado) {
                        console.log(`Actualizando espacio ${nuevoBloque.id}: ${bloqueExistente.estado} → ${nuevoBloque.estado}`);
                        bloqueExistente.estado = nuevoBloque.estado;
                        bloqueExistente.color = nuevoBloque.color || bloqueExistente.color;
                        cambiosDetectados.push({
                            id: nuevoBloque.id,
                            estadoAnterior: bloqueExistente.estado,
                            estadoNuevo: nuevoBloque.estado
                        });
                    }
                });

                if (cambiosDetectados.length > 0 || forzarActualizacion) {
                    state.originalCoordinates = state.indicators.map(i => ({ ...i }));
                    drawIndicators();
                }
            } catch (error) {
                // Error silencioso para no saturar la consola
            }
        }

        document.addEventListener('DOMContentLoaded', function () {
            const modal = document.getElementById('modal-solicitar-espacio');
            if (modal) {
                modal.addEventListener('show.bs.modal', function () {
                    actualizarHora();
                    actualizarModuloYColores();
                });
            }
                // Iniciar polling para sincronizar estados entre máquinas
                startEstadoPolling();
        });

            // Polling que consulta el endpoint de estados cada 5s si la pestaña está visible
            let estadoPollingInterval = null;
            function startEstadoPolling(intervalMs = 5000) {
                if (estadoPollingInterval) return;
                estadoPollingInterval = setInterval(async () => {
                    // No consultar si la pestaña está en background
                    if (document.hidden) return;

                    // Evitar consultar si hubo cambios locales recientes
                    const tiempoTranscurrido = Date.now() - (state.ultimoCambioLocal || 0);
                    if (tiempoTranscurrido < 5000) return;

                    try {
                        const res = await fetch('/api/espacios/estados');
                        if (!res.ok) return;
                        const json = await res.json();
                        if (!json.success) return;

                        // Actualizar state.indicators según respuesta
                        const nuevos = json.espacios || [];
                        let cambios = false;
                        nuevos.forEach(item => {
                            const indicador = state.indicators.find(i => i.id === item.id_espacio);
                            if (indicador && indicador.estado !== item.estado) {
                                indicador.estado = item.estado;
                                cambios = true;
                            }
                        });

                        if (cambios) {
                            drawIndicators();
                        }
                    } catch (err) {
                        // Silenciar errores de red
                    }
                }, intervalMs);
            }

        document.addEventListener("DOMContentLoaded", function () {

            // Configurar el input del escáner QR
            const inputEscanner = document.getElementById('qr-input');
            if (inputEscanner) {
                inputEscanner.addEventListener('keydown', handleScan);
                document.addEventListener('click', function (event) {
                    // Solo enfocar si no se está haciendo clic en un modal o formulario
                    if (!event.target.closest('.modal') &&
                        !event.target.closest('form') &&
                        !event.target.closest('input') &&
                        !event.target.closest('select') &&
                        !event.target.closest('button')) {
                        qrInputManager.setActiveInput('main');
                    }
                });
                inputEscanner.focus();
                document.getElementById('qr-status').innerHTML = 'Esperando... Escanea el código QR';
                // Asegurar que la interfaz esté en estado inicial
                limpiarEstadoCompleto();

                // Sistema QR inicializado
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
            // Configurar el formulario de registro de solicitante
            const formRegistroSolicitante = document.getElementById('form-registro-solicitante');
            if (formRegistroSolicitante) {
                formRegistroSolicitante.addEventListener('submit', procesarRegistroSolicitante);
            }



            // Configurar botón de solicitar llaves para cambiar al input correspondiente
            const btnSolicitar = document.querySelector('[onclick*="solicitarLlaves"]');
            if (btnSolicitar) {
                btnSolicitar.addEventListener('click', function() {
                    qrInputManager.setActiveInput('solicitud');
                });
            }


            // Configurar event listeners para los campos del formulario de registro de solicitante
            const camposSolicitante = [
                'nombre-solicitante',
                'email-solicitante',
                'telefono-solicitante'
            ];

            camposSolicitante.forEach(campoId => {
                const campo = document.getElementById(campoId);
                if (campo) {
                    // Cuando se haga clic en un campo, desactivar todos los inputs QR
                    campo.addEventListener('click', function() {
                        qrInputManager.desactivarTodosLosInputs();
                    });

                    // Cuando se haga foco en un campo, desactivar todos los inputs QR
                    campo.addEventListener('focus', function() {
                        qrInputManager.desactivarTodosLosInputs();
                    });
                }
            });

            // Configurar validación en tiempo real para el teléfono
            const campoTelefono = document.getElementById('telefono-solicitante');
            if (campoTelefono) {
                campoTelefono.addEventListener('input', function(e) {
                    // Permitir solo números
                    e.target.value = e.target.value.replace(/[^0-9]/g, '');

                    // Validar longitud y mostrar feedback visual
                    const valor = e.target.value;
                    const esValido = /^[0-9]{8,9}$/.test(valor);

                    if (valor.length > 0 && !esValido) {
                        e.target.classList.add('border-red-500');
                        e.target.classList.remove('border-gray-300');
                    } else {
                        e.target.classList.remove('border-red-500');
                        e.target.classList.add('border-gray-300');
                    }
                });

                // Validación al perder el foco
                campoTelefono.addEventListener('blur', function(e) {
                    const valor = e.target.value;
                    if (valor.length > 0 && !/^[0-9]{8,9}$/.test(valor)) {
                        e.target.classList.add('border-red-500');
                        // Opcional: mostrar mensaje de error
                    }
                });
            }

            // Configurar event listener específico para el select de tipo solicitante
            const selectTipoSolicitante = document.getElementById('tipo-solicitante');
            if (selectTipoSolicitante) {
                // Desactivar todos los inputs QR cuando se abra el select
                selectTipoSolicitante.addEventListener('mousedown', function() {
                    qrInputManager.desactivarTodosLosInputs();
                });

                // Prevenir que el select se cierre inmediatamente
                selectTipoSolicitante.addEventListener('click', function(e) {
                    e.stopPropagation();
                });

                // Asegurar que el select mantenga el foco
                selectTipoSolicitante.addEventListener('focus', function() {
                    qrInputManager.desactivarTodosLosInputs();
                });
            }
            // Configurar botón devolver
            const btnDevolver = document.getElementById('btnDevolver');
            const areaQR = document.getElementById('area-qr-devolucion');
            const inputQR = document.getElementById('qr-input-devolucion');
            const lineaDiv = document.getElementById('linea-divisoria-qr');
            if (btnDevolver && areaQR && inputQR && lineaDiv) {
                btnDevolver.addEventListener('click', function () {
                    areaQR.classList.remove('hidden');
                    lineaDiv.classList.remove('hidden');
                    // Cambiar al input de devolución
                    qrInputManager.setActiveInput('devolucion');
                });
            }
            window.actualizarColoresEspacios = actualizarColoresEspacios;

            // Configurar intervalos para actualizar hora y módulo
            setInterval(actualizarHora, 1000);
            actualizarHora();

            setInterval(actualizarModuloYColores, 5000);
            actualizarModuloYColores();
        });

        async function procesarRegistroSolicitante(event) {
            event.preventDefault();

            const formData = new FormData(event.target);
            const datosSolicitante = {
                run_solicitante: runSolicitantePendiente,
                nombre: formData.get('nombre'),
                correo: formData.get('email'),
                telefono: formData.get('telefono'),
                tipo_solicitante: formData.get('tipo_solicitante')
            };

            // Validación básica
            if (!datosSolicitante.nombre || !datosSolicitante.correo || !datosSolicitante.telefono || !datosSolicitante.tipo_solicitante) {
                Swal.fire({
                    title: 'Error de Validación',
                    text: 'Por favor, complete todos los campos requeridos.',
                    icon: 'error',
                    confirmButtonText: 'Entendido'
                });
                return;
            }

            // Validación adicional del teléfono
            const telefonoPattern = /^[0-9]{8,9}$/;
            if (!telefonoPattern.test(datosSolicitante.telefono)) {
                Swal.fire({
                    title: 'Teléfono Inválido',
                    text: 'Por favor, ingrese un número de teléfono válido (8-9 dígitos, sin el +56).',
                    icon: 'error',
                    confirmButtonText: 'Entendido'
                });
                return;
            }

            try {
                // Mostrar loading
                Swal.fire({
                    title: 'Registrando Solicitante',
                    text: 'Por favor, espere...',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                const resultado = await registrarSolicitante(datosSolicitante);

                if (resultado && resultado.success) {
                    // Mostrar SweetAlert de éxito
                    Swal.fire({
                        title: '¡Solicitante Registrado!',
                        text: `${datosSolicitante.nombre} ha sido registrado exitosamente. Ahora puede escanear el QR del espacio deseado.`,
                        icon: 'success',
                        confirmButtonText: 'Continuar',
                        timer: 3000,
                        timerProgressBar: true
                    }).then(() => {
                        // Cerrar modal de registro después de que termine el SweetAlert
                        cerrarModalRegistroSolicitante();

                        // Restaurar el input QR activo
                        qrInputManager.setActiveInput('main');
                    });

                    // Actualizar información en la interfaz
                    document.getElementById('qr-status').innerHTML = 'Solicitante registrado. Escanee el QR del espacio.';
                    mostrarInfo('usuario', datosSolicitante.nombre, runSolicitantePendiente);

                    // Continuar con el flujo - solo necesita escanear espacio
                    usuarioEscaneado = runSolicitantePendiente;
                    ordenEscaneo = 'espacio'; // Ya no necesita escanear usuario

                    // Limpiar variables
                    runSolicitantePendiente = null;

                } else {
                    // Error al registrar solicitante
                    const mensajeError = resultado?.mensaje || 'No se pudo registrar el solicitante. Intente nuevamente.';
                    Swal.fire({
                        title: 'Error al Registrar',
                        text: mensajeError,
                        icon: 'error',
                        confirmButtonText: 'Intentar Nuevamente'
                    }).then(() => {
                        // Cerrar modal también en caso de error
                        cerrarModalRegistroSolicitante();
                    });
                }
            } catch (error) {
                // Error al procesar el registro
                console.error('Error al procesar registro:', error);
                Swal.fire({
                    title: 'Error de Conexión',
                    text: 'Ocurrió un error al comunicarse con el servidor. Verifique su conexión e intente nuevamente.',
                    icon: 'error',
                    confirmButtonText: 'Reintentar'
                }).then(() => {
                    // Cerrar modal también en caso de error de conexión
                    cerrarModalRegistroSolicitante();
                });
            }
        }



        function cancelarRegistroSolicitante() {
            // Cerrar modal de registro
            window.dispatchEvent(new CustomEvent('close-modal', {
                detail: 'registro-solicitante'
            }));

            // Método adicional: Forzar cierre si el evento no funciona
            setTimeout(() => {
                const modalElement = document.querySelector('[x-data*="modalComponent"]');
                if (modalElement && modalElement._x_dataStack) {
                    const alpineData = modalElement._x_dataStack[0];
                    if (alpineData && typeof alpineData.show !== 'undefined') {
                        alpineData.show = false;
                    }
                }
            }, 100);

            // Restaurar el input QR activo usando el gestor
            setTimeout(() => {
                qrInputManager.restaurarInputActivo();
            }, 200);

            // Limpiar variables
            runSolicitantePendiente = null;

            // Resetear estado
            document.getElementById('qr-status').innerHTML = 'Esperando';
            limpiarEstadoCompleto();
        }

        function cerrarModalRegistroSolicitante() {
            // Disparar evento de cierre del modal
            window.dispatchEvent(new CustomEvent('close-modal', {
                detail: 'registro-solicitante'
            }));

            // Método adicional: Forzar cierre si el evento no funciona
            setTimeout(() => {
                const modalElement = document.querySelector('[x-data*="modalComponent"]');
                if (modalElement && modalElement._x_dataStack) {
                    const alpineData = modalElement._x_dataStack[0];
                    if (alpineData && typeof alpineData.show !== 'undefined') {
                        alpineData.show = false;
                    }
                }
            }, 100);

            // Restaurar el input QR activo usando el gestor
            setTimeout(() => {
                qrInputManager.restaurarInputActivo();
            }, 200);

            // Limpiar variables
            runSolicitantePendiente = null;

            // Resetear estado
            document.getElementById('qr-status').innerHTML = 'Esperando';
            limpiarEstadoCompleto();
        }






        // Asegurar que indicators sea siempre un array
        if (!state.indicators || !Array.isArray(state.indicators)) {
            state.indicators = [];
        }
        if (!state.originalCoordinates || !Array.isArray(state.originalCoordinates)) {
            state.originalCoordinates = [];
        }

        function cerrarModalModulos() {
            // Ocultar directamente el modal
            const modal = document.getElementById('modal-seleccionar-modulos');
            if (modal) {
                modal.classList.add('hidden');
            }

            // También intentar con el selector de data-modal
            const modalAlt = document.querySelector('[data-modal="seleccionar-modulos"]');
            if (modalAlt) {
                modalAlt.classList.add('hidden');
            }

            // Restaurar el input QR activo usando el gestor
            setTimeout(() => {
                qrInputManager.restaurarInputActivo();
            }, 200);

            // Limpiar variables
            espacioParaReserva = null;
            runParaReserva = null;

            // Resetear interfaz
            limpiarEstadoCompleto();
        }

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

                // Enviando parámetros al servidor, incluyendo información sobre si estamos en break
                const moduloParaReserva = obtenerModuloParaReserva(horaActual);
                const response = await fetch(
                    `/api/espacio/${idEspacio}/modulos-disponibles?hora_actual=${horaActual}&dia_actual=${diaActual}&modulo_solicitado=${moduloParaReserva}&permitir_breaks=true`
                );

                if (response.ok) {
                    const data = await response.json();

                            if (data.success) {
            // Guardar información adicional para mostrar en el modal
            window.modulosInfo = {
                max_modulos: data.max_modulos || 1,
                modulo_actual: data.modulo_actual,
                modulos_disponibles: data.modulos_disponibles || [],
                proxima_clase: data.proxima_clase,
                clases_proximas: data.clases_proximas || [],
                detalles: data.detalles
            };

            return data.max_modulos || 1;
        } else {
            // Mostrar información detallada del error
            if (data.detalles && data.detalles.razon === 'fuera_horario') {
                // Horario no disponible
            }
            return 1;
        }
                } else {
                    // Error en la respuesta del servidor
                    return 1;
                }
            } catch (error) {
                // Error al calcular módulos disponibles
                return 1;
            }
        }

        async function mostrarModalSeleccionarModulos(idEspacio, run, maxModulos = 2) {
                    const modulosDisponibles = await calcularModulosDisponibles(idEspacio);

        // Limitar a máximo 2 módulos según la lógica del negocio
        maxModulosDisponibles = Math.min(modulosDisponibles, maxModulos);

        // Actualizar elementos del modal si existen
        const maxModulosElement = document.getElementById('max-modulos-disponibles');
        const inputModulos = document.getElementById('input-cantidad-modulos');

        if (maxModulosElement) {
            maxModulosElement.textContent = maxModulosDisponibles;
        }

        if (inputModulos) {
            inputModulos.max = maxModulosDisponibles;
            inputModulos.value = 1;
        }

        espacioParaReserva = idEspacio;
        runParaReserva = run;

                    // Mostrar información detallada si está disponible
        if (window.modulosInfo) {
            mostrarInformacionModulos(window.modulosInfo);
        }

        // Mostrar el modal directamente
        const modal = document.getElementById('modal-seleccionar-modulos');
        if (modal) {
            modal.classList.remove('hidden');
            // Desactivar todos los inputs QR cuando se abre el modal
            qrInputManager.desactivarTodosLosInputs();
            // Enfocar el input
            setTimeout(() => {
                if (inputModulos) {
                    inputModulos.focus();
                }
            }, 100);
        }
        }

        function mostrarInformacionModulos(info) {
            const infoContainer = document.getElementById('info-modulos-disponibles');
            if (!infoContainer) return;

                    // Información de módulos recibida

            let html = '<div class="p-4 bg-white border-l-4 border-green-500 rounded-lg shadow-sm">';
            html += '<h3 class="mb-3 text-lg font-semibold text-gray-800">Información de Disponibilidad</h3>';

            // Información básica con validación
            const moduloActual = info.modulo_actual !== null && info.modulo_actual !== undefined ? info.modulo_actual : 'No disponible';
            const maxModulos = info.max_modulos || 0;

            html += '<div class="grid grid-cols-1 gap-4 mb-4 md:grid-cols-2">';
            html += `<div class="text-sm"><p class="font-medium text-gray-600">Módulo actual:</p><p class="font-semibold text-gray-800">${moduloActual}</p></div>`;
            html += `<div class="text-sm"><p class="font-medium text-gray-600">Módulos disponibles:</p><p class="font-semibold text-gray-800">${maxModulos}</p></div>`;
            html += '</div>';


        // Clases próximas con información básica
            if (info.clases_proximas && info.clases_proximas.length > 0) {
                html += '<div class="p-4 mb-4 border-l-4 border-blue-400 rounded-lg bg-blue-50">';
                html += '<h4 class="mb-3 text-sm font-semibold text-blue-800">Clases próximas:</h4>';
                info.clases_proximas.forEach((clase, index) => {
                    html += `<div class="p-3 mb-3 bg-white border rounded">`;
                    html += '<div class="grid grid-cols-1 gap-3 md:grid-cols-2">';
                    html += `<div class="text-sm"><p class="font-medium text-blue-700">Asignatura:</p><p class="text-blue-800">${clase.asignatura || 'Sin asignatura'}</p></div>`;
                    html += `<div class="text-sm"><p class="font-medium text-blue-700">Profesor:</p><p class="text-blue-800">${clase.profesor || 'No especificado'}</p></div>`;
                    html += '</div>';
                    html += '</div>';
                });
                html += '</div>';
            }

        html += '</div>';
            infoContainer.innerHTML = html;
        }

       document.addEventListener('DOMContentLoaded', function () {
    // Cargar horarios de módulos al inicio
    cargarHorariosModulos();
    
    const btnConfirmarModulos = document.getElementById('btn-confirmar-modulos');

    if (btnConfirmarModulos) {
        btnConfirmarModulos.addEventListener('click', async function () {
            const cantidad = parseInt(document.getElementById('input-cantidad-modulos').value);

            if (!espacioParaReserva || !runParaReserva) {
                return;
            }



            // Determinar el tipo de usuario y la ruta correcta
            let apiEndpoint = '/api/crear-reserva-solicitante';
            let tipoUsuario = 'solicitante';

            // Si tenemos información del usuario escaneado, usar su tipo real
            if (usuarioEscaneado && typeof usuarioInfo !== 'undefined' && usuarioInfo.tipo_usuario) {
                if (usuarioInfo.tipo_usuario === 'profesor') {
                    apiEndpoint = '/api/crear-reserva-profesor';
                    tipoUsuario = 'profesor';
                } else if (usuarioInfo.tipo_usuario === 'solicitante_registrado') {
                    apiEndpoint = '/api/crear-reserva-solicitante';
                    tipoUsuario = 'solicitante';
                }
            }


            // Preparar datos para la petición según el tipo de usuario
            let requestBody = {};

            // Obtener el módulo para la reserva (incluso durante breaks)
            const ahora = new Date();
            const horaActual = ahora.toLocaleTimeString('es-ES', {
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit'
            });
            const moduloParaReserva = obtenerModuloParaReserva(horaActual);

            if (tipoUsuario === 'profesor') {
                requestBody = {
                    run_profesor: runParaReserva,
                    id_espacio: espacioParaReserva,
                    modulo_solicitado: moduloParaReserva
                };
            } else {
                requestBody = {
                    run_solicitante: runParaReserva,
                    id_espacio: espacioParaReserva,
                    modulos: cantidad,
                    modulo_inicio: moduloParaReserva
                };
            }


            // Mostrar mensaje de proceso
            document.getElementById('qr-status').innerHTML = 'Creando reserva...';

            const response = await fetch(apiEndpoint, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify(requestBody)
            });

        const data = await response.json();


        if (data.success) {
            // Cerrar el modal inmediatamente
            cerrarModalModulos();

            // Mostrar Sweet Alert de éxito para reserva creada
            Swal.fire({
                title: '¡Reserva Creada!',
                text: 'La reserva ha sido creada exitosamente.',
                icon: 'success',
                confirmButtonText: 'Aceptar',
                confirmButtonColor: '#059669',
                timer: 1500,
                timerProgressBar: true,
                showConfirmButton: false
            });

            document.getElementById('qr-status').innerHTML = 'Reserva creada';
            document.getElementById('qr-status').classList.remove('parpadeo');

            // Limpiar estado después del Sweet Alert
            setTimeout(() => {
                limpiarEstadoCompleto();
                // Restaurar autofocus del qr-input después de crear reserva
                if (qrInputManager) {
                    qrInputManager.setActiveInput('main');
                }
            }, 2000);
        } else {
            let mensajeError = data.mensaje || 'No se pudo reservar';

            if (data.errors) {
                mensajeError = 'Errores de validación:\n';
                Object.keys(data.errors).forEach(field => {
                    data.errors[field].forEach(error => {
                        mensajeError += `• ${field}: ${error}\n`;
                    });
                });
            }

            // Mostrar SweetAlert de error
            Swal.fire({
                title: 'Error al Crear Reserva',
                text: mensajeError,
                icon: 'error',
                confirmButtonText: 'Entendido',
                confirmButtonColor: '#dc2626'
            });

            // Cerrar modal en caso de error
            cerrarModalModulos();

            // Restaurar autofocus del qr-input después de error en reserva
            setTimeout(() => {
                if (qrInputManager) {
                    qrInputManager.setActiveInput('main');
                }
            }, 300);
        }
        });
    }
});



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

            // Si no estamos en ningún módulo, buscar el siguiente módulo disponible
            // Esto permite hacer reservas durante los breaks
            for (const [modulo, horario] of Object.entries(horariosDia)) {
                if (hora < horario.inicio) {
                    return parseInt(modulo); // Retornar el siguiente módulo
                }
            }

            return null;
        }

        function obtenerModuloParaReserva(hora) {
            const diaActual = obtenerDiaActual();
            const horariosDia = horariosModulos[diaActual];

            if (!horariosDia) return null;

            // Primero verificar si estamos en un módulo activo
            for (const [modulo, horario] of Object.entries(horariosDia)) {
                if (hora >= horario.inicio && hora < horario.fin) {
                    return parseInt(modulo);
                }
            }

            // Si estamos en break, buscar el siguiente módulo
            for (const [modulo, horario] of Object.entries(horariosDia)) {
                if (hora < horario.inicio) {
                    return parseInt(modulo);
                }
            }

            // Si es después del último módulo del día, permitir reserva para el primer módulo del día siguiente
            return 1;
        }

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

        function formatearHora(horaCompleta) {
            return horaCompleta.slice(0, 5);
        }

        async function actualizarModuloYColores() {
            const ahora = new Date();
            const horaActual = ahora.toLocaleTimeString('es-ES', {
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit'
            });

            // Determinar el módulo actual
            const moduloActual = moduloActualNum(horaActual);
            const moduloParaReserva = obtenerModuloParaReserva(horaActual);
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
                // Estamos en un break entre módulos
                if (moduloActualElement) {
                    if (moduloParaReserva) {
                        moduloActualElement.textContent = `Break (Próximo: ${moduloParaReserva})`;
                    } else {
                        moduloActualElement.textContent = 'Break entre módulos';
                    }
                }
                if (moduloHorarioElement) {
                    moduloHorarioElement.textContent = 'Reservas disponibles';
                }
            }

                    // Actualizar colores de los indicadores desde el servidor
        await actualizarColoresEspacios();
    }

    // Agregar funcionalidad para cerrar modales con la tecla Escape
    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            // Cerrar modal de espacio si está abierto
            const modalEspacio = document.getElementById('modal-espacio-info');
            if (modalEspacio && !modalEspacio.classList.contains('hidden')) {
                cerrarModalEspacio();
                return;
            }

            // Cerrar modal de módulos si está abierto
            const modalModulos = document.getElementById('modal-seleccionar-modulos');
            if (modalModulos && !modalModulos.classList.contains('hidden')) {
                cerrarModalModulos();
                return;
            }

            // Cerrar modal de registro de solicitante si está abierto
            const modalRegistro = document.querySelector('[data-modal="registro-solicitante"]');
            if (modalRegistro && !modalRegistro.classList.contains('hidden')) {
                cerrarModalRegistroSolicitante();
                return;
            }

            // Cerrar modales de Livewire si están abiertos
            const modalesLivewire = document.querySelectorAll('[data-modal]');
            modalesLivewire.forEach(modal => {
                if (!modal.classList.contains('hidden')) {
                    // Disparar evento para cerrar modal de Livewire
                    window.dispatchEvent(new CustomEvent('close-modal', {
                        detail: modal.getAttribute('data-modal')
                    }));
                }
            });
        }
    });

    // Escuchar cambios en localStorage para sincronizar mapa entre pestañas (p.ej. eliminación de reservas)
    window.addEventListener('storage', function (event) {
        if (!event.key) return;
        if (event.key === 'reserva_eliminada') {
            try {
                const payload = JSON.parse(event.newValue);
                const espacioKey = `espacio_${payload.id_espacio}`;
                sessionStorage.removeItem(espacioKey);
                sessionStorage.removeItem(`${espacioKey}_time`);

                if (typeof actualizarModuloYColores === 'function') {
                    actualizarModuloYColores();
                }
            } catch (err) {
                console.error('Error procesando evento storage reserva_eliminada', err);
            }
        }
    });

    // ==========================================
    // FUNCIONES PARA SALA DE ESTUDIO
    // ==========================================

    let salaEstudioState = {
        espacioId: null,
        espacioNombre: null,
        capacidadMaxima: 0,
        asistentes: [],
        modalAbierto: false
    };

    /**
     * Abre el modal para registrar asistentes de sala de estudio
     */
    function abrirModalSalaEstudio(espacio) {
        console.log('🎯 Abriendo modal sala de estudio para:', espacio);
        
        // Resetear estado
        salaEstudioState = {
            espacioId: espacio.id,
            espacioNombre: espacio.nombre || espacio.id,
            capacidadMaxima: espacio.capacidad || 0,
            asistentes: [],
            modalAbierto: true
        };

        // Actualizar UI del modal
        document.getElementById('modal-sala-estudio-titulo').textContent = salaEstudioState.espacioNombre;
        document.getElementById('sala-capacidad-maxima').textContent = salaEstudioState.capacidadMaxima;
        document.getElementById('sala-asistentes-count').textContent = '0';
        document.getElementById('sala-progreso-bar').style.width = '0%';
        
        // Limpiar lista de asistentes
        document.getElementById('lista-asistentes-sala').innerHTML = '<p class="text-sm text-gray-500 italic">No hay asistentes registrados aún</p>';
        
        // Deshabilitar botón de registro
        document.getElementById('btn-registrar-asistencia-sala').disabled = true;

        // Mostrar modal
        const modal = document.getElementById('modal-sala-estudio');
        if (modal) {
            modal.classList.remove('hidden');
            
            // Enfocar input QR
            setTimeout(() => {
                const qrInput = document.getElementById('qr-input-sala-estudio');
                if (qrInput) {
                    qrInput.value = '';
                    qrInput.focus();
                }
            }, 150);
        }
    }

    /**
     * Cierra el modal de sala de estudio
     */
    function cerrarModalSalaEstudio() {
        const modal = document.getElementById('modal-sala-estudio');
        if (modal) {
            modal.classList.add('hidden');
        }
        
        salaEstudioState.modalAbierto = false;
        
        // Volver a enfocar el input principal
        setTimeout(() => {
            const qrInput = document.getElementById('qr-input');
            if (qrInput) {
                qrInput.focus();
            }
        }, 100);
    }

    /**
     * Agrega un asistente a la lista
     */
    function agregarAsistenteSalaEstudio(run, nombre) {
        // Verificar si ya está registrado
        if (salaEstudioState.asistentes.some(a => a.run === run)) {
            Swal.fire({
                icon: 'warning',
                title: 'Asistente ya registrado',
                text: `${nombre} ya fue registrado en esta sala`,
                confirmButtonColor: '#ec4899'
            });
            return false;
        }

        // Verificar capacidad
        if (salaEstudioState.asistentes.length >= salaEstudioState.capacidadMaxima) {
            Swal.fire({
                icon: 'error',
                title: 'Capacidad completa',
                text: 'La sala ha alcanzado su capacidad máxima',
                confirmButtonColor: '#ec4899'
            });
            return false;
        }

        // Agregar asistente
        salaEstudioState.asistentes.push({ run, nombre });
        
        // Actualizar UI
        actualizarListaAsistentes();
        actualizarProgresoSala();
        
        // Mostrar confirmación
        const qrStatus = document.getElementById('qr-sala-status');
        if (qrStatus) {
            qrStatus.textContent = `✓ ${nombre} agregado`;
            qrStatus.classList.remove('parpadeo');
            
            setTimeout(() => {
                qrStatus.textContent = 'Esperando escaneo';
                qrStatus.classList.add('parpadeo');
            }, 2000);
        }

        return true;
    }

    /**
     * Actualiza la lista visual de asistentes
     */
    function actualizarListaAsistentes() {
        const listaContainer = document.getElementById('lista-asistentes-sala');
        
        if (salaEstudioState.asistentes.length === 0) {
            listaContainer.innerHTML = '<p class="text-sm text-gray-500 italic">No hay asistentes registrados aún</p>';
            return;
        }

        listaContainer.innerHTML = salaEstudioState.asistentes.map((asistente, index) => `
            <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg border border-gray-200">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 bg-pink-100 rounded-full flex items-center justify-center">
                        <span class="text-pink-600 font-semibold text-sm">${index + 1}</span>
                    </div>
                    <div>
                        <p class="font-medium text-gray-900">${asistente.nombre}</p>
                        <p class="text-xs text-gray-500">RUN: ${asistente.run}</p>
                    </div>
                </div>
                <button onclick="eliminarAsistenteSalaEstudio('${asistente.run}')" 
                    class="text-red-500 hover:text-red-700 transition-colors"
                    title="Eliminar asistente">
                    <i class="fas fa-times-circle"></i>
                </button>
            </div>
        `).join('');

        // Habilitar botón de registro si hay al menos un asistente
        document.getElementById('btn-registrar-asistencia-sala').disabled = false;
    }

    /**
     * Actualiza el progreso de ocupación de la sala
     */
    function actualizarProgresoSala() {
        const count = salaEstudioState.asistentes.length;
        const porcentaje = (count / salaEstudioState.capacidadMaxima) * 100;
        
        document.getElementById('sala-asistentes-count').textContent = count;
        document.getElementById('sala-progreso-bar').style.width = `${porcentaje}%`;
        
        // Cambiar color de la barra según ocupación
        const barra = document.getElementById('sala-progreso-bar');
        if (porcentaje >= 90) {
            barra.className = 'bg-red-500 h-2.5 rounded-full transition-all duration-300';
        } else if (porcentaje >= 70) {
            barra.className = 'bg-yellow-500 h-2.5 rounded-full transition-all duration-300';
        } else {
            barra.className = 'bg-pink-500 h-2.5 rounded-full transition-all duration-300';
        }
    }

    /**
     * Elimina un asistente de la lista
     */
    function eliminarAsistenteSalaEstudio(run) {
        salaEstudioState.asistentes = salaEstudioState.asistentes.filter(a => a.run !== run);
        actualizarListaAsistentes();
        actualizarProgresoSala();
    }

    /**
     * Registra la asistencia de todos los asistentes
     */
    async function registrarAsistenciaSalaEstudio() {
        if (salaEstudioState.asistentes.length === 0) {
            Swal.fire({
                icon: 'warning',
                title: 'Sin asistentes',
                text: 'Debe registrar al menos un asistente',
                confirmButtonColor: '#ec4899'
            });
            return;
        }

        try {
            // Mostrar loading
            Swal.fire({
                title: 'Registrando asistencia...',
                text: 'Por favor espere',
                allowOutsideClick: false,
                showConfirmButton: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            const response = await fetch('/api/sala-estudio/registrar-asistencia', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({
                    espacio_id: salaEstudioState.espacioId,
                    asistentes: salaEstudioState.asistentes
                })
            });

            const data = await response.json();

            if (data.success) {
                Swal.fire({
                    icon: 'success',
                    title: '¡Asistencia registrada!',
                    text: `Se registró la asistencia de ${salaEstudioState.asistentes.length} asistente(s)`,
                    confirmButtonColor: '#ec4899'
                });

                cerrarModalSalaEstudio();
                
                // Actualizar colores del mapa
                if (typeof actualizarColoresEspacios === 'function') {
                    await actualizarColoresEspacios();
                }
            } else {
                throw new Error(data.message || 'Error al registrar asistencia');
            }
        } catch (error) {
            console.error('Error:', error);
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: error.message || 'No se pudo registrar la asistencia',
                confirmButtonColor: '#ec4899'
            });
        }
    }

    // Event listener para el input QR de sala de estudio
    document.addEventListener('DOMContentLoaded', function() {
        const qrInputSala = document.getElementById('qr-input-sala-estudio');
        if (qrInputSala) {
            qrInputSala.addEventListener('keypress', async function(e) {
                if (e.key === 'Enter') {
                    const qrCode = this.value.trim();
                    if (!qrCode) return;

                    this.value = '';

                    try {
                        // Buscar información del usuario
                        const response = await fetch(`/api/usuario/buscar/${qrCode}`);
                        const data = await response.json();

                        if (data.success) {
                            agregarAsistenteSalaEstudio(qrCode, data.nombre);
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Usuario no encontrado',
                                text: 'El código QR no corresponde a un usuario válido',
                                confirmButtonColor: '#ec4899'
                            });
                        }
                    } catch (error) {
                        console.error('Error:', error);
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'No se pudo verificar el código QR',
                            confirmButtonColor: '#ec4899'
                        });
                    }

                    this.focus();
                }
            });
        }

        // Event listener para botón de registrar asistencia
        const btnRegistrar = document.getElementById('btn-registrar-asistencia-sala');
        if (btnRegistrar) {
            btnRegistrar.addEventListener('click', registrarAsistenciaSalaEstudio);
        }

        // Cerrar modal con ESC
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && salaEstudioState.modalAbierto) {
                cerrarModalSalaEstudio();
            }
        });
    });

    </script>

    <!-- Modales del Panel de Administración -->
    <x-modal-agregar-reserva />
    <x-modal-editar />
    <x-modal-editar-reservas />
    <x-modal-editar-espacios />

    <!-- JavaScript del Panel de Administración -->
    <script src="{{ asset('js/admin-panel.js') }}"></script>

</x-show-layout>
