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

    <!-- Barra de herramientas -->
    <div class="p-4 bg-gray-50">
        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <div class="flex flex-col gap-2 md:flex-row md:items-center md:gap-4">
                <!-- Búsqueda -->
                <div class="relative">
                    <input type="text" wire:model.live="search" placeholder="Buscar..."
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg md:w-64 focus:ring-2 focus:ring-light-cloud-blue focus:border-transparent">
                    <i class="absolute text-gray-400 transform -translate-y-1/2 fa-solid fa-search right-3 top-1/2"></i>
                </div>

                <!-- Filtro de estado -->
                <select wire:model.live="estado"
                    class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-light-cloud-blue focus:border-transparent">
                    <option value="">Todos los estados</option>
                    <option value="pendiente">Pendientes</option>
                    <option value="reagendada">Reagendadas</option>
                    <option value="obviada">Obviadas</option>
                    <option value="realizada">Realizadas</option>
                </select>
            </div>

            <!-- Botones de navegación -->
            <div class="flex flex-col gap-2 md:flex-row md:items-center md:gap-3">
                <a href="{{ route('ausencias-profesores') }}"
                    class="px-4 py-2 text-white transition-colors rounded bg-light-cloud-blue hover:bg-cloud-blue">
                    <i class="mr-2 fa-solid fa-user-clock"></i>Ausencias de Profesores
                </a>
                <a href="{{ route('clases-no-registradas') }}"
                    class="px-4 py-2 text-white transition-colors bg-orange-500 rounded hover:bg-orange-600">
                    <i class="mr-2 fa-solid fa-exclamation-triangle"></i>Clases No Registradas
                </a>
            </div>
        </div>
    </div>

    <!-- Tabla -->
    <div class="overflow-x-auto">
        <table class="w-full text-sm text-left">
            <thead class="text-xs text-white uppercase bg-light-cloud-blue">
                <tr>
                    <th class="px-4 py-3 cursor-pointer" wire:click="sortBy('fecha_clase_original')">
                        Fecha Original
                        @if ($sortField === 'fecha_clase_original')
                            <i class="fa-solid fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>
                        @endif
                    </th>
                    <th class="px-4 py-3">Profesor</th>
                    <th class="px-4 py-3">Asignatura</th>
                    <th class="px-4 py-3">Módulo</th>
                    <th class="px-4 py-3">Licencia</th>
                    <th class="px-4 py-3">Nueva Fecha</th>
                    <th class="px-4 py-3">Estado</th>
                    <th class="px-4 py-3 text-center">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($recuperaciones as $recuperacion)
                    <tr class="bg-white border-b hover:bg-gray-50">
                        <td class="px-4 py-4 font-medium">
                            {{ $recuperacion->fecha_clase_original->format('d/m/Y') }}
                        </td>
                        <td class="px-4 py-4">
                            <div class="font-medium">{{ $recuperacion->profesor->name }}</div>
                            <div class="text-xs text-gray-500">{{ $recuperacion->run_profesor }}</div>
                        </td>
                        <td class="px-4 py-4">
                            {{ $recuperacion->asignatura->nombre_asignatura ?? 'N/A' }}
                        </td>
                        <td class="px-4 py-4">
                            @if ($recuperacion->moduloOriginal)
                                <div class="font-medium">{{ $recuperacion->moduloOriginal->nombre_modulo }}</div>
                                <div class="text-xs text-gray-500">{{ ucfirst($recuperacion->moduloOriginal->dia) }}</div>
                            @else
                                <span class="text-gray-400">N/A</span>
                            @endif
                        </td>
                        <td class="px-4 py-4">
                            @if ($recuperacion->licencia)
                                <div class="text-xs">
                                    <div class="font-medium text-gray-700">
                                        {{ $recuperacion->licencia->motivo ?? 'Licencia' }}
                                    </div>
                                    <div class="text-gray-500">
                                        {{ $recuperacion->licencia->fecha_inicio->format('d/m/Y') }} - 
                                        {{ $recuperacion->licencia->fecha_fin->format('d/m/Y') }}
                                    </div>
                                </div>
                            @else
                                <span class="text-gray-400 text-xs">Sin licencia</span>
                            @endif
                        </td>
                        <td class="px-4 py-4">
                            @if ($recuperacion->fecha_reagendada)
                                <div class="font-medium text-green-600">
                                    {{ $recuperacion->fecha_reagendada->format('d/m/Y') }}
                                </div>
                                @if ($recuperacion->moduloReagendado)
                                    <div class="text-xs text-gray-500">
                                        {{ $recuperacion->moduloReagendado->nombre_modulo }}
                                    </div>
                                @endif
                            @else
                                <span class="text-gray-400">Sin reagendar</span>
                            @endif
                        </td>
                        <td class="px-4 py-4">
                            @php
                                $badgeColors = [
                                    'pendiente' => 'bg-yellow-100 text-yellow-800',
                                    'reagendada' => 'bg-blue-100 text-blue-800',
                                    'obviada' => 'bg-gray-100 text-gray-800',
                                    'realizada' => 'bg-green-100 text-green-800',
                                ];
                            @endphp
                            <span class="px-2 py-1 text-xs font-semibold rounded {{ $badgeColors[$recuperacion->estado] ?? 'bg-gray-100 text-gray-800' }}">
                                {{ ucfirst($recuperacion->estado) }}
                            </span>
                        </td>
                        <td class="px-4 py-4">
                            <div class="flex items-center justify-center gap-1">
                                <!-- Notificar -->
                                @if (!$recuperacion->notificado)
                                    <button wire:click="notificar({{ $recuperacion->id_recuperacion }})"
                                        wire:loading.attr="disabled"
                                        wire:target="notificar({{ $recuperacion->id_recuperacion }})"
                                        class="px-2 py-1 text-white transition-colors bg-blue-500 rounded hover:bg-blue-600 disabled:opacity-50 disabled:cursor-not-allowed"
                                        title="Notificar por correo">
                                        <span wire:loading.remove wire:target="notificar({{ $recuperacion->id_recuperacion }})">
                                            <i class="fa-solid fa-envelope"></i>
                                        </span>
                                        <span wire:loading wire:target="notificar({{ $recuperacion->id_recuperacion }})">
                                            <i class="fa-solid fa-spinner fa-spin"></i>
                                        </span>
                                    </button>
                                @else
                                    <span class="px-2 py-1 text-xs text-green-600" title="Ya notificado">
                                        <i class="fa-solid fa-check-circle"></i>
                                    </span>
                                @endif

                                <!-- Reagendar -->
                                @if (in_array($recuperacion->estado, ['pendiente', 'reagendada']))
                                    <button wire:click="openReagendarModal({{ $recuperacion->id_recuperacion }})"
                                        class="px-2 py-1 text-white transition-colors rounded bg-light-cloud-blue hover:bg-cloud-blue"
                                        title="Reagendar clase">
                                        <i class="fa-solid fa-calendar-plus"></i>
                                    </button>
                                @endif

                                <!-- Obviar -->
                                @if ($recuperacion->estado === 'pendiente')
                                    <button wire:click="obviar({{ $recuperacion->id_recuperacion }})"
                                        onclick="return confirm('¿Seguro que deseas obviar esta recuperación?')"
                                        class="px-2 py-1 text-white transition-colors bg-gray-500 rounded hover:bg-gray-600"
                                        title="Obviar reagendamiento">
                                        <i class="fa-solid fa-ban"></i>
                                    </button>
                                @endif

                                <!-- Marcar realizada -->
                                @if ($recuperacion->estado === 'reagendada')
                                    <button wire:click="marcarRealizada({{ $recuperacion->id_recuperacion }})"
                                        class="px-2 py-1 text-white transition-colors bg-green-500 rounded hover:bg-green-600"
                                        title="Marcar como realizada">
                                        <i class="fa-solid fa-check"></i>
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
                                        class="px-4 py-2 text-white transition-colors rounded bg-light-cloud-blue hover:bg-cloud-blue">
                                        <i class="mr-2 fa-solid fa-user-clock"></i>Ir a Ausencia de Profesores
                                    </a>
                                    <a href="{{ route('clases-no-registradas') }}"
                                        class="px-4 py-2 text-white transition-colors bg-orange-500 rounded hover:bg-orange-600">
                                        <i class="mr-2 fa-solid fa-exclamation-triangle"></i>Ir a Clases No Registradas
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
    <div class="p-4">
        {{ $recuperaciones->links() }}
    </div>

    <!-- Modal Reagendar -->
    @if ($showReagendarModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto bg-gray-900 bg-opacity-50">
            <div class="relative w-full max-w-2xl p-6 mx-4 bg-white rounded-lg shadow-xl">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-xl font-bold">Reagendar Clase</h3>
                    <button wire:click="closeReagendarModal" class="text-gray-400 hover:text-gray-600">
                        <i class="fa-solid fa-times"></i>
                    </button>
                </div>

                <form wire:submit.prevent="reagendar">
                    <div class="grid gap-4 md:grid-cols-2">
                        <!-- Nueva Fecha -->
                        <div>
                            <label class="block mb-2 text-sm font-medium text-gray-700">Nueva Fecha *</label>
                            <input type="date" wire:model="fecha_reagendada" 
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg" required>
                            @error('fecha_reagendada') <span class="text-sm text-red-600">{{ $message }}</span> @enderror
                        </div>

                        <!-- Nuevo Módulo -->
                        <div>
                            <label class="block mb-2 text-sm font-medium text-gray-700">Módulo *</label>
                            <select wire:model="id_modulo_reagendado" 
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg" required>
                                <option value="">Seleccione módulo</option>
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
                            <label class="block mb-2 text-sm font-medium text-gray-700">Espacio (Opcional)</label>
                            <select wire:model="id_espacio_reagendado" 
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg">
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
                            <label class="block mb-2 text-sm font-medium text-gray-700">Notas</label>
                            <textarea wire:model="notas" rows="3" 
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg" 
                                placeholder="Observaciones sobre el reagendamiento..."></textarea>
                            @error('notas') <span class="text-sm text-red-600">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="flex justify-end gap-3 mt-6">
                        <button type="button" wire:click="closeReagendarModal"
                            class="px-4 py-2 text-gray-700 transition-colors bg-gray-200 rounded-lg hover:bg-gray-300">
                            Cancelar
                        </button>
                        <button type="submit"
                            class="px-4 py-2 text-white transition-colors rounded-lg bg-light-cloud-blue hover:bg-cloud-blue">
                            Reagendar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
