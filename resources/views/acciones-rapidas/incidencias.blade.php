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
            <div class="flex flex-wrap items-center gap-2.5">
                <a href="{{ route('quick-actions.incidencias.export', ['format' => 'excel'] + $exportParams) }}"
                    class="inline-flex items-center gap-2 px-4 py-2 bg-white hover:bg-slate-50 border border-slate-200 text-slate-700 text-sm font-semibold rounded-xl shadow-xs">
                    <i class="fa-solid fa-file-excel" style="color:#16a34a"></i> Excel
                </a>
                <a href="{{ route('quick-actions.incidencias.export', ['format' => 'pdf'] + $exportParams) }}"
                    class="inline-flex items-center gap-2 px-4 py-2 bg-white hover:bg-slate-50 border border-slate-200 text-slate-700 text-sm font-semibold rounded-xl shadow-xs">
                    <i class="fa-solid fa-file-pdf" style="color:#dc2626"></i> PDF
                </a>
                <button type="button" onclick="abrirReporte()"
                    class="inline-flex items-center gap-2 px-4 py-2 text-white text-sm font-semibold rounded-xl shadow-xs"
                    style="background:#D12420">
                    <i class="fa-solid fa-plus"></i> Reportar falla
                </button>
                <a href="{{ route('quick-actions.index') }}"
                    class="inline-flex items-center gap-2 px-4 py-2 bg-white hover:bg-slate-50 border border-slate-200 text-slate-700 text-sm font-semibold rounded-xl shadow-xs">
                    Volver
                </a>
            </div>
        </div>
    </x-slot>

    @php
        // Mismos badges que usan las demás tablas del sistema (espacios, reservas, etc.)
        $sevClase = [
            'baja' => 'bg-slate-100 text-slate-700 border border-slate-200 dark:bg-slate-700 dark:text-slate-300 dark:border-slate-600',
            'media' => 'bg-blue-50 text-blue-700 border border-blue-200 dark:bg-blue-900/40 dark:text-blue-300 dark:border-blue-800',
            'alta' => 'bg-amber-50 text-amber-700 border border-amber-200 dark:bg-amber-900/40 dark:text-amber-300 dark:border-amber-800',
            'critica' => 'bg-red-50 text-red-700 border border-red-200 dark:bg-red-900/40 dark:text-red-300 dark:border-red-800',
        ];
        $estClase = [
            'abierta' => 'bg-red-50 text-red-700 border border-red-200 dark:bg-red-900/40 dark:text-red-300 dark:border-red-800',
            'en_proceso' => 'bg-amber-50 text-amber-700 border border-amber-200 dark:bg-amber-900/40 dark:text-amber-300 dark:border-amber-800',
            'resuelta' => 'bg-emerald-50 text-emerald-700 border border-emerald-200 dark:bg-emerald-900/40 dark:text-emerald-300 dark:border-emerald-800',
            'descartada' => 'bg-slate-100 text-slate-600 border border-slate-200 dark:bg-slate-700 dark:text-slate-300 dark:border-slate-600',
        ];

        // Gráficos (últimos 90 días)
        $porEstado = $stats['por_estado_90d'] ?? [];
        $totalEstado = array_sum($porEstado);
        $coloresEstado = ['abierta' => '#D12420', 'en_proceso' => '#F59E0B', 'resuelta' => '#10B981', 'descartada' => '#94A3B8'];
        $segmentos = [];
        $acum = 0;
        foreach ($coloresEstado as $k => $color) {
            $n = (int) ($porEstado[$k] ?? 0);
            if ($n > 0) {
                $desdePct = $acum;
                $acum += ($n / $totalEstado) * 100;
                $segmentos[] = "{$color} {$desdePct}% {$acum}%";
            }
        }
        $gradiente = $totalEstado > 0 ? 'conic-gradient(' . implode(', ', $segmentos) . ')' : '#E5E7EB';

        $porCategoria = $stats['por_categoria_90d'] ?? [];
        $maxCategoria = $porCategoria ? max($porCategoria) : 1;
        $reincidentes = $stats['reincidentes_90d'] ?? [];
        $maxReincidente = $reincidentes ? max($reincidentes) : 1;
        $hayDatos90d = $totalEstado > 0;
    @endphp

    <div class="space-y-4">
        {{-- Indicadores --}}
        <div class="grid grid-cols-2 gap-4 lg:grid-cols-4">
            @foreach ([
                ['Incidencias abiertas', $stats['abiertas'], 'fa-folder-open', '#D12420', '#FEF2F2'],
                ['Graves (alta / crítica)', $stats['graves_abiertas'], 'fa-circle-exclamation', '#B45309', '#FFFBEB'],
                ['Salas fuera de servicio', $stats['salas_bloqueadas'], 'fa-door-closed', '#950101', '#FEF2F2'],
                ['Resolución media (90 d)', $stats['tiempo_medio_horas'] !== null ? $stats['tiempo_medio_horas'] . ' h' : '—', 'fa-stopwatch', '#047857', '#ECFDF5'],
            ] as [$etiqueta, $valor, $icono, $color, $fondo])
                <div class="flex items-center gap-3 p-4 bg-white border border-gray-200 shadow-sm rounded-xl dark:bg-gray-800 dark:border-gray-700">
                    <div class="flex items-center justify-center w-11 h-11 rounded-xl shrink-0" style="background:{{ $fondo }};color:{{ $color }}">
                        <i class="fa-solid {{ $icono }} text-lg"></i>
                    </div>
                    <div class="min-w-0">
                        <div class="text-2xl font-bold leading-none text-gray-900 dark:text-white">{{ $valor }}</div>
                        <div class="mt-1 text-xs font-medium text-gray-500 truncate dark:text-gray-400">{{ $etiqueta }}</div>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Gráficos --}}
        @if ($hayDatos90d)
            <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                {{-- Dona: incidencias por estado --}}
                <div class="p-4 bg-white border border-gray-200 shadow-sm rounded-xl dark:bg-gray-800 dark:border-gray-700">
                    <div class="mb-3 text-sm font-semibold text-gray-700 dark:text-gray-200">Estado de las incidencias <span class="font-normal text-gray-400">(90 días)</span></div>
                    <div class="flex items-center gap-5">
                        <div class="relative w-28 h-28 rounded-full shrink-0" style="background: {{ $gradiente }}">
                            <div class="absolute flex flex-col items-center justify-center bg-white rounded-full dark:bg-gray-800" style="inset:14px">
                                <span class="text-xl font-bold leading-none text-gray-900 dark:text-white">{{ $totalEstado }}</span>
                                <span class="text-[10px] text-gray-500">total</span>
                            </div>
                        </div>
                        <ul class="space-y-1.5 text-xs text-gray-600 dark:text-gray-300">
                            @foreach ($coloresEstado as $k => $color)
                                @if (($porEstado[$k] ?? 0) > 0)
                                    <li class="flex items-center gap-2">
                                        <span class="inline-block w-2.5 h-2.5 rounded-full" style="background:{{ $color }}"></span>
                                        <span>{{ $estados[$k] }}</span>
                                        <strong class="text-gray-900 dark:text-white">{{ $porEstado[$k] }}</strong>
                                    </li>
                                @endif
                            @endforeach
                        </ul>
                    </div>
                </div>

                {{-- Barras: fallas más frecuentes --}}
                <div class="p-4 bg-white border border-gray-200 shadow-sm rounded-xl dark:bg-gray-800 dark:border-gray-700">
                    <div class="mb-3 text-sm font-semibold text-gray-700 dark:text-gray-200">Fallas más frecuentes <span class="font-normal text-gray-400">(90 días)</span></div>
                    <div class="space-y-2.5">
                        @foreach ($porCategoria as $cat => $total)
                            <div>
                                <div class="flex justify-between mb-1 text-xs text-gray-600 dark:text-gray-300">
                                    <span class="truncate">{{ $categorias[$cat]['label'] ?? $cat }}</span>
                                    <strong class="ml-2 text-gray-900 dark:text-white">{{ $total }}</strong>
                                </div>
                                <div class="h-2 overflow-hidden bg-gray-100 rounded-full dark:bg-gray-700">
                                    <div class="h-2 rounded-full" style="width: {{ round(($total / $maxCategoria) * 100) }}%; background:#950101"></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- Barras: espacios reincidentes --}}
                <div class="p-4 bg-white border border-gray-200 shadow-sm rounded-xl dark:bg-gray-800 dark:border-gray-700">
                    <div class="mb-3 text-sm font-semibold text-gray-700 dark:text-gray-200">Espacios reincidentes <span class="font-normal text-gray-400">(90 días)</span></div>
                    @if ($reincidentes)
                        <div class="space-y-2.5">
                            @foreach ($reincidentes as $esp => $total)
                                <div>
                                    <div class="flex justify-between mb-1 text-xs text-gray-600 dark:text-gray-300">
                                        <span>{{ $esp }}</span>
                                        <strong class="text-gray-900 dark:text-white">{{ $total }} incidencias</strong>
                                    </div>
                                    <div class="h-2 overflow-hidden bg-gray-100 rounded-full dark:bg-gray-700">
                                        <div class="h-2 rounded-full" style="width: {{ round(($total / $maxReincidente) * 100) }}%; background:#F59E0B"></div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="flex items-center gap-2 text-sm text-gray-400">
                            <i class="fa-solid fa-circle-check text-emerald-500"></i> Ningún espacio con 2 o más incidencias.
                        </div>
                    @endif
                </div>
            </div>
        @endif

        {{-- Card Filtros (mismo diseño que las demás tablas) --}}
        <form method="GET" class="p-4 bg-white border border-gray-200 shadow-sm rounded-xl dark:bg-gray-800 dark:border-gray-700">
            @php
                $campo = 'w-full px-3 py-2 text-sm text-gray-900 bg-white border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-[#D12420]/20 focus:border-[#D12420] dark:bg-gray-700 dark:border-gray-600 dark:text-white';
            @endphp
            <div class="grid grid-cols-2 gap-3 md:grid-cols-6">
                <select name="estado" class="{{ $campo }}">
                    <option value="pendientes" @selected(($filtros['estado'] ?? '') === 'pendientes')>Pendientes</option>
                    <option value="" @selected(array_key_exists('estado', $filtros) && $filtros['estado'] === '')>Todas</option>
                    @foreach ($estados as $k => $v)
                        <option value="{{ $k }}" @selected(($filtros['estado'] ?? '') === $k)>{{ $v }}</option>
                    @endforeach
                </select>
                <select name="categoria" class="{{ $campo }}">
                    <option value="">Todas las fallas</option>
                    @foreach ($categorias as $k => $c)
                        <option value="{{ $k }}" @selected(($filtros['categoria'] ?? '') === $k)>{{ $c['label'] }}</option>
                    @endforeach
                </select>
                <select name="severidad" class="{{ $campo }}">
                    <option value="">Toda severidad</option>
                    @foreach ($severidades as $k => $v)
                        <option value="{{ $k }}" @selected(($filtros['severidad'] ?? '') === $k)>{{ $v }}</option>
                    @endforeach
                </select>
                <select name="espacio" class="{{ $campo }}">
                    <option value="">Todos los espacios</option>
                    @foreach ($espacios as $e)
                        <option value="{{ $e->id_espacio }}" @selected(($filtros['espacio'] ?? '') === $e->id_espacio)>{{ $e->id_espacio }} — {{ $e->nombre_espacio }}</option>
                    @endforeach
                </select>
                <div class="relative col-span-2 md:col-span-2">
                    <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </div>
                    <input type="text" name="buscar" value="{{ $filtros['buscar'] ?? '' }}" placeholder="Espacio, falla o descripción..."
                        class="{{ $campo }} pl-9">
                </div>
            </div>
            <div class="flex flex-wrap items-center justify-between gap-3 mt-3">
                <span class="px-3 py-1 text-xs font-semibold text-gray-500 bg-gray-100 rounded-full dark:bg-gray-700 dark:text-gray-400">
                    Total: <strong class="text-gray-800 dark:text-white">{{ $incidencias->total() }}</strong> incidencias
                </span>
                <div class="flex items-center gap-2">
                    <a href="{{ route('quick-actions.incidencias.index') }}"
                        class="px-3 py-1.5 text-xs font-semibold text-gray-700 bg-white border border-gray-300 rounded-lg shadow-xs hover:bg-gray-100 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200">
                        Limpiar
                    </a>
                    <button type="submit" class="px-4 py-1.5 text-xs font-semibold text-white rounded-lg shadow-xs" style="background:#950101">
                        <i class="mr-1 fa-solid fa-filter"></i> Filtrar
                    </button>
                </div>
            </div>
        </form>

        {{-- Card Tabla (mismo diseño que las demás tablas) --}}
        <div class="overflow-hidden bg-white border border-gray-200 shadow-sm rounded-xl dark:bg-gray-800 dark:border-gray-700">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-[#950101] text-white border-b border-[#7B0101]">
                        <tr>
                            @foreach (['Espacio', 'Falla', 'Severidad', 'Estado', 'Reportó', 'Abierta', 'Acciones'] as $col)
                                <th scope="col" class="px-3 py-3.5 text-center text-xs font-semibold text-white uppercase tracking-wider">{{ $col }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200 dark:bg-gray-800 dark:divide-gray-700">
                        @forelse ($incidencias as $i)
                            <tr class="transition-colors hover:bg-slate-50/80 dark:hover:bg-gray-700/50">
                                <td class="px-3 py-3 text-center align-middle whitespace-nowrap">
                                    <div class="text-sm font-semibold text-gray-900 dark:text-gray-100">{{ $i->id_espacio }}</div>
                                    <div class="text-xs text-gray-500">{{ $i->espacio?->nombre_espacio }}</div>
                                    @if ($i->bloquea_uso && $i->estaAbierta())
                                        <span class="inline-flex items-center px-2 py-0.5 mt-1 text-[11px] font-semibold rounded-full bg-amber-50 text-amber-700 border border-amber-200 dark:bg-amber-900/40 dark:text-amber-300 dark:border-amber-800">Fuera de servicio</span>
                                    @endif
                                </td>
                                <td class="px-3 py-3 text-center align-middle">
                                    <div class="text-sm font-medium text-gray-800 dark:text-gray-200">
                                        <i class="mr-1 fa-solid {{ $i->categoria_icon }} text-gray-400"></i>{{ $i->categoria_label }}
                                    </div>
                                    <div class="max-w-xs mx-auto text-xs text-gray-500 line-clamp-2">{{ $i->descripcion }}</div>
                                </td>
                                <td class="px-3 py-3 text-center align-middle whitespace-nowrap">
                                    <span class="px-2.5 py-0.5 inline-flex text-xs font-semibold rounded-full {{ $sevClase[$i->severidad] ?? '' }}">{{ $i->severidad_label }}</span>
                                </td>
                                <td class="px-3 py-3 text-center align-middle whitespace-nowrap">
                                    <span class="px-2.5 py-0.5 inline-flex text-xs font-semibold rounded-full {{ $estClase[$i->estado] ?? '' }}">{{ $i->estado_label }}</span>
                                </td>
                                <td class="px-3 py-3 text-center align-middle whitespace-nowrap">
                                    <div class="text-sm text-gray-800 dark:text-gray-300">{{ $i->reportado_por_nombre ?? '—' }}</div>
                                    <div class="text-xs text-gray-500">{{ $i->reportado_en?->format('d-m-Y H:i') }}</div>
                                </td>
                                <td class="px-3 py-3 text-sm font-medium text-center text-gray-700 align-middle whitespace-nowrap dark:text-gray-300">{{ $i->horas_abierta }} h</td>
                                <td class="px-3 py-3 text-center align-middle whitespace-nowrap">
                                    <button type="button" onclick="verIncidencia({{ $i->id }})"
                                        class="inline-flex items-center gap-1.5 px-2.5 py-1 text-xs font-semibold text-gray-700 bg-white hover:bg-gray-100 hover:text-[#950101] rounded-lg border border-gray-300 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200 dark:hover:bg-gray-600 transition-colors shadow-xs">
                                        <i class="text-xs fa-solid fa-eye"></i>
                                        <span>Ver / gestionar</span>
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-8 text-center text-gray-500 dark:text-gray-400">
                                    <div class="flex flex-col items-center justify-center">
                                        <i class="mb-2 text-4xl text-gray-300 fa-solid fa-clipboard-check dark:text-gray-600"></i>
                                        <p class="text-base font-medium text-gray-700 dark:text-gray-300">No se encontraron incidencias</p>
                                        <p class="mt-1 text-xs text-gray-400">Intenta ajustar los filtros o reporta una nueva falla</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($incidencias->hasPages())
                {{-- Footer de paginación integrado --}}
                <div class="px-6 py-4 border-t border-gray-200 bg-gray-50/70 dark:bg-gray-900/40 dark:border-gray-700">
                    {{ $incidencias->links('vendor.pagination.tailwind') }}
                </div>
            @endif
        </div>
    </div>

    @push('scripts')
    <script>
    const CSRF = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    const CATEGORIAS = @json(collect($categorias)->map(fn ($c) => ['label' => $c['label'], 'bloquea' => $c['bloquea_por_defecto']]));
    const SEVERIDADES = @json($severidades);
    const ESPACIOS = @json($espacios->map(fn ($e) => ['id' => $e->id_espacio, 'nombre' => $e->nombre_espacio]));
    const URL_STORE = @json(route('quick-actions.api.incidencias.store'));
    const URL_BASE = @json(url('quick-actions/api/incidencias'));

    // Alert de éxito estándar del sistema (modal "¡Éxito!" con botón Aceptar); al aceptar se recarga la tabla.
    function exitoYRecargar(mensaje) {
        Swal.fire({
            title: '¡Éxito!',
            text: mensaje,
            icon: 'success',
            confirmButtonColor: '#10B981',
            confirmButtonText: 'Aceptar'
        }).then(() => location.reload());
    }

    const esc = t => String(t ?? '').replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));

    async function api(url, method, body) {
        const r = await fetch(url, {
            method,
            headers: {'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF},
            body: body ? JSON.stringify(body) : undefined
        });
        const data = await r.json().catch(() => ({}));
        if (!r.ok && !data.mensaje) {
            data.mensaje = data.errors ? Object.values(data.errors).flat().join(' ') : 'Error inesperado';
        }
        return data;
    }

    async function abrirReporte() {
        const optEsp = ESPACIOS.map(e => `<option value="${esc(e.id)}">${esc(e.id)} — ${esc(e.nombre)}</option>`).join('');
        const optCat = Object.entries(CATEGORIAS).map(([k, c]) => `<option value="${k}">${esc(c.label)}</option>`).join('');
        const optSev = Object.entries(SEVERIDADES).map(([k, v]) => `<option value="${k}" ${k==='media'?'selected':''}>${esc(v)}</option>`).join('');

        const { value } = await Swal.fire({
            title: 'Reportar falla',
            html: `<div class="text-left space-y-3">
                <div><label class="block mb-1 text-sm font-semibold">Espacio</label>
                  <select id="r-esp" class="swal2-select" style="width:100%;margin:0"><option value="">Selecciona...</option>${optEsp}</select></div>
                <div><label class="block mb-1 text-sm font-semibold">Tipo de falla</label>
                  <select id="r-cat" class="swal2-select" style="width:100%;margin:0"><option value="">Selecciona...</option>${optCat}</select></div>
                <div><label class="block mb-1 text-sm font-semibold">Severidad</label>
                  <select id="r-sev" class="swal2-select" style="width:100%;margin:0">${optSev}</select></div>
                <div><label class="block mb-1 text-sm font-semibold">Descripción</label>
                  <textarea id="r-desc" class="swal2-textarea" style="width:100%;margin:0" placeholder="Ej: Faltan plumones y borrador"></textarea></div>
                <label class="flex items-center gap-2 text-sm"><input type="checkbox" id="r-bloq">
                  <span>La sala no se puede usar (ponerla en <b>Mantenimiento</b>)</span></label>
            </div>`,
            didOpen: () => {
                const cat = document.getElementById('r-cat'), chk = document.getElementById('r-bloq');
                cat.addEventListener('change', () => { chk.checked = !!(CATEGORIAS[cat.value] && CATEGORIAS[cat.value].bloquea); });
            },
            showCancelButton: true, confirmButtonText: 'Registrar', cancelButtonText: 'Cancelar', focusConfirm: false,
            preConfirm: () => {
                const v = {
                    id_espacio: document.getElementById('r-esp').value,
                    categoria: document.getElementById('r-cat').value,
                    severidad: document.getElementById('r-sev').value,
                    descripcion: document.getElementById('r-desc').value.trim(),
                    bloquea_uso: document.getElementById('r-bloq').checked
                };
                if (!v.id_espacio) return Swal.showValidationMessage('Selecciona el espacio'), false;
                if (!v.categoria) return Swal.showValidationMessage('Selecciona el tipo de falla'), false;
                if (v.descripcion.length < 5) return Swal.showValidationMessage('Describe la falla (mínimo 5 caracteres)'), false;
                return v;
            }
        });
        if (!value) return;

        const data = await api(URL_STORE, 'POST', value);
        if (data.success) {
            exitoYRecargar(data.mensaje);
        } else {
            Swal.fire('Error', data.mensaje || 'No se pudo registrar', 'error');
        }
    }

    async function verIncidencia(id) {
        const data = await api(`${URL_BASE}/${id}`, 'GET');
        if (!data.success) return Swal.fire('Error', data.mensaje || 'No encontrada', 'error');
        const i = data.incidencia;
        const abierta = ['abierta', 'en_proceso'].includes(i.estado);

        const eventos = i.eventos.map(e => {
            const cambio = e.estado_nuevo ? ` <span class="text-slate-400">(${esc(e.estado_anterior || '—')} → ${esc(e.estado_nuevo)})</span>` : '';
            return `<li class="py-1.5 border-b border-slate-100"><div class="text-xs text-slate-400">${esc(e.fecha)} · ${esc(e.usuario)} · ${esc(e.tipo.replace('_',' '))}${cambio}</div>
                    <div class="text-sm">${esc(e.detalle)}</div></li>`;
        }).join('');

        const botones = abierta
            ? `<button id="b-proc" class="swal2-confirm swal2-styled" style="background:#d97706" ${i.estado==='en_proceso'?'disabled':''}>En proceso</button>
               <button id="b-res" class="swal2-confirm swal2-styled" style="background:#059669">Resolver</button>
               <button id="b-desc" class="swal2-cancel swal2-styled" style="background:#64748b">Descartar</button>`
            : `<button id="b-reab" class="swal2-confirm swal2-styled" style="background:#d97706">Reabrir</button>`;

        Swal.fire({
            title: `${esc(i.id_espacio)} · ${esc(i.categoria_label)}`,
            width: 640,
            html: `<div class="text-left text-sm">
                <p class="mb-2">${esc(i.descripcion)}</p>
                <p class="mb-2 text-xs text-slate-500">Severidad: <b>${esc(i.severidad_label)}</b> · Estado: <b>${esc(i.estado_label)}</b>
                  ${i.bloquea_uso ? ' · <b>Sala fuera de servicio mientras esté abierta</b>' : ''}</p>
                ${i.resolucion ? `<p class="p-2 mb-2 rounded bg-emerald-50 text-emerald-800"><b>Resolución:</b> ${esc(i.resolucion)} <span class="text-xs">(${esc(i.resuelto_por || '')} ${esc(i.resuelto_en || '')})</span></p>` : ''}
                <div class="mb-1 font-semibold">Bitácora</div>
                <ul class="overflow-y-auto" style="max-height:220px">${eventos}</ul>
                <div class="flex gap-2 mt-3"><input id="c-txt" class="swal2-input" style="margin:0;flex:1" placeholder="Agregar comentario...">
                  <button id="c-add" class="swal2-confirm swal2-styled" style="margin:0">Comentar</button></div>
                <div class="mt-3 text-center">${botones}</div>
            </div>`,
            showConfirmButton: false, showCloseButton: true,
            didOpen: () => {
                const $ = s => document.getElementById(s);
                $('c-add').onclick = async () => {
                    const t = $('c-txt').value.trim(); if (t.length < 2) return;
                    const r = await api(`${URL_BASE}/${id}/comentarios`, 'POST', {comentario: t});
                    r.success ? verIncidencia(id) : Swal.fire('Error', r.mensaje, 'error');
                };
                const cambiar = async (estado, pedirNota, titulo) => {
                    let nota = null;
                    if (pedirNota) {
                        const r = await Swal.fire({title: titulo, input: 'textarea', inputAttributes: {maxlength: 1000},
                            inputValidator: v => (!v || v.trim().length < 3) ? 'Indica un detalle (mínimo 3 caracteres)' : null,
                            showCancelButton: true, confirmButtonText: 'Confirmar', cancelButtonText: 'Cancelar'});
                        if (!r.isConfirmed) return verIncidencia(id);
                        nota = r.value.trim();
                    }
                    const r2 = await api(`${URL_BASE}/${id}/estado`, 'PUT', {estado, nota});
                    if (r2.success) { exitoYRecargar(r2.mensaje); }
                    else Swal.fire('Error', r2.mensaje, 'error');
                };
                if ($('b-proc')) $('b-proc').onclick = () => cambiar('en_proceso', false);
                if ($('b-res')) $('b-res').onclick = () => cambiar('resuelta', true, '¿Cómo se resolvió?');
                if ($('b-desc')) $('b-desc').onclick = () => cambiar('descartada', true, '¿Por qué se descarta?');
                if ($('b-reab')) $('b-reab').onclick = () => cambiar('abierta', false);
            }
        });
    }
    </script>
    @endpush
</x-app-layout>
