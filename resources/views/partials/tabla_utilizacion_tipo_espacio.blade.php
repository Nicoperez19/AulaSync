@if($comparativaTipos && (is_array($comparativaTipos) ? count($comparativaTipos) > 0 : $comparativaTipos->isNotEmpty()))
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        @foreach($comparativaTipos as $data)
            @php
                $porcentaje = $data['porcentaje'] ?? 0;
                $colorBarra = $porcentaje >= 80 ? '#ef4444' : ($porcentaje >= 50 ? '#f59e0b' : '#10b981');
                $iconos = [
                    'Aula' => ['icon' => 'fa-graduation-cap', 'color' => 'blue'],
                    'Laboratorio' => ['icon' => 'fa-flask', 'color' => 'purple'],
                    'Auditorio' => ['icon' => 'fa-volume-up', 'color' => 'red'],
                    'Sala de Estudio' => ['icon' => 'fa-book', 'color' => 'green'],
                    'Taller' => ['icon' => 'fa-tools', 'color' => 'orange'],
                    'Sala de Reuniones' => ['icon' => 'fa-comments', 'color' => 'indigo'],
                    'Sala de Clases' => ['icon' => 'fa-chalkboard-teacher', 'color' => 'teal'],
                ];
                $tipoData = $iconos[$data['nombre'] ?? $data['tipo'] ?? ''] ?? ['icon' => 'fa-door-closed', 'color' => 'gray'];
            @endphp
            <div class="group flex flex-col justify-between p-5 bg-gradient-to-br from-white to-gray-50 rounded-xl border-2 border-gray-200 hover:border-{{ $tipoData['color'] }}-400 hover:shadow-lg transition-all duration-300 min-h-[140px]">
                <div class="flex items-start justify-between mb-3">
                    <div class="flex items-center gap-3">
                        <span class="inline-flex items-center justify-center w-11 h-11 rounded-xl bg-{{ $tipoData['color'] }}-100 group-hover:bg-{{ $tipoData['color'] }}-200 transition-colors">
                            <i class="fas {{ $tipoData['icon'] }} text-xl text-{{ $tipoData['color'] }}-600"></i>
                        </span>
                        <div>
                            <h3 class="font-bold text-gray-900 text-base">{{ $data['nombre'] ?? $data['tipo'] ?? 'Tipo no especificado' }}</h3>
                            <p class="text-xs text-gray-500 mt-0.5">{{ $data['total'] ?? 0 }} espacios totales</p>
                        </div>
                    </div>
                    <div class="text-right">
                        <div class="text-xl font-semibold text-gray-700">{{ $data['ocupados'] ?? 0 }} de {{ $data['total'] ?? 0 }}</div>
                        <div class="text-xs text-gray-500 font-medium">Ocupadas</div>
                    </div>
                </div>
                
                <div class="space-y-2">
                    <div class="flex items-center justify-between text-sm">
                        <span class="text-gray-600 font-medium">{{ $data['ocupados'] ?? 0 }} ocupadas</span>
                        <span class="text-gray-400">{{ $data['total'] - ($data['ocupados'] ?? 0) }} libres</span>
                    </div>
                    <div class="relative w-full h-3 bg-gray-200 rounded-full overflow-hidden shadow-inner">
                        <div class="absolute left-0 top-0 h-full rounded-full transition-all duration-500 ease-out" 
                             style="width: {{ $porcentaje }}%; background: {{ $colorBarra }};"></div>
                    </div>
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