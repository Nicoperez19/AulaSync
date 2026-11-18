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

            {{-- Barra de Acciones Masivas + Tabla con selección instantánea en cliente --}}
            <div x-data="{
                selected: $wire.entangle('selectedClases'),
                get pageKeys() {
                    const tabla = this.$refs.tablaClases;
                    if (!tabla) return [];
                    return Array.from(tabla.querySelectorAll('input[data-clase-checkbox]')).map(el => el.value);
                },
                get totalSelected() {
                    if ($wire.selectAllFiltered) {
                        return Math.max(0, {{ (int) $totalFiltradasSeleccionables }} - ($wire.excludedClases ? $wire.excludedClases.length : 0));
                    }
                    return Array.isArray(this.selected) ? this.selected.length : 0;
                },
                get allPageChecked() {
                    const keys = this.pageKeys;
                    const sel = Array.isArray(this.selected) ? this.selected : [];
                    return keys.length > 0 && keys.every(k => sel.includes(k));
                },
                togglePage(checked) {
                    if ($wire.selectAllFiltered) {
                        $wire.set('selectAllPage', checked);
                        return;
                    }
                    const keys = this.pageKeys;
                    const current = Array.isArray(this.selected) ? [...this.selected] : [];
                    this.selected = checked
                        ? Array.from(new Set([...current, ...keys]))
                        : current.filter(k => !keys.includes(k));
                }
            }">
            @if($this->puedeGestionarClases)
                <div x-show="totalSelected > 0 || $wire.selectAllFiltered"
                     x-cloak
                     class="mb-4 bg-slate-50 text-slate-800 px-5 py-3 rounded-xl shadow-sm flex flex-wrap items-center justify-between gap-3 border border-slate-200 transition-all duration-200 relative z-30">
                    <div class="flex items-center gap-3">
                        <div class="p-2 bg-slate-200/70 rounded-lg text-slate-700 border border-slate-300/60">
                            <i class="fas fa-check-double text-lg"></i>
                        </div>
                        <div>
                            <p class="font-semibold text-sm text-slate-900 flex flex-wrap items-center gap-2">
                                <span><span x-text="totalSelected"></span> <span x-text="totalSelected === 1 ? 'clase seleccionada' : 'clases seleccionadas'"></span></span>
                                @if($selectAllFiltered)
                                    <span class="text-[11px] px-2 py-0.5 bg-slate-200 text-slate-700 rounded-md font-medium">Filtro completo</span>
                                    @if($totalFiltradasSeleccionables > count($currentPageSelectableKeys ?? []))
                                        <button type="button" wire:click="seleccionarSoloPaginaActual"
                                                class="text-[11px] text-[#950101] hover:underline font-medium cursor-pointer">
                                            Solo esta página ({{ count($currentPageSelectableKeys ?? []) }})
                                        </button>
                                    @endif
                                @else
                                    <button type="button"
                                            x-show="{{ (int) $totalFiltradasSeleccionables }} > totalSelected"
                                            wire:click="seleccionarTodoElFiltro"
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
                        <button type="button"
                                x-on:click="if ($wire.selectAllFiltered) { $wire.limpiarSeleccion(); } else { selected = []; }"
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
                                    Aplicar estado a <span x-text="totalSelected"></span> <span x-text="totalSelected === 1 ? 'clase' : 'clases'"></span>
                                </div>
                                <div class="py-1">
                                    <button type="button"
                                            x-on:click="openEstadoMenu = false; $dispatch('abrir-modal-masivo', { cantidad: totalSelected, tipo: 'justificado' })"
                                            class="w-full text-left px-4 py-2.5 text-sm hover:bg-amber-50/70 flex items-start gap-3 transition-colors cursor-pointer">
                                        <i class="fas fa-shield-alt text-amber-600 mt-0.5 w-4 text-center"></i>
                                        <div>
                                            <div class="font-semibold text-gray-900">Justificada</div>
                                            <div class="text-xs text-gray-500">Justificar ausencia (sin recuperación)</div>
                                        </div>
                                    </button>
                                    <button type="button"
                                            x-on:click="openEstadoMenu = false; $dispatch('abrir-modal-masivo', { cantidad: totalSelected, tipo: 'realizada' })"
                                            class="w-full text-left px-4 py-2.5 text-sm hover:bg-emerald-50/70 flex items-start gap-3 transition-colors cursor-pointer">
                                        <i class="fas fa-check-circle text-emerald-600 mt-0.5 w-4 text-center"></i>
                                        <div>
                                            <div class="font-semibold text-gray-900">Realizada / Registrada</div>
                                            <div class="text-xs text-gray-500">Regularizar como clase dictada</div>
                                        </div>
                                    </button>
                                    <button type="button"
                                            x-on:click="openEstadoMenu = false; $dispatch('abrir-modal-masivo', { cantidad: totalSelected, tipo: 'no_realizada' })"
                                            class="w-full text-left px-4 py-2.5 text-sm hover:bg-red-50/70 flex items-start gap-3 transition-colors cursor-pointer">
                                        <i class="fas fa-times-circle text-red-600 mt-0.5 w-4 text-center"></i>
                                        <div>
                                            <div class="font-semibold text-gray-900">No Registrada</div>
                                            <div class="text-xs text-gray-500">Marcar o revertir a no registrada</div>
                                        </div>
                                    </button>
                                    <button type="button"
                                            x-on:click="openEstadoMenu = false; $dispatch('abrir-modal-masivo', { cantidad: totalSelected, tipo: 'pendiente' })"
                                            class="w-full text-left px-4 py-2.5 text-sm hover:bg-orange-50/70 flex items-start gap-3 transition-colors cursor-pointer">
                                        <i class="fas fa-clock text-orange-600 mt-0.5 w-4 text-center"></i>
                                        <div>
                                            <div class="font-semibold text-gray-900">Pendiente de Recuperación</div>
                                            <div class="text-xs text-gray-500">Marcar como pendiente de recuperar</div>
                                        </div>
                                    </button>
                                    <button type="button"
                                            x-on:click="openEstadoMenu = false; $dispatch('abrir-modal-masivo', { cantidad: totalSelected, tipo: 'recuperada' })"
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
                    <table x-ref="tablaClases" class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-[#950101] text-white border-b border-[#7B0101]">
                            <tr>
                                @if($this->puedeGestionarClases)
                                <th class="px-3 py-3 text-center w-10">
                                    <input type="checkbox" 
                                           :checked="allPageChecked"
                                           x-on:change="togglePage($event.target.checked)"
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
                                               data-clase-checkbox
                                               wire:key="clase-check-{{ $clase['unique_key'] }}"
                                               x-model="selected" 
                                               x-on:change="if ($wire.selectAllFiltered) $wire.$commit()"
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
window.ClasesNoRealizadasConfig = {
    componentId: @json($this->getId())
};
</script>
@vite(['resources/js/views/livewire/clases-no-realizadas-table.js'])
@endpush
