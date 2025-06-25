<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
            <div class="flex items-center gap-2">
                <h2 class="text-2xl font-bold leading-tight text-gray-900">
                    {{ $tituloPiso ?? 'Primer Piso' }}
            </h2>
            </div>
            <div class="flex items-center gap-4 mt-2 md:mt-0">
                <span class="flex items-center gap-1 text-sm font-semibold text-green-600">
                    <span class="inline-block w-3 h-3 bg-green-500 rounded-full"></span> Disponible
                </span>
                <span class="flex items-center gap-1 text-sm font-semibold text-red-600">
                    <span class="inline-block w-3 h-3 bg-red-500 rounded-full"></span> Ocupado
                </span>
            </div>
        </div>
        <!-- Módulo actual -->
        <div class="flex items-center gap-2 mt-2 text-sm text-gray-600">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            @if(isset($moduloActualNum))
                Módulo {{ $moduloActualNum }} ({{ substr($moduloActualHorario['inicio'], 0, 5) }} - {{ substr($moduloActualHorario['fin'], 0, 5) }})
            @else
                No hay módulo disponible
            @endif
        </div>
    </x-slot>
    <div class="p-6 bg-white rounded-lg shadow-md dark:bg-gray-800">
        @php
            // Agrupar espacios por tipo
            $espaciosPorTipo = collect($espacios)->groupBy('tipo_espacio');
            $iconos = [
                'Auditorio' => 'fa-solid fa-chalkboard',
                'Laboratorio' => 'fa-solid fa-flask',
                'Sala de Reuniones' => 'fa-solid fa-comments',
                'Aula' => 'fa-solid fa-graduation-cap',
                'Taller' => 'fa-solid fa-tools',
                'Sala de Estudio' => 'fa-solid fa-book',
            ];
            $colores = [
                'Auditorio' => 'border-purple-200 bg-purple-50',
                'Laboratorio' => 'border-blue-200 bg-blue-50',
                'Sala de Reuniones' => 'border-green-200 bg-green-50',
                'Aula' => 'border-yellow-200 bg-yellow-50',
                'Taller' => 'border-orange-200 bg-orange-50',
                'Sala de Estudio' => 'border-pink-200 bg-pink-50',
            ];
            $badgeColores = [
                'Auditorio' => 'bg-purple-100 text-purple-700',
                'Laboratorio' => 'bg-blue-100 text-blue-700',
                'Sala de Reuniones' => 'bg-green-100 text-green-700',
                'Aula' => 'bg-yellow-100 text-yellow-700',
                'Taller' => 'bg-orange-100 text-orange-700',
                'Sala de Estudio' => 'bg-pink-100 text-pink-700',
            ];
        @endphp
        @foreach($espaciosPorTipo as $tipo => $espacios)
            <div class="mb-8">
                <div class="flex items-center gap-3 mb-3">
                    <i class="{{ $iconos[$tipo] ?? 'fa-solid fa-door-closed' }} text-2xl text-gray-700"></i>
                    <h3 class="text-xl font-bold text-gray-900">{{ $tipo }}</h3>
                    <span class="px-2 py-0.5 text-xs font-semibold bg-gray-200 rounded-full text-gray-700">{{ $espacios->count() }} espacio{{ $espacios->count() > 1 ? 's' : '' }}</span>
                </div>
                <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3">
                    @foreach($espacios as $espacio)
                        @php
                            $estado = $espacio->esta_ocupado ? 'Ocupado' : 'Disponible';
                            $colorEstado = $espacio->esta_ocupado ? 'text-red-500' : 'text-green-500';
                            $puntoEstado = $espacio->esta_ocupado ? 'bg-red-500' : 'bg-green-500';
                            $colorTarjeta = $colores[$tipo] ?? 'border-gray-200 bg-gray-50';
                            $badgeTipo = $badgeColores[$tipo] ?? 'bg-gray-100 text-gray-700';
                @endphp
                        <div class="relative flex flex-col justify-between p-4 border rounded-xl shadow-sm {{ $colorTarjeta }} transition hover:shadow-md">
                            <div class="flex items-center justify-between mb-2">
                                <div class="flex items-center gap-2">
                                    <i class="{{ $iconos[$tipo] ?? 'fa-solid fa-door-closed' }} text-lg text-gray-500"></i>
                                    <span class="font-bold text-gray-800">{{ $espacio->codigo }}</span>
                                </div>
                                <span class="flex items-center gap-1 text-xs font-semibold">
                                    <span class="inline-block w-2 h-2 rounded-full {{ $puntoEstado }}"></span>
                                    {{ $estado }}
                                </span>
                            </div>
                            <div class="mb-1 text-base font-semibold text-gray-900">{{ $espacio->nombre }}</div>
                            <div class="flex items-center gap-2 mb-2">
                                <span class="px-2 py-0.5 text-xs font-semibold rounded-full {{ $badgeTipo }}">{{ $tipo }}</span>
                                <span class="flex items-center gap-1 text-xs text-gray-500">
                                    <i class="fa-solid fa-users"></i> {{ $espacio->capacidad ?? '-' }} personas
                                </span>
                            </div>
                            <a href="{{ route('espacios.show', ['id' => $espacio->id]) }}" class="flex items-center gap-1 text-xs font-medium text-violet-700 hover:underline">
                                <i class="fa-solid fa-calendar-days"></i> Ver horarios disponibles
                            </a>
                        </div>
            @endforeach
        </div>
            </div>
        @endforeach
    </div>
</x-app-layout>
