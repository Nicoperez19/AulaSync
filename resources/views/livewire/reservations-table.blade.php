<div>
    <!-- Tarjetas principales de Ocupación y Reservas -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-4">
        <!-- 1. Total Reservas -->
        <div class="stat-card bg-blue-50 border border-blue-200 rounded-xl p-4 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-blue-700 text-xs font-semibold uppercase tracking-wider">Total Reservas</p>
                    <p class="text-3xl font-black text-blue-950 mt-1">{{ number_format($kpis['total'] ?? 0) }}</p>
                </div>
                <div class="p-3 bg-blue-100 rounded-lg text-[#950101]">
                    <i class="fas fa-calendar-check text-xl"></i>
                </div>
            </div>
        </div>

        <!-- 2. % de Ocupación (Externos + Docentes) -->
        <div class="stat-card bg-emerald-50 border border-emerald-200 rounded-xl p-4 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-emerald-800 text-xs font-semibold uppercase tracking-wider" title="Porcentaje de ocupación sobre las salas reservadas en el período evaluado">
                        % Ocupación Reservas
                    </p>
                    <p class="text-3xl font-black text-emerald-950 mt-1">{{ number_format($kpis['pct_ocupacion_salas_usadas'] ?? 0, 1) }}%</p>
                </div>
                <div class="p-3 bg-emerald-100 rounded-lg text-emerald-700 shrink-0">
                    <i class="fas fa-chart-pie text-xl"></i>
                </div>
            </div>
        </div>

        <!-- 3. Reservas Docentes -->
        <div class="stat-card bg-indigo-50 border border-indigo-200 rounded-xl p-4 shadow-sm cursor-pointer hover:bg-indigo-100/70 transition-colors"
             wire:click="$set('tipoSolicitante', '{{ $tipoSolicitante === 'docente' ? '' : 'docente' }}')"
             title="Clic para filtrar solo reservas de Docentes">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-indigo-700 text-xs font-semibold uppercase tracking-wider flex items-center gap-1.5">
                        <span>Reservas Docentes</span>
                        @if($tipoSolicitante === 'docente')
                            <span class="px-1.5 py-0.2 bg-indigo-600 text-white text-[10px] rounded">Filtro activo</span>
                        @endif
                    </p>
                    <p class="text-3xl font-black text-indigo-950 mt-1">{{ number_format($kpis['reservas_docentes'] ?? 0) }}</p>
                </div>
                <div class="p-3 bg-indigo-100 rounded-lg text-indigo-600">
                    <i class="fas fa-chalkboard-user text-xl"></i>
                </div>
            </div>
        </div>

        <!-- 4. Reservas Externos -->
        <div class="stat-card bg-amber-50 border border-amber-200 rounded-xl p-4 shadow-sm cursor-pointer hover:bg-amber-100/70 transition-colors"
             wire:click="$set('tipoSolicitante', '{{ $tipoSolicitante === 'externo' ? '' : 'externo' }}')"
             title="Clic para filtrar solo reservas de Solicitantes Externos">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-amber-800 text-xs font-semibold uppercase tracking-wider flex items-center gap-1.5">
                        <span>Reservas Externos</span>
                        @if($tipoSolicitante === 'externo')
                            <span class="px-1.5 py-0.2 bg-amber-600 text-white text-[10px] rounded">Filtro activo</span>
                        @endif
                    </p>
                    <p class="text-3xl font-black text-amber-950 mt-1">{{ number_format($kpis['reservas_externos'] ?? 0) }}</p>
                </div>
                <div class="p-3 bg-amber-100 rounded-lg text-amber-600">
                    <i class="fas fa-building-user text-xl"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Sección FILTROS -->
    <div class="bg-white p-5 rounded-xl border border-gray-200 shadow-sm mb-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-sm font-bold text-gray-700 uppercase tracking-wider flex items-center gap-2">
                <i class="fas fa-filter text-[#950101]"></i> FILTROS DEL REPORTE
            </h3>
            <span class="text-xs text-gray-500">
                Período evaluado: <strong>{{ $kpis['fecha_inicio_calculo'] ?? '-' }}</strong> al <strong>{{ $kpis['fecha_fin_calculo'] ?? '-' }}</strong>
                ({{ $kpis['dias_habiles'] ?? 1 }} días hábiles · {{ number_format($kpis['horas_disponibles_salas_usadas'] ?? 0, 0) }} hrs disp. en salas usadas)
            </span>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-4">
            {{-- Tipo de Solicitante --}}
            <div>
                <label for="tipo_solicitante_filtro" class="block text-xs font-semibold text-gray-700 uppercase mb-1">Tipo Solicitante</label>
                <select id="tipo_solicitante_filtro" wire:model.live="tipoSolicitante"
                        class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-[#D12420]/20 focus:border-[#D12420] bg-white">
                    <option value="">Docentes + Externos (Reporte Completo)</option>
                    <option value="externo">Solo Externos (Servicio Salud, Municipalidad, etc.)</option>
                    <option value="docente">Solo Docentes</option>
                    <option value="todos">Todos (incluye Estudiantes / Salas Estudio)</option>
                </select>
            </div>

            {{-- Tipo de Espacio --}}
            <div>
                <label for="tipo_espacio_filtro" class="block text-xs font-semibold text-gray-700 uppercase mb-1">Tipo de Espacio</label>
                <select id="tipo_espacio_filtro" wire:model.live="tipoEspacio"
                        class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-[#D12420]/20 focus:border-[#D12420] bg-white">
                    <option value="">Todos los espacios</option>
                    <option value="Sala de Clases">Salas de Clases</option>
                    <option value="Auditorio">Auditorio</option>
                    <option value="Laboratorios">Laboratorios</option>
                    <option value="Taller">Taller</option>
                    <option value="Sala de Estudio">Salas de Estudio</option>
                </select>
            </div>

            {{-- Buscar (incluye institución / actividad) --}}
            <div>
                <label for="search_input" class="block text-xs font-semibold text-gray-700 uppercase mb-1">Buscar / Institución</label>
                <div class="relative">
                    <input type="text"
                           id="search_input"
                           wire:model.live.debounce.400ms="search"
                           placeholder="Servicio de Salud, Municipalidad, docente, sala..."
                           class="w-full pl-9 pr-3 py-2 text-sm border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-[#D12420]/20 focus:border-[#D12420]" />
                    <i class="fas fa-search absolute left-3 top-2.5 text-gray-400 text-xs"></i>
                </div>
            </div>

            {{-- Estado --}}
            <div>
                <label for="estado_filtro" class="block text-xs font-semibold text-gray-700 uppercase mb-1">Estado</label>
                <select id="estado_filtro" wire:model.live="estado"
                        class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-[#D12420]/20 focus:border-[#D12420] bg-white">
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
                       class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-[#D12420]/20 focus:border-[#D12420]">
            </div>

            {{-- Hasta --}}
            <div>
                <label for="fecha_fin_filtro" class="block text-xs font-semibold text-gray-700 uppercase mb-1">Hasta</label>
                <input type="date"
                       id="fecha_fin_filtro"
                       wire:model.live="fechaFin"
                       class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-[#D12420]/20 focus:border-[#D12420]">
            </div>
        </div>

        <!-- Botones de Acción -->
        <div class="flex items-center justify-end gap-2 mt-4 pt-4 border-t border-gray-100">
            <button type="button" wire:click="limpiarFiltros" 
                    class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 border border-gray-300 rounded-lg transition-colors inline-flex items-center gap-2">
                <i class="fas fa-undo text-xs"></i> Limpiar
            </button>
            <button type="button" wire:click="aplicarFiltros" 
                    class="px-4 py-2 text-sm font-semibold text-white bg-[#191919] hover:bg-black active:bg-gray-900 rounded-lg shadow-sm transition-colors inline-flex items-center gap-2">
                <i class="fas fa-filter text-xs"></i> Aplicar filtros
            </button>
        </div>
    </div>

    <!-- Botón Exportar Reporte a Excel -->
    <div class="flex justify-end mb-4">
        <a href="{{ route('reservas.export-excel', array_filter(['tipo_espacio' => $tipoEspacio, 'tipo_solicitante' => $tipoSolicitante, 'fecha_inicio' => $fechaInicio, 'fecha_fin' => $fechaFin, 'estado' => $estado, 'search' => $search])) }}"
           class="btn btn-excel gap-2 px-4 py-2.5 text-sm font-semibold">
            <i class="fas fa-file-excel"></i>
            <span>Exportar reporte</span>
        </a>
    </div>

    <!-- Card Tabla de Reservas Universal -->
    <div class="bg-white shadow-sm rounded-xl border border-gray-200 overflow-hidden dark:bg-gray-800 dark:border-gray-700">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-[#950101] text-white border-b border-[#7B0101]">
                    <tr>
                        <th scope="col" class="w-24 px-3 py-3.5 text-center text-xs font-semibold text-white uppercase tracking-wider cursor-pointer select-none hover:text-red-100" wire:click="sortBy('id_reserva')">
                            <span class="inline-flex items-center gap-1 justify-center">
                                ID Reserva
                                @if($sortField === 'id_reserva')
                                    <i class="fa-solid fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} text-white text-xs"></i>
                                @else
                                    <i class="fa-solid fa-sort text-xs text-white/50"></i>
                                @endif
                            </span>
                        </th>
                        <th scope="col" class="w-28 px-3 py-3.5 text-center text-xs font-semibold text-white uppercase tracking-wider cursor-pointer select-none hover:text-red-100" wire:click="sortBy('fecha_reserva')">
                            <span class="inline-flex items-center gap-1 justify-center">
                                Fecha
                                @if($sortField === 'fecha_reserva')
                                    <i class="fa-solid fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} text-white text-xs"></i>
                                @else
                                    <i class="fa-solid fa-sort text-xs text-white/50"></i>
                                @endif
                            </span>
                        </th>
                        <th scope="col" class="w-32 px-3 py-3.5 text-center text-xs font-semibold text-white uppercase tracking-wider">
                            Horario / Duración
                        </th>
                        <th scope="col" class="w-36 px-3 py-3.5 text-center text-xs font-semibold text-white uppercase tracking-wider cursor-pointer select-none hover:text-red-100" wire:click="sortBy('id_espacio')">
                            <span class="inline-flex items-center gap-1 justify-center">
                                Espacio
                                @if($sortField === 'id_espacio')
                                    <i class="fa-solid fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} text-white text-xs"></i>
                                @else
                                    <i class="fa-solid fa-sort text-xs text-white/50"></i>
                                @endif
                            </span>
                        </th>
                        <th scope="col" class="px-3 py-3.5 text-left text-xs font-semibold text-white uppercase tracking-wider">
                            Usuario / Categoría
                        </th>
                        <th scope="col" class="px-3 py-3.5 text-left text-xs font-semibold text-white uppercase tracking-wider">
                            Institución / Actividad / Motivo
                        </th>
                        <th scope="col" class="w-28 px-3 py-3.5 text-center text-xs font-semibold text-white uppercase tracking-wider">
                            Estado
                        </th>
                        <th scope="col" class="min-w-[200px] px-3 py-3.5 text-center text-xs font-semibold text-white uppercase tracking-wider">
                            Acciones
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200 dark:bg-gray-800 dark:divide-gray-700">
                    @forelse ($reservas as $reserva)
                        @php
                            $infoExtra = \App\Livewire\ReservationsTable::extraerInstitucionYActividad($reserva);
                            $horasDuracion = \App\Livewire\ReservationsTable::calcularHorasReserva($reserva);
                            $modulosReserva = max(1, (int) ($reserva->modulos ?? 1));
                        @endphp
                        <tr wire:key="reserva-row-{{ $reserva->id_reserva }}" class="hover:bg-slate-50/80 dark:hover:bg-gray-700/50 transition-colors">
                            <td class="w-24 px-3 py-3 text-center align-middle whitespace-nowrap">
                                <span class="font-medium text-[#950101] dark:text-blue-400 text-sm">#{{ $reserva->id_reserva }}</span>
                            </td>
                            <td class="w-28 px-3 py-3 text-center align-middle whitespace-nowrap">
                                <span class="font-medium text-gray-800 dark:text-gray-200 text-sm">
                                    {{ $reserva->fecha_reserva ? \Carbon\Carbon::parse($reserva->fecha_reserva)->format('d/m/Y') : '-' }}
                                </span>
                            </td>
                            <td class="w-32 px-3 py-3 text-center align-middle whitespace-nowrap">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-semibold bg-slate-100 text-slate-700 border border-slate-200 dark:bg-slate-700 dark:text-slate-300 dark:border-slate-600">
                                    {{ substr($reserva->hora, 0, 5) }} - {{ substr($reserva->hora_salida, 0, 5) }}
                                </span>
                                <div class="text-[11px] text-gray-500 mt-0.5 font-medium">
                                    {{ $horasDuracion }} hrs ({{ $modulosReserva }} {{ $modulosReserva === 1 ? 'mód.' : 'móds.' }})
                                </div>
                            </td>
                            <td class="w-36 px-3 py-3 text-center align-middle whitespace-nowrap">
                                <div class="font-semibold text-gray-800 dark:text-white text-sm">
                                    {{ $reserva->espacio->nombre_espacio ?? $reserva->id_espacio }}
                                </div>
                                @if($reserva->espacio && $reserva->espacio->tipo_espacio)
                                    <span class="text-[11px] text-gray-500 truncate block">
                                        {{ $reserva->espacio->tipo_espacio }} ({{ $reserva->id_espacio }})
                                    </span>
                                @endif
                            </td>
                            <td class="px-3 py-3 align-middle">
                                <div class="flex flex-col items-start">
                                    <span class="font-semibold text-gray-900 dark:text-gray-100 text-sm leading-snug">
                                        {{ $reserva->nombre_usuario }}
                                    </span>
                                    <div class="flex items-center gap-1.5 mt-1">
                                        @if($infoExtra['categoria'] === 'Docente')
                                            <span class="px-2 py-0.5 text-[10px] font-bold uppercase rounded-full bg-indigo-50 text-indigo-700 border border-indigo-200">
                                                Docente
                                            </span>
                                        @elseif($infoExtra['categoria'] === 'Externo')
                                            <span class="px-2 py-0.5 text-[10px] font-bold uppercase rounded-full bg-amber-50 text-amber-800 border border-amber-200">
                                                Externo
                                            </span>
                                        @else
                                            <span class="px-2 py-0.5 text-[10px] font-bold uppercase rounded-full bg-sky-50 text-sky-700 border border-sky-200">
                                                {{ $infoExtra['categoria'] }}
                                            </span>
                                        @endif
                                        @if($reserva->run_profesor || $reserva->run_solicitante)
                                            <span class="text-[11px] text-gray-500 font-mono">
                                                RUN: {{ $reserva->run_profesor ?: $reserva->run_solicitante }}
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="px-3 py-3 align-middle max-w-xs">
                                <div class="flex flex-col items-start">
                                    @if($infoExtra['institucion'] && $infoExtra['institucion'] !== 'Docente Interno' && $infoExtra['institucion'] !== $infoExtra['actividad'])
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 mb-1 rounded text-[11px] font-bold bg-amber-100/80 text-amber-900 border border-amber-300/70">
                                            <i class="fa-solid fa-building text-[10px]"></i>
                                            {{ $infoExtra['institucion'] }}
                                        </span>
                                    @endif
                                    <span class="text-sm font-medium text-gray-800 dark:text-gray-200 line-clamp-2" title="{{ $infoExtra['actividad'] }}">
                                        {{ $infoExtra['actividad'] }}
                                    </span>
                                    @if(!empty($infoExtra['descripcion']) && $infoExtra['descripcion'] !== $infoExtra['actividad'])
                                        <span class="text-xs text-gray-500 line-clamp-1 mt-0.5" title="{{ $infoExtra['descripcion'] }}">
                                            {{ $infoExtra['descripcion'] }}
                                        </span>
                                    @endif
                                    @if(!empty($reserva->creado_por))
                                        <span class="text-[10px] text-gray-400 mt-0.5">
                                            Reg. por: {{ $reserva->creado_por }}
                                        </span>
                                    @endif
                                </div>
                            </td>
                            <td class="w-28 px-3 py-3 text-center align-middle whitespace-nowrap">
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
                            <td class="min-w-[200px] px-3 py-3 text-center align-middle whitespace-nowrap">
                                <div class="flex items-center justify-center gap-1.5 flex-wrap">
                                    @can('mantenedor de reservas')
                                        <a href="{{ route('reservas.edit', $reserva->id_reserva) }}"
                                           class="inline-flex items-center gap-1.5 px-2.5 py-1 text-xs font-semibold text-gray-700 bg-white hover:bg-gray-100 hover:text-[#950101] rounded-lg border border-gray-300 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200 dark:hover:bg-gray-600 transition-colors shadow-xs"
                                           title="Editar reserva">
                                            <i class="fa-solid fa-pen-to-square text-xs"></i>
                                            <span>Editar</span>
                                        </a>
                                    @endcan
                                    <a href="{{ route('reservas.comprobante', $reserva->id_reserva) }}"
                                       target="_blank"
                                       class="inline-flex items-center gap-1.5 px-2.5 py-1 text-xs font-semibold text-red-700 bg-red-50 hover:bg-red-600 hover:text-white rounded-lg border border-red-200 transition-colors shadow-xs"
                                       title="Descargar Comprobante PDF">
                                        <i class="fa-solid fa-file-pdf text-xs"></i>
                                        <span>PDF</span>
                                    </a>
                                    @can('mantenedor de reservas')
                                        <form method="POST" action="{{ route('reservas.delete', $reserva->id_reserva) }}" class="reserva-delete-form inline-block">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" title="Cancelar reserva"
                                                    class="inline-flex items-center gap-1.5 px-2.5 py-1 text-xs font-semibold text-[#D12420] bg-red-50 hover:bg-[#D12420] hover:text-white rounded-lg border border-[#EDB9B9] transition-colors shadow-xs btn-delete-reserva"
                                                    data-espacio="{{ $reserva->id_espacio }}">
                                                <i class="fa-solid fa-trash text-xs"></i>
                                                <span>Cancelar</span>
                                            </button>
                                        </form>
                                    @endcan

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
                            <td colspan="8" class="px-6 py-12 text-center text-gray-500 dark:text-gray-400">
                                <div class="flex flex-col items-center justify-center">
                                    <i class="fa-solid fa-calendar-xmark text-5xl text-gray-300 dark:text-gray-600 mb-3"></i>
                                    <p class="text-base font-medium text-gray-700 dark:text-gray-300">No se encontraron reservas con los filtros seleccionados.</p>
                                    @if(!empty($search) || !empty($tipoEspacio) || !empty($tipoSolicitante) || !empty($fechaInicio) || !empty($fechaFin) || !empty($estado))
                                        <button type="button" wire:click="limpiarFiltros" class="mt-2 text-xs text-[#950101] hover:text-blue-800 underline font-medium">
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
                        confirmButtonColor: '#D12420',
                        cancelButtonColor: '#8D8D8D',
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
