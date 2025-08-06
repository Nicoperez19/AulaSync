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
                    <div class="p-4 text-white bg-red-700 rounded shadow-[0_0_10px_2px_rgba(255,255,255,0.4)]">
                        <div class="flex items-center justify-between pb-4">
                            <div
                                class="flex items-center gap-1 bg-red-700 rounded shadow-[0_0_1px_1px_rgba(255,255,255,0.1)]">
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
                            <h3 class="mb-2 text-xs font-semibold tracking-wide uppercase text-gray-800">Información de
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

                        <!-- Información del espacio (oculta inicialmente) -->
                        <div id="info-espacio" class="hidden px-4 py-3 space-y-2 text-sm bg-white rounded-lg shadow-md">
                            <h3 class="mb-2 text-xs font-semibold tracking-wide uppercase text-gray-800">Información de
                                espacio</h3>

                            <div class="flex items-center gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-gray-600" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M17.657 16.657L13.414 12 17.657 7.757M6.343 7.757L10.586 12 6.343 16.243" />
                                </svg>
                                <span class="font-bold text-gray-800">Espacio:</span>
                                <span id="nombre-espacio" class="ml-auto text-gray-700">--</span>
                            </div>
                            

                        </div>

                        <input type="text" id="qr-input"
                            class="absolute w-full px-1 py-1 text-transparent bg-transparent border-0 focus:outline-none focus:border-0 focus:ring-0 opacity-0"
    autocomplete="off" autocorrect="off" autocapitalize="off" spellcheck="false" autofocus>

                    </div>
                </div>
            </div>



            <!-- Leyenda abajo del todo -->
            <div class="flex flex-col items-center justify-center w-full max-w-md p-1 mx-auto ">
                <div class="w-full mt-6">
                    <div class="p-4 text-white bg-red-700 rounded shadow-[0_0_10px_2px_rgba(255,255,255,0.4)]">
                <h3
                    class="flex items-center justify-center gap-1 mb-2 text-sm font-semibold text-center text-white md:text-xs">
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
                        <div class="w-3 h-3 bg-orange-500 border-2 border-white rounded-full "></div>
                        <span class="flex-1 text-xs text-white">Reservado</span>
                    </div>
                    <div class="flex items-center w-full gap-1">
                        <div class="w-3 h-3 bg-blue-500 border-2 border-white rounded-full"></div>
                        <span class="flex-1 text-xs text-white">Próximo</span>
                    </div>
                    <div class="flex items-center w-full gap-1">
                        <div class="w-3 h-3 bg-green-500 border-2 border-white rounded-full"></div>
                        <span class="flex-1 text-xs text-white">Disponible</span>
                            </div>
                        </div>
                    </div>
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
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
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

                    <h3 class="text-lg font-medium text-gray-900">Usuario No Registrado</h3>
                    <p class="mt-2 text-sm text-gray-600">
                        El RUN <span id="run-no-registrado" class="font-semibold"></span> no está registrado en el
                        sistema.
                        Complete los siguientes datos para continuar con la solicitud.
                    </p>
                </div>

                <form id="form-registro-usuario" class="space-y-4">
                    <div>
                        <label for="nombre-usuario-input" class="block text-sm font-medium text-right text-gray-700">
                            Nombre Completo *
                        </label>
                        <input type="text" id="nombre-usuario-input" name="nombre" required autocomplete="name"
                            class="block w-full px-3 py-2 mt-1 text-right border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                    </div>


                    <div>
                        <label for="email-usuario" class="block text-sm font-medium text-gray-700">Correo Electrónico
                            *</label>
                        <input type="email" id="email-usuario" name="email" required autocomplete="email"
                            class="block w-full px-3 py-2 mt-1 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                    </div>

                    <div>
                        <label for="telefono-usuario" class="block text-sm font-medium text-gray-700">Teléfono
                            *</label>
                        <input type="tel" id="telefono-usuario" name="telefono" required autocomplete="tel"
                            class="block w-full px-3 py-2 mt-1 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                    </div>

                    <div>
                        <label for="modulos-utilizacion" class="block text-sm font-medium text-gray-700">Módulos de
                            Utilización *</label>
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
    <div id="modal-seleccionar-modulos" class="fixed inset-0 z-50 hidden" data-modal="seleccionar-modulos">
        <div class="fixed inset-0 bg-black bg-opacity-50 transition-opacity"></div>
        
        <div class="fixed inset-0 z-10 overflow-y-auto">
            <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                <div class="relative transform overflow-hidden rounded-lg bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg">
                    <div class="bg-white px-4 pb-4 pt-5 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                            <div class="mx-auto flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-full bg-blue-100 sm:mx-0 sm:h-10 sm:w-10">
                                <svg class="h-6 w-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <div class="mt-3 text-center sm:ml-4 sm:mt-0 sm:text-left w-full">
                                <h3 class="text-lg font-medium leading-6 text-gray-900 mb-4">
                                    Seleccionar Módulos
                                </h3>
                                <div class="text-center">
                                    <p class="mb-4 text-base text-gray-800">¿Por cuántos módulos desea reservar?</p>
                                    <div class="mb-4">
                                        <input type="number" id="input-cantidad-modulos" min="1" max="1" value="1"
                                            class="w-24 px-3 py-2 text-lg text-center border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                    </div>
                                    <div class="mb-6 text-sm text-gray-600">
                                        Disponibles: <span id="max-modulos-disponibles" class="font-semibold text-blue-600">1</span> módulos consecutivos antes de la próxima clase/reserva.
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6">
                        <button id="btn-confirmar-modulos" type="button"
                            class="inline-flex w-full justify-center rounded-md bg-blue-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-blue-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600 sm:ml-3 sm:w-auto">
                            Reservar
                        </button>
                        <button type="button" onclick="cerrarModalModulos()"
                            class="mt-3 inline-flex w-full justify-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 sm:mt-0 sm:w-auto">
                            Cancelar
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para registro de solicitante -->
    <x-modal name="registro-solicitante" :show="false" focusable>
        @slot('title')
            <h2 class="text-lg font-medium text-white dark:text-gray-100">
                Registro de Solicitante
            </h2>
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
                            class="block w-full px-3 py-2 mt-1 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
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
                        <input type="tel" id="telefono-solicitante" name="telefono" required autocomplete="tel"
                            class="block w-full px-3 py-2 mt-1 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
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
                        <button type="button" onclick="cancelarRegistroSolicitante()"
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
        let bufferQR = ''; // Buffer para almacenar el código QR escaneado
        let esperandoProfesor = true; // Flag para indicar si estamos esperando escanear profesor o espacio
        let profesorEscaneado = null; // Profesor que se escaneó

        // ========================================
        // VARIABLES GLOBALES PARA EL MODO DE OPERACIÓN
        // ========================================
        let bufferQRDevolucion = ''; // Buffer específico para devolución
        let esperandoUsuarioDevolucion = true; // Flag para devolución
        let usuarioEscaneadoDevolucion = null; // Usuario escaneado para devolución
        let espacioEscaneadoDevolucion = null; // Espacio escaneado para devolución

        // ========================================
        // VARIABLES GLOBALES PARA EL FLUJO DE SOLICITUD
        // ========================================
        let bufferQRSolicitud = ''; // Buffer específico para solicitud
        let esperandoUsuarioSolicitud = true; // Flag para solicitud
        let usuarioEscaneadoSolicitud = null; // Usuario escaneado para solicitud
        let espacioEscaneadoSolicitud = null; // Espacio escaneado para solicitud

        // ========================================
        // VARIABLES GLOBALES PARA USUARIOS NO REGISTRADOS
        // ========================================

        let modoOperacionActual = null; // 'solicitud' o 'devolucion'

        // ========================================
        // VARIABLES GLOBALES PARA SOLICITANTES
        // ========================================
        let runSolicitantePendiente = null; // RUN del solicitante pendiente



        // ========================================
        // LÓGICA PARA RESERVA POR MÓDULOS
        // ========================================
        let maxModulosDisponibles = 1;
        let espacioParaReserva = null;
        let runParaReserva = null;

        // ========================================
        // OBTENER ID DEL MAPA DESDE EL CONTROLADOR
        // ========================================
        const mapaId = @json($mapaIdValue);

        // ========================================
        // CONFIGURACIÓN GLOBAL PARA LOS INDICADORES
        // ========================================
        const config = {
            indicatorSize: 35, // Tamaño del indicador
            indicatorWidth: 37, // Ancho del indicador
            indicatorHeight: 37, // Alto del indicador
            indicatorBorder: '#FFFFFF', // Color del borde
            indicatorTextColor: '#FFFFFF', // Color del texto
            fontSize: 12 // Tamaño de fuente
        };

        // ========================================
        // VARIABLES GLOBALES PARA EL ESTADO DEL MAPA
        // ========================================
        const state = {
            mapImage: null, // Imagen del mapa
            originalImageSize: null, // Tamaño original de la imagen
            indicators: @json($bloques) || [], // Indicadores/bloques del mapa
            originalCoordinates: @json($bloques) || [], // Coordenadas originales
            isImageLoaded: false, // Si la imagen está cargada
            mouseX: 0, // Posición X del mouse
            mouseY: 0, // Posición Y del mouse
            updateInterval: null, // Intervalo de actualización
            hoveredIndicator: null, // Indicador sobre el que está el mouse
            lastLocalChange: null, // Timestamp del último cambio local
            ultimoCambioLocal: null // Timestamp del último cambio local
        };

        // ========================================
        // VARIABLES GLOBALES PARA LOS ELEMENTOS DEL CANVAS
        // ========================================
        let elements = {
            mapCanvas: null, // Canvas del mapa
            mapCtx: null, // Contexto del canvas del mapa
            indicatorsCanvas: null, // Canvas de los indicadores
            indicatorsCtx: null // Contexto del canvas de indicadores
        };

        // ========================================
        // FUNCIONES PARA MOSTRAR INFORMACIÓN PROGRESIVAMENTE
        // ========================================

        // Función para mostrar información del usuario
        function mostrarInfoUsuario(run, nombre) {
            // Ocultar mensaje inicial
            const mensajeInicial = document.getElementById('mensaje-inicial');
            if (mensajeInicial) {
                mensajeInicial.classList.add('hidden');
            }

            // Mostrar información del usuario
            const infoUsuario = document.getElementById('info-usuario');
            if (infoUsuario) {
                infoUsuario.classList.remove('hidden');
            }

            // Actualizar datos del usuario
            document.getElementById('run-escaneado').textContent = run;
            document.getElementById('nombre-usuario').textContent = nombre;

            // Quitar parpadeo del estado QR cuando se procesa usuario
            const qrStatus = document.getElementById('qr-status');
            if (qrStatus) {
                qrStatus.classList.remove('parpadeo');
            }
        }

        // Función para mostrar información del espacio
        function mostrarInfoEspacio(nombreEspacio) {
            // Mostrar información del espacio
            const infoEspacio = document.getElementById('info-espacio');
            if (infoEspacio) {
                infoEspacio.classList.remove('hidden');
            }

            // Actualizar datos del espacio
            document.getElementById('nombre-espacio').textContent = nombreEspacio;
        }

        // Función para resetear la interfaz
        function resetearInterfaz() {
            // Ocultar información del usuario y espacio
            const infoUsuario = document.getElementById('info-usuario');
            const infoEspacio = document.getElementById('info-espacio');
            if (infoUsuario) infoUsuario.classList.add('hidden');
            if (infoEspacio) infoEspacio.classList.add('hidden');

            // Mostrar mensaje inicial
            const mensajeInicial = document.getElementById('mensaje-inicial');
            if (mensajeInicial) {
                mensajeInicial.classList.remove('hidden');
            }

            // Limpiar datos
            document.getElementById('run-escaneado').textContent = '--';
            document.getElementById('nombre-usuario').textContent = '--';
            document.getElementById('nombre-espacio').textContent = '--';

            // Restaurar parpadeo del estado QR
            const qrStatus = document.getElementById('qr-status');
            if (qrStatus) {
                qrStatus.classList.add('parpadeo');
                qrStatus.innerHTML = 'Esperando... Escanea el código QR';
            }
            
            // Limpiar cualquier input de QR que pueda tener datos
            const qrInput = document.getElementById('qr-input');
            if (qrInput) {
                qrInput.value = '';
            }
            
            // Limpiar buffer de QR si existe
            if (typeof bufferQR !== 'undefined') {
                bufferQR = '';
            }
            
            // Limpiar buffer de solicitud QR si existe
            if (typeof bufferQRSolicitud !== 'undefined') {
                bufferQRSolicitud = '';
            }
        }
        
        // Función para limpiar completamente el estado de la aplicación
        function limpiarEstadoCompleto() {
            // Resetear variables globales
            ordenEscaneo = 'usuario';
            profesorEscaneado = null;
            espacioParaReserva = null;
            runParaReserva = null;
            
            // Limpiar buffers
            if (typeof bufferQR !== 'undefined') {
                bufferQR = '';
            }
            if (typeof bufferQRSolicitud !== 'undefined') {
                bufferQRSolicitud = '';
            }
            
            // Resetear interfaz visual
            resetearInterfaz();
            
            // Restaurar parpadeo del estado QR
            const qrStatus = document.getElementById('qr-status');
            if (qrStatus) {
                qrStatus.classList.add('parpadeo');
                qrStatus.innerHTML = 'Esperando... Escanea el código QR';
            }
        }

        // ========================================
        // FUNCIONES DE VERIFICACIÓN DE USUARIO Y ESPACIO
        // ========================================

        // Función para verificar si un usuario existe en el sistema
        async function verificarUsuario(run) {
            try {
                const response = await fetch(`/api/verificar-usuario/${run}`);
                const result = await response.json();
                return result;
            } catch (error) {
                console.error('Error:', error);
                return null;
            }
        }



        // Función para verificar si un espacio existe y está disponible
        async function verificarEspacio(idEspacio) {
            try {
                const response = await fetch(`/api/verificar-espacio/${idEspacio}`);
                const result = await response.json();
                return result;
            } catch (error) {
                console.error('Error:', error);
                return null;
            }
        }

        // Función para verificar si un profesor tiene clases programadas
        async function verificarClasesProfesor(run) {
            try {
                console.log('🔍 Verificando clases para profesor:', run);
                const response = await fetch(`/api/verificar-clases-programadas/${run}`);
                const result = await response.json();
                console.log('📡 Respuesta completa del endpoint:', result);
                
                // La respuesta tiene una estructura extraña, necesitamos acceder a result.original
                const data = result.original || result;
                console.log('📊 Datos extraídos:', data);
                console.log('✅ data.success:', data.success, 'tipo:', typeof data.success);
                console.log('📚 data.tiene_clases:', data.tiene_clases, 'tipo:', typeof data.tiene_clases);
                
                // Verificar si la respuesta es exitosa y tiene clases
                const tieneClases = data.success && data.tiene_clases;
                console.log('🔍 Condición evaluada: data.success && data.tiene_clases =', tieneClases);
                
                if (tieneClases) {
                    console.log('✅ Profesor TIENE clases programadas - retornando TRUE');
                    return true;
                } else {
                    console.log('❌ Profesor NO tiene clases programadas - retornando FALSE');
                    console.log('   Detalles:');
                    console.log('     - data.success:', data.success);
                    console.log('     - data.tiene_clases:', data.tiene_clases);
                    console.log('     - data.success && data.tiene_clases:', data.success && data.tiene_clases);
                    return false;
                }
            } catch (error) {
                console.error('💥 Error en verificarClasesProfesor:', error);
                return false;
            }
        }

        // Función para crear una reserva
        async function crearReserva(run, idEspacio, tipoUsuario = 'profesor') {
            try {
                const response = await fetch('/api/crear-reserva', {
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
                console.error('Error:', error);
                return null;
            }
        }

        // Función para registrar asistencia de profesor con clases programadas
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
                console.error('Error al registrar asistencia:', error);
                return null;
            }
        }



        // Función para registrar solicitante
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
                return await response.json();
            } catch (error) {
                console.error('Error:', error);
                return null;
            }
        }

        // Función para crear reserva de solicitante
        async function crearReservaSolicitante(runSolicitante, idEspacio) {
            try {
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
                return await response.json();
            } catch (error) {
                console.error('Error:', error);
                return null;
            }
        }

        // Función para devolver espacio
        async function devolverEspacio(runUsuario, idEspacio) {
            try {
                const response = await fetch('/api/devolver-espacio', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        run_usuario: runUsuario,
                        id_espacio: idEspacio
                    })
                });
                return await response.json();
            } catch (error) {
                console.error('Error:', error);
                return null;
            }
        }

        // Función para verificar el estado del espacio y las reservas del usuario
        async function verificarEstadoEspacioYReserva(runUsuario, idEspacio) {
            try {
                console.log('Verificando estado del espacio y reserva:', { runUsuario, idEspacio });
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
                console.log('Resultado de verificación:', result);
                return result;
            } catch (error) {
                console.error('Error:', error);
                return {
                    tipo: 'error',
                    mensaje: 'Error de conexión al verificar el estado del espacio'
                };
            }
        }

        // ========================================
        // NUEVA LÓGICA DE ESCANEO QR CON VALIDACIÓN DE ORDEN
        // ========================================
        let lastBufferLength = 0;
        let processingTimeout = null;
        let ordenEscaneo = 'usuario'; // Controla el orden: 'usuario' -> 'espacio'
        let procesandoDevolucion = false; // Flag para evitar procesamiento múltiple
        
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
                    }
                }
                return;
            }

            await procesarQRCompleto();
        }
        
        // Función para procesar el QR completo con validación de orden
        async function procesarQRCompleto() {
            // Validar orden de escaneo
            if (ordenEscaneo === 'usuario') {
                // PASO 1: Escanear usuario (obligatorio primero)
                await procesarUsuario();
            } else if (ordenEscaneo === 'espacio') {
                // PASO 2: Escanear espacio (solo después del usuario)
                const resultado = await procesarEspacio();
                
                // Si la devolución fue exitosa, no continuar con más procesamiento
                if (resultado === 'devolucion_exitosa') {
                    console.log('Devolución exitosa - deteniendo procesamiento adicional');
                    return;
                }
            } else {
                // Error: orden incorrecto
                console.error('Error: Debe escanear primero el QR del usuario');
            }

            // Limpiar buffer y input
            bufferQR = '';
            lastBufferLength = 0;
            if (processingTimeout) {
                clearTimeout(processingTimeout);
                processingTimeout = null;
            }
            const inputEscanner = document.getElementById('qr-input');
            if (inputEscanner) {
                inputEscanner.value = '';
            }
        }

        // Función para procesar QR de usuario con diferenciación de tipos
        async function procesarUsuario() {
            // Extraer RUN del QR (buscar "RUN" seguido de números)
            const runMatch = bufferQR.match(/RUN[^0-9]*(\d+)/);
            if (!runMatch) {
                console.error('Error: Formato de RUN no válido');
                return;
            }

            const run = runMatch[1];

            // Verificar usuario en la base de datos
            const usuarioInfo = await verificarUsuario(run);
            
            if (!usuarioInfo) {
                console.error('Error: Error al verificar usuario');
                return;
            }

            if (usuarioInfo.verificado) {
                if (usuarioInfo.tipo_usuario === 'profesor') {
                    // Es profesor - verificar si tiene clases programadas
                    console.log('👨‍🏫 Usuario es profesor, verificando clases...');
                    const tieneClases = await verificarClasesProfesor(run);
                    console.log('🎯 Resultado de verificarClasesProfesor:', tieneClases, 'tipo:', typeof tieneClases);
                    
                    if (tieneClases === true) {
                        console.log('✅ Profesor CON clases - mostrando mensaje de asistencia');
                        // Profesor CON clases - solo registra solicitud
                        document.getElementById('qr-status').innerHTML = 'Profesor con clases verificado. Escanee el espacio para registrar asistencia.';
                        // Mostrar información del usuario
                        mostrarInfoUsuario(usuarioInfo.usuario.run, usuarioInfo.usuario.nombre);
                        profesorEscaneado = run;
                        ordenEscaneo = 'espacio';
                        // No necesita devolución para volver a solicitar
                    } else {
                        console.log('❌ Profesor SIN clases - mostrando mensaje de solicitud');
                        console.log('   Valor exacto de tieneClases:', tieneClases);
                        console.log('   Comparación tieneClases === true:', tieneClases === true);
                        console.log('   Comparación tieneClases == true:', tieneClases == true);
                        // Profesor SIN clases - solicita con módulos
                        document.getElementById('qr-status').innerHTML = 'Profesor sin clases. Escanee el espacio para solicitar.';
                        // Mostrar información del usuario
                        mostrarInfoUsuario(usuarioInfo.usuario.run, usuarioInfo.usuario.nombre);
                        profesorEscaneado = run;
                        ordenEscaneo = 'espacio';
                        // Necesitará especificar módulos (máx 2)
                    }
                } else if (usuarioInfo.tipo_usuario === 'solicitante_registrado') {
                    // Es solicitante registrado - solicita con módulos
                    document.getElementById('qr-status').innerHTML = 'Solicitante verificado. Escanee el espacio para solicitar.';
                    // Mostrar información del usuario
                    mostrarInfoUsuario(usuarioInfo.usuario.run, usuarioInfo.usuario.nombre);
                    profesorEscaneado = run;
                    ordenEscaneo = 'espacio';
                    // Necesitará especificar módulos (máx 2)
                } else {
                    // Otro tipo de usuario - mostrar error
                    console.error('Error: Tipo de usuario no válido para solicitar espacios');
                }
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
                    window.dispatchEvent(new CustomEvent('open-modal', {
                        detail: 'registro-solicitante'
                    }));
                }, 300);
            }
        }

        // Función para procesar QR de espacio con manejo de diferentes casos
        async function procesarEspacio() {
            // Extraer código de espacio (buscar "TH" seguido de letras/números)
            const espacioMatch = bufferQR.match(/(TH[^A-Z0-9]*[A-Z0-9]+)/);
            if (!espacioMatch) {
                console.error('Error: Código de espacio no válido');
                return;
            }

            const espacio = espacioMatch[1].replace(/[^A-Z0-9]/g, '-');

            // Verificar estado del espacio y reservas del usuario
            console.log('Verificando estado para usuario:', profesorEscaneado, 'espacio:', espacio);
            const resultadoVerificacion = await verificarEstadoEspacioYReserva(profesorEscaneado, espacio);
            console.log('Resultado de verificación completo:', resultadoVerificacion);
            
            if (resultadoVerificacion.tipo === 'error') {
                console.error('Error en verificación:', resultadoVerificacion.mensaje);
                ordenEscaneo = 'usuario';
                return;
            }

            if (resultadoVerificacion.tipo === 'devolucion') {
                // Evitar procesamiento múltiple
                if (procesandoDevolucion) {
                    console.log('Devolución ya en proceso, ignorando...');
                    return 'devolucion_en_proceso';
                }
                
                procesandoDevolucion = true;
                
                // El usuario tiene una reserva activa en este espacio - procesar devolución automáticamente
                console.log('Procesando devolución automática para usuario:', profesorEscaneado, 'espacio:', espacio);
                
                // Mostrar mensaje de devolución en proceso
                document.getElementById('qr-status').innerHTML = 'Procesando devolución...';
                
                const devolucion = await devolverEspacio(profesorEscaneado, espacio);
                console.log('Resultado de devolución:', devolucion);
                
                                    if (devolucion && devolucion.success) {
                        // Actualizar indicador en el mapa
                        const block = state.indicators.find(b => b.id === espacio);
                        if (block) {
                            block.estado = '#00FF00'; // Verde = Disponible
                            state.originalCoordinates = state.indicators.map(i => ({ ...i }));
                            drawIndicators();
                        }
                        
                        // Mostrar Sweet Alert de éxito para devolución
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
                    
                    // Limpiar completamente la interfaz después de un delay
                    setTimeout(() => {
                        limpiarEstadoCompleto();
                    }, 2000);
                    
                    // IMPORTANTE: Detener completamente el procesamiento aquí
                    procesandoDevolucion = false;
                    return 'devolucion_exitosa';
                } else {
                    // Mostrar error específico de devolución
                    const mensajeError = devolucion?.mensaje || 'Error al devolver las llaves';
                    console.error('Error en devolución:', mensajeError);
                    
                    // Resetear el estado para permitir nuevo escaneo
                    procesandoDevolucion = false;
                    ordenEscaneo = 'usuario';
                    return;
                }
            }

            if (resultadoVerificacion.tipo === 'reserva_existente') {
                console.log('🔍 DEBUG: Usuario ya tiene reserva activa en otro espacio');
                console.log('   - profesorEscaneado:', profesorEscaneado);
                console.log('   - mensaje:', resultadoVerificacion.mensaje);
                
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
                    limpiarEstadoCompleto();
                }, 2500);
                
                ordenEscaneo = 'usuario';
                return;
            }

            if (resultadoVerificacion.tipo === 'espacio_ocupado') {
                console.log('🔍 DEBUG: Espacio ocupado detectado');
                console.log('   - profesorEscaneado:', profesorEscaneado);
                console.log('   - resultadoVerificacion.ocupante:', resultadoVerificacion.ocupante);
                console.log('   - resultadoVerificacion.ocupante?.run:', resultadoVerificacion.ocupante?.run);
                console.log('   - Comparación run:', resultadoVerificacion.ocupante?.run === profesorEscaneado);
                
                // Verificar si el ocupante es el mismo usuario que acaba de escanear
                if (resultadoVerificacion.ocupante && resultadoVerificacion.ocupante.run === profesorEscaneado) {
                    // Es el mismo usuario, no mostrar mensaje de ocupado
                    console.log('✅ Usuario escaneó su propio espacio ocupado, no mostrar mensaje');
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
                            <div class="bg-gray-100 p-3 rounded-lg">
                                <p><strong>${tipoUsuario}:</strong> ${ocupante.nombre}</p>
                                <p><strong>RUN:</strong> ${ocupante.run}</p>
                                <p><strong>Hora de inicio:</strong> ${ocupante.hora_inicio}</p>
                                <p><strong>Fecha:</strong> ${ocupante.fecha}</p>
                            </div>
                        </div>
                    `;
                }
                
                // Mostrar mensaje de espacio ocupado en consola
                console.log('Espacio Ocupado:', mensajeDetallado);
                
                // Limpiar estado después de mostrar el mensaje
                setTimeout(() => {
                    limpiarEstadoCompleto();
                }, 1000);
                
                ordenEscaneo = 'usuario';
                return;
            }

            // Si llegamos aquí, el espacio está disponible para crear una nueva reserva
            // Verificar el tipo de usuario para determinar el flujo
            const usuarioInfo = await verificarUsuario(profesorEscaneado);
            
            if (!usuarioInfo || !usuarioInfo.verificado) {
                console.error('Error: Error al verificar usuario para crear reserva');
                ordenEscaneo = 'usuario';
                return;
            }

            // Determinar el flujo según el tipo de usuario
            console.log('Usuario info completa:', usuarioInfo);
            console.log('Tipo usuario detectado:', usuarioInfo.tipo_usuario);
            
            if (usuarioInfo.tipo_usuario === 'profesor') {
                console.log('✅ Usuario es profesor, verificando clases...');
                // Verificar si tiene clases programadas
                const tieneClases = await verificarClasesProfesor(profesorEscaneado);
                console.log('Tipo usuario:', usuarioInfo.tipo_usuario);
                console.log('Tiene clases:', tieneClases);
                console.log('Tipo de dato tieneClases:', typeof tieneClases);
                
                if (tieneClases === true) {
                    console.log('✅ Profesor CON clases - registrando asistencia automática');
                    // CASO 1: Profesor CON clases - registrar asistencia usando endpoint específico
                    const resultado = await registrarAsistenciaProfesor(profesorEscaneado, espacio);
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
                        
                        // Limpiar completamente la interfaz después de un delay
                        setTimeout(() => {
                            // Limpiar todo excepto profesorEscaneado
                            ordenEscaneo = 'usuario';
                            espacioParaReserva = null;
                            runParaReserva = null;
                            
                            // Limpiar buffers
                            if (typeof bufferQR !== 'undefined') {
                                bufferQR = '';
                            }
                            if (typeof bufferQRSolicitud !== 'undefined') {
                                bufferQRSolicitud = '';
                            }
                            
                            // Resetear interfaz visual
                            resetearInterfaz();
                            
                            // Restaurar parpadeo del estado QR
                            const qrStatus = document.getElementById('qr-status');
                            if (qrStatus) {
                                qrStatus.classList.add('parpadeo');
                                qrStatus.innerHTML = 'Esperando... Escanea el código QR';
                            }
                            
                            // Limpiar profesorEscaneado después de 5 segundos
                            setTimeout(() => {
                                profesorEscaneado = null;
                            }, 5000);
                        }, 2000);
                    } else {
                        console.error('Error al registrar asistencia:', resultado?.mensaje || 'Error desconocido');
                    }
                } else {
                    console.log('⚠️ Profesor SIN clases - mostrando modal de módulos');
                    console.log('Valor exacto de tieneClases:', tieneClases);
                    console.log('Comparación tieneClases === true:', tieneClases === true);
                    console.log('Comparación tieneClases == true:', tieneClases == true);
                    // CASO 2: Profesor SIN clases - solicita con módulos (máx 2)
                    await mostrarModalSeleccionarModulos(espacio, profesorEscaneado, 2); // Máximo 2 módulos
                    return; // No continuar, esperar selección de módulos
                }
            } else if (usuarioInfo.tipo_usuario === 'solicitante_registrado') {
                // CASO 3: Solicitante registrado - solicita con módulos (máx 2)
                await mostrarModalSeleccionarModulos(espacio, profesorEscaneado, 2); // Máximo 2 módulos
                return; // No continuar, esperar selección de módulos
            } else {
                console.error('Error: Tipo de usuario no válido para crear reserva');
                ordenEscaneo = 'usuario';
                return;
            }

            // Resetear para siguiente usuario
            setTimeout(() => {
                limpiarEstadoCompleto();
            }, 3000);
            
            return 'procesamiento_completado';
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

        // ========================================
        // FUNCIÓN PARA DIBUJAR UN INDICADOR
        // ========================================
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

        // ========================================
        // FUNCIÓN PARA DIBUJAR TODOS LOS INDICADORES
        // ========================================
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

        // ========================================
        // FUNCIÓN PARA MOSTRAR EL MODAL CON LA INFORMACIÓN DEL ESPACIO
        // ========================================
        async function mostrarModalEspacio(indicator) {
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

            // Si el espacio está ocupado, obtener información del ocupante
            if (estadoTexto === 'Ocupado') {
                try {
                    const response = await fetch('/api/verificar-estado-espacio-reserva', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        },
                        body: JSON.stringify({
                            run: '00000000', // RUN dummy para verificar ocupación
                            id_espacio: indicator.id
                        })
                    });

                    const result = await response.json();
                    
                    if (result.tipo === 'espacio_ocupado' && result.ocupante) {
                        const ocupante = result.ocupante;
                        const tipoUsuario = ocupante.tipo === 'profesor' ? 'Profesor' : 'Solicitante';
                        
                        usuarioOcupando = `<br><span class='text-xs text-gray-700'>Ocupado por: <b>${ocupante.nombre}</b></span>`;
                        
                        informacionUsuario = `
                            <div class='p-3 mt-2 rounded-lg bg-gray-50'>
                                <h4 class='mb-2 text-sm font-semibold text-gray-800'>Información del Ocupante</h4>
                                <div class='space-y-1 text-xs text-gray-600'>
                                    <div><span class='font-medium'>Tipo:</span> ${tipoUsuario}</div>
                                    <div><span class='font-medium'>Nombre:</span> ${ocupante.nombre}</div>
                                    <div><span class='font-medium'>RUN:</span> ${ocupante.run}</div>
                                    <div><span class='font-medium'>Hora de inicio:</span> ${ocupante.hora_inicio}</div>
                                    <div><span class='font-medium'>Fecha:</span> ${ocupante.fecha}</div>
                                </div>
                            </div>
                        `;
                    } else if (indicator.detalles?.usuario_ocupando) {
                        // Fallback a información existente si no se puede obtener del servidor
                        usuarioOcupando = `<br><span class='text-xs text-gray-700'>Ocupado por: <b>${indicator.detalles.usuario_ocupando}</b></span>`;
                        
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
                } catch (error) {
                    console.error('Error al obtener información del ocupante:', error);
                    // Fallback a información existente
                    if (indicator.detalles?.usuario_ocupando) {
                        usuarioOcupando = `<br><span class='text-xs text-gray-700'>Ocupado por: <b>${indicator.detalles.usuario_ocupando}</b></span>`;
                    }
                }
            } else if (estadoTexto === 'Reservado' && indicator.detalles?.usuario_ocupando) {
                usuarioOcupando = `<br><span class='text-xs text-gray-700'>Reservado por: <b>${indicator.detalles.usuario_ocupando}</b></span>`;

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
                modalPlanificacionHorario.textContent =
                    `${infoClaseActual.hora_inicio} - ${infoClaseActual.hora_termino} hrs`;
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
                        planificacionContainer.parentNode.insertBefore(usuarioContainer, planificacionContainer
                            .nextSibling);
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
            let pillColor = '',
                iconColor = '',
                texto = '',
                mostrarPlanificacion = true;
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
            estadoPill.className =
                `inline-flex items-center px-4 py-2 text-base font-bold border rounded-full ${pillColor}`;
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
            const moduloActual = moduloActualNum(horaActual);
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
                return {
                    success: false,
                    mensaje: 'Error de conexión'
                };
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
            window.dispatchEvent(new CustomEvent('close-modal', {
                detail: 'devolver-llaves'
            }));
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
                            document.getElementById('qr-status-devolucion').innerHTML =
                                'Usuario verificado. Escanee el espacio para devolver.';
                            esperandoUsuarioDevolucion = false;
                        } else {
                            console.error('Error de verificación:', usuarioInfo?.mensaje || 'Error desconocido');
                            document.getElementById('qr-status-devolucion').innerHTML = usuarioInfo?.mensaje ||
                                'Error de verificación';
                        }
                    } else {
                        console.error('Error: RUN inválido');
                        document.getElementById('qr-status-devolucion').innerHTML = 'RUN inválido';
                    }
                } else {
                    // Procesar QR de espacio para devolución (formato: TH'L01)
                    const espacioProcesado = bufferQRDevolucion.replace(/'/g, '-');
                    espacioEscaneadoDevolucion = espacioProcesado;

                    // Primero verificar el estado del espacio y si el usuario tiene reserva activa
                    console.log('Verificando estado para devolución:', { usuarioEscaneadoDevolucion, espacioProcesado });
                    const resultadoVerificacion = await verificarEstadoEspacioYReserva(usuarioEscaneadoDevolucion, espacioProcesado);
                    console.log('Resultado de verificación para devolución:', resultadoVerificacion);

                    if (resultadoVerificacion.tipo === 'devolucion') {
                        // El usuario tiene una reserva activa en este espacio, proceder con devolución
                        const resultado = await procesarDevolucion(usuarioEscaneadoDevolucion, espacioProcesado);

                        if (resultado.success) {
                            // Mostrar mensaje de devolución exitosa
                            console.log('¡Devolución exitosa! Las llaves han sido devueltas correctamente');
                            
                            // Cerrar modales
                            cerrarModalesDespuesDeSwal(['devolver-llaves', 'data-space']);
                            document.getElementById('qr-status-devolucion').innerHTML = 'Devolución completada';
                            // Actualizar el color del indicador a 'Disponible' (verde)
                            const block = state.indicators.find(b => b.id === espacioProcesado);
                            if (block) {
                                block.estado = '#059669'; // Verde
                                state.originalCoordinates = state.indicators.map(i => ({
                                    ...i
                                }));
                                drawIndicators();
                            }
                            // Cerrar el modal después de la devolución exitosa
                            setTimeout(() => {
                                cerrarModalDevolverLlaves();
                                // Resetear interfaz después de cerrar modal
                                setTimeout(() => {
                                    resetearInterfaz();
                                }, 500);
                            }, 1000);
                        } else {
                            console.error('Error al procesar la devolución:', resultado.message || resultado.mensaje || 'Error desconocido');
                            document.getElementById('qr-status-devolucion').innerHTML = resultado.message || resultado.mensaje ||
                                'Error al procesar la devolución';
                        }
                    } else if (resultadoVerificacion.tipo === 'reserva_existente') {
                        // El usuario ya tiene una reserva activa en otro espacio
                        console.log('Reserva Existente:', resultadoVerificacion.mensaje);
                        document.getElementById('qr-status-devolucion').innerHTML = resultadoVerificacion.mensaje;
                    } else if (resultadoVerificacion.tipo === 'espacio_ocupado') {
                        // El espacio está ocupado por otro usuario
                        let mensajeDetallado = resultadoVerificacion.mensaje;
                        if (resultadoVerificacion.ocupante) {
                            const ocupante = resultadoVerificacion.ocupante;
                            const tipoUsuario = ocupante.tipo === 'profesor' ? 'Profesor' : 'Solicitante';
                            mensajeDetallado = `
                                <div class="text-left">
                                    <p class="mb-2"><strong>${resultadoVerificacion.mensaje}</strong></p>
                                    <div class="bg-gray-100 p-3 rounded-lg">
                                        <p><strong>${tipoUsuario}:</strong> ${ocupante.nombre}</p>
                                        <p><strong>RUN:</strong> ${ocupante.run}</p>
                                        <p><strong>Hora de inicio:</strong> ${ocupante.hora_inicio}</p>
                                        <p><strong>Fecha:</strong> ${ocupante.fecha}</p>
                                    </div>
                                </div>
                            `;
                        }
                        
                        console.log('Espacio Ocupado:', mensajeDetallado);
                        document.getElementById('qr-status-devolucion').innerHTML = resultadoVerificacion.mensaje;
                    } else {
                        // Error en la verificación
                        console.error('Error al verificar el estado del espacio:', resultadoVerificacion.mensaje || 'Error desconocido');
                        document.getElementById('qr-status-devolucion').innerHTML = resultadoVerificacion.mensaje || 'Error al verificar el estado del espacio';
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
        }

        // ========================================
        // FUNCIÓN PARA ACTUALIZAR COLORES DE ESPACIOS
        // ========================================
        async function actualizarColoresEspacios() {
            // Verificar si ha habido cambios locales recientes
            const tiempoTranscurrido = Date.now() - (state.ultimoCambioLocal || 0);
            if (tiempoTranscurrido < 10000) { // 10 segundos
                return;
            }

            try {
                const response = await fetch(`/plano/${mapaId}/bloques`);
                if (!response.ok) {
                    return;
                }

                const data = await response.json();
                if (!data.bloques || !Array.isArray(data.bloques)) {
                    return;
                }

                let cambiosDetectados = [];

                data.bloques.forEach(nuevoBloque => {
                    const bloqueExistente = state.indicators.find(b => b.id === nuevoBloque.id);
                    if (bloqueExistente && bloqueExistente.estado !== nuevoBloque.estado) {
                        bloqueExistente.estado = nuevoBloque.estado;
                        cambiosDetectados.push({
                            id: nuevoBloque.id,
                            estadoAnterior: bloqueExistente.estado,
                            estadoNuevo: nuevoBloque.estado
                        });
                    }
                });

                if (cambiosDetectados.length > 0) {
                    state.originalCoordinates = state.indicators.map(i => ({ ...i }));
                    drawIndicators();
                }
            } catch (error) {
                // Error silencioso para no saturar la consola
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
        // INICIALIZACIÓN PRINCIPAL 
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
                document.getElementById('qr-status').innerHTML = 'Esperando';
                // Asegurar que la interfaz esté en estado inicial
                resetearInterfaz();
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

            // Configurar el formulario de registro de solicitante
            const formRegistroSolicitante = document.getElementById('form-registro-solicitante');
            if (formRegistroSolicitante) {
                formRegistroSolicitante.addEventListener('submit', procesarRegistroSolicitante);
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
                    setTimeout(() => {
                        inputQR.focus();
                    }, 200);
                });
            }
            window.actualizarColoresEspacios = actualizarColoresEspacios;
        });

        // ========================================
        // FUNCIONES PARA MANEJAR REGISTRO DE SOLICITANTE
        // ========================================
        
        // Función para procesar el registro de solicitante
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

            try {
                const resultado = await registrarSolicitante(datosSolicitante);
                
                if (resultado && resultado.success) {
                    // Cerrar modal de registro inmediatamente
                    window.dispatchEvent(new CustomEvent('close-modal', {
                        detail: 'registro-solicitante'
                    }));
                    
                    // Actualizar información en la interfaz
                    document.getElementById('qr-status').innerHTML = 'Solicitante registrado. Escanee el QR del espacio.';
                    mostrarInfoUsuario(runSolicitantePendiente, datosSolicitante.nombre);
                    
                    // Continuar con el flujo - solo necesita escanear espacio
                    profesorEscaneado = runSolicitantePendiente;
                    ordenEscaneo = 'espacio'; // Ya no necesita escanear usuario
                    
                    // Limpiar variables
                    runSolicitantePendiente = null;
                    
                    // Mostrar mensaje de éxito
                    console.log('¡Registro exitoso! Solicitante registrado correctamente. Ahora escanee el QR del espacio.');
                    
                } else {
                    console.error('Error al registrar solicitante:', resultado?.mensaje || 'Error desconocido');
                }
            } catch (error) {
                console.error('Error:', error);
                console.error('Error al procesar el registro');
            }
        }

        // Función para cancelar registro de solicitante
        function cancelarRegistroSolicitante() {
            // Cerrar modal de registro
            window.dispatchEvent(new CustomEvent('close-modal', {
                detail: 'registro-solicitante'
            }));
            
            // Limpiar variables
            runSolicitantePendiente = null;
            
            // Resetear estado
            esperandoProfesor = true;
            document.getElementById('qr-status').innerHTML = 'Esperando';
            resetearInterfaz();
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
                nombre: formData.get('nombre'),
                email: formData.get('email'),
                telefono: formData.get('telefono'),
                modulos_utilizacion: parseInt(formData.get('modulos_utilizacion'))
            };

            try {
                const resultado = await registrarSolicitante(datosUsuario);

                if (resultado && resultado.success) {
                    console.log('¡Usuario registrado exitosamente! El usuario ha sido registrado y puede continuar con la solicitud.');
                    
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
                            document.getElementById('qr-status-solicitud').innerHTML =
                                'Usuario registrado. Escanee el espacio para solicitar.';

                            // Configurar el input de solicitud
                            const inputSolicitud = document.getElementById('qr-input-solicitud');
                            if (inputSolicitud) {
                                inputSolicitud.value = '';
                                inputSolicitud.focus();
                            }
                        }, 300);
                    }

                    // Resetear variables
                    modoOperacionActual = null;
                } else {
                    console.error('Error al registrar usuario:', resultado?.mensaje || 'Error desconocido');
                }
            } catch (error) {
                console.error('Error al procesar registro:', error);
                console.error('Error al procesar el registro');
            }
        }

     
        // Asegurar que indicators sea siempre un array
        if (!state.indicators || !Array.isArray(state.indicators)) {
            state.indicators = [];
        }
        if (!state.originalCoordinates || !Array.isArray(state.originalCoordinates)) {
            state.originalCoordinates = [];
        }

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
                                document.getElementById('qr-status-solicitud').innerHTML =
                                    'Usuario registrado verificado. Escanee el espacio para solicitar.';
                            } else if (usuarioInfo.tipo_usuario === 'no_registrado') {
                                document.getElementById('qr-status-solicitud').innerHTML =
                                    'Usuario no registrado verificado. Escanee el espacio para solicitar.';
                            }
                            esperandoUsuarioSolicitud = false;
                        } else if (usuarioInfo && usuarioInfo.usuario_no_registrado && usuarioInfo.tipo_usuario ===
                            'nuevo') {
                            // Usuario completamente nuevo - mostrar modal de registro como solicitante
                            runSolicitantePendiente = usuarioInfo.run_escaneado;
                            document.getElementById('run-solicitante-no-registrado').textContent = usuarioInfo.run_escaneado;
                            
                            // Cerrar modal actual si está abierto
                            window.dispatchEvent(new CustomEvent('close-modal', {
                                detail: 'solicitar-llaves'
                            }));
                            
                            // Abrir modal de registro de solicitante
                            setTimeout(() => {
                                window.dispatchEvent(new CustomEvent('open-modal', {
                                    detail: 'registro-solicitante'
                                }));
                            }, 300);
                        } else {
                            console.error('Error de verificación:', usuarioInfo?.mensaje || 'Error desconocido');
                            document.getElementById('qr-status-solicitud').innerHTML = usuarioInfo?.mensaje ||
                                'Error de verificación';
                        }
                    } else {
                        console.error('Error: RUN inválido');
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
                            const reserva = await crearReservaSolicitante(usuarioEscaneadoSolicitud, espacioProcesado);
                            if (reserva?.success) {
                                // Cerrar modal inmediatamente
                                window.dispatchEvent(new CustomEvent('close-modal', {
                                    detail: 'solicitar-llaves'
                                }));
                                
                                // Actualizar el color del indicador a 'Ocupado' (rojo)
                                const block = state.indicators.find(b => b.id === espacioProcesado);
                                if (block) {
                                    block.estado = '#FF0000'; // Rojo
                                    const originalBlock = state.originalCoordinates.find(b => b.id ===
                                        espacioProcesado);
                                    if (originalBlock) {
                                        originalBlock.estado = '#FF0000';
                                    }
                                    state.lastLocalChange = Date.now();
                                    setTimeout(() => {
                                        drawIndicators();
                                    }, 100);
                                }
                                
                                // Mostrar Sweet Alert de éxito para solicitud
                                Swal.fire({
                                    title: '¡Solicitud Exitosa!',
                                    text: 'Las llaves han sido asignadas correctamente.',
                                    icon: 'success',
                                    confirmButtonText: 'Aceptar',
                                    confirmButtonColor: '#059669',
                                    timer: 1500,
                                    timerProgressBar: true,
                                    showConfirmButton: false
                                });
                                
                                // Mostrar mensaje de éxito
                                console.log('¡Solicitud exitosa! Las llaves han sido asignadas correctamente.');
                                
                                // Resetear interfaz después de un delay
                                setTimeout(() => {
                                    resetearInterfaz();
                                }, 2000);
                            } else {
                                // Manejar diferentes tipos de errores con mensajes específicos
                                let titulo = 'Error';
                                let mensaje = reserva?.mensaje || 'Error al procesar la solicitud';
                                let icono = 'error';

                                // Verificar si es un error de módulos no disponibles
                                if (reserva?.mensaje && reserva.mensaje.includes('módulo actual disponible')) {
                                    titulo = 'Horario No Disponible';
                                    icono = 'info';
                                    mensaje = 'El sistema de reservas solo está disponible durante el horario de clases (08:10 - 23:00). Por favor, intenta durante el horario habilitado.';
                                } else if (reserva?.mensaje && reserva.mensaje.includes('módulos consecutivos disponibles')) {
                                    titulo = 'Módulos No Disponibles';
                                    icono = 'warning';
                                    mensaje = reserva.mensaje;
                                    
                                    // Mostrar información detallada si está disponible
                                    if (reserva.detalles && reserva.detalles.proxima_clase) {
                                        mensaje += `\n\nPróxima clase: ${reserva.detalles.proxima_clase.asignatura} (Módulo ${reserva.detalles.proxima_clase.modulo})`;
                                    }
                                } else if (reserva?.tipo) {
                                    switch (reserva.tipo) {
                                        case 'reserva_activa':
                                            titulo = 'Reserva Activa';
                                            icono = 'warning';
                                            mensaje = 'Ya tienes una reserva activa. Debes finalizarla antes de solicitar una nueva.';
                                            break;
                                        case 'reserva_simultanea':
                                            titulo = 'Reserva Simultánea';
                                            icono = 'warning';
                                            mensaje = 'Ya tienes una reserva activa en ese horario. Debes finalizarla antes de solicitar una nueva.';
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
                                console.log(titulo + ':', mensaje);
                                document.getElementById('qr-status-solicitud').innerHTML = mensaje;
                            }
                        } else {
                            console.error('Error: El espacio no está disponible para solicitar.');
                            document.getElementById('qr-status-solicitud').innerHTML = 'Espacio no disponible';
                        }
                    } else {
                        console.error('Error al verificar espacio:', espacioInfo?.mensaje || 'Error desconocido');
                        document.getElementById('qr-status-solicitud').innerHTML = espacioInfo?.mensaje ||
                            'Error al verificar espacio';
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

        // Función para cerrar modal de selección de módulos
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
            
            // Limpiar variables
            espacioParaReserva = null;
            runParaReserva = null;
            
            // Resetear interfaz
            resetearInterfaz();
        }

        // Función para calcular módulos disponibles consecutivos
        async function calcularModulosDisponibles(idEspacio) {
            try {
                // Obtener hora y día actual
                const ahora = new Date();
                const horaActual = ahora.toLocaleTimeString('es-ES', {
                    hour: '2-digit',
                    minute: '2-digit'
                });
                const diaActual = obtenerDiaActual();

                const response = await fetch(
                    `/api/espacio/${idEspacio}/modulos-disponibles?hora_actual=${horaActual}&dia_actual=${diaActual}`
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
                            console.log('Horario no disponible:', data.detalles.descripcion);
                        }
                        return 1;
                    }
                } else {
                    return 1;
                }
            } catch (error) {
                console.error('Error al calcular módulos disponibles:', error);
                return 1;
            }
        }

        // Mostrar modal de módulos con límite máximo
        async function mostrarModalSeleccionarModulos(idEspacio, run, maxModulos = 2) {
            const modulosDisponibles = await calcularModulosDisponibles(idEspacio);
            // Limitar a máximo 2 módulos según la lógica del negocio
            maxModulosDisponibles = Math.min(modulosDisponibles, maxModulos);
            
            document.getElementById('max-modulos-disponibles').textContent = maxModulosDisponibles;
            const inputModulos = document.getElementById('input-cantidad-modulos');
            inputModulos.max = maxModulosDisponibles;
            inputModulos.value = 1;
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
                // Enfocar el input
                setTimeout(() => {
                    inputModulos.focus();
                }, 100);
            }
        }
        
        // Función para mostrar información detallada de módulos
        function mostrarInformacionModulos(info) {
            const infoContainer = document.getElementById('info-modulos-disponibles');
            if (!infoContainer) return;
            
            let html = '<div class="text-sm text-gray-600 mb-3">';
            
            // Información básica
            html += `<p><strong>Módulo actual:</strong> ${info.modulo_actual}</p>`;
            html += `<p><strong>Módulos disponibles:</strong> ${info.max_modulos}</p>`;
            
            // Módulos específicos disponibles
            if (info.modulos_disponibles && info.modulos_disponibles.length > 0) {
                html += `<p><strong>Módulos:</strong> ${info.modulos_disponibles.join(', ')}</p>`;
            }
            
            // Próxima clase
            if (info.proxima_clase) {
                html += `<p class="text-orange-600"><strong>Próxima clase:</strong> ${info.proxima_clase.asignatura} (Módulo ${info.proxima_clase.modulo})</p>`;
            }
            
            // Clases próximas
            if (info.clases_proximas && info.clases_proximas.length > 0) {
                html += '<p class="text-blue-600"><strong>Clases próximas:</strong></p>';
                info.clases_proximas.forEach(clase => {
                    html += `<p class="ml-2">• ${clase.asignatura} (Módulo ${clase.modulo})</p>`;
                });
            }
            
            // Detalles adicionales
            if (info.detalles) {
                html += `<p class="text-xs text-gray-500 mt-2">Planificaciones: ${info.detalles.planificaciones_encontradas}, Reservas activas: ${info.detalles.reservas_activas}</p>`;
            }
            
            html += '</div>';
            infoContainer.innerHTML = html;
        }

       document.addEventListener('DOMContentLoaded', function () {
    const btnConfirmarModulos = document.getElementById('btn-confirmar-modulos');

    if (btnConfirmarModulos) {
        btnConfirmarModulos.addEventListener('click', async function () {
            const cantidad = parseInt(document.getElementById('input-cantidad-modulos').value);

            if (!espacioParaReserva || !runParaReserva) {
                return;
            }

            // Mostrar mensaje de proceso
            document.getElementById('qr-status').innerHTML = 'Creando reserva...';

            const response = await fetch('/api/crear-reserva', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({
                    run_usuario: runParaReserva,
                    id_espacio: espacioParaReserva,
                    tipo_usuario: 'solicitante'
                })
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

                        console.log('¡Reserva creada! La reserva ha sido creada exitosamente');

                        document.getElementById('qr-status').innerHTML = 'Reserva creada';
                        document.getElementById('qr-status').classList.remove('parpadeo');

                        // Limpiar estado después del Sweet Alert
                        setTimeout(() => {
                            limpiarEstadoCompleto();
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

                console.error('Error al crear reserva:', mensajeError);
                
                // Cerrar modal en caso de error
                cerrarModalModulos();
            }
        });
    }
});


    </script>
</x-show-layout>