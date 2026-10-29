<x-table-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 pr-6 md:flex-row md:items-center md:justify-between" 
             x-data="{ nombreSede: '{{ $nombreSedeActual }}' }"
             @sedes:seleccionada.window="nombreSede = $event.detail.nombre">
            <div class="flex items-center gap-4">
                <!-- Botón Volver (solo visible cuando hay sesión activa) -->
                @auth
                <a href="{{ auth()->user()->hasRole('Usuario') ? route('espacios.show') : route('dashboard') }}" 
                   class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-white bg-gray-600 rounded-lg hover:bg-gray-700 transition-colors duration-200 shadow-md">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="size-5">
                        <path d="M11.47 3.841a.75.75 0 0 1 1.06 0l8.69 8.69a.75.75 0 1 0 1.06-1.061l-8.689-8.69a2.25 2.25 0 0 0-3.182 0l-8.69 8.69a.75.75 0 1 0 1.061 1.06l8.69-8.689Z" />
                        <path d="m12 5.432 8.159 8.159c.03.03.06.058.091.086v6.198c0 1.035-.84 1.875-1.875 1.875H15a.75.75 0 0 1-.75-.75v-4.5a.75.75 0 0 0-.75-.75h-3a.75.75 0 0 0-.75.75V21a.75.75 0 0 1-.75.75H5.625a1.875 1.875 0 0 1-1.875-1.875v-6.198a2.29 2.29 0 0 0 .091-.086L12 5.432Z" />
                    </svg>
                    Volver
                </a>
                @endauth

                <!-- Logo y título -->
            <div class="flex items-center gap-4 justify-center flex-1">
               <img src='images/Logo-UCSC-Color-Horizontal.png' alt="Logo" class="h-16 w-auto" >
               <div class="flex items-center px-4 py-2 bg-white rounded-lg shadow-sm border border-gray-200">
                   <div class="text-center">
                       <div class="text-lg font-bold text-gray-900" x-text="nombreSede.toLowerCase().startsWith('sede') ? nombreSede : 'Sede ' + nombreSede"></div>
                   </div>
               </div>
            </div>
            </div>            
            
            <!-- Indicador de feriado y paginación -->
            <div class="flex items-center justify-center gap-4" 
                 x-data="{ 
                     paginaActual: 1, 
                     totalPaginas: 1,
                     esFeriado: false,
                     nombreFeriado: '',
                     periodoNoIniciado: false,
                     nombrePeriodo: ''
                 }" 
                 @actualizar-pagina.window="paginaActual = $event.detail.pagina; totalPaginas = $event.detail.total"
                 @actualizar-feriado.window="esFeriado = $event.detail.esFeriado; nombreFeriado = $event.detail.nombreFeriado"
                 @actualizar-periodo.window="periodoNoIniciado = $event.detail.periodoNoIniciado; nombrePeriodo = $event.detail.nombrePeriodo">
                
                <!-- Mensaje de Periodo No Iniciado -->
                <div x-show="periodoNoIniciado" 
                     x-transition
                     class="flex items-center gap-2 px-4 py-2 bg-yellow-500 border-yellow-600 rounded-lg shadow-md">
                    <i class="fas fa-hourglass-half text-xl text-white"></i>
                    <span class="text-sm font-bold text-white">Periodo no iniciado: <span x-text="nombrePeriodo"></span></span>
                </div>
                
                <!-- Mensaje de Feriado -->
                <div x-show="esFeriado && !periodoNoIniciado" 
                     x-transition
                     class="flex items-center gap-2 px-4 py-2 bg-red-600  border-red-600 rounded-lg shadow-md">
                    <i class="fas fa-calendar-xmark text-xl text-white"></i>
                    <span class="text-sm font-bold text-white" x-text="nombreFeriado"></span>
                </div>
                
                <!-- Indicador de página -->
                <div x-show="totalPaginas > 1" class="inline-flex items-center gap-2 px-4 py-2 bg-gray-100 rounded-lg border shadow-sm">
                    <span class="text-sm font-medium text-gray-600">Página</span>
                    <span class="px-2 py-1 bg-red-600 text-white text-sm font-bold rounded" x-text="paginaActual"></span>
                    <span class="text-sm text-gray-600">de</span>
                    <span class="px-2 py-1 bg-gray-200 text-gray-700 text-sm font-medium rounded" x-text="totalPaginas"></span>
                </div>
            </div>

            <!-- Información del módulo actual -->
            <div class="flex items-center gap-4">
                <div class="hidden md:flex items-center gap-3 px-4 py-2 bg-white rounded-lg shadow-sm border border-gray-200">
                    <div class="text-center">
                        <div class="text-xs font-medium text-gray-500 uppercase tracking-wide">Hora Actual</div>
                        <div class="text-lg font-mono font-bold text-gray-900" id="hora-actual">--:--:--</div>
                    </div>
                    <div class="w-px h-8 bg-gray-300"></div>
                    <div class="text-center">
                        <div class="text-xs font-medium text-gray-500 uppercase tracking-wide">Módulo</div>
                        <div class="text-lg font-bold text-light-cloud-blue" id="modulo-actual">--</div>
                    </div>
                </div>
            </div>
        </div>
    </x-slot>

    <!-- Componente Livewire principal -->
    <livewire:modulos-actuales-table />

    <!-- Reloj flotante para pantallas pequeñas -->
    <div id="reloj-flotante" 
         class="fixed top-4 right-4 z-50 md:hidden bg-light-cloud-blue shadow-lg rounded-xl border border-gray-200 px-4 py-3 flex flex-col items-center gap-1 min-w-[140px] text-white">
        <span class="px-2 font-mono text-lg font-bold text-white" id="hora-actual-mobile"></span>
        <span class="px-2 font-mono text-sm text-white" id="modulo-actual-mobile"></span>
    </div>

    <!-- Configuración para el script JS externo -->
    <div id="tablero-config"
         data-horarios-modulos="{{ json_encode(\App\Helpers\ModulosHelper::getHorariosModulos()) }}"
         class="hidden">
    </div>

    <!-- Cargar script externo del tablero -->
    @vite(['resources/js/tablero.js'])
</x-table-layout>
