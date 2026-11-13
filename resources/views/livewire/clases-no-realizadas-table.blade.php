<div wire:poll.visible.300s>

    @if($periodoNoIniciado)
        <!-- Mensaje cuando el periodo no ha iniciado -->
        <div class="flex items-center justify-center min-h-96 bg-gradient-to-br from-yellow-50 to-yellow-100 rounded-lg border-2 border-dashed border-yellow-300 p-8">
            <div class="text-center max-w-md">
                <div class="mb-6 flex justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-20 h-20 text-yellow-500">
                        <path fill-rule="evenodd" d="M12 2.25c-5.385 0-9.75 4.365-9.75 9.75s4.365 9.75 9.75 9.75 9.75-4.365 9.75-9.75S17.385 2.25 12 2.25ZM12.75 6a.75.75 0 0 0-1.5 0v6c0 .414.336.75.75.75h4.5a.75.75 0 0 0 0-1.5h-3.75V6Z" clip-rule="evenodd" />
                    </svg>
                </div>
                <h2 class="text-2xl font-bold text-gray-800 mb-2">{{ $nombrePeriodo }}</h2>
                <p class="text-gray-600 mb-4">El periodo académico aún no ha comenzado</p>
                <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 rounded">
                    <p class="text-yellow-800 text-sm">
                        No existen registros en el Control de Clases porque el periodo académico oficial no ha iniciado. 
                        Los registros se generarán automáticamente una vez que comiencen las actividades académicas.
                    </p>
                </div>
            </div>
        </div>
    @else

    <!-- Título Clases Realizadas -->
    <div class="mb-4">
        <span class="inline-flex items-center gap-3 px-5 py-2.5 bg-white border border-gray-200 rounded-xl shadow-sm text-base sm:text-lg font-bold text-gray-900">
            <i class="fas fa-calendar-check text-[#950101] text-xl"></i>
            <span>Clases Realizadas</span>
        </span>
    </div>

    <!-- Navpills / Estadísticas (interactivas al hacer clic) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <!-- Total realizadas / Evaluadas -->
        <button type="button" wire:click="$set('estado', '')"
                class="text-left stat-card bg-slate-50 hover:bg-slate-100/70 border {{ $estado === '' ? 'border-[#D12420] ring-2 ring-[#D12420]/20' : 'border-[#C0C0C0]' }} rounded-xl p-4 shadow-sm transition cursor-pointer">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-slate-700 text-xs font-semibold uppercase tracking-wider">Total clases</p>
                    <p class="text-3xl font-black text-[#191919] mt-1">{{ $estadisticas['total'] }}</p>
                </div>
                <div class="p-3 bg-slate-100 rounded-lg text-[#D12420]">
                    <i class="fas fa-calendar-check text-xl"></i>
                </div>
            </div>
        </button>

        <!-- No registradas -->
        <button type="button" wire:click="$set('estado', 'no_realizada')"
                class="text-left stat-card bg-red-50 hover:bg-red-100/70 border {{ $estado === 'no_realizada' ? 'border-red-500 ring-2 ring-red-500/20' : 'border-red-200' }} rounded-xl p-4 shadow-sm transition cursor-pointer">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-red-700 text-xs font-semibold uppercase tracking-wider">No registradas</p>
                    <p class="text-3xl font-black text-red-950 mt-1">{{ $estadisticas['no_realizadas'] }}</p>
                </div>
                <div class="p-3 bg-red-100 rounded-lg text-red-600">
                    <i class="fas fa-times-circle text-xl"></i>
                </div>
            </div>
        </button>

        <!-- Registradas -->
        <button type="button" wire:click="$set('estado', 'impartidas')"
                class="text-left stat-card bg-indigo-50 hover:bg-indigo-100/70 border {{ in_array($estado, ['realizada', 'impartidas']) ? 'border-indigo-500 ring-2 ring-indigo-500/20' : 'border-indigo-200' }} rounded-xl p-4 shadow-sm transition cursor-pointer">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-indigo-700 text-xs font-semibold uppercase tracking-wider">Registradas / Impartidas</p>
                    <p class="text-3xl font-black text-indigo-950 mt-1">{{ $estadisticas['realizadas'] ?? 0 }}</p>
                </div>
                <div class="p-3 bg-indigo-100 rounded-lg text-indigo-600">
                    <i class="fas fa-check-circle text-xl"></i>
                </div>
            </div>
        </button>

        <!-- Justificadas -->
        <button type="button" wire:click="$set('estado', 'justificado')"
                class="text-left stat-card bg-amber-50 hover:bg-amber-100/70 border {{ $estado === 'justificado' ? 'border-amber-500 ring-2 ring-amber-500/20' : 'border-amber-200' }} rounded-xl p-4 shadow-sm transition cursor-pointer">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-amber-700 text-xs font-semibold uppercase tracking-wider">Justificadas</p>
                    <p class="text-3xl font-black text-amber-950 mt-1">{{ $estadisticas['justificados'] }}</p>
                </div>
                <div class="p-3 bg-amber-100 rounded-lg text-amber-600">
                    <i class="fas fa-shield-alt text-xl"></i>
                </div>
            </div>
        </button>
    </div>

    <!-- Sección FILTROS -->
    <div class="bg-white p-5 rounded-xl border border-gray-200 shadow-sm mb-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-sm font-bold text-gray-700 uppercase tracking-wider flex items-center gap-2">
                <i class="fas fa-filter text-[#950101]"></i> FILTROS
            </h3>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-7 gap-3">
            <!-- Semestre -->
            <div>
                <label for="periodo_filtro" class="block text-xs font-semibold text-gray-700 uppercase mb-1">Semestre</label>
                <select wire:model.live="periodo" 
                        id="periodo_filtro"
                        class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-[#D12420]/20 focus:border-[#D12420] bg-white">
                    @foreach($periodosDisponibles as $p)
                        <option value="{{ $p->codigo }}">
                            {{ $p->display_label }}
                        </option>
                    @endforeach
                    <option value="">Todos los semestres</option>
                </select>
            </div>

            <!-- Buscar -->
            <div>
                <label for="search" class="block text-xs font-semibold text-gray-700 uppercase mb-1">Buscar</label>
                <div class="relative">
                    <input type="text" 
                           wire:model.live.debounce.800ms="search" 
                           id="search"
                           class="w-full pl-9 pr-3 py-2 text-sm border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-[#D12420]/20 focus:border-[#D12420]"
                           placeholder="Profesor, asignatura, RUN o espacio...">
                    <i class="fas fa-search absolute left-3 top-2.5 text-gray-400 text-xs"></i>
                </div>
            </div>

            <!-- Escuela -->
            <div>
                <label for="escuela_filtro" class="block text-xs font-semibold text-gray-700 uppercase mb-1">Escuela</label>
                <select wire:model.live="escuela" 
                        id="escuela_filtro"
                        class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-[#D12420]/20 focus:border-[#D12420] bg-white">
                    <option value="">Todas las Escuelas</option>
                    @if(isset($escuelasDisponibles) && count($escuelasDisponibles) > 0)
                        @foreach($escuelasDisponibles as $esc)
                            @php
                                $escNombreCorto = preg_replace('/^Escuela de\s+/i', '', $esc);
                            @endphp
                            <option value="{{ $esc }}" title="{{ $esc }}">
                                {{ $escNombreCorto }}
                            </option>
                        @endforeach
                    @endif
                </select>
            </div>

            <!-- Unidad Académica (UA) -->
            <div>
                <label for="ua_filtro" class="block text-xs font-semibold text-gray-700 uppercase mb-1">Unidad Académica</label>
                <select wire:model.live="ua" 
                        id="ua_filtro"
                        class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-[#D12420]/20 focus:border-[#D12420] bg-white">
                    <option value="">Todas las UAs</option>
                    @if(isset($unidadesAcademicas) && count($unidadesAcademicas) > 0)
                        @foreach($unidadesAcademicas as $u)
                            <option value="{{ $u->id_carrera }}">
                                {{ $u->id_carrera }} - {{ $u->nombre }}
                            </option>
                        @endforeach
                    @endif
                </select>
            </div>

            <!-- Estado -->
            <div>
                <label for="estado" class="block text-xs font-semibold text-gray-700 uppercase mb-1">Estado</label>
                <select wire:model.live.debounce.800ms="estado" 
                        id="estado"
                        class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-[#D12420]/20 focus:border-[#D12420] bg-white">
                    <option value="">Todos</option>
                    <option value="no_realizada">No registrada</option>
                    <option value="impartidas">Todas las impartidas / efectivas</option>
                    <option value="realizada">Registrada (Clase realizada)</option>
                    <option value="recuperada">Recuperada</option>
                    <option value="pendiente">Pendiente de recuperación (Reagendada)</option>
                    <option value="justificado">Justificado</option>
                </select>
            </div>

            <!-- Desde -->
            <div>
                <label for="fecha_inicio" class="block text-xs font-semibold text-gray-700 uppercase mb-1">Desde</label>
                <input type="date" 
                       wire:model.live.debounce.800ms="fecha_inicio" 
                       id="fecha_inicio"
                       class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-[#D12420]/20 focus:border-[#D12420]">
            </div>

            <!-- Hasta -->
            <div>
                <label for="fecha_fin" class="block text-xs font-semibold text-gray-700 uppercase mb-1">Hasta</label>
                <input type="date" 
                       wire:model.live.debounce.800ms="fecha_fin" 
                       id="fecha_fin"
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

    <!-- Botón Exportar y Auditorías -->
    <div class="flex flex-wrap justify-end gap-2 mb-4">
        <a href="{{ route('reportes.auditoria-clases.index') }}"
           class="inline-flex items-center justify-center px-4 py-2.5 bg-slate-800 hover:bg-slate-900 text-white text-sm font-semibold rounded-lg shadow-sm hover:shadow transition-all duration-200 gap-2">
            <i class="fas fa-clipboard-user"></i>
            <span>Auditorías</span>
        </a>
        <a href="{{ route('clases-no-realizadas.export-all-excel', array_filter(['periodo' => $periodo, 'fecha_inicio' => $fecha_inicio, 'fecha_fin' => $fecha_fin, 'estado' => $estado, 'search' => $search, 'ua' => $ua, 'escuela' => $escuela])) }}"
           class="btn btn-excel gap-2 px-4 py-2.5 text-sm font-semibold">
            <i class="fas fa-file-excel"></i>
            <span>Exportar todas las clases</span>
        </a>
    </div>

            
            <!-- Mensaje Flash -->
            @if (session()->has('message'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                    {{ session('message') }}
                </div>
            @endif

            {{-- Barra de Acciones Masivas --}}
            @if($this->puedeGestionarClases && ($cantidadSeleccionada > 0 || $selectAllFiltered))
                <div class="mb-4 bg-slate-50 text-slate-800 px-5 py-3 rounded-xl shadow-sm flex flex-wrap items-center justify-between gap-3 border border-slate-200 transition-all duration-200 relative z-30">
                    <div class="flex items-center gap-3">
                        <div class="p-2 bg-slate-200/70 rounded-lg text-slate-700 border border-slate-300/60">
                            <i class="fas fa-check-double text-lg"></i>
                        </div>
                        <div>
                            <p class="font-semibold text-sm text-slate-900 flex flex-wrap items-center gap-2">
                                <span>{{ $cantidadSeleccionada }} {{ $cantidadSeleccionada === 1 ? 'clase seleccionada' : 'clases seleccionadas' }}</span>
                                @if($selectAllFiltered)
                                    <span class="text-[11px] px-2 py-0.5 bg-slate-200 text-slate-700 rounded-md font-medium">Filtro completo</span>
                                    @if($totalFiltradasSeleccionables > count($currentPageSelectableKeys ?? []))
                                        <button type="button" wire:click="seleccionarSoloPaginaActual"
                                                class="text-[11px] text-[#950101] hover:underline font-medium cursor-pointer">
                                            Solo esta página ({{ count($currentPageSelectableKeys ?? []) }})
                                        </button>
                                    @endif
                                @elseif($totalFiltradasSeleccionables > $cantidadSeleccionada)
                                    <button type="button" wire:click="seleccionarTodoElFiltro"
                                            class="text-[11px] text-[#950101] hover:underline font-medium cursor-pointer">
                                        Seleccionar todo el filtro ({{ $totalFiltradasSeleccionables }})
                                    </button>
                                @endif
                            </p>
                            <p class="text-xs text-slate-500">
                                Elige el nuevo estado para aplicar a todas las clases seleccionadas.
                            </p>
                        </div>
                    </div>

                    <div class="flex flex-wrap items-center gap-2">
                        <button type="button" wire:click="limpiarSeleccion" 
                                class="border border-gray-300 bg-white text-gray-700 hover:bg-gray-50 active:bg-gray-100 focus:ring-gray-300 rounded-md font-medium text-sm gap-2 inline-flex items-center justify-center shadow-sm px-4 py-2 cursor-pointer transition-all duration-150">
                            <i class="fas fa-times"></i>
                            <span>Cancelar</span>
                        </button>

                        <!-- Botón seleccionable para cambiar estado masivo -->
                        <div class="relative inline-block text-left"
                             x-data="{ openEstadoMenu: false }"
                             x-on:click.outside="openEstadoMenu = false"
                             x-on:keydown.escape.window="openEstadoMenu = false">
                            <button type="button"
                                    x-on:click="openEstadoMenu = !openEstadoMenu"
                                    class="inline-flex items-center justify-center px-4 py-2 bg-slate-800 hover:bg-slate-900 text-white text-sm font-semibold rounded-lg shadow-sm hover:shadow transition-all duration-200 gap-2 cursor-pointer">
                                <i class="fas fa-exchange-alt text-sm"></i>
                                <span>Cambiar estado a...</span>
                                <i class="fas fa-chevron-down text-xs opacity-80 transition-transform duration-150"
                                   :class="openEstadoMenu ? 'rotate-180' : ''"></i>
                            </button>

                            <div x-show="openEstadoMenu"
                                 x-cloak
                                 x-transition
                                 class="origin-top-right absolute right-0 mt-2 w-72 rounded-xl shadow-xl bg-white ring-1 ring-black/10 z-40 overflow-hidden divide-y divide-gray-100">
                                <div class="px-3.5 py-2 bg-gray-50 text-[11px] font-bold uppercase tracking-wider text-gray-500">
                                    Aplicar estado a {{ $cantidadSeleccionada }} {{ $cantidadSeleccionada === 1 ? 'clase' : 'clases' }}
                                </div>
                                <div class="py-1">
                                    <button type="button"
                                            x-on:click="openEstadoMenu = false; $dispatch('abrir-modal-masivo', { cantidad: {{ $cantidadSeleccionada }}, tipo: 'justificado' })"
                                            class="w-full text-left px-4 py-2.5 text-sm hover:bg-amber-50/70 flex items-start gap-3 transition-colors cursor-pointer">
                                        <i class="fas fa-shield-alt text-amber-600 mt-0.5 w-4 text-center"></i>
                                        <div>
                                            <div class="font-semibold text-gray-900">Justificada</div>
                                            <div class="text-xs text-gray-500">Justificar ausencia (sin recuperación)</div>
                                        </div>
                                    </button>
                                    <button type="button"
                                            x-on:click="openEstadoMenu = false; $dispatch('abrir-modal-masivo', { cantidad: {{ $cantidadSeleccionada }}, tipo: 'realizada' })"
                                            class="w-full text-left px-4 py-2.5 text-sm hover:bg-emerald-50/70 flex items-start gap-3 transition-colors cursor-pointer">
                                        <i class="fas fa-check-circle text-emerald-600 mt-0.5 w-4 text-center"></i>
                                        <div>
                                            <div class="font-semibold text-gray-900">Realizada / Registrada</div>
                                            <div class="text-xs text-gray-500">Regularizar como clase dictada</div>
                                        </div>
                                    </button>
                                    <button type="button"
                                            x-on:click="openEstadoMenu = false; $dispatch('abrir-modal-masivo', { cantidad: {{ $cantidadSeleccionada }}, tipo: 'no_realizada' })"
                                            class="w-full text-left px-4 py-2.5 text-sm hover:bg-red-50/70 flex items-start gap-3 transition-colors cursor-pointer">
                                        <i class="fas fa-times-circle text-red-600 mt-0.5 w-4 text-center"></i>
                                        <div>
                                            <div class="font-semibold text-gray-900">No Registrada</div>
                                            <div class="text-xs text-gray-500">Marcar o revertir a no registrada</div>
                                        </div>
                                    </button>
                                    <button type="button"
                                            x-on:click="openEstadoMenu = false; $dispatch('abrir-modal-masivo', { cantidad: {{ $cantidadSeleccionada }}, tipo: 'pendiente' })"
                                            class="w-full text-left px-4 py-2.5 text-sm hover:bg-orange-50/70 flex items-start gap-3 transition-colors cursor-pointer">
                                        <i class="fas fa-clock text-orange-600 mt-0.5 w-4 text-center"></i>
                                        <div>
                                            <div class="font-semibold text-gray-900">Pendiente de Recuperación</div>
                                            <div class="text-xs text-gray-500">Marcar como pendiente de recuperar</div>
                                        </div>
                                    </button>
                                    <button type="button"
                                            x-on:click="openEstadoMenu = false; $dispatch('abrir-modal-masivo', { cantidad: {{ $cantidadSeleccionada }}, tipo: 'recuperada' })"
                                            class="w-full text-left px-4 py-2.5 text-sm hover:bg-blue-50/70 flex items-start gap-3 transition-colors cursor-pointer">
                                        <i class="fas fa-calendar-check text-blue-600 mt-0.5 w-4 text-center"></i>
                                        <div>
                                            <div class="font-semibold text-gray-900">Recuperada</div>
                                            <div class="text-xs text-gray-500">Confirmar recuperación de clase</div>
                                        </div>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <div class="bg-white shadow-sm rounded-xl border border-gray-200 overflow-hidden dark:bg-gray-800 dark:border-gray-700">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-[#950101] text-white border-b border-[#7B0101]">
                            <tr>
                                @if($this->puedeGestionarClases)
                                <th class="px-3 py-3 text-center w-10">
                                    <input type="checkbox" 
                                           wire:model.live="selectAllPage" 
                                           class="rounded border-gray-300 text-[#D12420] shadow-sm focus:ring-[#950101] cursor-pointer"
                                           title="Seleccionar todas las clases"
                                           @if(empty($currentPageSelectableKeys)) disabled @endif>
                                </th>
                                @endif
                                <th class="px-3 py-3 text-center text-xs font-semibold text-white uppercase tracking-wider cursor-pointer w-24" 
                                    wire:click="sortBy('fecha_clase')">
                                    Fecha
                                    @if($sortField === 'fecha_clase')
                                        <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} text-white"></i>
                                    @endif
                                </th>
                                <th class="px-6 py-3 text-center text-xs font-semibold text-white uppercase tracking-wider w-32">Profesor</th>
                                <th class="px-3 py-3 text-center text-xs font-semibold text-white uppercase tracking-wider w-40">Asignatura</th>
                                <th class="px-4 py-3 text-center text-xs font-semibold text-white uppercase tracking-wider">Espacio</th>
                                <th class="px-4 py-3 text-center text-xs font-semibold text-white uppercase tracking-wider">Mód. Inicio</th>
                                <th class="px-4 py-3 text-center text-xs font-semibold text-white uppercase tracking-wider">Mód. Fin</th>
                                <th class="px-4 py-3 text-center text-xs font-semibold text-white uppercase tracking-wider cursor-pointer"
                                    wire:click="sortBy('estado')">
                                    Estado
                                    @if($sortField === 'estado')
                                        <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} text-white"></i>
                                    @endif
                                </th>
                                <th class="px-3 py-3 text-center text-xs font-semibold text-white uppercase tracking-wider w-28">Horario</th>
                                <th class="px-4 py-3 text-center text-xs font-semibold text-white uppercase tracking-wider min-w-[200px]">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($clasesNoRealizadas as $clase)
                                @php
                                    // Parsear módulos inicio y fin
                                    $modulos = explode(',', $clase['modulo']);
                                    $moduloInicio = preg_replace('/^[A-Z]{2}\./', '', $modulos[0]);
                                    $moduloFin = count($modulos) > 1 ? preg_replace('/^[A-Z]{2}\./', '', end($modulos)) : $moduloInicio;
                                @endphp
                                <tr wire:key="clase-row-{{ $clase['unique_key'] }}" class="table-row hover:bg-gray-50">
                                    @if($this->puedeGestionarClases)
                                    <td class="px-3 py-4 text-center w-10">
                                        <input type="checkbox" 
                                               wire:key="clase-check-{{ $clase['unique_key'] }}"
                                               wire:model.live="selectedClases" 
                                               value="{{ $clase['unique_key'] }}" 
                                               class="rounded border-gray-300 text-[#D12420] shadow-sm focus:ring-[#950101] cursor-pointer">
                                    </td>
                                    @endif
                                    <td class="px-3 py-4 text-sm text-gray-900 w-24">
                                        <div class="flex items-center gap-1 justify-center">
                                            {{ \Carbon\Carbon::parse($clase['fecha'])->format('d/m/Y') }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-900 w-32">
                                        <div class="break-words">{{ $clase['profesor'] ?? 'N/A' }}</div>
                                    </td>
                                    <td class="px-3 py-4 text-sm text-gray-900 w-40">
                                        <div class="break-words">
                                            <div class="font-medium">{{ $clase['asignatura'] ?? 'N/A' }}</div>
                                            <div class="text-xs text-gray-500">
                                                {{ $clase['codigo_asignatura'] ?? '' }}
                                                @if(!empty($clase['seccion']) && $clase['seccion'] !== 'N/A')
                                                    <span class="text-slate-600 font-medium">&bull; Sec. {{ $clase['seccion'] }}</span>
                                                @endif
                                            </div>
                                            @if(!empty($clase['ua']) && $clase['ua'] !== 'N/A')
                                                <div class="text-xs font-semibold text-gray-700 mt-0.5" title="{{ $clase['carrera'] ?? '' }}">
                                                    UA: {{ $clase['ua'] }}
                                                    @if(!empty($clase['carrera']))
                                                        <span class="font-normal text-gray-500">· {{ Str::limit($clase['carrera'], 24) }}</span>
                                                    @endif
                                                </div>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-900 text-center">
                                        {{ $clase['espacio'] }}
                                    </td>
                                    <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-900 text-center">
                                        {{ $moduloInicio }}
                                    </td>
                                    <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-900 text-center">
                                        {{ $moduloFin }}
                                    </td>
                                    <td class="px-4 py-4 whitespace-nowrap text-center">
                                        <div class="flex flex-col gap-1 items-center justify-center">
                                            @if($clase['estado'] === 'No Registrada')
                                                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">
                                                    No Registrada
                                                </span>
                                            @elseif($clase['estado'] === 'Realizada' || $clase['estado'] === 'Registrada')
                                                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-indigo-100 text-indigo-800 flex items-center gap-1">
                                                    <i class="fas fa-check-circle text-[10px]"></i>
                                                    Registrada
                                                </span>
                                            @elseif($clase['estado'] === 'Pendiente de Recuperación')
                                                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800 flex items-center gap-1">
                                                    <i class="fas fa-clock text-[10px]"></i>
                                                    Pendiente Recuperación
                                                </span>
                                            @elseif($clase['estado'] === 'Recuperada')
                                                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-emerald-100 text-emerald-800 flex items-center gap-1">
                                                    <i class="fas fa-check-double text-[10px]"></i>
                                                    Recuperada
                                                </span>
                                            @elseif($clase['estado'] === 'Justificada' || $clase['estado'] === 'Feriado/Justificado')
                                                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                                    {{ $clase['estado'] }}
                                                </span>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="px-3 py-4 text-sm text-gray-500 whitespace-nowrap text-center">
                                        <div class="break-words text-center">
                                            @if(!empty($clase['hora_inicio']) && !empty($clase['hora_fin']))<div class="font-medium text-gray-800">{{ \Carbon\Carbon::parse($clase['fecha'])->format('d/m/Y') }}</div><div class="text-xs text-gray-500">{{ ($clase['id_modulo_raw'] ?? $clase['modulo']) }} ({{ substr($clase['hora_inicio'], 0, 5) }} - {{ substr($clase['hora_fin'], 0, 5) }})</div>@elseif(isset($clase['hora_deteccion']))
                                                <div>{{ \Carbon\Carbon::parse($clase['hora_deteccion'])->format('d/m/Y') }}</div>
                                                <div class="text-xs">{{ \Carbon\Carbon::parse($clase['hora_deteccion'])->format('H:i') }}</div>
                                            @else
                                                <span class="text-xs text-gray-400">N/A</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="px-4 py-4 text-center align-middle whitespace-nowrap">
                                        <div class="flex items-center justify-center gap-1.5 flex-wrap">
                                            @if($clase['estado'] === 'No Registrada')
                                                <button wire:click="prepararAccion('reagendar', {{ json_encode($clase) }})" 
                                                        class="inline-flex items-center gap-1.5 px-2.5 py-1 text-xs font-semibold text-blue-700 bg-blue-50 border border-blue-200 rounded-lg hover:bg-blue-600 hover:text-white transition-colors shadow-xs"
                                                        title="Reagendar Clase">
                                                    <i class="fas fa-calendar-plus text-xs"></i>
                                                    <span>Reagendar</span>
                                                </button>
                                            @endif
                                            @if($clase['estado'] === 'Pendiente de Recuperación' || $clase['estado'] === 'No Registrada')
                                                <button wire:click="prepararAccion('recuperada', {{ json_encode($clase) }})" 
                                                        class="inline-flex items-center gap-1.5 px-2.5 py-1 text-xs font-semibold text-emerald-700 bg-emerald-50 border border-emerald-200 rounded-lg hover:bg-emerald-600 hover:text-white transition-colors shadow-xs"
                                                        title="Marcar como recuperada">
                                                    <i class="fas fa-check-circle text-xs"></i>
                                                    <span>Recuperada</span>
                                                </button>
                                            @endif
                                            <button wire:click="prepararAccion('editar', {{ json_encode($clase) }})" 
                                                    class="inline-flex items-center gap-1.5 px-2.5 py-1 text-xs font-semibold text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-100 hover:text-[#950101] transition-colors shadow-xs"
                                                    title="Editar">
                                                <i class="fas fa-edit text-xs"></i>
                                                <span>Editar</span>
                                            </button>
                                            <button wire:click="prepararAccion('eliminar', {{ json_encode($clase) }})" 
                                                    class="inline-flex items-center gap-1.5 px-2.5 py-1 text-xs font-semibold text-[#D12420] bg-red-50 border border-red-200 rounded-lg hover:bg-[#D12420] hover:text-white transition-colors shadow-xs"
                                                    title="Eliminar">
                                                <i class="fas fa-trash text-xs"></i>
                                                <span>Eliminar</span>
                                            </button>
                                            <button wire:click="prepararAccion('historial', {{ json_encode($clase) }})" 
                                                    class="inline-flex items-center gap-1.5 px-2.5 py-1 text-xs font-semibold text-slate-700 bg-slate-50 border border-slate-300 rounded-lg hover:bg-slate-700 hover:text-white transition-colors shadow-xs"
                                                    title="Ver Historial de Auditoría">
                                                <i class="fas fa-history text-xs"></i>
                                                <span>Auditoría</span>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="{{ $this->puedeGestionarClases ? 10 : 9 }}" class="px-6 py-12 text-center text-gray-500">
                                        <div class="flex flex-col items-center">
                                            <i class="fas fa-calendar-times text-4xl text-gray-300 mb-4"></i>
                                            <p class="text-lg font-medium">No se encontraron registros</p>
                                            <p class="text-sm">No hay registros con los filtros aplicados en el Control de Clases.</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Paginación -->
                @if($clasesNoRealizadas->hasPages())
                    <div class="px-6 py-3 border-t border-gray-200">
                        {{ $clasesNoRealizadas->links() }}
                    </div>
                @endif
            </div>

            <!-- Modal Unificado de Acción Masiva (Diseño Institucional, Alpine + wire:ignore) -->
            <div wire:ignore
                 x-data="{
                    open: false,
                    cantidad: 0,
                    tipo: 'justificado',
                    estadoOrden: ['justificado', 'realizada', 'no_realizada', 'pendiente', 'recuperada'],
                    motivoSeleccionado: 'Supervisión de Prácticas / Salida a Terreno',
                    otroMotivo: '',
                    obs: '',
                    sobrescribir: false,
                    estados: {
                        justificado: {
                            label: 'Justificada',
                            titulo: 'Justificar Ausencia',
                            btnTexto: 'Justificar',
                            loading: 'Aplicando justificación masiva...',
                            desc: 'Pasa a estado Justificada. No exige recuperación horaria.',
                            badgeClass: 'bg-amber-100 text-amber-800 border-amber-200',
                            activeCard: 'border-slate-800 bg-slate-50/70',
                            dotActive: 'border-slate-800 bg-slate-800',
                            btnClass: 'bg-slate-800 hover:bg-slate-900',
                            iconClass: 'fas fa-shield-alt text-amber-600',
                            defaultMotivo: 'Supervisión de Prácticas / Salida a Terreno',
                            motivos: [
                                'Supervisión de Prácticas / Salida a Terreno',
                                'Licencia Médica Docente',
                                'Comisión de Servicio / Capacitación Institucional',
                                'Suspensión de Actividades Institucionales / Feriado',
                                'Problema Técnico o de Infraestructura en Aula',
                                'Clase Realizada en Modalidad Online / Alternativa'
                            ]
                        },
                        realizada: {
                            label: 'Realizada / Registrada',
                            titulo: 'Regularizar / Realizada',
                            btnTexto: 'Registrar',
                            loading: 'Aplicando registro masivo...',
                            desc: 'Pasa a estado Realizada. El docente sí dictó la clase.',
                            badgeClass: 'bg-emerald-100 text-emerald-800 border-emerald-200',
                            activeCard: 'border-emerald-600 bg-emerald-50/60',
                            dotActive: 'border-emerald-600 bg-emerald-600',
                            btnClass: 'bg-emerald-600 hover:bg-emerald-700',
                            iconClass: 'fas fa-check-circle text-emerald-600',
                            defaultMotivo: 'Docente dictó la clase en aula (asistencia presencial)',
                            motivos: [
                                'Docente dictó la clase en aula (asistencia presencial)',
                                'Firma en libro de asistencia / Registro manual validado',
                                'Falla técnica en escaneo de código QR en sala',
                                'Validación directa de Jefatura de Carrera / Dirección',
                                'Recuperación de clase efectuada'
                            ]
                        },
                        no_realizada: {
                            label: 'No Registrada',
                            titulo: 'Marcar No Registrada',
                            btnTexto: 'Marcar No Registrada',
                            loading: 'Marcando clases como no registradas...',
                            desc: 'Pasa a estado No Registrada para seguimiento o recuperación.',
                            badgeClass: 'bg-red-100 text-red-800 border-red-200',
                            activeCard: 'border-red-600 bg-red-50/60',
                            dotActive: 'border-red-600 bg-red-600',
                            btnClass: 'bg-[#D12420] hover:bg-[#b51d1a]',
                            iconClass: 'fas fa-times-circle text-red-600',
                            defaultMotivo: 'Cambio manual masivo a No Registrada',
                            motivos: [
                                'Cambio manual masivo a No Registrada',
                                'Reversión de registro o justificación errónea',
                                'Inasistencia docente confirmada sin registro previo',
                                'Error administrativo en asignación de asistencia'
                            ]
                        },
                        pendiente: {
                            label: 'Pendiente de Recuperación',
                            titulo: 'Pendiente de Recuperación',
                            btnTexto: 'Marcar Pendiente',
                            loading: 'Marcando clases como pendientes...',
                            desc: 'Pasa a estado Pendiente de Recuperación.',
                            badgeClass: 'bg-orange-100 text-orange-800 border-orange-200',
                            activeCard: 'border-orange-500 bg-orange-50/60',
                            dotActive: 'border-orange-500 bg-orange-500',
                            btnClass: 'bg-amber-600 hover:bg-amber-700',
                            iconClass: 'fas fa-clock text-orange-600',
                            defaultMotivo: 'Pendiente de recuperación por ausencia justificada/aviso',
                            motivos: [
                                'Pendiente de recuperación por ausencia justificada/aviso',
                                'Clase suspendida sujeta a recalendarización',
                                'Acuerdo docente-jefatura para recuperar módulo'
                            ]
                        },
                        recuperada: {
                            label: 'Recuperada',
                            titulo: 'Marcar como Recuperada',
                            btnTexto: 'Marcar Recuperada',
                            loading: 'Confirmando recuperación masiva...',
                            desc: 'Pasa a estado Recuperada y sincroniza recuperaciones.',
                            badgeClass: 'bg-blue-100 text-blue-800 border-blue-200',
                            activeCard: 'border-blue-600 bg-blue-50/60',
                            dotActive: 'border-blue-600 bg-blue-600',
                            btnClass: 'bg-blue-600 hover:bg-blue-700',
                            iconClass: 'fas fa-calendar-check text-blue-600',
                            defaultMotivo: 'Clase recuperada confirmada por Jefatura / Dirección',
                            motivos: [
                                'Clase recuperada confirmada por Jefatura / Dirección',
                                'Recuperación realizada en horario alternativo validado',
                                'Asistencia de recuperación verificada manualmente'
                            ]
                        }
                    },
                    normalizarTipo(raw) {
                        if (raw === 'justificar') return 'justificado';
                        if (raw === 'registrar' || raw === 'registrada') return 'realizada';
                        return this.estados[raw] ? raw : 'justificado';
                    },
                    cambiarTipo(nuevoTipo) {
                        const t = this.normalizarTipo(nuevoTipo);
                        this.tipo = t;
                        this.otroMotivo = '';
                        this.$nextTick(() => {
                            this.motivoSeleccionado = this.estados[t].defaultMotivo;
                        });
                    },
                    abrir(cant, tipoInicial) {
                        this.cantidad = Number(cant) || 0;
                        this.obs = '';
                        this.sobrescribir = false;
                        this.cambiarTipo(tipoInicial || 'justificado');
                        this.open = true;
                    },
                    confirmar() {
                        const motivo = this.motivoSeleccionado === '__OTRO__' ? this.otroMotivo.trim() : this.motivoSeleccionado;
                        if (!motivo) {
                            Swal.fire({ title: 'Atención', text: 'Por favor especifica un motivo para la acción masiva.', icon: 'warning', confirmButtonColor: '#D12420' });
                            return;
                        }
                        const cfg = this.estados[this.tipo];
                        this.open = false;
                        Swal.fire({
                            title: cfg.loading,
                            text: `Procesando ${this.cantidad} clases hacia ${cfg.label}, por favor espera...`,
                            allowOutsideClick: false,
                            didOpen: () => Swal.showLoading()
                        });
                        $wire.ejecutarCambioEstadoMasivo(this.tipo, motivo, this.obs.trim(), this.sobrescribir);
                    }
                 }"
                 x-on:abrir-modal-masivo.window="abrir($event.detail.cantidad, $event.detail.tipo)"
                 x-on:show-bulk-action-modal.window="abrir(($event.detail[0] || $event.detail).cantidad, ($event.detail[0] || $event.detail).tipo)"
                 x-on:keydown.escape.window="open = false">
                <div x-show="open" x-cloak x-transition.opacity
                     class="fixed inset-0 z-[9999] bg-black/60 backdrop-blur-xs flex items-center justify-center p-4"
                     x-on:click.self="open = false">
                    <div class="bg-white rounded-2xl shadow-2xl max-w-2xl w-full border border-gray-200 overflow-hidden flex flex-col max-h-[90vh]">
                        <!-- Cabecera Institucional -->
                        <div class="p-4 bg-[#D12420] text-white flex items-center justify-between">
                            <div class="flex items-center gap-2.5">
                                <div class="p-1.5 bg-white/20 rounded-lg">
                                    <i class="fas fa-exchange-alt text-lg"></i>
                                </div>
                                <div>
                                    <h3 class="text-base font-bold leading-tight">Cambio Masivo de Estado</h3>
                                    <p class="text-xs text-white/80">Aplica el estado seleccionado a múltiples clases en una sola operación</p>
                                </div>
                            </div>
                            <button type="button" x-on:click="open = false" class="text-white/80 hover:text-white hover:bg-white/10 p-1.5 rounded-lg transition-colors cursor-pointer">
                                <i class="fas fa-times text-lg"></i>
                            </button>
                        </div>

                        <!-- Cuerpo -->
                        <div class="p-6 overflow-y-auto space-y-5 text-gray-800 text-sm">
                            <div class="p-3.5 bg-slate-50 border border-slate-200 rounded-xl flex items-center justify-between gap-3">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-lg bg-slate-200 text-slate-700 flex items-center justify-center">
                                        <i class="fas fa-layer-group"></i>
                                    </div>
                                    <div>
                                        <p class="text-xs text-gray-500 font-medium">Clases seleccionadas para actualizar</p>
                                        <p class="text-base font-extrabold text-gray-900">
                                            <span x-text="cantidad"></span>
                                            <span x-text="cantidad === 1 ? 'clase seleccionada' : 'clases seleccionadas'"></span>
                                        </p>
                                    </div>
                                </div>
                                <span :class="estados[tipo].badgeClass"
                                      class="text-xs px-2.5 py-1 font-semibold rounded-full border shrink-0"
                                      x-text="estados[tipo].label"></span>
                            </div>

                            <!-- Selector de Estado Destino -->
                            <div>
                                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">Nuevo estado destino <span class="text-red-500">*</span></label>
                                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-2.5">
                                    <template x-for="key in estadoOrden" :key="key">
                                        <div x-on:click="cambiarTipo(key)"
                                             :class="tipo === key ? estados[key].activeCard : 'border-gray-200 bg-white hover:border-gray-300'"
                                             class="relative flex flex-col p-3 border-2 rounded-xl cursor-pointer transition-all">
                                            <div class="flex items-center justify-between mb-1">
                                                <span class="inline-flex items-center gap-1.5 text-xs font-bold text-gray-900">
                                                    <i :class="estados[key].iconClass"></i>
                                                    <span x-text="estados[key].titulo"></span>
                                                </span>
                                                <span :class="tipo === key ? estados[key].dotActive : 'border-gray-300 bg-white'"
                                                      class="w-4 h-4 rounded-full border-2 flex items-center justify-center shrink-0">
                                                    <span x-show="tipo === key" class="w-1.5 h-1.5 rounded-full bg-white"></span>
                                                </span>
                                            </div>
                                            <p class="text-[11px] text-gray-500 leading-snug" x-text="estados[key].desc"></p>
                                        </div>
                                    </template>
                                </div>
                            </div>

                            <!-- Motivo Dinámico -->
                            <div class="space-y-2">
                                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider">
                                    Motivo del cambio (<span x-text="estados[tipo].label"></span>) <span class="text-red-500">*</span>
                                </label>
                                <select x-model="motivoSeleccionado" class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-slate-800 focus:border-slate-800 bg-white">
                                    <template x-for="m in (estados[tipo]?.motivos || [])" :key="m">
                                        <option :value="m" x-text="m"></option>
                                    </template>
                                    <option value="__OTRO__">Otro motivo (especificar abajo)...</option>
                                </select>
                                <input type="text" x-show="motivoSeleccionado === '__OTRO__'" x-model="otroMotivo" placeholder="Describe el motivo del cambio..."
                                       class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-slate-800 focus:border-slate-800 mt-2">
                            </div>

                            <!-- Observaciones -->
                            <div>
                                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">
                                    Observaciones / Respaldo <span class="text-gray-400 font-normal lowercase">(opcional)</span>
                                </label>
                                <textarea x-model="obs" rows="2" maxlength="1000" placeholder="Agrega notas, número de oficio, documento o antecedentes relevantes..."
                                          class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-slate-800 focus:border-slate-800 resize-none"></textarea>
                            </div>

                            <!-- Sobrescribir -->
                            <label class="flex items-start gap-2.5 p-3 bg-gray-50 border border-gray-200 rounded-xl cursor-pointer">
                                <input type="checkbox" x-model="sobrescribir" class="mt-0.5 rounded border-gray-300 text-slate-800 focus:ring-slate-800">
                                <span class="text-xs text-gray-600">
                                    <strong>Sobrescribir observaciones previas:</strong> si no se marca, el texto se añadirá al final de las observaciones existentes.
                                </span>
                            </label>
                        </div>

                        <!-- Footer -->
                        <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex items-center justify-end gap-3 rounded-b-2xl">
                            <button type="button" x-on:click="open = false"
                                    class="border border-gray-300 bg-white text-gray-700 hover:bg-gray-50 active:bg-gray-100 focus:ring-gray-300 rounded-md font-medium text-sm gap-2 inline-flex items-center justify-center shadow-sm px-4 py-2 cursor-pointer transition-all duration-150">
                                <i class="fas fa-times"></i>
                                <span>Cancelar</span>
                            </button>
                            <button type="button" x-on:click="confirmar()"
                                    :class="estados[tipo].btnClass"
                                    class="inline-flex items-center justify-center px-4 py-2 text-white text-sm font-semibold rounded-md shadow-sm transition-all duration-150 gap-2 cursor-pointer">
                                <i class="fas fa-check"></i>
                                <span x-text="estados[tipo].btnTexto + ' (' + cantidad + (cantidad === 1 ? ' Clase)' : ' Clases)')"></span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

@endif
</div>

@push('scripts')
<script>
function initClasesNoRealizadasScripts() {
    // Listener para abrir modal automáticamente desde URL
    Livewire.on('auto-open-reagendar', (data) => {
        const id = data[0]?.id || data.id;
        if (id) {
            // Pequeño delay para asegurar que la página esté lista
            setTimeout(() => {
                window.Livewire.find('{{ $this->getId() }}').call('showReagendarModal', id);
            }, 500);
        }
    });

    Livewire.on('show-edit-modal', (data) => {
        const clase = data[0];
        
        Swal.fire({
            title: '<strong>Editar Estado de Clase</strong>',
            html: `
                <div class="text-left space-y-4">
                    <div class="bg-gray-50 p-3 rounded-lg mb-4">
                        <p><strong>Profesor:</strong> ${clase.profesor}</p>
                        <p><strong>Asignatura:</strong> ${clase.asignatura}${clase.seccion && clase.seccion !== 'N/A' ? ' (Sec. ' + clase.seccion + ')' : ''}</p>
                        ${clase.ua && clase.ua !== 'N/A' ? `<p><strong>UA:</strong> ${clase.ua}</p>` : ''}
                        <p><strong>Fecha:</strong> ${clase.fecha}</p>
                        <p><strong>Espacio:</strong> ${clase.espacio}</p>
                    </div>
                    
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Estado</label>
                        <select id="swal-estado" class="w-full p-2 border border-gray-300 rounded-md">
                            <option value="no_realizada" ${clase.estado === 'no_realizada' ? 'selected' : ''}>No registrada</option>
                            <option value="realizada" ${clase.estado === 'realizada' || clase.estado === 'registrada' ? 'selected' : ''}>Registrada (Docente realizó la clase)</option>
                            <option value="pendiente" ${clase.estado === 'pendiente' ? 'selected' : ''}>Pendiente de recuperación</option>
                            <option value="justificado" ${clase.estado === 'justificado' ? 'selected' : ''}>Justificado</option>
                            <option value="recuperada" ${clase.estado === 'recuperada' ? 'selected' : ''}>Recuperada</option>
                        </select>
                        <p class="text-xs text-gray-500 mt-1">
                            <strong>Registrada:</strong> Docente dictó la clase (asistencia regularizada)<br>
                            <strong>Pendiente:</strong> Clase reagendada esperando recuperación<br>
                            <strong>Justificado:</strong> Clase justificada<br>
                            <strong>Recuperada:</strong> Recuperación confirmada
                        </p>
                    </div>
                    
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Observaciones</label>
                        <textarea id="swal-observaciones" rows="4" class="w-full p-2 border border-gray-300 rounded-md resize-none" placeholder="Ingrese observaciones...">${clase.observaciones || ''}</textarea>
                    </div>
                </div>
            `,
            width: 600,
            showCancelButton: true,
            confirmButtonText: '<i class="fas fa-save"></i> Guardar',
            cancelButtonText: '<i class="fas fa-times"></i> Cancelar',
            confirmButtonColor: '#10B981',
            cancelButtonColor: '#8D8D8D',
            didOpen: () => {
                document.getElementById('swal-estado').focus();
            },
            preConfirm: () => {
                const estado = document.getElementById('swal-estado').value;
                const observaciones = document.getElementById('swal-observaciones').value;
                
                if (!estado) {
                    Swal.showValidationMessage('Por favor selecciona un estado');
                    return false;
                }
                
                if (observaciones.length > 1000) {
                    Swal.showValidationMessage('Las observaciones no pueden exceder 1000 caracteres');
                    return false;
                }
                
                return { estado, observaciones };
            }
        }).then((result) => {
            if (result.isConfirmed) {
                const { estado, observaciones } = result.value;
                window.Livewire.find('{{ $this->getId() }}').call('updateClase', clase.id, estado, observaciones);
            }
        });
    });

    Livewire.on('show-reagendar-modal', (data) => {
        const clase = data[0];
        
        console.log('Datos completos recibidos:', clase);
        
        // Variable global para almacenar módulos
        let modulosDisponibles = [];
        const fechaOriginal = clase.fecha_original; // formato: "dd/mm/yyyy"

        Swal.fire({
            title: '<strong><i class="fas fa-calendar-plus"></i> Reagendar Clase</strong>',
            html: `
                <div class="text-left space-y-4">
                    <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-4">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <i class="fas fa-exclamation-triangle text-yellow-400 text-xl"></i>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm text-yellow-700">
                                    <strong>Importante:</strong> Al reagendar esta clase, se marcará como <strong>justificada</strong> 
                                    pero quedará <strong>pendiente de recuperación</strong>. La clase permanecerá en el listado 
                                    hasta que se confirme su realización.
                                </p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-slate-50 border border-slate-200 p-4 rounded-lg mb-4">
                        <h4 class="font-semibold text-slate-900 mb-2">Clase Original</h4>
                        <p><strong>Profesor:</strong> ${clase.profesor}</p>
                        <p><strong>Asignatura:</strong> ${clase.asignatura}${clase.seccion && clase.seccion !== 'N/A' ? ' (Sec. ' + clase.seccion + ')' : ''}</p>
                        ${clase.ua && clase.ua !== 'N/A' ? `<p><strong>UA:</strong> ${clase.ua}</p>` : ''}
                        <p><strong>Fecha:</strong> ${clase.fecha_original}</p>
                        <p><strong>Espacio:</strong> ${clase.espacio_original}</p>
                        <p><strong>Módulo:</strong> ${clase.modulo_original}</p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Nueva Fecha <span class="text-red-500">*</span></label>
                            <input type="date" id="swal-nueva-fecha" class="w-full p-2 border border-gray-300 rounded-md">
                        </div>
                        
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Cantidad de Módulos <span class="text-red-500">*</span></label>
                            <input type="number" id="swal-cantidad-modulos" class="w-full p-2 border border-gray-300 rounded-md" min="1" max="8" value="${clase.totalModulosProgramados}">
                        </div>
                        
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Módulo Inicio <span class="text-red-500">*</span></label>
                            <select id="swal-nuevo-modulo-inicio" class="w-full p-2 border border-gray-300 rounded-md">
                                <option value="">Cargando módulos...</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Módulo Final (Automático)</label>
                        <div class="p-3 bg-gray-50 border border-gray-300 rounded-md">
                            <span id="swal-modulo-final-display" class="text-gray-700">Selecciona un módulo inicial</span>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Nuevo Espacio <span class="text-red-500">*</span></label>
                        <select id="swal-nuevo-espacio" class="w-full p-2 border border-gray-300 rounded-md">
                            <option value="">Selecciona fecha y módulo primero</option>
                        </select>
                        <p id="espacios-disponibles-info" class="text-xs text-gray-500 mt-1"></p>
                    </div>
                    
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Motivo del reagendamiento</label>
                        <textarea id="swal-motivo-reagendamiento" rows="3" class="w-full p-2 border border-gray-300 rounded-md resize-none" placeholder="Explique el motivo del reagendamiento..."></textarea>
                    </div>
                </div>
            `,
            width: 700,
            showCancelButton: true,
            confirmButtonText: '<i class="fas fa-calendar-check"></i> Reagendar',
            cancelButtonText: '<i class="fas fa-times"></i> Cancelar',
            confirmButtonColor: '#10B981',
            cancelButtonColor: '#8D8D8D',
            didOpen: async () => {
                const dateInput = document.getElementById('swal-nueva-fecha');
                const hoy = new Date().toISOString().split('T')[0];
                dateInput.min = hoy;
                dateInput.focus();
                
                // Cargar módulos desde la API
                try {
                    const response = await fetch('/api/modulos');
                    modulosDisponibles = await response.json();
                    console.log('Módulos cargados:', modulosDisponibles);
                    
                    // Llenar select de módulos iniciales inicialmente vacío
                    const selectModuloInicio = document.getElementById('swal-nuevo-modulo-inicio');
                    selectModuloInicio.innerHTML = '<option value="">Selecciona fecha primero</option>';
                    selectModuloInicio.disabled = true;

                    // Función para filtrar módulos por día
                    const filtrarModulosPorDia = (fechaStr) => {
                        if (!fechaStr) {
                            selectModuloInicio.innerHTML = '<option value="">Selecciona fecha primero</option>';
                            selectModuloInicio.disabled = true;
                            return;
                        }

                        const fecha = new Date(fechaStr + 'T12:00:00');
                        const prefijos = ['DO', 'LU', 'MA', 'MI', 'JU', 'VI', 'SA'];
                        const prefijo = prefijos[fecha.getDay()];

                        // Filtrar y ordenar módulos
                        const modulosFiltrados = modulosDisponibles
                            .filter(m => m.id_modulo.startsWith(prefijo + '.'))
                            .sort((a, b) => {
                                const numA = parseInt(a.id_modulo.split('.')[1]);
                                const numB = parseInt(b.id_modulo.split('.')[1]);
                                return numA - numB;
                            });

                        if (modulosFiltrados.length === 0) {
                            selectModuloInicio.innerHTML = '<option value="">No hay módulos para este día</option>';
                            selectModuloInicio.disabled = true;
                        } else {
                            selectModuloInicio.innerHTML = '<option value="">Seleccionar módulo</option>';
                            modulosFiltrados.forEach(modulo => {
                                const option = document.createElement('option');
                                option.value = modulo.id_modulo;
                                option.textContent = `${modulo.id_modulo} (${modulo.hora_inicio} - ${modulo.hora_termino})`;
                                selectModuloInicio.appendChild(option);
                            });
                            selectModuloInicio.disabled = false;
                        }
                    };

                    // Listener para cambio de fecha
                    dateInput.addEventListener('change', (e) => {
                        filtrarModulosPorDia(e.target.value);
                        // Limpiar espacio e info si cambia la fecha
                        document.getElementById('swal-nuevo-espacio').innerHTML = '<option value="">Selecciona fecha y módulo primero</option>';
                        const infoP = document.getElementById('espacios-disponibles-info');
                        if (infoP) infoP.textContent = '';
                    });
                } catch (error) {
                    console.error('Error cargando módulos:', error);
                }
                
                // Variables para controlar el cálculo
                const moduloInicio = document.getElementById('swal-nuevo-modulo-inicio');
                const moduloFinalDisplay = document.getElementById('swal-modulo-final-display');
                const cantidadModulos = document.getElementById('swal-cantidad-modulos');
                const dateInput2 = document.getElementById('swal-nueva-fecha');
                const selectEspacios = document.getElementById('swal-nuevo-espacio');
                const infoParagraph = document.getElementById('espacios-disponibles-info');
                
                // Función para cargar espacios disponibles
                const cargarEspaciosDisponibles = async () => {
                    const fecha = dateInput2.value;
                    const modulo = moduloInicio.value;
                    
                    if (!fecha || !modulo) {
                        selectEspacios.innerHTML = '<option value="">Selecciona fecha y módulo primero</option>';
                        infoParagraph.textContent = '';
                        return;
                    }

                    // Validar que no sea el mismo día
                    const [dia, mes, anio] = fechaOriginal.split('/');
                    const fechaOriginalFormato = anio + '-' + mes + '-' + dia;
                    if (fecha === fechaOriginalFormato) {
                        selectEspacios.innerHTML = '<option value="">No puedes reagendar para el mismo día</option>';
                        infoParagraph.textContent = 'El reagendamiento debe ser en una fecha diferente';
                        return;
                    }

                    // Calcular módulo final
                    const partesModulo = modulo.split('.');
                    const inicio = parseInt(partesModulo[1] || partesModulo[0]);
                    const prefijo = partesModulo[0];
                    const cantidad = parseInt(cantidadModulos.value) || clase.totalModulosProgramados;
                    const fin = inicio + cantidad - 1;
                    const moduloFinalId = prefijo + '.' + fin;

                    try {
                        const url = `/api/espacios-disponibles/${fecha}/${modulo}/${moduloFinalId}`;
                        const response = await fetch(url);
                        const data = await response.json();
                        
                        if (data.error) {
                            selectEspacios.innerHTML = '<option value="">Error: ' + data.error + '</option>';
                            infoParagraph.textContent = '';
                            return;
                        }

                        console.log('Espacios disponibles:', data);

                        if (data.espacios.length === 0) {
                            selectEspacios.innerHTML = '<option value="">No hay espacios disponibles para esta fecha y módulos</option>';
                            infoParagraph.textContent = 'Intenta con otra fecha o módulo';
                            return;
                        }

                        selectEspacios.innerHTML = '<option value="">Seleccionar espacio</option>';
                        data.espacios.forEach(espacio => {
                            const option = document.createElement('option');
                            option.value = espacio.id_espacio;
                            option.textContent = espacio.display_name;
                            selectEspacios.appendChild(option);
                        });

                        infoParagraph.textContent = 'Disponibles: ' + data.total_disponibles + ' espacio(s) • Módulo: ' + data.hora_inicio + ' - ' + data.hora_fin;
                    } catch (error) {
                        console.error('Error cargando espacios:', error);
                        selectEspacios.innerHTML = '<option value="">Error al cargar espacios</option>';
                    }
                };
                
                // Función para recalcular el módulo final y mostrar horarios
                const recalcularModuloFinal = () => {
                    if (moduloInicio.value && modulosDisponibles.length > 0) {
                        const partesModulo = moduloInicio.value.split('.');
                        const inicio = parseInt(partesModulo[1] || partesModulo[0]);
                        const prefijo = partesModulo[0];
                        const cantidad = parseInt(cantidadModulos.value) || clase.totalModulosProgramados;
                        const fin = inicio + cantidad - 1;
                        const moduloFinalId = prefijo + '.' + fin;
                        
                        // Buscar horarios de inicio y fin
                        const moduloInicial = modulosDisponibles.find(m => m.id_modulo == moduloInicio.value);
                        const moduloFinal = modulosDisponibles.find(m => m.id_modulo == moduloFinalId);
                        
                        if (moduloInicial && moduloFinal) {
                            const texto = 'Módulos ' + inicio + ' - ' + fin + ' (' + moduloInicial.hora_inicio + ' - ' + moduloFinal.hora_termino + ')';
                            moduloFinalDisplay.textContent = texto;
                        }
                        
                        // Cargar espacios disponibles cuando se selecciona módulo
                        cargarEspaciosDisponibles();
                    } else {
                        moduloFinalDisplay.textContent = 'Selecciona un módulo inicial';
                    }
                };
                
                dateInput2.addEventListener('change', cargarEspaciosDisponibles);
                moduloInicio.addEventListener('change', recalcularModuloFinal);
                cantidadModulos.addEventListener('change', recalcularModuloFinal);
                cantidadModulos.addEventListener('input', recalcularModuloFinal);
            },
            preConfirm: () => {
                const nuevaFecha = document.getElementById('swal-nueva-fecha').value;
                const nuevoEspacio = document.getElementById('swal-nuevo-espacio').value;
                const nuevoModulo = document.getElementById('swal-nuevo-modulo-inicio').value;
                const cantidadModulos = document.getElementById('swal-cantidad-modulos').value;
                const motivo = document.getElementById('swal-motivo-reagendamiento').value;
                
                if (!nuevaFecha) {
                    Swal.showValidationMessage('Por favor selecciona una fecha');
                    return false;
                }
                
                // Validar que no sea el mismo día
                const [dia, mes, anio] = fechaOriginal.split('/');
                const fechaOriginalFormato = anio + '-' + mes + '-' + dia;
                if (nuevaFecha === fechaOriginalFormato) {
                    Swal.showValidationMessage('No puedes reagendar para el mismo día');
                    return false;
                }
                
                if (!nuevoEspacio) {
                    Swal.showValidationMessage('Por favor selecciona un espacio');
                    return false;
                }
                
                if (!nuevoModulo) {
                    Swal.showValidationMessage('Por favor selecciona un módulo de inicio');
                    return false;
                }
                
                if (!cantidadModulos || parseInt(cantidadModulos) < 1 || parseInt(cantidadModulos) > 15) {
                    Swal.showValidationMessage('Por favor ingresa una cantidad válida de módulos (1-15)');
                    return false;
                }
                
                return { nuevaFecha, nuevoEspacio, nuevoModulo, cantidadModulos, motivo };
            }
        }).then((result) => {
            if (result.isConfirmed) {
                const { nuevaFecha, nuevoEspacio, nuevoModulo, cantidadModulos, motivo } = result.value;
                window.Livewire.find('{{ $this->getId() }}').call('reagendarClase', clase.id, nuevaFecha, nuevoEspacio, nuevoModulo, cantidadModulos, motivo);
            }
        });
    });

    Livewire.on('confirm-delete', (data) => {
        const clase = data[0];
        
        Swal.fire({
            title: '¿Eliminar registro?',
            html: `
                <div class="text-left">
                    <p class="mb-2"><strong>Profesor:</strong> ${clase.profesor}</p>
                    <p class="mb-2"><strong>Asignatura:</strong> ${clase.asignatura}${clase.seccion && clase.seccion !== 'N/A' ? ' (Sec. ' + clase.seccion + ')' : ''}</p>
                    <p class="mb-2"><strong>Fecha:</strong> ${clase.fecha}</p>
                    <br>
                    <p class="text-red-600"><strong>Esta acción no se puede deshacer</strong></p>
                </div>
            `,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: '<i class="fas fa-trash"></i> Sí, eliminar',
            cancelButtonText: '<i class="fas fa-times"></i> Cancelar',
            confirmButtonColor: '#D12420',
            cancelButtonColor: '#8D8D8D',
        }).then((result) => {
            if (result.isConfirmed) {
                window.Livewire.find('{{ $this->getId() }}').call('confirmDelete', clase.id);
            }
        });
    });

    Livewire.on('show-success', (data) => {
        const payload = Array.isArray(data) ? data[0] : data;
        const message = payload?.message || 'Operación realizada exitosamente';
        
        Swal.fire({
            title: '¡Éxito!',
            text: message,
            icon: 'success',
            confirmButtonText: 'Entendido',
            confirmButtonColor: '#10B981',
            timer: 3000,
            timerProgressBar: true
        });
    });

    Livewire.on('show-error', (data) => {
        const payload = Array.isArray(data) ? data[0] : data;
        const message = payload?.message || 'Ha ocurrido un error inesperado';
        
        Swal.fire({
            title: 'Error',
            text: message,
            icon: 'error',
            confirmButtonText: 'Entendido',
            confirmButtonColor: '#D12420'
        });
    });

    Livewire.on('abrir-modal-historial-auditoria', (data) => {
        const payload = Array.isArray(data) ? data[0] : data;
        const historial = payload?.historial || [];
        
        let htmlContent = `
            <div class="text-left text-xs text-slate-700 max-h-96 overflow-y-auto">
                <div class="mb-3 p-3 bg-slate-100 rounded-xl border border-slate-200">
                    <p class="font-bold text-slate-900">${payload.profesor}</p>
                    <p class="text-slate-600">${payload.asignatura}${payload.seccion && payload.seccion !== 'N/A' ? ' (Sec. ' + payload.seccion + ')' : ''}</p>
                    <p class="text-[11px] text-slate-500 mt-0.5">Fecha de la clase: <strong>${payload.fecha}</strong></p>
                </div>
        `;
        
        if (historial.length === 0) {
            htmlContent += `
                <div class="text-center py-6 text-slate-400">
                    <i class="fas fa-history text-2xl mb-2 text-slate-300"></i>
                    <p>No se registran cambios previos para esta clase.</p>
                </div>
            `;
        } else {
            htmlContent += `<div class="space-y-2.5">`;
            historial.forEach((item) => {
                htmlContent += `
                    <div class="p-3 bg-white border border-slate-200 rounded-xl shadow-2xs">
                        <div class="flex justify-between items-center mb-1">
                            <span class="font-extrabold text-slate-900 text-xs">${item.accion}</span>
                            <span class="text-[10px] text-slate-400 font-mono">${item.fecha_hora}</span>
                        </div>
                        <p class="text-[11px] text-slate-600"><strong>Por:</strong> ${item.usuario} <span class="text-slate-400">(${item.rol})</span></p>
                        <p class="text-[11px] text-slate-600"><strong>Transición:</strong> <span class="line-through text-slate-400">${item.estado_anterior}</span> &rarr; <span class="font-bold text-emerald-700">${item.estado_nuevo}</span></p>
                        ${item.motivo ? `<p class="text-[11px] text-slate-800 mt-1"><strong>Motivo:</strong> ${item.motivo}</p>` : ''}
                        ${item.observaciones ? `<p class="text-[10px] text-slate-500 mt-1 p-2 bg-slate-50 rounded-lg border border-slate-100 italic">${item.observaciones}</p>` : ''}
                    </div>
                `;
            });
            htmlContent += `</div>`;
        }
        
        htmlContent += `</div>`;
        
        Swal.fire({
            title: '<i class="fas fa-history text-slate-700 mr-2"></i> Historial de Auditoría',
            html: htmlContent,
            width: '600px',
            confirmButtonText: 'Cerrar',
            confirmButtonColor: '#8D8D8D',
        });
    });
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initClasesNoRealizadasScripts);
} else {
    initClasesNoRealizadasScripts();
}
</script>
@endpush
