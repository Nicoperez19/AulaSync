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

            <div class="flex flex-wrap items-center gap-2">
                <button type="button" onclick="abrirModalManualReserva()" 
                   class="inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold rounded-lg shadow-sm hover:shadow transition-all duration-200 cursor-pointer">
                    <x-icons.add class="w-4 h-4 shrink-0" aria-hidden="true" />
                    <span>Nueva Reserva</span>
                </button>
                <a href="{{ route('quick-actions.index') }}" 
                   class="inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 text-sm font-semibold rounded-lg shadow-sm hover:shadow transition-all duration-200">
                    <i class="fas fa-arrow-left text-xs"></i>
                    <span>Volver</span>
                </a>
            </div>
        </div>
    </x-slot>

    <div class="space-y-6 pb-8">

        <!-- Input Oculto y Continuo de Lectura QR (Plano Digital Style) -->
        <input type="text" id="qr-input-global" class="sr-only absolute top-0 left-0 opacity-0 w-1 h-1 pointer-events-none" autofocus autocomplete="off">

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

        <!-- Tabla de reservas -->
        <div class="bg-white shadow-sm rounded-xl border border-gray-200 overflow-hidden dark:bg-gray-800 dark:border-gray-700">
            <div class="hidden lg:block overflow-x-auto">
                <table class="min-w-full table-fixed divide-y divide-gray-200 dark:divide-gray-700 text-sm">
                    <thead class="bg-[#950101] text-white border-b border-[#7B0101]">
                        <tr>
                            <th class="px-3 py-3.5 text-center text-xs font-semibold text-white uppercase tracking-wider w-[5%]">
                                <input type="checkbox" id="select-all-reservas" class="rounded border-gray-300 text-[#D12420] focus:ring-[#950101]">
                            </th>
                            <th class="px-4 py-3.5 text-center text-xs font-semibold text-white uppercase tracking-wider w-[14%] cursor-pointer hover:text-red-100">
                                ESTADO <i class="fa-solid fa-sort ml-1 text-xs text-white/50"></i>
                            </th>
                            <th class="px-4 py-3.5 text-center text-xs font-semibold text-white uppercase tracking-wider w-[15%] cursor-pointer hover:text-red-100">
                                ESPACIO <i class="fa-solid fa-sort ml-1 text-xs text-white/50"></i>
                            </th>
                            <th class="px-6 py-3.5 text-left text-xs font-semibold text-white uppercase tracking-wider w-[26%] cursor-pointer hover:text-red-100">
                                RESPONSABLE <i class="fa-solid fa-sort ml-1 text-xs text-white/50"></i>
                            </th>
                            <th class="px-4 py-3.5 text-center text-xs font-semibold text-white uppercase tracking-wider w-[14%] cursor-pointer hover:text-red-100">
                                FECHA / INGRESO <i class="fa-solid fa-sort ml-1 text-xs text-white/50"></i>
                            </th>
                            <th class="px-4 py-3.5 text-center text-xs font-semibold text-white uppercase tracking-wider w-[14%] cursor-pointer hover:text-red-100">
                                PERMANENCIA (MÁX 2H) <i class="fa-solid fa-sort ml-1 text-xs text-white/50"></i>
                            </th>
                            <th class="px-4 py-3.5 text-center text-xs font-semibold text-white uppercase tracking-wider w-[12%]">
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
            <div id="tabla-reservas-cards" class="lg:hidden space-y-4 p-4">
                <div class="flex flex-col items-center justify-center py-12 text-gray-500">
                    <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600 mb-4"></div>
                    <p>Cargando reservas...</p>
                </div>
            </div>

            <!-- Controles de Paginación (20 por página) -->
            <div id="paginacion-salas-estudio" class="flex flex-col sm:flex-row items-center justify-between gap-4 px-6 py-4 border-t border-gray-200 bg-gray-50">
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

<div data-page-config="SalasEstudioConfig" data-config="{{ json_encode(['apiEspaciosUrl' => route('quick-actions.api.espacios'), 'apiReservasUrl' => route('quick-actions.api.reservas-salas-estudio'), 'apiProcesarEscaneoUrl' => route('quick-actions.api.sala-estudio.procesar-escaneo'), 'apiReservaEstadoBaseUrl' => url('/quick-actions/api/reserva'), 'apiVerificarNotifUrl' => route('quick-actions.api.sala-estudio.verificar-notificaciones'), 'csrfToken' => csrf_token()]) }}" class="hidden"></div>
@vite(['resources/js/views/acciones-rapidas/gestionar-salas-estudio.js'])
@endpush
