<x-app-layout>
    @php
        $tabActual = $tab ?? request()->query('tab', 'justificaciones');
        $exportParams = request()->all();
    @endphp

    <x-slot name="header">
        <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
            <div class="flex items-center gap-3">
                <div class="p-2 rounded-xl bg-light-cloud-blue">
                    <i class="text-2xl text-white fa-solid fa-clipboard-user"></i>
                </div>
                <div>
                    <h2 class="text-2xl font-bold leading-tight">Auditorías</h2>
                    <p class="text-sm text-gray-500">
                        Registro inmutable de trazabilidad: justificaciones de clases, cambios de estado y reagendamientos
                    </p>
                </div>
            </div>

            <!-- Botones de acción y exportación de la pestaña activa -->
            <div class="flex flex-wrap items-center gap-2">
                <a href="{{ route('clases-no-realizadas.index') }}"
                   class="inline-flex items-center justify-center px-4 py-2.5 bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 text-sm font-semibold rounded-lg shadow-sm hover:shadow transition-all duration-200 gap-2">
                    <i class="fas fa-arrow-left"></i>
                    <span>Control de Clases</span>
                </a>
                <a id="btnExportExcel" href="{{ route('reportes.auditoria-clases.export', ['format' => 'excel']) }}?{{ http_build_query(array_merge($exportParams, ['tab' => $tabActual])) }}"
                   class="btn btn-excel gap-2 px-4 py-2.5 text-sm font-semibold">
                    <i class="fas fa-file-excel"></i>
                    <span>Exportar Excel</span>
                </a>
                <a id="btnExportPdf" href="{{ route('reportes.auditoria-clases.export', ['format' => 'pdf']) }}?{{ http_build_query(array_merge($exportParams, ['tab' => $tabActual])) }}" target="_blank"
                   class="btn btn-pdf gap-2 px-4 py-2.5 text-sm font-semibold">
                    <i class="fas fa-file-pdf"></i>
                    <span>Exportar PDF</span>
                </a>
            </div>
        </div>
    </x-slot>

    <div x-data="{ 
            activeTab: (new URLSearchParams(window.location.search)).get('tab') || '{{ $tabActual }}',
            cambiarTab(tabName) {
                this.activeTab = tabName;
                const u = new URL(window.location);
                u.searchParams.set('tab', tabName);
                window.history.replaceState(null, '', u);

                const excelBtn = document.getElementById('btnExportExcel');
                if (excelBtn) {
                    const eu = new URL(excelBtn.href);
                    eu.searchParams.set('tab', tabName);
                    excelBtn.href = eu.toString();
                }
                const pdfBtn = document.getElementById('btnExportPdf');
                if (pdfBtn) {
                    const pu = new URL(pdfBtn.href);
                    pu.searchParams.set('tab', tabName);
                    pdfBtn.href = pu.toString();
                }
            }
        }" class="space-y-6 pb-8">

        <!-- Tabs de navegación (Navpills al estilo Control de Clases) -->
        <div class="flex flex-wrap sm:inline-flex items-center gap-2 bg-gray-200/80 p-1.5 rounded-2xl border border-gray-300/70 shadow-sm">
            <button 
                type="button"
                @click="cambiarTab('justificaciones')"
                :class="activeTab === 'justificaciones' ? 'bg-white shadow-md text-gray-900 font-bold border border-gray-200' : 'text-gray-600 hover:text-gray-900 hover:bg-white/60 font-medium'"
                class="px-6 py-3 rounded-xl text-base sm:text-lg transition-all duration-200 flex items-center gap-3 cursor-pointer"
            >
                <i class="fa-solid fa-shield-halved text-xl" :class="activeTab === 'justificaciones' ? 'text-[#950101]' : 'text-gray-400'"></i>
                <span>Clases y Justificaciones</span>
                <span class="text-xs px-2.5 py-0.5 rounded-full font-bold shadow-sm" :class="activeTab === 'justificaciones' ? 'bg-[#950101] text-white' : 'bg-gray-300/80 text-gray-700'">
                    {{ $totalJustificaciones ?? 0 }}
                </span>
            </button>

            <button 
                type="button"
                @click="cambiarTab('reagendamientos')"
                :class="activeTab === 'reagendamientos' ? 'bg-white shadow-md text-gray-900 font-bold border border-gray-200' : 'text-gray-600 hover:text-gray-900 hover:bg-white/60 font-medium'"
                class="px-6 py-3 rounded-xl text-base sm:text-lg transition-all duration-200 flex items-center gap-3 cursor-pointer"
            >
                <i class="fa-solid fa-calendar-days text-xl" :class="activeTab === 'reagendamientos' ? 'text-[#950101]' : 'text-gray-400'"></i>
                <span>Reagendamientos y Recuperación</span>
                <span class="text-xs px-2.5 py-0.5 rounded-full font-bold shadow-sm" :class="activeTab === 'reagendamientos' ? 'bg-[#950101] text-white' : 'bg-gray-300/80 text-gray-700'">
                    {{ $totalReagendamientos ?? 0 }}
                </span>
            </button>
        </div>

        <!-- Barra de Filtros -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
            <form method="GET" action="{{ route('reportes.auditoria-clases.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3.5 items-end">
                <input type="hidden" name="tab" :value="activeTab">

                <!-- Búsqueda -->
                <div class="lg:col-span-2">
                    <label class="block text-xs font-medium text-gray-700 mb-1">
                        <span x-show="activeTab === 'justificaciones'">Buscar (Docente, RUN, Asignatura o Usuario que justificó):</span>
                        <span x-show="activeTab === 'reagendamientos'">Buscar (Docente, RUN, Asignatura, Sala o Usuario que reagendó):</span>
                    </label>
                    <div class="relative">
                        <i class="fas fa-search absolute left-3 top-3 text-gray-400 text-xs"></i>
                        <input type="text" name="search" value="{{ $search }}" placeholder="Ej: Perez, 12345678, Cálculo..."
                               class="w-full pl-8 text-sm rounded-lg border-gray-300 focus:border-red-500 focus:ring-red-500 py-2">
                    </div>
                </div>

                <!-- Tipo de Acción -->
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">Acción Realizada:</label>
                    <select name="accion" class="w-full text-sm rounded-lg border-gray-300 focus:border-red-500 focus:ring-red-500 py-2">
                        <option value="">Todas las Acciones</option>
                        @foreach($accionesDisponiblesJustificaciones as $aKey => $aLabel)
                            <option value="{{ $aKey }}" x-show="activeTab === 'justificaciones'" {{ $accionSeleccionada === $aKey ? 'selected' : '' }}>{{ $aLabel }}</option>
                        @endforeach
                        @foreach($accionesDisponiblesReagendamientos as $aKey => $aLabel)
                            <option value="{{ $aKey }}" x-show="activeTab === 'reagendamientos'" {{ $accionSeleccionada === $aKey ? 'selected' : '' }}>{{ $aLabel }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Rango de Fechas -->
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">Desde:</label>
                    <input type="date" name="fecha_inicio" value="{{ $fechaInicio }}"
                           class="w-full text-sm rounded-lg border-gray-300 focus:border-red-500 focus:ring-red-500 py-2">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">Hasta:</label>
                    <input type="date" name="fecha_fin" value="{{ $fechaFin }}"
                           class="w-full text-sm rounded-lg border-gray-300 focus:border-red-500 focus:ring-red-500 py-2">
                </div>

                <div class="sm:col-span-2 lg:col-span-5 flex justify-end gap-2 pt-2 border-t border-gray-100">
                    <a :href="'{{ route('reportes.auditoria-clases.index') }}?tab=' + activeTab"
                       class="inline-flex items-center justify-center px-4 py-2 bg-gray-100 hover:bg-gray-200 border border-gray-300 text-gray-700 text-xs font-semibold rounded-lg shadow-sm transition-all duration-200">
                        <i class="fas fa-undo mr-1.5 text-xs"></i> Limpiar
                    </a>
                    <button type="submit"
                            class="inline-flex items-center justify-center px-5 py-2 bg-[#191919] hover:bg-black text-white text-xs font-semibold rounded-lg shadow-sm hover:shadow transition-all duration-200">
                        <i class="fas fa-filter mr-1.5 text-xs"></i> Filtrar
                    </button>
                </div>
            </form>
        </div>

        <!-- Tabla 1: Justificaciones y Cambios -->
        <div x-show="activeTab === 'justificaciones'" x-transition class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-[#950101] text-white border-b border-[#7B0101]">
                        <tr>
                            <th class="px-4 py-3 text-center text-xs font-medium text-white uppercase tracking-wider">Fecha y Hora</th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-white uppercase tracking-wider">Usuario Responsable</th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-white uppercase tracking-wider">Acción</th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-white uppercase tracking-wider">Docente Afectado</th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-white uppercase tracking-wider">Asignatura / Espacio</th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-white uppercase tracking-wider">Fecha Clase</th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-white uppercase tracking-wider">Transición</th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-white uppercase tracking-wider">Motivo / Observación</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200 text-sm">
                        @forelse ($registrosJustificaciones as $row)
                            @php
                                $act = strtolower($row->accion);
                                $badgeStyle = match(true) {
                                    str_contains($act, 'justific') => 'bg-green-100 text-green-800',
                                    str_contains($act, 'registra') => 'bg-indigo-100 text-indigo-800',
                                    str_contains($act, 'recuperada') => 'bg-green-100 text-green-800',
                                    str_contains($act, 'reagend') => 'bg-yellow-100 text-yellow-800',
                                    str_contains($act, 'elimin') => 'bg-red-100 text-red-800',
                                    default => 'bg-gray-100 text-gray-800',
                                };
                            @endphp
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-4 py-3.5 whitespace-nowrap">
                                    <div class="font-medium text-gray-900">{{ $row->created_at->format('d/m/Y') }}</div>
                                    <div class="text-xs text-gray-500 font-mono">{{ $row->created_at->format('H:i:s') }}</div>
                                </td>
                                <td class="px-4 py-3.5">
                                    <div class="font-medium text-gray-900">{{ $row->user_nombre }}</div>
                                    <div class="text-xs text-gray-500">
                                        {{ $row->user_rol ?? 'Usuario' }}
                                        @if($row->ip) &bull; <span class="font-mono">IP: {{ $row->ip }}</span> @endif
                                    </div>
                                </td>
                                <td class="px-4 py-3.5 text-center whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold {{ $badgeStyle }}">
                                        {{ ucfirst(str_replace('_', ' ', $row->accion)) }}
                                    </span>
                                </td>
                                <td class="px-4 py-3.5">
                                    <div class="font-medium text-gray-900">{{ $row->profesor->name ?? 'N/A' }}</div>
                                    <div class="text-xs text-gray-500 font-mono">RUN: {{ $row->run_profesor ?? 'N/A' }}</div>
                                </td>
                                <td class="px-4 py-3.5">
                                    <div class="font-medium text-gray-900">{{ $row->asignatura->nombre_asignatura ?? ($row->id_asignatura ?? 'N/A') }}</div>
                                    <div class="text-xs text-gray-500">Espacio: <span class="font-semibold text-gray-700">{{ $row->id_espacio ?? 'N/A' }}</span></div>
                                </td>
                                <td class="px-4 py-3.5 text-center whitespace-nowrap">
                                    <div class="font-medium text-gray-900">{{ $row->fecha_clase ? $row->fecha_clase->format('d/m/Y') : 'N/A' }}</div>
                                    <div class="text-xs text-gray-500">Mód. {{ $row->id_modulo ?? 'N/A' }}</div>
                                </td>
                                <td class="px-4 py-3.5 text-center whitespace-nowrap text-xs">
                                    <span class="text-gray-500">{{ $row->estado_anterior ?? '—' }}</span>
                                    <span class="text-gray-400 mx-1">&rarr;</span>
                                    <span class="font-bold text-gray-900">{{ $row->estado_nuevo ?? '—' }}</span>
                                </td>
                                <td class="px-4 py-3.5 max-w-xs">
                                    @if($row->motivo)
                                        <div class="font-semibold text-gray-900 text-xs">{{ $row->motivo }}</div>
                                    @endif
                                    @if($row->observaciones)
                                        <div class="text-xs text-gray-500 truncate" title="{{ $row->observaciones }}">{{ $row->observaciones }}</div>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-6 py-12 text-center text-gray-400">
                                    <i class="fas fa-clipboard-check text-4xl mb-3 text-gray-300"></i>
                                    <p class="font-semibold text-base text-gray-700">No se encontraron registros de auditoría.</p>
                                    <p class="text-xs text-gray-500 mt-1">Todas las justificaciones, ediciones y cambios realizados por usuarios quedarán registrados automáticamente aquí.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($registrosJustificaciones->hasPages())
                <div class="px-6 py-4 border-t border-gray-200 bg-gray-50">
                    {{ $registrosJustificaciones->links() }}
                </div>
            @endif
        </div>

        <!-- Tabla 2: Reagendamientos y Recuperación -->
        <div x-show="activeTab === 'reagendamientos'" x-transition class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-[#950101] text-white border-b border-[#7B0101]">
                        <tr>
                            <th class="px-4 py-3 text-center text-xs font-medium text-white uppercase tracking-wider">Fecha y Hora</th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-white uppercase tracking-wider">Usuario Responsable</th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-white uppercase tracking-wider">Acción</th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-white uppercase tracking-wider">Docente Afectado</th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-white uppercase tracking-wider">Asignatura / Espacio</th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-white uppercase tracking-wider">Clase Original</th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-white uppercase tracking-wider">Nueva Programación</th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-white uppercase tracking-wider">Motivo / Observación</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200 text-sm">
                        @forelse ($registrosReagendamientos as $row)
                            @php
                                $act = strtolower($row->accion);
                                $badgeStyle = match(true) {
                                    str_contains($act, 'justific') => 'bg-green-100 text-green-800',
                                    str_contains($act, 'registra') => 'bg-indigo-100 text-indigo-800',
                                    str_contains($act, 'recuperada') => 'bg-green-100 text-green-800',
                                    str_contains($act, 'reagend') => 'bg-yellow-100 text-yellow-800',
                                    str_contains($act, 'elimin') => 'bg-red-100 text-red-800',
                                    default => 'bg-gray-100 text-gray-800',
                                };
                                $ant = is_array($row->valores_anteriores) ? $row->valores_anteriores : [];
                                $nue = is_array($row->valores_nuevos) ? $row->valores_nuevos : [];
                                $fechaAnt = !empty($ant['fecha_clase']) ? \Carbon\Carbon::parse($ant['fecha_clase'])->format('d/m/Y') : null;
                                $fechaNue = !empty($nue['fecha_clase']) ? \Carbon\Carbon::parse($nue['fecha_clase'])->format('d/m/Y') : ($row->fecha_clase ? $row->fecha_clase->format('d/m/Y') : 'N/A');
                            @endphp
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-4 py-3.5 whitespace-nowrap">
                                    <div class="font-medium text-gray-900">{{ $row->created_at->format('d/m/Y') }}</div>
                                    <div class="text-xs text-gray-500 font-mono">{{ $row->created_at->format('H:i:s') }}</div>
                                </td>
                                <td class="px-4 py-3.5">
                                    <div class="font-medium text-gray-900">{{ $row->user_nombre }}</div>
                                    <div class="text-xs text-gray-500">
                                        {{ $row->user_rol ?? 'Usuario' }}
                                        @if($row->ip) &bull; <span class="font-mono">IP: {{ $row->ip }}</span> @endif
                                    </div>
                                </td>
                                <td class="px-4 py-3.5 text-center whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold {{ $badgeStyle }}">
                                        {{ ucfirst(str_replace('_', ' ', $row->accion)) }}
                                    </span>
                                </td>
                                <td class="px-4 py-3.5">
                                    <div class="font-medium text-gray-900">{{ $row->profesor->name ?? 'N/A' }}</div>
                                    <div class="text-xs text-gray-500 font-mono">RUN: {{ $row->run_profesor ?? 'N/A' }}</div>
                                </td>
                                <td class="px-4 py-3.5">
                                    <div class="font-medium text-gray-900">{{ $row->asignatura->nombre_asignatura ?? ($row->id_asignatura ?? 'N/A') }}</div>
                                    <div class="text-xs text-gray-500">Espacio: <span class="font-semibold text-gray-700">{{ $row->id_espacio ?? 'N/A' }}</span></div>
                                </td>
                                <td class="px-4 py-3.5 text-center whitespace-nowrap">
                                    @if($fechaAnt || !empty($ant['id_espacio']) || !empty($ant['id_modulo']))
                                        <div class="font-medium text-gray-700">{{ $fechaAnt ?? '—' }}</div>
                                        <div class="text-xs text-gray-500">
                                            {{ $ant['id_espacio'] ?? '—' }} &bull; Mód. {{ $ant['id_modulo'] ?? '—' }}
                                        </div>
                                    @else
                                        <span class="text-xs text-gray-400">—</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3.5 text-center whitespace-nowrap">
                                    <div class="font-semibold text-gray-900">{{ $fechaNue }}</div>
                                    <div class="text-xs text-gray-600">
                                        {{ $nue['id_espacio'] ?? ($row->id_espacio ?? 'N/A') }} &bull; Mód. {{ $nue['id_modulo'] ?? ($row->id_modulo ?? 'N/A') }}
                                    </div>
                                </td>
                                <td class="px-4 py-3.5 max-w-xs">
                                    @if($row->motivo)
                                        <div class="font-semibold text-gray-900 text-xs">{{ $row->motivo }}</div>
                                    @endif
                                    @if($row->observaciones)
                                        <div class="text-xs text-gray-500 truncate" title="{{ $row->observaciones }}">{{ $row->observaciones }}</div>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-6 py-12 text-center text-gray-400">
                                    <i class="fas fa-clipboard-check text-4xl mb-3 text-gray-300"></i>
                                    <p class="font-semibold text-base text-gray-700">No se encontraron registros de reagendamiento.</p>
                                    <p class="text-xs text-gray-500 mt-1">Todos los reagendamientos y recuperaciones gestionados por usuarios aparecerán aquí.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($registrosReagendamientos->hasPages())
                <div class="px-6 py-4 border-t border-gray-200 bg-gray-50">
                    {{ $registrosReagendamientos->links() }}
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
