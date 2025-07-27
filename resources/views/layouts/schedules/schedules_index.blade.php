<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
            <div class="flex items-center gap-3">
                <div class=" p-2 rounded-xl bg-light-cloud-blue">
                    <i class="fa-solid fa-graduation-cap text-white text-2xl"></i>
                </div>
                <div>
                    <h2 class="text-2xl font-bold leading-tight text-black">Horarios</h2>
                    <p class="text-gray-500 text-base">Directorio de profesores</p>
                </div>
            </div>
        </div>
    </x-slot>

    <div class="p-6 min-h-[80vh]">
        <!-- Tarjeta de filtros -->
        <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
            <form id="filtro-letra-form" method="GET" action="" class="flex flex-col gap-4" onsubmit="return false;">
                <div class="flex flex-col sm:flex-row sm:items-center gap-2 w-full">
                    <div class="flex-1 flex items-center gap-2">
                        <div class="relative w-full">
                            <input type="text" name="search" id="search-profesor" value="{{ request('search') }}"
                                placeholder="Buscar por nombre, apellido o RUN..." class="w-full px-4 py-2 border border-gray-300 rounded-lg bg-white focus:ring-2 focus:ring-light-cloud-blue/30 focus:border-light-cloud-blue transition pr-10" />
                        </div>
                        <button id="buscar-btn" type="button" class="px-4 py-2 bg-light-cloud-blue text-white rounded font-semibold text-sm hover:bg-[#b10718] transition">Buscar</button>
                    </div>
                </div>
                
                <!-- Filtros por período -->
                <div class="flex flex-col sm:flex-row sm:items-center gap-4 w-full">
                    <div class="flex items-center gap-2">
                        <span class="font-semibold text-light-cloud-blue">Año:</span>
                        <select name="anio" id="anio-filtro" class="px-3 py-2 border border-gray-300 rounded-lg bg-white focus:ring-2 focus:ring-light-cloud-blue/30 focus:border-light-cloud-blue transition">
                            <option value="">Todos los años</option>
                            @foreach($aniosDisponibles ?? [] as $anio)
                                <option value="{{ $anio }}" {{ request('anio') == $anio ? 'selected' : '' }}>{{ $anio }}</option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div class="flex items-center gap-2">
                        <span class="font-semibold text-light-cloud-blue">Semestre:</span>
                        <select name="semestre" id="semestre-filtro" class="px-3 py-2 border border-gray-300 rounded-lg bg-white focus:ring-2 focus:ring-light-cloud-blue/30 focus:border-light-cloud-blue transition">
                            <option value="">Todos los semestres</option>
                            @foreach($semestresDisponibles ?? [] as $semestre)
                                <option value="{{ $semestre }}" {{ request('semestre') == $semestre ? 'selected' : '' }}>{{ $semestre }}er Semestre</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                
                <div class="flex flex-col sm:flex-row sm:items-center gap-2 w-full">
                    <span class="font-semibold text-light-cloud-blue mr-2">Usuario:</span>
                    <div class="flex flex-wrap gap-1 items-center">
                        @php
                            $letras = ['Todos','A','B','C','D','E','F','G','H','I','J','K','L','M','N','Ñ','O','P','Q','R','S','T','U','V','W','X','Y','Z'];
                            $letraSeleccionada = request('letra', 'Todos');
                        @endphp
                        @foreach($letras as $letra)
                            <button type="button"
                                class="{{ $letra === 'Todos' ? 'w-16' : 'w-8' }} h-8 flex items-center justify-center rounded border text-xs font-bold uppercase focus:outline-none letra-filtro-btn transition
                                {{ $letraSeleccionada == $letra ? 'bg-light-cloud-blue text-white' : 'bg-white text-light-cloud-blue  hover:bg-light-cloud-blue/10' }}"
                                data-letra="{{ $letra }}">{{ $letra }}</button>
                        @endforeach
                    </div>
                    <input type="hidden" name="letra" id="letra-filtro-input" value="{{ $letraSeleccionada }}">
                    <button id="aplicar-filtro-btn" type="button" class="ml-2 px-4 py-2 bg-light-cloud-blue text-white rounded font-semibold text-sm hover:bg-[#b10718] transition">Aplicar filtro</button>
                </div>
            </form>
        </div>
        <!-- Fin tarjeta de filtros -->

        <div id="profesores-lista">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between mb-4">
                <p class="text-gray-500 text-sm">{{ $profesores->total() }} profesores encontrados</p>
                @php
                    $anioActual = \App\Helpers\SemesterHelper::getCurrentAcademicYear();
                    $semestre = \App\Helpers\SemesterHelper::getCurrentSemester();
                    $periodoActual = \App\Helpers\SemesterHelper::getCurrentPeriod();
                @endphp
                <p class="text-sm text-light-cloud-blue font-semibold">
                    <i class="fa-solid fa-calendar-days mr-1"></i>
                    Período actual: {{ $semestre }}er Semestre {{ $anioActual }}
                </p>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-6">
                @foreach ($profesores as $profesor)
                    <div class="bg-white border border-gray-200 rounded-xl p-5 flex flex-col gap-3 shadow-sm hover:shadow-md transition profesor-card cursor-pointer" data-run="{{ $profesor->run_profesor }}">
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
                        <button class="mt-2 flex items-center gap-2 px-3 py-1.5 border border-light-cloud-blue text-light-cloud-blue bg-white hover:bg-light-cloud-blue/10 rounded-lg font-semibold text-sm transition ver-horario-btn" type="button">
                            <i class="fa-solid fa-calendar-days !text-light-cloud-blue"></i> Ver datos funcionario
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
                    <h3 class="text-base font-bold text-black mb-2 text-center" id="modalNombreProfesor">Profesor</h3>
                    <p class="text-xs text-gray-700 mb-1 text-center" id="modalCorreoProfesor">Correo: </p>
                    <p class="text-xs text-gray-600 mb-1 text-center" id="modalPeriodo">
                        <i class="fa-solid fa-calendar-days mr-1"></i>
                        Período: {{ $semestre }}er Semestre {{ $anioActual }}
                    </p>
                </div>
                <!-- Columna derecha: Horario -->
                <div class="flex-1 flex flex-col bg-white">
                    <!-- Encabezado colorido -->
                    <div class="relative bg-red-700 p-8 flex items-center justify-between">
                        <h3 class="text-2xl font-bold text-white flex items-center gap-2" id="modalTitle">
                            <i class="fa-solid fa-calendar-days text-white"></i> Horario del Profesor
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

        <!-- Spinner -->
        <div id="profesores-spinner" class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-10 z-50 hidden">
            <div class="flex flex-col items-center">
                <svg class="animate-spin h-10 w-10 text-black mb-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                </svg>
                <span class="text-black text-sm font-semibold">Cargando...</span>
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
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    return response.text();
                })
                .then(html => {
                    // Extraer solo el contenido de la lista de profesores
                    const parser = new DOMParser();
                    const doc = parser.parseFromString(html, 'text/html');
                    const newList = doc.getElementById('profesores-lista');
                    if (newList) {
                        document.getElementById('profesores-lista').innerHTML = newList.innerHTML;
                        // Volver a activar los eventos de click en las tarjetas y botones
                        document.querySelectorAll('.profesor-card .ver-horario-btn').forEach(btn => {
                            btn.addEventListener('click', function(e) {
                                e.stopPropagation();
                                const run = this.closest('.profesor-card').dataset.run;
                                mostrarHorario(run);
                            });
                        });
                    } else {
                        console.error('No se encontró el elemento profesores-lista en la respuesta');
                        document.getElementById('profesores-lista').innerHTML = '<div class="text-center py-8 text-red-500">Error al cargar los datos</div>';
                    }
                })
                .catch(error => {
                    console.error('Error en la búsqueda:', error);
                    document.getElementById('profesores-lista').innerHTML = '<div class="text-center py-8 text-red-500">Error al cargar los datos: ' + error.message + '</div>';
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
            // Mostrar el modal y el spinner inmediatamente
            abrirModal();
            const horarioBody = document.getElementById('horarioBody');
            horarioBody.innerHTML = "<tr><td colspan='7' class='text-center py-8'><span class='text-gray-400'>Cargando horario...</span></td></tr>";
            document.getElementById('modalNombreProfesor').textContent = 'Cargando...';
            document.getElementById('modalCorreoProfesor').textContent = '';

            // Obtener los filtros actuales
            const anioFiltro = document.getElementById('anio-filtro').value;
            const semestreFiltro = document.getElementById('semestre-filtro').value;
            
            // Construir la URL con los parámetros de filtro
            let url = `/horarios/${run}`;
            const params = new URLSearchParams();
            if (anioFiltro) params.append('anio', anioFiltro);
            if (semestreFiltro) params.append('semestre', semestreFiltro);
            if (params.toString()) {
                url += '?' + params.toString();
            }

            fetch(url)
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    return response.json();
                })
                .then(data => {
                    if (!data.horario || !data.horario.profesor) {
                        throw new Error('No se encontró el horario para el período seleccionado');
                    }
                    document.getElementById('modalNombreProfesor').textContent = data.horario.profesor.name;
                    document.getElementById('modalCorreoProfesor').textContent = `Correo: ${data.horario.profesor.email}`;
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
                })
                .catch(error => {
                    console.error('Error:', error);
                    document.getElementById('modalNombreProfesor').textContent = 'Error';
                    document.getElementById('modalCorreoProfesor').textContent = '';
                    horarioBody.innerHTML = "<tr><td colspan='7' class='text-center py-8 text-red-500'>Error al cargar el horario: " + error.message + "</td></tr>";
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

        // Manejo visual de selección de letra
        const letraBtns = document.querySelectorAll('.letra-filtro-btn');
        const letraInput = document.getElementById('letra-filtro-input');
        letraBtns.forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                letraBtns.forEach(b => b.classList.remove('bg-light-cloud-blue', 'text-white'));
                letraBtns.forEach(b => b.classList.add('bg-white', 'text-light-cloud-blue'));
                this.classList.remove('bg-white', 'text-light-cloud-blue');
                this.classList.add('bg-light-cloud-blue', 'text-white');
                letraInput.value = this.dataset.letra;
            });
        });

        // Aplicar filtro por AJAX
        const aplicarFiltroBtn = document.getElementById('aplicar-filtro-btn');
        const filtroForm = document.getElementById('filtro-letra-form');
        const profesoresLista = document.getElementById('profesores-lista');
        const profesoresSpinner = document.getElementById('profesores-spinner');

        function mostrarSpinner() {
            profesoresSpinner.classList.remove('hidden');
        }
        function ocultarSpinner() {
            profesoresSpinner.classList.add('hidden');
        }

        function aplicarFiltros() {
            const formData = new FormData(filtroForm);
            const params = new URLSearchParams(formData).toString();
            mostrarSpinner();
            fetch(`?${params}`, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.text();
            })
            .then(html => {
                const parser = new DOMParser();
                const doc = parser.parseFromString(html, 'text/html');
                const newList = doc.getElementById('profesores-lista');
                if (newList) {
                    profesoresLista.innerHTML = newList.innerHTML;
                    activarClickCard();
                } else {
                    console.error('No se encontró el elemento profesores-lista en la respuesta');
                    profesoresLista.innerHTML = '<div class="text-center py-8 text-red-500">Error al cargar los datos</div>';
                }
            })
            .catch(error => {
                console.error('Error al aplicar filtros:', error);
                profesoresLista.innerHTML = '<div class="text-center py-8 text-red-500">Error al cargar los datos: ' + error.message + '</div>';
            })
            .finally(() => ocultarSpinner());
        }

        aplicarFiltroBtn.addEventListener('click', aplicarFiltros);

        // Buscar por AJAX
        const buscarBtn = document.getElementById('buscar-btn');
        buscarBtn.addEventListener('click', aplicarFiltros);

        // Aplicar filtros automáticamente cuando cambien los selectores de año y semestre
        const anioFiltro = document.getElementById('anio-filtro');
        const semestreFiltro = document.getElementById('semestre-filtro');
        
        anioFiltro.addEventListener('change', aplicarFiltros);
        semestreFiltro.addEventListener('change', aplicarFiltros);

        // Permitir que al hacer clic en la tarjeta completa se active el modal
        function activarClickCard() {
            document.querySelectorAll('.profesor-card').forEach(card => {
                card.addEventListener('click', function(e) {
                    // Evitar doble apertura si se hace click en el botón interno
                    if (e.target.closest('.ver-horario-btn')) return;
                    const run = this.dataset.run;
                    mostrarHorario(run);
                });
            });
            // Seguir permitiendo el click en el botón
            document.querySelectorAll('.profesor-card .ver-horario-btn').forEach(btn => {
                btn.addEventListener('click', function(e) {
                    e.stopPropagation();
                    const run = this.closest('.profesor-card').dataset.run;
                    mostrarHorario(run);
                });
            });
        }

        // Llamar a la función al cargar y después de AJAX
        activarClickCard();
    </script>
</x-app-layout>
