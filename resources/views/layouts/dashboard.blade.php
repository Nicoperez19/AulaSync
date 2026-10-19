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

    <!-- Modal flotante de reloj digital y módulo actual con opción de minimizar -->
    <div id="modal-reloj"
        class="fixed bottom-4 right-4 sm:bottom-6 sm:right-8 z-50 transition-all duration-300 ease-out group">
        <!-- Estado Expandido -->
        <div id="reloj-expandido"
            class="bg-gradient-to-br from-[#d2091e]/95 to-[#b10718]/95 backdrop-blur-md shadow-2xl shadow-red-950/20 rounded-2xl border border-white/20 px-4 py-3 sm:px-5 sm:py-3.5 flex items-center gap-3 sm:gap-4 transition-all duration-300">
            <div class="p-2 sm:p-2.5 bg-white/15 text-white rounded-xl transition-all duration-300 shrink-0">
                <i class="fa-solid fa-clock text-base sm:text-xl"></i>
            </div>
            <div class="flex flex-col select-none">
                <span class="font-mono text-xl sm:text-2xl font-black text-white leading-none tracking-tight my-0.5" id="modal-hora-actual">--:--:--</span>
                <span class="text-xs sm:text-sm font-bold text-red-100" id="modal-modulo-actual">Módulo actual: -</span>
            </div>
            <button onclick="toggleModalReloj(true)" title="Minimizar reloj" class="ml-1 text-white/70 hover:text-white hover:bg-white/20 rounded-lg p-1.5 transition-colors duration-150 shrink-0">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/></svg>
            </button>
        </div>

        <!-- Estado Minimizado -->
        <div id="reloj-minimizado" 
            onclick="toggleModalReloj(false)"
            title="Mostrar reloj y módulo actual"
            class="hidden cursor-pointer bg-gradient-to-br from-[#d2091e]/95 to-[#b10718]/95 backdrop-blur-md shadow-xl hover:shadow-2xl rounded-full border border-white/20 px-3.5 py-2 flex items-center gap-2 text-white hover:scale-105 transition-all duration-200">
            <i class="fa-solid fa-clock text-xs sm:text-sm"></i>
            <span class="font-mono text-xs sm:text-sm font-bold" id="modal-hora-minimizada">--:--:--</span>
            <svg class="w-3.5 h-3.5 text-white/70" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 15l7-7 7 7"/></svg>
        </div>
    </div>

    <div class="w-full px-4 sm:px-6 lg:px-8 py-6">
        <!-- Sección: Porcentaje de Ocupación Semanal -->
        <div class="mb-8">
            <h3 class="text-lg font-bold text-gray-700 mb-4 flex items-center">
                <i class="fas fa-percent mr-2 text-blue-600"></i>
                Porcentaje de Ocupación Semanal
            </h3>
            
            <div class="bg-white rounded-2xl shadow-md border border-gray-100 p-4 sm:p-6">
                <!-- Nav Pills + Leyenda -->
                <div class="flex flex-col xl:flex-row justify-between items-start xl:items-center gap-4 mb-6">
                    <!-- Nav Pills -->
                    <div class="flex flex-wrap sm:inline-flex items-center gap-1 bg-gray-100 p-1 rounded-xl border border-gray-200 w-full xl:w-auto overflow-x-auto">
                        <button onclick="cambiarTabOcupacion('todos')" id="tab-ocupacion-todos"
                            class="tab-ocupacion-btn inline-flex items-center justify-center gap-1.5 px-3 sm:px-4 py-1.5 text-xs sm:text-sm font-semibold rounded-lg transition-all duration-200 bg-[#D2091E] text-white shadow-sm whitespace-nowrap flex-1 sm:flex-initial">
                            <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>
                            <span>Todos</span>
                        </button>
                        <button onclick="cambiarTabOcupacion('laboratorios')" id="tab-ocupacion-laboratorios"
                            class="tab-ocupacion-btn inline-flex items-center justify-center gap-1.5 px-3 sm:px-4 py-1.5 text-xs sm:text-sm font-semibold rounded-lg transition-all duration-200 text-gray-600 hover:bg-white hover:text-[#D2091E] hover:shadow-sm whitespace-nowrap flex-1 sm:flex-initial">
                            <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/></svg>
                            <span>Laboratorios</span>
                        </button>
                        <button onclick="cambiarTabOcupacion('salas_clases')" id="tab-ocupacion-salas_clases"
                            class="tab-ocupacion-btn inline-flex items-center justify-center gap-1.5 px-3 sm:px-4 py-1.5 text-xs sm:text-sm font-semibold rounded-lg transition-all duration-200 text-gray-600 hover:bg-white hover:text-[#D2091E] hover:shadow-sm whitespace-nowrap flex-1 sm:flex-initial">
                            <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                            <span>Salas de Clases</span>
                        </button>
                        <button onclick="cambiarTabOcupacion('salas_estudio')" id="tab-ocupacion-salas_estudio"
                            class="tab-ocupacion-btn inline-flex items-center justify-center gap-1.5 px-3 sm:px-4 py-1.5 text-xs sm:text-sm font-semibold rounded-lg transition-all duration-200 text-gray-600 hover:bg-white hover:text-[#D2091E] hover:shadow-sm whitespace-nowrap flex-1 sm:flex-initial">
                            <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                            <span>Salas de Estudio</span>
                        </button>
                    </div>
                    
                    <!-- Acciones: Leyenda + Exportar & Histórico -->
                    <div class="flex flex-wrap items-center gap-3">
                        <!-- Leyenda (Niveles de Ocupación) -->
                        <div class="flex flex-wrap items-center gap-2 text-xs md:text-sm bg-slate-50 border border-slate-200/80 rounded-xl px-3 sm:px-4 py-2 shadow-xs shrink-0">
                            <span class="font-extrabold text-slate-500 mr-1">Ocupación:</span>
                            <span class="flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-emerald-100 border border-emerald-200 text-emerald-800 font-bold whitespace-nowrap">
                                <span class="inline-block w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
                                <span>0% - 35%</span>
                            </span>
                            <span class="flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-amber-100 border border-amber-200 text-amber-900 font-bold whitespace-nowrap">
                                <span class="inline-block w-2.5 h-2.5 rounded-full bg-amber-500"></span>
                                <span>35% - 75%</span>
                            </span>
                            <span class="flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-red-100 border border-red-200 text-red-800 font-bold whitespace-nowrap">
                                <span class="inline-block w-2.5 h-2.5 rounded-full bg-red-500"></span>
                                <span>75% - 100%</span>
                            </span>
                        </div>

                        <!-- Botones de Exportar e Histórico -->
                        <div class="flex items-center gap-2 shrink-0">
                            <a id="btn-export-ocupacion-excel" href="{{ route('dashboard.ocupacion.export', ['format' => 'excel', 'tipo' => 'todos']) }}"
                               title="Exportar semana actual en Excel"
                               class="inline-flex items-center justify-center gap-2 px-3.5 py-2 text-xs sm:text-sm font-semibold text-white bg-emerald-600 hover:bg-emerald-700 rounded-lg shadow-sm hover:shadow transition-all duration-200">
                                <i class="fas fa-file-excel"></i>
                                <span>Excel</span>
                            </a>
                            <a id="btn-export-ocupacion-pdf" href="{{ route('dashboard.ocupacion.export', ['format' => 'pdf', 'tipo' => 'todos']) }}" target="_blank"
                               title="Exportar semana actual en PDF"
                               class="inline-flex items-center justify-center gap-2 px-3.5 py-2 text-xs sm:text-sm font-semibold text-white bg-red-600 hover:bg-red-700 rounded-lg shadow-sm hover:shadow transition-all duration-200">
                                <i class="fas fa-file-pdf"></i>
                                <span>PDF</span>
                            </a>
                            <a href="{{ route('reportes.ocupacion-semanal.index') }}"
                               title="Ver histórico de semanas almacenadas"
                               class="inline-flex items-center justify-center gap-2 px-3.5 py-2 text-xs sm:text-sm font-semibold text-white bg-slate-800 hover:bg-slate-900 rounded-lg shadow-sm hover:shadow transition-all duration-200">
                                <i class="fas fa-history"></i>
                                <span>Histórico</span>
                            </a>
                        </div>
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
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 sm:gap-8 mb-8">
            <!-- Widget Izquierda: Estado de Clases (Control de Asistencia) -->
            <div class="lg:col-span-7 flex flex-col">
                <h3 class="text-lg font-bold text-gray-700 mb-4 flex items-center">
                    <i class="fas fa-chart-pie mr-2 text-blue-600"></i>
                    Estado de Clases (Control de Asistencia)
                </h3>
                <div class="bg-white rounded-2xl shadow-md border border-gray-100 p-4 sm:p-6 flex-1">
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
                <div class="bg-white rounded-2xl shadow-md border border-gray-100 p-4 sm:p-6 flex-1 flex flex-col justify-between gap-4">
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

                    <!-- Histórico de Ocupación Semanal -->
                    <a href="{{ route('reportes.ocupacion-semanal.index') }}"
                       class="flex items-center justify-between p-4 bg-slate-50 hover:bg-slate-100/80 rounded-xl border border-slate-200/80 shadow-2xs transition duration-200 group">
                        <div class="flex items-center gap-3.5">
                            <div class="w-10 h-10 bg-red-100 rounded-lg flex items-center justify-center text-[#D2091E] group-hover:bg-[#D2091E] group-hover:text-white transition duration-200 shrink-0">
                                <i class="fas fa-chart-line text-lg"></i>
                            </div>
                            <div>
                                <h4 class="font-bold text-gray-800 text-sm">Histórico de Ocupación</h4>
                                <p class="text-xs text-gray-500">Semanas almacenadas y descargas en Excel y PDF</p>
                            </div>
                        </div>
                        <div class="text-gray-400 group-hover:text-[#D2091E] transition duration-200 ml-2">
                            <i class="fas fa-chevron-right text-sm"></i>
                        </div>
                    </a>

                    <!-- Auditoría de Clases y Justificaciones -->
                    <a href="{{ route('reportes.auditoria-clases.index') }}"
                       class="flex items-center justify-between p-4 bg-slate-50 hover:bg-slate-100/80 rounded-xl border border-slate-200/80 shadow-2xs transition duration-200 group">
                        <div class="flex items-center gap-3.5">
                            <div class="w-10 h-10 bg-slate-200 rounded-lg flex items-center justify-center text-slate-700 group-hover:bg-slate-800 group-hover:text-white transition duration-200 shrink-0">
                                <i class="fas fa-clipboard-user text-lg"></i>
                            </div>
                            <div>
                                <h4 class="font-bold text-gray-800 text-sm">Auditoría de Clases</h4>
                                <p class="text-xs text-gray-500">Registro inmutable de justificaciones y regularizaciones</p>
                            </div>
                        </div>
                        <div class="text-gray-400 group-hover:text-slate-800 transition duration-200 ml-2">
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
            <div class="bg-[#F8FAFC] rounded-2xl shadow-sm border border-slate-200/80 p-4 sm:p-6">
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

    <script>
        (function () {
            let currentModalStatusTab = 'todas';

            window.abrirDetalleStatusClases = function (tab = 'todas') {
                const modal = document.getElementById('modal-status-clases-detalle');
                if (!modal) return;

                modal.classList.remove('hidden');
                modal.classList.add('flex');
                document.body.style.overflow = 'hidden';

                const searchInput = document.getElementById('modal-status-search');
                if (searchInput) {
                    searchInput.value = '';
                }

                window.cambiarTabDetalleStatusClases(tab);
            };

            window.cerrarDetalleStatusClases = function () {
                const modal = document.getElementById('modal-status-clases-detalle');
                if (!modal) return;

                modal.classList.add('hidden');
                modal.classList.remove('flex');
                document.body.style.overflow = '';
            };

            window.cambiarTabDetalleStatusClases = function (tab = 'todas') {
                currentModalStatusTab = tab;

                const modal = document.getElementById('modal-status-clases-detalle');
                const titulo = document.getElementById('modal-status-titulo');
                const linkControl = document.getElementById('modal-status-link-control');

                const titulosPorTab = {
                    todas: 'Todas las Clases del Período',
                    no_registradas: 'Clases No Registradas / No Realizadas',
                    sin_registro: 'Clases Sin Marca de Asistencia / Ausentes',
                    pendiente_recuperacion: 'Clases Pendientes de Recuperación',
                    impartidas: 'Clases Impartidas / Efectivas',
                    realizadas: 'Clases Realizadas Normales',
                    recuperadas: 'Clases Recuperadas',
                    justificadas: 'Clases Justificadas',
                    futuras: 'Próximas Clases Programadas / En Curso'
                };

                if (titulo) {
                    titulo.textContent = titulosPorTab[tab] || 'Detalle de Clases del Período';
                }

                if (modal && linkControl) {
                    const baseUrl = modal.dataset.baseControlUrl || '/clases-no-realizadas';
                    const fechaInicio = modal.dataset.fechaInicio || '';
                    const fechaFin = modal.dataset.fechaFin || '';
                    const estadoParamMap = {
                        todas: '',
                        no_registradas: 'no_realizada',
                        sin_registro: 'no_realizada',
                        pendiente_recuperacion: 'pendiente',
                        impartidas: 'impartidas',
                        realizadas: 'realizada',
                        recuperadas: 'recuperada',
                        justificadas: 'justificado',
                        futuras: ''
                    };
                    const estadoParam = estadoParamMap[tab] || '';
                    let url = `${baseUrl}?fecha_inicio=${encodeURIComponent(fechaInicio)}&fecha_fin=${encodeURIComponent(fechaFin)}`;
                    if (estadoParam) {
                        url += `&estado=${encodeURIComponent(estadoParam)}`;
                    }
                    linkControl.href = url;
                }

                const tabButtons = document.querySelectorAll('#modal-status-tabs .status-modal-tab');
                const tabToHighlight = (tab === 'sin_registro' || tab === 'pendiente_recuperacion') ? 'no_registradas' : tab;

                tabButtons.forEach(btn => {
                    const btnTab = btn.dataset.tab;
                    if (btnTab === tabToHighlight) {
                        btn.className = 'status-modal-tab px-3 py-1.5 rounded-xl text-xs font-extrabold border transition cursor-pointer flex items-center gap-1.5 bg-slate-900 text-white border-slate-900 shadow-xs';
                    } else {
                        btn.className = 'status-modal-tab px-3 py-1.5 rounded-xl text-xs font-extrabold border transition cursor-pointer flex items-center gap-1.5 bg-white text-slate-700 border-slate-200 hover:bg-slate-100';
                    }
                });

                window.filtrarItemsModalStatusClases();
            };

            window.filtrarItemsModalStatusClases = function () {
                const searchInput = document.getElementById('modal-status-search');
                const query = (searchInput ? searchInput.value : '').trim().toLowerCase();
                const items = document.querySelectorAll('#modal-status-lista .status-modal-item');
                const emptyMsg = document.getElementById('modal-status-empty-filter');
                const contadorPie = document.getElementById('modal-status-contador-pie');

                let visibles = 0;

                items.forEach(item => {
                    const cat = item.dataset.categoria || '';
                    const sub = item.dataset.subtipo || '';
                    const searchStr = item.dataset.search || '';

                    let matchTab = false;
                    switch (currentModalStatusTab) {
                        case 'todas':
                            matchTab = cat !== 'futura';
                            break;
                        case 'no_registradas':
                            matchTab = cat === 'no_registrada';
                            break;
                        case 'sin_registro':
                            matchTab = cat === 'no_registrada' && sub === 'sin_registro';
                            break;
                        case 'pendiente_recuperacion':
                            matchTab = cat === 'no_registrada' && sub === 'pendiente_recuperacion';
                            break;
                        case 'impartidas':
                            matchTab = ['realizada', 'recuperada', 'justificada'].includes(cat);
                            break;
                        case 'realizadas':
                            matchTab = cat === 'realizada';
                            break;
                        case 'recuperadas':
                            matchTab = cat === 'recuperada';
                            break;
                        case 'justificadas':
                            matchTab = cat === 'justificada';
                            break;
                        case 'futuras':
                            matchTab = cat === 'futura';
                            break;
                        default:
                            matchTab = true;
                    }

                    const matchSearch = !query || searchStr.includes(query);

                    if (matchTab && matchSearch) {
                        item.classList.remove('hidden');
                        visibles++;
                    } else {
                        item.classList.add('hidden');
                    }
                });

                if (emptyMsg) {
                    if (visibles === 0 && items.length > 0) {
                        emptyMsg.classList.remove('hidden');
                    } else {
                        emptyMsg.classList.add('hidden');
                    }
                }

                if (contadorPie) {
                    contadorPie.textContent = `Mostrando ${visibles} ${visibles === 1 ? 'clase' : 'clases'}`;
                }
            };

            document.addEventListener('keydown', (e) => {
                if (e.key === 'Escape') {
                    window.cerrarDetalleStatusClases();
                }
            });
        })();
    </script>
</x-app-layout>
