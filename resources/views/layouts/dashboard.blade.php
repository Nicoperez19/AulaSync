<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <div class="flex items-center gap-3">
                <div class="p-2 rounded-xl bg-light-cloud-blue">
                    <i class="text-2xl text-white fa-solid fa-gauge-high"></i>
                </div>
                <div>
                    <h2 class="text-2xl font-bold leading-tight">Dashboard</h2>
                    <p class="text-sm text-gray-500">Resumen y reportes integrados del sistema</p>
                </div>
            </div>
        </div>
    </x-slot>

    <!-- Modal fijo de reloj digital y módulo actual -->
    <div id="modal-reloj"
        class="fixed bottom-6 right-8 z-50 bg-gradient-to-br from-[#d2091e]/95 to-[#b10718]/95 backdrop-blur-md shadow-2xl shadow-red-950/20 rounded-2xl border border-white/20 px-6 py-4 flex items-center gap-4 min-w-[220px] transition-all duration-300 ease-out cursor-default group">
        <div class="p-3 bg-white/15 text-white rounded-xl transition-all duration-300 shrink-0">
            <i class="fa-solid fa-clock text-xl"></i>
        </div>
        <div class="flex flex-col">
            <span class="font-mono text-2xl font-black text-white leading-none tracking-tight my-1" id="modal-hora-actual">--:--:--</span>
            <span class="text-sm font-bold text-red-100" id="modal-modulo-actual">Módulo actual: -</span>
        </div>
    </div>

    <div class="w-full px-8 py-6">
        <!-- Sección: Porcentaje de Ocupación Semanal -->
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
                        <button onclick="cambiarTabOcupacion('todos')" id="tab-ocupacion-todos" class="px-4 py-2 text-sm font-bold rounded-lg transition duration-150 tab-ocupacion-btn bg-blue-600 text-white shadow-sm">
                            Todos
                        </button>
                        <button onclick="cambiarTabOcupacion('laboratorios')" id="tab-ocupacion-laboratorios" class="px-4 py-2 text-sm font-bold rounded-lg transition duration-150 tab-ocupacion-btn bg-gray-50 text-gray-600 hover:bg-gray-100">
                            Laboratorios
                        </button>
                        <button onclick="cambiarTabOcupacion('salas_clases')" id="tab-ocupacion-salas_clases" class="px-4 py-2 text-sm font-bold rounded-lg transition duration-150 tab-ocupacion-btn bg-gray-50 text-gray-600 hover:bg-gray-100">
                            Salas de Clases
                        </button>
                        <button onclick="cambiarTabOcupacion('salas_estudio')" id="tab-ocupacion-salas_estudio" class="px-4 py-2 text-sm font-bold rounded-lg transition duration-150 tab-ocupacion-btn bg-gray-50 text-gray-600 hover:bg-gray-100">
                            Salas de Estudio
                        </button>
                    </div>
                    
                    <!-- Leyenda (Niveles de Ocupación) -->
                    <div class="flex flex-wrap gap-2.5 items-center text-xs md:text-sm bg-slate-50 border border-slate-200/80 rounded-xl px-4 py-2 shadow-xs shrink-0">
                        <span class="font-extrabold text-slate-500 mr-1">Ocupación:</span>
                        <span class="flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-emerald-100 border border-emerald-200 text-emerald-800 font-bold">
                            <span class="inline-block w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
                            <span>0% - 35%</span>
                        </span>
                        <span class="flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-amber-100 border border-amber-200 text-amber-900 font-bold">
                            <span class="inline-block w-2.5 h-2.5 rounded-full bg-amber-500"></span>
                            <span>35% - 75%</span>
                        </span>
                        <span class="flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-red-100 border border-red-200 text-red-800 font-bold">
                            <span class="inline-block w-2.5 h-2.5 rounded-full bg-red-500"></span>
                            <span>75% - 100%</span>
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

        <!-- Sección de 2 Columnas: Estado de Clases a la Izquierda | Reportes a la Derecha -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 mb-8">
            <!-- Widget Izquierda: Estado de Clases (Control de Asistencia) -->
            <div class="lg:col-span-7 flex flex-col">
                <h3 class="text-lg font-bold text-gray-700 mb-4 flex items-center">
                    <i class="fas fa-chart-pie mr-2 text-blue-600"></i>
                    Estado de Clases (Control de Asistencia)
                </h3>
                <div class="bg-white rounded-2xl shadow-md border border-gray-100 p-6 flex-1">
                    <div id="status-clases-container">
                        <div class="flex flex-col items-center justify-center py-16 text-slate-400">
                            <div class="inline-block w-8 h-8 border-4 border-blue-600 border-t-transparent rounded-full animate-spin mb-4"></div>
                            <p class="text-sm">Cargando estado de clases...</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Widget Derecha: Reportes de Uso de Espacios -->
            @can('reportes')
            <div class="lg:col-span-5 flex flex-col">
                <h3 class="text-lg font-bold text-gray-700 mb-4 flex items-center">
                    <i class="fas fa-chart-bar mr-2 text-blue-600"></i>
                    Reportes de Uso de Espacios
                </h3>
                <div class="bg-white rounded-2xl shadow-md border border-gray-100 p-6 flex-1 flex flex-col justify-between gap-4">
                    <!-- Accesos registrados (QR) -->
                    <a href="{{ route('reportes.accesos') }}"
                       class="flex items-center justify-between p-4 bg-slate-50 hover:bg-slate-100/80 rounded-xl border border-slate-200/80 shadow-2xs transition duration-200 group">
                        <div class="flex items-center gap-3.5">
                            <div class="w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center text-purple-600 group-hover:bg-purple-600 group-hover:text-white transition duration-200 shrink-0">
                                <i class="fas fa-qrcode text-lg"></i>
                            </div>
                            <div>
                                <h4 class="font-bold text-gray-800 text-sm">Accesos registrados (QR)</h4>
                                <p class="text-xs text-gray-500">Registros de acceso escaneados por código QR</p>
                            </div>
                        </div>
                        <div class="text-gray-400 group-hover:text-purple-600 transition duration-200 ml-2">
                            <i class="fas fa-chevron-right text-sm"></i>
                        </div>
                    </a>    

                    <!-- Control de Clases -->
                    <a href="{{ route('clases-no-realizadas.index') }}"
                       class="flex items-center justify-between p-4 bg-slate-50 hover:bg-slate-100/80 rounded-xl border border-slate-200/80 shadow-2xs transition duration-200 group">
                        <div class="flex items-center gap-3.5">
                            <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center text-blue-600 group-hover:bg-blue-600 group-hover:text-white transition duration-200 shrink-0">
                                <i class="fas fa-clipboard-check text-lg"></i>
                            </div>
                            <div>
                                <h4 class="font-bold text-gray-800 text-sm">Control de Clases</h4>
                                <p class="text-xs text-gray-500">Asistencia, ausencias y recuperación de clases</p>
                            </div>
                        </div>
                        <div class="text-gray-400 group-hover:text-blue-600 transition duration-200 ml-2">
                            <i class="fas fa-chevron-right text-sm"></i>
                        </div>
                    </a>

                    <!-- Salas de Estudio -->
                    <a href="{{ route('reportes.salas-estudio') }}"
                       class="flex items-center justify-between p-4 bg-slate-50 hover:bg-slate-100/80 rounded-xl border border-slate-200/80 shadow-2xs transition duration-200 group">
                        <div class="flex items-center gap-3.5">
                            <div class="w-10 h-10 bg-emerald-100 rounded-lg flex items-center justify-center text-emerald-600 group-hover:bg-emerald-600 group-hover:text-white transition duration-200 shrink-0">
                                <i class="fas fa-book text-lg"></i>
                            </div>
                            <div>
                                <h4 class="font-bold text-gray-800 text-sm">Salas de Estudio</h4>
                                <p class="text-xs text-gray-500">Uso y reservas de las salas de estudio</p>
                            </div>
                        </div>
                        <div class="text-gray-400 group-hover:text-emerald-600 transition duration-200 ml-2">
                            <i class="fas fa-chevron-right text-sm"></i>
                        </div>
                    </a>

                    <!-- Uso del Auditorio -->
                    <a href="{{ route('reportes.uso-auditorio') }}"
                       class="flex items-center justify-between p-4 bg-slate-50 hover:bg-slate-100/80 rounded-xl border border-slate-200/80 shadow-2xs transition duration-200 group">
                        <div class="flex items-center gap-3.5">
                            <div class="w-10 h-10 bg-amber-100 rounded-lg flex items-center justify-center text-amber-600 group-hover:bg-amber-600 group-hover:text-white transition duration-200 shrink-0">
                                <i class="fas fa-landmark text-lg"></i>
                            </div>
                            <div>
                                <h4 class="font-bold text-gray-800 text-sm">Uso del Auditorio</h4>
                                <p class="text-xs text-gray-500">Historial y estadísticas de uso del auditorio</p>
                            </div>
                        </div>
                        <div class="text-gray-400 group-hover:text-amber-600 transition duration-200 ml-2">
                            <i class="fas fa-chevron-right text-sm"></i>
                        </div>
                    </a>
                </div>
            </div>
            @endcan
        </div>

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

    <!-- Script de Chart.js -->
    
    <!-- Configuración para el script JS externo -->
    <div id="dashboard-config"
         data-horarios-actual-route="{{ route('dashboard.horarios-actual') }}"
         data-ocupacion-datos-route="{{ route('dashboard.ocupacion-datos') }}"
         data-status-clases-route="{{ route('dashboard.status-clases') }}"
         data-horarios-modulos="{{ json_encode(\App\Helpers\ModulosHelper::getHorariosModulos()) }}"
         class="hidden">
    </div>

    <!-- Cargar script externo del dashboard -->
    @vite(['resources/js/dashboard.js'])
</x-app-layout>
