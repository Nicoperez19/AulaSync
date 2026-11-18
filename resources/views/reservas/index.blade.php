<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <h2 class="text-xl font-semibold leading-tight" style="font-style: oblique;">
                {{ __('Reservas') }}
            </h2>
        </div>
    </x-slot>

    <div class="flex justify-end mb-4">
        <x-button x-on:click.prevent="$dispatch('open-modal', 'add-reserva')" variant="add" class="gap-2">
            <x-icons.add class="w-4 h-4 shrink-0" aria-hidden="true" />
            <span>Nueva Reserva</span>
        </x-button>
    </div>

    @livewire('reservations-table')

    <x-modal name="add-reserva" :show="$errors->any()" focusable>
        <form method="POST" action="{{ route('reservas.store') }}">
            @csrf
            <!-- Ajuste de estilo para parecerse al modal del plano digital -->
            <div class="p-6 space-y-6 bg-white rounded-lg max-w-4xl mx-auto">
                <!-- Filtros Universiad y Facultad -->
                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div class="space-y-2">
                        <x-form.label for="universidad" :value="__('Universidad')" />
                        <select id="universidad" name="universidad" class="block w-full border-gray-300 rounded-md"
                            required>
                            <option value="">Seleccione universidad</option>
                            @foreach ($universidades as $uni)
                                <option value="{{ $uni->id_universidad }}">{{ $uni->nombre_universidad }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="space-y-2">
                        <x-form.label for="facultad" :value="__('Facultad')" />
                        <select id="facultad" name="facultad" class="block w-full border-gray-300 rounded-md" required>
                            <option value="">Seleccione facultad</option>
                        </select>
                    </div>
                </div>

                <!-- Espacios Disponibles (aparecerá dinámicamente) -->
                <div id="espacios-container" class="hidden">
                    <div class="mb-4 space-y-2">
                        <x-form.label :value="__('Espacio a reservar')" />
                        <div id="espacios-grid" class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3">
                            <!-- Espacios se cargarán aquí -->
                        </div>
                    </div>
                </div>

                <!-- Fecha y Módulo -->
                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div class="space-y-2">
                        <x-form.label for="fecha_reserva" :value="__('Fecha de Reserva')" />
                        <x-form.input id="fecha_reserva" name="fecha_reserva" type="date" class="block w-full"
                            required />
                    </div>
                    <div class="space-y-2">
                        <x-form.label for="hora_select" :value="__('Módulo / Hora de inicio')" />
                        <!-- Select visible con módulos disponibles; el valor enviado será el hidden 'hora' -->
                        <select id="hora_select" class="block w-full border-gray-300 rounded-md" aria-label="Módulo de inicio">
                            <option value="">Seleccione módulo</option>
                        </select>
                        <!-- Hidden con la hora real (HH:MM) que espera el backend -->
                        <input type="hidden" id="hora" name="hora" />

                        <!-- Módulos seleccionables (se mostrará al elegir un espacio) -->
                        <div id="modulos-container" class="space-y-2 hidden mt-2">
                            <x-form.label for="modulos" :value="__('Cantidad de Módulos')" />
                            <input id="input-modulos" name="modulos" type="number" min="1" value="1" class="w-24 px-3 py-2 border rounded" />
                            <p class="text-sm text-gray-500">Módulo actual: <span id="modulo-actual-display">-</span></p>
                        </div>
                    </div>
                </div>

                <!-- Usuario (autocompletar por correo) -->
                <div class="space-y-2">
                    <x-form.label for="usuario_buscar" :value="__('Correo del usuario')" />
                    <input id="usuario_buscar" name="usuario_buscar" type="search" autocomplete="off"
                        class="block w-full border-gray-300 rounded-md px-3 py-2" placeholder="Escribe correo del usuario..." />
                    <!-- Hidden con el id real que llegará al servidor -->
                    <input type="hidden" id="id_usuario" name="id_usuario" />
                    <div id="usuario_suggestions" class="mt-1 bg-white border rounded shadow-sm max-h-48 overflow-auto hidden"></div>
                </div>

                <!-- Botón de Submit -->
                <!-- Módulos seleccionables (se mostrará al elegir un espacio) -->
                <div id="modulos-container" class="space-y-2 hidden">
                    <x-form.label for="modulos" :value="__('Cantidad de Módulos')" />
                    <input id="input-modulos" name="modulos" type="number" min="1" value="1" class="w-24 px-3 py-2 border rounded" />
                    <p class="text-sm text-gray-500">Módulo actual: <span id="modulo-actual-display">-</span></p>
                </div>

                <!-- Hidden para hora_salida calculada -->
                <input type="hidden" id="hora_salida" name="hora_salida" />
                <div class="flex justify-end pt-4">
                    <x-button id="btn-submit-reserva" variant="add" type="submit" class="gap-2">
                        <x-heroicon-o-user-add class="w-4 h-4 shrink-0" aria-hidden="true" />
                        {{ __('Agregar Reserva') }}
                    </x-button>
                </div>
            </div>
        </form>
    </x-modal>


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
                        <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-0.5">Docente</p>
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

    <script>
        window.ReservasIndexConfig = {
            horariosComplete: @json(\App\Helpers\ModulosHelper::getHorariosModulos()['lunes'] ?? []),
            autocompleteUrl: "{{ url('/api/usuarios/autocomplete') }}"
        };
    </script>
    @vite(['resources/js/views/reservas/index.js'])

</x-app-layout>
