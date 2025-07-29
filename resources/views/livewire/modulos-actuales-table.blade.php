<div class="flex flex-col w-full h-full">
    @if (count($pisos) > 0)
        <div class="relative flex-1 w-full bg-white">
            @if ($moduloActual)
                @php
                    $todosLosEspacios = [];
                    foreach ($pisos as $piso) {
                        $espaciosPiso = $espacios[$piso->id] ?? [];
                        foreach ($espaciosPiso as $espacio) {
                            $espacio['piso'] = $piso->numero_piso;
                            $todosLosEspacios[] = $espacio;
                        }
                    }
                @endphp

                @if (count($todosLosEspacios) > 0)
                    <div class="flex flex-col w-full h-full">
                        <!-- Contenedor de las 2 tablas -->
                        <div class="flex w-full h-full">
                            <div class="flex w-full h-full">
                                <!-- Tabla 1 -->
                                <div class="flex flex-col flex-1 border-r border-gray-300">
                                    <table class="w-full h-full table-fixed">
                                        <thead class="sticky top-0 z-10">
                                            <tr class="bg-light-cloud-blue">
                                                <th class="w-1/2 px-3 py-3 text-xs font-medium text-left text-white uppercase">
                                                    NOMBRE ESPACIO</th>
                                                <th class="w-1/4 px-3 py-3 text-xs font-medium text-left text-white uppercase">
                                                    CLASE ACTUAL</th>
                                                <th class="w-1/4 px-3 py-3 text-xs font-medium text-left text-white uppercase">
                                                    PROFESOR</th>
                                            </tr>
                                        </thead>
                                        <tbody class="bg-white divide-y divide-gray-200">
                                            @foreach (array_slice($todosLosEspacios, 0, ceil(count($todosLosEspacios) / 2)) as $index => $espacio)
                                                @php
                                                    $estado = $espacio['estado'];
                                                    $colorFila = $index % 2 === 0 ? 'bg-white' : 'bg-gray-50';
                                                @endphp
                                                <tr class="{{ $colorFila }} h-full">
                                                    <td class="px-2 py-2 text-left">
                                                        <span class="inline-block w-3 h-3 rounded-full mr-1
                                                        {{ $estado === 'Ocupado' ? 'bg-red-500' : ($estado === 'Reservado' ? 'bg-yellow-500' : 'bg-green-500') }}">
                                                        </span>
                                                        <span class="font-bold text-blue-700">({{ $espacio['id_espacio'] }})</span> {{ $espacio['nombre_espacio'] }}
                                                        <span class="font-bold text-blue-700">(Piso {{ $espacio['piso'] }})</span>
                                                    </td>
                                                    <td class="px-2 py-2 text-left">
                                                        @if ($espacio['tiene_clase'] && $espacio['datos_clase'])
                                                            <div>
                                                                <div class="font-medium text-gray-900 truncate">
                                                                    {{ $espacio['datos_clase']['nombre_asignatura'] }}
                                                                    @if ($espacio['datos_clase']['seccion'])
                                                                        <span class="text-gray-500">({{ $espacio['datos_clase']['seccion'] }})</span>
                                                                    @endif
                                                                </div>
                                                            </div>
                                                        @else
                                                            <span class="text-gray-400">Sin clases</span>
                                                        @endif
                                                    </td>
                                                    <td class="px-2 py-2 text-left">
                                                        @if ($espacio['tiene_clase'] && $espacio['datos_clase'])
                                                            {{ $espacio['datos_clase']['profesor']['name'] }}
                                                        @else
                                                            <span class="text-gray-400">Sin clases</span>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>

                                <!-- Tabla 2 -->
                                <div class="flex flex-col flex-1">
                                    <table class="w-full h-full table-fixed">
                                        <thead class="sticky top-0 z-10">
                                            <tr class="bg-light-cloud-blue">
                                                <th class="w-1/2 px-3 py-3 text-xs font-medium text-left text-white uppercase">
                                                    NOMBRE ESPACIO</th>
                                                <th class="w-1/4 px-3 py-3 text-xs font-medium text-left text-white uppercase">
                                                    CLASE ACTUAL</th>
                                                <th class="w-1/4 px-3 py-3 text-xs font-medium text-left text-white uppercase">
                                                    PROFESOR</th>
                                            </tr>
                                        </thead>
                                        <tbody class="bg-white divide-y divide-gray-200">
                                            @foreach (array_slice($todosLosEspacios, ceil(count($todosLosEspacios) / 2)) as $index => $espacio)
                                                @php
                                                    $estado = $espacio['estado'];
                                                    $colorFila = $index % 2 === 0 ? 'bg-white' : 'bg-gray-50';
                                                @endphp
                                                <tr class="{{ $colorFila }} h-full">
                                                    <td class="px-2 py-2 text-left">
                                                        <span class="inline-block w-3 h-3 rounded-full mr-1
                                                        {{ $estado === 'Ocupado' ? 'bg-red-500' : ($estado === 'Reservado' ? 'bg-yellow-500' : 'bg-green-500') }}">
                                                        </span>
                                                        <span class="font-bold text-blue-700">({{ $espacio['id_espacio'] }})</span> {{ $espacio['nombre_espacio'] }}
                                                        <span class="font-bold text-blue-700">(Piso {{ $espacio['piso'] }})</span>
                                                    </td>
                                                    <td class="px-2 py-2 text-left">
                                                        @if ($espacio['tiene_clase'] && $espacio['datos_clase'])
                                                            <div>
                                                                <div class="font-medium text-gray-900 truncate">
                                                                    {{ $espacio['datos_clase']['nombre_asignatura'] }}
                                                                    @if ($espacio['datos_clase']['seccion'])
                                                                        <span class="text-gray-500">({{ $espacio['datos_clase']['seccion'] }})</span>
                                                                    @endif
                                                                </div>
                                                            </div>
                                                        @else
                                                            <span class="text-gray-400">Sin clases</span>
                                                        @endif
                                                    </td>
                                                    <td class="px-2 py-2 text-left">
                                                        @if ($espacio['tiene_clase'] && $espacio['datos_clase'])
                                                            {{ $espacio['datos_clase']['profesor']['name'] }}
                                                        @else
                                                            <span class="text-gray-400">Sin clases</span>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                @else
                    <div class="flex items-center justify-center w-full h-full bg-white">
                        <div class="text-center">
                            <i class="text-4xl text-gray-300 fa-solid fa-building "></i>
                            <p class="text-gray-500">No hay espacios disponibles.</p>
                        </div>
                    </div>
                @endif
            @else
                <!-- Mensaje cuando no hay módulo actual -->
                <div class="flex items-center justify-center w-full h-full p-4 bg-white">
                    <div class="text-center">
                        <i class="text-4xl text-gray-300 fa-solid fa-clock "></i>
                        <p class="text-lg font-medium text-gray-500">No hay horario en el módulo actual</p>
                        <p class="mt-2 text-sm text-gray-400">Fuera del horario de clases programadas</p>
                    </div>
                </div>
            @endif
        </div>
    @else
        <div class="flex items-center justify-center w-full h-full bg-white">
            <div class="text-center">
                <i class="text-4xl text-gray-300 fa-solid fa-building "></i>
                <p class="text-gray-500">No hay pisos disponibles.</p>
            </div>
        </div>
    @endif

    <script>
        // Función para ajustar la altura de las filas
        function ajustarAlturaFilas() {
            const tablas = document.querySelectorAll('table');
            tablas.forEach(tabla => {
                const tbody = tabla.querySelector('tbody');
                const filas = tbody.querySelectorAll('tr');
                
                // Calcular altura disponible (restar header, padding y márgenes)
                const headerHeight = 60; // Altura del header de la tabla
                const paddingTop = 100; // Padding del header principal
                const paddingBottom = 40; // Padding inferior
                const alturaDisponible = window.innerHeight - headerHeight - paddingTop - paddingBottom;
                
                // Calcular altura por fila
                const alturaPorFila = Math.max(alturaDisponible / filas.length, 40); // Mínimo 40px por fila
                
                filas.forEach(fila => {
                    fila.style.height = `${alturaPorFila}px`;
                    fila.style.minHeight = `${alturaPorFila}px`;
                    fila.style.maxHeight = `${alturaPorFila}px`;
                });
                
                // Ajustar altura del tbody para asegurar que todas las filas sean visibles
                tbody.style.height = `${alturaPorFila * filas.length}px`;
            });
        }
        
        // Ajustar altura cuando se carga la página
        document.addEventListener('DOMContentLoaded', function() {
            setTimeout(ajustarAlturaFilas, 100);
        });
        
        // Ajustar altura cuando cambia el tamaño de la ventana
        window.addEventListener('resize', function() {
            setTimeout(ajustarAlturaFilas, 100);
        });
        
        // Actualizar datos cada 30 segundos
        setInterval(function() {
            @this.actualizarAutomaticamente();
        }, 30000);
        
        // Ajustar altura después de que Livewire actualice el contenido
        document.addEventListener('livewire:load', function() {
            Livewire.hook('message.processed', (message, component) => {
                setTimeout(ajustarAlturaFilas, 200);
            });
        });
        
        // Ejecutar ajuste inicial
        setTimeout(ajustarAlturaFilas, 500);
    </script>
</div>
