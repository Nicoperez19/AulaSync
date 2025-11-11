{{-- Debug: {{ json_encode($comparativaTipos) }} --}}
@if($comparativaTipos && $comparativaTipos->isNotEmpty())
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
                                'Sala de Clases' => 'fa-chalkboard-teacher',
                            ];
                            $icono = $iconos[$data['nombre'] ?? $data['tipo'] ?? ''] ?? 'fa-door-closed';
                        @endphp
                        <span class="inline-flex items-center justify-center w-7 h-7 rounded-full bg-gray-100">
                            <i class="fas {{ $icono }} text-xl text-gray-400"></i>
                        </span>
                        <span class="font-semibold text-gray-900">{{ $data['nombre'] ?? $data['tipo'] ?? 'Tipo no especificado' }}</span>
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
@else
    <div class="flex flex-col items-center justify-center py-12 text-center">
        <svg xmlns="http://www.w3.org/2000/svg" class="w-16 h-16 mb-4 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
        </svg>
        <p class="text-lg font-medium text-gray-500">No hay datos de utilización de espacios</p>
        <p class="mt-1 text-sm text-gray-400">Los datos aparecerán aquí cuando haya espacios registrados</p>
    </div>
@endif 