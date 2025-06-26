<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
            <div class="flex items-center gap-3">
                <div class="bg-blue-100 p-2 rounded-full">
                    <i class="fa-solid fa-graduation-cap text-blue-600 text-2xl"></i>
                </div>
                <div>
                    <h2 class="text-2xl font-bold leading-tight text-gray-900">Horarios</h2>
                    <p class="text-gray-500 text-base">Directorio de profesores</p>
                </div>
            </div>
        </div>
    </x-slot>

    <div class="p-6 bg-gray-50 min-h-[80vh]">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6">
            <div class="flex-1 relative">
            <input type="text" name="search" id="search-profesor" value="{{ request('search') }}"
                    placeholder="Nombre o RUN" class="w-full px-4 py-2 border border-gray-300 rounded-lg bg-white focus:ring-2 focus:ring-blue-200 focus:border-blue-400 transition pr-10" />
                <span id="search-spinner" class="absolute right-3 top-1/2 -translate-y-1/2 hidden">
                    <svg class="animate-spin h-5 w-5 text-blue-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                    </svg>
                </span>
            </div>
            <!-- Aquí podrías agregar un filtro por área/facultad si lo deseas -->
        </div>

        <div id="profesores-lista">
            <p class="mb-4 text-gray-500 text-sm">{{ $profesores->total() }} profesores encontrados</p>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                @foreach ($profesores as $profesor)
                    <div class="bg-white border border-gray-200 rounded-xl p-5 flex flex-col gap-3 shadow-sm hover:shadow-md transition profesor-card cursor-pointer" data-run="{{ $profesor->run }}">
                        <div class="flex items-center gap-3">
                            <div class="bg-gray-100 rounded-full p-3 flex items-center justify-center">
                                <i class="fa-solid fa-user text-2xl text-gray-400"></i>
                            </div>
                            <div class="flex flex-col">
                                <span class="font-bold text-gray-900 uppercase text-base">{{ $profesor->name }}</span>
                            </div>
                        </div>
                        <div class="flex flex-wrap gap-2">
                            <!-- Etiquetas de área/facultad eliminadas -->
                        </div>
                        <button class="mt-2 flex items-center gap-2 px-3 py-1.5 bg-gray-100 hover:bg-blue-100 text-blue-700 rounded-lg font-semibold text-sm border border-blue-100 transition ver-horario-btn" type="button">
                            <i class="fa-solid fa-calendar-days"></i> Ver datos funcionario
                        </button>
                    </div>
                @endforeach
            </div>
            <div class="mt-6">
                {{ $profesores->appends(request()->query())->links() }}
            </div>
        </div>

        {{-- Modal --}}
        <div id="horarioModal"
            class="fixed inset-0 z-50 flex items-center justify-center hidden bg-black bg-opacity-50">
            <div class="w-full max-w-7xl mx-2 md:mx-8 bg-white rounded-lg shadow-lg overflow-hidden max-h-screen flex flex-col md:flex-row">
                <!-- Columna izquierda: Información personal -->
                <div class="bg-red-50 p-6 flex flex-col items-center justify-center md:w-1/5 w-full border-b md:border-b-0 md:border-r border-red-100">
                    <div class="bg-red-100 rounded-full p-4 mb-4">
                        <i class="fa-solid fa-user text-3xl text-red-600"></i>
                    </div>
                    <h3 class="text-base font-bold text-red-900 mb-2 text-center" id="modalNombreProfesor">Profesor</h3>
                    <p class="text-xs text-gray-700 mb-1 text-center" id="modalRunProfesor">RUN: </p>
                    <p class="text-xs text-gray-700 mb-1 text-center" id="modalCorreoProfesor">Correo: </p>
                </div>
                <!-- Columna derecha: Horario -->
                <div class="flex-1 flex flex-col bg-white">
                    <!-- Encabezado colorido -->
                    <div class="relative bg-red-700 p-8 flex items-center justify-between">
                        <h3 class="text-2xl font-bold text-white flex items-center gap-2" id="modalTitle">
                            <i class="fa-solid fa-calendar-days"></i> Horario del Profesor
                        </h3>
                        <button onclick="cerrarModal()" class="text-2xl font-bold text-white hover:text-gray-200 ml-2">&times;</button>
                        <!-- Círculos decorativos -->
                        <span class="absolute left-0 top-0 w-32 h-32 bg-white bg-opacity-10 rounded-full -translate-x-1/2 -translate-y-1/2 pointer-events-none"></span>
                        <span class="absolute right-0 top-0 w-32 h-32 bg-white bg-opacity-10 rounded-full translate-x-1/2 -translate-y-1/2 pointer-events-none"></span>
                    </div>
                    <div class="overflow-y-auto max-h-[70vh] p-6">
                        <table class="min-w-full bg-white rounded-lg overflow-hidden">
                            <thead class="sticky top-0 z-10 bg-gray-100 shadow">
                                <tr>
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
    </div>

    {{-- Scripts --}}
    <script>
        // Buscador por nombre o RUN con debounce rápido y spinner
        function debounce(fn, delay) {
            let timeout;
            return function(...args) {
                clearTimeout(timeout);
                timeout = setTimeout(() => fn.apply(this, args), delay);
            };
        }

        const searchInput = document.getElementById('search-profesor');
        const searchSpinner = document.getElementById('search-spinner');
        searchInput.addEventListener('input', debounce(function() {
            const search = this.value;
            const url = `{{ route('horarios.index') }}?search=${encodeURIComponent(search)}`;
            searchSpinner.classList.remove('hidden');
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
                    // Volver a activar los eventos de click en las tarjetas y botones
                    document.querySelectorAll('.profesor-card .ver-horario-btn').forEach(btn => {
                        btn.addEventListener('click', function(e) {
                            e.stopPropagation();
                            const run = this.closest('.profesor-card').dataset.run;
                            mostrarHorario(run);
                        });
                    });
                })
                .finally(() => {
                    searchSpinner.classList.add('hidden');
        });
        }, 100));

        // Colores pastel muy claros para bloques de clase
        const coloresClases = [
            'bg-pink-50', 'bg-blue-50', 'bg-yellow-50', 'bg-green-50', 'bg-purple-50', 'bg-orange-50', 'bg-cyan-50', 'bg-lime-50'
        ];
        function getColorClase(nombre) {
            let hash = 0;
            for (let i = 0; i < nombre.length; i++) hash = nombre.charCodeAt(i) + ((hash << 5) - hash);
            return coloresClases[Math.abs(hash) % coloresClases.length];
        }

        // Evento para abrir modal desde botón "Ver Horario"
        document.querySelectorAll('.profesor-card .ver-horario-btn').forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.stopPropagation();
                const run = this.closest('.profesor-card').dataset.run;
                mostrarHorario(run);
            });
        });

        function mostrarHorario(run) {
            fetch(`/horarios/${run}`)
                .then(response => response.json())
                .then(data => {
                    document.getElementById('modalNombreProfesor').textContent = data.horario.docente.name;
                    document.getElementById('modalRunProfesor').textContent = `RUN: ${data.horario.docente.run}`;
                    document.getElementById('modalCorreoProfesor').textContent = `Correo: ${data.horario.docente.email}`;
                    const horarioBody = document.getElementById('horarioBody');
                    horarioBody.innerHTML = '';

                    const modulosUnicos = [...new Set(data.modulos.map(m => m.id_modulo.split('.')[1]))].sort((a, b) =>
                        parseInt(a) - parseInt(b));

                    const diasUnicos = ['LU', 'MA', 'MI', 'JU', 'VI', 'SA'];

                    modulosUnicos.forEach(modulo => {
                        const tr = document.createElement('tr');
                        tr.className = 'hover:bg-gray-50';

                        const moduloInfo = data.modulos.find(m => m.id_modulo.split('.')[1] === modulo);
                        const horaInicio = moduloInfo.hora_inicio.substring(0, 5);
                        const horaTermino = moduloInfo.hora_termino.substring(0, 5);
                        const hora = `${horaInicio} a ${horaTermino}`;

                        const tdHora = document.createElement('td');
                        tdHora.className = 'py-3 px-4 border-b text-center text-sm text-gray-600 leading-tight';
                        tdHora.innerHTML = `<div class='flex flex-col items-center justify-center'><span class='font-semibold text-gray-800'>${hora}</span></div>`;
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
                                    <div class='p-2 rounded-lg min-h-[90px] w-[120px] max-w-[120px] mx-auto flex flex-col items-center justify-center text-center break-words text-black font-semibold ${getColorClase(plan.asignatura.nombre_asignatura)} shadow-md'>
                                        <div class='text-xs uppercase tracking-wide mb-1'>${plan.asignatura.nombre_asignatura}</div>
                                        <div class='text-xs font-normal mb-1'><i class='fa-solid fa-door-closed mr-1'></i>${plan.espacio.id_espacio}</div>
                                        <div class='text-xs font-normal mb-1'><i class='fa-solid fa-hashtag mr-1'></i>${plan.asignatura.codigo_asignatura}</div>
</div>
`).join('');
                                td.innerHTML = clasesHTML;
                            } else {
                                td.innerHTML = `<div class='h-full min-h-[60px] flex items-center justify-center'><span class='text-sm text-gray-400'>-</span></div>`;
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
            document.body.classList.add('overflow-hidden');
            document.documentElement.classList.add('overflow-hidden');
        }

        function cerrarModal() {
            document.getElementById('horarioModal').classList.add('hidden');
            document.body.classList.remove('overflow-hidden');
            document.documentElement.classList.remove('overflow-hidden');
        }
    </script>
</x-app-layout>
