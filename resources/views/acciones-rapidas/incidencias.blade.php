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
                <button type="button" onclick="abrirReporte()"
                    class="inline-flex items-center gap-2 px-4 py-2 bg-rose-600 hover:bg-rose-700 text-white text-sm font-semibold rounded-xl shadow-xs">
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
        $sevClase = [
            'baja' => 'bg-slate-100 text-slate-700',
            'media' => 'bg-sky-100 text-sky-800',
            'alta' => 'bg-amber-100 text-amber-800',
            'critica' => 'bg-red-100 text-red-800',
        ];
        $estClase = [
            'abierta' => 'bg-red-100 text-red-800',
            'en_proceso' => 'bg-amber-100 text-amber-800',
            'resuelta' => 'bg-emerald-100 text-emerald-800',
            'descartada' => 'bg-slate-100 text-slate-600',
        ];
    @endphp

    <div class="space-y-6">
        {{-- KPIs --}}
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div class="p-4 bg-white border border-slate-200/80 rounded-2xl">
                <div class="text-xs font-medium text-slate-500">Incidencias abiertas</div>
                <div class="text-2xl font-bold text-slate-900">{{ $stats['abiertas'] }}</div>
            </div>
            <div class="p-4 bg-white border border-slate-200/80 rounded-2xl">
                <div class="text-xs font-medium text-slate-500">Graves (alta/crítica)</div>
                <div class="text-2xl font-bold text-red-700">{{ $stats['graves_abiertas'] }}</div>
            </div>
            <div class="p-4 bg-white border border-slate-200/80 rounded-2xl">
                <div class="text-xs font-medium text-slate-500">Salas fuera de servicio</div>
                <div class="text-2xl font-bold text-amber-700">{{ $stats['salas_bloqueadas'] }}</div>
            </div>
            <div class="p-4 bg-white border border-slate-200/80 rounded-2xl">
                <div class="text-xs font-medium text-slate-500">Tiempo medio de resolución (90 d)</div>
                <div class="text-2xl font-bold text-slate-900">
                    {{ $stats['tiempo_medio_horas'] !== null ? $stats['tiempo_medio_horas'] . ' h' : '—' }}
                </div>
            </div>
        </div>

        @if (!empty($stats['por_categoria_90d']) || !empty($stats['reincidentes_90d']))
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="p-4 bg-white border border-slate-200/80 rounded-2xl">
                    <div class="mb-2 text-sm font-semibold text-slate-700">Fallas más frecuentes (90 días)</div>
                    @foreach ($stats['por_categoria_90d'] as $cat => $total)
                        <div class="flex justify-between py-0.5 text-sm text-slate-600">
                            <span>{{ $categorias[$cat]['label'] ?? $cat }}</span><span class="font-semibold">{{ $total }}</span>
                        </div>
                    @endforeach
                </div>
                <div class="p-4 bg-white border border-slate-200/80 rounded-2xl">
                    <div class="mb-2 text-sm font-semibold text-slate-700">Espacios reincidentes (90 días)</div>
                    @forelse ($stats['reincidentes_90d'] as $esp => $total)
                        <div class="flex justify-between py-0.5 text-sm text-slate-600">
                            <span>{{ $esp }}</span><span class="font-semibold">{{ $total }} incidencias</span>
                        </div>
                    @empty
                        <div class="text-sm text-slate-400">Sin reincidencias.</div>
                    @endforelse
                </div>
            </div>
        @endif

        {{-- Filtros --}}
        <form method="GET" class="grid grid-cols-2 gap-3 p-4 bg-white border border-slate-200/80 rounded-2xl md:grid-cols-6">
            <select name="estado" class="px-3 py-2 text-sm border border-gray-300 rounded-md">
                <option value="pendientes" @selected(($filtros['estado'] ?? '') === 'pendientes')>Pendientes</option>
                <option value="" @selected(array_key_exists('estado', $filtros) && $filtros['estado'] === '')>Todas</option>
                @foreach ($estados as $k => $v)
                    <option value="{{ $k }}" @selected(($filtros['estado'] ?? '') === $k)>{{ $v }}</option>
                @endforeach
            </select>
            <select name="categoria" class="px-3 py-2 text-sm border border-gray-300 rounded-md">
                <option value="">Todas las fallas</option>
                @foreach ($categorias as $k => $c)
                    <option value="{{ $k }}" @selected(($filtros['categoria'] ?? '') === $k)>{{ $c['label'] }}</option>
                @endforeach
            </select>
            <select name="severidad" class="px-3 py-2 text-sm border border-gray-300 rounded-md">
                <option value="">Toda severidad</option>
                @foreach ($severidades as $k => $v)
                    <option value="{{ $k }}" @selected(($filtros['severidad'] ?? '') === $k)>{{ $v }}</option>
                @endforeach
            </select>
            <select name="espacio" class="px-3 py-2 text-sm border border-gray-300 rounded-md">
                <option value="">Todos los espacios</option>
                @foreach ($espacios as $e)
                    <option value="{{ $e->id_espacio }}" @selected(($filtros['espacio'] ?? '') === $e->id_espacio)>{{ $e->id_espacio }} — {{ $e->nombre_espacio }}</option>
                @endforeach
            </select>
            <input type="text" name="buscar" value="{{ $filtros['buscar'] ?? '' }}" placeholder="Buscar..."
                class="px-3 py-2 text-sm border border-gray-300 rounded-md">
            <button class="px-3 py-2 text-sm font-semibold text-white rounded-md bg-slate-700 hover:bg-slate-800">Filtrar</button>
        </form>

        {{-- Tabla --}}
        <div class="overflow-x-auto bg-white border border-slate-200/80 rounded-2xl">
            <table class="min-w-full divide-y divide-slate-200">
                <thead class="bg-slate-50">
                    <tr class="text-xs font-semibold tracking-wider text-left uppercase text-slate-500">
                        <th class="px-4 py-3">Espacio</th>
                        <th class="px-4 py-3">Falla</th>
                        <th class="px-4 py-3">Severidad</th>
                        <th class="px-4 py-3">Estado</th>
                        <th class="px-4 py-3">Reportó</th>
                        <th class="px-4 py-3">Abierta</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($incidencias as $i)
                        <tr class="text-sm hover:bg-slate-50/80">
                            <td class="px-4 py-3">
                                <div class="font-semibold text-slate-900">{{ $i->id_espacio }}</div>
                                <div class="text-xs text-slate-500">{{ $i->espacio?->nombre_espacio }}</div>
                                @if ($i->bloquea_uso && $i->estaAbierta())
                                    <span class="inline-block mt-1 px-2 py-0.5 text-[11px] font-semibold rounded-full bg-amber-100 text-amber-800">Fuera de servicio</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 max-w-xs">
                                <div class="font-medium text-slate-800"><i class="mr-1 fa-solid {{ $i->categoria_icon }} text-slate-400"></i>{{ $i->categoria_label }}</div>
                                <div class="text-xs text-slate-500 line-clamp-2">{{ $i->descripcion }}</div>
                            </td>
                            <td class="px-4 py-3"><span class="px-2.5 py-0.5 text-xs font-semibold rounded-full {{ $sevClase[$i->severidad] ?? '' }}">{{ $i->severidad_label }}</span></td>
                            <td class="px-4 py-3"><span class="px-2.5 py-0.5 text-xs font-semibold rounded-full {{ $estClase[$i->estado] ?? '' }}">{{ $i->estado_label }}</span></td>
                            <td class="px-4 py-3 text-xs text-slate-600">
                                {{ $i->reportado_por_nombre ?? '—' }}<br>{{ $i->reportado_en?->format('d-m-Y H:i') }}
                            </td>
                            <td class="px-4 py-3 text-xs text-slate-600">{{ $i->horas_abierta }} h</td>
                            <td class="px-4 py-3 text-right">
                                <button type="button" onclick="verIncidencia({{ $i->id }})"
                                    class="px-3 py-1 text-xs font-semibold border rounded-lg text-slate-700 border-slate-300 hover:bg-slate-100">Ver / gestionar</button>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="px-4 py-10 text-sm text-center text-slate-400">No hay incidencias con estos filtros.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div>{{ $incidencias->links() }}</div>
    </div>

    @push('scripts')
    <script>
    const CSRF = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    const CATEGORIAS = @json(collect($categorias)->map(fn ($c) => ['label' => $c['label'], 'bloquea' => $c['bloquea_por_defecto']]));
    const SEVERIDADES = @json($severidades);
    const ESPACIOS = @json($espacios->map(fn ($e) => ['id' => $e->id_espacio, 'nombre' => $e->nombre_espacio]));
    const URL_STORE = @json(route('quick-actions.api.incidencias.store'));
    const URL_BASE = @json(url('quick-actions/api/incidencias'));

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
            await Swal.fire({title: 'Registrada', text: data.mensaje, icon: 'success', timer: 2200, showConfirmButton: false});
            location.reload();
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
                    if (r2.success) { await Swal.fire({title: 'Listo', text: r2.mensaje, icon: 'success', timer: 2200, showConfirmButton: false}); location.reload(); }
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
