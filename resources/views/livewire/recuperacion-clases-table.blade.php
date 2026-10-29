<div>
    <!-- Notificaciones Toast -->
    <div x-data="{ 
        show: false, 
        message: '', 
        type: 'success',
        init() {
            this.$wire.on('notify', (event) => {
                this.message = event[0].message;
                this.type = event[0].type;
                this.show = true;
                setTimeout(() => { this.show = false }, 5000);
            });
        }
    }" x-cloak>
        <div x-show="show" 
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 transform translate-y-2"
             x-transition:enter-end="opacity-100 transform translate-y-0"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed z-50 top-4 right-4">
            <div :class="{
                'bg-green-100 border-green-500 text-green-700': type === 'success',
                'bg-red-100 border-red-500 text-red-700': type === 'error',
                'bg-blue-100 border-blue-500 text-blue-700': type === 'info'
            }" class="px-4 py-3 border-l-4 rounded shadow-lg" role="alert">
                <div class="flex items-center">
                    <i :class="{
                        'fa-check-circle': type === 'success',
                        'fa-exclamation-circle': type === 'error',
                        'fa-info-circle': type === 'info'
                    }" class="mr-2 fa-solid"></i>
                    <p x-text="message"></p>
                    <button @click="show = false" class="ml-4">
                        <i class="fa-solid fa-times"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Indicador de carga global -->
    <div wire:loading class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900 bg-opacity-50">
        <div class="px-6 py-4 text-white bg-gray-800 rounded-lg shadow-xl">
            <div class="flex items-center">
                <svg class="w-5 h-5 mr-3 -ml-1 text-white animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <span>Procesando...</span>
            </div>
        </div>
    </div>

    <!-- Card Filtros -->
    <div class="bg-white shadow-sm rounded-xl border border-gray-200 p-4 mb-6 dark:bg-gray-800 dark:border-gray-700">
        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <div class="flex flex-col gap-2 md:flex-row md:items-center md:gap-4">
                <!-- Búsqueda -->
                <div class="relative">
                    <input type="text" wire:model.live="search" placeholder="Buscar..."
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg md:w-64 text-sm focus:ring-2 focus:ring-[#950101] focus:border-[#950101] dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                    <i class="absolute text-gray-400 transform -translate-y-1/2 fa-solid fa-search right-3 top-1/2"></i>
                </div>

                <!-- Filtro de estado -->
                <select wire:model.live="estado"
                    class="px-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-[#950101] focus:border-[#950101] dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                    <option value="">Todos los estados</option>
                    <option value="pendiente">Pendientes</option>
                    <option value="reagendada">Reagendadas</option>
                    <option value="obviada">Obviadas</option>
                    <option value="realizada">Realizadas</option>
                </select>
            </div>

            <!-- Botones de navegación -->
            <div class="flex flex-col gap-2 md:flex-row md:items-center md:gap-3">
                <a href="{{ route('reportes.auditoria-clases.index', ['tab' => 'reagendamientos']) }}"
                    class="inline-flex items-center px-4 py-2 text-sm font-medium text-white transition-colors rounded-lg bg-slate-800 hover:bg-slate-900 shadow-xs">
                    <i class="mr-2 fa-solid fa-clipboard-user"></i>Auditorías
                </a>
                <a href="{{ route('ausencias-profesores') }}"
                    class="inline-flex items-center px-4 py-2 text-sm font-medium text-white transition-colors rounded-lg bg-[#D12420] hover:bg-[#950101] shadow-xs">
                    <i class="mr-2 fa-solid fa-user-clock"></i>Ausencias de Profesores
                </a>
                <a href="{{ route('clases-no-realizadas.index') }}"
                    class="inline-flex items-center px-4 py-2 text-sm font-medium text-white transition-colors bg-amber-600 hover:bg-amber-700 rounded-lg shadow-xs">
                    <i class="mr-2 fa-solid fa-exclamation-triangle"></i>Control de Clases
                </a>
            </div>
        </div>
    </div>

    <!-- Card Tabla -->
    <div class="bg-white shadow-sm rounded-xl border border-gray-200 overflow-hidden dark:bg-gray-800 dark:border-gray-700">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 text-sm">
                <thead class="bg-[#950101] text-white border-b border-[#7B0101]">
                    <tr>
                        <th scope="col" class="px-4 py-3.5 text-xs font-semibold text-white uppercase tracking-wider text-center cursor-pointer select-none hover:text-red-100" wire:click="sortBy('fecha_clase_original')">
                            <div class="flex items-center gap-1.5 justify-center">
                                <span>Fecha Original</span>
                                @if ($sortField === 'fecha_clase_original')
                                    <i class="fa-solid fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} text-white text-xs"></i>
                                @else
                                    <i class="fa-solid fa-sort text-xs text-white/50"></i>
                                @endif
                            </div>
                        </th>
                        <th scope="col" class="px-4 py-3.5 text-xs font-semibold text-white uppercase tracking-wider text-center">Profesor</th>
                        <th scope="col" class="px-4 py-3.5 text-xs font-semibold text-white uppercase tracking-wider text-center">Asignatura</th>
                        <th scope="col" class="px-4 py-3.5 text-xs font-semibold text-white uppercase tracking-wider text-center">Módulo</th>
                        <th scope="col" class="px-4 py-3.5 text-xs font-semibold text-white uppercase tracking-wider text-center">Licencia</th>
                        <th scope="col" class="px-4 py-3.5 text-xs font-semibold text-white uppercase tracking-wider text-center">Nueva Fecha</th>
                        <th scope="col" class="px-4 py-3.5 text-xs font-semibold text-white uppercase tracking-wider text-center">Estado</th>
                        <th scope="col" class="px-4 py-3.5 text-xs font-semibold text-white uppercase tracking-wider text-center min-w-[260px]">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700 bg-white dark:bg-gray-800">
                    @forelse ($recuperaciones as $recuperacion)
                        <tr wire:key="recuperacion-row-{{ $recuperacion->id_recuperacion }}" class="hover:bg-slate-50/80 dark:hover:bg-gray-700/50 transition-colors">
                            <td class="px-4 py-3 text-sm font-medium text-gray-900 dark:text-gray-100 text-center align-middle whitespace-nowrap">
                                {{ $recuperacion->fecha_clase_original->format('d/m/Y') }}
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-800 dark:text-gray-200 text-center align-middle">
                                <div class="font-medium">{{ $recuperacion->profesor->name }}</div>
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300 text-center align-middle">
                                {{ $recuperacion->asignatura->nombre_asignatura ?? 'N/A' }}
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300 text-center align-middle">
                                @if ($recuperacion->moduloOriginal)
                                    <div class="font-medium">{{ $recuperacion->moduloOriginal->nombre_modulo }}</div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400">{{ ucfirst($recuperacion->moduloOriginal->dia) }}</div>
                                @else
                                    <span class="text-gray-400">N/A</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-sm text-center align-middle">
                                @if ($recuperacion->licencia)
                                    <div class="text-xs">
                                        <div class="font-medium text-gray-700 dark:text-gray-300">
                                            {{ $recuperacion->licencia->motivo ?? 'Licencia' }}
                                        </div>
                                        <div class="text-gray-500 dark:text-gray-400">
                                            {{ $recuperacion->licencia->fecha_inicio->format('d/m/Y') }} - 
                                            {{ $recuperacion->licencia->fecha_fin->format('d/m/Y') }}
                                        </div>
                                    </div>
                                @else
                                    <span class="text-gray-400 text-xs">Sin licencia</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-sm text-center align-middle whitespace-nowrap">
                                @if ($recuperacion->fecha_reagendada)
                                    <div class="font-medium text-emerald-600 dark:text-emerald-400">
                                        {{ $recuperacion->fecha_reagendada->format('d/m/Y') }}
                                    </div>
                                    @if ($recuperacion->moduloReagendado)
                                        <div class="text-xs text-gray-500 dark:text-gray-400">
                                            {{ $recuperacion->moduloReagendado->nombre_modulo }}
                                        </div>
                                    @endif
                                @else
                                    <span class="text-gray-400 text-xs">Sin reagendar</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-sm text-center align-middle whitespace-nowrap">
                                @php
                                    $badgeColors = [
                                        'pendiente' => 'bg-yellow-100 text-yellow-800',
                                        'reagendada' => 'bg-blue-100 text-blue-800',
                                        'obviada' => 'bg-gray-100 text-gray-800',
                                        'realizada' => 'bg-green-100 text-green-800',
                                    ];
                                @endphp
                                <span class="inline-flex px-2 py-0.5 text-xs font-semibold rounded {{ $badgeColors[$recuperacion->estado] ?? 'bg-gray-100 text-gray-800' }}">
                                    {{ ucfirst($recuperacion->estado) }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-center align-middle whitespace-nowrap">
                                <div class="flex items-center justify-center gap-1.5 flex-wrap">
                                    <!-- Notificar -->
                                    @if (!$recuperacion->notificado)
                                        <button wire:click="notificar({{ $recuperacion->id_recuperacion }})"
                                            wire:loading.attr="disabled"
                                            wire:target="notificar({{ $recuperacion->id_recuperacion }})"
                                            class="inline-flex items-center gap-1.5 px-2.5 py-1 text-xs font-semibold text-blue-700 bg-blue-50 hover:bg-blue-600 hover:text-white rounded-lg border border-blue-200 transition-colors shadow-xs disabled:opacity-50 disabled:cursor-not-allowed"
                                            title="Notificar por correo">
                                            <span wire:loading.remove wire:target="notificar({{ $recuperacion->id_recuperacion }})">
                                                <i class="fa-solid fa-envelope text-xs"></i>
                                            </span>
                                            <span wire:loading wire:target="notificar({{ $recuperacion->id_recuperacion }})">
                                                <i class="fa-solid fa-spinner fa-spin text-xs"></i>
                                            </span>
                                            <span>Notificar</span>
                                        </button>
                                    @else
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 text-xs font-semibold text-emerald-700 bg-emerald-50 rounded-lg border border-emerald-200" title="Ya notificado">
                                            <i class="fa-solid fa-check-circle text-xs"></i>
                                            <span>Notificado</span>
                                        </span>
                                    @endif

                                    <!-- Reagendar -->
                                    @if (in_array($recuperacion->estado, ['pendiente', 'reagendada']))
                                        <button wire:click="openReagendarModal({{ $recuperacion->id_recuperacion }})"
                                            class="inline-flex items-center gap-1.5 px-2.5 py-1 text-xs font-semibold text-amber-700 bg-amber-50 hover:bg-amber-600 hover:text-white rounded-lg border border-amber-200 transition-colors shadow-xs"
                                            title="Reagendar clase">
                                            <i class="fa-solid fa-calendar-plus text-xs"></i>
                                            <span>Reagendar</span>
                                        </button>
                                    @endif

                                    <!-- Obviar -->
                                    @if ($recuperacion->estado === 'pendiente')
                                        <button wire:click="obviar({{ $recuperacion->id_recuperacion }})"
                                            onclick="return confirm('¿Seguro que deseas obviar esta recuperación?')"
                                            class="inline-flex items-center gap-1.5 px-2.5 py-1 text-xs font-semibold text-gray-700 bg-gray-50 hover:bg-gray-200 rounded-lg border border-gray-300 transition-colors shadow-xs"
                                            title="Obviar reagendamiento">
                                            <i class="fa-solid fa-ban text-xs"></i>
                                            <span>Obviar</span>
                                        </button>
                                    @endif

                                    <!-- Marcar realizada -->
                                    @if ($recuperacion->estado === 'reagendada')
                                        <button wire:click="marcarRealizada({{ $recuperacion->id_recuperacion }})"
                                            class="inline-flex items-center gap-1.5 px-2.5 py-1 text-xs font-semibold text-emerald-700 bg-emerald-50 hover:bg-emerald-600 hover:text-white rounded-lg border border-emerald-200 transition-colors shadow-xs"
                                            title="Marcar como realizada">
                                            <i class="fa-solid fa-check text-xs"></i>
                                            <span>Realizada</span>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-8">
                                <div class="flex flex-col items-center gap-4">
                                    <p class="text-center text-gray-500">No se encontraron clases para recuperar</p>
                                    <div class="flex gap-3">
                                        <a href="{{ route('ausencias-profesores') }}"
                                            class="inline-flex items-center px-4 py-2 text-sm font-medium text-white transition-colors rounded-lg bg-[#D12420] hover:bg-[#950101] shadow-xs">
                                            <i class="mr-2 fa-solid fa-user-clock"></i>Ir a Ausencia de Profesores
                                        </a>
                                        <a href="{{ route('clases-no-realizadas.index') }}"
                                            class="inline-flex items-center px-4 py-2 text-sm font-medium text-white transition-colors bg-amber-600 hover:bg-amber-700 rounded-lg shadow-xs">
                                            <i class="mr-2 fa-solid fa-exclamation-triangle"></i>Ir a Control de Clases
                                        </a>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Paginación -->
        @if($recuperaciones->hasPages())
            <div class="px-6 py-4 bg-gray-50/70 border-t border-gray-200 dark:bg-gray-900/40 dark:border-gray-700">
                {{ $recuperaciones->links() }}
            </div>
        @endif
    </div>

    <!-- Modal Reagendar -->
    @if ($showReagendarModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto bg-gray-900 bg-opacity-50">
            <div class="relative w-full max-w-2xl p-6 mx-4 bg-white rounded-xl shadow-xl dark:bg-gray-800">
                <div class="flex items-center justify-between pb-4 border-b dark:border-gray-700">
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white">Reagendar Clase</h3>
                    <button wire:click="closeReagendarModal" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 text-xl font-bold">
                        &times;
                    </button>
                </div>

                <form wire:submit.prevent="reagendar">
                    <div class="py-4 grid gap-4 md:grid-cols-2">
                        <!-- Nueva Fecha -->
                        <div>
                            <label class="block mb-2 text-sm font-medium text-gray-700 dark:text-gray-300">Nueva Fecha *</label>
                            <input type="date" wire:model.live="fecha_reagendada" 
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-[#950101] focus:border-[#950101] dark:bg-gray-700 dark:border-gray-600 dark:text-white" required>
                            @error('fecha_reagendada') <span class="text-sm text-red-600">{{ $message }}</span> @enderror
                        </div>

                        <!-- Nuevo Módulo -->
                        <div>
                            <label class="block mb-2 text-sm font-medium text-gray-700 dark:text-gray-300">Módulo *</label>
                            <select wire:model="id_modulo_reagendado" 
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-[#950101] focus:border-[#950101] dark:bg-gray-700 dark:border-gray-600 dark:text-white" 
                                required {{ empty($fecha_reagendada) ? 'disabled' : '' }}>
                                <option value="">{{ empty($fecha_reagendada) ? 'Seleccione fecha primero' : 'Seleccione módulo' }}</option>
                                @foreach ($modulos as $modulo)
                                    <option value="{{ $modulo->id_modulo }}">
                                        {{ $modulo->nombre_modulo }}
                                    </option>
                                @endforeach
                            </select>
                            @error('id_modulo_reagendado') <span class="text-sm text-red-600">{{ $message }}</span> @enderror
                        </div>

                        <!-- Nuevo Espacio (Opcional) -->
                        <div class="md:col-span-2">
                            <label class="block mb-2 text-sm font-medium text-gray-700 dark:text-gray-300">Espacio (Opcional)</label>
                            <select wire:model="id_espacio_reagendado" 
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-[#950101] focus:border-[#950101] dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                <option value="">Mantener espacio original</option>
                                @foreach ($espacios as $espacio)
                                    <option value="{{ $espacio->id_espacio }}">
                                        {{ $espacio->nombre_espacio }}
                                    </option>
                                @endforeach
                            </select>
                            @error('id_espacio_reagendado') <span class="text-sm text-red-600">{{ $message }}</span> @enderror
                        </div>

                        <!-- Notas -->
                        <div class="md:col-span-2">
                            <label class="block mb-2 text-sm font-medium text-gray-700 dark:text-gray-300">Notas</label>
                            <textarea wire:model="notas" rows="3" 
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-[#950101] focus:border-[#950101] dark:bg-gray-700 dark:border-gray-600 dark:text-white" 
                                placeholder="Observaciones sobre el reagendamiento..."></textarea>
                            @error('notas') <span class="text-sm text-red-600">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="flex justify-end gap-2 pt-4 border-t dark:border-gray-700">
                        <x-button type="button" wire:click="closeReagendarModal" variant="cancel">
                            Cancelar
                        </x-button>
                        <x-button type="submit" variant="save">
                            Reagendar
                        </x-button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
