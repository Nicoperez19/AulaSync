<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 pr-6 md:flex-row md:items-center md:justify-between">
            <div class="flex items-center gap-3">
                <div class="p-2 rounded-xl bg-light-cloud-blue">
                    <i class="text-2xl text-white fas fa-calendar-check"></i>
                </div>

                <div>
                    <div class="flex items-center gap-3">
                        <h2 class="text-2xl font-bold leading-tight">Gestión de Reservas</h2>
                    
                    </div>
                    <p class="text-sm text-gray-500">Administrar estados de reservas activas y finalizadas</p>
                </div>
            </div>

            <div class="flex items-center gap-2.5">
                <a href="{{ route('quick-actions.crear-reserva') }}" 
                   class="inline-flex items-center gap-2 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-medium rounded-lg shadow-sm transition-all duration-150 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500">
                    <x-icons.add class="w-4 h-4 shrink-0" aria-hidden="true" />
                    <span>Nueva Reserva</span>
                </a>
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

    <!-- Widget móvil de reservas activas -->
    <div class="sm:hidden bg-white overflow-hidden shadow-sm rounded-lg">
        <div class="p-3">
            <div class="flex items-center justify-center gap-2">
                <i class="fa-solid fa-circle-check text-green-600 text-lg"></i>
                <span class="text-sm font-medium text-green-700">Vigentes:</span>
                <span class="text-xl font-bold text-green-900" id="stats-activas-mobile">0</span>
            </div>
        </div>
    </div>

    <!-- Sección FILTROS -->
    <div class="bg-white p-5 rounded-xl border border-gray-200 shadow-sm mb-6 dark:bg-gray-800 dark:border-gray-700">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-sm font-bold text-gray-700 uppercase tracking-wider flex items-center gap-2 dark:text-gray-200">
                <i class="fas fa-filter text-[#950101]"></i> FILTROS
            </h3>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
            <div>
                <label class="block text-xs font-semibold text-gray-700 uppercase mb-1 dark:text-gray-300">Buscar</label>
                <div class="relative">
                    <input 
                        type="text"
                        id="filtro-solicitante-reserva"
                        placeholder="Solicitante, RUN, asignatura o actividad..."
                        oninput="filtrarReservas()"
                        class="w-full pl-9 pr-3 py-2 text-sm border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-[#950101]/20 focus:border-[#950101] dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                    />
                    <i class="fas fa-search absolute left-3 top-2.5 text-gray-400 text-xs"></i>
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-gray-700 uppercase mb-1 dark:text-gray-300">Estado</label>
                <select 
                    id="filtro-estado-reserva"
                    onchange="filtrarReservas()"
                    class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-[#950101]/20 focus:border-[#950101] bg-white dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                    <option value="">Todos los estados</option>
                    <option value="activa">Activas</option>
                    <option value="programada">Programadas</option>
                    <option value="finalizada">Finalizadas</option>
                </select>
            </div>

            <div>
                <label class="block text-xs font-semibold text-gray-700 uppercase mb-1 dark:text-gray-300">Espacio</label>
                <select 
                    id="filtro-espacio-reserva"
                    onchange="filtrarReservas()"
                    class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-[#950101]/20 focus:border-[#950101] bg-white dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                    <option value="">Todos los espacios</option>
                </select>
            </div>
            
            <div>
                <label class="block text-xs font-semibold text-gray-700 uppercase mb-1 dark:text-gray-300">Módulo</label>
                <select 
                    id="filtro-modulo-reserva"
                    onchange="filtrarReservas()"
                    class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-[#950101]/20 focus:border-[#950101] bg-white dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                    <option value="">Todos los módulos</option>
                </select>
            </div>

            <div>
                <label class="block text-xs font-semibold text-gray-700 uppercase mb-1 dark:text-gray-300">Fecha</label>
                <input 
                    type="date"
                    id="filtro-fecha-reserva"
                    onchange="filtrarReservas()"
                    class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-[#950101]/20 focus:border-[#950101] dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                />
            </div>
            
            <div>
                <label class="block text-xs font-semibold text-gray-700 uppercase mb-1 dark:text-gray-300">Ordenar por</label>
                <select 
                    id="ordenar-reservas"
                    onchange="aplicarOrdenamiento()"
                    class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-[#950101]/20 focus:border-[#950101] bg-white dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                    <option value="fecha-desc">Fecha (más reciente)</option>
                    <option value="fecha-asc">Fecha (más antigua)</option>
                    <option value="responsable-asc">Responsable (A-Z)</option>
                    <option value="responsable-desc">Responsable (Z-A)</option>
                    <option value="espacio-asc">Espacio (A-Z)</option>
                    <option value="espacio-desc">Espacio (Z-A)</option>
                    <option value="hora-asc">Hora (temprano-tarde)</option>
                    <option value="hora-desc">Hora (tarde-temprano)</option>
                </select>
            </div>
        </div>

        <!-- Botones de Acción separados por línea -->
        <div class="flex items-center justify-end gap-2 mt-4 pt-4 border-t border-gray-100 dark:border-gray-700">
            <button 
                type="button"
                onclick="limpiarFiltrosReservas()"
                class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 border border-gray-300 rounded-lg transition-colors inline-flex items-center gap-2 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200 dark:hover:bg-gray-600">
                <i class="fas fa-undo text-xs"></i> Limpiar
            </button>
            <button 
                type="button"
                onclick="cargarReservas()"
                class="px-4 py-2 text-sm font-semibold text-white bg-[#191919] hover:bg-black active:bg-gray-900 rounded-lg shadow-sm transition-colors inline-flex items-center gap-2">
                <i class="fas fa-filter text-xs"></i> Aplicar filtros
            </button>
        </div>
    </div>

    <!-- Tabla de reservas -->
    <div class="bg-white shadow-sm rounded-xl border border-gray-200 overflow-hidden">
        <!-- Versión Desktop -->
        <div class="hidden lg:block overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 table-fixed">
                <thead class="bg-[#950101] text-white border-b border-[#7B0101]">
                    <tr>
                        <th scope="col" class="w-12 px-3 py-3.5 text-center">
                            <input type="checkbox" id="select-all-reservas" onchange="toggleSelectAllReservas(this)" class="rounded border-gray-300 text-blue-600 focus:ring-[#950101] focus:border-[#950101] cursor-pointer">
                        </th>
                        <th scope="col" class="w-28 px-3 py-3.5 text-center text-xs font-semibold text-white uppercase tracking-wider cursor-pointer hover:text-red-100 select-none" onclick="ordenarPor('fecha')">
                            <span class="inline-flex items-center gap-1 justify-center">Fecha <i id="sort-icon-fecha" class="fa-solid fa-sort text-xs text-white/50"></i></span>
                        </th>
                        <th scope="col" class="w-32 px-3 py-3.5 text-center text-xs font-semibold text-white uppercase tracking-wider cursor-pointer hover:text-red-100 select-none" onclick="ordenarPor('modulo')">
                            <span class="inline-flex items-center gap-1 justify-center">Módulos <i id="sort-icon-modulo" class="fa-solid fa-sort text-xs text-white/50"></i></span>
                        </th>
                        <th scope="col" class="w-24 px-3 py-3.5 text-center text-xs font-semibold text-white uppercase tracking-wider cursor-pointer hover:text-red-100 select-none" onclick="ordenarPor('espacio')">
                            <span class="inline-flex items-center gap-1 justify-center">Espacio <i id="sort-icon-espacio" class="fa-solid fa-sort text-xs text-white/50"></i></span>
                        </th>
                        <th scope="col" class="w-48 px-3 py-3.5 text-center text-xs font-semibold text-white uppercase tracking-wider cursor-pointer hover:text-red-100 select-none" onclick="ordenarPor('responsable')">
                            <span class="inline-flex items-center gap-1 justify-center">Responsable <i id="sort-icon-responsable" class="fa-solid fa-sort text-xs text-white/50"></i></span>
                        </th>
                        <th scope="col" class="px-3 py-3.5 text-center text-xs font-semibold text-white uppercase tracking-wider">
                            Asignatura / Actividad
                        </th>
                        <th scope="col" class="w-32 px-3 py-3.5 text-center text-xs font-semibold text-white uppercase tracking-wider cursor-pointer hover:text-red-100 select-none" onclick="ordenarPor('estado')">
                            <span class="inline-flex items-center gap-1 justify-center">Estado <i id="sort-icon-estado" class="fa-solid fa-sort text-xs text-white/50"></i></span>
                        </th>
                        <th scope="col" class="w-44 px-3 py-3.5 text-center text-xs font-semibold text-white uppercase tracking-wider">
                            Acciones
                        </th>
                    </tr>
                </thead>
                <tbody id="tabla-reservas-body" class="bg-white divide-y divide-gray-200">
                    <tr>
                        <td colspan="8" class="px-6 py-12 text-center text-gray-500">
                            <div class="flex flex-col items-center">
                                <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600 mb-4"></div>
                                <p class="text-sm font-medium">Cargando reservas...</p>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Versión Mobile/Tablet (Cards) -->
        <div id="tabla-reservas-cards" class="lg:hidden p-4 space-y-3">
            <div class="flex flex-col items-center justify-center py-12 text-gray-500">
                <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600 mb-4"></div>
                <p class="text-sm font-medium">Cargando reservas...</p>
            </div>
        </div>

        <!-- Controles de paginación (Barra footer con padding generoso) -->
        <div id="paginacion-reservas" class="bg-gray-50/70 border-t border-gray-200 px-6 py-4 flex flex-col sm:flex-row items-center justify-between gap-3 text-sm text-gray-700">
            <div id="paginacion-info" class="text-xs sm:text-sm text-gray-500 font-medium">
                Mostrando <span id="pag-desde" class="font-bold text-gray-900">0</span> a <span id="pag-hasta" class="font-bold text-gray-900">0</span> de <span id="pag-total" class="font-bold text-gray-900">0</span> reservas
            </div>
            <div id="paginacion-botones" class="flex items-center gap-1.5 flex-wrap justify-center">
                <!-- Botones dinámicos -->
            </div>
        </div>
        
        <!-- Controles de acciones en lote -->
        <div id="acciones-lote" class="border-t border-gray-200 bg-gray-50/70 px-6 py-3.5" style="display: none;">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <div class="text-sm text-gray-700 font-medium">
                    <span id="contador-seleccionadas" class="font-bold text-gray-900">0</span> reserva(s) seleccionada(s)
                </div>
                <div class="flex flex-wrap items-center gap-2">
                    <button 
                        type="button"
                        id="btn-finalizar-lote"
                        onclick="finalizarReservasEnLote()"
                        title="Finalizar reservas seleccionadas"
                        class="btn btn-danger gap-1.5 px-4 py-2 text-sm font-medium">
                        <i class="fa-solid fa-xmark w-4 h-4 mr-1.5"></i>
                        Finalizar Seleccionadas
                    </button>
                    <button 
                        type="button"
                        id="btn-cancelar-lote"
                        onclick="cancelarReservasEnLote()"
                        title="Cancelar reserva"
                        class="btn btn-danger gap-1.5 px-4 py-2 text-sm font-medium">
                        <x-icons.delete class="w-4 h-4 mr-1.5" aria-hidden="true" />
                        Cancelar Seleccionadas
                    </button>
                    <button 
                        type="button"
                        onclick="limpiarSeleccion()"
                        class="inline-flex items-center justify-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-lg text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-300 transition-all shadow-sm">
                        <i class="fa-solid fa-xmark w-4 h-4 mr-1.5"></i>
                        Limpiar Selección
                    </button>
                </div>
            </div>
        </div>
    </div>


</div>

<!-- Modal para editar reserva -->
<div id="modal-editar-reserva" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-full max-w-2xl shadow-lg rounded-lg bg-white">
        <div class="flex justify-between items-center mb-4 pb-3 border-b">
            <h3 class="text-xl font-bold text-gray-900">
                <i class="fa-solid fa-edit text-blue-600 mr-2"></i>
                Editar Reserva
            </h3>
            <button onclick="cerrarModalEditar()" class="text-white/50 hover:text-gray-600">
                <i class="fa-solid fa-times text-2xl"></i>
            </button>
        </div>
        
        <form id="form-editar-reserva-modal" onsubmit="guardarEdicionReserva(event)">
            <input type="hidden" id="edit-reserva-id">
            
            <!-- Información del responsable y Reasignación -->
            <div class="mb-6 p-4 bg-gray-50 rounded-lg border border-gray-200">
                <h4 class="text-sm font-semibold text-gray-800 mb-3">Información del Responsable</h4>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-3 text-sm">
                    <div>
                        <span class="font-medium text-gray-600 block text-xs">Nombre actual:</span>
                        <span class="text-gray-900 font-semibold" id="edit-responsable-nombre">-</span>
                    </div>
                    <div>
                        <span class="font-medium text-gray-600 block text-xs">RUN:</span>
                        <span class="text-gray-900 font-semibold" id="edit-responsable-run">-</span>
                    </div>
                    <div>
                        <span class="font-medium text-gray-600 block text-xs">Tipo:</span>
                        <span class="text-gray-900 font-semibold" id="edit-responsable-tipo">-</span>
                    </div>
                </div>

                <!-- Campo para reasignar docente -->
                <div class="mt-4 pt-3 border-t border-gray-200">
                    <label class="block text-xs font-semibold text-gray-700 mb-1">
                        <i class="fa-solid fa-user-pen text-blue-600 mr-1"></i> Reasignar a otro docente
                        <span class="font-normal text-gray-500">(opcional — dejar vacío para mantener el actual)</span>
                    </label>
                    <div class="relative">
                        <input id="edit-nuevo-docente-buscar"
                               type="search"
                               autocomplete="off"
                               placeholder="Escribe nombre, correo o RUN del nuevo docente..."
                               class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#950101] focus:border-[#950101] bg-white" />
                        <input type="hidden" id="edit-nuevo-docente-run" name="edit_nuevo_run_profesor" />
                        <div id="edit-nuevo-docente-sugerencias"
                             class="absolute z-30 w-full mt-1 bg-white border border-gray-200 rounded-lg shadow-lg max-h-48 overflow-y-auto hidden">
                        </div>
                    </div>
                    <div id="edit-nuevo-docente-confirmado" class="hidden mt-2 flex items-center justify-between gap-2 text-xs font-medium text-emerald-800 bg-emerald-50 border border-emerald-200 rounded-lg p-2">
                        <span class="flex items-center gap-1.5 truncate">
                            <i class="fa-solid fa-circle-check text-emerald-600"></i>
                            <span id="edit-nuevo-docente-confirmado-texto"></span>
                        </span>
                        <button type="button" onclick="limpiarNuevoDocenteModal()" class="text-emerald-700 hover:text-emerald-900 font-bold px-1">✕</button>
                    </div>
                </div>
            </div>
            
            <!-- Campos editables -->
            <div class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Espacio *</label>
                        <select 
                            id="edit-codigo-espacio"
                            required
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#950101] focus:border-[#950101]">
                            <option value="">Cargando espacios...</option>
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Fecha *</label>
                        <input 
                            type="date" 
                            id="edit-fecha"
                            required
                            min="{{ date('Y-m-d') }}"
                            onchange="actualizarModulosPorFechaModal()"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#950101] focus:border-[#950101]"
                        />
                    </div>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Módulo inicial *</label>
                        <select 
                            id="edit-modulo-inicial"
                            required
                            onchange="actualizarModulosFinalesModal()"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#950101] focus:border-[#950101]">
                            <option value="">Seleccione módulo inicial</option>
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Módulo final *</label>
                        <select 
                            id="edit-modulo-final"
                            required
                            onchange="actualizarPreviewHorario()"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#950101] focus:border-[#950101]">
                            <option value="">Seleccione módulo final</option>
                        </select>
                    </div>
                </div>
                
                <!-- Horario original (FIJO - no cambia) -->
                <div class="bg-gray-50 border border-gray-300 rounded-lg p-3">
                    <div class="flex items-start gap-2">
                        <i class="fa-solid fa-clock text-gray-600 mt-0.5"></i>
                        <div class="text-sm">
                            <span class="font-medium text-gray-700">Horario original (referencia):</span>
                            <span class="text-gray-900" id="horario-original-fijo">-</span>
                        </div>
                    </div>
                </div>
                
                <!-- Preview del nuevo horario seleccionado -->
                <div class="bg-blue-50 border border-blue-300 rounded-lg p-3">
                    <div class="flex items-start gap-2">
                        <i class="fa-solid fa-calendar-check text-blue-600 mt-0.5"></i>
                        <div class="text-sm">
                            <span class="font-medium text-blue-700">Nuevo horario:</span>
                            <span class="text-blue-900" id="preview-horario">Seleccione los módulos</span>
                        </div>
                    </div>
                </div>
                
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Observaciones originales</label>
                    <textarea 
                        id="edit-observaciones-originales"
                        rows="2"
                        disabled
                        class="w-full px-3 py-2 border border-gray-200 rounded-lg bg-gray-50 text-gray-600 text-sm"></textarea>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Agregar observaciones
                        <span class="text-xs text-gray-500">(se agregará a las existentes)</span>
                    </label>
                    <textarea 
                        id="edit-observaciones-nuevas"
                        rows="2"
                        placeholder="Escriba observaciones adicionales..."
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#950101] focus:border-[#950101]"></textarea>
                </div>
            </div>

            <!-- Botones -->
            <div class="mt-6 flex justify-end gap-3">
                <button 
                    type="button"
                    onclick="cerrarModalEditar()"
                    class="px-4 py-2 border border-gray-300 text-sm font-medium rounded-lg text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-300 transition-all shadow-sm">
                    Cancelar
                </button>
                <button 
                    type="submit"
                    id="btn-guardar-edicion"
                    class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-lg text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#950101] focus:border-[#950101] transition-all shadow-sm disabled:opacity-50 disabled:cursor-not-allowed">
                    <i class="fa-solid fa-save mr-2"></i>
                    Guardar cambios
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<template id="tpl-icono-cancelar"><x-icons.delete class="w-3.5 h-3.5 inline-block" aria-hidden="true" /></template>
<template id="tpl-icono-cancelar-mobile"><x-icons.delete class="w-4 h-4 inline-block" aria-hidden="true" /></template>

@push('scripts')
<script>
window.GestionarReservasConfig = {
    modulosBase: @json(\App\Helpers\ModulosHelper::getHorariosModulos()['lunes'] ?? [])
};
</script>
@vite(['resources/js/views/acciones-rapidas/gestionar-reservas.js'])
@endpush
