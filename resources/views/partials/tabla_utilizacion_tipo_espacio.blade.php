<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
    @foreach($comparativaTipos as $data)
        <div class="flex flex-col justify-between p-4 bg-white rounded-lg shadow border border-gray-200 min-h-[120px]">
            <div class="flex items-center justify-between mb-2">
                <div class="flex items-center gap-2">
                    {{-- Icono por tipo (puedes personalizar según el tipo) --}}
                    @php
                        $iconos = [
                            'Aula' => 'fa-graduation-cap',
                            'Laboratorio' => 'fa-flask',
                            'Auditorio' => 'fa-volume-up',
                            'Sala de Estudio' => 'fa-book',
                            'Taller' => 'fa-tools',
                            'Sala de Reuniones' => 'fa-comments',
                        ];
                        $icono = $iconos[$data['nombre']] ?? 'fa-door-closed';
                    @endphp
                    <span class="inline-flex items-center justify-center w-7 h-7 rounded-full bg-gray-100">
                        <i class="fas {{ $icono }} text-xl text-gray-400"></i>
                    </span>
                    <span class="font-semibold text-gray-900">{{ $data['nombre'] }}</span>
                </div>
                <span class="text-xs font-bold text-gray-500">{{ $data['porcentaje'] ?? 0 }}%</span>
            </div>
            <div class="flex items-center gap-2 mb-1">
                <span class="text-xs text-gray-500">{{ $data['ocupados'] ?? 0 }} de {{ $data['total'] ?? 0 }} ocupadas</span>
                    </div>
            <div class="flex items-center gap-2">
                <div class="relative w-full h-2 bg-gray-200 rounded-full overflow-hidden">
                    <div class="absolute left-0 top-0 h-2 rounded-full" style="width: {{ $data['porcentaje'] ?? 0 }}%; background: #8C0303;"></div>
                </div>
                <span class="ml-2 text-xs text-gray-600 font-semibold">{{ $data['ocupados'] ?? 0 }}/{{ $data['total'] ?? 0 }}</span>
            </div>
        </div>
    @endforeach
</div> 