<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <div class="flex items-center gap-3">
                <div class="p-2 rounded-xl bg-light-cloud-blue">
                    <i class="text-2xl text-white fa-solid fa-gauge-high"></i>
                </div>
                <div>
                    <h2 class="text-2xl font-bold leading-tight">Dashboard</h2>
                    <p class="text-sm text-gray-500">Resumen y reportes rápidos del sistema</p>
                </div>
            </div>
        </div>
    </x-slot>

    <!-- Modal fijo de reloj digital y módulo actual -->
    <div id="modal-reloj"
        class="fixed bottom-6 right-8 z-50 bg-gradient-to-br from-[#d2091e]/95 to-[#b10718]/95 backdrop-blur-md shadow-2xl shadow-red-950/20 rounded-2xl border border-white/20 px-5 py-3.5 flex items-center gap-4 min-w-[200px] transition-all duration-300 ease-out cursor-default group">
        <div class="p-2.5 bg-white/15 text-white rounded-xl transition-all duration-300 shrink-0">
            <i class="fa-solid fa-clock text-lg"></i>
        </div>
        <div class="flex flex-col">
            <span class="font-mono text-xl font-extrabold text-white leading-none tracking-tight my-1" id="modal-hora-actual">--:--:--</span>
            <span class="text-xs font-semibold text-red-200" id="modal-modulo-actual">Módulo actual: -</span>
        </div>
    </div>

    <div class="w-full px-8 py-6">
        <!-- Sección: Porcentaje de Ocupación -->
        <div class="mb-8">
            <h3 class="text-lg font-bold text-gray-700 mb-4 flex items-center">
                <i class="fas fa-percent mr-2 text-blue-600"></i>
                Porcentaje de Ocupación Semanal
            </h3>
            
            <div class="bg-white rounded-2xl shadow-md border border-gray-100 p-6">
                <!-- Nav Pills + Leyenda -->
                <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6">
                    <!-- Nav Pills -->
                    <div class="flex flex-wrap gap-2">
                        <button onclick="cambiarTabOcupacion('todos')" id="tab-ocupacion-todos" class="px-4 py-2 text-xs font-semibold rounded-lg transition duration-150 tab-ocupacion-btn bg-blue-600 text-white shadow-sm">
                            Todos
                        </button>
                        <button onclick="cambiarTabOcupacion('laboratorios')" id="tab-ocupacion-laboratorios" class="px-4 py-2 text-xs font-semibold rounded-lg transition duration-150 tab-ocupacion-btn bg-gray-50 text-gray-600 hover:bg-gray-100">
                            Laboratorios
                        </button>
                        <button onclick="cambiarTabOcupacion('salas_clases')" id="tab-ocupacion-salas_clases" class="px-4 py-2 text-xs font-semibold rounded-lg transition duration-150 tab-ocupacion-btn bg-gray-50 text-gray-600 hover:bg-gray-100">
                            Salas de Clases
                        </button>
                        <button onclick="cambiarTabOcupacion('salas_estudio')" id="tab-ocupacion-salas_estudio" class="px-4 py-2 text-xs font-semibold rounded-lg transition duration-150 tab-ocupacion-btn bg-gray-50 text-gray-600 hover:bg-gray-100">
                            Salas de Estudio
                        </button>
                    </div>
                    
                    <!-- Leyenda (Niveles de Ocupación) -->
                    <div class="flex flex-wrap gap-3 items-center text-xs bg-slate-50 border border-slate-100 rounded-xl px-3 py-2 shadow-xs shrink-0">
                        <span class="font-bold text-slate-500 mr-1">Ocupación:</span>
                        <span class="flex items-center gap-1.5">
                            <span class="inline-block w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
                            <span class="text-emerald-700 font-semibold">0% - 35%</span>
                        </span>
                        <span class="flex items-center gap-1.5">
                            <span class="inline-block w-2.5 h-2.5 rounded-full bg-amber-500"></span>
                            <span class="text-amber-700 font-semibold">35% - 75%</span>
                        </span>
                        <span class="flex items-center gap-1.5">
                            <span class="inline-block w-2.5 h-2.5 rounded-full bg-red-500"></span>
                            <span class="text-red-700 font-semibold">75% - 100%</span>
                        </span>
                    </div>
                </div>
                               
                <!-- Contenedor dinámico de la grilla -->
                <div id="ocupacion-grid-container" class="overflow-x-auto relative min-h-[200px]">
                    <div class="flex flex-col items-center justify-center py-20 text-gray-400">
                        <div class="inline-block w-8 h-8 border-4 border-blue-600 border-t-transparent rounded-full animate-spin mb-4"></div>
                        <p class="text-sm">Cargando ocupación semanal...</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sección de Reportes -->
        @can('reportes')
        <div class="mb-8">
            <h3 class="text-lg font-bold text-gray-700 mb-4 flex items-center">
                <i class="fas fa-chart-bar mr-2 text-blue-600"></i>
                Reportes de Uso de Espacios
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <!-- Accesos registrados (QR) -->
                <a href="{{ route('reportes.accesos') }}"
                   class="flex items-center justify-between p-6 bg-white rounded-2xl border border-gray-100 shadow-md hover:shadow-lg transition duration-200 group">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 bg-purple-100 rounded-xl flex items-center justify-center text-purple-600 group-hover:bg-purple-600 group-hover:text-white transition duration-200">
                            <i class="fas fa-qrcode text-xl"></i>
                        </div>
                        <div>
                            <h4 class="font-bold text-gray-800 text-lg">Accesos registrados (QR)</h4>
                            <p class="text-sm text-gray-500">Revisa los registros de acceso escaneados por código QR</p>
                        </div>
                    </div>
                    <div class="text-gray-400 group-hover:text-purple-600 transition duration-200 mr-2">
                        <i class="fas fa-chevron-right text-lg"></i>
                    </div>
                </a>    
            
            <!-- Control de Clases -->
                <a href="{{ route('clases-no-realizadas.index') }}"
                   class="flex items-center justify-between p-6 bg-white rounded-2xl border border-gray-100 shadow-md hover:shadow-lg transition duration-200 group">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center text-blue-600 group-hover:bg-blue-600 group-hover:text-white transition duration-200">
                            <i class="fas fa-clipboard-check text-xl"></i>
                        </div>
                        <div>
                            <h4 class="font-bold text-gray-800 text-lg">Control de Clases</h4>
                            <p class="text-sm text-gray-500">Monitorea la asistencia, ausencias y recuperación de clases</p>
                        </div>
                    </div>
                    <div class="text-gray-400 group-hover:text-blue-600 transition duration-200 mr-2">
                        <i class="fas fa-chevron-right text-lg"></i>
                    </div>
                </a>

                <!-- Salas de Estudio -->
                <a href="{{ route('reportes.salas-estudio') }}"
                   class="flex items-center justify-between p-6 bg-white rounded-2xl border border-gray-100 shadow-md hover:shadow-lg transition duration-200 group">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 bg-green-100 rounded-xl flex items-center justify-center text-green-600 group-hover:bg-green-600 group-hover:text-white transition duration-200">
                            <i class="fas fa-book text-xl"></i>
                        </div>
                        <div>
                            <h4 class="font-bold text-gray-800 text-lg">Salas de Estudio</h4>
                            <p class="text-sm text-gray-500">Analiza el uso y reservas de las salas de estudio</p>
                        </div>
                    </div>
                    <div class="text-gray-400 group-hover:text-green-600 transition duration-200 mr-2">
                        <i class="fas fa-chevron-right text-lg"></i>
                    </div>
                </a>
                
                <!-- Uso del Auditorio -->
                <a href="{{ route('reportes.uso-auditorio') }}"
                   class="flex items-center justify-between p-6 bg-white rounded-2xl border border-gray-100 shadow-md hover:shadow-lg transition duration-200 group">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 bg-amber-100 rounded-xl flex items-center justify-center text-amber-600 group-hover:bg-amber-600 group-hover:text-white transition duration-200">
                            <i class="fas fa-landmark text-xl"></i>
                        </div>
                        <div>
                            <h4 class="font-bold text-gray-800 text-lg">Uso del Auditorio</h4>
                            <p class="text-sm text-gray-500">Consulta el historial y estadísticas de uso del auditorio</p>
                        </div>
                    </div>
                    <div class="text-gray-400 group-hover:text-amber-600 transition duration-200 mr-2">
                        <i class="fas fa-chevron-right text-lg"></i>
                    </div>
                </a>

                
            </div>
        </div>
        @endcan

        <!-- Sección: Horarios del día actual - Módulos actuales -->
        <div class="mb-8">
            <h3 class="text-lg font-bold text-gray-700 mb-4 flex items-center">
                <i class="fas fa-calendar-day mr-2 text-blue-600"></i>
                Horarios del Día Actual - Módulos Actuales
            </h3>
            <div class="bg-white rounded-2xl shadow-md border border-gray-100 p-6">
                <!-- Contenedor dinámico del horario -->
                <div id="horarios-actual-container">
                    <div class="flex flex-col items-center justify-center py-12 text-gray-400">
                        <div class="inline-block w-8 h-8 border-4 border-blue-600 border-t-transparent rounded-full animate-spin mb-4"></div>
                        <p class="text-sm">Cargando horario actual...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Configuración para el script JS externo -->
    <div id="dashboard-config"
         data-horarios-actual-route="{{ route('dashboard.horarios-actual') }}"
         data-ocupacion-datos-route="{{ route('dashboard.ocupacion-datos') }}"
         data-horarios-modulos="{{ json_encode(\App\Helpers\ModulosHelper::getHorariosModulos()) }}"
         class="hidden">
    </div>

    <!-- Cargar script externo del dashboard -->
    @vite(['resources/js/dashboard.js'])
</x-app-layout>
