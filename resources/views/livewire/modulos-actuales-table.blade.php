<div class="flex flex-col w-full h-full p-6">
    <style>
        /* Estilos iniciales para evitar tabla infinita */
        .flex.flex-col.w-full.h-full.p-6 {
            max-height: 1200px;
            min-height: 600px;
            overflow: hidden;
        }
        
        .flex.flex-col.w-full.h-full.p-6 table {
            max-height: 800px;
            min-height: 400px;
            overflow: hidden;
        }
        
        .flex.flex-col.w-full.h-full.p-6 tbody {
            max-height: 700px;
            overflow: hidden;
        }
    </style>
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
                                            @if ($esBreak)
                                                <tr class="bg-yellow-500">
                                                    <th colspan="3" class="px-3 py-2 text-center">
                                                        <div class="flex items-center justify-center space-x-4">
                                                            <span class="text-lg font-bold text-white">BREAK</span>
                                                            <i class="fas fa-coffee text-white text-lg"></i>
                                                            <span class="text-lg font-bold text-white" id="cuenta-regresiva-2">
                                                                {{ sprintf('%02d:%02d', $minutosRestantes, $segundosRestantes) }}
                                                            </span>
                                                        </div>
                                                    </th>
                                                </tr>
                                            @endif
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
        // Variables para la cuenta regresiva
        let tiempoRestante = {{ $tiempoRestante ?? 0 }};
        let esBreak = {{ $esBreak ? 'true' : 'false' }};
        let tamañoEstablecido = false; // Control para establecer tamaño solo una vez
        
        // Función para actualizar la cuenta regresiva
        function actualizarCuentaRegresiva() {
            const cuentaRegresivaElement = document.getElementById('cuenta-regresiva');
            const cuentaRegresivaModal = document.getElementById('cuenta-regresiva-modal');
            
            if (tiempoRestante > 0) {
                tiempoRestante--;
                const minutos = Math.floor(tiempoRestante / 60);
                const segundos = tiempoRestante % 60;
                const tiempoFormateado = `${minutos.toString().padStart(2, '0')}:${segundos.toString().padStart(2, '0')}`;
                
                // Actualizar cuenta regresiva del header
                if (cuentaRegresivaElement) {
                    cuentaRegresivaElement.textContent = tiempoFormateado;
                }
                
                // Actualizar cuenta regresiva del modal
                if (cuentaRegresivaModal) {
                    cuentaRegresivaModal.textContent = tiempoFormateado;
                }
            } else {
                // El tiempo se acabó, recargar para obtener el siguiente módulo
                location.reload();
            }
        }
        
        // Función para fijar el tamaño de las tablas
        function fijarTamañoTablas() {
            // Solo ejecutar si no se ha establecido el tamaño antes
            if (tamañoEstablecido) {
                return;
            }
            
            const tablas = document.querySelectorAll('table');
            
            // Verificar que las tablas existan y tengan contenido
            if (tablas.length === 0) {
                // Si no hay tablas, intentar de nuevo en 500ms
                setTimeout(fijarTamañoTablas, 500);
                return;
            }
            
            // Verificar que al menos una tabla tenga filas
            const primeraTabla = tablas[0];
            const tbody = primeraTabla.querySelector('tbody');
            const filas = tbody ? tbody.querySelectorAll('tr') : [];
            
            if (filas.length === 0) {
                // Si no hay filas, intentar de nuevo en 500ms
                setTimeout(fijarTamañoTablas, 500);
                return;
            }
            
            // Calcular altura disponible para las tablas
            const viewportHeight = window.innerHeight;
            const headerHeight = 120; // Altura del header principal
            const paddingTop = 48; // p-6 = 24px * 2
            const paddingBottom = 48; // p-6 = 24px * 2
            const marginBottom = 16; // mb-4 = 16px
            
            // Altura disponible para las tablas (con altura máxima)
            let alturaDisponible = viewportHeight - headerHeight - paddingTop - paddingBottom - marginBottom;
            
            // Establecer altura máxima para evitar tablas infinitas
            const alturaMaxima = 800; // Altura máxima de 800px
            if (alturaDisponible > alturaMaxima) {
                alturaDisponible = alturaMaxima;
            }
            
            // Establecer altura mínima
            const alturaMinima = 400; // Altura mínima de 400px
            if (alturaDisponible < alturaMinima) {
                alturaDisponible = alturaMinima;
            }
            
            tablas.forEach(tabla => {
                // Establecer altura dinámica basada en la pantalla
                tabla.style.height = `${alturaDisponible}px`;
                tabla.style.minHeight = `${alturaDisponible}px`;
                tabla.style.maxHeight = `${alturaDisponible}px`;
                
                // Establecer ancho completo
                tabla.style.width = '100%';
                tabla.style.minWidth = '100%';
                tabla.style.maxWidth = '100%';
                
                const tbody = tabla.querySelector('tbody');
                const filas = tbody.querySelectorAll('tr');
                
                if (filas.length > 0) {
                    // Calcular altura por fila (altura total / número de filas)
                    const alturaPorFila = Math.floor(alturaDisponible / filas.length);
                    
                    filas.forEach(fila => {
                        fila.style.height = `${alturaPorFila}px`;
                        fila.style.minHeight = `${alturaPorFila}px`;
                        fila.style.maxHeight = `${alturaPorFila}px`;
                    });
                    
                    // Ajustar altura del tbody
                    tbody.style.height = `${alturaPorFila * filas.length}px`;
                }
            });
            
            // Marcar que el tamaño ya se estableció
            tamañoEstablecido = true;
        }
        
        // Iniciar cuenta regresiva solo si estamos en break
        if (esBreak) {
            setInterval(actualizarCuentaRegresiva, 1000);
        }
        
        // Función para ajustar el contenedor principal
        function ajustarContenedorPrincipal() {
            const contenedor = document.querySelector('.flex.flex-col.w-full.h-full.p-6');
            if (contenedor) {
                // Usar altura del viewport pero con límites
                const viewportHeight = window.innerHeight;
                const alturaMaxima = 1200; // Altura máxima del contenedor
                const alturaFinal = Math.min(viewportHeight, alturaMaxima);
                
                contenedor.style.height = `${alturaFinal}px`;
                contenedor.style.minHeight = '600px'; // Altura mínima
                contenedor.style.maxHeight = `${alturaMaxima}px`;
                contenedor.style.overflow = 'hidden';
            }
        }
        
        // Fijar tamaño de tablas cuando se carga la página
        document.addEventListener('DOMContentLoaded', function() {
            // Esperar más tiempo para que Livewire cargue completamente
            setTimeout(ajustarContenedorPrincipal, 200);
            setTimeout(fijarTamañoTablas, 1000);
        });
        
        // Fijar tamaño cuando cambia el tamaño de la ventana (solo si el usuario lo hace manualmente)
        window.addEventListener('resize', function() {
            setTimeout(ajustarContenedorPrincipal, 50);
            // Solo reajustar tablas si el usuario cambia el tamaño de la ventana
            if (tamañoEstablecido) {
                // Resetear la variable para permitir un nuevo cálculo
                tamañoEstablecido = false;
                setTimeout(fijarTamañoTablas, 100);
            }
        });
        
        // Actualizar datos cada 30 segundos (sin afectar el tamaño de las tablas)
        setInterval(function() {
            @this.actualizarAutomaticamente();
        }, 30000);
        
        // Fijar tamaño después de que Livewire actualice el contenido
        document.addEventListener('livewire:load', function() {
            // Ajustar inmediatamente cuando Livewire se carga
            setTimeout(ajustarContenedorPrincipal, 100);
            setTimeout(fijarTamañoTablas, 500);
            
            // Solo ajustar el contenedor principal en actualizaciones posteriores
            Livewire.hook('message.processed', (message, component) => {
                setTimeout(ajustarContenedorPrincipal, 50);
                // NO ajustar el tamaño de las tablas durante las actualizaciones
            });
        });
        
        // Ejecutar fijación inicial con más tiempo de espera
        setTimeout(ajustarContenedorPrincipal, 500);
        setTimeout(fijarTamañoTablas, 1500);
    </script>
</div>
