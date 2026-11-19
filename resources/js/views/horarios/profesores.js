import '../../shared/page-config';
        // Año académico actual desde el backend
        const currentYear = (window.HorariosProfesoresConfig?.currentYear ?? new Date().getFullYear());

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
                        document.getElementById('modalSemestre').textContent = semestreFiltro || (window.HorariosProfesoresConfig?.currentSemester ?? 1);
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
                    document.getElementById('modalSemestre').textContent = semestreFiltro || (window.HorariosProfesoresConfig?.currentSemester ?? 1);
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
            const semestreActual = (window.HorariosProfesoresConfig?.currentSemester ?? 1);
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

if (typeof cerrarModal === 'function') window.cerrarModal = cerrarModal;
if (typeof abrirModalProfesor === 'function') window.abrirModalProfesor = abrirModalProfesor;
