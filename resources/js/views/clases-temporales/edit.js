        const profesorColaborador = (window.ClasesTemporalesEditConfig?.profesorColaborador ?? {});
        const espacios = (window.ClasesTemporalesEditConfig?.espacios ?? []);
        const modulos = (window.ClasesTemporalesEditConfig?.modulos ?? []);
        const asignaturas = (window.ClasesTemporalesEditConfig?.asignaturas ?? []);
        const diasMap = {
            'lunes': 'LU',
            'martes': 'MA',
            'miercoles': 'MI',
            'jueves': 'JU',
            'viernes': 'VI',
            'sabado': 'SA'
        };

        let seleccionados = {};
        let seleccionandoDesde = null;

        // Inicializar seleccionados
        function initializarSeleccionados() {
            document.querySelectorAll('.modulo-cell[data-selected="true"]').forEach(cell => {
                const idModulo = cell.getAttribute('data-id-modulo');
                const planificacion = profesorColaborador.planificaciones.find(p => p.id_modulo === idModulo);
                seleccionados[idModulo] = {
                    id_modulo: idModulo,
                    id_espacio: planificacion ? planificacion.id_espacio : null
                };
            });
        }

        // Event listeners para el calendario
        document.querySelectorAll('.modulo-cell:not([data-disabled])').forEach(cell => {
            cell.addEventListener('mousedown', (e) => {
                if (e.button !== 0) return;
                e.preventDefault();
                const idModulo = cell.getAttribute('data-id-modulo');
                seleccionandoDesde = { cell, idModulo };
            });

            cell.addEventListener('mouseover', () => {
                if (!seleccionandoDesde) return;
                
                const fromModulo = parseInt(seleccionandoDesde.idModulo.split('.')[1]);
                const toModulo = parseInt(cell.getAttribute('data-id-modulo').split('.')[1]);
                const fromDia = seleccionandoDesde.cell.getAttribute('data-dia');
                const toDia = cell.getAttribute('data-dia');

                if (fromDia === toDia) {
                    const min = Math.min(fromModulo, toModulo);
                    const max = Math.max(fromModulo, toModulo);

                    document.querySelectorAll('.modulo-cell').forEach(c => {
                        c.style.backgroundColor = '';
                        if (c.getAttribute('data-disabled')) return;
                        
                        const dia = c.getAttribute('data-dia');
                        const modulo = parseInt(c.getAttribute('data-id-modulo').split('.')[1]);
                        
                        if (dia === fromDia && modulo >= min && modulo <= max) {
                            c.style.backgroundColor = '#FCA5A5';
                        } else if (c.getAttribute('data-selected') === 'true') {
                            c.style.backgroundColor = '#DC2626';
                        }
                    });
                }
            });

            cell.addEventListener('mouseup', (e) => {
                if (!seleccionandoDesde) return;
                e.preventDefault();

                const fromModulo = parseInt(seleccionandoDesde.idModulo.split('.')[1]);
                const toModulo = parseInt(cell.getAttribute('data-id-modulo').split('.')[1]);
                const fromDia = seleccionandoDesde.cell.getAttribute('data-dia');
                const toDia = cell.getAttribute('data-dia');

                if (fromDia === toDia) {
                    const min = Math.min(fromModulo, toModulo);
                    const max = Math.max(fromModulo, toModulo);
                    const prefijo = diasMap[fromDia];

                    for (let i = min; i <= max; i++) {
                        const idModulo = prefijo + '.' + i;
                        if (!seleccionados[idModulo]) {
                            seleccionados[idModulo] = { id_modulo: idModulo, id_espacio: null };
                        }
                    }

                    actualizarVista();
                }

                seleccionandoDesde = null;
            });

            // Click individual
            cell.addEventListener('click', (e) => {
                if (e.detail === 1 && !seleccionandoDesde) {
                    const idModulo = cell.getAttribute('data-id-modulo');
                    if (seleccionados[idModulo]) {
                        delete seleccionados[idModulo];
                    }
                    actualizarVista();
                }
            });
        });

        // Función para actualizar la vista
        function actualizarVista() {
            // Actualizar células
            document.querySelectorAll('.modulo-cell:not([data-disabled])').forEach(cell => {
                const idModulo = cell.getAttribute('data-id-modulo');
                if (seleccionados[idModulo]) {
                    cell.style.backgroundColor = '#DC2626';
                    cell.innerHTML = '<div class="h-full flex items-center justify-center"><i class="fa-solid fa-check text-white text-xs"></i></div>';
                    cell.setAttribute('data-selected', 'true');
                } else {
                    cell.style.backgroundColor = '';
                    cell.innerHTML = '';
                    cell.setAttribute('data-selected', 'false');
                }
            });

            // Actualizar lista de clases
            actualizarListaClases();

            // Cargar salas disponibles
            if (Object.keys(seleccionados).length > 0) {
                cargarSalasDisponibles();
            } else {
                limpiarSalas();
            }
        }

        // Actualizar lista de clases seleccionadas
        function actualizarListaClases() {
            const selectedClasses = document.getElementById('selected-classes');
            selectedClasses.innerHTML = '';

            if (Object.keys(seleccionados).length === 0) {
                selectedClasses.innerHTML = '<p class="text-sm text-gray-500 italic">No hay clases seleccionadas aún</p>';
                document.getElementById('planificaciones-input').value = '';
                return;
            }

            Object.keys(seleccionados).forEach(idModulo => {
                const prefijo = idModulo.split('.')[0];
                const numModulo = idModulo.split('.')[1];
                
                const diaKeys = Object.keys(diasMap);
                const dia = diaKeys.find(d => diasMap[d] === prefijo);

                const div = document.createElement('div');
                div.className = 'flex items-center justify-between bg-white p-3 rounded-lg border border-gray-200 selected-class-item';
                div.setAttribute('data-id-modulo', idModulo);
                div.innerHTML = `
                    <div class="flex items-center gap-3">
                        <div class="w-3 h-3 bg-red-600 rounded-full"></div>
                        <span class="text-sm font-medium text-gray-700">
                            ${dia.charAt(0).toUpperCase() + dia.slice(1)} - Módulo ${numModulo}
                        </span>
                    </div>
                    <button type="button" class="text-red-600 hover:text-red-800 delete-class-btn" data-id-modulo="${idModulo}">
                        <i class="fa-solid fa-times"></i>
                    </button>
                `;
                
                div.querySelector('.delete-class-btn').addEventListener('click', () => {
                    delete seleccionados[idModulo];
                    actualizarVista();
                });

                selectedClasses.appendChild(div);
            });

            // Actualizar input oculto
            const planificaciones = Object.values(seleccionados).map(item => ({
                id_modulo: item.id_modulo,
                id_espacio: item.id_espacio
            }));
            document.getElementById('planificaciones-input').value = JSON.stringify(planificaciones);
        }

        // Cargar salas disponibles
        async function cargarSalasDisponibles() {
            const modulosArray = Object.keys(seleccionados);
            
            try {
                const response = await fetch((window.ClasesTemporalesEditConfig?.salasDisponiblesUrl ?? '/api/clases-temporales/salas-disponibles'), {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({ modulos: modulosArray })
                });

                const data = await response.json();
                if (data.success) {
                    actualizarSelectSalas(data.salas);
                }
            } catch (error) {
                console.error('Error cargando salas:', error);
            }
        }

        // Actualizar select de salas
        function actualizarSelectSalas(salas) {
            const select = document.getElementById('salas-select');
            const currentValue = select.value;
            
            select.innerHTML = '<option value="">-- Seleccione una sala --</option>';
            
            salas.forEach(sala => {
                const option = document.createElement('option');
                option.value = sala.id_espacio;
                option.textContent = `[${sala.id_espacio}] ${sala.nombre_espacio} (${sala.piso_nombre}) - Ocupación: ${sala.porcentaje_planificacion}%`;
                option.dataset.sala = JSON.stringify(sala);
                select.appendChild(option);
            });

            if (currentValue && salas.some(s => s.id_espacio == currentValue)) {
                select.value = currentValue;
                mostrarDetallesSala();
            }
        }

        // Event listener para cambio de sala
        document.getElementById('salas-select').addEventListener('change', () => {
            if (document.getElementById('salas-select').value) {
                mostrarDetallesSala();
            } else {
                document.getElementById('sala-details').classList.add('hidden');
            }
        });

        // Mostrar detalles de la sala
        function mostrarDetallesSala() {
            const select = document.getElementById('salas-select');
            const selectedOption = select.options[select.selectedIndex];
            
            if (!selectedOption.dataset.sala) {
                document.getElementById('sala-details').classList.add('hidden');
                return;
            }

            const sala = JSON.parse(selectedOption.dataset.sala);
            
            document.getElementById('sala-nombre').textContent = sala.nombre_espacio;
            document.getElementById('sala-piso').textContent = sala.piso_nombre || 'N/A';
            document.getElementById('sala-tipo').textContent = sala.tipo_espacio || 'N/A';
            document.getElementById('sala-capacidad').textContent = sala.capacidad_maxima;
            document.getElementById('sala-ocupacion-pct').textContent = sala.porcentaje_planificacion + '%';
            document.getElementById('sala-ocupacion-bar').style.width = sala.porcentaje_planificacion + '%';
            document.getElementById('selected-sala-id').value = sala.id_espacio;

            document.getElementById('sala-details').classList.remove('hidden');

            // Actualizar planificaciones con la sala seleccionada
            Object.keys(seleccionados).forEach(idModulo => {
                seleccionados[idModulo].id_espacio = sala.id_espacio;
            });

            actualizarListaClases();
        }

        // Limpiar salas
        function limpiarSalas() {
            document.getElementById('salas-select').innerHTML = '<option value="">-- Seleccione una sala --</option>';
            document.getElementById('sala-details').classList.add('hidden');
            document.getElementById('selected-sala-id').value = '';
        }

        // Filtro de turno - REMOVIDO
        // Mostrar todos los módulos sin filtrar

        // Confirmación de eliminación
        function confirmDelete() {
            document.getElementById('delete-modal').classList.remove('hidden');
        }

        function closeDeleteModal() {
            document.getElementById('delete-modal').classList.add('hidden');
        }

        // Submit del formulario
        document.getElementById('formProfesorColaborador').addEventListener('submit', (e) => {
            if (Object.keys(seleccionados).length === 0) {
                e.preventDefault();
                alert('Debe seleccionar al menos una clase temporal');
                return;
            }

            const planificaciones = Object.values(seleccionados).map(item => ({
                id_modulo: item.id_modulo,
                id_espacio: item.id_espacio
            }));

            console.log('Planificaciones a guardar:', planificaciones);
            console.log('Nombre asignatura:', document.getElementById('nombre_asignatura').value);
            console.log('Profesor:', document.getElementById('run_profesor_colaborador').value);
            
            document.getElementById('planificaciones-input').value = JSON.stringify(planificaciones);
        });

        // Autocomplete de Asignaturas
        const asignaturaInput = document.getElementById('asignatura_search');
        const asignaturaList = document.getElementById('asignatura-list');
        const idAsignaturaInput = document.getElementById('id_asignatura');

        if (asignaturaInput) {
            asignaturaInput.addEventListener('input', (e) => {
                const query = e.target.value.toLowerCase().trim();
                asignaturaList.innerHTML = '';

                if (query.length === 0) {
                    asignaturaList.classList.add('hidden');
                    return;
                }

                const filtered = asignaturas.filter(a => 
                    a.nombre_asignatura.toLowerCase().includes(query) ||
                    (a.codigo_asignatura && a.codigo_asignatura.toLowerCase().includes(query))
                );

                if (filtered.length === 0) {
                    asignaturaList.classList.remove('hidden');
                    asignaturaList.innerHTML = '<div class="px-4 py-3 text-sm text-gray-500">No se encontraron asignaturas</div>';
                    return;
                }

                asignaturaList.classList.remove('hidden');
                filtered.slice(0, 10).forEach(a => {
                    const div = document.createElement('div');
                    div.className = 'px-4 py-2 cursor-pointer hover:bg-gray-100 text-sm';
                    div.innerHTML = `
                        <div class="font-medium text-gray-900">${a.nombre_asignatura}</div>
                        <div class="text-xs text-gray-500">${a.codigo_asignatura || 'Sin código'}</div>
                    `;
                    div.addEventListener('click', () => {
                        asignaturaInput.value = a.nombre_asignatura;
                        idAsignaturaInput.value = a.id_asignatura;
                        asignaturaList.classList.add('hidden');
                    });
                    asignaturaList.appendChild(div);
                });
            });

            // Cerrar dropdown al hacer click fuera
            document.addEventListener('click', (e) => {
                if (!asignaturaInput.contains(e.target) && !asignaturaList.contains(e.target)) {
                    asignaturaList.classList.add('hidden');
                }
            });

            // Permitir limpiar la búsqueda
            asignaturaInput.addEventListener('focus', (e) => {
                if (e.target.value.length > 0) {
                    const query = e.target.value.toLowerCase().trim();
                    const filtered = asignaturas.filter(a => 
                        a.nombre_asignatura.toLowerCase().includes(query)
                    );
                    if (filtered.length > 0) {
                        asignaturaList.classList.remove('hidden');
                    }
                }
            });
        }

        // Inicializar
        document.addEventListener('DOMContentLoaded', () => {
            initializarSeleccionados();
            actualizarVista();
        });
