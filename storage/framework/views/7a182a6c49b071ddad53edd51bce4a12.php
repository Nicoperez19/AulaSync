<?php if (isset($component)) { $__componentOriginal9ac128a9029c0e4701924bd2d73d7f54 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal9ac128a9029c0e4701924bd2d73d7f54 = $attributes; } ?>
<?php $component = App\View\Components\AppLayout::resolve([] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('app-layout'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(App\View\Components\AppLayout::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
     <?php $__env->slot('header', null, []); ?> 
        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <div class="flex items-center gap-3">
                <div class="p-2 rounded-xl bg-light-cloud-blue">
                    <i class="text-2xl text-white fa-solid fa-clock"></i>
                </div>
                <div>
                    <h2 class="text-2xl font-bold leading-tight">Horarios por Espacio</h2>
                    <p class="text-sm text-gray-500">Visualiza y gestiona la programación de espacios por bloques y días
                    </p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <span class="text-sm font-semibold text-light-cloud-blue">
                    <i class="mr-1 fa-solid fa-calendar-days"></i>
                    Período: <?php echo e($semestre); ?>° Semestre <?php echo e($anioActual); ?>

                </span>
            </div>
        </div>
     <?php $__env->endSlot(); ?>

    <div class="px-6 min-h-[80vh] w-full">
        <!-- Indicador de carga inicial -->
        <div id="loadingIndicator" class="fixed inset-0 z-50 flex items-center justify-center bg-white bg-opacity-90">
            <div class="text-center">
                <div class="w-16 h-16 mx-auto mb-4 border-b-2 border-red-700 rounded-full animate-spin"></div>
                <p class="text-lg font-semibold text-gray-700">Cargando horarios...</p>
                <p class="text-sm text-gray-500">Preparando datos para una experiencia más rápida</p>
            </div>
        </div>

        <div class="flex flex-col items-center justify-center w-full space-y-6"
            x-data="{ selectedPiso: '<?php echo e($pisos->first()->id ?? 1); ?>' }">
            <!-- Tarjeta de filtros -->
            <div class="w-full p-6 bg-white shadow-sm rounded-xl">
                <form id="filtro-periodo-form" method="GET" action="" class="flex flex-col w-full gap-4"
                    onsubmit="return false;">
                    <div class="flex flex-col w-full gap-4 sm:flex-row sm:items-center">
                        <div class="flex items-center flex-1 gap-4">
                            <div class="flex items-center gap-2">
                                <span class="font-semibold text-light-cloud-blue">Año:</span>
                                <span
                                    class="px-3 py-2 font-medium text-gray-700 border border-gray-300 rounded-lg bg-gray-50">
                                    <?php echo e(\App\Helpers\SemesterHelper::getCurrentAcademicYear()); ?>

                                </span>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="font-semibold text-light-cloud-blue">Semestre:</span>
                                <select name="semestre" id="semestre"
                                    class="px-4 py-2 pr-8 transition bg-white border border-gray-300 rounded-lg focus:ring-2 focus:ring-light-cloud-blue/30 focus:border-light-cloud-blue">
                                    <?php $__currentLoopData = $semestresDisponibles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $sem): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($sem); ?>" <?php echo e(($semestreFiltro ?: \App\Helpers\SemesterHelper::getCurrentSemester()) == $sem ? 'selected' : ''); ?>>
                                            <?php echo e($sem); ?>° Semestre
                                        </option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                            </div>
                        </div>
                        <button id="aplicar-filtro-btn" type="button"
                            class="flex items-center gap-2 px-6 py-2 font-semibold text-white transition rounded-lg bg-light-cloud-blue hover:bg-red-800">
                            <i class="fa-solid fa-filter"></i>
                            Aplicar Filtros
                        </button>
                    </div>
                </form>
            </div>

            <!-- Nav Pills de Pisos - Arriba alineados a la izquierda -->
            <div class="w-full">
                <div class="">
                    <ul class="flex justify-start w-full border-b border-gray-200" role="tablist">
                        <?php $__currentLoopData = $pisos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $piso): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <li role="presentation">
                                <button type="button" @click="selectedPiso = '<?php echo e($piso->id); ?>'"
                                    class="px-8 py-3 text-base font-semibold transition-all duration-300 border border-b-0 rounded-t-xl focus:outline-none"
                                    :class="selectedPiso == '<?php echo e($piso->id); ?>' 
                                                                    ? 'bg-red-700 text-white border-red-700 shadow-md'
                                                                    : 'bg-white text-gray-700 border-gray-200 hover:bg-gray-100 hover:text-red-700'">
                                    Piso <?php echo e($piso->numero_piso); ?>

                                </button>
                            </li>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </ul>
                    <!-- Cards de espacios por piso -->
                    <div class="w-full p-6 bg-white shadow-md rounded-b-xl">
                        <?php $__currentLoopData = $pisos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $piso): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <div x-show="selectedPiso == '<?php echo e($piso->id); ?>'"
                                x-transition:enter="transition ease-out duration-300"
                                x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                                x-transition:leave="transition ease-in duration-200"
                                x-transition:leave-start="opacity-100 scale-100"
                                x-transition:leave-end="opacity-0 scale-95">
                                <?php
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
                                ?>
                                <div class="flex flex-col items-center w-full">
                                    <?php $__currentLoopData = $espaciosPorTipo; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tipo => $espacios): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <?php
                                            $icono = $iconos[$tipo] ?? 'fa-solid fa-door-closed';
                                            $badgeTipo = $badgeColores[$tipo] ?? 'bg-gray-100 text-gray-700';
                                        ?>
                                        <div class="flex flex-col items-center w-full mb-8">
                                            <div class="flex flex-col items-center justify-center w-full mb-3">
                                                <div class="flex items-center justify-center w-full gap-3">
                                                    <i class="<?php echo e($icono); ?> text-2xl text-gray-700"></i>
                                                    <h3 class="text-xl font-bold text-gray-900"><?php echo e($tipo); ?></h3>
                                                    <span
                                                        class="px-2 py-0.5 text-xs font-semibold bg-gray-200 rounded-full text-gray-700"><?php echo e($espacios->count()); ?>

                                                        espacio<?php echo e($espacios->count() > 1 ? 's' : ''); ?></span>
                                                </div>
                                            </div>
                                            <div
                                                class="grid justify-center w-full grid-cols-1 gap-6 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 justify-items-center">
                                                <?php $__currentLoopData = $espacios; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $espacio): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                    <div class="relative flex flex-col justify-between p-4 border border-gray-200 rounded-xl shadow-sm bg-white transition hover:shadow-lg hover:scale-[1.03] cursor-pointer group items-center text-center mx-auto"
                                                        data-id="<?php echo e($espacio->id_espacio); ?>"
                                                        data-nombre="<?php echo e($espacio->nombre_espacio); ?>"
                                                        @click="mostrarHorarioEspacio('<?php echo e($espacio->id_espacio); ?>', '<?php echo e($espacio->nombre_espacio); ?>')">
                                                        <div class="flex items-center justify-center gap-2 mb-2">
                                                            <i class="<?php echo e($icono); ?> text-xl text-gray-500"></i>
                                                            <span class="font-bold text-gray-800"><?php echo e($espacio->id_espacio); ?></span>
                                                        </div>
                                                        <span
                                                            class="flex items-center justify-center gap-1 mb-1 text-xs font-semibold">
                                                            
                                                        </span>
                                                        <div class="mb-1 text-base font-semibold text-gray-900">
                                                            <?php echo e($espacio->nombre_espacio); ?>

                                                        </div>
                                                        <div class="flex items-center justify-center gap-2 mb-2">
                                                            <span
                                                                class="px-2 py-0.5 text-xs font-semibold rounded-full <?php echo e($badgeTipo); ?>"><?php echo e($tipo); ?></span>
                                                            <span class="flex items-center gap-1 text-xs text-gray-500">
                                                                <i class="fa-solid fa-users"></i>
                                                                <?php echo e($espacio->puestos_disponibles ?? '-'); ?> personas
                                                            </span>
                                                        </div>
                                                        <span
                                                            class="flex items-center justify-center gap-1 text-xs font-medium text-violet-700 group-hover:underline">
                                                            <i class="fa-solid fa-calendar-days"></i>
                                                            <span
                                                                class="ml-1 px-1.5 py-0.5 bg-violet-100 text-violet-700 rounded-full text-xs font-semibold"
                                                                x-text="(horariosPorEspacio['<?php echo e($espacio->id_espacio); ?>'] || []).length + ' clases'">
                                                            </span>
                                                        </span>
                                                    </div>
                                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                            </div>
                                        </div>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </div>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal para mostrar horarios del espacio -->
        <div id="horarioEspacioModal"
            class="fixed inset-0 z-[9999] flex items-center justify-center bg-black bg-opacity-50 hidden">
            <div
                class="flex flex-col w-full max-h-screen mx-2 overflow-hidden bg-white rounded-lg shadow-lg max-w-7xl md:mx-8">
                <!-- Encabezado rojo con diseño tipo banner -->
                <div id="modalHeader"
                    class="relative flex flex-col gap-6 p-8 bg-red-700 md:flex-row md:items-center md:justify-between">
                    <!-- Círculos decorativos -->
                    <span
                        class="absolute top-0 left-0 w-32 h-32 -translate-x-1/2 -translate-y-1/2 bg-white rounded-full pointer-events-none bg-opacity-10"></span>
                    <span
                        class="absolute top-0 right-0 w-32 h-32 translate-x-1/2 -translate-y-1/2 bg-white rounded-full pointer-events-none bg-opacity-10"></span>
                    <div class="flex items-center flex-1 min-w-0 gap-5">
                        <div class="flex flex-col items-center justify-center flex-shrink-0">
                            <div class="p-4 mb-2 bg-white rounded-full bg-opacity-20">
                                <i class="text-3xl text-white fa-solid fa-calendar-days"></i>
                            </div>
                        </div>
                        <div class="flex flex-col min-w-0">
                            <h1 id="modalEspacioTitle" class="text-3xl font-bold text-white truncate"></h1>
                            <div class="flex items-center gap-2 mt-1">
                                <span id="modalEspacioTipo" class="text-lg truncate text-white/80"></span>
                                <span class="text-lg text-white/80">•</span>
                                <span id="modalPeriodo" class="text-lg font-semibold text-white/80"><?php echo e($semestre); ?>er
                                    semestre - <?php echo e($anioActual); ?></span>
                            </div>
                        </div>
                    </div>
                    <div class="flex items-center self-start flex-shrink-0 gap-3 md:self-center">
                        <button id="exportPdfBtn"
                            class="border border-white text-white px-4 py-1.5 rounded-lg font-semibold hover:bg-white hover:text-red-700 transition">Exportar
                            PDF</button>
                        <button onclick="cerrarModalEspacio()"
                            class="ml-2 text-3xl font-bold text-white hover:text-gray-200">&times;</button>
                    </div>
                </div>
                <div class="p-6 bg-gray-50 overflow-y-auto max-h-[70vh] flex-1">
                    <div class="overflow-x-auto">
                        <table class="min-w-full overflow-hidden bg-white rounded-lg">
                            <thead>
                                <tr class="bg-gray-100">
                                    <th class="px-4 py-3 font-semibold text-center text-gray-600 border-b"><i
                                            class="mr-1 fa-regular fa-clock"></i>Hora</th>
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
        let horariosPorEspacio = <?php echo json_encode($horariosPorEspacio, 15, 512) ?>;
        let espacioActualId = null;

        const filtroForm = document.getElementById('filtro-periodo-form');
        const aplicarFiltroBtn = document.getElementById('aplicar-filtro-btn');

        function aplicarFiltros() {
            const anioFiltro = new Date().getFullYear();
            const semestreFiltro = document.getElementById('semestre')?.value;

            if (!semestreFiltro) {
                Swal.fire({
                    title: 'No hay datos de horarios en los espacios',
                    icon: 'warning',
                    confirmButtonText: 'Entendido',
                    confirmButtonColor: '#3B82F6'
                });
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
                    Swal.fire({
                        title: 'Error al cargar horarios',
                        html: `
                            <div class="text-center">
                                <p class="mb-4">Error al cargar los horarios. Por favor, inténtalo de nuevo.</p>
                                <p class="text-sm text-gray-600">Si el problema persiste, contacta al administrador del sistema.</p>
                            </div>
                        `,
                        icon: 'error',
                        confirmButtonText: 'Entendido',
                        confirmButtonColor: '#EF4444'
                    });
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
                // Establecer valor por defecto en el select de semestre
                const semestreSelect = document.getElementById('semestre');

                if (semestreSelect) {
                    semestreSelect.value = '1';

                    // Aplicar filtros automáticamente
                    setTimeout(() => {
                        aplicarFiltros();
                    }, 100);
                }
            } else {

            }
        });

        const coloresClases = [
            'bg-pink-50', 'bg-blue-50', 'bg-yellow-50', 'bg-green-50', 'bg-purple-50', 'bg-orange-50', 'bg-cyan-50', 'bg-lime-50'
        ];
        function getColorClase(nombre) {
            let hash = 0;
            for (let i = 0; i < nombre.length; i++) hash = nombre.charCodeAt(i) + ((hash << 5) - hash);
            return coloresClases[Math.abs(hash) % coloresClases.length];
        }

        function mostrarHorarioEspacio(idEspacio, nombreEspacio) {
            espacioActualId = idEspacio;

            let tipoEspacio = '';
            let nombreCompleto = '';
            const card = document.querySelector(`[data-id='${idEspacio}']`);
            if (card) {
                const tipoSpan = card.querySelector('span.rounded-full');
                if (tipoSpan) tipoEspacio = tipoSpan.textContent;

                const nombreSpan = card.querySelector('.text-base.font-semibold');
                if (nombreSpan) nombreCompleto = nombreSpan.textContent;
            }

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

            const tituloFormateado = `Horario ${tipoEspacio.toLowerCase()} ${nombreCompleto} (${idEspacio})`;
            document.getElementById('modalEspacioTitle').textContent = tituloFormateado;
            document.getElementById('modalEspacioTipo').textContent = tipoEspacio;

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

            const Modulos = [
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

            Modulos.forEach(modulo => {
                const tr = document.createElement('tr');
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
                                <div class='mb-1 text-xs tracking-wide uppercase'>${h.asignatura} (${h.codigo_asignatura || 'N/A'})</div>
                                <div class='mb-1 text-xs font-normal'><i class='mr-1 fa-solid fa-user'></i>${h.profesor ? h.profesor.name : 'Sin profesor asignado'}</div>
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
            if (!hora) return '';
            const [h, m] = hora.split(':');
            return `${parseInt(h)}:${m}`;
        }

        function cerrarModalEspacio() {
            document.getElementById('horarioEspacioModal').classList.add('hidden');
            document.body.classList.remove('overflow-hidden');
            document.documentElement.classList.remove('overflow-hidden');
            espacioActualId = null;
        }

        function exportarHorarioPDF() {
            if (!espacioActualId) {
                Swal.fire({
                    title: 'No se ha seleccionado ningún espacio',
                    html: `
                        <div class="text-center">
                            <p class="mb-4">Error: No se ha seleccionado ningún espacio</p>
                            <p class="text-sm text-gray-600">Por favor, selecciona un espacio para exportar su horario en PDF.</p>
                        </div>
                    `,
                    icon: 'warning',
                    confirmButtonText: 'Entendido',
                    confirmButtonColor: '#3B82F6'
                });
                return;
            }

            const anioFiltro = new Date().getFullYear();
            const semestreFiltro = document.getElementById('semestre')?.value || '';

            let url = `/espacios/${espacioActualId}/export-pdf`;
            const params = new URLSearchParams();
            params.append('anio', anioFiltro);
            if (semestreFiltro) params.append('semestre', semestreFiltro);
            if (params.toString()) {
                url += '?' + params.toString();
            }

            const exportBtn = document.getElementById('exportPdfBtn');
            const originalText = exportBtn.innerHTML;
            exportBtn.innerHTML = '<i class="mr-2 fa-solid fa-spinner fa-spin"></i>Generando PDF...';
            exportBtn.disabled = true;

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
                    const url = window.URL.createObjectURL(blob);
                    const a = document.createElement('a');
                    a.style.display = 'none';
                    a.href = url;

                    const fecha = new Date();
                    const mes = fecha.getMonth() + 1;
                    const dia = fecha.getDate();
                    let anio = fecha.getFullYear();

                    let semestre;
                    if (mes >= 7 && dia >= 21) {
                        semestre = 2;
                    } else if (mes >= 8) {
                        semestre = 2;
                    } else {
                        semestre = 1;
                    }

                    if (anioFiltro) anio = anioFiltro;
                    if (semestreFiltro) semestre = semestreFiltro;

                    const periodo = `${anio}_${semestre}`;

                    a.download = `${espacioActualId}_horario_${periodo}.pdf`;

                    document.body.appendChild(a);
                    a.click();
                    window.URL.revokeObjectURL(url);
                    document.body.removeChild(a);
                })
                .catch(error => {
                    Swal.fire({
                        title: 'Error al generar PDF',
                        html: `
                            <div class="text-center">
                                <p class="mb-4">Error al generar el PDF: ${error.message}</p>
                                <p class="text-sm text-gray-600">Por favor, inténtalo de nuevo o contacta al administrador si el problema persiste.</p>
                            </div>
                        `,
                        icon: 'error',
                        confirmButtonText: 'Entendido',
                        confirmButtonColor: '#EF4444'
                    });
                })
                .finally(() => {
                    exportBtn.innerHTML = originalText;
                    exportBtn.disabled = false;
                });
        }

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
 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal9ac128a9029c0e4701924bd2d73d7f54)): ?>
<?php $attributes = $__attributesOriginal9ac128a9029c0e4701924bd2d73d7f54; ?>
<?php unset($__attributesOriginal9ac128a9029c0e4701924bd2d73d7f54); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal9ac128a9029c0e4701924bd2d73d7f54)): ?>
<?php $component = $__componentOriginal9ac128a9029c0e4701924bd2d73d7f54; ?>
<?php unset($__componentOriginal9ac128a9029c0e4701924bd2d73d7f54); ?>
<?php endif; ?><?php /**PATH D:\Dev\AulaSync\resources\views/layouts/spacetime/spacetime_show.blade.php ENDPATH**/ ?>