<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-2 pr-6 md:flex-row md:items-center md:justify-between">
            <div class="flex items-center gap-3">
                <div class="p-2 rounded-xl bg-light-cloud-blue">
                    <i class="text-2xl text-white fas fa-book-reader"></i>
                </div>

                <div>
                    <h2 class="text-2xl font-bold leading-tight">Gestión de Salas de Estudio</h2>
                    <p class="text-sm text-gray-500">Administrar reservas activas y finalizadas en salas de estudio</p>
                </div>
            </div>

            <div class="flex items-center gap-2.5">
                <button type="button" onclick="abrirModalManualReserva()" 
                   class="inline-flex items-center gap-2 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-medium rounded-lg shadow-sm transition-all duration-150 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500 cursor-pointer">
                    <x-icons.add class="w-4 h-4 shrink-0" aria-hidden="true" />
                    <span>Nueva Reserva</span>
                </button>
                <a href="{{ route('quick-actions.index') }}" 
                   class="inline-flex items-center gap-2 px-4 py-2 bg-white hover:bg-gray-50 border border-gray-300 text-gray-700 text-sm font-medium rounded-lg shadow-sm transition-all duration-150 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-300">
                    <svg class="w-4 h-4 shrink-0 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    <span>Volver</span>
                </a>
            </div>
        </div>
    </x-slot>



    <div class="space-y-6">

    <!-- Input Oculto y Continuo de Lectura QR (Plano Digital Style) -->
    <input type="text" id="qr-input-global" class="sr-only absolute top-0 left-0 opacity-0 w-1 h-1 pointer-events-none" autofocus autocomplete="off">

    <!-- Filtros -->
    <!-- Sección FILTROS -->
    <div class="bg-white p-5 rounded-xl border border-gray-200 shadow-sm mb-6 dark:bg-gray-800 dark:border-gray-700">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-sm font-bold text-gray-700 uppercase tracking-wider flex items-center gap-2 dark:text-gray-200">
                <i class="fas fa-filter text-[#950101]"></i> FILTROS
            </h3>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
            <div>
                <label class="block text-xs font-semibold text-gray-700 uppercase mb-1 dark:text-gray-300">Solicitante</label>
                <div class="relative">
                    <input type="text" id="filtro-solicitante" oninput="aplicarFiltrosLocales()" placeholder="Nombre o RUN..." class="w-full pl-9 pr-3 py-2 text-sm border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-[#950101]/20 focus:border-[#950101] dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                    <i class="fas fa-search absolute left-3 top-2.5 text-gray-400 text-xs"></i>
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-gray-700 uppercase mb-1 dark:text-gray-300">Estado</label>
                <select id="filtro-estado" onchange="cargarReservas()" class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-[#950101]/20 focus:border-[#950101] bg-white dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                    <option value="todos">Todos los estados</option>
                    <option value="activa">Activas</option>
                    <option value="finalizada">Finalizadas</option>
                    <option value="cancelada">Canceladas</option>
                </select>
            </div>

            <div>
                <label class="block text-xs font-semibold text-gray-700 uppercase mb-1 dark:text-gray-300">Espacio</label>
                <select id="filtro-espacio" onchange="cargarReservas()" class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-[#950101]/20 focus:border-[#950101] bg-white dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                    <option value="todos">Todos los espacios</option>
                </select>
            </div>
            
            <div>
                <label class="block text-xs font-semibold text-gray-700 uppercase mb-1 dark:text-gray-300">Módulo</label>
                <select id="filtro-modulo" onchange="cargarReservas()" class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-[#950101]/20 focus:border-[#950101] bg-white dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                    <option value="todos">Todos los módulos</option>
                </select>
            </div>

            <div>
                <label class="block text-xs font-semibold text-gray-700 uppercase mb-1 dark:text-gray-300">Fecha</label>
                <input type="date" id="filtro-fecha" onchange="cargarReservas()" class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-[#950101]/20 focus:border-[#950101] dark:bg-gray-700 dark:border-gray-600 dark:text-white">
            </div>
            
            <div>
                <label class="block text-xs font-semibold text-gray-700 uppercase mb-1 dark:text-gray-300">Ordenar por</label>
                <select id="filtro-orden" onchange="cargarReservas()" class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-[#950101]/20 focus:border-[#950101] bg-white dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                    <option value="desc">Fecha (más reciente)</option>
                    <option value="asc">Fecha (más antigua)</option>
                </select>
            </div>
        </div>

        <!-- Botones de Acción separados por línea -->
        <div class="flex items-center justify-end gap-2 mt-4 pt-4 border-t border-gray-100 dark:border-gray-700">
            <button type="button" 
                    onclick="limpiarFiltrosSalasEstudio()" 
                    class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 border border-gray-300 rounded-lg transition-colors inline-flex items-center gap-2 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200 dark:hover:bg-gray-600">
                <i class="fas fa-undo text-xs"></i> Limpiar
            </button>
            <button onclick="cargarReservas()" 
                    class="px-4 py-2 text-sm font-semibold text-white bg-[#191919] hover:bg-black active:bg-gray-900 rounded-lg shadow-sm transition-colors inline-flex items-center gap-2">
                <i class="fas fa-filter text-xs"></i> Aplicar filtros
            </button>
        </div>
    </div>
    </div>

    <!-- Tabla de reservas -->
    <div class="bg-white shadow-sm rounded-xl border border-gray-200 overflow-hidden dark:bg-gray-800 dark:border-gray-700">
        <div class="hidden lg:block overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 text-sm">
                <thead class="bg-[#950101] text-white border-b border-[#7B0101]">
                            <tr>
                                <th class="px-2 py-3 text-center text-xs font-semibold text-white uppercase w-10">
                                    <input type="checkbox" id="select-all-reservas" class="rounded">
                                </th>
                                <th class="px-2 py-3 text-center text-xs font-semibold text-white uppercase w-28 cursor-pointer hover:text-red-100">
                                    ESTADO <i class="fa-solid fa-sort ml-1 text-xs text-white/50"></i>
                                </th>
                                <th class="px-2 py-3 text-center text-xs font-semibold text-white uppercase w-28 cursor-pointer hover:text-red-100">
                                    ESPACIO <i class="fa-solid fa-sort ml-1 text-xs text-white/50"></i>
                                </th>
                                <th class="px-2 py-3 text-center text-xs font-semibold text-white uppercase cursor-pointer hover:text-red-100">
                                    RESPONSABLE <i class="fa-solid fa-sort ml-1 text-xs text-white/50"></i>
                                </th>
                                <th class="px-2 py-3 text-center text-xs font-semibold text-white uppercase w-32 cursor-pointer hover:text-red-100">
                                    FECHA <i class="fa-solid fa-sort ml-1 text-xs text-white/50"></i>
                                </th>
                                <th class="px-2 py-3 text-center text-xs font-semibold text-white uppercase w-44 cursor-pointer hover:text-red-100">
                                    PERMANENCIA (MAX 2H) <i class="fa-solid fa-sort ml-1 text-xs text-white/50"></i>
                                </th>
                                <th class="px-2 py-3 text-center text-xs font-semibold text-white uppercase w-32">
                                    ACCIONES
                                </th>
                            </tr>
                        </thead>
                        <tbody id="tabla-reservas-body" class="bg-white divide-y divide-gray-200">
                            <tr>
                                <td colspan="7" class="px-3 sm:px-6 py-12 text-center text-gray-500">
                                    <div class="flex flex-col items-center">
                                        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600 mb-4"></div>
                                        <p>Cargando reservas de salas de estudio...</p>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
            </table>
        </div>

        <!-- Versión Mobile (Cards) -->
            <div id="tabla-reservas-cards" class="lg:hidden space-y-4">
                <div class="flex flex-col items-center justify-center py-12 text-gray-500">
                    <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600 mb-4"></div>
                    <p>Cargando reservas...</p>
                </div>
            </div>

            <!-- Controles de Paginación (20 por página) -->
            <div id="paginacion-salas-estudio" class="flex flex-col sm:flex-row items-center justify-between gap-4 mt-6 pt-4 border-t border-gray-200">
                <div class="text-sm text-gray-600">
                    Mostrando <span id="paginacion-desde" class="font-semibold text-gray-900">0</span> a <span id="paginacion-hasta" class="font-semibold text-gray-900">0</span> de <span id="paginacion-total" class="font-semibold text-gray-900">0</span> reservas
                </div>
                <div class="flex items-center gap-1.5">
                    <button id="btn-pagina-anterior" onclick="cambiarPagina(paginaActual - 1)" class="px-3 py-1.5 border border-gray-300 rounded-lg text-xs sm:text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 disabled:opacity-40 disabled:cursor-not-allowed transition">
                        <i class="fa-solid fa-chevron-left mr-1"></i> Anterior
                    </button>
                    <div id="paginacion-numeros" class="flex items-center gap-1"></div>
                    <button id="btn-pagina-siguiente" onclick="cambiarPagina(paginaActual + 1)" class="px-3 py-1.5 border border-gray-300 rounded-lg text-xs sm:text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 disabled:opacity-40 disabled:cursor-not-allowed transition">
                        Siguiente <i class="fa-solid fa-chevron-right ml-1"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ========================================== -->
<!-- MODALES DEL SISTEMA (Estilo Plano Digital) -->
<!-- ========================================== -->

<!-- Modal 1: Procesando Lectura / Verificando -->
<div id="modal-procesando" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-60 hidden">
    <div class="bg-white rounded-2xl shadow-2xl p-6 text-center max-w-sm w-full animate-pulse">
        <div class="w-12 h-12 border-4 border-amber-500 border-t-transparent rounded-full animate-spin mx-auto mb-4"></div>
        <h3 class="text-lg font-bold text-gray-900">Verificando Lectura...</h3>
        <p class="text-xs text-gray-500 mt-1" id="msg-procesando">Procesando código QR escaneado...</p>
    </div>
</div>

<!-- Modal 2: Devolución Registrada Exitosamente -->
<div id="modal-devolucion-exitosa" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-60 hidden">
    <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full p-6 text-center relative">
        <div id="dev-icon-container" class="w-16 h-16 bg-green-100 text-green-600 rounded-full flex items-center justify-center mx-auto mb-4 text-3xl">
            <i id="dev-icon" class="fas fa-check-circle"></i>
        </div>
        <h3 id="dev-titulo" class="text-xl font-bold text-gray-900 mb-1">🔑 Devolución Registrada</h3>
        <p id="dev-subtitulo" class="text-sm text-gray-600 mb-4">La devolución de la sala se ha completado correctamente.</p>
        
        <div id="dev-alerta-sancion" class="hidden bg-red-50 border border-red-300 text-red-800 rounded-xl p-3 text-xs mb-3 text-left"></div>

        <div class="bg-green-50 border border-green-200 rounded-xl p-4 text-left space-y-2 mb-5 text-xs">
            <div class="flex justify-between"><span class="font-semibold text-green-900">Usuario:</span> <span id="dev-estudiante" class="font-bold"></span></div>
            <div class="flex justify-between"><span class="font-semibold text-green-900">Sala Liberada:</span> <span id="dev-sala" class="font-bold text-green-800"></span></div>
            <div class="flex justify-between"><span class="font-semibold text-green-900">Tiempo de Uso:</span> <span id="dev-duracion" class="font-bold text-green-900"></span></div>
            <div class="flex justify-between"><span class="font-semibold text-green-900">Hora Devolución:</span> <span id="dev-hora"></span></div>
        </div>

        <button onclick="cerrarModalDevolucion()" class="w-full py-2.5 bg-green-600 hover:bg-green-700 text-white font-bold rounded-xl shadow-md transition-colors">
            Aceptar
        </button>
    </div>
</div>

<!-- Modal 3: Escanear Sala de Estudio (Paso 2) -->
<div id="modal-seleccionar-sala" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-60 hidden">
    <div class="bg-white rounded-2xl shadow-2xl max-w-lg w-full p-6 relative">
        <button onclick="cerrarModalSeleccionSala()" class="absolute top-4 right-4 text-gray-400 hover:text-gray-600 text-xl font-bold">&times;</button>
        
        <div class="flex items-center space-x-3 mb-4 border-b pb-3">
            <div class="p-3 bg-amber-100 text-amber-700 rounded-xl">
                <i class="fas fa-qrcode text-2xl"></i>
            </div>
            <div>
                <h3 class="text-lg font-bold text-gray-900">Escanear Sala de Estudio</h3>
                <p class="text-xs text-gray-500">
                    Usuario: <strong id="sel-nombre-estudiante" class="text-amber-700 text-sm"></strong>
                </p>
            </div>
        </div>

        <div class="space-y-5 text-center">
            <div class="bg-amber-50 border-2 border-dashed border-amber-300 rounded-2xl p-6 relative overflow-hidden">
                <div class="relative flex items-center justify-center mx-auto mb-3">
                    <div class="w-20 h-20 bg-amber-500 text-white rounded-2xl flex items-center justify-center text-4xl shadow-lg animate-pulse">
                        <i class="fas fa-door-open"></i>
                    </div>
                </div>
                <h4 class="font-bold text-base text-amber-950 mb-1">Escanee el Código QR de la Sala</h4>
                <p class="text-xs text-amber-800">Acerque el código QR o código de barras del espacio al lector</p>

                <div class="mt-4 max-w-xs mx-auto relative">
                    <input type="text" id="input-qr-sala" autocomplete="off" 
                           class="sr-only absolute top-0 left-0 opacity-0 w-1 h-1 pointer-events-none"
                           autofocus
                           oninput="onInputSalaChange()"
                           onkeydown="if(event.key==='Enter' || event.keyCode===13){ event.preventDefault(); procesarEscaneoCodSala(); }">
                    
                    <div class="flex items-center justify-center space-x-2 text-xs font-bold text-amber-800 bg-amber-100/80 py-2.5 px-4 rounded-xl border border-amber-300 animate-pulse">
                        <i class="fas fa-barcode text-sm"></i>
                        <span>Esperando lectura del lector QR...</span>
                    </div>
                </div>

            </div>

            <div class="flex justify-end">
                <button onclick="cerrarModalSeleccionSala()" class="px-5 py-2 bg-gray-200 text-gray-700 rounded-xl text-xs font-semibold hover:bg-gray-300">
                    Cancelar Escaneo
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal 4: Reserva Confirmada Exitosamente -->
<div id="modal-reserva-exitosa" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-60 hidden">
    <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full p-6 text-center relative">
        <div class="w-16 h-16 bg-blue-100 text-blue-600 rounded-full flex items-center justify-center mx-auto mb-4 text-3xl">
            <i class="fas fa-calendar-check"></i>
        </div>
        <h3 class="text-xl font-bold text-gray-900 mb-1">🎉 Reserva Iniciada (2 Horas)</h3>
        <p class="text-sm text-gray-600 mb-4">La sala de estudio ha sido asignada correctamente.</p>
        
        <div class="bg-blue-50 border border-blue-200 rounded-xl p-4 text-left space-y-2 mb-5 text-xs">
            <div class="flex justify-between"><span class="font-semibold text-blue-900">Usuario:</span> <span id="res-estudiante" class="font-bold"></span></div>
            <div class="flex justify-between"><span class="font-semibold text-blue-900">Sala Asignada:</span> <span id="res-sala" class="font-bold text-blue-800"></span></div>
            <div class="flex justify-between"><span class="font-semibold text-blue-900">Tiempo Máximo:</span> <span class="font-bold text-amber-700">2 Horas</span></div>
        </div>

        <button onclick="cerrarModalReservaExitosa()" class="w-full py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-xl shadow-md transition-colors">
            Aceptar
        </button>
    </div>
</div>

<!-- Modal 5: Registro Solicitante Nuevos (Plano Digital Style) -->
<div id="modal-registro-solicitante" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-60 hidden">
    <div class="bg-white rounded-2xl shadow-2xl max-w-lg w-full overflow-hidden relative">
        <!-- Header Estilo Plano Digital -->
        <div class="bg-gradient-to-r from-blue-600 to-indigo-700 p-5 text-white flex items-center justify-between">
            <div class="flex items-center space-x-3">
                <div class="p-3 bg-white/20 rounded-full">
                    <i class="fas fa-user-plus text-2xl text-white"></i>
                </div>
                <div>
                    <h3 class="text-xl font-bold">Registro de Solicitante</h3>
                    <p class="text-xs text-blue-100 mt-0.5">Usuario No Registrado • Sistema de Aulas</p>
                </div>
            </div>
            <button onclick="cerrarModalRegistro()" class="text-white/80 hover:text-white text-2xl font-bold">&times;</button>
        </div>

        <div class="p-6">
            <p class="text-xs text-gray-600 mb-4 bg-blue-50 p-3 rounded-lg border border-blue-100">
                <i class="fas fa-info-circle text-blue-600 mr-1"></i> Complete los siguientes datos para registrar al usuario como solicitante y continuar con la reserva de la sala de estudio.
            </p>

            <form id="form-registro-solicitante" onsubmit="guardarYContinuar(event)" class="space-y-4 text-xs">
                <div>
                    <label class="block font-semibold text-gray-700 mb-1">RUN / Identificación *</label>
                    <input type="text" id="reg-run" readonly class="w-full text-sm bg-gray-100 border-gray-300 rounded-lg font-bold text-gray-800 px-3 py-2">
                </div>

                <div>
                    <label class="block font-semibold text-gray-700 mb-1">Tipo de Solicitante *</label>
                    <select id="reg-tipo" required class="w-full text-sm border-gray-300 rounded-lg shadow-sm focus:ring-[#950101] focus:border-[#950101] focus:border-blue-500 font-medium px-3 py-2">
                        <option value="">Seleccione el tipo</option>
                        <option value="estudiante" selected>Estudiante</option>
                        <option value="personal">Personal Administrativo</option>
                        <option value="profesor">Profesor / Docente</option>
                        <option value="visitante">Visitante</option>
                        <option value="otro">Otro</option>
                    </select>
                </div>

                <div>
                    <label class="block font-semibold text-gray-700 mb-1">Nombre Completo *</label>
                    <input type="text" id="reg-nombre" required placeholder="Ej. Juan Pérez Garay" class="w-full text-sm border-gray-300 rounded-lg shadow-sm focus:ring-[#950101] focus:border-[#950101] focus:border-blue-500 px-3 py-2">
                </div>

                <div>
                    <label class="block font-semibold text-gray-700 mb-1">Correo Electrónico *</label>
                    <input type="email" id="reg-correo" required placeholder="usuario@ejemplo.cl" class="w-full text-sm border-gray-300 rounded-lg shadow-sm focus:ring-[#950101] focus:border-[#950101] focus:border-blue-500 px-3 py-2">
                </div>

                <div>
                    <label class="block font-semibold text-gray-700 mb-1">Teléfono *</label>
                    <div class="flex">
                        <span class="inline-flex items-center px-3 bg-gray-100 border border-r-0 border-gray-300 rounded-l-md text-gray-600 font-medium">+56</span>
                        <input type="tel" id="reg-telefono" placeholder="912345678" maxlength="9" pattern="[0-9]{9}" class="flex-1 text-sm border-gray-300 rounded-r-md shadow-sm focus:ring-[#950101] focus:border-[#950101] focus:border-blue-500 px-3 py-2">
                    </div>
                </div>

                <div class="flex justify-end space-x-3 pt-3">
                    <button type="button" onclick="cerrarModalRegistro()" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg font-semibold hover:bg-gray-300">
                        Cancelar
                    </button>
                    <button type="submit" class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-lg shadow-md transition-colors">
                        Registrar y Continuar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal 6: Entrada Manual Reserva -->
<div id="modal-entrada-manual" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-60 hidden">
    <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full p-6 relative">
        <button onclick="cerrarModalManual()" class="absolute top-4 right-4 text-gray-400 hover:text-gray-600 text-xl font-bold">&times;</button>
        
        <div class="flex items-center space-x-3 mb-4">
            <div class="p-3 bg-amber-100 text-amber-700 rounded-xl">
                <i class="fas fa-keyboard text-2xl"></i>
            </div>
            <div>
                <h3 class="text-lg font-bold text-gray-900">Reserva Manual / Selección Directa</h3>
                <p class="text-xs text-gray-500">Ingrese RUN y Seleccione Sala</p>
            </div>
        </div>

        <div class="space-y-3">
            <div>
                <label class="block text-xs font-semibold text-gray-700 mb-1">RUN Usuario *</label>
                <input type="text" id="manual-run" placeholder="12345678-K" class="w-full text-sm border-gray-300 rounded-lg shadow-sm">
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-700 mb-1">Sala de Estudio *</label>
                <select id="manual-select-espacio" class="w-full text-sm border-gray-300 rounded-lg shadow-sm">
                    <option value="">Cargando salas...</option>
                </select>
            </div>
            <div class="flex justify-end space-x-3 pt-3">
                <button onclick="cerrarModalManual()" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-xl text-sm font-semibold">Cancelar</button>
                <button onclick="procesarReservaManualDirecta()" class="px-4 py-2 bg-amber-600 text-white rounded-xl text-sm font-semibold hover:bg-amber-700">Procesar</button>
            </div>
        </div>
    </div>
</div>

<script>
    let reservasGlobales = [];
    let salasEstudioGlobales = [];

    // MOTOR DE ESCANEO PLANO DIGITAL
    let bufferQR = '';
    let lastKeyTime = Date.now();
    let lastBufferLength = 0;
    let processingTimeout = null;
    let inputSalaTimeout = null;
    let devolucionTimer = null;
    let reservaTimer = null;

    let lastScannedBuffer = null;
    let lastScannedTime = 0;

    let usuarioEscaneado = null;

    function extraerRUNFromBuffer(str) {
        if (!str) return null;
        str = str.trim();

        const runUrlMatch = str.match(/[?&]run=([^&/]+)/i);
        if (runUrlMatch) return runUrlMatch[1].replace(/[^0-9kK]/g, '');

        const runMatch = str.match(/RUN[^0-9]*([0-9.Kk-]+)/i);
        if (runMatch) return runMatch[1].replace(/[^0-9kK]/g, '');

        const runMatchAlt = str.match(/([0-9]{1,2}(?:\.[0-9]{3}){2}-?[0-9Kk]|[0-9]{7,9}-?[0-9Kk]?)/i);
        if (runMatchAlt) return runMatchAlt[1].replace(/[^0-9kK]/g, '');

        const limpio = str.replace(/[^0-9kK]/g, '');
        return limpio.length >= 6 ? limpio : str;
    }

    function extraerCodigoEspacio(str) {
        if (!str) return null;
        
        let limpio = str.trim().toUpperCase().replace(/['"`?_\s]/g, '-').replace(/-+/g, '-');
        const limpioSoloAlfa = limpio.replace(/[^A-Z0-9]/g, '');

        let encontrado = salasEstudioGlobales.find(s => {
            const idNorm = (s.id_espacio || '').toUpperCase();
            const idSoloAlfa = idNorm.replace(/[^A-Z0-9]/g, '');
            const nomNorm = (s.nombre_espacio || '').toUpperCase().replace(/[^A-Z0-9]/g, '');
            
            return idNorm === limpio || idSoloAlfa === limpioSoloAlfa || nomNorm === limpioSoloAlfa;
        });

        if (encontrado) return encontrado.id_espacio;

        const match = limpio.match(/([A-Z]{1,4})[-']?([A-Z0-9]{1,4})/i);
        if (match) {
            const posibleId = (match[1] + '-' + match[2]).toUpperCase();
            const encontradoMatch = salasEstudioGlobales.find(s => s.id_espacio.toUpperCase() === posibleId);
            if (encontradoMatch) return encontradoMatch.id_espacio;
            return posibleId;
        }

        return limpio;
    }

    document.addEventListener('DOMContentLoaded', function () {
        cargarEspaciosEstudio();
        cargarReservas();
        mantenerEnfoqueGlobal();

        // GESTOR DE ESCÁNER PLANO DIGITAL
        window.addEventListener('keydown', handleKeyScan);

        setInterval(mantenerEnfoqueGlobal, 2000);

        setInterval(verificarNotificacionesServidor, 30000);
        verificarNotificacionesServidor();
    });

    function handleKeyScan(event) {
        const activeElem = document.activeElement;
        const isInputForm = activeElem && (activeElem.tagName === 'INPUT' || activeElem.tagName === 'TEXTAREA' || activeElem.tagName === 'SELECT');
        const inputGlobal = document.getElementById('qr-input-global');
        const inputSalaModal = document.getElementById('input-qr-sala');

        if (isInputForm && activeElem !== inputGlobal && activeElem !== inputSalaModal) {
            return;
        }

        const now = Date.now();
        if (now - lastKeyTime > 300) {
            bufferQR = '';
        }
        lastKeyTime = now;

        const isEnter = (event.key === 'Enter' || event.keyCode === 13 || event.which === 13);

        if (!isEnter) {
            if (event.key && event.key.length === 1) {
                bufferQR += event.key;

                if (bufferQR.length > lastBufferLength) {
                    lastBufferLength = bufferQR.length;

                    if (processingTimeout) clearTimeout(processingTimeout);

                    processingTimeout = setTimeout(() => {
                        if (bufferQR && bufferQR.length >= 2) {
                            const raw = bufferQR;
                            bufferQR = '';
                            procesarLecturaGeneral(raw);
                        }
                    }, 120);
                }
            }
            return;
        }

        if (processingTimeout) clearTimeout(processingTimeout);
        if (bufferQR.length >= 2) {
            event.preventDefault();
            const raw = bufferQR;
            bufferQR = '';
            procesarLecturaGeneral(raw);
        }
    }

    function mantenerEnfoqueGlobal() {
        const inputGlobal = document.getElementById('qr-input-global');
        const activeElem = document.activeElement;
        const modalSala = document.getElementById('modal-seleccionar-sala');
        
        if (modalSala && !modalSala.classList.contains('hidden')) {
            const inputSala = document.getElementById('input-qr-sala');
            if (inputSala && activeElem !== inputSala) {
                inputSala.focus();
            }
            return;
        }

        if (inputGlobal && (!activeElem || activeElem === document.body)) {
            inputGlobal.focus();
        }
    }

    function onInputSalaChange() {
        if (inputSalaTimeout) clearTimeout(inputSalaTimeout);
        inputSalaTimeout = setTimeout(() => {
            procesarEscaneoCodSala();
        }, 120);
    }

    let reservasFiltradas = [];
    let paginaActual = 1;
    const ITEMS_PER_PAGE = 20;

    async function cargarEspaciosEstudio() {
        try {
            const res = await fetch("{{ route('quick-actions.api.espacios') }}");
            const data = await res.json();
            if (data.success && data.espacios) {
                const selectFiltro = document.getElementById('filtro-espacio');
                const selectManual = document.getElementById('manual-select-espacio');
                
                selectFiltro.innerHTML = '<option value="todos">Todos los espacios</option>';
                selectManual.innerHTML = '';

                // Filtrar exclusivamente salas de estudio
                const salas = data.espacios.filter(e => 
                    (e.tipo_espacio && e.tipo_espacio.toLowerCase().includes('estudio')) ||
                    (e.nombre_espacio && e.nombre_espacio.toLowerCase().includes('estudio'))
                );

                salasEstudioGlobales = salas;

                salasEstudioGlobales.forEach(sala => {
                    const optFiltro = document.createElement('option');
                    optFiltro.value = sala.id_espacio;
                    optFiltro.textContent = `${sala.id_espacio} - ${sala.nombre_espacio || 'Sala de Estudio'}`;
                    selectFiltro.appendChild(optFiltro);

                    const optMan = document.createElement('option');
                    optMan.value = sala.id_espacio;
                    optMan.textContent = `${sala.id_espacio} - ${sala.nombre_espacio || 'Sala de Estudio'} (Cap: ${sala.capacidad_alumnos || sala.capacidad_maxima || 'N/A'})`;
                    selectManual.appendChild(optMan);
                });
            }
        } catch (e) {
            console.error('Error al cargar espacios:', e);
        }
    }

    async function cargarReservas() {
        const estado = document.getElementById('filtro-estado').value;
        const espacio = document.getElementById('filtro-espacio').value;
        const fecha = document.getElementById('filtro-fecha').value;
        const orden = document.getElementById('filtro-orden').value;

        const params = new URLSearchParams({ estado, espacio, fecha, orden });
        const tbody = document.getElementById('tabla-reservas-body');
        const cardsContainer = document.getElementById('tabla-reservas-cards');
        
        tbody.innerHTML = `
            <tr>
                <td colspan="7" class="px-3 sm:px-6 py-12 text-center text-gray-500">
                    <div class="flex flex-col items-center">
                        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600 mb-4"></div>
                        <p>Cargando reservas de salas de estudio...</p>
                    </div>
                </td>
            </tr>`;

        if (cardsContainer) {
            cardsContainer.innerHTML = `
                <div class="flex flex-col items-center justify-center py-12 text-gray-500">
                    <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600 mb-4"></div>
                    <p>Cargando reservas...</p>
                </div>`;
        }

        try {
            const res = await fetch(`{{ route('quick-actions.api.reservas-salas-estudio') }}?${params}`);
            const data = await res.json();

            if (data.success) {
                reservasGlobales = data.reservas || [];
                aplicarFiltrosLocales();
            } else {
                tbody.innerHTML = `<tr><td colspan="7" class="px-6 py-4 text-center text-red-500">Error al cargar datos</td></tr>`;
            }
        } catch (e) {
            console.error('Error al cargar reservas:', e);
            tbody.innerHTML = `<tr><td colspan="7" class="px-6 py-4 text-center text-red-500">Error de conexión</td></tr>`;
        }
    }

    function limpiarFiltrosSalasEstudio() {
        const s = document.getElementById('filtro-solicitante'); if (s) s.value = '';
        const e = document.getElementById('filtro-estado'); if (e) e.value = 'todos';
        const esp = document.getElementById('filtro-espacio'); if (esp) esp.value = 'todos';
        const m = document.getElementById('filtro-modulo'); if (m) m.value = 'todos';
        const f = document.getElementById('filtro-fecha'); if (f) f.value = '';
        const o = document.getElementById('filtro-orden'); if (o) o.value = 'desc';
        cargarReservas();
    }
    window.limpiarFiltrosSalasEstudio = limpiarFiltrosSalasEstudio;

    function aplicarFiltrosLocales() {
        const solicitanteFiltro = (document.getElementById('filtro-solicitante')?.value || '').toLowerCase().trim();

        if (solicitanteFiltro) {
            reservasFiltradas = reservasGlobales.filter(r => {
                const nombre = String(r.nombre_responsable || '').toLowerCase();
                const run = String(r.run_responsable || r.run || '').toLowerCase();
                const id = String(r.id_reserva || r.id || '').toLowerCase();
                return nombre.includes(solicitanteFiltro) || run.includes(solicitanteFiltro) || id.includes(solicitanteFiltro);
            });
        } else {
            reservasFiltradas = [...reservasGlobales];
        }

        paginaActual = 1;
        renderizarPaginaActual();
    }

    function renderizarPaginaActual() {
        const tbody = document.getElementById('tabla-reservas-body');
        const cardsContainer = document.getElementById('tabla-reservas-cards');
        const paginacionContainer = document.getElementById('paginacion-salas-estudio');

        const total = reservasFiltradas.length;

        if (total === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="7" class="px-6 py-8 text-center text-gray-500 font-medium">
                        No hay reservas de salas de estudio que coincidan con los filtros
                    </td>
                </tr>`;
            if (cardsContainer) {
                cardsContainer.innerHTML = `
                    <div class="p-6 text-center text-gray-500 bg-white rounded-lg border border-gray-200">
                        No hay reservas de salas de estudio registradas
                    </div>`;
            }
            if (paginacionContainer) paginacionContainer.classList.add('hidden');
            return;
        }

        if (paginacionContainer) paginacionContainer.classList.remove('hidden');

        const totalPaginas = Math.ceil(total / ITEMS_PER_PAGE) || 1;
        if (paginaActual > totalPaginas) paginaActual = totalPaginas;
        if (paginaActual < 1) paginaActual = 1;

        const inicio = (paginaActual - 1) * ITEMS_PER_PAGE;
        const fin = Math.min(inicio + ITEMS_PER_PAGE, total);
        const reservasPagina = reservasFiltradas.slice(inicio, fin);

        // Actualizar contadores
        const elDesde = document.getElementById('paginacion-desde');
        const elHasta = document.getElementById('paginacion-hasta');
        const elTotal = document.getElementById('paginacion-total');
        if (elDesde) elDesde.textContent = (inicio + 1).toLocaleString();
        if (elHasta) elHasta.textContent = fin.toLocaleString();
        if (elTotal) elTotal.textContent = total.toLocaleString();

        // Botones anterior / siguiente
        const btnAnt = document.getElementById('btn-pagina-anterior');
        const btnSig = document.getElementById('btn-pagina-siguiente');
        if (btnAnt) btnAnt.disabled = (paginaActual <= 1);
        if (btnSig) btnSig.disabled = (paginaActual >= totalPaginas);

        // Botones de números de página
        const numerosCont = document.getElementById('paginacion-numeros');
        if (numerosCont) {
            numerosCont.innerHTML = '';
            const maxVisible = 5;
            let startPage = Math.max(1, paginaActual - Math.floor(maxVisible / 2));
            let endPage = Math.min(totalPaginas, startPage + maxVisible - 1);
            if (endPage - startPage + 1 < maxVisible) {
                startPage = Math.max(1, endPage - maxVisible + 1);
            }

            for (let i = startPage; i <= endPage; i++) {
                const btn = document.createElement('button');
                btn.textContent = i;
                btn.onclick = () => cambiarPagina(i);
                btn.className = `px-3 py-1.5 border text-xs sm:text-sm font-semibold rounded-lg transition ${
                    i === paginaActual
                        ? 'bg-blue-600 border-blue-600 text-white shadow-sm'
                        : 'border-gray-300 text-gray-700 bg-white hover:bg-gray-50'
                }`;
                numerosCont.appendChild(btn);
            }
        }

        // Renderizar tabla desktop
        tbody.innerHTML = reservasPagina.map(r => {
            let badgeEstado = '';
            if (r.estado === 'activa') {
                if (r.vencida) {
                    badgeEstado = `<span class="px-2 py-1 inline-flex text-xs font-semibold rounded-full bg-red-100 text-red-800 animate-pulse"><i class="fas fa-exclamation-triangle mr-1"></i> Vencida (2h+)</span>`;
                } else if (r.proxima_vencer) {
                    badgeEstado = `<span class="px-2 py-1 inline-flex text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800"><i class="fas fa-clock mr-1"></i> Por Vencer (<15m)</span>`;
                } else {
                    badgeEstado = `<span class="px-2 py-1 inline-flex text-xs font-semibold rounded-full bg-green-100 text-green-800"><i class="fas fa-check-circle mr-1"></i> Activa</span>`;
                }
            } else if (r.estado === 'finalizada') {
                badgeEstado = `<span class="px-2 py-1 inline-flex text-xs font-semibold rounded-full bg-gray-100 text-gray-800">Finalizada</span>`;
            } else {
                badgeEstado = `<span class="px-2 py-1 inline-flex text-xs font-semibold rounded-full bg-red-50 text-red-600">Cancelada</span>`;
            }

            const minutos = r.minutos_transcurridos || 0;
            const horas = Math.floor(minutos / 60);
            const minsRest = minutos % 60;
            const tiempoTexto = r.estado === 'activa' ? `${horas}h ${minsRest}m / 2h00m` : (r.hora_salida ? `Salida: ${r.hora_salida}` : 'Finalizada');

            return `
                <tr class="hover:bg-gray-50 transition-colors">
                    <td class="px-2 py-3 text-center"><input type="checkbox" class="rounded border-gray-300"></td>
                    <td class="px-2 py-3 text-center">${badgeEstado}</td>
                    <td class="px-2 py-3 text-center text-sm text-gray-900 font-medium">${r.id_espacio}</td>
                    <td class="px-2 py-3">
                        <div class="text-sm font-medium text-gray-900">${r.nombre_responsable}</div>
                        <div class="text-xs text-gray-500">RUN: ${r.run_responsable}</div>
                    </td>
                    <td class="px-2 py-3 text-center text-sm text-gray-900">${r.fecha_reserva} <span class="text-xs text-gray-500">${r.hora_inicio ? r.hora_inicio.substring(0, 5) : ''}</span></td>
                    <td class="px-2 py-3 text-center text-xs text-gray-900 font-medium">
                        <span class="${r.vencida ? 'text-red-600 font-bold' : (r.proxima_vencer ? 'text-yellow-700 font-bold' : 'text-gray-900')}">
                            ${tiempoTexto}
                        </span>
                    </td>
                    <td class="px-2 py-3 text-center">
                        <div class="flex justify-center items-center gap-1">
                            ${r.estado === 'activa' ? `
                                <button onclick="devolverSalaDirecta('${r.id_espacio}', '${r.run_responsable}')" 
                                        class="px-3 py-1 bg-amber-600 hover:bg-amber-700 text-white text-xs font-semibold rounded shadow-sm transition-colors mr-1">
                                    <i class="fas fa-undo mr-1"></i> Devolver
                                </button>
                            ` : ''}
                            <a href="/reservas/${r.id_reserva}/comprobante" 
                               target="_blank"
                               class="p-1.5 border border-blue-200 text-xs font-medium rounded text-blue-600 hover:bg-blue-50 transition-colors"
                               title="Descargar Comprobante PDF">
                                <i class="fa-solid fa-file-pdf"></i>
                            </a>
                            <button onclick="cambiarEstadoReserva('${r.id_reserva}', 'cancelada')" 
                                    class="p-1.5 border border-gray-200 text-xs font-medium rounded text-gray-400 hover:text-red-600 hover:border-red-300 bg-white transition-colors"
                                    title="Cancelar reserva">
                                <i class="fa-solid fa-times"></i>
                            </button>
                        </div>
                    </td>
                </tr>`;
        }).join('');

        // Renderizar tarjetas mobile
        if (cardsContainer) {
            cardsContainer.innerHTML = reservasPagina.map(r => `
                <div class="bg-white p-4 rounded-lg shadow-sm border border-gray-200 space-y-2">
                    <div class="flex items-center justify-between">
                        <span class="font-bold text-gray-900 text-base">${r.id_espacio}</span>
                        ${r.estado === 'activa' ? '<span class="px-2.5 py-0.5 text-xs font-bold bg-green-100 text-green-800 rounded-full">Activa</span>' : '<span class="px-2.5 py-0.5 text-xs font-medium bg-gray-100 text-gray-700 rounded-full">Finalizada</span>'}
                    </div>
                    <div class="text-sm font-semibold text-gray-800">${r.nombre_responsable}</div>
                    <div class="text-xs text-gray-500">RUN: ${r.run_responsable} • ${r.fecha_reserva}</div>
                    <div class="pt-2 flex gap-2 justify-end">
                        ${r.estado === 'activa' ? `
                            <button onclick="devolverSalaDirecta('${r.id_espacio}', '${r.run_responsable}')" class="flex-1 py-2 border border-transparent bg-amber-600 hover:bg-amber-700 text-white text-xs font-medium rounded-lg shadow-xs transition-all">
                                Devolver Sala
                            </button>
                        ` : ''}
                        <a href="/reservas/${r.id_reserva}/comprobante" target="_blank" class="px-3 py-2 border border-gray-300 bg-white hover:bg-gray-50 text-gray-700 text-xs font-medium rounded-lg flex items-center justify-center shadow-xs transition-all">
                            <i class="fa-solid fa-file-pdf mr-1.5 text-red-600"></i> PDF
                        </a>
                    </div>
                </div>
            `).join('');
        }
    }

    function cambiarPagina(nuevaPagina) {
        paginaActual = nuevaPagina;
        renderizarPaginaActual();
    }

    // PROCESAMIENTO PRINCIPAL DE LECTURA DE PLANO DIGITAL
    async function procesarLecturaGeneral(rawText) {
        if (!rawText) return;

        if (usuarioEscaneado && usuarioEscaneado.run) {
            const codEspacio = extraerCodigoEspacio(rawText);
            if (codEspacio) {
                asignarSalaAEstudiante(codEspacio);
                return;
            }
        }

        const now = Date.now();
        if (lastScannedBuffer === rawText.trim() && (now - lastScannedTime) < 2500) {
            return;
        }
        lastScannedBuffer = rawText.trim();
        lastScannedTime = now;

        const modalProcesando = document.getElementById('modal-procesando');
        modalProcesando.classList.remove('hidden');

        const runLimpio = extraerRUNFromBuffer(rawText);

        try {
            const res = await fetch("{{ route('quick-actions.api.sala-estudio.procesar-escaneo') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ paso: 'validar_usuario', run: runLimpio })
            });

            const data = await res.json();
            modalProcesando.classList.add('hidden');

            if (res.status === 403 && data.vetado) {
                alert(data.message);
                return;
            }

            // CASO 1: DEVOLUCIÓN AUTOMÁTICA
            if (data.success && data.devolucion) {
                document.getElementById('dev-estudiante').textContent = data.nombre_estudiante || 'Usuario';
                document.getElementById('dev-sala').textContent = data.id_espacio || 'Sala';
                document.getElementById('dev-hora').textContent = new Date().toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
                document.getElementById('modal-devolucion-exitosa').classList.remove('hidden');
                
                if (devolucionTimer) clearTimeout(devolucionTimer);
                devolucionTimer = setTimeout(cerrarModalDevolucion, 2500);

                cargarReservas();
                return;
            }

            // CASO 2: USUARIO NO REGISTRADO -> REQUIERE REGISTRO DE PERFIL (Plano Digital Style)
            if (data.requiere_registro) {
                document.getElementById('reg-run').value = data.run;
                document.getElementById('reg-nombre').value = '';
                document.getElementById('reg-correo').value = '';
                document.getElementById('reg-telefono').value = '';
                document.getElementById('reg-tipo').value = 'estudiante';
                document.getElementById('modal-registro-solicitante').classList.remove('hidden');
                document.getElementById('reg-nombre').focus();
                return;
            }

            // CASO 3: USUARIO VÁLIDO SIN RESERVA ACTIVA -> MOSTRAR ESCANEO DE SALA (PASO 2)
            usuarioEscaneado = {
                run: runLimpio,
                nombre: data.nombre_estudiante || 'Usuario'
            };

            document.getElementById('sel-nombre-estudiante').textContent = usuarioEscaneado.nombre;
            document.getElementById('modal-seleccionar-sala').classList.remove('hidden');
            const inputSala = document.getElementById('input-qr-sala');
            if (inputSala) {
                inputSala.value = '';
                setTimeout(() => inputSala.focus(), 150);
            }

        } catch (e) {
            console.error('Error al procesar lectura:', e);
            modalProcesando.classList.add('hidden');
            alert('Error al verificar la lectura del código escaneado.');
        }
    }

    async function asignarSalaAEstudiante(idEspacio) {
        const userToBook = usuarioEscaneado;
        if (!userToBook || !userToBook.run) return;

        usuarioEscaneado = null;

        const modalProcesando = document.getElementById('modal-procesando');
        modalProcesando.classList.remove('hidden');
        cerrarModalSeleccionSala();

        try {
            const res = await fetch("{{ route('quick-actions.api.sala-estudio.procesar-escaneo') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ paso: 'asignar_sala', id_espacio: idEspacio, run: userToBook.run })
            });

            const data = await res.json();
            modalProcesando.classList.add('hidden');

            if (data.success) {
                document.getElementById('res-estudiante').textContent = data.nombre_estudiante || userToBook.nombre || 'Usuario';
                document.getElementById('res-sala').textContent = idEspacio;
                document.getElementById('modal-reserva-exitosa').classList.remove('hidden');
                
                if (reservaTimer) clearTimeout(reservaTimer);
                reservaTimer = setTimeout(cerrarModalReservaExitosa, 2500);

                cargarReservas();
            } else {
                alert(data.message || 'Error al asignar la sala.');
            }
        } catch (e) {
            console.error('Error:', e);
            modalProcesando.classList.add('hidden');
            alert('Error de comunicación con el servidor.');
        }
    }

    function procesarEscaneoCodSala() {
        const input = document.getElementById('input-qr-sala');
        const val = input ? input.value.trim() : '';
        if (val) {
            const codEspacio = extraerCodigoEspacio(val);
            if (codEspacio) {
                if (input) input.value = '';
                asignarSalaAEstudiante(codEspacio);
            }
        }
    }

    function mostrarModalDevolucion(data, idEspacioFallback) {
        document.getElementById('dev-estudiante').textContent = data.nombre_estudiante || 'Usuario';
        document.getElementById('dev-sala').textContent = data.id_espacio || idEspacioFallback || 'Sala';
        document.getElementById('dev-hora').textContent = new Date().toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
        
        const iconContainer = document.getElementById('dev-icon-container');
        const icon = document.getElementById('dev-icon');
        const titulo = document.getElementById('dev-titulo');
        const subtitulo = document.getElementById('dev-subtitulo');
        const alerta = document.getElementById('dev-alerta-sancion');
        const duracion = document.getElementById('dev-duracion');

        if (duracion) {
            duracion.textContent = (data.minutos_transcurridos || 0) + ' min';
        }

        if (data.sancionado) {
            if (iconContainer) iconContainer.className = 'w-16 h-16 bg-red-100 text-red-600 rounded-full flex items-center justify-center mx-auto mb-4 text-3xl animate-pulse';
            if (icon) icon.className = 'fas fa-exclamation-triangle';
            if (titulo) {
                titulo.textContent = '⚠️ Devolución Fuera de Plazo';
                titulo.className = 'text-xl font-bold text-red-700 mb-1';
            }
            if (subtitulo) subtitulo.textContent = 'El usuario ha superado las 2 horas máximas permitidas.';
            if (alerta) {
                alerta.classList.remove('hidden');
                alerta.innerHTML = `<strong>🚫 SANCIÓN AUTOMÁTICA (1 SEMANA):</strong><br>El usuario estuvo <b>${data.minutos_transcurridos || 0} min</b> en la sala. Ha sido bloqueado para solicitar salas de estudio por 1 semana (hasta ${data.fecha_fin_veto || "7 días"}).`;
            }
        } else {
            if (iconContainer) iconContainer.className = 'w-16 h-16 bg-green-100 text-green-600 rounded-full flex items-center justify-center mx-auto mb-4 text-3xl';
            if (icon) icon.className = 'fas fa-check-circle';
            if (titulo) {
                titulo.textContent = '🔑 Devolución Registrada';
                titulo.className = 'text-xl font-bold text-gray-900 mb-1';
            }
            if (subtitulo) subtitulo.textContent = 'La devolución de la sala se ha completado correctamente a tiempo.';
            if (alerta) alerta.classList.add('hidden');
        }

        document.getElementById('modal-devolucion-exitosa').classList.remove('hidden');
        
        if (devolucionTimer) clearTimeout(devolucionTimer);
        devolucionTimer = setTimeout(cerrarModalDevolucion, data.sancionado ? 7000 : 2500);

        cargarReservas();
    }

    function cerrarModalDevolucion() {
        if (devolucionTimer) clearTimeout(devolucionTimer);
        document.getElementById('modal-devolucion-exitosa').classList.add('hidden');
        mantenerEnfoqueGlobal();
    }

    function cerrarModalSeleccionSala() {
        document.getElementById('modal-seleccionar-sala').classList.add('hidden');
        usuarioEscaneado = null;
        mantenerEnfoqueGlobal();
    }

    function cerrarModalReservaExitosa() {
        if (reservaTimer) clearTimeout(reservaTimer);
        document.getElementById('modal-reserva-exitosa').classList.add('hidden');
        mantenerEnfoqueGlobal();
    }

    function cerrarModalRegistro() {
        document.getElementById('modal-registro-solicitante').classList.add('hidden');
        mantenerEnfoqueGlobal();
    }

    function abrirModalManualReserva() {
        document.getElementById('modal-entrada-manual').classList.remove('hidden');
        document.getElementById('manual-run').focus();
    }

    function cerrarModalManual() {
        document.getElementById('modal-entrada-manual').classList.add('hidden');
        mantenerEnfoqueGlobal();
    }

    async function procesarReservaManualDirecta() {
        const run = document.getElementById('manual-run').value.trim();
        const idEspacio = document.getElementById('manual-select-espacio').value;

        if (!run || !idEspacio) {
            alert('Ingrese RUN y seleccione una sala.');
            return;
        }

        cerrarModalManual();
        usuarioEscaneado = { run: run, nombre: 'Manual' };
        asignarSalaAEstudiante(idEspacio);
    }

    async function guardarYContinuar(e) {
        e.preventDefault();
        const run = document.getElementById('reg-run').value;
        const nombre = document.getElementById('reg-nombre').value;
        const correo = document.getElementById('reg-correo').value;
        const telefono = document.getElementById('reg-telefono').value;
        const tipo_solicitante = document.getElementById('reg-tipo').value;

        try {
            const res = await fetch("{{ route('quick-actions.api.sala-estudio.procesar-escaneo') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ paso: 'validar_usuario', run, nombre, correo, telefono, tipo_solicitante })
            });

            const data = await res.json();
            cerrarModalRegistro();

            if (data.success || data.requiere_registro === false) {
                usuarioEscaneado = { run: run, nombre: nombre };
                document.getElementById('sel-nombre-estudiante').textContent = nombre;
                document.getElementById('modal-seleccionar-sala').classList.remove('hidden');
                const inputSala = document.getElementById('input-qr-sala');
                if (inputSala) {
                    inputSala.value = '';
                    setTimeout(() => inputSala.focus(), 150);
                }
            } else {
                alert(data.message || 'Error al registrar.');
            }
        } catch (err) {
            console.error('Error al guardar:', err);
            alert('Error de comunicación.');
        }
    }

    async function devolverSalaDirecta(idEspacio, run) {
        if (!confirm(`¿Confirmar devolución de la sala ${idEspacio}?`)) return;

        try {
            const res = await fetch("{{ route('quick-actions.api.sala-estudio.procesar-escaneo') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ id_espacio: idEspacio, run: run })
            });
            const data = await res.json();
            if (data.success) {
                document.getElementById('dev-estudiante').textContent = data.nombre_estudiante || 'Usuario';
                document.getElementById('dev-sala').textContent = idEspacio;
                document.getElementById('dev-hora').textContent = new Date().toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
                document.getElementById('modal-devolucion-exitosa').classList.remove('hidden');
                
                if (devolucionTimer) clearTimeout(devolucionTimer);
                devolucionTimer = setTimeout(cerrarModalDevolucion, 2500);

                cargarReservas();
            } else {
                alert(data.message || 'Error al devolver sala.');
            }
        } catch (e) {
            console.error('Error:', e);
        }
    }

    async function cambiarEstadoReserva(idReserva, nuevoEstado) {
        if (!confirm(`¿Desea marcar esta reserva como ${nuevoEstado}?`)) return;

        try {
            const res = await fetch(`{{ url('/quick-actions/api/reserva') }}/${idReserva}/estado`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ estado: nuevoEstado })
            });
            const data = await res.json();
            if (data.success) {
                cargarReservas();
            } else {
                alert(data.message || 'Error al actualizar estado.');
            }
        } catch (e) {
            console.error('Error:', e);
        }
    }

    async function verificarNotificacionesServidor() {
        try {
            const res = await fetch("{{ route('quick-actions.api.sala-estudio.verificar-notificaciones') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            });
            const data = await res.json();
            if (data.success && (data.advertencias_enviadas > 0 || data.vencidos_enviadas > 0)) {
                if (window.Livewire) {
                    Livewire.dispatch('notificacionCreada');
                }
            }
        } catch (e) {
            console.error('Error al verificar notificaciones:', e);
        }
    }
</script>
</x-app-layout>
