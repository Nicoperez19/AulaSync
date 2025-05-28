<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <h2 class="text-xl font-semibold leading-tight" style="font-style: oblique;">
                {{ __('Horarios') }}
            </h2>
        </div>
    </x-slot>

    <div class="p-6 bg-white rounded-lg shadow-lg">
        <div class="w-1/3 mb-4">
            <input type="text" name="search" id="search-profesor" value="{{ request('search') }}"
                placeholder="Nombre o RUN" class="w-full px-4 py-2 border rounded dark:bg-gray-700 dark:text-white">
        </div>

        <div id="profesores-lista">
            <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3">
                @foreach ($profesores as $profesor)
                    <div class="p-4 transition-shadow rounded-lg shadow cursor-pointer profesor-card bg-gray-50 hover:shadow-md"
                        data-run="{{ $profesor->run }}">
                        <h4 class="font-medium text-gray-900">{{ $profesor->name }}</h4>
                        <p class="text-sm text-gray-600">RUN: {{ $profesor->run }}</p>
                    </div>
                @endforeach
            </div>
            <div class="mt-4">
                {{ $profesores->appends(request()->query())->links() }}
            </div>
        </div>

        {{-- Modal --}}
        <div id="horarioModal"
            class="fixed inset-0 z-50 flex items-center justify-center hidden bg-black bg-opacity-50">
            <div class="w-full p-4 bg-white rounded-lg shadow-lg max-w-7xl md:p-4">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold" id="modalTitle">Horario del Profesor</h3>
                    <button onclick="cerrarModal()" class="text-xl font-bold text-red-600">&times;</button>
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
                        <tbody id="horarioBody">
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- Scripts --}}
    <script>
        document.getElementById('search-profesor').addEventListener('input', function() {
            const search = this.value;
            const url = `{{ route('horarios.index') }}?search=${encodeURIComponent(search)}`;
            fetch(url, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => response.text())
                .then(html => {
                    // Extraer solo el contenido de la lista de profesores
                    const parser = new DOMParser();
                    const doc = parser.parseFromString(html, 'text/html');
                    const newList = doc.getElementById('profesores-lista');
                    document.getElementById('profesores-lista').innerHTML = newList.innerHTML;
                    // Volver a activar los eventos de click en las tarjetas
                    document.querySelectorAll('.profesor-card').forEach(card => {
                        card.addEventListener('click', () => {
                            const run = card.dataset.run;
                            mostrarHorario(run);
                        });
                    });
                });
        });

        document.querySelectorAll('.profesor-card').forEach(card => {
            card.addEventListener('click', () => {
                const run = card.dataset.run;
                mostrarHorario(run);
            });
        });

        function mostrarHorario(run) {
            fetch(`/horarios/${run}`)
                .then(response => response.json())
                .then(data => {
                    document.getElementById('modalTitle').textContent = `Horario de ${data.horario.docente.name}`;
                    const horarioBody = document.getElementById('horarioBody');
                    horarioBody.innerHTML = '';

                    const modulosUnicos = [...new Set(data.modulos.map(m => m.id_modulo.split('.')[1]))].sort((a, b) =>
                        parseInt(a) - parseInt(b));

                    const diasUnicos = ['LU', 'MA', 'MI', 'JU', 'VI', 'SA'];

                    modulosUnicos.forEach(modulo => {
                        const tr = document.createElement('tr');
                        tr.className = 'hover:bg-gray-50';

                        const moduloInfo = data.modulos.find(m => m.id_modulo.split('.')[1] === modulo);
                        const hora = `${moduloInfo.hora_inicio} - ${moduloInfo.hora_termino}`;

                        const tdHora = document.createElement('td');
                        tdHora.className = 'py-3 px-4 border-b text-center text-sm text-gray-600 leading-tight';
                        tdHora.innerHTML = `<div class="flex flex-col items-center justify-center">
                        <span class="font-semibold text-gray-800">Módulo ${modulo}</span>
    <span class="text-xs">${hora}</span>
</div>`;
                        tr.appendChild(tdHora);

                        diasUnicos.forEach(dia => {
                            const td = document.createElement('td');
                            td.className = 'py-3 px-4 border-b text-center align-middle';
                            const planificaciones = data.horario.planificaciones.filter(plan => {
                                const [planDia, planModulo] = plan.id_modulo.split('.');
                                return planDia === dia && planModulo === modulo;
                            });

                            if (planificaciones.length > 0) {
                                const clasesHTML = planificaciones.map(plan => `
<div class="bg-blue-100 p-2 rounded-lg min-h-[90px] w-[120px] mx-auto flex flex-col items-center justify-center text-center break-words">
    <p class="text-sm font-medium text-blue-900 break-words">
        ${plan.asignatura.codigo_asignatura}
    </p>
    <p class="text-xs text-blue-700 break-words">
        Sala: ${plan.espacio.id_espacio}
    </p>
    <p class="text-sm font-medium text-blue-900 break-words">
        ${plan.asignatura.nombre_asignatura}
    </p>
</div>
`).join('');



                                td.innerHTML = clasesHTML;
                            } else {
                                td.innerHTML = `
                                <div class="h-full min-h-[60px] flex items-center justify-center">
                                    <span class="text-sm text-gray-400">-</span>
                                </div>`;
                            }

                            tr.appendChild(td);
                        });

                        horarioBody.appendChild(tr);
                    });

                    abrirModal();
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error al cargar el horario');
                });
        }

        function abrirModal() {
            document.getElementById('horarioModal').classList.remove('hidden');
        }

        function cerrarModal() {
            document.getElementById('horarioModal').classList.add('hidden');
        }
    </script>
</x-app-layout>
