@if(!empty($horariosAgrupados))
    <div class="space-y-6">
        @foreach($horariosAgrupados as $dia => $horarios)
            <div class="border rounded-lg p-4 bg-gray-50">
                <h4 class="text-lg font-semibold text-blue-600 mb-3 flex items-center">
                    <i class="fas fa-calendar-day mr-2"></i>
                    {{ $dia }}
                </h4>
                
                @foreach($horarios as $hora => $espacios)
                    <div class="mb-4">
                        <h5 class="text-md font-medium text-green-600 mb-2 flex items-center">
                            <i class="fas fa-clock mr-2"></i>
                            {{ $hora }}
                        </h5>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                            @foreach($espacios as $espacio)
                                <div class="bg-white rounded-lg p-3 border shadow-sm hover:shadow-md transition-shadow">
                                    <div class="font-semibold text-purple-600 text-sm mb-2">
                                        <i class="fas fa-building mr-1"></i>
                                        {{ $espacio['espacio'] }}
                                    </div>
                                    <div class="text-sm text-gray-700 mb-1">
                                        <i class="fas fa-book mr-1"></i>
                                        <span class="font-medium">{{ $espacio['asignatura'] }}</span>
                                    </div>
                                    <div class="text-sm text-orange-600 mb-1">
                                        <i class="fas fa-user mr-1"></i>
                                        <span class="font-medium">{{ $espacio['profesor'] }}</span>
                                    </div>
                                    @if($espacio['email'] !== 'No disponible')
                                        <div class="text-xs text-gray-500">
                                            <i class="fas fa-envelope mr-1"></i>
                                            {{ $espacio['email'] }}
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        @endforeach
    </div>
@else
    <div class="text-center text-gray-500 py-8">
        <i class="fas fa-calendar-times text-4xl mb-4"></i>
        <p class="text-lg">No hay horarios programados para esta semana.</p>
        <p class="text-sm mt-2">Los horarios se mostrarán aquí cuando se programen asignaturas para los espacios.</p>
    </div>
@endif

@if(!empty($horariosPorTipoDiaModulo))
    <div class="overflow-x-auto">
        <table class="min-w-full text-center border border-gray-300 rounded-lg">
            <thead>
                <tr class="bg-gray-200">
                    <th class="px-2 py-1 border">Tipo de Espacio</th>
                    @php
                        $dias = array_keys(reset($horariosPorTipoDiaModulo));
                        $modulos = [];
                        foreach ($dias as $dia) {
                            $modulos = array_unique(array_merge($modulos, array_keys(reset($horariosPorTipoDiaModulo)[$dia])));
                        }
                        sort($modulos);
                    @endphp
                    @foreach($dias as $dia)
                        @foreach($modulos as $modulo)
                            <th class="px-2 py-1 border">{{ $dia }}<br>Mód {{ $modulo }}</th>
                        @endforeach
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @foreach($horariosPorTipoDiaModulo as $tipo => $diasData)
                    <tr>
                        <td class="border font-bold bg-gray-50">{{ $tipo }}</td>
                        @foreach($dias as $dia)
                            @foreach($modulos as $modulo)
                                <td class="border {{ ($diasData[$dia][$modulo] ?? 0) > 70 ? 'bg-green-200' : (($diasData[$dia][$modulo] ?? 0) > 40 ? 'bg-yellow-200' : 'bg-red-100') }}">
                                    {{ $diasData[$dia][$modulo] ?? 0 }}%
                                </td>
                            @endforeach
                        @endforeach
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@else
    <div class="p-4 text-center text-gray-500">No hay datos de ocupación para mostrar.</div>
@endif 