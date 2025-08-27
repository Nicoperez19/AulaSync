<div class="p-6">
    @if (count($pisos) > 0)
        <div class="relative w-full bg-white rounded-lg shadow-sm border border-gray-200">
            @if (count($this->getTodosLosEspacios()) > 0)
                <div class="flex w-full">

                    {{-- Columna izquierda - DISPONIBLES fijos --}}
                    <div class="flex-1 border-r border-gray-200">
                        <table class="w-full table-fixed">
                            <thead>
                                <tr class="bg-green-600 text-white border-b border-gray-200">
                                    <th class="px-3 py-1 text-left text-sm font-semibold uppercase">N° Sala</th>
                                    <th class="px-3 py-1 text-left text-sm font-semibold uppercase">Nombre</th>
                                    <th class="px-3 py-1 text-left text-sm font-semibold uppercase">Piso</th>
                                    <th class="px-3 py-1 text-left text-sm font-semibold uppercase">Estado</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @foreach ($this->getTodosLosEspacios() as $espacio)
                                    @if (strtolower($espacio['estado']) === 'disponible')
                                        <tr class="bg-white hover:bg-green-50 transition-colors h-10">
                                            <td class="px-3 py-1 text-sm font-semibold text-blue-700">{{ $espacio['id_espacio'] }}</td>
                                            <td class="px-3 py-1 text-sm">{{ $espacio['nombre_espacio'] }}</td>
                                            <td class="px-3 py-1 text-sm text-gray-500">{{ $espacio['piso'] }}</td>
                                            <td class="px-3 py-1 text-sm text-green-600 font-medium">Disponible</td>
                                        </tr>
                                    @endif
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    {{-- Columna derecha - CARRUSEL con Ocupados / Reservados --}}
                    <div class="flex-1 relative overflow-hidden">
                        <table class="w-full table-fixed">
                            <thead>
                                <tr class="bg-blue-600 text-white border-b border-gray-200">
                                    <th class="px-3 py-1 text-left text-sm font-semibold uppercase">N° Sala</th>
                                    <th class="px-3 py-1 text-left text-sm font-semibold uppercase">Nombre</th>
                                    <th class="px-3 py-1 text-left text-sm font-semibold uppercase">Piso</th>
                                    <th class="px-3 py-1 text-left text-sm font-semibold uppercase">Estado</th>
                                    <th class="px-3 py-1 text-left text-sm font-semibold uppercase">Responsable</th>
                                </tr>
                            </thead>
                        </table>

                        <div id="tabla-carrusel" class="transition-transform duration-700 ease-in-out relative">
                            @foreach ($this->getTodosLosEspacios() as $espacio)
                                @if (in_array(strtolower($espacio['estado']), ['ocupado', 'reservado']))
                                    <table class="w-full table-fixed absolute top-0 left-0">
                                        <tbody>
                                            <tr class="bg-white hover:bg-blue-50 transition-colors h-10">
                                                <td class="px-3 py-1 text-sm font-semibold text-blue-700">{{ $espacio['id_espacio'] }}</td>
                                                <td class="px-3 py-1 text-sm">{{ $espacio['nombre_espacio'] }}</td>
                                                <td class="px-3 py-1 text-sm text-gray-500">{{ $espacio['piso'] }}</td>
                                                <td class="px-3 py-1 text-sm 
                                                    {{ strtolower($espacio['estado']) === 'ocupado' ? 'text-red-600' : 'text-yellow-600' }} font-medium">
                                                    {{ ucfirst($espacio['estado']) }}
                                                </td>
                                                <td class="px-3 py-1 text-sm">
                                                    @if ($espacio['tiene_clase'] && $espacio['datos_clase'])
                                                        {{ $this->getPrimerApellido($espacio['datos_clase']['profesor']['name']) }}
                                                    @elseif ($espacio['tiene_reserva_profesor'] && $espacio['datos_profesor'])
                                                        {{ $this->getPrimerApellido($espacio['datos_profesor']['nombre']) }}
                                                    @elseif ($espacio['tiene_reserva_solicitante'] && $espacio['datos_solicitante'])
                                                        {{ $this->getPrimerApellidoSolicitante($espacio['datos_solicitante']['nombre']) }}
                                                    @else
                                                        -
                                                    @endif
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                @endif
                            @endforeach
                        </div>
                    </div>

                </div>
            @endif
        </div>
    @endif

    <script>
        // Carrusel automático por tabla
        let index = 0;
        const contenedor = document.getElementById("tabla-carrusel");
        
        // Verificar que el contenedor existe y tiene elementos
        if (contenedor && contenedor.children.length > 0) {
            const tablas = contenedor.children;

            // Mostrar solo la primera tabla al inicio
            for (let i = 1; i < tablas.length; i++) {
                if (tablas[i]) {
                    tablas[i].style.display = "none";
                }
            }

            // Solo iniciar el carrusel si hay más de una tabla
            if (tablas.length > 1) {
                setInterval(() => {
                    // Verificar que el elemento actual existe
                    if (tablas[index] && tablas[index].style) {
                        tablas[index].style.display = "none";
                    }
                    
                    index = (index + 1) % tablas.length;
                    
                    // Verificar que el nuevo elemento existe
                    if (tablas[index] && tablas[index].style) {
                        tablas[index].style.display = "table";
                    }
                }, 4000); // cambia cada 4 segundos
            }
        }
    </script>

    {{-- Actualización automática del componente Livewire --}}
    <script>
        // Actualizar el componente cada 30 segundos para mostrar reservas nuevas
        setInterval(() => {
            @this.actualizarAutomaticamente();
        }, 30000);
    </script>
</div>
