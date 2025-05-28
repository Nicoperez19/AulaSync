<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <h2 class="text-xl font-semibold leading-tight" style="font-style: oblique;">
                {{ __('Horarios por Espacios') }}
            </h2>
        </div>
    </x-slot>

    <div class="p-6 space-y-6" x-data="{ selectedPiso: {{ $pisos->first()->id ?? 1 }} }">
        <!-- Nav Pills de Pisos -->
        <div class="w-full">
            <div class="bg-white shadow-md dark:bg-dark-eval-0 rounded-t-xl">
                <ul class="flex border-b border-gray-300 dark:border-gray-700" role="tablist">
                    @foreach ($pisos as $piso)
                        <li role="presentation">
                            <button type="button"
                                @click="selectedPiso = {{ $piso->id }}"
                                class="px-10 py-4 text-lg font-semibold transition-all duration-300 border border-b-0 rounded-t-xl focus:outline-none"
                                :class="selectedPiso == {{ $piso->id }} 
                                    ? 'bg-light-cloud-blue text-white border-light-cloud-blue'
                                    : 'bg-white text-gray-700 border-gray-300 hover:bg-gray-100 hover:text-light-cloud-blue'">
                                Piso {{ $piso->numero_piso }}
                            </button>
                        </li>
                    @endforeach
                </ul>
                <!-- Cards de espacios por piso -->
                <div class="p-6 bg-white shadow-md rounded-b-xl dark:bg-gray-800">
                    @foreach ($pisos as $piso)
                        <div x-show="selectedPiso == {{ $piso->id }}"
                             x-transition:enter="transition ease-out duration-300"
                             x-transition:enter-start="opacity-0 scale-95"
                             x-transition:enter-end="opacity-100 scale-100"
                             x-transition:leave="transition ease-in duration-200"
                             x-transition:leave-start="opacity-100 scale-100"
                             x-transition:leave-end="opacity-0 scale-95">
                            <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3">
                                @foreach ($piso->espacios as $espacio)
                                    <div class="p-4 transition-shadow rounded-lg shadow cursor-pointer espacio-card bg-gray-50 hover:shadow-md"
                                        data-id="{{ $espacio->id_espacio }}"
                                        data-nombre="{{ $espacio->nombre_espacio }}"
                                        @click="mostrarHorarioEspacio('{{ $espacio->id_espacio }}', '{{ $espacio->nombre_espacio }}')">
                                        <h4 class="font-medium text-gray-900"> {{ $espacio->id_espacio }},
                                            {{ $espacio->nombre_espacio }}</h4>
                                        <p class="text-sm text-gray-600">Tipo: {{ $espacio->tipo_espacio }}</p>
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
        class="fixed inset-0 z-50 flex items-center justify-center hidden bg-black bg-opacity-50">
        <div class="w-full p-4 bg-white rounded-lg shadow-lg max-w-7xl md:p-4">
            <div class="flex items-center justify-between mb-4">
                <h1 id="modalEspacioTitle" class="font-sans text-lg font-semibold text-gray-900 dark:text-white"></h1>
                <button onclick="cerrarModalEspacio()" class="text-xl font-bold text-red-600">&times;</button>
            </div>
            <div class="overflow-y-auto max-h-[70vh]">
                <table class="min-w-full bg-white border border-gray-200">
                    <thead class="sticky top-0 z-10 bg-white shadow">
                        <tr class="bg-gray-100">
                            <th class="px-4 py-3 font-semibold text-center border-b">Hora</th>
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

    <script>
        function mostrarHorarioEspacio(idEspacio, nombreEspacio) {
            document.getElementById('modalEspacioTitle').textContent = `Horario de ${idEspacio}, ${nombreEspacio}`;
            fetch(`/horarios-espacios?id_espacio=${idEspacio}`)
                .then(res => res.json())
                .then(data => {
                    const horarios = data.horarios[idEspacio] || [];
                    // Obtener todos los módulos únicos y ordenarlos por hora_inicio
                    const modulosUnicos = [...new Set(horarios.map(h => h.hora_inicio + '-' + h.hora_termino))]
                        .map(hora => {
                            const [hora_inicio, hora_termino] = hora.split('-');
                            return {
                                hora_inicio,
                                hora_termino
                            };
                        })
                        .sort((a, b) => a.hora_inicio.localeCompare(b.hora_inicio));

                    const diasUnicos = ['lunes', 'martes', 'miércoles', 'jueves', 'viernes', 'sábado'];

                    const tbody = document.getElementById('horarioEspacioBody');
                    tbody.innerHTML = '';

                    modulosUnicos.forEach(modulo => {
                        const tr = document.createElement('tr');
                        tr.className = 'hover:bg-gray-50';

                        // Celda de la hora
                        const tdHora = document.createElement('td');
                        tdHora.className = 'py-3 px-4 border-b text-center text-sm text-gray-600 leading-tight';
                        tdHora.innerHTML = `<div class="flex flex-col items-center justify-center">
                        <span class="font-semibold text-gray-800">${modulo.hora_inicio} - ${modulo.hora_termino}</span>
                    </div>`;
                        tr.appendChild(tdHora);

                        diasUnicos.forEach(dia => {
                            const td = document.createElement('td');
                            td.className = 'py-3 px-4 border-b text-center align-middle';
                            const clases = horarios.filter(h =>
                                h.dia.toLowerCase() === dia &&
                                h.hora_inicio === modulo.hora_inicio &&
                                h.hora_termino === modulo.hora_termino
                            );
                            if (clases.length > 0) {
                                td.innerHTML = clases.map(h => `
                                <div class="bg-blue-100 p-2 rounded-lg min-h-[90px] w-[120px] mx-auto flex flex-col items-center justify-center text-center break-words">
                                    <p class="text-sm font-medium text-blue-900 break-words">${h.asignatura}</p>
                                    <p class="text-xs text-blue-700 break-words">${h.profesor}</p>
                                </div>
                            `).join('');
                            } else {
                                td.innerHTML = `<div class="h-full min-h-[60px] flex items-center justify-center">
                                <span class="text-sm text-gray-400">-</span>
                            </div>`;
                            }
                            tr.appendChild(td);
                        });

                        tbody.appendChild(tr);
                    });

                    document.getElementById('horarioEspacioModal').classList.remove('hidden');
                });
        }

        function cerrarModalEspacio() {
            document.getElementById('horarioEspacioModal').classList.add('hidden');
        }
    </script>
</x-app-layout>
