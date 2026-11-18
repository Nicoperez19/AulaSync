<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-2 pr-6 md:flex-row md:items-center md:justify-between">
            <div class="flex items-center gap-3">
                <div class="p-2 rounded-xl bg-light-cloud-blue">
                    <i class="text-2xl text-white fa-solid fa-calendar-check"></i>
                </div>

                <div>
                    <h2 class="text-2xl font-bold leading-tight">SOLICITUDES Y RESERVAS</h2>
                    <p class="text-sm text-gray-500">Reporte de reservas de espacios y porcentaje de ocupación (Docentes y Externos)</p>
                </div>
            </div>

            <div class="flex flex-wrap items-center gap-2.5">
                @can('admin panel')
                    <a href="{{ route('quick-actions.crear-reserva') }}"
                       class="inline-flex items-center gap-2 px-4 py-2 bg-[#950101] hover:bg-[#7B0101] text-white text-sm font-semibold rounded-xl shadow-sm transition-colors duration-150">
                        <i class="fa-solid fa-plus text-xs"></i>
                        <span>Nueva Reserva</span>
                    </a>
                @endcan
                <a href="{{ route('dashboard') }}"
                   class="inline-flex items-center gap-2 px-4 py-2 bg-white hover:bg-slate-50 border border-slate-200 hover:border-slate-300 text-slate-700 text-sm font-semibold rounded-xl shadow-xs transition-colors duration-150">
                    <i class="fa-solid fa-arrow-left text-xs text-slate-500"></i>
                    <span>Dashboard</span>
                </a>
            </div>
        </div>
    </x-slot>

    <div>
        @livewire('reservations-table')
    </div>

    {{-- =============================================
         MODAL: Registro Administrativo Entrada/Salida
         ============================================= --}}
    <div id="modal-admin-registro"
         class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 backdrop-blur-sm p-4"
         role="dialog" aria-modal="true" aria-labelledby="modal-admin-titulo">

        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl w-full max-w-md mx-auto overflow-hidden">

            {{-- Cabecera del modal --}}
            <div id="modal-admin-header" class="px-6 py-4 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <span id="modal-admin-icono" class="text-xl"></span>
                    <h3 id="modal-admin-titulo" class="text-base font-bold text-white"></h3>
                </div>
                <button onclick="cerrarModalAdmin()" class="text-white/80 hover:text-white transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            {{-- Cuerpo --}}
            <div class="px-6 py-4 space-y-4">
                <div class="grid grid-cols-2 gap-3 text-sm">
                    <div class="bg-gray-50 dark:bg-gray-700 rounded-xl p-3">
                        <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-0.5">Responsable</p>
                        <p id="modal-admin-docente" class="font-bold text-gray-800 dark:text-gray-100 truncate">—</p>
                    </div>
                    <div class="bg-gray-50 dark:bg-gray-700 rounded-xl p-3">
                        <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-0.5">Sala</p>
                        <p id="modal-admin-sala" class="font-bold text-gray-800 dark:text-gray-100 truncate">—</p>
                    </div>
                    <div class="bg-gray-50 dark:bg-gray-700 rounded-xl p-3">
                        <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-0.5">Fecha</p>
                        <p id="modal-admin-fecha" class="font-bold text-gray-800 dark:text-gray-100">—</p>
                    </div>
                    <div class="bg-gray-50 dark:bg-gray-700 rounded-xl p-3">
                        <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-0.5">Horario reserva</p>
                        <p id="modal-admin-horario" class="font-bold text-gray-800 dark:text-gray-100">—</p>
                    </div>
                </div>

                {{-- Campo hora real (solo para entrada) --}}
                <div id="campo-hora-real" class="hidden">
                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">
                        Hora real de inicio <span class="text-xs font-normal text-gray-400">(opcional — si es distinta a la reservada)</span>
                    </label>
                    <input type="time" id="input-hora-real"
                           class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 text-sm
                                  bg-white dark:bg-gray-700 text-gray-800 dark:text-gray-100
                                  focus:ring-2 focus:ring-blue-500 focus:border-blue-500" />
                    <p class="text-xs text-gray-400 mt-1">
                        Útil para corregir casos donde la actividad comenzó antes de que se registrara en el sistema.
                    </p>
                </div>

                {{-- Mensaje de error --}}
                <div id="modal-admin-error" class="hidden text-sm text-rose-700 bg-rose-50 border border-rose-200 rounded-lg px-3 py-2"></div>
            </div>

            {{-- Pie --}}
            <div class="px-6 py-4 bg-gray-50/70 flex justify-end gap-3 border-t border-gray-200">
                <button onclick="cerrarModalAdmin()"
                        class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-300 transition-all shadow-sm">
                    Cancelar
                </button>
                <button id="btn-confirmar-admin"
                        onclick="confirmarRegistroAdmin()"
                        class="inline-flex items-center px-4 py-2 text-sm font-medium text-white border border-transparent rounded-md bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all shadow-sm gap-2 disabled:opacity-50 disabled:cursor-not-allowed">
                    <svg id="btn-spinner" class="w-4 h-4 animate-spin hidden" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/>
                    </svg>
                    <span id="btn-confirmar-texto">Confirmar</span>
                </button>
            </div>
        </div>
    </div>

@vite(['resources/js/views/reservas/solicitudes-reservas.js'])
</x-app-layout>
