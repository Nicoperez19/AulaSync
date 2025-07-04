@php
// Colores por tipo de espacio (puedes ajustar estos valores)
$coloresTipo = [
    'Aula' => 'bg-blue-500',
    'Laboratorio' => 'bg-orange-500',
    'Auditorio' => 'bg-green-500',
    'Sala' => 'bg-purple-500',
    'Otro' => 'bg-gray-400',
];
@endphp
@if(!$moduloActualNum)
    <div class="text-gray-500 text-center py-8">No hay módulo actual en este momento.</div>
@else
    <div class="mb-2 flex items-center gap-2 text-lg font-semibold">
        <i class="fas fa-clock"></i>
        {{ ucfirst($diaActual) }} - Módulo {{ $moduloActualNum }} ({{ substr($moduloActualHorario['inicio'],0,5) }} - {{ substr($moduloActualHorario['fin'],0,5) }})
    </div>
    <div class="text-gray-500 mb-4">Usuarios asignados por espacio</div>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
        @forelse($asignaciones as $asig)
            @php
                $tipo = $asig->espacio->tipo_espacio ?? 'Otro';
                $color = $coloresTipo[$tipo] ?? $coloresTipo['Otro'];
            @endphp
            <div class="rounded-lg border border-gray-200 bg-white p-4 flex flex-col gap-2 shadow-sm">
                <div class="flex items-center gap-2 mb-1">
                    <span class="inline-block w-3 h-3 rounded-full {{ $color }}"> aaa< /span>
                    <span class="font-bold text-base">{{ $asig->espacio->id_espacio }}</span>
                    <span class="text-xs text-gray-500 ml-2">Piso {{ $asig->espacio->piso->numero_piso ?? '-' }}</span>
                </div>
                <div class="font-semibold text-sm text-gray-800">{{ $asig->asignatura->nombre_asignatura ?? '-' }}</div>
                <div class="text-xs text-gray-500">{{ $asig->asignatura->user->name ?? '-' }}</div>
                <div class="flex items-center gap-1 text-xs text-gray-500">
                    <i class="fas fa-envelope"></i>
                    <span>{{ $asig->asignatura->user->email ?? '-' }}</span>
                </div>
            </div>
        @empty
            <div class="col-span-full text-center text-gray-500 py-8">No hay asignaciones para este módulo.</div>
        @endforelse
    </div>
@endif 