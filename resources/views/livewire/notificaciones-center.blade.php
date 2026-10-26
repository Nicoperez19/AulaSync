@php
    // Clases Tailwind por color lógico (deben escribirse completas para que Tailwind las compile)
    $estilos = [
        'red' => [
            'icono' => 'bg-gradient-to-br from-red-500 to-red-700 shadow-red-200',
            'barra' => 'bg-red-500',
            'chip' => 'bg-red-50 text-red-700 ring-red-200 dark:bg-red-900/30 dark:text-red-300 dark:ring-red-800',
        ],
        'emerald' => [
            'icono' => 'bg-gradient-to-br from-emerald-400 to-emerald-600 shadow-emerald-200',
            'barra' => 'bg-emerald-500',
            'chip' => 'bg-emerald-50 text-emerald-700 ring-emerald-200 dark:bg-emerald-900/30 dark:text-emerald-300 dark:ring-emerald-800',
        ],
        'amber' => [
            'icono' => 'bg-gradient-to-br from-amber-400 to-amber-600 shadow-amber-200',
            'barra' => 'bg-amber-500',
            'chip' => 'bg-amber-50 text-amber-700 ring-amber-200 dark:bg-amber-900/30 dark:text-amber-300 dark:ring-amber-800',
        ],
        'orange' => [
            'icono' => 'bg-gradient-to-br from-orange-400 to-orange-600 shadow-orange-200',
            'barra' => 'bg-orange-500',
            'chip' => 'bg-orange-50 text-orange-700 ring-orange-200 dark:bg-orange-900/30 dark:text-orange-300 dark:ring-orange-800',
        ],
        'blue' => [
            'icono' => 'bg-gradient-to-br from-blue-400 to-blue-600 shadow-blue-200',
            'barra' => 'bg-blue-500',
            'chip' => 'bg-blue-50 text-blue-700 ring-blue-200 dark:bg-blue-900/30 dark:text-blue-300 dark:ring-blue-800',
        ],
        'slate' => [
            'icono' => 'bg-gradient-to-br from-slate-400 to-slate-600 shadow-slate-200',
            'barra' => 'bg-slate-400',
            'chip' => 'bg-slate-100 text-slate-700 ring-slate-200 dark:bg-slate-700/40 dark:text-slate-300 dark:ring-slate-600',
        ],
    ];

    $inputBase = 'h-10 px-3 text-sm text-gray-700 bg-white border border-gray-300 rounded-lg shadow-sm
                  focus:ring-2 focus:ring-[#D2091E]/40 focus:border-[#D2091E] dark:bg-dark-eval-2 dark:text-gray-100 dark:border-gray-600';
@endphp

<div class="w-full max-w-6xl pb-10 mx-auto space-y-5">

    {{-- ═════════════ HERO ═════════════ --}}
    <div class="relative p-6 overflow-hidden text-white shadow-xl rounded-2xl bg-gradient-to-r from-[#D2091E] via-[#B30719] to-[#7A0310] sm:p-7">
        <div class="absolute rounded-full pointer-events-none -right-10 -top-16 w-60 h-60 bg-white/10"></div>
        <div class="absolute rounded-full pointer-events-none right-24 -bottom-20 w-52 h-52 bg-white/5"></div>

        <div class="relative flex flex-col gap-5 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex items-center gap-4">
                <div class="relative flex items-center justify-center w-16 h-16 text-3xl rounded-2xl bg-white/20 ring-1 ring-white/40 backdrop-blur">
                    <i class="fa-solid fa-bell {{ $sinLeer > 0 ? 'animate-[wiggle_1.2s_ease-in-out_infinite]' : '' }}"></i>
                    @if ($sinLeer > 0)
                        <span class="absolute flex w-4 h-4 -right-1 -top-1">
                            <span class="absolute inline-flex w-full h-full bg-yellow-300 rounded-full opacity-75 animate-ping"></span>
                            <span class="relative inline-flex w-4 h-4 bg-yellow-300 border-2 border-[#B30719] rounded-full"></span>
                        </span>
                    @endif
                </div>

                <div>
                    <h2 class="text-2xl font-extrabold tracking-tight sm:text-3xl">Centro de Notificaciones</h2>
                    <p class="mt-1 text-sm text-white/85">
                        Historial completo de avisos: clases no realizadas, reagendamientos y respuestas de Soporte
                    </p>
                    @if ($sedeNombre)
                        <span class="inline-flex items-center gap-2 px-3 py-1 mt-3 text-xs font-semibold rounded-full bg-white/20 ring-1 ring-white/30">
                            <i class="fa-solid fa-building"></i> Sede {{ $sedeNombre }}
                        </span>
                    @endif
                </div>
            </div>

            <div class="flex items-center gap-3 px-5 py-3 rounded-xl bg-white/15 ring-1 ring-white/30 backdrop-blur sm:flex-col sm:gap-0 sm:text-center">
                <span class="text-4xl font-black leading-none">{{ $sinLeer }}</span>
                <span class="text-xs font-semibold tracking-wide uppercase text-white/85">
                    sin leer
                </span>
            </div>
        </div>
    </div>

    {{-- ═════════════ TARJETAS DE RESUMEN ═════════════ --}}
    <div class="grid gap-4 md:grid-cols-3">
        <button type="button" wire:click="limpiarFiltros"
            class="flex items-center gap-4 p-4 text-left transition bg-white border-l-4 border-gray-800 shadow-sm rounded-xl hover:shadow-md hover:-translate-y-0.5 dark:bg-dark-eval-1">
            <div class="flex items-center justify-center w-12 h-12 text-xl text-gray-700 bg-gray-100 rounded-xl dark:bg-dark-eval-3 dark:text-gray-200">
                <i class="fa-solid fa-inbox"></i>
            </div>
            <div>
                <p class="text-xs font-semibold tracking-wide text-gray-500 uppercase">Total</p>
                <p class="text-3xl font-extrabold text-gray-900 dark:text-white">{{ $total }}</p>
            </div>
        </button>

        <button type="button" wire:click="$set('estado', 'no_leidas')"
            class="flex items-center gap-4 p-4 text-left transition bg-white border-l-4 border-[#D2091E] shadow-sm rounded-xl hover:shadow-md hover:-translate-y-0.5 dark:bg-dark-eval-1">
            <div class="flex items-center justify-center w-12 h-12 text-xl text-[#D2091E] bg-red-50 rounded-xl dark:bg-red-900/30">
                <i class="fa-solid fa-envelope-open-text"></i>
            </div>
            <div>
                <p class="text-xs font-semibold tracking-wide text-gray-500 uppercase">Sin leer</p>
                <p class="text-3xl font-extrabold text-[#D2091E]">{{ $sinLeer }}</p>
            </div>
        </button>

        <div class="p-4 bg-white border-l-4 border-blue-500 shadow-sm rounded-xl dark:bg-dark-eval-1">
            <p class="mb-2 text-xs font-semibold tracking-wide text-gray-500 uppercase">Por tipo</p>
            @if (count($porTipo))
                <div class="flex flex-wrap gap-2">
                    @foreach ($porTipo as $t => $cantidad)
                        @php
                            $m = \App\Models\Notificacion::metaTipo($t);
                            $e = $estilos[$m['color']] ?? $estilos['slate'];
                            $activo = $tipo === $t;
                        @endphp
                        <button type="button" wire:click="filtrarPorTipo('{{ $t }}')" wire:key="chip-{{ $t }}"
                            title="Filtrar por {{ $m['label'] }}"
                            class="inline-flex items-center gap-1.5 px-2.5 py-1 text-xs font-semibold rounded-full ring-1 transition {{ $e['chip'] }} {{ $activo ? 'ring-2 scale-105 shadow' : 'hover:scale-105' }}">
                            <i class="fa-solid {{ $m['icon'] }}"></i>
                            {{ $m['label'] }}
                            <span class="px-1.5 rounded-full bg-white/70 dark:bg-black/20">{{ $cantidad }}</span>
                        </button>
                    @endforeach
                </div>
            @else
                <p class="text-sm text-gray-400">Sin notificaciones</p>
            @endif
        </div>
    </div>

    {{-- ═════════════ FILTROS + ACCIONES (juntos) ═════════════ --}}
    <div class="p-4 space-y-3 bg-white border border-gray-200 shadow-sm rounded-2xl dark:bg-dark-eval-1 dark:border-gray-700">

        {{-- Fila 1: búsqueda, tipo, estado --}}
        <div class="flex flex-wrap items-center gap-3">
            <div class="relative flex-1 min-w-[220px]">
                <i class="absolute text-gray-400 -translate-y-1/2 fa-solid fa-magnifying-glass left-3 top-1/2"></i>
                <input type="search" wire:model.live.debounce.350ms="search"
                    placeholder="Buscar en título, mensaje, sala o profesor..."
                    class="{{ $inputBase }} w-full pl-9" aria-label="Buscar notificaciones">
                <span wire:loading wire:target="search" class="absolute -translate-y-1/2 right-3 top-1/2">
                    <i class="text-gray-400 fa-solid fa-circle-notch fa-spin"></i>
                </span>
            </div>

            <select wire:model.live="tipo" class="{{ $inputBase }} min-w-[190px]" aria-label="Filtrar por tipo">
                <option value="">Todos los tipos</option>
                @foreach ($tiposDisponibles as $clave => $etiqueta)
                    <option value="{{ $clave }}">{{ $etiqueta }}</option>
                @endforeach
            </select>

            <div class="inline-flex p-1 bg-gray-100 rounded-lg dark:bg-dark-eval-3" role="group" aria-label="Filtrar por estado">
                @foreach (['' => 'Todas', 'no_leidas' => 'Sin leer', 'leidas' => 'Leídas'] as $valor => $texto)
                    <button type="button" wire:click="$set('estado', '{{ $valor }}')" wire:key="estado-{{ $valor ?: 'todas' }}"
                        class="px-3 py-1.5 text-sm font-semibold rounded-md transition
                            {{ $estado === $valor
                                ? 'bg-[#D2091E] text-white shadow'
                                : 'text-gray-600 hover:bg-white dark:text-gray-300 dark:hover:bg-dark-eval-1' }}">
                        {{ $texto }}
                    </button>
                @endforeach
            </div>
        </div>

        {{-- Fila 2: fechas + atajos + ACCIONES (pegadas a los filtros) --}}
        <div class="flex flex-wrap items-center gap-x-3 gap-y-2">
            <label class="flex items-center gap-2 text-sm font-medium text-gray-600 dark:text-gray-300">
                Desde
                <input type="date" wire:model.live="desde" max="{{ now()->toDateString() }}" class="{{ $inputBase }}">
            </label>
            <label class="flex items-center gap-2 text-sm font-medium text-gray-600 dark:text-gray-300">
                Hasta
                <input type="date" wire:model.live="hasta" max="{{ now()->toDateString() }}" class="{{ $inputBase }}">
            </label>

            <div class="flex items-center gap-1.5">
                @foreach (['hoy' => 'Hoy', '7d' => '7 días', '30d' => '30 días'] as $clave => $texto)
                    <button type="button" wire:click="rangoRapido('{{ $clave }}')"
                        class="px-2.5 py-1 text-xs font-semibold text-gray-600 transition bg-gray-100 rounded-full hover:bg-gray-200 dark:bg-dark-eval-3 dark:text-gray-300">
                        {{ $texto }}
                    </button>
                @endforeach
            </div>

            <span class="hidden w-px h-7 bg-gray-300 sm:block dark:bg-gray-600"></span>

            <button type="button" wire:click="marcarTodasComoLeidas" wire:loading.attr="disabled"
                @disabled($sinLeer === 0)
                class="inline-flex items-center gap-2 h-10 px-4 text-sm font-bold text-white transition rounded-lg shadow-sm bg-[#D2091E] hover:bg-[#B30719] disabled:opacity-40 disabled:cursor-not-allowed">
                <i class="fa-solid fa-check-double"></i>
                Marcar todas como leídas
                @if ($sinLeer > 0)
                    <span class="px-1.5 text-xs rounded-full bg-white/25">{{ $sinLeer }}</span>
                @endif
            </button>

            <button type="button" wire:click="eliminarLeidas" wire:loading.attr="disabled"
                wire:confirm="¿Eliminar las {{ $leidas }} notificaciones leídas? Esta acción no se puede deshacer."
                @disabled($leidas === 0)
                class="inline-flex items-center gap-2 h-10 px-4 text-sm font-bold text-[#D2091E] transition bg-red-50 border border-red-200 rounded-lg hover:bg-red-100 disabled:opacity-40 disabled:cursor-not-allowed dark:bg-red-900/20 dark:border-red-800">
                <i class="fa-solid fa-trash-can"></i>
                Eliminar leídas
                @if ($leidas > 0)
                    <span class="px-1.5 text-xs bg-white rounded-full dark:bg-black/20">{{ $leidas }}</span>
                @endif
            </button>

            @if ($hayFiltros)
                <button type="button" wire:click="limpiarFiltros"
                    class="inline-flex items-center gap-1.5 h-10 px-3 text-sm font-semibold text-gray-500 transition rounded-lg hover:text-[#D2091E] hover:bg-gray-100 dark:hover:bg-dark-eval-3">
                    <i class="fa-solid fa-filter-circle-xmark"></i> Limpiar filtros
                </button>
            @endif
        </div>

        <p class="text-xs text-gray-500 dark:text-gray-400" wire:loading.remove wire:target="search,tipo,estado,desde,hasta,rangoRapido,limpiarFiltros,filtrarPorTipo">
            @if ($hayFiltros)
                {{ $coincidencias }} {{ $coincidencias === 1 ? 'resultado' : 'resultados' }} de {{ $total }}
            @else
                Mostrando {{ $mostradas }} de {{ $coincidencias }}
            @endif
        </p>
    </div>

    {{-- ═════════════ LISTA ═════════════ --}}
    <div class="relative space-y-5" wire:loading.class="opacity-60" wire:target="search,tipo,estado,desde,hasta,rangoRapido,limpiarFiltros,filtrarPorTipo">

        @forelse ($grupos as $dia => $items)
            <section wire:key="dia-{{ \Illuminate\Support\Str::slug($dia) }}">
                <h3 class="flex items-center gap-3 mb-2 text-xs font-bold tracking-wider text-gray-500 uppercase dark:text-gray-400">
                    <span>{{ $dia }}</span>
                    <span class="flex-1 h-px bg-gray-200 dark:bg-gray-700"></span>
                    <span class="px-2 py-0.5 text-[11px] bg-gray-200 rounded-full dark:bg-dark-eval-3">{{ $items->count() }}</span>
                </h3>

                <div class="space-y-3">
                    @foreach ($items as $n)
                        @php
                            $m = \App\Models\Notificacion::metaTipo($n->tipo);
                            $e = $estilos[$m['color']] ?? $estilos['slate'];
                        @endphp

                        <article wire:key="n-{{ $n->id }}"
                            class="group relative flex gap-4 p-4 pl-5 overflow-hidden transition border shadow-sm rounded-xl hover:shadow-lg hover:-translate-y-0.5
                                {{ $n->leida
                                    ? 'bg-white border-gray-200 dark:bg-dark-eval-1 dark:border-gray-700'
                                    : 'bg-gradient-to-r from-red-50/80 to-white border-red-200 dark:from-red-900/20 dark:to-dark-eval-1 dark:border-red-900/50' }}">

                            <span class="absolute inset-y-0 left-0 w-1.5 {{ $n->leida ? 'opacity-30' : '' }} {{ $e['barra'] }}"></span>

                            <div class="flex items-center justify-center flex-shrink-0 w-12 h-12 text-xl text-white shadow-lg rounded-xl {{ $e['icono'] }}">
                                <i class="fa-solid {{ $m['icon'] }}"></i>
                            </div>

                            <div class="flex-1 min-w-0 cursor-pointer" wire:click="abrir({{ $n->id }})">
                                <div class="flex flex-wrap items-center gap-2">
                                    <h4 class="text-base font-bold text-gray-900 dark:text-white {{ $n->leida ? 'font-semibold' : '' }}">
                                        {{ $n->titulo }}
                                    </h4>
                                    @unless ($n->leida)
                                        <span class="px-2 py-0.5 text-[10px] font-black tracking-wide text-white uppercase rounded-full bg-[#D2091E]">Nueva</span>
                                    @endunless
                                    <span class="px-2 py-0.5 text-[11px] font-semibold rounded-full ring-1 {{ $e['chip'] }}">{{ $m['label'] }}</span>
                                </div>

                                <p class="mt-1 text-sm text-gray-600 break-words dark:text-gray-300">{{ $n->mensajeFormateado() }}</p>

                                <p class="mt-2 text-xs text-gray-400" title="{{ $n->created_at->format('d-m-Y H:i') }}">
                                    <i class="mr-1 fa-regular fa-clock"></i>
                                    {{ $n->created_at->locale('es')->diffForHumans() }}
                                    · {{ $n->created_at->format('d-m-Y H:i') }}
                                    @if ($n->leida && $n->fecha_lectura)
                                        · <i class="fa-solid fa-check"></i> leída {{ $n->fecha_lectura->locale('es')->diffForHumans() }}
                                    @endif
                                </p>
                            </div>

                            {{-- Acciones por notificación --}}
                            <div class="flex flex-col items-end justify-center flex-shrink-0 gap-1.5 sm:flex-row sm:items-center">
                                <button type="button" wire:click="abrir({{ $n->id }})" title="Abrir"
                                    class="flex items-center justify-center w-9 h-9 text-gray-500 transition rounded-lg hover:text-white hover:bg-[#D2091E] bg-gray-100 dark:bg-dark-eval-3">
                                    <i class="fa-solid fa-arrow-up-right-from-square"></i>
                                </button>

                                @if ($n->leida)
                                    <button type="button" wire:click="marcarComoNoLeida({{ $n->id }})" title="Marcar como no leída"
                                        class="flex items-center justify-center text-gray-500 transition bg-gray-100 rounded-lg w-9 h-9 hover:text-white hover:bg-gray-700 dark:bg-dark-eval-3">
                                        <i class="fa-regular fa-envelope"></i>
                                    </button>
                                @else
                                    <button type="button" wire:click="marcarComoLeida({{ $n->id }})" title="Marcar como leída"
                                        class="flex items-center justify-center text-gray-500 transition bg-gray-100 rounded-lg w-9 h-9 hover:text-white hover:bg-emerald-600 dark:bg-dark-eval-3">
                                        <i class="fa-solid fa-check"></i>
                                    </button>
                                @endif

                                <button type="button" wire:click="eliminar({{ $n->id }})"
                                    wire:confirm="¿Eliminar esta notificación?" title="Eliminar"
                                    class="flex items-center justify-center text-gray-500 transition bg-gray-100 rounded-lg w-9 h-9 hover:text-white hover:bg-red-600 dark:bg-dark-eval-3">
                                    <i class="fa-regular fa-trash-can"></i>
                                </button>
                            </div>
                        </article>
                    @endforeach
                </div>
            </section>
        @empty
            <div class="flex flex-col items-center px-6 py-16 text-center bg-white border border-gray-200 border-dashed rounded-2xl dark:bg-dark-eval-1 dark:border-gray-700">
                @if ($hayFiltros)
                    <div class="flex items-center justify-center w-20 h-20 mb-4 text-3xl text-gray-400 bg-gray-100 rounded-full dark:bg-dark-eval-3">
                        <i class="fa-solid fa-magnifying-glass"></i>
                    </div>
                    <p class="text-lg font-bold text-gray-700 dark:text-gray-200">Sin resultados</p>
                    <p class="mt-1 text-sm text-gray-500">Ninguna notificación coincide con los filtros aplicados.</p>
                    <button type="button" wire:click="limpiarFiltros"
                        class="inline-flex items-center gap-2 px-4 py-2 mt-5 text-sm font-bold text-white rounded-lg bg-[#D2091E] hover:bg-[#B30719]">
                        <i class="fa-solid fa-filter-circle-xmark"></i> Limpiar filtros
                    </button>
                @else
                    <div class="flex items-center justify-center w-20 h-20 mb-4 text-3xl text-gray-300 bg-gray-100 rounded-full dark:bg-dark-eval-3">
                        <i class="fa-solid fa-bell-slash"></i>
                    </div>
                    <p class="text-lg font-bold text-gray-700 dark:text-gray-200">No tienes notificaciones</p>
                    <p class="mt-1 text-sm text-gray-500">Cuando haya avisos nuevos en esta sede, aparecerán aquí.</p>
                @endif
            </div>
        @endforelse

        @if ($mostradas < $coincidencias)
            <div class="flex justify-center pt-1">
                <button type="button" wire:click="mostrarMas" wire:loading.attr="disabled"
                    class="inline-flex items-center gap-2 px-5 py-2.5 text-sm font-bold text-[#D2091E] transition bg-white border-2 border-[#D2091E] rounded-full hover:bg-[#D2091E] hover:text-white dark:bg-dark-eval-1">
                    <i class="fa-solid fa-chevron-down" wire:loading.remove wire:target="mostrarMas"></i>
                    <i class="fa-solid fa-circle-notch fa-spin" wire:loading wire:target="mostrarMas"></i>
                    Mostrar más ({{ $coincidencias - $mostradas }} restantes)
                </button>
            </div>
        @endif
    </div>

    <style>
        @keyframes wiggle {
            0%, 100% { transform: rotate(0deg); }
            15% { transform: rotate(14deg); }
            30% { transform: rotate(-12deg); }
            45% { transform: rotate(8deg); }
            60% { transform: rotate(-6deg); }
            75% { transform: rotate(0deg); }
        }
    </style>
</div>
