@php
// Colores por tipo de espacio (sin rojo ni verde para evitar interpretaciones de estado)
$coloresTipo = [
    'Sala de Clases' => 'bg-sky-500',
    'Laboratorio' => 'bg-amber-500',
    'Auditorio' => 'bg-indigo-500',
    'Sala de Estudio' => 'bg-purple-500',
    'Otro' => 'bg-gray-400',
];
@endphp

<!-- Leyenda de Colores y Botón de Actualizar -->
<div class="flex flex-col xl:flex-row justify-between items-start xl:items-center gap-4 mb-6 bg-gray-50 p-3 rounded-xl border border-gray-150">
    <div class="flex flex-wrap gap-y-2 items-center text-xs text-gray-600">
        <span class="font-semibold text-gray-500 mr-1">Leyenda (Tipos de Espacio):</span>
        <span class="flex items-center gap-1.5 mr-4">
            <span class="inline-block w-2.5 h-2.5 rounded-full bg-sky-500"></span>
            <span>Sala de Clases</span>
        </span>
        <span class="flex items-center gap-1.5 mr-4">
            <span class="inline-block w-2.5 h-2.5 rounded-full bg-amber-500"></span>
            <span>Laboratorio</span>
        </span>
        <span class="flex items-center gap-1.5 mr-4">
            <span class="inline-block w-2.5 h-2.5 rounded-full bg-indigo-500"></span>
            <span>Auditorio</span>
        </span>
        <span class="flex items-center gap-1.5 mr-4">
            <span class="inline-block w-2.5 h-2.5 rounded-full bg-purple-500"></span>
            <span>Sala de Estudio</span>
        </span>
        <span class="flex items-center gap-1.5 mr-4">
            <span class="inline-block w-2.5 h-2.5 rounded-full bg-gray-400"></span>
            <span>Otro</span>
        </span>

        <span class="hidden sm:inline border-l border-gray-300 h-4 mx-2"></span>

        <span class="font-semibold text-gray-500 mr-1 sm:ml-2">Presencia Docente:</span>
        <span class="flex items-center gap-1.5 mr-4">
            <span class="inline-block w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
            <span class="text-emerald-700 font-bold">En Sala</span>
        </span>
        <span class="flex items-center gap-1.5">
            <span class="inline-block w-2.5 h-2.5 rounded-full bg-rose-500"></span>
            <span class="text-rose-700 font-bold">Ausente</span>
        </span>
    </div>
    
    <button onclick="cargarHorarioActual()" class="flex items-center gap-2 px-3 py-1.5 text-xs font-semibold text-gray-700 bg-white border border-gray-200 rounded-lg hover:bg-gray-50 hover:text-blue-600 transition-colors duration-200 shadow-sm shrink-0" id="btn-actualizar">
        <i id="btn-sync-icon" class="fas fa-sync-alt"></i>
        Actualizar
    </button>
</div>

@if(!$moduloActualNum)
    <div class="text-gray-500 text-center py-12">
        <i class="far fa-calendar-times text-3xl text-gray-300 mb-2 block"></i>
        <p class="font-medium text-gray-600">No hay módulo actual en este momento.</p>
    </div>
@else
    <div class="mb-5 flex items-center gap-2 text-lg font-semibold text-gray-800">
        <i class="fas fa-clock text-gray-600"></i>
        {{ ucfirst($diaActual) }} - Módulo Actual {{ $moduloActualNum }} ({{ substr($moduloActualHorario['inicio'],0,5) }} - {{ substr($moduloActualHorario['fin'],0,5) }})
    </div>

    @if($asignaciones->isEmpty())
        <div class="text-center text-gray-500 py-12">
            <i class="fas fa-info-circle text-2xl text-gray-300 mb-2 block"></i>
            No hay asignaciones para este módulo.
        </div>
    @else
        @php
            $chunks = $asignaciones->chunk(8);
        @endphp

        <div class="relative w-full px-1" id="carousel-container" data-current-slide="0" data-total-slides="{{ $chunks->count() }}">
            <!-- Wrapper to center controls vertically only relative to cards -->
            <div class="relative w-full">
                <!-- Hidden overflow viewport -->
                <div class="overflow-hidden w-full" id="carousel-viewport">
                    <!-- Slides Wrapper (Slower 1200ms transition) -->
                    <div class="flex transition-transform duration-[1200ms] ease-in-out" id="carousel-slides" style="transform: translateX(0%);">
                        @foreach($chunks as $index => $chunk)
                            <div class="w-full flex-shrink-0 px-2 pb-2">
                                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                                    @foreach($chunk as $asig)
                                        @php
                                            $tipoLower = strtolower($asig->espacio->tipo_espacio ?? 'Otro');
                                            if (str_contains($tipoLower, 'laboratorio')) {
                                                $color = 'bg-amber-500';
                                            } elseif (str_contains($tipoLower, 'aula') || str_contains($tipoLower, 'clase')) {
                                                $color = 'bg-sky-500';
                                            } elseif (str_contains($tipoLower, 'estudio') || str_contains($tipoLower, 'sala')) {
                                                $color = 'bg-purple-500';
                                            } elseif (str_contains($tipoLower, 'auditorio')) {
                                                $color = 'bg-indigo-500';
                                            } else {
                                                $color = 'bg-gray-400';
                                            }

                                            // Definir colores de borde y fondo de tarjeta según presencia del docente
                                            if ($asig->profesor_presente) {
                                                $cardStyles = 'border-emerald-200 bg-emerald-50/20 hover:border-emerald-400 hover:shadow-emerald-100/40';
                                            } else {
                                                $cardStyles = 'border-rose-200 bg-rose-50/20 hover:border-rose-400 hover:shadow-rose-100/40';
                                            }
                                        @endphp
                                        <div class="rounded-xl border {{ $cardStyles }} p-4 flex flex-col justify-between gap-3 shadow-xs hover:shadow-md transition duration-150 min-h-[145px]">
                                            <div>
                                                <div class="flex items-center justify-between gap-2 mb-1.5">
                                                    <div class="flex items-center gap-2">
                                                        <span class="inline-block w-2.5 h-2.5 rounded-full {{ $color }}" title="Tipo: {{ $asig->espacio->tipo_espacio ?? 'Otro' }}"></span>
                                                        <span class="font-bold text-base text-gray-800 leading-none">{{ $asig->espacio->id_espacio }}</span>
                                                        <span class="text-[11px] font-medium text-slate-500 bg-slate-100 px-1.5 py-0.5 rounded ml-2">Piso {{ $asig->espacio->piso->numero_piso ?? '-' }}</span>
                                                    </div>
                                                    @if($asig->profesor_presente)
                                                        <span class="text-[10px] font-bold text-emerald-700 bg-emerald-100/80 border border-emerald-200 px-2 py-0.5 rounded-full flex items-center gap-1 shrink-0">
                                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                                            En Sala
                                                        </span>
                                                    @else
                                                        <span class="text-[10px] font-bold text-rose-700 bg-rose-100/80 border border-rose-200 px-2 py-0.5 rounded-full flex items-center gap-1 shrink-0">
                                                            <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                                                            Ausente
                                                        </span>
                                                    @endif
                                                </div>
                                                <div class="font-semibold text-sm text-gray-800 line-clamp-2" title="{{ $asig->nombre_asignatura }}">
                                                    {{ $asig->nombre_asignatura }}
                                                </div>
                                            </div>
                                            <div class="border-t border-gray-100 pt-2 shrink-0">
                                                <div class="text-xs text-gray-700 font-bold truncate" title="{{ $asig->profesor_name }}">{{ $asig->profesor_name }}</div>
                                                <div class="flex items-center gap-1 text-[11px] text-gray-400 mt-0.5 truncate" title="{{ $asig->profesor_email }}">
                                                    <i class="fas fa-envelope text-gray-300 shrink-0"></i>
                                                    <span>{{ $asig->profesor_email }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- Controls -->
                @if($chunks->count() > 1)
                    <!-- Prev Button -->
                    <button onclick="prevSlide()" class="absolute left-0 top-1/2 transform -translate-y-1/2 bg-white/95 hover:bg-white text-gray-700 p-2.5 rounded-full shadow-md border border-gray-200 hover:text-blue-600 hover:scale-105 transition-all duration-150 z-10 -ml-5 flex items-center justify-center w-9 h-9" id="btn-prev">
                        <i class="fas fa-chevron-left text-sm"></i>
                    </button>
                    <!-- Next Button -->
                    <button onclick="nextSlide()" class="absolute right-0 top-1/2 transform -translate-y-1/2 bg-white/95 hover:bg-white text-gray-700 p-2.5 rounded-full shadow-md border border-gray-200 hover:text-blue-600 hover:scale-105 transition-all duration-150 z-10 -mr-5 flex items-center justify-center w-9 h-9" id="btn-next">
                        <i class="fas fa-chevron-right text-sm"></i>
                    </button>
                @endif
            </div>

            <!-- Indicators -->
            @if($chunks->count() > 1)
                <div class="flex justify-center items-center gap-2 mt-4" id="carousel-indicators">
                    @foreach($chunks as $index => $chunk)
                        <button onclick="goToSlide({{ $index }})" class="h-2 rounded-full transition-all duration-200 {{ $index === 0 ? 'bg-blue-600 w-6' : 'bg-gray-300 w-2 hover:bg-gray-400' }}" id="indicator-{{ $index }}"></button>
                    @endforeach
                </div>
            @endif
        </div>
    @endif
@endif
