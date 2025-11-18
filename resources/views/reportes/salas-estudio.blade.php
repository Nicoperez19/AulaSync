<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
            <div class="flex items-center gap-3">
                <div class="p-2 rounded-xl bg-light-cloud-blue">
                    <i class="text-2xl text-white fa-solid fa-book"></i>
                </div>

                <div>
                    <h2 class="text-2xl font-bold leading-tight">Reporte de Salas de Estudio</h2>
                    <p class="text-sm text-gray-500">Accesos registrados agrupados por sesión</p>
                </div>
            </div>

            <div class="flex gap-2">
                <a href="{{ route('reportes.salas-estudio.export', ['format' => 'pdf']) }}?fecha_inicio={{ $fechaInicio }}&fecha_fin={{ $fechaFin }}{{ $salaId ? '&sala_id=' . $salaId : '' }}"
                   class="px-4 py-2 text-white transition-colors bg-red-600 rounded-lg hover:bg-red-700">
                    <i class="mr-2 fas fa-file-pdf"></i> Exportar PDF
                </a>
            </div>
        </div>
    </x-slot>

    <div class="px-4 min-h-[80vh]">
        <!-- Nav Pills -->
        <div class="mb-4">
            <nav class="flex gap-2" aria-label="Tabs">
                <button onclick="cambiarVista('registros')" id="pill-registros"
                        class="pill-button active px-4 py-2 text-sm font-medium text-white bg-light-cloud-blue rounded-lg">
                    <i class="mr-2 fas fa-book"></i>
                    Salas de Estudio
                </button>
                <button onclick="cambiarVista('vetados')" id="pill-vetados"
                        class="pill-button px-4 py-2 text-sm font-medium text-gray-700 bg-gray-200 rounded-lg hover:bg-gray-300 dark:bg-gray-700 dark:text-gray-300">
                    <i class="mr-2 fas fa-user-slash"></i>
                    Usuarios Vetados
                </button>
            </nav>
        </div>

        <!-- Vista Registros -->
        <div id="vista-registros" class="vista-content">
        <!-- KPIs -->
        <div class="grid grid-cols-1 gap-3 mb-4 md:grid-cols-4">
            <div class="p-3 bg-white rounded-lg shadow-md dark:bg-gray-800">
                <div class="flex items-center">
                    <div class="p-2 bg-blue-100 rounded-lg dark:bg-blue-900">
                        <i class="text-blue-600 fas fa-users dark:text-blue-400"></i>
                    </div>
                    <div class="ml-3">
                        <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Total accesos</p>
                        <p class="text-lg font-semibold text-gray-900 dark:text-white">{{ $totalAccesos }}</p>
                    </div>
                </div>
            </div>

            <div class="p-3 bg-white rounded-lg shadow-md dark:bg-gray-800">
                <div class="flex items-center">
                    <div class="p-2 bg-green-100 rounded-lg dark:bg-green-900">
                        <i class="text-green-600 fas fa-layer-group dark:text-green-400"></i>
                    </div>
                    <div class="ml-3">
                        <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Total grupos</p>
                        <p class="text-lg font-semibold text-gray-900 dark:text-white">{{ $totalGrupos }}</p>
                    </div>
                </div>
            </div>

            <div class="p-3 bg-white rounded-lg shadow-md dark:bg-gray-800">
                <div class="flex items-center">
                    <div class="p-2 bg-yellow-100 rounded-lg dark:bg-yellow-900">
                        <i class="text-yellow-600 fas fa-door-open dark:text-yellow-400"></i>
                    </div>
                    <div class="ml-3">
                        <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Salas utilizadas</p>
                        <p class="text-lg font-semibold text-gray-900 dark:text-white">{{ $salasUsadas }}</p>
                    </div>
                </div>
            </div>

            <div class="p-3 bg-white rounded-lg shadow-md dark:bg-gray-800">
                <div class="flex items-center">
                    <div class="p-2 bg-purple-100 rounded-lg dark:bg-purple-900">
                        <i class="text-purple-600 fas fa-user-friends dark:text-purple-400"></i>
                    </div>
                    <div class="ml-3">
                        <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Promedio personas/grupo</p>
                        <p class="text-lg font-semibold text-gray-900 dark:text-white">{{ $promedioPersonasPorGrupo }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filtros -->
        <div class="p-4 mb-4 bg-white rounded-lg shadow-md dark:bg-gray-800">
            <h3 class="flex items-center gap-2 mb-3 text-base font-semibold text-gray-700 dark:text-gray-300">
                <i class="fas fa-filter"></i> Filtros de búsqueda
            </h3>
            <form method="GET" action="{{ route('reportes.salas-estudio') }}" class="flex flex-wrap items-end gap-4">
                <div class="flex-1 min-w-[200px]">
                    <label for="fecha_inicio" class="block mb-1 text-sm font-medium text-gray-700 dark:text-gray-300">
                        Fecha inicio
                    </label>
                    <input type="date" id="fecha_inicio" name="fecha_inicio" value="{{ $fechaInicio }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                </div>

                <div class="flex-1 min-w-[200px]">
                    <label for="fecha_fin" class="block mb-1 text-sm font-medium text-gray-700 dark:text-gray-300">
                        Fecha fin
                    </label>
                    <input type="date" id="fecha_fin" name="fecha_fin" value="{{ $fechaFin }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                </div>

                <div class="flex-1 min-w-[200px]">
                    <label for="sala_id" class="block mb-1 text-sm font-medium text-gray-700 dark:text-gray-300">
                        Sala
                    </label>
                    <select id="sala_id" name="sala_id"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                        <option value="">Todas las salas</option>
                        @foreach($salasEstudio as $sala)
                            <option value="{{ $sala->id_espacio }}" {{ $salaId == $sala->id_espacio ? 'selected' : '' }}>
                                {{ $sala->nombre_espacio }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="flex gap-2">
                    <button type="submit"
                            class="px-4 py-2 text-white transition-colors bg-light-cloud-blue rounded-lg hover:bg-[#b10718]">
                        <i class="mr-2 fas fa-search"></i> Filtrar
                    </button>
                    <a href="{{ route('reportes.salas-estudio') }}"
                       class="px-4 py-2 text-white transition-colors bg-gray-500 rounded-lg hover:bg-gray-600">
                        <i class="mr-2 fas fa-times"></i> Limpiar
                    </a>
                </div>
            </form>
        </div>
        <!-- Datos agrupados por sala -->
        @if(count($gruposPorSala) > 0)
            @foreach($gruposPorSala as $idSala => $data)
                <div class="mb-6 overflow-hidden bg-white rounded-lg shadow-md dark:bg-gray-800">
                    <div class="p-4 text-gray-800 bg-gray-200 dark:bg-gray-700 dark:text-gray-200">
                        <h3 class="flex items-center gap-2 text-lg font-semibold">
                            <i class="fas fa-door-open"></i>
                            {{ $data['sala']->nombre_espacio }}
                            <span class="ml-auto text-sm font-normal">
                                Capacidad: {{ $data['sala']->capacidad_maxima }} personas
                            </span>
                        </h3>
                    </div>

                    <div class="p-4">
                        @foreach($data['grupos'] as $index => $grupo)
                            <div class="p-4 mb-4 bg-gray-50 rounded-lg dark:bg-gray-700">
                                <div class="flex items-center justify-between mb-3">
                                    <h4 class="text-base font-semibold text-gray-800 dark:text-gray-200">
                                        Grupo #{{ $index + 1 }} - {{ $grupo['fecha']->format('d/m/Y') }}
                                    </h4>
                                    <div class="flex items-center gap-2">
                                        <button onclick="vetarGrupo('{{ $grupo['reservas'][0]->id_reserva }}', '{{ $data['sala']->nombre_espacio }}', {{ $index + 1 }})"
                                                class="px-3 py-1 text-xs text-white transition-colors bg-red-600 rounded hover:bg-red-700">
                                            <i class="mr-1 fas fa-ban"></i> Vetar Grupo
                                        </button>
                                        <div class="text-sm text-gray-600 dark:text-gray-400">
                                            <i class="mr-1 fas fa-clock"></i>
                                            {{ $grupo['hora_inicio']->format('H:i') }} - {{ $grupo['hora_fin']->format('H:i') }}
                                            <span class="ml-2 text-xs text-gray-500">
                                                ({{ round($grupo['hora_inicio']->diffInMinutes($grupo['hora_fin']) / 60, 1) }} hrs)
                                            </span>
                                        </div>
                                    </div>
                                </div>

                                <div class="overflow-x-auto">
                                    <table class="min-w-full text-sm">
                                        <thead class="bg-gray-200 dark:bg-gray-600">
                                            <tr>
                                                <th class="px-3 py-2 text-left">#</th>
                                                <th class="px-3 py-2 text-left">RUN</th>
                                                <th class="px-3 py-2 text-left">Nombre</th>
                                                <th class="px-3 py-2 text-left">Entrada</th>
                                                <th class="px-3 py-2 text-left">Salida</th>
                                                <th class="px-3 py-2 text-left">Tiempo</th>
                                                <th class="px-3 py-2 text-left">Acciones</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-gray-200 dark:divide-gray-600">
                                            @foreach($grupo['reservas'] as $i => $reserva)
                                                <tr class="hover:bg-gray-100 dark:hover:bg-gray-600">
                                                    <td class="px-3 py-2">{{ $i + 1 }}</td>
                                                    <td class="px-3 py-2">{{ $reserva->run_solicitante }}</td>
                                                    <td class="px-3 py-2 font-medium">
                                                        {{ $reserva->solicitante->nombre ?? 'N/A' }}
                                                    </td>
                                                    <td class="px-3 py-2">
                                                        {{ \Carbon\Carbon::parse($reserva->hora)->format('H:i') }}
                                                    </td>
                                                    <td class="px-3 py-2">
                                                        @if($reserva->hora_salida)
                                                            {{ \Carbon\Carbon::parse($reserva->hora_salida)->format('H:i') }}
                                                        @else
                                                            <span class="text-gray-400">--:--</span>
                                                        @endif
                                                    </td>
                                                    <td class="px-3 py-2 text-gray-600 dark:text-gray-400">
                                                        @if($reserva->hora_salida)
                                                            {{ round(\Carbon\Carbon::parse($reserva->hora)->diffInMinutes(\Carbon\Carbon::parse($reserva->hora_salida)) / 60, 1) }} hrs
                                                        @else
                                                            <span class="text-gray-400">--</span>
                                                        @endif
                                                    </td>
                                                    <td class="px-3 py-2">
                                                        <button onclick="vetarIndividual('{{ $reserva->run_solicitante }}', '{{ $reserva->solicitante->nombre ?? 'N/A' }}', '{{ $reserva->id_reserva }}')"
                                                                class="px-2 py-1 text-xs text-white transition-colors bg-red-600 rounded hover:bg-red-700">
                                                            <i class="fas fa-ban"></i> Vetar
                                                        </button>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>

                                <div class="mt-2 text-xs text-gray-500">
                                    {{ count($grupo['reservas']) }} personas en este grupo
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach
        @else
            <div class="p-8 text-center bg-white rounded-lg shadow-md dark:bg-gray-800">
                <i class="mb-3 text-5xl text-gray-400 fas fa-inbox"></i>
                <p class="text-lg text-gray-600 dark:text-gray-400">No hay registros para el período seleccionado</p>
            </div>
        @endif
        </div>

        <!-- Vista Usuarios Vetados -->
        <div id="vista-vetados" class="hidden vista-content">
            <div class="p-4 mb-4 bg-white rounded-lg shadow-md dark:bg-gray-800">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-base font-semibold text-gray-700 dark:text-gray-300">
                        <i class="mr-2 fas fa-user-slash"></i>
                        Lista de Usuarios Vetados
                    </h3>
                    <div class="flex gap-2">
                        <button onclick="exportarVetados()" id="btn-exportar-vetados"
                                class="px-4 py-2 text-sm text-white transition-colors bg-green-600 rounded-lg hover:bg-green-700">
                            <i class="mr-2 fas fa-file-excel"></i> Exportar Excel
                        </button>
                        <button onclick="filtrarVetados('activo')" id="filter-activo"
                                class="filter-button active px-4 py-2 text-sm text-white bg-red-600 rounded-lg hover:bg-red-700">
                            Activos
                        </button>
                        <button onclick="filtrarVetados('liberado')" id="filter-liberado"
                                class="filter-button px-4 py-2 text-sm text-gray-700 bg-gray-200 rounded-lg hover:bg-gray-300 dark:bg-gray-700 dark:text-gray-300">
                            Liberados
                        </button>
                        <button onclick="filtrarVetados('')" id="filter-todos"
                                class="filter-button px-4 py-2 text-sm text-gray-700 bg-gray-200 rounded-lg hover:bg-gray-300 dark:bg-gray-700 dark:text-gray-300">
                            Todos
                        </button>
                    </div>
                </div>

                <div id="listaVetados" class="space-y-3">
                    <!-- Se llenará con JavaScript -->
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para vetar individual -->
    <div id="modalVetarIndividual" class="fixed inset-0 z-50 items-center justify-center hidden bg-black bg-opacity-50">
        <div class="w-full max-w-md p-6 mx-4 bg-white rounded-lg shadow-xl dark:bg-gray-800">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-bold text-gray-900 dark:text-white">
                    <i class="mr-2 text-red-600 fas fa-ban"></i> Vetar Usuario
                </h3>
                <button onclick="cerrarModalVetar()" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <div class="mb-4">
                <p class="text-sm text-gray-600 dark:text-gray-400">RUN: <span id="vetoRun" class="font-semibold"></span></p>
                <p class="text-sm text-gray-600 dark:text-gray-400">Nombre: <span id="vetoNombre" class="font-semibold"></span></p>
            </div>

            <div class="mb-4">
                <label class="block mb-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                    Motivo del veto <span class="text-red-500">*</span>
                </label>
                <textarea id="vetoObservacion" rows="4" 
                          class="w-full px-3 py-2 border border-gray-300 rounded-lg dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                          placeholder="Describe el motivo del veto..."></textarea>
            </div>

            <div class="flex gap-2">
                <button onclick="confirmarVetoIndividual()" 
                        class="flex-1 px-4 py-2 text-white transition-colors bg-red-600 rounded-lg hover:bg-red-700">
                    <i class="mr-2 fas fa-check"></i> Confirmar Veto
                </button>
                <button onclick="cerrarModalVetar()" 
                        class="flex-1 px-4 py-2 text-gray-700 transition-colors bg-gray-200 rounded-lg hover:bg-gray-300 dark:bg-gray-700 dark:text-gray-300">
                    <i class="mr-2 fas fa-times"></i> Cancelar
                </button>
            </div>
        </div>
    </div>

    <!-- Modal para vetar grupo -->
    <div id="modalVetarGrupo" class="fixed inset-0 z-50 items-center justify-center hidden bg-black bg-opacity-50">
        <div class="w-full max-w-md p-6 mx-4 bg-white rounded-lg shadow-xl dark:bg-gray-800">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-bold text-gray-900 dark:text-white">
                    <i class="mr-2 text-red-600 fas fa-ban"></i> Vetar Grupo Completo
                </h3>
                <button onclick="cerrarModalVetarGrupo()" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <div class="p-3 mb-4 border-l-4 border-amber-500 bg-amber-50 dark:bg-amber-900/20">
                <p class="text-sm font-medium text-amber-800 dark:text-amber-200">
                    <i class="mr-1 fas fa-exclamation-triangle"></i> Se vetará a todos los miembros del <span id="grupoInfo" class="font-bold"></span>
                </p>
            </div>

            <div class="mb-4">
                <label class="block mb-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                    Motivo del veto grupal <span class="text-red-500">*</span>
                </label>
                <textarea id="vetoGrupoObservacion" rows="4" 
                          class="w-full px-3 py-2 border border-gray-300 rounded-lg dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                          placeholder="Describe el motivo del veto grupal..."></textarea>
            </div>

            <div class="flex gap-2">
                <button onclick="confirmarVetoGrupo()" 
                        class="flex-1 px-4 py-2 text-white transition-colors bg-red-600 rounded-lg hover:bg-red-700">
                    <i class="mr-2 fas fa-check"></i> Confirmar Veto Grupal
                </button>
                <button onclick="cerrarModalVetarGrupo()" 
                        class="flex-1 px-4 py-2 text-gray-700 transition-colors bg-gray-200 rounded-lg hover:bg-gray-300 dark:bg-gray-700 dark:text-gray-300">
                    <i class="mr-2 fas fa-times"></i> Cancelar
                </button>
            </div>
        </div>
    </div>

    <!-- Modal para editar veto -->
    <div id="modalEditarVeto" class="fixed inset-0 z-50 items-center justify-center hidden bg-black bg-opacity-50">
        <div class="w-full max-w-md p-6 mx-4 bg-white rounded-lg shadow-xl dark:bg-gray-800">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-bold text-gray-900 dark:text-white">
                    <i class="mr-2 text-blue-600 fas fa-edit"></i> Editar Observación
                </h3>
                <button onclick="cerrarModalEditar()" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <div class="mb-4">
                <label class="block mb-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                    Nueva observación <span class="text-red-500">*</span>
                </label>
                <textarea id="editarObservacion" rows="4" 
                          class="w-full px-3 py-2 border border-gray-300 rounded-lg dark:bg-gray-700 dark:border-gray-600 dark:text-white"></textarea>
            </div>

            <div class="flex gap-2">
                <button onclick="confirmarEdicion()" 
                        class="flex-1 px-4 py-2 text-white transition-colors bg-blue-600 rounded-lg hover:bg-blue-700">
                    <i class="mr-2 fas fa-save"></i> Guardar
                </button>
                <button onclick="cerrarModalEditar()" 
                        class="flex-1 px-4 py-2 text-gray-700 transition-colors bg-gray-200 rounded-lg hover:bg-gray-300 dark:bg-gray-700 dark:text-gray-300">
                    <i class="mr-2 fas fa-times"></i> Cancelar
                </button>
            </div>
        </div>
    </div>

    <script>
        let vetoActual = {};
        let grupoActual = {};
        let vetoEditando = null;

        // Vetar individual
        function vetarIndividual(run, nombre, idReserva) {
            vetoActual = { run, nombre, idReserva };
            document.getElementById('vetoRun').textContent = run;
            document.getElementById('vetoNombre').textContent = nombre;
            document.getElementById('vetoObservacion').value = '';
            document.getElementById('modalVetarIndividual').classList.remove('hidden');
            document.getElementById('modalVetarIndividual').classList.add('flex');
        }

        function cerrarModalVetar() {
            document.getElementById('modalVetarIndividual').classList.add('hidden');
            document.getElementById('modalVetarIndividual').classList.remove('flex');
        }

        async function confirmarVetoIndividual() {
            const observacion = document.getElementById('vetoObservacion').value.trim();
            
            if (!observacion) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Campo requerido',
                    text: 'Debe ingresar el motivo del veto',
                    confirmButtonColor: '#dc2626'
                });
                return;
            }

            try {
                const response = await fetch('/api/sala-estudio/vetar-individual', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        run: vetoActual.run,
                        observacion: observacion,
                        id_reserva: vetoActual.idReserva
                    })
                });

                const data = await response.json();

                if (data.success) {
                    await Swal.fire({
                        icon: 'success',
                        title: 'Veto aplicado',
                        text: 'Usuario vetado correctamente',
                        confirmButtonColor: '#10b981'
                    });
                    cerrarModalVetar();
                    location.reload();
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: data.message,
                        confirmButtonColor: '#dc2626'
                    });
                }
            } catch (error) {
                console.error('Error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Error al aplicar el veto',
                    confirmButtonColor: '#dc2626'
                });
            }
        }

        // Vetar grupo
        function vetarGrupo(idReserva, sala, grupoNum) {
            grupoActual = { idReserva, sala, grupoNum };
            document.getElementById('grupoInfo').textContent = `Grupo #${grupoNum} de ${sala}`;
            document.getElementById('vetoGrupoObservacion').value = '';
            document.getElementById('modalVetarGrupo').classList.remove('hidden');
            document.getElementById('modalVetarGrupo').classList.add('flex');
        }

        function cerrarModalVetarGrupo() {
            document.getElementById('modalVetarGrupo').classList.add('hidden');
            document.getElementById('modalVetarGrupo').classList.remove('flex');
        }

        async function confirmarVetoGrupo() {
            const observacion = document.getElementById('vetoGrupoObservacion').value.trim();
            
            if (!observacion) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Campo requerido',
                    text: 'Debe ingresar el motivo del veto grupal',
                    confirmButtonColor: '#dc2626'
                });
                return;
            }

            try {
                const response = await fetch('/api/sala-estudio/vetar-grupo', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        id_reserva: grupoActual.idReserva,
                        observacion: observacion
                    })
                });

                const data = await response.json();

                if (data.success) {
                    const mensaje = `${data.vetados} usuarios vetados${data.ya_vetados > 0 ? '<br>' + data.ya_vetados + ' ya estaban vetados' : ''}`;
                    await Swal.fire({
                        icon: 'success',
                        title: 'Veto grupal aplicado',
                        html: mensaje,
                        confirmButtonColor: '#10b981'
                    });
                    cerrarModalVetarGrupo();
                    location.reload();
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: data.message,
                        confirmButtonColor: '#dc2626'
                    });
                }
            } catch (error) {
                console.error('Error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Error al aplicar el veto grupal',
                    confirmButtonColor: '#dc2626'
                });
            }
        }

        // Mostrar lista de vetados
        async function mostrarVetados() {
            cambiarTab('vetados');
        }

        function cerrarModalVetados() {
            // Ya no se usa - removido
        }

        async function filtrarVetados(estado) {
            // Actualizar botones activos
            document.querySelectorAll('.filter-button').forEach(btn => {
                btn.classList.remove('active', 'bg-red-600', 'text-white');
                btn.classList.add('bg-gray-200', 'text-gray-700', 'dark:bg-gray-700', 'dark:text-gray-300');
            });
            
            const activeButton = estado === 'activo' ? 'filter-activo' : (estado === 'liberado' ? 'filter-liberado' : 'filter-todos');
            const btn = document.getElementById(activeButton);
            btn.classList.add('active', 'bg-red-600', 'text-white');
            btn.classList.remove('bg-gray-200', 'text-gray-700', 'dark:bg-gray-700', 'dark:text-gray-300');
            
            await cargarVetados(estado, 'listaVetados');
        }

        async function cargarVetados(estado = '', containerId = 'listaVetados') {
            try {
                const url = estado ? `/api/sala-estudio/vetos?estado=${estado}` : '/api/sala-estudio/vetos';
                console.log('Cargando vetos desde:', url);
                
                const response = await fetch(url);
                console.log('Response status:', response.status);
                
                const data = await response.json();
                console.log('Data recibida:', data);

                const container = document.getElementById(containerId);
                
                if (data.success && data.vetos.length > 0) {
                    container.innerHTML = data.vetos.map(veto => `
                        <div class="p-4 border rounded-lg ${veto.estado === 'activo' ? 'bg-red-50 border-red-200' : 'bg-green-50 border-green-200'} dark:bg-gray-700">
                            <div class="flex items-start justify-between">
                                <div class="flex-1">
                                    <div class="flex items-center gap-2 mb-2">
                                        <span class="px-2 py-1 text-xs font-semibold rounded ${veto.estado === 'activo' ? 'bg-red-200 text-red-800' : 'bg-green-200 text-green-800'}">
                                            ${veto.estado === 'activo' ? 'ACTIVO' : 'LIBERADO'}
                                        </span>
                                        <span class="px-2 py-1 text-xs font-semibold rounded ${veto.tipo_veto === 'grupal' ? 'bg-orange-200 text-orange-800' : 'bg-blue-200 text-blue-800'}">
                                            ${veto.tipo_veto === 'grupal' ? 'Grupal' : 'Individual'}
                                        </span>
                                    </div>
                                    <p class="font-semibold text-gray-900 dark:text-white">
                                        ${veto.solicitante?.nombre || 'N/A'} (RUN: ${veto.run_vetado})
                                    </p>
                                    <p class="mt-2 text-sm text-gray-700 dark:text-gray-300">
                                        <strong>Motivo:</strong> ${veto.observacion}
                                    </p>
                                    <div class="mt-2 text-xs text-gray-600 dark:text-gray-400">
                                        <p>Vetado por: ${veto.vetado_por || 'Sistema'} - ${new Date(veto.fecha_veto).toLocaleString('es-CL')}</p>
                                        ${veto.estado === 'liberado' ? `<p>Liberado por: ${veto.liberado_por || 'Sistema'} - ${new Date(veto.fecha_liberacion).toLocaleString('es-CL')}</p>` : ''}
                                    </div>
                                </div>
                                ${veto.estado === 'activo' ? `
                                <div class="flex gap-1 ml-2">
                                    <button onclick="editarVeto(${veto.id}, '${veto.observacion}')" 
                                            class="px-2 py-1 text-xs text-white transition-colors bg-blue-600 rounded hover:bg-blue-700"
                                            title="Editar observación">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button onclick="liberarVeto(${veto.id})" 
                                            class="px-2 py-1 text-xs text-white transition-colors bg-green-600 rounded hover:bg-green-700"
                                            title="Liberar veto">
                                        <i class="fas fa-unlock"></i>
                                    </button>
                                </div>
                                ` : ''}
                            </div>
                        </div>
                    `).join('');
                } else {
                    container.innerHTML = `
                        <div class="p-8 text-center text-gray-500">
                            <i class="mb-2 text-4xl fas fa-inbox"></i>
                            <p>No hay vetos ${estado ? estado + 's' : 'registrados'}</p>
                        </div>
                    `;
                }
            } catch (error) {
                console.error('Error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Error al cargar la lista de vetados',
                    confirmButtonColor: '#dc2626'
                });
            }
        }

        // Liberar veto
        async function liberarVeto(id) {
            const result = await Swal.fire({
                icon: 'question',
                title: '¿Liberar veto?',
                text: '¿Está seguro de liberar este veto?',
                showCancelButton: true,
                confirmButtonText: 'Sí, liberar',
                cancelButtonText: 'Cancelar',
                confirmButtonColor: '#10b981',
                cancelButtonColor: '#6b7280'
            });

            if (!result.isConfirmed) {
                return;
            }

            try {
                const response = await fetch(`/api/sala-estudio/veto/${id}/liberar`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                });

                const data = await response.json();

                if (data.success) {
                    await Swal.fire({
                        icon: 'success',
                        title: 'Veto liberado',
                        text: 'Veto liberado correctamente',
                        confirmButtonColor: '#10b981',
                        timer: 2000
                    });
                    await cargarVetados('activo', 'listaVetados');
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: data.message,
                        confirmButtonColor: '#dc2626'
                    });
                }
            } catch (error) {
                console.error('Error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Error al liberar el veto',
                    confirmButtonColor: '#dc2626'
                });
            }
        }

        // Editar veto
        function editarVeto(id, observacionActual) {
            vetoEditando = id;
            document.getElementById('editarObservacion').value = observacionActual;
            document.getElementById('modalEditarVeto').classList.remove('hidden');
            document.getElementById('modalEditarVeto').classList.add('flex');
        }

        function cerrarModalEditar() {
            document.getElementById('modalEditarVeto').classList.add('hidden');
            document.getElementById('modalEditarVeto').classList.remove('flex');
            vetoEditando = null;
        }

        async function confirmarEdicion() {
            const nuevaObservacion = document.getElementById('editarObservacion').value.trim();
            
            if (!nuevaObservacion) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Campo requerido',
                    text: 'Debe ingresar una observación',
                    confirmButtonColor: '#dc2626'
                });
                return;
            }

            try {
                const response = await fetch(`/api/sala-estudio/veto/${vetoEditando}`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        observacion: nuevaObservacion
                    })
                });

                const data = await response.json();

                if (data.success) {
                    await Swal.fire({
                        icon: 'success',
                        title: 'Actualizado',
                        text: 'Observación actualizada correctamente',
                        confirmButtonColor: '#10b981',
                        timer: 2000
                    });
                    cerrarModalEditar();
                    await cargarVetados('activo', 'listaVetados');
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: data.message,
                        confirmButtonColor: '#dc2626'
                    });
                }
            } catch (error) {
                console.error('Error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Error al actualizar la observación',
                    confirmButtonColor: '#dc2626'
                });
            }
        }

        // Cambiar entre vistas
        function cambiarVista(vista) {
            // Ocultar todas las vistas
            document.querySelectorAll('.vista-content').forEach(v => {
                v.classList.add('hidden');
            });

            // Remover estado activo de todos los pills
            document.querySelectorAll('.pill-button').forEach(btn => {
                btn.classList.remove('active', 'bg-light-cloud-blue', 'text-white');
                btn.classList.add('bg-gray-200', 'text-gray-700', 'dark:bg-gray-700', 'dark:text-gray-300');
            });

            // Mostrar vista seleccionada
            document.getElementById(`vista-${vista}`).classList.remove('hidden');

            // Activar pill seleccionado
            const activeBtn = document.getElementById(`pill-${vista}`);
            activeBtn.classList.add('active', 'bg-light-cloud-blue', 'text-white');
            activeBtn.classList.remove('bg-gray-200', 'text-gray-700', 'dark:bg-gray-700', 'dark:text-gray-300');

            // Si cambia a vista de vetados, cargar datos
            if (vista === 'vetados') {
                filtrarVetados('activo');
            }
        }

        // Exportar vetados a Excel
        async function exportarVetados() {
            const estadoActual = document.querySelector('.filter-button.active').id.replace('filter-', '');
            
            try {
                const url = estadoActual ? `/api/sala-estudio/vetos/export?estado=${estadoActual}` : '/api/sala-estudio/vetos/export';
                
                const response = await fetch(url, {
                    method: 'GET',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                });

                if (response.ok) {
                    const blob = await response.blob();
                    const downloadUrl = window.URL.createObjectURL(blob);
                    const a = document.createElement('a');
                    a.href = downloadUrl;
                    a.download = `usuarios-vetados-${estadoActual || 'todos'}-${new Date().toISOString().split('T')[0]}.xlsx`;
                    document.body.appendChild(a);
                    a.click();
                    window.URL.revokeObjectURL(downloadUrl);
                    a.remove();

                    Swal.fire({
                        icon: 'success',
                        title: 'Exportado',
                        text: 'El archivo se ha descargado correctamente',
                        confirmButtonColor: '#10b981',
                        timer: 2000
                    });
                } else {
                    throw new Error('Error en la descarga');
                }
            } catch (error) {
                console.error('Error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Error al exportar el reporte',
                    confirmButtonColor: '#dc2626'
                });
            }
        }

        // Cargar vetos activos al cargar la página
        document.addEventListener('DOMContentLoaded', function() {
            // No cargar automáticamente, solo cuando se acceda a la vista
        });
    </script>
</x-app-layout>
