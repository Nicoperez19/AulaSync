<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
            <div class="flex items-center gap-3">
                <div class="p-2 rounded-xl bg-light-cloud-blue">
                    <i class="text-2xl text-white fa-solid fa-graduation-cap"></i>
                </div>
                <div>
                    <h2 class="text-2xl font-bold leading-tight text-black">Horarios</h2>
                    <p class="text-base text-gray-500">Directorio de profesores</p>
                </div>
            </div>
        </div>
    </x-slot>

    <div class="px-6 min-h-[80vh]">
        <!-- Tarjeta de filtros -->
        <div class="bg-white p-5 rounded-xl border border-gray-200 shadow-sm mb-6 dark:bg-gray-800 dark:border-gray-700">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-sm font-bold text-gray-700 uppercase tracking-wider flex items-center gap-2 dark:text-gray-200">
                    <i class="fas fa-filter text-[#950101]"></i> FILTROS
                </h3>
            </div>

            <form id="filtro-letra-form" method="GET" action="" class="flex flex-col gap-4" onsubmit="return false;">
                <!-- Fila 1: Filtros principales (Año, Semestre y Búsqueda) -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-4 items-end">
                    <!-- Año -->
                    <div class="sm:col-span-1 lg:col-span-2">
                        <label class="block text-xs font-semibold text-gray-700 uppercase mb-1 dark:text-gray-300">Año</label>
                        <div class="w-full px-3 py-2 text-sm font-semibold text-center text-gray-700 border border-gray-300 rounded-lg bg-gray-50 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                            {{ \App\Helpers\SemesterHelper::getCurrentAcademicYear() }}
                        </div>
                    </div>

                    <!-- Semestre -->
                    <div class="sm:col-span-1 lg:col-span-3">
                        <label class="block text-xs font-semibold text-gray-700 uppercase mb-1 dark:text-gray-300">Semestre</label>
                        <select name="semestre" id="semestre-filtro"
                            class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-[#950101]/20 focus:border-[#950101] bg-white dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                            @foreach($semestresDisponibles ?? [] as $semestre)
                                <option value="{{ $semestre }}" {{ request('semestre', \App\Helpers\SemesterHelper::getCurrentSemester()) == $semestre ? 'selected' : '' }}>
                                    {{ $semestre }}° Semestre
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Búsqueda rápida por texto -->
                    <div class="col-span-1 sm:col-span-2 lg:col-span-7">
                        <label class="block text-xs font-semibold text-gray-700 uppercase mb-1 dark:text-gray-300">Buscar profesor</label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-gray-400">
                                <i class="fas fa-search text-xs"></i>
                            </span>
                            <input type="text" name="search" id="search-profesor" value="{{ request('search') }}"
                                placeholder="Buscar por nombre, apellido o RUN..."
                                class="w-full pl-9 pr-4 py-2 text-sm border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-[#950101]/20 focus:border-[#950101] bg-white dark:bg-gray-700 dark:border-gray-600 dark:text-white placeholder:text-gray-400" />
                        </div>
                    </div>
                </div>


                <!-- Botones de Acción separados por línea -->
                <div class="flex items-center justify-end gap-2 mt-2 pt-4 border-t border-gray-100 dark:border-gray-700">
                    <button id="limpiar-filtro-btn" type="button"
                        class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 border border-gray-300 rounded-lg transition-colors inline-flex items-center gap-2 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200 dark:hover:bg-gray-600 cursor-pointer">
                        <i class="fas fa-undo text-xs"></i> Limpiar
                    </button>
                    <button id="aplicar-filtro-btn" type="button"
                        class="px-4 py-2 text-sm font-semibold text-white bg-[#191919] hover:bg-black active:bg-gray-900 rounded-lg shadow-sm transition-colors inline-flex items-center gap-2 cursor-pointer">
                        <i class="fas fa-filter text-xs"></i> Aplicar filtros
                    </button>
                </div>
            </form>
        </div>
        <!-- Fin tarjeta de filtros -->

        <div id="profesores-lista">
            @php
                $anioActual = \App\Helpers\SemesterHelper::getCurrentAcademicYear();
                $semestre = \App\Helpers\SemesterHelper::getCurrentSemester();
                $periodoActual = \App\Helpers\SemesterHelper::getCurrentPeriod();

                // Usar el año actual y el semestre seleccionado o el actual
                $anioMostrar = $anioActual;
                $semestreMostrar = request('semestre', $semestre);
            @endphp
            <div class="mb-4">
                <p class="text-sm text-gray-500">{{ $profesores->total() }} profesores encontrados</p>
            </div>
            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 2xl:grid-cols-6">
                @foreach ($profesores as $profesor)
                                    <div class="flex flex-col gap-3 p-5 transition-all duration-300 ease-in-out bg-white border border-gray-200 shadow-sm cursor-pointer rounded-xl hover:shadow-2xl hover:scale-105 hover:-translate-y-2 hover:border-light-cloud-blue profesor-card"
                        data-run="{{ $profesor->run_profesor }}">
                        <div class="flex items-center gap-3">
                            <div class="flex items-center justify-center p-3 bg-gray-100 rounded-full">
                                <i class="text-2xl text-gray-400 fa-solid fa-user"></i>
                            </div>
                            <div class="flex flex-col">
                                <span class="text-base font-bold text-gray-900 uppercase">{{ $profesor->name }}</span>
                            </div>
                        </div>


                    </div>
                @endforeach
            </div>
            <div class="mt-6">
                {{ $profesores->appends(request()->query())->links() }}
            </div>
        </div>

        {{-- Modal --}}
        <div id="horarioModal"
            class="fixed inset-0 z-[150] flex items-center justify-center hidden bg-black bg-opacity-50">
            <div
                class="flex flex-col w-full max-h-screen mx-2 overflow-hidden bg-white rounded-lg shadow-lg max-w-7xl md:mx-8 md:flex-row">
                <!-- Columna izquierda: Información personal -->
                <div
                    class="flex flex-col items-center justify-center w-full p-6 border-b border-red-100 bg-red-50 md:w-1/5 md:border-b-0 md:border-r">
                    <div class="p-4 mb-4 bg-red-100 rounded-full">
                        <i class="text-3xl text-red-600 fa-solid fa-user"></i>
                    </div>
                    <h3 class="mb-2 text-base font-bold text-center text-black" id="modalNombreProfesor">Profesor</h3>
                    <p class="mb-1 text-xs text-center text-gray-700" id="modalCorreoProfesor">Correo: </p>
                    <p class="mb-1 text-xs text-center text-gray-600" id="modalPeriodo">
                        <i class="mr-1 fa-solid fa-calendar-days"></i>
                        Período: <span id="modalSemestre">{{ $semestreMostrar }}</span>° Semestre <span
                            id="modalAnio">{{ $anioMostrar }}</span>
                    </p>
                </div>
                <!-- Columna derecha: Horario -->
                <div class="flex flex-col flex-1 bg-white">
                    <!-- Encabezado colorido -->
                    <div class="relative flex items-center justify-between p-8 bg-red-700">
                        <h3 class="flex items-center gap-2 text-2xl font-bold text-white" id="modalTitle">
                            <i class="text-white fa-solid fa-calendar-days"></i> Horario del Profesor
                        </h3>
                        <button onclick="cerrarModal()"
                            class="ml-2 text-2xl font-bold text-white hover:text-gray-200">&times;</button>
                        <!-- Círculos decorativos -->
                        <span
                            class="absolute top-0 left-0 w-32 h-32 -translate-x-1/2 -translate-y-1/2 bg-white rounded-full pointer-events-none bg-opacity-10"></span>
                        <span
                            class="absolute top-0 right-0 w-32 h-32 translate-x-1/2 -translate-y-1/2 bg-white rounded-full pointer-events-none bg-opacity-10"></span>
                    </div>
                    <div class="overflow-y-auto max-h-[70vh] p-6">
                        <table class="min-w-full overflow-hidden bg-white rounded-lg">
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
        <div id="profesores-spinner"
            class="fixed inset-0 z-50 flex items-center justify-center hidden bg-black bg-opacity-10">
            <div class="flex flex-col items-center">
                <svg class="w-10 h-10 mb-2 text-black animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none"
                    viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                </svg>
                <span class="text-sm font-semibold text-black">Cargando...</span>
            </div>
        </div>
    </div>

    {{-- Scripts --}}
    <script>
        // Año académico actual desde el backend
        const currentYear = "{{ \App\Helpers\SemesterHelper::getCurrentAcademicYear() }}";

        // Buscador por nombre o RUN con debounce rápido y spinner
        function debounce(fn, delay) {
            let timeout;
            return function (...args) {
                clearTimeout(timeout);
                timeout = setTimeout(() => fn.apply(this, args), delay);
            };
        }


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
            btn.addEventListener('click', function (e) {
                e.stopPropagation();
                const run = this.closest('.profesor-card').dataset.run;
                mostrarHorario(run);
            });
        });

        function mostrarHorario(run) {
            // Mostrar el modal y el spinner inmediatamente
            abrirModal();
            const horarioBody = document.getElementById('horarioBody');
            horarioBody.innerHTML = "<tr><td colspan='7' class='py-8 text-center'><span class='text-gray-400'>Cargando horario...</span></td></tr>";
            document.getElementById('modalNombreProfesor').textContent = 'Cargando...';
            document.getElementById('modalCorreoProfesor').textContent = '';

            // Obtener el filtro de semestre actual
            const semestreFiltro = document.getElementById('semestre-filtro').value;

            // Construir la URL con el año actual y el semestre seleccionado
            let url = `/horarios/${run}`;
            const params = new URLSearchParams();
            // Usar el año actual configurado
            params.append('anio', currentYear);
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
                    console.log('Datos recibidos:', {
                        horario: data.horario,
                        asignaturas: data.asignaturas,
                        periodo: data.periodo,
                        total_planificaciones: data.horario ? data.horario.planificaciones.length : 0
                    });

                    // Verificar si hay un mensaje de error
                    if (data.mensaje) {
                        document.getElementById('modalNombreProfesor').textContent = data.profesor.name;
                        document.getElementById('modalCorreoProfesor').textContent = `Correo: ${data.profesor.email}`;
                        // Actualizar el período mostrado en el modal
                        document.getElementById('modalSemestre').textContent = semestreFiltro || '{{ \App\Helpers\SemesterHelper::getCurrentSemester() }}';
                        document.getElementById('modalAnio').textContent = currentYear;
                        const horarioBody = document.getElementById('horarioBody');
                        horarioBody.innerHTML = `<tr><td colspan='7' class='py-8 text-center'><div class='p-4 rounded-lg text-amber-600 bg-amber-50'><i class='mr-2 fa-solid fa-exclamation-triangle'></i>${data.mensaje}</div></td></tr>`;
                        return;
                    }

                    if (!data.horario || !data.horario.profesor) {
                        throw new Error('No se encontró el horario para el período seleccionado');
                    }
                    document.getElementById('modalNombreProfesor').textContent = data.horario.profesor.name;
                    document.getElementById('modalCorreoProfesor').textContent = `Correo: ${data.horario.profesor.email}`;
                    // Actualizar el período mostrado en el modal
                    document.getElementById('modalSemestre').textContent = semestreFiltro || '{{ \App\Helpers\SemesterHelper::getCurrentSemester() }}';
                    document.getElementById('modalAnio').textContent = currentYear;
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
                            // Filtrar planificaciones por día y módulo, y verificar que pertenezcan al horario del período correcto
                            const planificaciones = data.horario.planificaciones.filter(plan => {
                                const [planDia, planModulo] = plan.id_modulo.split('.');
                                const coincideDiaModulo = planDia === dia && planModulo === modulo;

                                // Verificar que la asignatura tenga el período correcto (si está disponible)
                                const asignaturaPeriodo = plan.asignatura && plan.asignatura.periodo;
                                const periodoCorrecto = !asignaturaPeriodo || asignaturaPeriodo === data.periodo;

                                return coincideDiaModulo && periodoCorrecto;
                            });

                            if (planificaciones.length > 0) {
                                const clasesHTML = planificaciones.map(plan => `
                                    <div class='p-2 rounded-lg min-h-[90px] w-[120px] max-w-[120px] mx-auto flex flex-col items-center justify-center text-center break-words text-black font-semibold ${getColorClase(plan.asignatura.nombre_asignatura)} shadow-md'>
                                        <div class='mb-1 text-xs tracking-wide uppercase'>${plan.asignatura.nombre_asignatura}</div>
                                        <div class='mb-1 text-xs font-normal'><i class='mr-1 fa-solid fa-door-closed'></i>${plan.espacio.id_espacio}</div>
                                        <div class='mb-1 text-xs font-normal'><i class='mr-1 fa-solid fa-hashtag'></i>${plan.asignatura.codigo_asignatura}</div>
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
                    // Error
                    document.getElementById('modalNombreProfesor').textContent = 'Error';
                    document.getElementById('modalCorreoProfesor').textContent = '';
                    const horarioBody = document.getElementById('horarioBody');

                    let errorMessage = 'Error al cargar el horario';
                    if (error.message.includes('404')) {
                        errorMessage = 'Profesor no encontrado';
                    } else if (error.message.includes('HTTP error')) {
                        errorMessage = 'Error de conexión al servidor';
                    } else {
                        errorMessage = error.message;
                    }

                    horarioBody.innerHTML = `<tr><td colspan='7' class='py-8 text-center'><div class='p-4 text-red-600 rounded-lg bg-red-50'><i class='mr-2 fa-solid fa-times-circle'></i>${errorMessage}</div></td></tr>`;
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
            // Agregar el año actual automáticamente
            formData.append('anio', currentYear);
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
                        // No se encontró el elemento profesores-lista en la respuesta
                        profesoresLista.innerHTML = '<div class="py-8 text-center text-red-500">Error al cargar los datos</div>';
                    }
                })
                .catch(error => {
                    // Error al aplicar filtros
                    profesoresLista.innerHTML = '<div class="py-8 text-center text-red-500">Error al cargar los datos: ' + error.message + '</div>';
                })
                .finally(() => ocultarSpinner());
        }

        aplicarFiltroBtn.addEventListener('click', aplicarFiltros);

        // Buscar por AJAX
        const buscarBtn = document.getElementById('buscar-btn');
        if (buscarBtn) {
            buscarBtn.addEventListener('click', aplicarFiltros);
        }
        const searchInput = document.getElementById('search-profesor');
        if (searchInput) {
            // Búsqueda en vivo con debounce al escribir
            searchInput.addEventListener('input', debounce(function () {
                aplicarFiltros();
            }, 300));

            // Búsqueda inmediata con tecla Enter
            searchInput.addEventListener('keydown', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    aplicarFiltros();
                }
            });
        }

        // Limpiar filtros
        const limpiarFiltroBtn = document.getElementById('limpiar-filtro-btn');
        limpiarFiltroBtn.addEventListener('click', function() {
            // Limpiar campo de búsqueda
            document.getElementById('search-profesor').value = '';
            
            // Resetear selector de semestre al actual
            const semestreActual = '{{ \App\Helpers\SemesterHelper::getCurrentSemester() }}';
            document.getElementById('semestre-filtro').value = semestreActual;
            
            
            // Aplicar filtros limpios
            aplicarFiltros();
        });

        // Aplicar filtros automáticamente cuando cambie el selector de semestre
        const semestreFiltro = document.getElementById('semestre-filtro');
        
        // Bandera para evitar que se ejecute automáticamente al cargar la página
        let isInitialLoad = true;
        
        semestreFiltro.addEventListener('change', function() {
            if (!isInitialLoad) {
                aplicarFiltros();
            }
        });
        
        // Marcar que ya no es la carga inicial después de un breve delay
        setTimeout(() => {
            isInitialLoad = false;
        }, 100);

        // Permitir que al hacer clic en la tarjeta completa se active el modal
        function activarClickCard() {
            document.querySelectorAll('.profesor-card').forEach(card => {
                card.addEventListener('click', function (e) {
                    // Evitar doble apertura si se hace click en el botón interno
                    if (e.target.closest('.ver-horario-btn')) return;
                    const run = this.dataset.run;
                    mostrarHorario(run);
                });
            });
            // Seguir permitiendo el click en el botón
            document.querySelectorAll('.profesor-card .ver-horario-btn').forEach(btn => {
                btn.addEventListener('click', function (e) {
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
