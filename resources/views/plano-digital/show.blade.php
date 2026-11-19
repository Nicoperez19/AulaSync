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

        /* Estilos para modal de veto */
        .swal-veto-popup {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%) !important;
            padding: 0 !important;
            border-radius: 1rem !important;
            box-shadow: 0 20px 25px -5px rgba(239, 68, 68, 0.3), 0 10px 10px -5px rgba(239, 68, 68, 0.2) !important;
        }

        .swal-veto-close {
            color: white !important;
            font-size: 1.5em !important;
            background: rgba(255, 255, 255, 0.2) !important;
            border-radius: 50% !important;
            width: 32px !important;
            height: 32px !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            transition: all 0.3s ease !important;
        }

        .swal-veto-close:hover {
            background: rgba(255, 255, 255, 0.3) !important;
            transform: rotate(90deg) !important;
        }

        .swal2-icon.swal2-error {
            display: none !important;
        }

        /* Animación para el ícono de veto */
        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-10px); }
            75% { transform: translateX(10px); }
        }

        .swal-veto-icon {
            animation: shake 0.5s ease-in-out;
        }

        /* Estilos para el estado de mantención */
        @keyframes rotarEngranaje {
            from {
                transform: rotate(0deg);
            }
            to {
                transform: rotate(360deg);
            }
        }

        .mantenimiento-icon {
            animation: rotarEngranaje 2s linear infinite;
        }

        .espacio-mantenimiento {
            opacity: 0.7;
            filter: grayscale(50%);
        }

        .espacio-mantenimiento-overlay {
            position: relative;
            pointer-events: none;
        }

        .espacio-mantenimiento-overlay::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: repeating-linear-gradient(
                45deg,
                transparent,
                transparent 10px,
                rgba(107, 114, 128, 0.1) 10px,
                rgba(107, 114, 128, 0.1) 20px
            );
            pointer-events: none;
        }

        /* Ocultar barra de scroll visualmente en clases actuales pero manteniendo desplazamiento */
        #sidebar-modulo-list {
            -ms-overflow-style: none !important;
            scrollbar-width: none !important;
        }
        #sidebar-modulo-list::-webkit-scrollbar {
            display: none !important;
            width: 0 !important;
            height: 0 !important;
            background: transparent !important;
        }
    </style>
    <div id="plano-digital-root" data-config="{{ json_encode($planoConfig ?? []) }}" class="flex h-screen overflow-hidden">
        <aside
            class="fixed top-0 left-0 z-40 flex flex-col w-56 h-screen pt-2 text-base border-r border-gray-200 md:w-56 bg-light-cloud-blue dark:border-gray-700 md:text-sm sm:text-xs overflow-hidden">

            <div class="flex-shrink-0 flex flex-col items-center gap-2 md:gap-1">
                <a href="{{ auth()->user()->hasRole('Usuario') ? route('espacios.show') : route('dashboard') }}" class="mb-1">
                    <x-application-logo-navbar class="w-10 h-10 md:w-8 md:h-8 sm:w-6 sm:h-6" />
                </a>
            </div>

            <div class="flex flex-col flex-1 min-h-0 w-full max-w-md p-1 mx-auto pb-2">
                <div class="w-full mt-6">
                    <div class="mt-3 text-sm text-white bg-red-700/90 rounded-xl px-3 py-2 hidden">
                        <div class="flex items-center gap-2 text-red-50">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                            <span class="text-xs font-medium">Horario:</span>
                            <span id="horario-actual" class="text-xs font-semibold">--:-- - --:--</span>
                        </div>
                    </div>
                </div>

                <!-- Tarjeta de QR y Usuario -->
                <div class="w-full mt-auto mb-2">
                    <div class="mt-2 mb-2 text-white bg-light-cloud-blue">
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

                        <hr class="my-2 border-white/30">

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
                                <span class="font-bold text-gray-800">Usuario:</span>
                                <span id="nombre-usuario" class="ml-auto text-gray-700">--</span>
                            </div>
                        </div>

                       <input type="text" id="qr-input" class="absolute w-px h-px p-0 m-0 opacity-0" autocomplete="off" autocorrect="off" autocapitalize="off" spellcheck="false" autofocus>

                    </div>
                </div>

                <!-- Lista de clases del módulo actual -->
                <div class="w-full mt-3 mb-2 flex-1 min-h-0">
                    <div class="flex h-full flex-col rounded-xl bg-red-700 rounded px-3 py-3 shadow-[inset_0_1px_0_rgba(255,255,255,0.12)]">
                       
                        <div id="sidebar-modulo-list" class="space-y-2 overflow-y-auto min-h-0 max-h-[25vh] select-none" style="-ms-overflow-style: none; scrollbar-width: none;"></div>
                    </div>
                </div>

                <!-- Leyenda abajo del todo -->
                <div class="flex flex-col items-center justify-end w-full max-w-md mx-auto mt-2">
                    <div class="w-full">
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
                                <div class="w-3 h-3 bg-emerald-500 rounded-full shadow-sm"></div>
                                <span class="flex-1 text-xs text-white">Disponible</span>
                            </div>
                            <div class="flex items-center w-full gap-1">
                                <div class="w-3 h-3 bg-red-500 rounded-full shadow-sm"></div>
                                <span class="flex-1 text-xs text-white">Ocupado</span>
                            </div>
                            <div class="flex items-center w-full gap-1">
                                <div class="w-3 h-3 bg-amber-500 rounded-full shadow-sm"></div>
                                <span class="flex-1 text-xs text-white">Reservado</span>
                            </div>
                            <div class="flex items-center w-full gap-1">
                                <div class="w-3 h-3 bg-indigo-500 rounded-full shadow-sm"></div>
                                <span class="flex-1 text-xs text-white">Programado</span>
                            </div>
                            <div class="flex items-center w-full gap-1">
                                <div class="w-3 h-3 bg-blue-500 rounded-full shadow-sm"></div>
                                <span class="flex-1 text-xs text-white">Próximo</span>
                            </div>
                            <div class="flex items-center w-full gap-1">
                                <div class="w-3 h-3 bg-gray-400 rounded-full shadow-sm"></div>
                                <span class="flex-1 text-xs text-white">En Mantención</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Enlace Volver -->
                <div class="w-full mt-4">
                    <a href="{{ auth()->user()->hasRole('Usuario') ? route('espacios.show') : route('dashboard') }}"
                       class="flex items-center justify-center gap-2 px-4 py-2 text-sm font-medium text-white bg-gray-600 rounded-lg hover:bg-gray-700 transition-colors duration-100 shadow-md"
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
                                            class="px-4 py-2 text-sm font-semibold transition-all duration-150 rounded-t-xl border border-b-0
                                            {{ $piso['id_mapa'] === $mapa->id_mapa
                                                ? 'bg-light-cloud-blue text-white border-light-cloud-blue'
                                    : 'bg-white text-gray-700 border-gray-300 hover:bg-gray-100 hover:text-light-cloud-blue' }}" role="tab"
                                            aria-selected="{{ $piso['id_mapa'] === $mapa->id_mapa ? 'true' : 'false' }}">
                                            {{ $piso['nombre'] }}
                                        </a>
                                    </li>
                                @endif
                            @endforeach
                        </ul>
                        <!-- Card para el canvas y controles -->
                        <div class="flex flex-col h-full">
                            <div class="p-4">
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                                    Plano: {{ $mapa->piso?->display_name }},
                                    {{ $mapa->piso?->facultad?->nombre_facultad }},
                                    Sede {{ $mapa->piso?->facultad?->sede?->nombre_sede }}
                                </h3>
                            </div>


                            <div class="relative flex-1 min-h-0 h-[calc(100vh-180px)] w-[calc(100%+1rem)] -mx-2">
                                <!-- Canvas para la imagen base -->
                                <canvas id="mapCanvas"
                                    class="absolute inset-0 w-full h-full bg-white dark:bg-gray-800"></canvas>

                                <!-- Canvas para los indicadores -->
                                <canvas id="indicatorsCanvas"
                                    class="absolute inset-0 w-full h-full pointer-events-auto"></canvas>

                                <!-- Reloj digital y módulo actual (flotante sobre el canvas) -->
                                <div id="modal-reloj"
                                    class="absolute bottom-4 right-4 sm:bottom-6 sm:right-8 z-50 pointer-events-auto transition-all duration-300 ease-out group"
                                    style="position: absolute; bottom: 1.5rem; right: 2rem; z-index: 50;">
                                    <!-- Estado Expandido -->
                                    <div id="reloj-expandido"
                                        class="bg-gradient-to-br from-[#d2091e]/95 to-[#b10718]/95 backdrop-blur-md shadow-2xl shadow-red-950/20 rounded-2xl border border-white/20 px-4 py-3 sm:px-5 sm:py-3.5 flex items-center gap-3 sm:gap-4 transition-all duration-300 relative z-50"
                                        style="z-index: 50;">
                                        <div class="p-2 sm:p-2.5 bg-white/15 text-white rounded-xl transition-all duration-300 shrink-0">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 sm:w-6 sm:h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                        </div>
                                        <div class="flex flex-col select-none">
                                            <span id="hora-actual" class="font-mono text-xl sm:text-2xl font-black text-white leading-none tracking-tight my-0.5">--:--:--</span>
                                            <span id="modulo-actual" class="text-xs sm:text-sm font-bold text-red-100">Módulo actual: -</span>
                                        </div>
                                        <button type="button" onclick="toggleModalReloj(true)" title="Minimizar reloj" class="ml-1 text-white/70 hover:text-white hover:bg-white/20 rounded-lg p-1.5 transition-colors duration-150 shrink-0">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7" /></svg>
                                        </button>
                                    </div>

                                    <!-- Estado Minimizado -->
                                    <div id="reloj-minimizado"
                                        onclick="toggleModalReloj(false)"
                                        title="Mostrar reloj y módulo actual"
                                        class="hidden cursor-pointer bg-gradient-to-br from-[#d2091e]/95 to-[#b10718]/95 backdrop-blur-md shadow-xl hover:shadow-2xl rounded-full border border-white/20 px-3.5 py-2 flex items-center gap-2 text-white hover:scale-105 transition-all duration-200 relative z-50"
                                        style="z-index: 50;">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        <span class="font-mono text-xs sm:text-sm font-bold" id="modal-hora-minimizada">--:--:--</span>
                                        <svg class="w-3.5 h-3.5 text-white/70" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 15l7-7 7 7" /></svg>
                                    </div>
                                </div>


                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Botones de desocupación forzada removidos - la liberación se realiza únicamente mediante escaneo QR -->

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
                    <!-- Grupo: Estado Badge -->
                    <div class="flex flex-col items-center gap-1">
                        <!-- Badge de estado -->
                        <div class="flex items-center gap-2">
                            <span id="estadoIcon" class="w-3 h-3 rounded-full"></span>
                            <span id="modalEstado" class="text-sm font-semibold text-white">Cargando...</span>
                        </div>
                    </div>
                    
                    <!-- Botón cerrar -->
                    <button onclick="cerrarModalEspacio(); qrInputManager.setActiveInput('main')"
                        class="ml-2 text-3xl font-bold text-white hover:text-gray-200 transition-colors duration-100 cursor-pointer"
                        title="Cerrar modal (Esc)"
                        aria-label="Cerrar modal">&times;</button>
                </div>
            </div>

            <!-- Contenido del modal -->
            <div class="p-6 bg-gray-50 overflow-y-auto max-h-[70vh] flex-1">
                <!-- Vista de Pasos: Clase Anterior - Próxima Clase -->
                <div id="pasoClasesContainer" class="p-6 mb-6 bg-white shadow-sm rounded-xl" style="display: none;">
                    <h3 class="mb-6 text-lg font-semibold text-gray-800">Cronología de Clases</h3>
                    
                    <div class="flex items-center justify-between">
                        <!-- Paso 1: Clase Anterior -->
                        <div class="flex flex-col items-center flex-1">
                            <div class="flex items-center justify-center w-12 h-12 mb-3 text-white bg-gray-400 rounded-full">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                            </div>
                            <div class="text-xs font-medium text-gray-500 uppercase mb-3">Clase Anterior</div>
                            <div id="pasoClaseAnterior" class="w-full px-4 py-3 text-sm text-center bg-gray-50 border border-gray-200 rounded-lg min-h-[80px] flex items-center justify-center">
                                <p class="text-gray-400 text-xs">Sin información</p>
                            </div>
                        </div>

                        <!-- Línea conectora 1 -->
                        <div class="flex-shrink-0 w-12 h-0.5 bg-gray-300 mx-2"></div>

                        <!-- Paso 2: Ahora -->
                        <div class="flex flex-col items-center flex-1">
                            <div id="pasoAhoraIcono" class="flex items-center justify-center w-12 h-12 mb-3 text-white bg-blue-500 rounded-full">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            <div id="pasoAhoraEtiqueta" class="text-xs font-medium text-blue-600 uppercase mb-3">Ahora</div>
                            <div id="pasoEstadoActual" class="w-full px-4 py-3 text-sm text-center bg-blue-50 border border-blue-200 rounded-lg min-h-[80px] flex items-center justify-center">
                                <p class="text-gray-400 text-xs">Sin información</p>
                            </div>
                        </div>

                        <!-- Línea conectora 2 -->
                        <div class="flex-shrink-0 w-12 h-0.5 bg-gray-300 mx-2"></div>

                        <!-- Paso 3: Próxima Clase -->
                        <div class="flex flex-col items-center flex-1">
                            <div class="flex items-center justify-center w-12 h-12 mb-3 text-gray-400 bg-white border-2 border-gray-300 rounded-full">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                </svg>
                            </div>
                            <div class="text-xs font-medium text-gray-500 uppercase mb-3">Próxima Clase</div>
                            <div id="pasoClaseProxima" class="w-full px-4 py-3 text-sm text-center bg-gray-50 border border-gray-200 rounded-lg min-h-[80px] flex items-center justify-center">
                                <p class="text-gray-400 text-xs">Sin información</p>
                            </div>
                        </div>
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

                <!-- Próxima clase programada -->
                <div id="proximaClaseContainer" class="p-6 mb-6 bg-white border-l-4 border-purple-500 shadow-sm rounded-xl" style="display: none;">
                    <h3 class="mb-4 text-xl font-semibold text-gray-800">
                        <i class="mr-2 text-purple-500 fas fa-clock"></i>
                        Próxima Clase
                    </h3>
                    <div id="proximaClaseInfo" class="space-y-3">
                        <!-- La información se insertará dinámicamente -->
                    </div>

                </div>


            </div>
        </div>
    </div>

    <!-- Modal para Sala de Estudio -->
    <div id="modal-sala-estudio" class="fixed inset-0 z-[9999] flex items-center justify-center bg-black bg-opacity-50 hidden">
        <div class="flex flex-col w-full max-w-4xl max-h-screen mx-2 overflow-hidden bg-white rounded-lg shadow-lg md:mx-8">
            <!-- Encabezado con diseño tipo banner -->
            <div class="relative flex flex-col gap-6 p-8 bg-gradient-to-r bg-red-600 md:flex-row md:items-center md:justify-between">
                <!-- Círculos decorativos -->
                <span class="absolute top-0 left-0 w-32 h-32 -translate-x-1/2 -translate-y-1/2 bg-white rounded-full pointer-events-none bg-opacity-10"></span>
                <span class="absolute top-0 right-0 w-32 h-32 translate-x-1/2 -translate-y-1/2 bg-white rounded-full pointer-events-none bg-opacity-10"></span>

                <div class="flex items-center flex-1 min-w-0 gap-5">
                    <div class="flex flex-col items-center justify-center flex-shrink-0">
                        <div class="p-4 mb-2 bg-white rounded-full bg-opacity-20">
                            <i class="text-3xl text-white fa-solid fa-book"></i>
                        </div>
                    </div>
                    <div class="flex flex-col min-w-0">
                        <h1 id="modalSalaEstudioTitulo" class="text-3xl font-bold text-white truncate">Sala de Estudio</h1>
                        <div class="flex items-center gap-2 mt-1">
                            <span id="modalSalaEstudioCapacidad" class="text-lg truncate text-white/80">Capacidad disponible</span>
                        </div>
                    </div>
                </div>

                <div class="flex items-center self-start flex-shrink-0 gap-3 md:self-center">
                    <button onclick="cerrarModalSalaEstudio(); qrInputManager.setActiveInput('main')"
                        class="ml-2 text-3xl font-bold text-white hover:text-gray-200 transition-colors duration-100 cursor-pointer"
                        title="Cerrar modal (Esc)"
                        aria-label="Cerrar modal">&times;</button>
                </div>
            </div>

            <!-- Contenido del modal -->
            <div class="p-6 bg-gray-50 overflow-y-auto max-h-[70vh] flex-1">
                <!-- Información de capacidad -->
                <div class="p-6 mb-6 bg-white border-l-4 border-red-500 shadow-sm rounded-xl">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-xl font-semibold text-gray-800">
                            <i class="mr-2 text-red-500 fas fa-users"></i>
                            Capacidad
                        </h3>
                        <div class="flex items-center gap-4">
                            <span class="text-sm text-gray-600">
                                <span id="salaEstudioOcupados" class="text-2xl font-bold text-red-600">0</span>
                                <span class="text-gray-500">/</span>
                                <span id="salaEstudioCapacidadMax" class="text-lg font-semibold text-gray-700">0</span>
                            </span>
                        </div>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-4">
                        <div id="salaEstudioProgressBar" class="bg-red-600 h-4 rounded-full transition-all duration-150" style="width: 0%"></div>
                    </div>
                </div>

              

                <!-- Alumnos registrados -->
                <div class="p-6 bg-white border-l-4 border-green-500 shadow-sm rounded-xl">
                    <h3 class="mb-4 text-xl font-semibold text-gray-800">
                        <i class="mr-2 text-green-500 fas fa-user-check"></i>
                        Alumnos Registrados
                    </h3>
                    <div id="listaSalaEstudioAlumnos" class="space-y-2">
                        <p class="text-sm text-gray-500 text-center py-4">No hay alumnos registrados aún</p>
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


    <!-- Modal de espera de llaves (Rediseñado con Estilo Banner) -->
    <div id="modal-espera-llaves" class="fixed inset-0 z-[10000] flex items-center justify-center bg-black bg-opacity-50 hidden">
        <div class="relative w-full max-w-lg mx-4 bg-white rounded-2xl shadow-2xl overflow-hidden transition-all transform scale-95 duration-150 opacity-0" id="modal-espera-llaves-content">
            
            <!-- Encabezado Rojo tipo Banner (Igual a la imagen de referencia) -->
            <div class="relative flex items-center justify-between p-6 bg-red-700">
                <!-- Círculos decorativos -->
                <span class="absolute top-0 left-0 w-24 h-24 -translate-x-1/2 -translate-y-1/2 bg-white rounded-full pointer-events-none bg-opacity-10"></span>
                <span class="absolute top-0 right-0 w-24 h-24 translate-x-1/2 -translate-y-1/2 bg-white rounded-full pointer-events-none bg-opacity-10"></span>
                
                <div class="flex items-center gap-4 relative z-10">
                    <!-- Icono Circular Blanco con icono rojo -->
                    <div class="flex items-center justify-center w-12 h-12 bg-white rounded-full shadow-md">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-red-700" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                           <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
                        </svg>
                    </div>
                    <h2 class="text-2xl font-bold text-white tracking-tight">Escaneo de Llaves</h2>
                </div>

                <!-- Botón Cerrar Cuadrado Blanco con X Roja (Igual a la imagen) -->
                <button onclick="cerrarModalEsperaLlaves()" class="flex items-center justify-center w-9 h-9 bg-white rounded-md shadow-lg hover:bg-gray-100 transition-all duration-100 group relative z-10">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-red-700 group-hover:scale-110 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <!-- Contenido del Cuerpo -->
            <div class="p-8 flex flex-col items-center text-center space-y-8">
                
                <div class="space-y-3">
                    <h3 class="text-3xl font-extrabold text-gray-800" id="espera-llaves-nombre">¡Hola!</h3>
                    <p class="text-lg text-gray-500 font-medium">Por favor, acerca las llaves al lector para continuar.</p>
                </div>

                <!-- Animación de Escaneo mejorada -->
                <div class="relative w-48 h-48 flex items-center justify-center bg-gray-50 rounded-3xl border-2 border-dashed border-gray-200 group-hover:border-red-200 transition-colors">
                    <!-- Marco de escaneo -->
                    <div class="absolute top-4 left-4 w-10 h-10 border-t-4 border-l-4 border-red-600 rounded-tl-xl"></div>
                    <div class="absolute top-4 right-4 w-10 h-10 border-t-4 border-r-4 border-red-600 rounded-tr-xl"></div>
                    <div class="absolute bottom-4 left-4 w-10 h-10 border-b-4 border-l-4 border-red-600 rounded-bl-xl"></div>
                    <div class="absolute bottom-4 right-4 w-10 h-10 border-b-4 border-r-4 border-red-600 rounded-br-xl"></div>

                    <!-- Icono Llave Animado -->
                    <div class="text-red-600 animate-pulse">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-28 w-28" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                           <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
                        </svg>
                    </div>
                </div>

                <!-- Banner Informativo Azul (estilo el de la imagen de referencia) -->
                <div class="w-full bg-blue-50 border border-blue-200 p-4 rounded-xl flex items-start gap-4 text-left shadow-sm">
                    <div class="bg-blue-600 p-2 rounded-lg shadow-md mt-0.5">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div class="flex-1">
                        <p class="font-bold text-blue-900 text-sm tracking-tight">Información de Escaneo</p>
                        <p class="text-blue-700 text-xs leading-relaxed mt-1 font-medium">
                            El sistema está esperando el código del llavero vinculado a este espacio. Acerque el sensor al lector para completar la operación.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

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
                        class="flex items-center justify-center px-6 py-3 text-white transition-colors duration-100 bg-red-600 rounded-lg hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                        <span class="font-semibold">Sin asistentes</span>
                    </button>
                    <button id="btn-con-asistentes"
                        class="flex items-center justify-center px-6 py-3 text-white transition-colors duration-100 bg-green-600 rounded-lg hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2">
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
                        class="ml-2 text-3xl font-bold text-white hover:text-gray-200 transition-colors duration-100 cursor-pointer"
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
                            <span class="text-lg font-semibold text-white/80">{{ date('Y') }}</span>
                        </div>
                    </div>
                </div>

                <div class="flex items-center self-start flex-shrink-0 gap-3 md:self-center">
                    <button onclick="cerrarModalRegistroSolicitante(); qrInputManager.setActiveInput('main')"
                        class="ml-2 text-3xl font-bold text-white hover:text-gray-200 transition-colors duration-100 cursor-pointer"
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
                        El usuario no está registrado como profesor.
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

    @vite(['resources/js/views/plano-digital/show.js'])

    <!-- Modales del Panel de Administración -->
    <x-modal-agregar-reserva />
    <x-modal-editar />
    <x-modal-editar-reservas />
    <x-modal-editar-espacios />

    <!-- JavaScript del Panel de Administración -->
    <script src="{{ asset('js/admin-panel.js') }}"></script>

</x-show-layout>
