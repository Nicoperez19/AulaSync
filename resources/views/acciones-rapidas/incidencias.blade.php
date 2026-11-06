<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-2 pr-6 md:flex-row md:items-center md:justify-between">
            <div class="flex items-center gap-3">
                <div class="p-2 rounded-xl bg-light-cloud-blue">
                    <i class="text-2xl text-white fas fa-triangle-exclamation"></i>
                </div>
                <div>
                    <h2 class="text-2xl font-bold leading-tight">Fallas e Incidencias</h2>
                    <p class="text-sm text-gray-500">Trazabilidad de por qué un espacio está en mantenimiento</p>
                </div>
            </div>
            <div class="flex items-center gap-2.5">
                <button type="button" onclick="window.dispatchEvent(new CustomEvent('reportar-falla'))"
                    class="inline-flex items-center gap-2 px-4 py-2 bg-[#D12420] hover:bg-[#950101] active:bg-[#7B0101] text-white text-sm font-semibold rounded-xl shadow-sm hover:shadow transition-colors duration-150 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#D12420]">
                    <i class="fa-solid fa-plus"></i> Reportar falla
                </button>
                <a href="{{ route('quick-actions.index') }}"
                    class="inline-flex items-center gap-2 px-4 py-2 bg-white hover:bg-slate-50 border border-slate-200 hover:border-slate-300 text-slate-700 text-sm font-semibold rounded-xl shadow-xs transition-colors duration-150 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-slate-300">
                    <i class="fas fa-arrow-left"></i> Volver
                </a>
            </div>
        </div>
    </x-slot>

    @php
        // Badges en píldora: bg-50 / text-700 / border-200 (mismo patrón que Solicitudes y Reservas)
        $sevClase = [
            'baja'    => 'bg-slate-50 text-slate-700 border-slate-200',
            'media'   => 'bg-sky-50 text-sky-700 border-sky-200',
            'alta'    => 'bg-amber-50 text-amber-700 border-amber-200',
            'critica' => 'bg-red-50 text-red-700 border-red-200',
        ];
        $estClase = [
            'abierta'    => 'bg-red-50 text-red-700 border-red-200',
            'en_proceso' => 'bg-amber-50 text-amber-700 border-amber-200',
            'resuelta'   => 'bg-emerald-50 text-emerald-700 border-emerald-200',
            'descartada' => 'bg-slate-50 text-slate-600 border-slate-200',
        ];

        // Datos para el componente Alpine (reemplaza el HTML crudo inyectado en Swal.fire)
        $cfg = [
            'categorias'  => collect($categorias)->map(fn ($c) => ['label' => $c['label'], 'bloquea' => $c['bloquea_por_defecto']]),
            'severidades' => $severidades,
            'espacios'    => $espacios->map(fn ($e) => ['id' => $e->id_espacio, 'nombre' => $e->nombre_espacio])->values(),
            'urlStore'    => route('quick-actions.api.incidencias.store'),
            'urlBase'     => url('quick-actions/api/incidencias'),
        ];
    @endphp

    <div class="space-y-6" x-data="incidenciasApp(@js($cfg))" @reportar-falla.window="abrirReporte()">

        {{-- KPIs + resumen (se refrescan sin recargar la página) --}}
        <div id="incidencias-resumen" class="space-y-6">
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                {{-- Abiertas: filtra el listado al hacer clic --}}
                <a href="{{ route('quick-actions.incidencias.index', ['estado' => 'pendientes']) }}"
                    class="block p-4 transition border border-red-200 shadow-sm stat-card bg-red-50 rounded-xl hover:shadow-md">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs font-semibold tracking-wider text-red-700 uppercase">Incidencias abiertas</p>
                            <p class="mt-1 text-3xl font-black text-red-950">{{ number_format($stats['abiertas']) }}</p>
                        </div>
                        <div class="p-3 bg-red-100 rounded-lg text-[#950101]">
                            <i class="text-xl fas fa-folder-open"></i>
                        </div>
                    </div>
                </a>

                <div class="p-4 border shadow-sm stat-card bg-amber-50 border-amber-200 rounded-xl">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs font-semibold tracking-wider uppercase text-amber-700">Graves (alta/crítica)</p>
                            <p class="mt-1 text-3xl font-black text-amber-950">{{ number_format($stats['graves_abiertas']) }}</p>
                        </div>
                        <div class="p-3 rounded-lg bg-amber-100 text-amber-600">
                            <i class="text-xl fas fa-triangle-exclamation"></i>
                        </div>
                    </div>
                </div>

                <div class="p-4 border shadow-sm stat-card bg-slate-50 border-slate-200 rounded-xl">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs font-semibold tracking-wider uppercase text-slate-700">Salas fuera de servicio</p>
                            <p class="mt-1 text-3xl font-black text-slate-950">{{ number_format($stats['salas_bloqueadas']) }}</p>
                        </div>
                        <div class="p-3 rounded-lg bg-slate-100 text-slate-600">
                            <i class="text-xl fas fa-door-closed"></i>
                        </div>
                    </div>
                </div>

                <div class="p-4 border shadow-sm stat-card bg-sky-50 border-sky-200 rounded-xl">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs font-semibold tracking-wider uppercase text-sky-700">Tiempo medio resolución (90 d)</p>
                            <p class="mt-1 text-3xl font-black text-sky-950">
                                {{ $stats['tiempo_medio_horas'] !== null ? $stats['tiempo_medio_horas'] . ' h' : '—' }}
                            </p>
                        </div>
                        <div class="p-3 rounded-lg bg-sky-100 text-sky-600">
                            <i class="text-xl fas fa-clock"></i>
                        </div>
                    </div>
                </div>
            </div>

            @if (!empty($stats['por_categoria_90d']) || !empty($stats['reincidentes_90d']))
                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div class="p-5 bg-white border border-gray-200 shadow-sm rounded-xl">
                        <h3 class="flex items-center gap-2 mb-3 text-sm font-bold tracking-wider text-gray-700 uppercase">
                            <i class="fas fa-chart-bar text-[#950101]"></i> Fallas más frecuentes (90 días)
                        </h3>
                        @foreach ($stats['por_categoria_90d'] as $cat => $total)
                            <div class="flex justify-between py-1 text-sm text-gray-600">
                                <span>{{ $categorias[$cat]['label'] ?? $cat }}</span>
                                <span class="font-semibold text-gray-800">{{ $total }}</span>
                            </div>
                        @endforeach
                    </div>
                    <div class="p-5 bg-white border border-gray-200 shadow-sm rounded-xl">
                        <h3 class="flex items-center gap-2 mb-3 text-sm font-bold tracking-wider text-gray-700 uppercase">
                            <i class="fas fa-rotate text-[#950101]"></i> Espacios reincidentes (90 días)
                        </h3>
                        @forelse ($stats['reincidentes_90d'] as $esp => $total)
                            <div class="flex justify-between py-1 text-sm text-gray-600">
                                <span>{{ $esp }}</span>
                                <span class="font-semibold text-gray-800">{{ $total }} incidencias</span>
                            </div>
                        @empty
                            <div class="text-sm text-gray-400">Sin reincidencias.</div>
                        @endforelse
                    </div>
                </div>
            @endif
        </div>

        {{-- Filtros --}}
        <form method="GET" action="{{ route('quick-actions.incidencias.index') }}"
            class="p-5 bg-white border border-gray-200 shadow-sm rounded-xl">
            <div class="flex items-center justify-between mb-4">
                <h3 class="flex items-center gap-2 text-sm font-bold tracking-wider text-gray-700 uppercase">
                    <i class="fas fa-filter text-[#950101]"></i> FILTROS
                </h3>
            </div>

            @php
                $inputCls = 'w-full px-3 py-2 text-sm bg-white border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-[#D12420]/20 focus:border-[#D12420]';
                $labelCls = 'block mb-1 text-xs font-semibold text-gray-700 uppercase';
            @endphp

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-5">
                <div>
                    <label for="f_estado" class="{{ $labelCls }}">Estado</label>
                    <select id="f_estado" name="estado" class="{{ $inputCls }}">
                        <option value="pendientes" @selected(($filtros['estado'] ?? '') === 'pendientes')>Pendientes</option>
                        <option value="" @selected(array_key_exists('estado', $filtros) && $filtros['estado'] === '')>Todas</option>
                        @foreach ($estados as $k => $v)
                            <option value="{{ $k }}" @selected(($filtros['estado'] ?? '') === $k)>{{ $v }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="f_categoria" class="{{ $labelCls }}">Tipo de falla</label>
                    <select id="f_categoria" name="categoria" class="{{ $inputCls }}">
                        <option value="">Todas las fallas</option>
                        @foreach ($categorias as $k => $c)
                            <option value="{{ $k }}" @selected(($filtros['categoria'] ?? '') === $k)>{{ $c['label'] }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="f_severidad" class="{{ $labelCls }}">Severidad</label>
                    <select id="f_severidad" name="severidad" class="{{ $inputCls }}">
                        <option value="">Toda severidad</option>
                        @foreach ($severidades as $k => $v)
                            <option value="{{ $k }}" @selected(($filtros['severidad'] ?? '') === $k)>{{ $v }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="f_espacio" class="{{ $labelCls }}">Espacio</label>
                    <select id="f_espacio" name="espacio" class="{{ $inputCls }}">
                        <option value="">Todos los espacios</option>
                        @foreach ($espacios as $e)
                            <option value="{{ $e->id_espacio }}" @selected(($filtros['espacio'] ?? '') === $e->id_espacio)>{{ $e->id_espacio }} — {{ $e->nombre_espacio }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="f_buscar" class="{{ $labelCls }}">Buscar</label>
                    <div class="relative">
                        <input type="text" id="f_buscar" name="buscar" value="{{ $filtros['buscar'] ?? '' }}"
                            placeholder="Espacio, falla o descripción..." class="pl-9 {{ $inputCls }}">
                        <i class="absolute text-xs text-gray-400 fas fa-search left-3 top-3"></i>
                    </div>
                </div>
            </div>

            <div class="flex justify-end gap-2 mt-4">
                <a href="{{ route('quick-actions.incidencias.index') }}"
                    class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold text-gray-700 transition-colors bg-white border border-gray-300 rounded-lg hover:bg-gray-50">
                    <i class="fas fa-eraser"></i> Limpiar
                </a>
                <button type="submit"
                    class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold text-white transition-colors rounded-lg shadow-sm bg-[#D12420] hover:bg-[#950101]">
                    <i class="fas fa-check"></i> Aplicar
                </button>
            </div>
        </form>

        {{-- Tabla + paginación (se refrescan sin recargar la página) --}}
        <div id="incidencias-listado" class="space-y-4">
            <div class="overflow-hidden bg-white border border-gray-200 shadow-sm rounded-xl">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-[#950101] text-white border-b border-[#7B0101]">
                            <tr>
                                @foreach (['Espacio', 'Falla', 'Severidad', 'Estado', 'Reportó', 'Abierta', 'Acciones'] as $col)
                                    <th scope="col" class="px-3 py-3.5 text-center text-xs font-semibold text-white uppercase tracking-wider">{{ $col }}</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse ($incidencias as $i)
                                <tr class="transition-colors hover:bg-slate-50/80">
                                    <td class="px-3 py-3 text-center align-middle">
                                        <div class="text-sm font-semibold text-[#950101]">{{ $i->id_espacio }}</div>
                                        <div class="text-xs text-gray-500">{{ $i->espacio?->nombre_espacio }}</div>
                                        @if ($i->bloquea_uso && $i->estaAbierta())
                                            <span class="inline-flex mt-1 px-2.5 py-0.5 text-xs font-semibold rounded-full border bg-amber-50 text-amber-700 border-amber-200">Fuera de servicio</span>
                                        @endif
                                    </td>
                                    <td class="max-w-xs px-3 py-3 text-center align-middle">
                                        <div class="text-sm font-medium text-gray-800">
                                            <i class="mr-1 fa-solid {{ $i->categoria_icon }} text-gray-400"></i>{{ $i->categoria_label }}
                                        </div>
                                        <div class="text-xs text-gray-500 line-clamp-2">{{ $i->descripcion }}</div>
                                    </td>
                                    <td class="px-3 py-3 text-center align-middle whitespace-nowrap">
                                        <span class="inline-flex px-2.5 py-0.5 text-xs font-semibold rounded-full border {{ $sevClase[$i->severidad] ?? 'bg-slate-50 text-slate-700 border-slate-200' }}">{{ $i->severidad_label }}</span>
                                    </td>
                                    <td class="px-3 py-3 text-center align-middle whitespace-nowrap">
                                        <span class="inline-flex px-2.5 py-0.5 text-xs font-semibold rounded-full border {{ $estClase[$i->estado] ?? 'bg-slate-50 text-slate-700 border-slate-200' }}">{{ $i->estado_label }}</span>
                                    </td>
                                    <td class="px-3 py-3 text-center align-middle whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-800">{{ $i->reportado_por_nombre ?? '—' }}</div>
                                        <div class="text-xs text-gray-500">{{ $i->reportado_en?->format('d/m/Y H:i') }}</div>
                                    </td>
                                    <td class="px-3 py-3 text-center align-middle whitespace-nowrap">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-semibold bg-slate-100 text-slate-700 border border-slate-200">{{ $i->horas_abierta }} h</span>
                                    </td>
                                    <td class="px-3 py-3 text-center align-middle whitespace-nowrap">
                                        <button type="button" @click="verDetalle({{ $i->id }})"
                                            class="inline-flex items-center gap-1 px-3 py-1 text-xs font-semibold text-[#950101] transition-colors border border-[#950101]/30 rounded-lg hover:bg-red-50">
                                            <i class="fas fa-eye"></i> Ver / gestionar
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-4 py-12 text-center">
                                        <i class="block mb-3 text-4xl text-gray-300 fas fa-clipboard-check"></i>
                                        <p class="text-sm font-medium text-gray-500">No hay incidencias con estos filtros.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div>{{ $incidencias->withQueryString()->links('vendor.pagination.brand') }}</div>
        </div>

        {{-- Modal: reportar falla --}}
        <x-modal name="incidencia-reporte" title="Reportar falla" maxWidth="lg">
            <form @submit.prevent="registrar()" class="space-y-4">
                <div>
                    <label class="block mb-1 text-xs font-semibold text-gray-700 uppercase">Espacio</label>
                    <select x-model="form.id_espacio"
                        class="w-full px-3 py-2 text-sm bg-white border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-[#D12420]/20 focus:border-[#D12420]">
                        <option value="">Selecciona...</option>
                        <template x-for="e in cfg.espacios" :key="e.id">
                            <option :value="e.id" x-text="e.id + ' — ' + e.nombre"></option>
                        </template>
                    </select>
                </div>
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label class="block mb-1 text-xs font-semibold text-gray-700 uppercase">Tipo de falla</label>
                        <select x-model="form.categoria"
                            class="w-full px-3 py-2 text-sm bg-white border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-[#D12420]/20 focus:border-[#D12420]">
                            <option value="">Selecciona...</option>
                            <template x-for="(c, k) in cfg.categorias" :key="k">
                                <option :value="k" x-text="c.label"></option>
                            </template>
                        </select>
                    </div>
                    <div>
                        <label class="block mb-1 text-xs font-semibold text-gray-700 uppercase">Severidad</label>
                        <select x-model="form.severidad"
                            class="w-full px-3 py-2 text-sm bg-white border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-[#D12420]/20 focus:border-[#D12420]">
                            <template x-for="(v, k) in cfg.severidades" :key="k">
                                <option :value="k" x-text="v"></option>
                            </template>
                        </select>
                    </div>
                </div>
                <div>
                    <label class="block mb-1 text-xs font-semibold text-gray-700 uppercase">Descripción</label>
                    <textarea x-model="form.descripcion" rows="3" placeholder="Ej: Faltan plumones y borrador"
                        class="w-full px-3 py-2 text-sm bg-white border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-[#D12420]/20 focus:border-[#D12420]"></textarea>
                </div>
                <label class="flex items-center gap-2 text-sm text-gray-700">
                    <input type="checkbox" x-model="form.bloquea_uso" class="rounded border-gray-300 text-[#D12420] focus:ring-[#D12420]">
                    <span>La sala no se puede usar (ponerla en <b>Mantenimiento</b>)</span>
                </label>

                <p x-show="errorForm" x-text="errorForm" x-cloak
                    class="px-3 py-2 text-sm text-red-700 border border-red-200 rounded-lg bg-red-50"></p>

                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" @click="$dispatch('close-modal', 'incidencia-reporte')"
                        class="px-4 py-2 text-sm font-semibold text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">Cancelar</button>
                    <button type="submit" :disabled="enviando"
                        class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold text-white rounded-lg shadow-sm bg-[#D12420] hover:bg-[#950101] disabled:opacity-60">
                        <i class="fas" :class="enviando ? 'fa-spinner fa-spin' : 'fa-check'"></i> Registrar
                    </button>
                </div>
            </form>
        </x-modal>

        {{-- Modal: detalle / gestión --}}
        <x-modal name="incidencia-detalle" title="Detalle de incidencia" maxWidth="2xl">
            <div x-show="cargandoDetalle" class="py-10 text-center text-gray-400">
                <i class="text-2xl fas fa-spinner fa-spin"></i>
            </div>

            <template x-if="detalle && !cargandoDetalle">
                <div class="space-y-4 text-sm text-gray-700">
                    <div>
                        <h4 class="text-lg font-bold text-gray-900" x-text="detalle.id_espacio + ' · ' + detalle.categoria_label"></h4>
                        <p class="mt-1" x-text="detalle.descripcion"></p>
                    </div>

                    <div class="flex flex-wrap items-center gap-2">
                        <span class="inline-flex px-2.5 py-0.5 text-xs font-semibold rounded-full border"
                            :class="sevCls[detalle.severidad]" x-text="'Severidad: ' + detalle.severidad_label"></span>
                        <span class="inline-flex px-2.5 py-0.5 text-xs font-semibold rounded-full border"
                            :class="estCls[detalle.estado]" x-text="detalle.estado_label"></span>
                        <span x-show="detalle.bloquea_uso" class="inline-flex px-2.5 py-0.5 text-xs font-semibold rounded-full border bg-amber-50 text-amber-700 border-amber-200">
                            Sala fuera de servicio mientras esté abierta
                        </span>
                    </div>

                    <div x-show="detalle.resolucion" class="p-3 border rounded-lg bg-emerald-50 border-emerald-200 text-emerald-800">
                        <b>Resolución:</b> <span x-text="detalle.resolucion"></span>
                        <span class="text-xs" x-text="'(' + (detalle.resuelto_por || '') + ' ' + (detalle.resuelto_en || '') + ')'"></span>
                    </div>

                    <div>
                        <h5 class="mb-2 text-xs font-bold tracking-wider text-gray-700 uppercase">
                            <i class="fas fa-list-ul text-[#950101] mr-1"></i> Bitácora
                        </h5>
                        <ul class="overflow-y-auto border border-gray-200 divide-y divide-gray-100 rounded-lg max-h-56">
                            <template x-for="(e, idx) in detalle.eventos" :key="idx">
                                <li class="px-3 py-2">
                                    <div class="text-xs text-gray-400">
                                        <span x-text="e.fecha + ' · ' + e.usuario + ' · ' + e.tipo.replace('_', ' ')"></span>
                                        <span x-show="e.estado_nuevo" x-text="'(' + (e.estado_anterior || '—') + ' → ' + e.estado_nuevo + ')'"></span>
                                    </div>
                                    <div x-text="e.detalle"></div>
                                </li>
                            </template>
                        </ul>
                    </div>

                    <div class="flex gap-2">
                        <input type="text" x-model="comentario" @keydown.enter.prevent="comentar()" placeholder="Agregar comentario..."
                            class="flex-1 px-3 py-2 text-sm bg-white border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-[#D12420]/20 focus:border-[#D12420]">
                        <button type="button" @click="comentar()"
                            class="px-4 py-2 text-sm font-semibold text-white rounded-lg bg-[#191919] hover:bg-[#950101]">Comentar</button>
                    </div>

                    {{-- Panel de nota (resolver / descartar) --}}
                    <div x-show="accion" x-cloak class="p-3 space-y-2 border border-gray-200 rounded-lg bg-gray-50">
                        <label class="block text-xs font-semibold text-gray-700 uppercase" x-text="accion?.titulo"></label>
                        <textarea x-model="nota" rows="2" maxlength="1000"
                            class="w-full px-3 py-2 text-sm bg-white border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#D12420]/20 focus:border-[#D12420]"></textarea>
                        <p x-show="errorNota" x-text="errorNota" class="text-xs text-red-600"></p>
                        <div class="flex justify-end gap-2">
                            <button type="button" @click="accion = null; nota = ''; errorNota = ''"
                                class="px-3 py-1.5 text-xs font-semibold text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">Volver</button>
                            <button type="button" @click="confirmarNota()"
                                class="px-3 py-1.5 text-xs font-semibold text-white rounded-lg bg-[#D12420] hover:bg-[#950101]">Confirmar</button>
                        </div>
                    </div>

                    <div class="flex flex-wrap justify-end gap-2 pt-2 border-t border-gray-100" x-show="!accion">
                        <template x-if="abierta">
                            <div class="flex flex-wrap justify-end gap-2">
                                <button type="button" :disabled="detalle.estado === 'en_proceso'" @click="cambiarEstado('en_proceso')"
                                    class="px-3 py-1.5 text-xs font-semibold border rounded-lg bg-amber-50 text-amber-700 border-amber-200 hover:bg-amber-100 disabled:opacity-50">En proceso</button>
                                <button type="button" @click="pedirNota('resuelta', '¿Cómo se resolvió?')"
                                    class="px-3 py-1.5 text-xs font-semibold border rounded-lg bg-emerald-50 text-emerald-700 border-emerald-200 hover:bg-emerald-100">Resolver</button>
                                <button type="button" @click="pedirNota('descartada', '¿Por qué se descarta?')"
                                    class="px-3 py-1.5 text-xs font-semibold border rounded-lg bg-slate-50 text-slate-600 border-slate-200 hover:bg-slate-100">Descartar</button>
                            </div>
                        </template>
                        <template x-if="!abierta">
                            <button type="button" @click="cambiarEstado('abierta')"
                                class="px-3 py-1.5 text-xs font-semibold border rounded-lg bg-amber-50 text-amber-700 border-amber-200 hover:bg-amber-100">Reabrir</button>
                        </template>
                    </div>
                </div>
            </template>
        </x-modal>
    </div>

    @push('scripts')
    <script>
    /**
     * Flujo de incidencias sin HTML inyectado en Swal.fire y sin location.reload().
     * - Formularios y detalle: componente modal de Blade con Alpine.
     * - Avisos: toasts del sistema (Swal.toastSuccess / Swal.toastError).
     * - Tras cada cambio se refresca solo KPIs + tabla (#incidencias-resumen / #incidencias-listado).
     */
    function incidenciasApp(cfg) {
        const CSRF = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        return {
            cfg,
            form: { id_espacio: '', categoria: '', severidad: 'media', descripcion: '', bloquea_uso: false },
            errorForm: '',
            enviando: false,
            detalle: null,
            cargandoDetalle: false,
            comentario: '',
            accion: null,
            nota: '',
            errorNota: '',

            sevCls: {
                baja: 'bg-slate-50 text-slate-700 border-slate-200',
                media: 'bg-sky-50 text-sky-700 border-sky-200',
                alta: 'bg-amber-50 text-amber-700 border-amber-200',
                critica: 'bg-red-50 text-red-700 border-red-200',
            },
            estCls: {
                abierta: 'bg-red-50 text-red-700 border-red-200',
                en_proceso: 'bg-amber-50 text-amber-700 border-amber-200',
                resuelta: 'bg-emerald-50 text-emerald-700 border-emerald-200',
                descartada: 'bg-slate-50 text-slate-600 border-slate-200',
            },

            init() {
                // Al elegir un tipo de falla se sugiere si bloquea el uso de la sala
                this.$watch('form.categoria', (c) => {
                    this.form.bloquea_uso = !!(this.cfg.categorias[c] && this.cfg.categorias[c].bloquea);
                });
            },

            get abierta() {
                return !!this.detalle && ['abierta', 'en_proceso'].includes(this.detalle.estado);
            },

            toast(ok, mensaje) {
                const fn = ok ? 'toastSuccess' : 'toastError';
                if (window.Swal && typeof Swal[fn] === 'function') Swal[fn](mensaje);
            },

            async api(url, method, body) {
                const r = await fetch(url, {
                    method,
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF },
                    body: body ? JSON.stringify(body) : undefined,
                });
                const data = await r.json().catch(() => ({}));
                if (!r.ok && !data.mensaje) {
                    data.mensaje = data.errors ? Object.values(data.errors).flat().join(' ') : 'Error inesperado';
                }
                return data;
            },

            // Reemplaza solo KPIs + tabla; no recarga la página
            async refrescar() {
                try {
                    const r = await fetch(window.location.href, {
                        headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'text/html' },
                    });
                    const doc = new DOMParser().parseFromString(await r.text(), 'text/html');
                    ['incidencias-resumen', 'incidencias-listado'].forEach((id) => {
                        const nuevo = doc.getElementById(id);
                        const actual = document.getElementById(id);
                        if (nuevo && actual) actual.innerHTML = nuevo.innerHTML;
                    });
                } catch (e) {
                    /* si falla el refresco, los datos ya quedaron guardados */
                }
            },

            // ---- Reportar falla ----
            abrirReporte() {
                this.form = { id_espacio: '', categoria: '', severidad: 'media', descripcion: '', bloquea_uso: false };
                this.errorForm = '';
                this.$dispatch('open-modal', 'incidencia-reporte');
            },

            async registrar() {
                const f = this.form;
                if (!f.id_espacio) return (this.errorForm = 'Selecciona el espacio');
                if (!f.categoria) return (this.errorForm = 'Selecciona el tipo de falla');
                if (f.descripcion.trim().length < 5) return (this.errorForm = 'Describe la falla (mínimo 5 caracteres)');

                this.errorForm = '';
                this.enviando = true;
                const data = await this.api(this.cfg.urlStore, 'POST', { ...f, descripcion: f.descripcion.trim() });
                this.enviando = false;

                if (data.success) {
                    this.$dispatch('close-modal', 'incidencia-reporte');
                    this.toast(true, data.mensaje || 'Falla registrada');
                    await this.refrescar();
                } else {
                    this.errorForm = data.mensaje || 'No se pudo registrar';
                }
            },

            // ---- Detalle / gestión ----
            async verDetalle(id, abrirModal = true) {
                this.accion = null;
                this.nota = '';
                this.errorNota = '';
                if (abrirModal) {
                    this.detalle = null;
                    this.cargandoDetalle = true;
                    this.$dispatch('open-modal', 'incidencia-detalle');
                }
                const data = await this.api(`${this.cfg.urlBase}/${id}`, 'GET');
                this.cargandoDetalle = false;
                if (!data.success) {
                    this.$dispatch('close-modal', 'incidencia-detalle');
                    return this.toast(false, data.mensaje || 'Incidencia no encontrada');
                }
                this.detalle = data.incidencia;
            },

            async comentar() {
                const t = this.comentario.trim();
                if (t.length < 2) return;
                const r = await this.api(`${this.cfg.urlBase}/${this.detalle.id}/comentarios`, 'POST', { comentario: t });
                if (r.success) {
                    this.comentario = '';
                    await this.verDetalle(this.detalle.id, false);
                } else {
                    this.toast(false, r.mensaje || 'No se pudo comentar');
                }
            },

            pedirNota(estado, titulo) {
                this.accion = { estado, titulo };
                this.nota = '';
                this.errorNota = '';
            },

            async confirmarNota() {
                if (this.nota.trim().length < 3) {
                    this.errorNota = 'Indica un detalle (mínimo 3 caracteres)';
                    return;
                }
                await this.cambiarEstado(this.accion.estado, this.nota.trim());
            },

            async cambiarEstado(estado, nota = null) {
                const r = await this.api(`${this.cfg.urlBase}/${this.detalle.id}/estado`, 'PUT', { estado, nota });
                if (r.success) {
                    this.$dispatch('close-modal', 'incidencia-detalle');
                    this.toast(true, r.mensaje || 'Estado actualizado');
                    await this.refrescar();
                } else {
                    this.toast(false, r.mensaje || 'No se pudo cambiar el estado');
                }
            },
        };
    }
    </script>
    @endpush
</x-app-layout>
