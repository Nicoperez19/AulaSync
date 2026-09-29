@php
// Mapeo de colores para tipo de espacio
$coloresTipo = [
    'Sala de Clases' => 'bg-blue-500',
    'Laboratorio' => 'bg-amber-400',
    'Auditorio' => 'bg-indigo-600',
    'Sala de Estudio' => 'bg-purple-500',
    'Otro' => 'bg-slate-400',
];
@endphp

<!-- Leyenda y Botón Actualizar -->
<div class="flex flex-col xl:flex-row justify-between items-start xl:items-center gap-4 mb-6 bg-white border border-slate-200/80 p-4 rounded-2xl shadow-2xs">
    <div class="flex flex-wrap lg:flex-nowrap items-center gap-6 text-xs w-full xl:w-auto">
        <!-- Grupo: Tipo de Espacio -->
        <div class="flex flex-col gap-1.5">
            <span class="text-[10px] font-bold tracking-wider text-slate-400 uppercase">Tipo de Espacio</span>
            <div class="flex flex-wrap items-center gap-3 sm:gap-4 text-slate-600 font-normal">
                <span class="flex items-center gap-1.5">
                    <span class="inline-block w-2.5 h-2.5 rounded-full bg-blue-500"></span>
                    <span>Sala de clases</span>
                </span>
                <span class="flex items-center gap-1.5">
                    <span class="inline-block w-2.5 h-2.5 rounded-full bg-amber-400"></span>
                    <span>Laboratorio</span>
                </span>
                <span class="flex items-center gap-1.5">
                    <span class="inline-block w-2.5 h-2.5 rounded-full bg-indigo-600"></span>
                    <span>Auditorio</span>
                </span>
                <span class="flex items-center gap-1.5">
                    <span class="inline-block w-2.5 h-2.5 rounded-full bg-purple-500"></span>
                    <span>Sala de estudio</span>
                </span>
                <span class="flex items-center gap-1.5">
                    <span class="inline-block w-2.5 h-2.5 rounded-full bg-slate-400"></span>
                    <span>Otro</span>
                </span>
            </div>
        </div>

        <!-- Divisor vertical -->
        <div class="hidden xl:block w-px h-9 bg-slate-200 mx-1"></div>

        <!-- Grupo: Estado -->
        <div class="flex flex-col gap-1.5">
            <span class="text-[10px] font-bold tracking-wider text-slate-400 uppercase">Estado</span>
            <div class="flex flex-wrap items-center gap-3 sm:gap-4 text-slate-600 font-normal">
                <span class="flex items-center gap-1.5">
                    <span class="inline-block w-2.5 h-2.5 rounded-full bg-indigo-600"></span>
                    <span>En sala</span>
                </span>
                <span class="flex items-center gap-1.5">
                    <span class="inline-block w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
                    <span>Disponible</span>
                </span>
                <span class="flex items-center gap-1.5">
                    <span class="inline-block w-2.5 h-2.5 rounded-full bg-amber-500"></span>
                    <span>En espera / Próxima</span>
                </span>
                <span class="flex items-center gap-1.5">
                    <span class="inline-block w-2.5 h-2.5 rounded-full bg-blue-500"></span>
                    <span>Finalizada</span>
                </span>
                <span class="flex items-center gap-1.5">
                    <span class="inline-block w-2.5 h-2.5 rounded-full bg-rose-500"></span>
                    <span>Ausente</span>
                </span>
            </div>
        </div>
    </div>
    
    <!-- Botón Actualizar -->
    <button onclick="cargarHorarioActual()" class="flex items-center gap-2 px-3.5 py-1.5 text-xs font-semibold text-slate-700 bg-white border border-slate-200 rounded-xl hover:bg-slate-50 hover:text-blue-600 transition-colors duration-200 shadow-2xs shrink-0 self-end xl:self-center" id="btn-actualizar">
        <i id="btn-sync-icon" class="fas fa-sync-alt text-slate-500"></i>
        <span>Actualizar</span>
    </button>
</div>

@if(!$moduloActualNum)
    <div class="text-slate-500 text-center py-12">
        <i class="far fa-calendar-times text-3xl text-slate-300 mb-2 block"></i>
        <p class="font-medium text-slate-600">No hay módulo actual en este momento.</p>
    </div>
@else
    <!-- Título del Módulo Actual -->
    <div class="mb-5 flex items-center gap-2.5 text-base sm:text-lg font-bold text-slate-800">
        <i class="fa-solid fa-clock text-slate-700 text-lg sm:text-xl"></i>
        <span>{{ ucfirst($diaActual) }} - Módulo Actual {{ $moduloActualNum }} ({{ substr($moduloActualHorario['inicio'],0,5) }} - {{ substr($moduloActualHorario['fin'],0,5) }})</span>
    </div>

    @if($asignaciones->isEmpty())
        <div class="text-center text-slate-500 py-12">
            <i class="fas fa-info-circle text-2xl text-slate-300 mb-2 block"></i>
            No hay asignaciones para este módulo.
        </div>
    @else
        @php
            $chunks = $asignaciones->chunk(8);
        @endphp

        <div class="relative w-full px-1" id="carousel-container" data-current-slide="0" data-total-slides="{{ $chunks->count() }}">
            <!-- Wrapper para centrar controles verticalmente relativo a las tarjetas -->
            <div class="relative w-full">
                <!-- Viewport del Carousel con overflow oculto -->
                <div class="overflow-hidden w-full" id="carousel-viewport">
                    <!-- Slides con transición suave -->
                    <div class="flex transition-transform duration-[1200ms] ease-in-out" id="carousel-slides" style="transform: translateX(0%);">
                        @foreach($chunks as $index => $chunk)
                            <div class="w-full flex-shrink-0 px-1 pb-1">
                                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                                    @foreach($chunk as $asig)
                                        @php
                                            $tipoLower = mb_strtolower($asig->espacio->tipo_espacio ?? 'otro');
                                            if (str_contains($tipoLower, 'laboratorio')) {
                                                $colorTipo = 'bg-amber-400';
                                                $barTipo = 'bg-amber-400';
                                            } elseif (str_contains($tipoLower, 'auditorio')) {
                                                $colorTipo = 'bg-indigo-600';
                                                $barTipo = 'bg-indigo-600';
                                            } elseif (str_contains($tipoLower, 'estudio')) {
                                                $colorTipo = 'bg-purple-500';
                                                $barTipo = 'bg-purple-500';
                                            } elseif (str_contains($tipoLower, 'aula') || str_contains($tipoLower, 'clase') || str_contains($tipoLower, 'sala')) {
                                                $colorTipo = 'bg-blue-500';
                                                $barTipo = 'bg-blue-500';
                                            } else {
                                                $colorTipo = 'bg-slate-400';
                                                $barTipo = 'bg-slate-400';
                                            }

                                            $estadoPres = $asig->estado_presencia ?? ($asig->profesor_presente ? 'en_sala' : 'ausente');
                                        @endphp
                                        <div class="relative bg-white rounded-2xl border border-slate-200/80 p-4 pl-5 flex flex-col justify-between gap-3 shadow-xs hover:shadow-md transition-all duration-200 min-h-[148px] overflow-hidden">
                                            <!-- Borde de acento izquierdo para Tipo de Espacio -->
                                            <div class="absolute left-0 top-0 bottom-0 w-[5px] {{ $barTipo }}"></div>

                                            <div>
                                                <!-- Fila superior: Tipo + Nombre de Espacio + Piso | Badge de Estado -->
                                                <div class="flex items-center justify-between gap-2 mb-2">
                                                    <div class="flex items-center gap-2 min-w-0">
                                                        <span class="inline-block w-2.5 h-2.5 rounded-full {{ $colorTipo }} shrink-0" title="Tipo: {{ $asig->espacio->tipo_espacio ?? 'Otro' }}"></span>
                                                        <span class="font-bold text-base text-slate-800 leading-none truncate">{{ $asig->espacio->id_espacio }}</span>
                                                        <span class="text-[11px] font-medium text-slate-500 bg-slate-100 px-2 py-0.5 rounded shrink-0">Piso {{ $asig->espacio->piso->numero_piso ?? '-' }}</span>
                                                    </div>

                                                    @if($estadoPres === 'disponible')
                                                        <span class="text-[11px] font-bold text-emerald-700 bg-emerald-50 border border-emerald-200/90 px-2.5 py-0.5 rounded-full flex items-center gap-1.5 shrink-0">
                                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                                            Disponible
                                                        </span>
                                                    @elseif($estadoPres === 'en_sala')
                                                        <span class="text-[11px] font-bold text-indigo-700 bg-indigo-50 border border-indigo-200/90 px-2.5 py-0.5 rounded-full flex items-center gap-1.5 shrink-0">
                                                            <span class="w-1.5 h-1.5 rounded-full bg-indigo-600"></span>
                                                            En Sala
                                                        </span>
                                                    @elseif($estadoPres === 'finalizada')
                                                        <span class="text-[11px] font-bold text-blue-700 bg-blue-50 border border-blue-200/90 px-2.5 py-0.5 rounded-full flex items-center gap-1.5 shrink-0" title="{{ !empty($asig->hora_salida) ? 'Finalizada a las ' . substr($asig->hora_salida, 0, 5) : 'Clase finalizada' }}">
                                                            <span class="w-1.5 h-1.5 rounded-full bg-blue-500"></span>
                                                            Finalizada{{ !empty($asig->hora_salida) ? ' (' . substr($asig->hora_salida, 0, 5) . ')' : '' }}
                                                        </span>
                                                    @elseif($estadoPres === 'espera')
                                                        <span class="text-[11px] font-bold text-amber-700 bg-amber-50 border border-amber-200/90 px-2.5 py-0.5 rounded-full flex items-center gap-1.5 shrink-0">
                                                            <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                                            En Espera
                                                        </span>
                                                    @elseif($estadoPres === 'proxima')
                                                        <span class="text-[11px] font-bold text-amber-800 bg-amber-50 border border-amber-300/90 px-2.5 py-0.5 rounded-full flex items-center gap-1.5 shrink-0" title="Próxima clase programada">
                                                            <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span>
                                                            Próxima{{ !empty($asig->hora_inicio_proxima) ? ' (' . $asig->hora_inicio_proxima . ')' : '' }}
                                                        </span>
                                                    @elseif($estadoPres === 'mantencion')
                                                        <span class="text-[11px] font-bold text-slate-600 bg-slate-100 border border-slate-300 px-2.5 py-0.5 rounded-full flex items-center gap-1.5 shrink-0">
                                                            <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span>
                                                            Mantención
                                                        </span>
                                                    @else
                                                        <span class="text-[11px] font-bold text-rose-700 bg-rose-50 border border-rose-200/90 px-2.5 py-0.5 rounded-full flex items-center gap-1.5 shrink-0">
                                                            <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                                                            Ausente
                                                        </span>
                                                    @endif
                                                </div>

                                                <!-- Asignatura o Título de Estado -->
                                                <div class="font-bold text-sm text-slate-800 line-clamp-2 leading-snug" title="{{ $asig->nombre_asignatura }}">
                                                    {{ $asig->nombre_asignatura }}
                                                </div>
                                            </div>

                                            <!-- Información Inferior (Profesor / Espacio Libre) -->
                                            <div class="pt-1 shrink-0 flex flex-col gap-1 text-slate-500">
                                                @if($estadoPres === 'disponible')
                                                    <div class="flex items-center gap-2 text-xs text-slate-500 font-medium truncate">
                                                        <i class="fa-regular fa-calendar text-slate-400 shrink-0 w-3.5 text-center"></i>
                                                        <span class="truncate">Sin clase programada</span>
                                                    </div>
                                                    <div class="flex items-center gap-2 text-[11px] text-slate-400 truncate">
                                                        <i class="fa-solid fa-circle text-[5px] text-slate-400 shrink-0 w-3.5 text-center"></i>
                                                        <span class="truncate">Espacio libre para uso</span>
                                                    </div>
                                                @elseif($estadoPres === 'mantencion')
                                                    <div class="flex items-center gap-2 text-xs text-slate-500 font-medium truncate">
                                                        <i class="fas fa-tools text-slate-400 shrink-0 w-3.5 text-center"></i>
                                                        <span class="truncate">Fuera de Servicio</span>
                                                    </div>
                                                    <div class="flex items-center gap-2 text-[11px] text-slate-400 truncate">
                                                        <i class="fas fa-info-circle text-slate-400 shrink-0 w-3.5 text-center"></i>
                                                        <span class="truncate">Mantenimiento preventivo / correctivo</span>
                                                    </div>
                                                @else
                                                    <div class="flex items-center gap-2 text-xs text-slate-600 font-medium truncate" title="{{ $asig->profesor_name }}">
                                                        <i class="fa-regular fa-user text-slate-400 shrink-0 w-3.5 text-center"></i>
                                                        <span class="truncate">{{ $asig->profesor_name }}</span>
                                                    </div>
                                                    <div class="flex items-center gap-2 text-[11px] text-slate-400 truncate" title="{{ $asig->profesor_email }}">
                                                        <i class="fa-regular fa-envelope text-slate-400 shrink-0 w-3.5 text-center"></i>
                                                        <span class="truncate">{{ $asig->profesor_email }}</span>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- Controles Prev / Next -->
                @if($chunks->count() > 1)
                    <!-- Botón Anterior -->
                    <button onclick="prevSlide()" class="absolute left-0 top-1/2 transform -translate-y-1/2 bg-white/95 hover:bg-white text-slate-700 p-2 rounded-full shadow-md border border-slate-200/90 hover:text-blue-600 hover:scale-105 transition-all duration-150 z-10 -ml-2 sm:-ml-4 flex items-center justify-center w-8 h-8 sm:w-9 sm:h-9" id="btn-prev" aria-label="Anterior">
                        <i class="fas fa-chevron-left text-xs sm:text-sm"></i>
                    </button>
                    <!-- Botón Siguiente -->
                    <button onclick="nextSlide()" class="absolute right-0 top-1/2 transform -translate-y-1/2 bg-white/95 hover:bg-white text-slate-700 p-2 rounded-full shadow-md border border-slate-200/90 hover:text-blue-600 hover:scale-105 transition-all duration-150 z-10 -mr-2 sm:-mr-4 flex items-center justify-center w-8 h-8 sm:w-9 sm:h-9" id="btn-next" aria-label="Siguiente">
                        <i class="fas fa-chevron-right text-xs sm:text-sm"></i>
                    </button>
                @endif
            </div>

            <!-- Indicadores de posición -->
            @if($chunks->count() > 1)
                <div class="flex justify-center items-center gap-2 mt-5" id="carousel-indicators">
                    @foreach($chunks as $index => $chunk)
                        <button onclick="goToSlide({{ $index }})" class="h-2 rounded-full transition-all duration-200 {{ $index === 0 ? 'bg-blue-600 w-6' : 'bg-slate-200 w-2 hover:bg-slate-300' }}" id="indicator-{{ $index }}"></button>
                    @endforeach
                </div>
            @endif
        </div>
    @endif
@endif
