<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col items-center gap-2 md:flex-row md:items-center md:justify-between">
            <h2 class="text-2xl font-bold leading-tight text-gray-900 text-center w-full">
                {{ __('Horarios por Espacios') }}
            </h2>
        </div>
    </x-slot>

    <!-- Indicador de carga inicial -->
    <div id="loadingIndicator" class="fixed inset-0 z-50 flex items-center justify-center bg-white bg-opacity-90">
        <div class="text-center">
            <div class="animate-spin rounded-full h-16 w-16 border-b-2 border-red-700 mx-auto mb-4"></div>
            <p class="text-lg font-semibold text-gray-700">Cargando horarios...</p>
            <p class="text-sm text-gray-500">Preparando datos para una experiencia más rápida</p>
        </div>
    </div>

    <div class="p-6 space-y-6 flex flex-col items-center justify-center" x-data="{ selectedPiso: '{{ $pisos->first()->id ?? 1 }}' }">
        <!-- Nav Pills de Pisos -->
        <div class="w-full max-w-7xl mx-auto">
            <div class="bg-white shadow-md rounded-t-xl">
                <ul class="flex border-b border-gray-200 justify-center" role="tablist">
                    @foreach ($pisos as $piso)
                        <li role="presentation">
                            <button type="button" @click="selectedPiso = '{{ $piso->id }}'"
                                class="px-8 py-3 text-base font-semibold transition-all duration-300 border border-b-0 rounded-t-xl focus:outline-none"
                                :class="selectedPiso == '{{ $piso->id }}' 
                                        ? 'bg-red-700 text-white border-red-700 shadow-md'
                                        : 'bg-white text-gray-700 border-gray-200 hover:bg-gray-100 hover:text-red-700'">
                                Piso {{ $piso->numero_piso }}
                            </button>
                        </li>
                    @endforeach
                </ul>
                <!-- Cards de espacios por piso -->
                <div class="p-6 bg-white shadow-md rounded-b-xl">
                    @foreach ($pisos as $piso)
                        <div x-show="selectedPiso == '{{ $piso->id }}'"
                            x-transition:enter="transition ease-out duration-300"
                            x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                            x-transition:leave="transition ease-in duration-200"
                            x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95">
                            @php
                                $iconos = [
                                    'Auditorio' => 'fa-solid fa-chalkboard',
                                    'Laboratorio' => 'fa-solid fa-flask',
                                    'Sala de Reuniones' => 'fa-solid fa-comments',
                                    'Aula' => 'fa-solid fa-graduation-cap',
                                    'Taller' => 'fa-solid fa-tools',
                                    'Sala de Estudio' => 'fa-solid fa-book',
                                ];
                                $badgeColores = [
                                    'Auditorio' => 'bg-purple-100 text-purple-700',
                                    'Laboratorio' => 'bg-blue-100 text-blue-700',
                                    'Sala de Reuniones' => 'bg-green-100 text-green-700',
                                    'Aula' => 'bg-yellow-100 text-yellow-700',
                                    'Taller' => 'bg-orange-100 text-orange-700',
                                    'Sala de Estudio' => 'bg-pink-100 text-pink-700',
                                ];
                                $espaciosPorTipo = collect($piso->espacios)->groupBy('tipo_espacio');
                            @endphp
                            <div class="flex flex-col items-center w-full">
                                @foreach($espaciosPorTipo as $tipo => $espacios)
                                    @php
                                        $icono = $iconos[$tipo] ?? 'fa-solid fa-door-closed';
                                        $badgeTipo = $badgeColores[$tipo] ?? 'bg-gray-100 text-gray-700';
                                    @endphp
                                    <div class="mb-8 w-full flex flex-col items-center">
                                        <div class="flex flex-col items-center justify-center mb-3 w-full">
                                            <div class="flex items-center gap-3 justify-center w-full">
                                                <i class="{{ $icono }} text-2xl text-gray-700"></i>
                                                <h3 class="text-xl font-bold text-gray-900">{{ $tipo }}</h3>
                                                <span class="px-2 py-0.5 text-xs font-semibold bg-gray-200 rounded-full text-gray-700">{{ $espacios->count() }} espacio{{ $espacios->count() > 1 ? 's' : '' }}</span>
                                            </div>
                                        </div>
                                        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-6 justify-center w-full">
                                            @foreach ($espacios as $espacio)
                                                @php
                                                    $estado = $espacio->esta_ocupado ? 'Ocupado' : 'Disponible';
                                                    $puntoEstado = $espacio->esta_ocupado ? 'bg-red-500' : 'bg-green-500';
                                                @endphp
                                                <div class="relative flex flex-col justify-between p-4 border border-gray-200 rounded-xl shadow-sm bg-white transition hover:shadow-lg hover:scale-[1.03] cursor-pointer group items-center text-center mx-auto"
                                        data-id="{{ $espacio->id_espacio }}" data-nombre="{{ $espacio->nombre_espacio }}"
                                        @click="mostrarHorarioEspacio('{{ $espacio->id_espacio }}', '{{ $espacio->nombre_espacio }}')">
                                                    <div class="flex items-center justify-center gap-2 mb-2">
                                                        <i class="{{ $icono }} text-xl text-gray-500"></i>
                                                        <span class="font-bold text-gray-800">{{ $espacio->id_espacio }}</span>
                                                    </div>
                                                    <span class="flex items-center justify-center gap-1 text-xs font-semibold mb-1">
                                                        <span class="inline-block w-2 h-2 rounded-full {{ $puntoEstado }}"></span>
                                                        {{ $estado }}
                                                    </span>
                                                    <div class="mb-1 text-base font-semibold text-gray-900">{{ $espacio->nombre_espacio }}</div>
                                                    <div class="flex items-center justify-center gap-2 mb-2">
                                                        <span class="px-2 py-0.5 text-xs font-semibold rounded-full {{ $badgeTipo }}">{{ $tipo }}</span>
                                                        <span class="flex items-center gap-1 text-xs text-gray-500">
                                                            <i class="fa-solid fa-users"></i> {{ $espacio->capacidad ?? '-' }} personas
                                                        </span>
                                                    </div>
                                                    <span class="flex items-center justify-center gap-1 text-xs font-medium text-violet-700 group-hover:underline">
                                                        <i class="fa-solid fa-calendar-days"></i> 
                                                        Ver horarios 
                                                        <span class="ml-1 px-1.5 py-0.5 bg-violet-100 text-violet-700 rounded-full text-xs font-semibold" 
                                                              x-text="(horariosPorEspacio['{{ $espacio->id_espacio }}'] || []).length + ' clases'">
                                                        </span>
                                                    </span>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para mostrar horarios del espacio -->
    <div id="horarioEspacioModal"
        class="fixed inset-0 z-[9999] flex items-center justify-center bg-black bg-opacity-50 hidden">
        <div class="w-full max-w-7xl mx-2 md:mx-8 bg-white rounded-lg shadow-lg overflow-hidden max-h-screen flex flex-col">
            <!-- Encabezado rojo con diseño tipo banner -->
            <div id="modalHeader" class="relative bg-red-700 p-8 flex flex-col md:flex-row md:items-center md:justify-between gap-6">
                <!-- Círculos decorativos -->
                <span class="absolute left-0 top-0 w-32 h-32 bg-white bg-opacity-10 rounded-full -translate-x-1/2 -translate-y-1/2 pointer-events-none"></span>
                <span class="absolute right-0 top-0 w-32 h-32 bg-white bg-opacity-10 rounded-full translate-x-1/2 -translate-y-1/2 pointer-events-none"></span>
                <div class="flex items-center gap-5 flex-1 min-w-0">
                    <div class="flex-shrink-0 flex flex-col items-center justify-center">
                        <div class="bg-white bg-opacity-20 rounded-full p-4 mb-2">
                            <i class="fa-solid fa-calendar-days text-3xl text-white"></i>
                        </div>
                    </div>
                    <div class="flex flex-col min-w-0">
                        <h1 id="modalEspacioTitle" class="text-3xl font-bold text-white truncate"></h1>
                        <div class="flex items-center gap-2 mt-1">
                            <i class="fa-solid fa-location-dot text-white/80"></i>
                            <span id="modalEspacioTipo" class="text-lg text-white/80 truncate"></span>
                        </div>
                    </div>
                </div>
                <div class="flex items-center gap-3 flex-shrink-0 self-start md:self-center">
                    <button id="exportPdfBtn" class="border border-white text-white px-4 py-1.5 rounded-lg font-semibold hover:bg-white hover:text-red-700 transition">Exportar PDF</button>
                    <button onclick="cerrarModalEspacio()" class="text-3xl font-bold text-white hover:text-gray-200 ml-2">&times;</button>
                </div>
            </div>
            <div class="p-6 bg-gray-50 overflow-y-auto max-h-[70vh] flex-1">
                <div class="overflow-x-auto">
                    <table class="min-w-full bg-white rounded-lg overflow-hidden">
                        <thead>
                        <tr class="bg-gray-100">
                                <th class="px-4 py-3 font-semibold text-center border-b text-gray-600"><i class="fa-regular fa-clock mr-1"></i>Hora</th>
                            <th class="px-4 py-3 font-semibold text-center border-b">Lunes</th>
                            <th class="px-4 py-3 font-semibold text-center border-b">Martes</th>
                            <th class="px-4 py-3 font-semibold text-center border-b">Miércoles</th>
                            <th class="px-4 py-3 font-semibold text-center border-b">Jueves</th>
                            <th class="px-4 py-3 font-semibold text-center border-b">Viernes</th>
                            <th class="px-4 py-3 font-semibold text-center border-b">Sábado</th>
                        </tr>
                    </thead>
                    <tbody id="horarioEspacioBody"></tbody>
                </table>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Convertir los datos de PHP a JavaScript
        const horariosPorEspacio = @json($horariosPorEspacio);

        // Diccionario de colores pastel muy claros para bloques de clase
        const coloresClases = [
            'bg-pink-50', 'bg-blue-50', 'bg-yellow-50', 'bg-green-50', 'bg-purple-50', 'bg-orange-50', 'bg-cyan-50', 'bg-lime-50'
        ];
        function getColorClase(nombre) {
            let hash = 0;
            for (let i = 0; i < nombre.length; i++) hash = nombre.charCodeAt(i) + ((hash << 5) - hash);
            return coloresClases[Math.abs(hash) % coloresClases.length];
        }

        function mostrarHorarioEspacio(idEspacio, nombreEspacio) {
            // Buscar tipo de espacio desde la tarjeta
            let tipoEspacio = '';
            const card = document.querySelector(`[data-id='${idEspacio}']`);
            if(card) {
                const tipoSpan = card.querySelector('span.rounded-full');
                if(tipoSpan) tipoEspacio = tipoSpan.textContent;
            }
            document.getElementById('modalEspacioTitle').textContent = `Horario de ${idEspacio}`;
            document.getElementById('modalEspacioTipo').textContent = tipoEspacio;
            document.getElementById('horarioEspacioModal').classList.remove('hidden');
            document.body.classList.add('overflow-hidden');
            document.documentElement.classList.add('overflow-hidden');

            const horarios = horariosPorEspacio[idEspacio] || [];
            const tbody = document.getElementById('horarioEspacioBody');
            tbody.innerHTML = '';

            if (horarios.length === 0) {
                tbody.innerHTML = `<tr><td colspan='7' class='py-12 text-center'>
                    <div class='flex flex-col items-center justify-center gap-2'>
                        <i class='fa-solid fa-calendar-xmark text-5xl text-red-400 mb-2'></i>
                        <p class='text-xl font-bold text-gray-700'>Este espacio no tiene horario asignado.</p>
                        <p class='text-base text-gray-500'>No hay clases ni actividades programadas para este espacio.</p>
                    </div>
                </td></tr>`;
                return;
            }

            // Obtener módulos únicos y ordenarlos
                    const modulosUnicos = [...new Set(horarios.map(h => h.hora_inicio + '-' + h.hora_termino))]
                        .map(hora => {
                            const [hora_inicio, hora_termino] = hora.split('-');
                    return { hora_inicio, hora_termino };
                        })
                        .sort((a, b) => a.hora_inicio.localeCompare(b.hora_inicio));
                    const diasUnicos = ['lunes', 'martes', 'miércoles', 'jueves', 'viernes', 'sábado'];

                    modulosUnicos.forEach(modulo => {
                        const tr = document.createElement('tr');
                        // Celda de la hora
                        const tdHora = document.createElement('td');
                        tdHora.className = 'py-3 px-4 border-b text-center text-sm text-gray-600 leading-tight';
                tdHora.innerHTML = `<div class='flex flex-col items-center justify-center'><span class='font-semibold text-gray-800'>${formatearHora(modulo.hora_inicio)} a ${formatearHora(modulo.hora_termino)}</span></div>`;
                        tr.appendChild(tdHora);
                        diasUnicos.forEach(dia => {
                            const td = document.createElement('td');
                            td.className = 'py-3 px-4 border-b text-center align-middle';
                    const clases = horarios.filter(h => h.dia.toLowerCase() === dia && h.hora_inicio === modulo.hora_inicio && h.hora_termino === modulo.hora_termino);
                            if (clases.length > 0) {
                                td.innerHTML = clases.map(h => `
                            <div class='p-2 rounded-lg min-h-[90px] w-[150px] mx-auto flex flex-col items-center justify-center text-center break-words text-black font-semibold ${getColorClase(h.asignatura)} shadow-md'>
                                <div class='text-xs uppercase tracking-wide mb-1'>${h.asignatura}</div>
                                <div class='text-xs font-normal mb-1'><i class='fa-solid fa-user mr-1'></i>${h.user ? h.user.name : 'Sin profesor asignado'}</div>
                                </div>
                            `).join('');
                            } else {
                        td.innerHTML = `<div class='h-full min-h-[60px] flex items-center justify-center'><span class='text-sm text-gray-400'>-</span></div>`;
                            }
                            tr.appendChild(td);
                        });
                        tbody.appendChild(tr);
                    });
        }

        function formatearHora(hora) {
            // Convierte "08:10:00" a "8:10" y "09:00:00" a "9:00"
            if (!hora) return '';
            const [h, m] = hora.split(':');
            return `${parseInt(h)}:${m}`;
        }

        function cerrarModalEspacio() {
            document.getElementById('horarioEspacioModal').classList.add('hidden');
            document.body.classList.remove('overflow-hidden');
            document.documentElement.classList.remove('overflow-hidden');
        }
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            setTimeout(() => {
                const loading = document.getElementById('loadingIndicator');
                if (loading) loading.style.display = 'none';
            }, 500);
        });
    </script>
</x-app-layout>