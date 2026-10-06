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
                    <option value="">Todos (Auditorio, Salas de Estudio, Laboratorios)</option>
                    <option value="Auditorio">Auditorio</option>
                    <option value="Sala de Estudio">Salas de Estudio</option>
                    <option value="Laboratorios">Laboratorios</option>
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

    <!-- Card Tabla de Reservas Universal -->
    <div class="bg-white shadow-sm rounded-xl border border-gray-200 overflow-hidden dark:bg-gray-800 dark:border-gray-700">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 border-b border-gray-200 dark:bg-gray-900/60 dark:border-gray-700">
                    <tr>
                        <th scope="col" class="w-24 px-3 py-3.5 text-center text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase tracking-wider cursor-pointer select-none hover:text-gray-900" wire:click="sortBy('id_reserva')">
                            <span class="inline-flex items-center gap-1 justify-center">
                                ID Reserva
                                @if($sortField === 'id_reserva')
                                    <i class="fa-solid fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} text-blue-600 text-xs"></i>
                                @else
                                    <i class="fa-solid fa-sort text-xs text-gray-400"></i>
                                @endif
                            </span>
                        </th>
                        <th scope="col" class="w-32 px-3 py-3.5 text-center text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase tracking-wider cursor-pointer select-none hover:text-gray-900" wire:click="sortBy('fecha_reserva')">
                            <span class="inline-flex items-center gap-1 justify-center">
                                Fecha
                                @if($sortField === 'fecha_reserva')
                                    <i class="fa-solid fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} text-blue-600 text-xs"></i>
                                @else
                                    <i class="fa-solid fa-sort text-xs text-gray-400"></i>
                                @endif
                            </span>
                        </th>
                        <th scope="col" class="w-32 px-3 py-3.5 text-center text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase tracking-wider">
                            Horario
                        </th>
                        <th scope="col" class="w-36 px-3 py-3.5 text-center text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase tracking-wider cursor-pointer select-none hover:text-gray-900" wire:click="sortBy('id_espacio')">
                            <span class="inline-flex items-center gap-1 justify-center">
                                Espacio
                                @if($sortField === 'id_espacio')
                                    <i class="fa-solid fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} text-blue-600 text-xs"></i>
                                @else
                                    <i class="fa-solid fa-sort text-xs text-gray-400"></i>
                                @endif
                            </span>
                        </th>
                        <th scope="col" class="px-3 py-3.5 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase tracking-wider">
                            Usuario / Solicitante
                        </th>
                        <th scope="col" class="w-32 px-3 py-3.5 text-center text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase tracking-wider">
                            Estado
                        </th>
                        <th scope="col" class="w-44 px-3 py-3.5 text-center text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase tracking-wider">
                            Acciones
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200 dark:bg-gray-800 dark:divide-gray-700">
                    @forelse ($reservas as $reserva)
                        <tr wire:key="reserva-row-{{ $reserva->id_reserva }}" class="hover:bg-slate-50/80 dark:hover:bg-gray-700/50 transition-colors">
                            <td class="w-24 px-3 py-3 text-center align-middle whitespace-nowrap">
                                <span class="font-medium text-blue-600 dark:text-blue-400 text-sm">#{{ $reserva->id_reserva }}</span>
                            </td>
                            <td class="w-32 px-3 py-3 text-center align-middle whitespace-nowrap">
                                <div class="font-medium text-gray-800 dark:text-gray-200 text-sm">
                                    {{ $reserva->fecha_reserva ? \Carbon\Carbon::parse($reserva->fecha_reserva)->format('d/m/Y') : '-' }}
                                </div>
                                <div class="text-[11px] text-gray-500 font-normal">
                                    {{ $reserva->fecha_reserva ? ucfirst(\Carbon\Carbon::parse($reserva->fecha_reserva)->locale('es')->isoFormat('ddd')) : '' }}
                                </div>
                            </td>
                            <td class="w-32 px-3 py-3 text-center align-middle whitespace-nowrap">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-semibold bg-slate-100 text-slate-700 border border-slate-200 dark:bg-slate-700 dark:text-slate-300 dark:border-slate-600">
                                    {{ substr($reserva->hora, 0, 5) }} - {{ substr($reserva->hora_salida, 0, 5) }}
                                </span>
                            </td>
                            <td class="w-36 px-3 py-3 text-center align-middle whitespace-nowrap">
                                <div class="font-semibold text-gray-800 dark:text-white text-sm">
                                    {{ $reserva->espacio->nombre_espacio ?? $reserva->id_espacio }}
                                </div>
                                @if($reserva->espacio && $reserva->espacio->tipo_espacio)
                                    <span class="text-[11px] text-gray-500 truncate block">
                                        {{ $reserva->espacio->tipo_espacio }}
                                    </span>
                                @endif
                            </td>
                            <td class="px-3 py-3 text-left align-middle">
                                <div class="font-medium text-gray-800 dark:text-gray-200 text-sm truncate max-w-xs" title="{{ $reserva->nombre_usuario }}">
                                    {{ $reserva->nombre_usuario }}
                                </div>
                                <div class="text-xs text-gray-500">
                                    {{ $reserva->run_profesor ?: ($reserva->run_solicitante ?: 'N/A') }}
                                </div>
                            </td>
                            <td class="w-32 px-3 py-3 text-center align-middle whitespace-nowrap">
                                @if($reserva->estado === 'activa')
                                    <span class="px-2.5 py-0.5 inline-flex text-xs font-semibold rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200 dark:bg-emerald-900/40 dark:text-emerald-300 dark:border-emerald-800">
                                        Activa
                                    </span>
                                @elseif($reserva->estado === 'programada')
                                    <span class="px-2.5 py-0.5 inline-flex text-xs font-semibold rounded-full bg-blue-50 text-blue-700 border border-blue-200 dark:bg-blue-900/40 dark:text-blue-300 dark:border-blue-800">
                                        Programada
                                    </span>
                                @elseif($reserva->estado === 'finalizada')
                                    <span class="px-2.5 py-0.5 inline-flex text-xs font-semibold rounded-full bg-gray-100 text-gray-700 border border-gray-200 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-600">
                                        Finalizada
                                    </span>
                                @else
                                    <span class="px-2.5 py-0.5 inline-flex text-xs font-semibold rounded-full bg-amber-50 text-amber-700 border border-amber-200 dark:bg-amber-900/40 dark:text-amber-300 dark:border-amber-800">
                                        {{ ucfirst($reserva->estado) }}
                                    </span>
                                @endif
                            </td>
                            <td class="w-44 px-3 py-3 text-center align-middle whitespace-nowrap">
                                <div class="flex items-center justify-center gap-1">
                                    <a href="{{ route('reservas.edit', $reserva->id_reserva) }}"
                                       class="inline-flex items-center justify-center p-1.5 border border-blue-300 text-xs font-medium rounded-md text-blue-700 bg-blue-50 hover:bg-blue-100 transition-colors shadow-xs dark:bg-blue-900/40 dark:text-blue-300 dark:border-blue-700"
                                       title="Editar reserva">
                                        <i class="fa-solid fa-edit w-3.5 h-3.5"></i>
                                    </a>
                                    <a href="{{ route('reservas.comprobante', $reserva->id_reserva) }}"
                                       target="_blank"
                                       class="inline-flex items-center justify-center p-1.5 border border-blue-300 text-xs font-medium rounded-md text-blue-700 bg-blue-50 hover:bg-blue-100 transition-colors shadow-xs dark:bg-blue-900/40 dark:text-blue-300 dark:border-blue-700"
                                       title="Descargar Comprobante PDF">
                                        <i class="fa-solid fa-file-pdf w-3.5 h-3.5"></i>
                                    </a>
                                    <form method="POST" action="{{ route('reservas.delete', $reserva->id_reserva) }}" class="reserva-delete-form inline-block">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" title="Cancelar reserva"
                                                class="inline-flex items-center justify-center p-1.5 border border-red-300 text-xs font-medium rounded-md text-red-700 bg-red-50 hover:bg-red-100 transition-colors shadow-xs btn-delete-reserva dark:bg-red-900/40 dark:text-red-300 dark:border-red-700"
                                                data-espacio="{{ $reserva->id_espacio }}">
                                            <x-icons.delete class="w-3.5 h-3.5" aria-hidden="true" />
                                        </button>
                                    </form>

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
                                                class="inline-flex items-center gap-1 px-2 py-1 text-xs font-semibold text-emerald-700 bg-emerald-50 hover:bg-emerald-100 rounded-md border border-emerald-300 transition-colors shadow-xs dark:bg-emerald-900/40 dark:text-emerald-300 dark:border-emerald-700">
                                            <i class="fa-solid fa-arrow-right-to-bracket text-xs"></i>
                                            Entrada
                                        </button>
                                    @endif

                                    @if($esHoy && $reserva->estado === 'activa')
                                        <button type="button"
                                                onclick="abrirModalAdmin('salida', '{{ $reserva->id_reserva }}', '{{ addslashes($docente) }}', '{{ addslashes($sala) }}', '{{ $fecha }}', '{{ $horario }}')"
                                                title="Registrar salida administrativamente"
                                                class="inline-flex items-center gap-1 px-2 py-1 text-xs font-semibold text-rose-700 bg-rose-50 hover:bg-rose-100 rounded-md border border-rose-300 transition-colors shadow-xs dark:bg-rose-900/40 dark:text-rose-300 dark:border-rose-700">
                                            <i class="fa-solid fa-arrow-right-from-bracket text-xs"></i>
                                            Salida
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr wire:key="empty-reservas-row">
                            <td colspan="7" class="px-6 py-12 text-center text-gray-500 dark:text-gray-400">
                                <div class="flex flex-col items-center justify-center">
                                    <i class="fa-solid fa-calendar-xmark text-5xl text-gray-300 dark:text-gray-600 mb-3"></i>
                                    <p class="text-base font-medium text-gray-700 dark:text-gray-300">No se encontraron reservas con los filtros seleccionados.</p>
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

        {{-- Footer de paginación integrado --}}
        <div class="px-6 py-4 bg-gray-50/70 border-t border-gray-200 dark:bg-gray-900/40 dark:border-gray-700">
            {{ $reservas->links('vendor.pagination.tailwind') }}
        </div>
    </div>
</div>

<script>
    // Interceptar clicks en botones de eliminar reservas para confirmación SweetAlert y notificar otras pestañas
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('.btn-delete-reserva').forEach(btn => {
            btn.addEventListener('click', function (e) {
                e.preventDefault();
                const espacioId = this.getAttribute('data-espacio');
                const form = this.closest('form');
                if (!form) return;

                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        title: '¿Cancelar reserva?',
                        text: '¿Estás seguro de cancelar y eliminar definitivamente esta reserva? Esta acción liberará el espacio.',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#DC2626',
                        cancelButtonColor: '#6B7280',
                        confirmButtonText: 'Sí, cancelar reserva',
                        cancelButtonText: 'No, mantener'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            localStorage.setItem('reserva_eliminada', JSON.stringify({ id_espacio: espacioId, ts: Date.now() }));
                            localStorage.setItem('reserva_cambiada', Date.now());
                            form.submit();
                        }
                    });
                } else {
                    if (confirm('¿Estás seguro de cancelar esta reserva?')) {
                        localStorage.setItem('reserva_eliminada', JSON.stringify({ id_espacio: espacioId, ts: Date.now() }));
                        localStorage.setItem('reserva_cambiada', Date.now());
                        form.submit();
                    }
                }
            });
        });
    });
</script>
