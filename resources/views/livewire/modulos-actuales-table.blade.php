<div class="flex flex-col w-full">
    @if (count($pisos) > 0)
        <div class="relative w-full bg-white rounded-lg shadow-sm border border-gray-200">
            @php
                $todosLosEspacios = [];
                foreach ($pisos as $piso) {
                    $espaciosPiso = $espacios[$piso->id] ?? [];
                    foreach ($espaciosPiso as $espacio) {
                        $espacio['piso'] = $piso->numero_piso;
                        if (!$moduloActual) {
                            $espacio['tiene_clase'] = false;
                            $espacio['datos_clase'] = null;
                            $espacio['estado'] = 'Disponible';
                        }
                        $todosLosEspacios[] = $espacio;
                    }
                }
            @endphp

            @if (count($todosLosEspacios) > 0)
                <!-- Tabla de dos columnas -->
                <div class="flex w-full">
                    <!-- Columna 1 - Primera mitad -->
                    <div class="flex-1 border-r border-gray-200">
                        <table class="w-full table-fixed">
                            <thead>
                                <tr class="bg-light-cloud-blue text-white border-b border-gray-200">
                                    <th class="w-1/3 px-3 py-1 text-left text-sm font-semibold uppercase tracking-wider">
                                        Espacio
                                    </th>
                                    <th class="w-2/5 px-3 py-1 text-left text-sm font-semibold uppercase tracking-wider">
                                        Clase Actual
                                    </th>
                                    <th class="w-1/5 px-3 py-1 text-left text-sm font-semibold uppercase tracking-wider">
                                        Profesor
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @foreach (array_slice($todosLosEspacios, 0, ceil(count($todosLosEspacios) / 2)) as $index => $espacio)
                                    @php
                                        $estado = $espacio['estado'];
                                        $colorFila = $index % 2 === 0 ? 'bg-white' : 'bg-gray-50';
                                        $estadoColor = $estado === 'Ocupado' ? 'bg-red-500' : ($estado === 'Reservado' ? 'bg-yellow-500' : 'bg-green-500');
                                    @endphp
                                    <tr class="{{ $colorFila }} hover:bg-blue-50 transition-colors duration-200 h-10">
                                        <td class="px-3 py-1 text-sm align-middle">
                                            <div class="flex items-center gap-2">
                                                <span class="w-2 h-2 rounded-full {{ $estadoColor }} flex-shrink-0"></span>
                                                <div class="min-w-0 flex-1">
                                                    <div class="flex items-center gap-1">
                                                        <span class="font-semibold text-blue-700 text-sm">{{ $espacio['id_espacio'] }}</span>
                                                        <span class="font-medium text-gray-900 text-sm">{{ $espacio['nombre_espacio'] }}</span>
                                                        <span class="text-sm text-gray-500">({{ $espacio['piso'] }})</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-3 py-1 text-sm align-middle">
                                            @if ($espacio['tiene_clase'] && $espacio['datos_clase'])
                                                <div class="min-w-0">
                                                    <div class="flex items-center gap-1">
                                                        <span class="text-sm">
                                                            {{ $espacio['datos_clase']['nombre_asignatura'] }}
                                                        </span>
                                                        @if ($espacio['datos_clase']['seccion'])
                                                            <span class="text-sm text-gray-500 flex-shrink-0">(Sección {{ $espacio['datos_clase']['seccion'] }})</span>
                                                        @endif
                                                    </div>
                                                </div>
                                            @else
                                                <span class="text-gray-400 italic text-sm">Sin clases</span>
                                            @endif
                                        </td>
                                        <td class="px-3 py-1 text-sm align-middle">
                                            @if ($espacio['tiene_clase'] && $espacio['datos_clase'])
                                                @php
                                                    $nombreCompleto = $espacio['datos_clase']['profesor']['name'];
                                                    $apellidos = explode(',', $nombreCompleto);
                                                    $primerApellido = trim($apellidos[0] ?? '');
                                                @endphp
                                                <span class="font-medium text-gray-900 text-sm">{{ $primerApellido }}</span>
                                            @else
                                                <span class="text-gray-400 italic text-sm">-</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Columna 2 - Segunda mitad -->
                    <div class="flex-1">
                        <table class="w-full table-fixed">
                            <thead>
                                <tr class="bg-light-cloud-blue text-white border-b border-gray-200">
                                    <th class="w-1/3 px-3 py-1 text-left text-sm font-semibold uppercase tracking-wider">
                                        Espacio
                                    </th>
                                    <th class="w-2/5 px-3 py-1 text-left text-sm font-semibold uppercase tracking-wider">
                                        Clase Actual
                                    </th>
                                    <th class="w-1/5 px-3 py-1 text-left text-sm font-semibold uppercase tracking-wider">
                                        Profesor
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @foreach (array_slice($todosLosEspacios, ceil(count($todosLosEspacios) / 2)) as $index => $espacio)
                                    @php
                                        $estado = $espacio['estado'];
                                        $colorFila = $index % 2 === 0 ? 'bg-white' : 'bg-gray-50';
                                        $estadoColor = $estado === 'Ocupado' ? 'bg-red-500' : ($estado === 'Reservado' ? 'bg-yellow-500' : 'bg-green-500');
                                    @endphp
                                    <tr class="{{ $colorFila }} hover:bg-blue-50 transition-colors duration-200 h-10">
                                        <td class="px-3 py-1 text-sm align-middle">
                                            <div class="flex items-center gap-2">
                                                <span class="w-2 h-2 rounded-full {{ $estadoColor }} flex-shrink-0"></span>
                                                <div class="min-w-0 flex-1">
                                                    <div class="flex items-center gap-1">
                                                        <span class="font-semibold text-blue-700 text-sm">{{ $espacio['id_espacio'] }}</span>
                                                        <span class="font-medium text-gray-900 text-sm">{{ $espacio['nombre_espacio'] }}</span>
                                                        <span class="text-sm text-gray-500">({{ $espacio['piso'] }})</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-3 py-1 text-sm align-middle">
                                            @if ($espacio['tiene_clase'] && $espacio['datos_clase'])
                                                <div class="min-w-0">
                                                    <div class="flex items-center gap-1">
                                                        <span class="text-sm">
                                                            {{ $espacio['datos_clase']['nombre_asignatura'] }}
                                                        </span>
                                                        @if ($espacio['datos_clase']['seccion'])
                                                            <span class="text-sm text-gray-500 flex-shrink-0">(Sección {{ $espacio['datos_clase']['seccion'] }})</span>
                                                        @endif
                                                    </div>
                                                </div>
                                            @else
                                                <span class="text-gray-400 italic text-sm">Sin clases</span>
                                            @endif
                                        </td>
                                        <td class="px-3 py-1 text-sm align-middle">
                                            @if ($espacio['tiene_clase'] && $espacio['datos_clase'])
                                                @php
                                                    $nombreCompleto = $espacio['datos_clase']['profesor']['name'];
                                                    $apellidos = explode(',', $nombreCompleto);
                                                    $primerApellido = trim($apellidos[0] ?? '');
                                                @endphp
                                                <span class="font-medium text-gray-900 text-sm">{{ $primerApellido }}</span>
                                            @else
                                                <span class="text-gray-400 italic text-sm">-</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif
        </div>
    @endif

    <!-- Scripts optimizados -->
    <script>
        // Actualizar datos cada 30 segundos
        setInterval(function() {
            @this.actualizarAutomaticamente();
        }, 30000);
    </script>
</div>
