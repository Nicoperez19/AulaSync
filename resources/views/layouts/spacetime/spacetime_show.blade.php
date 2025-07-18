<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <div class="flex items-center gap-3">
                <div class="p-2 rounded-xl bg-light-cloud-blue">
                    <i class="fa-solid fa-clock text-white text-2xl"></i>
                </div>
                <div>
                    <h2 class="text-2xl font-bold leading-tight">Horarios por Espacio</h2>
                    <p class="text-gray-500 text-sm">Visualiza y gestiona la programación de espacios por bloques y días
                    </p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <span class="text-sm text-light-cloud-blue font-semibold">
                    <i class="fa-solid fa-calendar-days mr-1"></i>
                    Período: {{ $semestre }}er Semestre {{ $anioActual }}
                </span>
            </div>
        </div>
    </x-slot>

    <div class="p-6 min-h-[80vh] w-full">
        <!-- Indicador de carga inicial -->
        <div id="loadingIndicator" class="fixed inset-0 z-50 flex items-center justify-center bg-white bg-opacity-90">
            <div class="text-center">
                <div class="animate-spin rounded-full h-16 w-16 border-b-2 border-red-700 mx-auto mb-4"></div>
                <p class="text-lg font-semibold text-gray-700">Cargando horarios...</p>
                <p class="text-sm text-gray-500">Preparando datos para una experiencia más rápida</p>
            </div>
        </div>

        <div class="p-6 space-y-6 flex flex-col items-center justify-center w-full"
            x-data="{ selectedPiso: '{{ $pisos->first()->id ?? 1 }}' }">
            <!-- Tarjeta de filtros -->
            <div class="w-full mb-6">
                <div class="bg-white rounded-xl shadow-sm p-6 w-full">
                    <form id="filtro-periodo-form" method="GET" action="" class="flex flex-col gap-4 w-full" onsubmit="return false;">
                        <div class="flex flex-col sm:flex-row sm:items-center gap-4 w-full">
                            <div class="flex-1 flex items-center gap-4">
                                <div class="flex items-center gap-2">
                                    <label for="anio" class="text-sm font-semibold text-gray-700">Año:</label>
                                    <select name="anio" id="anio"
                                        class="px-3 py-2 border border-gray-300 rounded-lg bg-white focus:ring-2 focus:ring-light-cloud-blue/30 focus:border-light-cloud-blue transition">
                                        <option value="">Todos los años</option>
                                        @foreach($aniosDisponibles as $anio)
                                            <option value="{{ $anio }}" {{ ($anioFiltro ?: '2025') == $anio ? 'selected' : '' }}>
                                                {{ $anio }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="flex items-center gap-2">
                                    <label for="semestre" class="text-sm font-semibold text-gray-700">Semestre:</label>
                                    <select name="semestre" id="semestre"
                                        class="px-3 py-2 border border-gray-300 rounded-lg bg-white focus:ring-2 focus:ring-light-cloud-blue/30 focus:border-light-cloud-blue transition">
                                        <option value="">Todos los semestres</option>
                                        @foreach($semestresDisponibles as $sem)
                                            <option value="{{ $sem }}" {{ ($semestreFiltro ?: '1') == $sem ? 'selected' : '' }}>
                                                {{ $sem }}er Semestre
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <button id="aplicar-filtro-btn" type="button"
                                class="px-6 py-2 bg-light-cloud-blue text-white rounded-lg font-semibold hover:bg-red-800 transition flex items-center gap-2">
                                <i class="fa-solid fa-filter"></i>
                                Aplicar Filtros
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Nav Pills de Pisos - Arriba alineados a la izquierda -->
            <div class="w-full">
                <div class="">
                    <ul class="flex border-b border-gray-200 justify-start w-full" role="tablist">
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
                    <div class="p-6 bg-white shadow-md rounded-b-xl w-full">
                        @foreach ($pisos as $piso)
                            <div x-show="selectedPiso == '{{ $piso->id }}'"
                                x-transition:enter="transition ease-out duration-300"
                                x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                                x-transition:leave="transition ease-in duration-200"
                                x-transition:leave-start="opacity-100 scale-100"
                                x-transition:leave-end="opacity-0 scale-95">
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
                                                    <span
                                                        class="px-2 py-0.5 text-xs font-semibold bg-gray-200 rounded-full text-gray-700">{{ $espacios->count() }}
                                                        espacio{{ $espacios->count() > 1 ? 's' : '' }}</span>
                                                </div>
                                            </div>
                                            <div
                                                class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-6 justify-center justify-items-center w-full">
                                                @foreach ($espacios as $espacio)
                                                    @php
                                                        $estado = $espacio->esta_ocupado ? 'Ocupado' : 'Disponible';
                                                        $puntoEstado = $espacio->esta_ocupado ? 'bg-red-500' : 'bg-green-500';
                                                    @endphp
                                                    <div class="relative flex flex-col justify-between p-4 border border-gray-200 rounded-xl shadow-sm bg-white transition hover:shadow-lg hover:scale-[1.03] cursor-pointer group items-center text-center mx-auto"
                                                        data-id="{{ $espacio->id_espacio }}"
                                                        data-nombre="{{ $espacio->nombre_espacio }}"
                                                        @click="mostrarHorarioEspacio('{{ $espacio->id_espacio }}', '{{ $espacio->nombre_espacio }}')">
                                                        <div class="flex items-center justify-center gap-2 mb-2">
                                                            <i class="{{ $icono }} text-xl text-gray-500"></i>
                                                            <span class="font-bold text-gray-800">{{ $espacio->id_espacio }}</span>
                                                        </div>
                                                        <span
                                                            class="flex items-center justify-center gap-1 text-xs font-semibold mb-1">
                                                            <span
                                                                class="inline-block w-2 h-2 rounded-full {{ $puntoEstado }}"></span>
                                                            {{ $estado }}
                                                        </span>
                                                        <div class="mb-1 text-base font-semibold text-gray-900">
                                                            {{ $espacio->nombre_espacio }}
                                                        </div>
                                                        <div class="flex items-center justify-center gap-2 mb-2">
                                                            <span
                                                                class="px-2 py-0.5 text-xs font-semibold rounded-full {{ $badgeTipo }}">{{ $tipo }}</span>
                                                            <span class="flex items-center gap-1 text-xs text-gray-500">
                                                                <i class="fa-solid fa-users"></i>
                                                                {{ $espacio->puestos_disponibles ?? '-' }} personas
                                                            </span>
                                                        </div>
                                                        <span
                                                            class="flex items-center justify-center gap-1 text-xs font-medium text-violet-700 group-hover:underline">
                                                            <i class="fa-solid fa-calendar-days"></i>
                                                            Ver horarios
                                                            <span
                                                                class="ml-1 px-1.5 py-0.5 bg-violet-100 text-violet-700 rounded-full text-xs font-semibold"
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
            <div
                class="w-full max-w-7xl mx-2 md:mx-8 bg-white rounded-lg shadow-lg overflow-hidden max-h-screen flex flex-col">
                <!-- Encabezado rojo con diseño tipo banner -->
                <div id="modalHeader"
                    class="relative bg-red-700 p-8 flex flex-col md:flex-row md:items-center md:justify-between gap-6">
                    <!-- Círculos decorativos -->
                    <span
                        class="absolute left-0 top-0 w-32 h-32 bg-white bg-opacity-10 rounded-full -translate-x-1/2 -translate-y-1/2 pointer-events-none"></span>
                    <span
                        class="absolute right-0 top-0 w-32 h-32 bg-white bg-opacity-10 rounded-full translate-x-1/2 -translate-y-1/2 pointer-events-none"></span>
                    <div class="flex items-center gap-5 flex-1 min-w-0">
                        <div class="flex-shrink-0 flex flex-col items-center justify-center">
                            <div class="bg-white bg-opacity-20 rounded-full p-4 mb-2">
                                <i class="fa-solid fa-calendar-days text-3xl text-white"></i>
                            </div>
                        </div>
                        <div class="flex flex-col min-w-0">
                            <h1 id="modalEspacioTitle" class="text-3xl font-bold text-white truncate"></h1>
                            <div class="flex items-center gap-2 mt-1">
                                <span id="modalEspacioTipo" class="text-lg text-white/80 truncate"></span>
                                <span class="text-lg text-white/80">•</span>
                                <span id="modalPeriodo" class="text-lg text-white/80 font-semibold">{{ $semestre }}er
                                    semestre - {{ $anioActual }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="flex items-center gap-3 flex-shrink-0 self-start md:self-center">
                        <button id="exportPdfBtn"
                            class="border border-white text-white px-4 py-1.5 rounded-lg font-semibold hover:bg-white hover:text-red-700 transition">Exportar
                            PDF</button>
                        <button onclick="cerrarModalEspacio()"
                            class="text-3xl font-bold text-white hover:text-gray-200 ml-2">&times;</button>
                    </div>
                </div>
                <div class="p-6 bg-gray-50 overflow-y-auto max-h-[70vh] flex-1">
                    <div class="overflow-x-auto">
                        <table class="min-w-full bg-white rounded-lg overflow-hidden">
                            <thead>
                                <tr class="bg-gray-100">
                                    <th class="px-4 py-3 font-semibold text-center border-b text-gray-600"><i
                                            class="fa-regular fa-clock mr-1"></i>Hora</th>
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
    </div>

    <script>
        // Variable global para almacenar los horarios
        let horariosPorEspacio = @json($horariosPorEspacio);
        let espacioActualId = null; // Variable global para el ID del espacio actual

        // Elementos del formulario de filtros
        const filtroForm = document.getElementById('filtro-periodo-form');
        const aplicarFiltroBtn = document.getElementById('aplicar-filtro-btn');

        // Aplicar filtros por AJAX
        function aplicarFiltros() {
            const anioFiltro = document.getElementById('anio')?.value;
            const semestreFiltro = document.getElementById('semestre')?.value;

            // Verificar que ambos filtros estén seleccionados
            if (!anioFiltro || !semestreFiltro) {
                alert('Por favor, selecciona tanto el año como el semestre para cargar los horarios.');
                return;
            }

            // Mostrar indicador de carga
            const loadingIndicator = document.getElementById('loadingIndicator');
            if (loadingIndicator) {
                loadingIndicator.style.display = 'flex';
            }

            // Cargar horarios por AJAX
            fetch(`/horarios-por-periodo?anio=${anioFiltro}&semestre=${semestreFiltro}`, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Error en la respuesta del servidor');
                    }
                    return response.json();
                })
                .then(data => {
                    // Actualizar los horarios globales
                    horariosPorEspacio = data.horariosPorEspacio;

                    // Actualizar el contador de clases en las tarjetas
                    actualizarContadoresClases();


                    // Mostrar mensaje de éxito
                    mostrarMensajeExito(`Horarios cargados para ${semestreFiltro}er Semestre ${anioFiltro}`);
                })
                .catch(error => {
                    console.error('Error al cargar horarios:', error);
                    alert('Error al cargar los horarios. Por favor, inténtalo de nuevo.');
                })
                .finally(() => {
                    // Ocultar indicador de carga
                    if (loadingIndicator) {
                        loadingIndicator.style.display = 'none';
                    }
                });
        }

        // Función para actualizar los contadores de clases en las tarjetas
        function actualizarContadoresClases() {
            document.querySelectorAll('[data-id]').forEach(card => {
                const espacioId = card.getAttribute('data-id');
                const contadorSpan = card.querySelector('.bg-violet-100');
                if (contadorSpan) {
                    const clases = horariosPorEspacio[espacioId] || [];
                    contadorSpan.textContent = clases.length + ' clases';
                }
            });
        }

        // Función para mostrar mensaje de éxito
        function mostrarMensajeExito(mensaje) {
            // Crear un toast de éxito
            const toast = document.createElement('div');
            toast.className = 'fixed top-4 right-4 bg-green-500 text-white px-6 py-3 rounded-lg shadow-lg z-50 transform transition-all duration-300 translate-x-full';
            toast.innerHTML = `
                <div class="flex items-center gap-2">
                    <i class="fa-solid fa-check-circle"></i>
                    <span>${mensaje}</span>
                </div>
            `;
            document.body.appendChild(toast);

            // Animar entrada
            setTimeout(() => {
                toast.classList.remove('translate-x-full');
            }, 100);

            // Remover después de 3 segundos
            setTimeout(() => {
                toast.classList.add('translate-x-full');
                setTimeout(() => {
                    document.body.removeChild(toast);
                }, 300);
            }, 3000);
        }

        // Event listeners para los filtros
        document.addEventListener('DOMContentLoaded', function () {
            if (aplicarFiltroBtn) {
                aplicarFiltroBtn.addEventListener('click', aplicarFiltros);
            }

            // Aplicar filtros automáticamente al cargar la página si no hay horarios cargados
            if (Object.keys(horariosPorEspacio).length === 0) {
                // Establecer valores por defecto en los selects
                const anioSelect = document.getElementById('anio');
                const semestreSelect = document.getElementById('semestre');

                if (anioSelect && semestreSelect) {
                    anioSelect.value = '2025';
                    semestreSelect.value = '1';

                    // Aplicar filtros automáticamente
                    setTimeout(() => {
                        aplicarFiltros();
                    }, 100);
                }
            } else {

            }
        });

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
            // Guardar el ID del espacio actual para la exportación
            espacioActualId = idEspacio;

            // Buscar tipo de espacio y nombre desde la tarjeta
            let tipoEspacio = '';
            let nombreCompleto = '';
            const card = document.querySelector(`[data-id='${idEspacio}']`);
            if (card) {
                const tipoSpan = card.querySelector('span.rounded-full');
                if (tipoSpan) tipoEspacio = tipoSpan.textContent;

                const nombreSpan = card.querySelector('.text-base.font-semibold');
                if (nombreSpan) nombreCompleto = nombreSpan.textContent;
            }

            // Obtener el período del horario del espacio específico
            let periodoEspacio = '';
            const horariosEspacio = horariosPorEspacio[idEspacio] || [];
            if (horariosEspacio.length > 0 && horariosEspacio[0].periodo) {
                const partesPeriodo = horariosEspacio[0].periodo.split('-');
                if (partesPeriodo.length === 2) {
                    const anio = partesPeriodo[0];
                    const semestre = partesPeriodo[1];
                    periodoEspacio = `${semestre}er semestre - ${anio}`;
                }
            }

            // Formatear el título como "Horario sala de clases TH-50"
            const tituloFormateado = `Horario ${tipoEspacio.toLowerCase()} ${nombreCompleto} (${idEspacio})`;
            document.getElementById('modalEspacioTitle').textContent = tituloFormateado;
            document.getElementById('modalEspacioTipo').textContent = tipoEspacio;

            // Actualizar el período en el modal
            const periodoElement = document.getElementById('modalPeriodo');
            if (periodoElement && periodoEspacio) {
                periodoElement.textContent = periodoEspacio;
            }

            document.getElementById('horarioEspacioModal').classList.remove('hidden');
            document.body.classList.add('overflow-hidden');
            document.documentElement.classList.add('overflow-hidden');

            const horarios = horariosPorEspacio[idEspacio] || [];
            const tbody = document.getElementById('horarioEspacioBody');
            tbody.innerHTML = '';

            // Definir todos los módulos disponibles desde las 8:10 hasta las 22:10
            const todosLosModulos = [
                { hora_inicio: '08:10:00', hora_termino: '09:00:00' },
                { hora_inicio: '09:10:00', hora_termino: '10:00:00' },
                { hora_inicio: '10:10:00', hora_termino: '11:00:00' },
                { hora_inicio: '11:10:00', hora_termino: '12:00:00' },
                { hora_inicio: '12:10:00', hora_termino: '13:00:00' },
                { hora_inicio: '13:10:00', hora_termino: '14:00:00' },
                { hora_inicio: '14:10:00', hora_termino: '15:00:00' },
                { hora_inicio: '15:10:00', hora_termino: '16:00:00' },
                { hora_inicio: '16:10:00', hora_termino: '17:00:00' },
                { hora_inicio: '17:10:00', hora_termino: '18:00:00' },
                { hora_inicio: '18:10:00', hora_termino: '19:00:00' },
                { hora_inicio: '19:10:00', hora_termino: '20:00:00' },
                { hora_inicio: '20:10:00', hora_termino: '21:00:00' },
                { hora_inicio: '21:10:00', hora_termino: '22:00:00' },
                { hora_inicio: '22:10:00', hora_termino: '23:00:00' }
            ];

            const diasUnicos = ['lunes', 'martes', 'miércoles', 'jueves', 'viernes', 'sábado'];

            todosLosModulos.forEach(modulo => {
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
                                <div class='text-xs uppercase tracking-wide mb-1'>${h.asignatura} (${h.codigo_asignatura || 'N/A'})</div>
                                <div class='text-xs font-normal mb-1'><i class='fa-solid fa-user mr-1'></i>${h.profesor ? h.profesor.name : 'Sin profesor asignado'}</div>
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
            espacioActualId = null; // Limpiar el ID del espacio actual
        }

        // Función para exportar PDF
        function exportarHorarioPDF() {
            if (!espacioActualId) {
                alert('Error: No se ha seleccionado ningún espacio');
                return;
            }

            // Obtener los filtros actuales
            const anioFiltro = document.getElementById('anio')?.value || '';
            const semestreFiltro = document.getElementById('semestre')?.value || '';

            // Construir la URL con los filtros
            let url = `/espacios/${espacioActualId}/export-pdf`;
            const params = new URLSearchParams();
            if (anioFiltro) params.append('anio', anioFiltro);
            if (semestreFiltro) params.append('semestre', semestreFiltro);
            if (params.toString()) {
                url += '?' + params.toString();
            }

            // Mostrar indicador de carga
            const exportBtn = document.getElementById('exportPdfBtn');
            const originalText = exportBtn.innerHTML;
            exportBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin mr-2"></i>Generando PDF...';
            exportBtn.disabled = true;

            // Realizar la petición para descargar el PDF
            fetch(url, {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                }
            })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Error al generar el PDF');
                    }
                    return response.blob();
                })
                .then(blob => {
                    // Crear un enlace temporal para descargar el archivo
                    const url = window.URL.createObjectURL(blob);
                    const a = document.createElement('a');
                    a.style.display = 'none';
                    a.href = url;

                    // Determinar el período para el nombre del archivo
                    const fecha = new Date();
                    const mes = fecha.getMonth() + 1; // getMonth() devuelve 0-11
                    let anio = fecha.getFullYear();
                    let semestre = (mes >= 3 && mes <= 7) ? 1 : 2; // Marzo-Julio = Semestre 1, Agosto-Febrero = Semestre 2

                    // Usar los filtros si están disponibles
                    if (anioFiltro) anio = anioFiltro;
                    if (semestreFiltro) semestre = semestreFiltro;

                    const periodo = `${anio}_${semestre}`;

                    // Formato del nombre: espacio_horario_2025_1.pdf
                    a.download = `${espacioActualId}_horario_${periodo}.pdf`;

                    document.body.appendChild(a);
                    a.click();
                    window.URL.revokeObjectURL(url);
                    document.body.removeChild(a);
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error al generar el PDF: ' + error.message);
                })
                .finally(() => {
                    // Restaurar el botón
                    exportBtn.innerHTML = originalText;
                    exportBtn.disabled = false;
                });
        }

        // Agregar event listener al botón de exportar
        document.addEventListener('DOMContentLoaded', function () {
            const exportBtn = document.getElementById('exportPdfBtn');
            if (exportBtn) {
                exportBtn.addEventListener('click', exportarHorarioPDF);
            }
        });
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            setTimeout(() => {
                const loading = document.getElementById('loadingIndicator');
                if (loading) loading.style.display = 'none';
            }, 500);
        });
    </script>
</x-app-layout>