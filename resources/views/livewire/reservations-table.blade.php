<div>
    <!-- Título Solicitudes y Reservas -->
    <div class="mb-4">
        <span class="inline-flex items-center gap-3 px-5 py-2.5 bg-white border border-gray-200 rounded-xl shadow-sm text-base sm:text-lg font-bold text-gray-900">
            <i class="fas fa-calendar-check text-blue-600 text-xl"></i>
            <span>Solicitudes y Reservas</span>
        </span>
    </div>

    <!-- Navpills / Estadísticas idénticas a Control de Clases -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <!-- Total reservas -->
        <div class="stat-card bg-blue-50 border border-blue-200 rounded-xl p-4 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-blue-700 text-xs font-semibold uppercase tracking-wider">Total Reservas</p>
                    <p class="text-3xl font-black text-blue-950 mt-1">{{ number_format($kpis['total'] ?? 0) }}</p>
                </div>
                <div class="p-3 bg-blue-100 rounded-lg text-blue-600">
                    <i class="fas fa-calendar-check text-xl"></i>
                </div>
            </div>
        </div>

        <!-- Auditorio -->
        <div class="stat-card bg-indigo-50 border border-indigo-200 rounded-xl p-4 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-indigo-700 text-xs font-semibold uppercase tracking-wider">Auditorio</p>
                    <p class="text-3xl font-black text-indigo-950 mt-1">{{ number_format($kpis['auditorio'] ?? 0) }}</p>
                </div>
                <div class="p-3 bg-indigo-100 rounded-lg text-indigo-600">
                    <i class="fas fa-landmark text-xl"></i>
                </div>
            </div>
        </div>

        <!-- Salas de Estudio -->
        <div class="stat-card bg-sky-50 border border-sky-200 rounded-xl p-4 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sky-700 text-xs font-semibold uppercase tracking-wider">Salas de Estudio</p>
                    <p class="text-3xl font-black text-sky-950 mt-1">{{ number_format($kpis['salas_estudio'] ?? 0) }}</p>
                </div>
                <div class="p-3 bg-sky-100 rounded-lg text-sky-600">
                    <i class="fas fa-book-reader text-xl"></i>
                </div>
            </div>
        </div>

        <!-- Laboratorios -->
        <div class="stat-card bg-amber-50 border border-amber-200 rounded-xl p-4 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-amber-700 text-xs font-semibold uppercase tracking-wider">Laboratorios</p>
                    <p class="text-3xl font-black text-amber-950 mt-1">{{ number_format($kpis['laboratorios'] ?? 0) }}</p>
                </div>
                <div class="p-3 bg-amber-100 rounded-lg text-amber-600">
                    <i class="fas fa-flask text-xl"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Sección FILTROS idéntica a Control de Clases -->
    <div class="bg-white p-5 rounded-xl border border-gray-200 shadow-sm mb-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-sm font-bold text-gray-700 uppercase tracking-wider flex items-center gap-2">
                <i class="fas fa-filter text-blue-600"></i> FILTROS
            </h3>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
            {{-- Tipo de Espacio (sin emojis) --}}
            <div>
                <label for="tipo_espacio_filtro" class="block text-xs font-semibold text-gray-700 uppercase mb-1">Tipo de Espacio</label>
                <select id="tipo_espacio_filtro" wire:model.live="tipoEspacio"
                        class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white">
                    <option value="">Todos los espacios</option>
                    <option value="Auditorio">Auditorio</option>
                    <option value="Sala de Estudio">Salas de Estudio</option>
                    <option value="Laboratorios">Laboratorios (Todos)</option>
                    <option value="Sala de Clases">Salas de Clases</option>
                    <option value="Sala de Reuniones">Salas de Reuniones</option>
                </select>
            </div>

            {{-- Buscar --}}
            <div>
                <label for="search_input" class="block text-xs font-semibold text-gray-700 uppercase mb-1">Buscar</label>
                <div class="relative">
                    <input type="text"
                           id="search_input"
                           wire:model.live.debounce.400ms="search"
                           placeholder="Profesor, solicitante, RUN o espacio..."
                           class="w-full pl-9 pr-3 py-2 text-sm border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500" />
                    <i class="fas fa-search absolute left-3 top-2.5 text-gray-400 text-xs"></i>
                </div>
            </div>

            {{-- Estado --}}
            <div>
                <label for="estado_filtro" class="block text-xs font-semibold text-gray-700 uppercase mb-1">Estado</label>
                <select id="estado_filtro" wire:model.live="estado"
                        class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white">
                    <option value="">Todos</option>
                    <option value="activa">Activa</option>
                    <option value="finalizada">Finalizada</option>
                    <option value="programada">Programada</option>
                    <option value="cancelada">Cancelada</option>
                </select>
            </div>

            {{-- Desde --}}
            <div>
                <label for="fecha_inicio_filtro" class="block text-xs font-semibold text-gray-700 uppercase mb-1">Desde</label>
                <input type="date"
                       id="fecha_inicio_filtro"
                       wire:model.live="fechaInicio"
                       class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            </div>

            {{-- Hasta --}}
            <div>
                <label for="fecha_fin_filtro" class="block text-xs font-semibold text-gray-700 uppercase mb-1">Hasta</label>
                <input type="date"
                       id="fecha_fin_filtro"
                       wire:model.live="fechaFin"
                       class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            </div>
        </div>

        <!-- Botones de Acción -->
        <div class="flex items-center justify-end gap-2 mt-4 pt-4 border-t border-gray-100">
            <button type="button" wire:click="limpiarFiltros" 
                    class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 border border-gray-300 rounded-lg transition-colors inline-flex items-center gap-2">
                <i class="fas fa-undo text-xs"></i> Limpiar
            </button>
        </div>
    </div>

    <!-- Botón Exportar al estilo Control de Clases -->
    <div class="flex justify-end mb-4">
        <a href="{{ route('reservas.export-excel', array_filter(['tipo_espacio' => $tipoEspacio, 'fecha_inicio' => $fechaInicio, 'fecha_fin' => $fechaFin, 'estado' => $estado, 'search' => $search])) }}"
           class="inline-flex items-center justify-center px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold rounded-lg shadow-sm hover:shadow transition-all duration-200 gap-2">
            <i class="fas fa-file-excel"></i>
            <span>Exportar todas las reservas</span>
        </a>
    </div>

    <div class="mt-2 mb-4">
        {{ $reservas->links('vendor.pagination.tailwind') }}
    </div>

    <div class="overflow-x-auto border border-gray-200 rounded-lg shadow-md dark:border-gray-700">
        <table class="w-full text-sm text-center border-collapse table-auto min-w-max">
            <thead class="text-white bg-light-cloud-blue dark:bg-black dark:text-white">
                <tr>
                    <th class="p-3 cursor-pointer select-none" wire:click="sortBy('id_reserva')">
                        ID Reserva
                        @if($sortField === 'id_reserva')
                            <span>{{ $sortDirection === 'asc' ? '▲' : '▼' }}</span>
                        @endif
                    </th>
                    <th class="p-3 cursor-pointer select-none" wire:click="sortBy('fecha_reserva')">
                        Fecha
                        @if($sortField === 'fecha_reserva')
                            <span>{{ $sortDirection === 'asc' ? '▲' : '▼' }}</span>
                        @endif
                    </th>
                    <th class="p-3">Horario</th>
                    <th class="p-3 cursor-pointer select-none" wire:click="sortBy('id_espacio')">
                        Espacio
                        @if($sortField === 'id_espacio')
                            <span>{{ $sortDirection === 'asc' ? '▲' : '▼' }}</span>
                        @endif
                    </th>
                    <th class="p-3">Usuario / Solicitante</th>
                    <th class="p-3">Estado</th>
                    <th class="p-3">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($reservas as $index => $reserva)
                    <tr wire:key="reserva-row-{{ $reserva->id_reserva }}" class="{{ $index % 2 === 0 ? 'bg-white' : 'bg-gray-50' }} hover:bg-blue-50 dark:hover:bg-gray-800">
                        <td class="p-3 font-semibold text-blue-600 border border-white dark:border-gray-700 dark:text-blue-400">
                            {{ $reserva->id_reserva }}
                        </td>
                        <td class="p-3 border border-white dark:border-gray-700 whitespace-nowrap">
                            {{ $reserva->fecha_reserva ? \Carbon\Carbon::parse($reserva->fecha_reserva)->format('d/m/Y') : '-' }}
                        </td>
                        <td class="p-3 border border-white dark:border-gray-700 whitespace-nowrap">
                            {{ substr($reserva->hora, 0, 5) }} - {{ substr($reserva->hora_salida, 0, 5) }}
                        </td>
                        <td class="p-3 border border-white dark:border-gray-700 whitespace-nowrap font-medium">
                            <div class="text-gray-900 dark:text-white font-semibold">{{ $reserva->espacio->nombre_espacio ?? $reserva->id_espacio }}</div>
                            @if($reserva->espacio && $reserva->espacio->tipo_espacio)
                                <span class="inline-block mt-0.5 px-2 py-0.5 text-[11px] font-medium rounded-md bg-slate-100 text-slate-700 dark:bg-slate-700 dark:text-slate-300">
                                    {{ $reserva->espacio->tipo_espacio }}
                                </span>
                            @endif
                        </td>
                        <td class="p-3 border border-white dark:border-gray-700 whitespace-nowrap">
                            <div class="flex flex-col items-center">
                                <span class="font-medium text-gray-800 dark:text-gray-200">{{ $reserva->nombre_usuario }}</span>
                                <span class="text-xs text-gray-500">{{ $reserva->run_profesor ?: $reserva->run_solicitante }}</span>
                            </div>
                        </td>
                        <td class="p-3 border border-white dark:border-gray-700 whitespace-nowrap">
                            @if($reserva->estado === 'activa')
                                <span class="px-2 py-1 text-xs font-semibold text-green-800 bg-green-100 rounded-full dark:bg-green-900 dark:text-green-200">Activa</span>
                            @elseif($reserva->estado === 'finalizada')
                                <span class="px-2 py-1 text-xs font-semibold text-gray-800 bg-gray-100 rounded-full dark:bg-gray-700 dark:text-gray-300">Finalizada</span>
                            @else
                                <span class="px-2 py-1 text-xs font-semibold text-yellow-800 bg-yellow-100 rounded-full dark:bg-yellow-900 dark:text-yellow-200">{{ ucfirst($reserva->estado) }}</span>
                            @endif
                        </td>
                        <td class="p-3 border border-white dark:border-gray-700 whitespace-nowrap">
                            <div class="flex flex-wrap justify-center gap-1.5">
                                <x-button variant="view" href="{{ route('reservas.edit', $reserva->id_reserva) }}"
                                    class="inline-flex items-center px-3 py-1.5">
                                    <x-icons.edit class="w-4 h-4" aria-hidden="true" />
                                </x-button>
                                <a href="{{ route('reservas.comprobante', $reserva->id_reserva) }}"
                                   target="_blank"
                                   title="Descargar Comprobante PDF"
                                   class="inline-flex items-center px-2.5 py-1.5 text-xs font-semibold text-blue-700 bg-blue-100 hover:bg-blue-200 rounded border border-blue-300 transition dark:bg-blue-900/40 dark:text-blue-300 dark:border-blue-700">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                    </svg>
                                    PDF
                                </a>
                                <form method="POST" action="{{ route('reservas.delete', $reserva->id_reserva) }}" class="reserva-delete-form inline-block">
                                    @csrf
                                    @method('DELETE')
                                    <x-button variant="danger" type="submit" class="px-3 py-1.5 text-white bg-red-500 rounded dark:bg-red-700 btn-delete-reserva" data-espacio="{{ $reserva->id_espacio }}">
                                        <x-icons.delete class="w-4 h-4" aria-hidden="true" />
                                    </x-button>
                                </form>

                                {{-- Botones Admin: solo para reservas de hoy que están programadas o activas --}}
                                @php
                                    $esHoy = $reserva->fecha_reserva && \Carbon\Carbon::parse($reserva->fecha_reserva)->isToday();
                                    $docente = $reserva->nombre_usuario;
                                    $sala = $reserva->id_espacio;
                                    $fecha = $reserva->fecha_reserva ? \Carbon\Carbon::parse($reserva->fecha_reserva)->format('d/m/Y') : '—';
                                    $horario = substr($reserva->hora, 0, 5) . ' - ' . substr($reserva->hora_salida ?? '', 0, 5);
                                @endphp

                                @if($esHoy && $reserva->estado === 'programada')
                                    <button type="button"
                                            onclick="abrirModalAdmin('entrada', '{{ $reserva->id_reserva }}', '{{ addslashes($docente) }}', '{{ addslashes($sala) }}', '{{ $fecha }}', '{{ $horario }}')"
                                            title="Registrar entrada administrativamente"
                                            class="inline-flex items-center gap-1 px-2.5 py-1.5 text-xs font-bold text-emerald-700 bg-emerald-100 hover:bg-emerald-200 rounded-lg border border-emerald-300 transition dark:bg-emerald-900/40 dark:text-emerald-300 dark:border-emerald-700">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/>
                                        </svg>
                                        Entrada
                                    </button>
                                @endif

                                @if($esHoy && $reserva->estado === 'activa')
                                    <button type="button"
                                            onclick="abrirModalAdmin('salida', '{{ $reserva->id_reserva }}', '{{ addslashes($docente) }}', '{{ addslashes($sala) }}', '{{ $fecha }}', '{{ $horario }}')"
                                            title="Registrar salida administrativamente"
                                            class="inline-flex items-center gap-1 px-2.5 py-1.5 text-xs font-bold text-rose-700 bg-rose-100 hover:bg-rose-200 rounded-lg border border-rose-300 transition dark:bg-rose-900/40 dark:text-rose-300 dark:border-rose-700">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                                        </svg>
                                        Salida
                                    </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr wire:key="empty-reservas-row">
                        <td colspan="7" class="p-8 text-center text-gray-500 dark:text-gray-400">
                            <div class="flex flex-col items-center justify-center">
                                <i class="fas fa-calendar-times text-3xl text-gray-300 dark:text-gray-600 mb-2"></i>
                                <p class="text-sm font-medium">No se encontraron reservas con los filtros seleccionados.</p>
                                @if(!empty($search) || !empty($tipoEspacio) || !empty($fechaInicio) || !empty($fechaFin) || !empty($estado))
                                    <button type="button" wire:click="limpiarFiltros" class="mt-2 text-xs text-blue-600 hover:text-blue-800 underline font-medium">
                                        Restablecer todos los filtros
                                    </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $reservas->links('vendor.pagination.tailwind') }}
    </div>
</div>

<script>
    // Interceptar clicks en botones de eliminar reservas para notificar otras pestañas
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('.btn-delete-reserva').forEach(btn => {
            btn.addEventListener('click', function (e) {
                e.preventDefault();
                const espacioId = this.getAttribute('data-espacio');
                const form = this.closest('form');
                if (!form) return;

                // Guardar en localStorage para notificar otras pestañas
                localStorage.setItem('reserva_eliminada', JSON.stringify({ id_espacio: espacioId, ts: Date.now() }));

                // Enviar el formulario para eliminar en esta pestaña
                form.submit();
            });
        });
    });
</script>
