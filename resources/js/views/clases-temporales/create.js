    document.addEventListener('DOMContentLoaded', function() {
        const profesores = window.ClasesTemporalesCreateConfig?.profesores ?? [];
        let currentStep = 1;
        const totalSteps = 4;
        let selectedModulos = new Set();
        let selectedProfesor = null;
        let selectedSala = null;
        let currentTurno = 'diurno';
        
        const runSearchInput = document.getElementById('run_search');
        const suggestionsContainer = document.getElementById('suggestions-list');
        const searchResults = document.getElementById('search-results');
        const nuevoProfesorForm = document.getElementById('nuevo-profesor-form');
        const btnNext = document.getElementById('btn-next');
        const btnPrev = document.getElementById('btn-prev');
        const btnSubmit = document.getElementById('btn-submit');
        const form = document.getElementById('formProfesorColaborador');
        const turnoSelect = document.getElementById('turno-select');

        function showStep(step) {
            for(let i = 1; i <= totalSteps; i++) {
                document.getElementById('step-' + i).classList.add('hidden');
            }
            document.getElementById('step-' + step).classList.remove('hidden');
            updateProgressIndicators(step);
            updateButtons(step);
            currentStep = step;
            if(step === 3) {
                filterTurnoRows();
            } else if(step === 4) {
                loadSalasDisponibles();
            }
        }

        function updateProgressIndicators(step) {
            for(let i = 1; i <= totalSteps; i++) {
                const indicator = document.getElementById('step-' + i + '-indicator');
                const line = document.getElementById('line-' + i);
                indicator.classList.remove('bg-red-600', 'bg-gray-300');
                
                if(i < step) {
                    indicator.classList.add('bg-red-600');
                    if(line) {
                        line.classList.remove('bg-gray-300');
                        line.classList.add('bg-red-600');
                    }
                } else if(i === step) {
                    indicator.classList.add('bg-red-600');
                    if(line) {
                        line.classList.remove('bg-red-600');
                        line.classList.add('bg-gray-300');
                    }
                } else {
                    indicator.classList.add('bg-gray-300');
                }
            }
        }

        function updateButtons(step) {
            btnPrev.classList.toggle('hidden', step === 1);
            btnNext.classList.toggle('hidden', step === totalSteps);
            btnSubmit.classList.toggle('hidden', step !== totalSteps);
            checkStep();
        }

        function filterTurnoRows() {
            const rows = document.querySelectorAll('.turno-row');
            rows.forEach(function(row) {
                const rowTurno = row.dataset.turno.trim();
                if(rowTurno === currentTurno) {
                    row.classList.remove('hidden');
                } else {
                    row.classList.add('hidden');
                }
            });
        }

        if(turnoSelect) {
            turnoSelect.addEventListener('change', function() {
                currentTurno = this.value;
                filterTurnoRows();
                checkStep();
            });
        }

        function checkStep() {
            let isValid = false;
            if(currentStep === 1) {
                isValid = (runSearchInput.value.trim().length > 0) && (selectedProfesor !== null || !nuevoProfesorForm.classList.contains('hidden'));
            } else if(currentStep === 2) {
                const nombre = document.getElementById('nombre_asignatura').value.trim();
                const inicio = document.getElementById('fecha_inicio').value;
                const termino = document.getElementById('fecha_termino').value;
                isValid = nombre && inicio && termino;
            } else if(currentStep === 3) {
                isValid = document.querySelectorAll('.modulo-cell.selected').length > 0;
            } else if(currentStep === 4) {
                isValid = selectedSala !== null;
            }
            
            if(currentStep === totalSteps) {
                btnSubmit.disabled = !isValid;
            } else {
                btnNext.disabled = !isValid;
            }
        }

        runSearchInput.addEventListener('input', function() {
            // Only allow numbers
            this.value = this.value.replace(/[^0-9]/g, '');
            
            const term = this.value.trim();
            suggestionsContainer.classList.add('hidden');
            searchResults.classList.add('hidden');
            nuevoProfesorForm.classList.add('hidden');
            selectedProfesor = null;

            if(term.length === 0) { 
                checkStep(); 
                return; 
            }

            const matches = profesores.filter(function(p) {
                return p.run_profesor.toString().includes(term) || p.name.toLowerCase().includes(term.toLowerCase());
            });
            
            if(matches.length > 0) {
                let html = '';
                matches.forEach(function(p) {
                    html += '<div class="px-4 py-3 hover:bg-gray-100 cursor-pointer border-b suggestion-item" data-run="' + p.run_profesor + '" data-name="' + p.name + '" data-email="' + (p.email || '') + '"><div class="font-semibold">' + p.name + '</div><div class="text-sm text-gray-600">RUN: ' + p.run_profesor + '</div></div>';
                });
                suggestionsContainer.innerHTML = html;
                suggestionsContainer.classList.remove('hidden');
                
                document.querySelectorAll('.suggestion-item').forEach(function(el) {
                    el.addEventListener('click', function() {
                        selectedProfesor = { 
                            run_profesor: this.dataset.run, 
                            name: this.dataset.name, 
                            email: this.dataset.email 
                        };
                        runSearchInput.value = this.dataset.run;
                        document.getElementById('run_profesor_colaborador').value = this.dataset.run;
                        document.getElementById('profesor_option').value = 'existente';
                        document.getElementById('profesor-info').innerHTML = '<p class="font-semibold">' + this.dataset.name + '</p><p class="text-xs">RUN: ' + this.dataset.run + '</p>';
                        searchResults.classList.remove('hidden');
                        suggestionsContainer.classList.add('hidden');
                        nuevoProfesorForm.classList.add('hidden');
                        checkStep();
                    });
                });
            } else if(term.length >= 7 && /^\d+$/.test(term)) {
                document.getElementById('nuevo_run').value = term;
                document.getElementById('profesor_option').value = 'nuevo';
                nuevoProfesorForm.classList.remove('hidden');
            }
            checkStep();
        });

        document.addEventListener('input', checkStep);

        btnNext.addEventListener('click', function() {
            if(validate(currentStep)) showStep(currentStep + 1);
        });

        btnPrev.addEventListener('click', function() {
            showStep(currentStep - 1);
        });

        const cells = document.querySelectorAll('.modulo-cell');
        let isSelecting = false;
        let dragStart = null;

        cells.forEach(function(cell) {
            cell.addEventListener('mousedown', function() {
                isSelecting = true;
                dragStart = cell;
                toggleCell(cell);
            });
            cell.addEventListener('mouseenter', function() {
                if(isSelecting && dragStart && dragStart.dataset.dia === this.dataset.dia) {
                    const start = parseInt(dragStart.dataset.modulo);
                    const end = parseInt(this.dataset.modulo);
                    const min = Math.min(start, end);
                    const max = Math.max(start, end);
                    cells.forEach(function(c) {
                        if(c.dataset.dia === dragStart.dataset.dia && parseInt(c.dataset.modulo) >= min && parseInt(c.dataset.modulo) <= max) {
                            if(!selectedModulos.has(c.dataset.idModulo)) {
                                selectedModulos.add(c.dataset.idModulo);
                                c.classList.add('selected');
                            }
                        }
                    });
                }
            });
        });

        document.addEventListener('mouseup', function() { 
            isSelecting = false; 
            updateHorariosInfo(); 
            checkStep(); 
        });

        function toggleCell(cell) {
            if(cell.dataset.disabled) return;
            const id = cell.dataset.idModulo;
            if(selectedModulos.has(id)) {
                selectedModulos.delete(id);
                cell.classList.remove('selected');
            } else {
                selectedModulos.add(id);
                cell.classList.add('selected');
            }
        }

        function updateHorariosInfo() {
            const container = document.getElementById('horarios-seleccionados-info');
            const instruccionesDiv = document.getElementById('instrucciones-info');
            
            if(selectedModulos.size === 0) { 
                container.innerHTML = '';
                if(instruccionesDiv) instruccionesDiv.style.display = 'block';
                return; 
            }
            
            // Ocultar instrucciones cuando hay selección
            if(instruccionesDiv) {
                instruccionesDiv.style.opacity = '0';
                instruccionesDiv.style.maxHeight = '0';
                instruccionesDiv.style.marginTop = '0';
                instruccionesDiv.style.marginBottom = '0';
                instruccionesDiv.style.overflow = 'hidden';
            }
            
            const byDay = {};
            selectedModulos.forEach(function(id) {
                const parts = id.split('.');
                const dia = parts[0];
                const num = parseInt(parts[1]);
                if(!byDay[dia]) byDay[dia] = [];
                byDay[dia].push(num);
            });
            
            let html = '<div class="p-4 bg-green-50 border border-green-200 rounded-lg"><h4 class="font-semibold text-green-800 mb-2">Horarios:</h4><ul class="list-disc list-inside">';
            const diaNames = { 'LU': 'Lunes', 'MA': 'Martes', 'MI': 'Miércoles', 'JU': 'Jueves', 'VI': 'Viernes' };
            Object.keys(byDay).forEach(function(dia) {
                const mods = byDay[dia].sort(function(a, b) { return a - b; });
                html += '<li class="text-green-700"><strong>' + diaNames[dia] + ':</strong> ' + mods.join(', ') + '</li>';
            });
            html += '</ul></div>';
            container.innerHTML = html;
        }

        function loadSalasDisponibles() {
            const container = document.getElementById('salas-disponibles-container');
            fetch('/api/clases-temporales/salas-disponibles', {
                method: 'POST',
                headers: { 
                    'Content-Type': 'application/json', 
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content 
                },
                body: JSON.stringify({ modulos: Array.from(selectedModulos) })
            })
            .then(function(response) { 
                console.log('Response status:', response.status);
                return response.json(); 
            })
            .then(function(data) {
                console.log('Salas data:', data);
                window.salasData = data.salas || [];
                window.salasDescartadas = data.salas_descartadas || [];
                renderSalas();
            })
            .catch(function(e) {
                console.error('Error al cargar salas:', e);
                container.innerHTML = '<div class="text-center py-8 text-red-500">Error al cargar salas: ' + e.message + '</div>';
            });
        }

        function renderSalas() {
            const container = document.getElementById('salas-disponibles-container');
            const mostrarEspecificas = document.getElementById('mostrar-salas-especificas')?.checked || false;
            const salas = window.salasData || [];
            
            // Filter salas
            const salasFiltradas = salas.filter(function(sala) {
                if (!mostrarEspecificas && sala.es_especifica) {
                    return false;
                }
                return true;
            });

            if(salasFiltradas.length > 0) {
                let html = '<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">';
                salasFiltradas.forEach(function(sala) {
                    const pisoInfo = sala.piso_nombre ? sala.piso_nombre : 'N/A';
                    html += '<div class="border border-gray-300 rounded-lg p-4 cursor-pointer hover:border-red-500 sala-card" data-sala="' + sala.id_espacio + '" data-especifica="' + (sala.es_especifica ? 'true' : 'false') + '" onclick="selectSala(this, \'' + sala.id_espacio + '\')">';
                    html += '<div class="mb-3">';
                    html += '<h4 class="font-semibold text-lg" style="color: #cd1627;">' + sala.id_espacio + '</h4>';
                    html += '<p class="text-xs text-gray-500">' + sala.nombre_espacio + '</p>';
                    html += '</div>';
                    html += '<div class="grid grid-cols-2 gap-2 text-sm">';
                    html += '<div><span class="text-gray-600">Piso:</span> <span class="font-medium">' + pisoInfo + '</span></div>';
                    html += '<div><span class="text-gray-600">Capacidad:</span> <span class="font-medium">' + sala.capacidad_maxima + '</span></div>';
                    html += '</div>';
                    html += '<div class="mt-3 pt-3 border-t border-gray-200">';
                    html += '<div class="flex justify-between items-center mb-2">';
                    html += '<span class="text-xs text-gray-600">Uso real:</span>';
                    html += '<div class="text-lg font-bold" style="color: ' + (sala.porcentaje_real < 50 ? '#22c55e' : sala.porcentaje_real < 80 ? '#eab308' : '#ef4444') + '">' + sala.porcentaje_real + '%</div>';
                    html += '</div>';
                    html += '<div class="flex justify-between items-center">';
                    html += '<span class="text-xs text-gray-600">Uso planificación:</span>';
                    html += '<div class="text-lg font-bold" style="color: ' + (sala.porcentaje_planificacion < 50 ? '#22c55e' : sala.porcentaje_planificacion < 80 ? '#eab308' : '#ef4444') + '">' + sala.porcentaje_planificacion + '%</div>';
                    html += '</div>';
                    html += '</div>';
                    html += '</div>';
                });
                html += '</div>';
                container.innerHTML = html;
            } else {
                container.innerHTML = '<div class="text-center py-8 text-gray-500">No hay salas disponibles con los filtros aplicados</div>';
            }
        }

        window.toggleSalasEspecificas = function() {
            renderSalas();
        };

        window.mostrarSalasDescartadas = function() {
            const modal = document.getElementById('modal-salas-descartadas');
            const content = document.getElementById('modal-descartadas-content');
            const salasDescartadas = window.salasDescartadas || [];
            
            if (salasDescartadas.length === 0) {
                content.innerHTML = '<p class="text-center text-gray-500 py-8">No hay salas descartadas</p>';
            } else {
                let html = '<div class="space-y-3">';
                salasDescartadas.forEach(function(sala) {
                    html += '<div class="border border-gray-300 rounded p-3 bg-gray-50">';
                    html += '<div class="flex justify-between items-start mb-2">';
                    html += '<div>';
                    html += '<h4 class="font-semibold" style="color: #cd1627;">' + sala.id_espacio + ' - ' + sala.nombre_espacio + '</h4>';
                    html += '<p class="text-xs text-gray-600">Piso: ' + (sala.piso_nombre || 'N/A') + ' | Capacidad: ' + sala.capacidad_maxima + '</p>';
                    html += '</div>';
                    html += '</div>';
                    
                    if (sala.conflictos && sala.conflictos.length > 0) {
                        html += '<div class="mt-2">';
                        html += '<p class="text-xs font-semibold text-gray-700 mb-1">Conflictos:</p>';
                        
                        // Agrupar conflictos por asignatura y día
                        let conflictosAgrupados = {};
                        sala.conflictos.forEach(function(conflicto) {
                            const key = conflicto.asignatura + '|' + conflicto.dia + '|' + conflicto.tipo;
                            if (!conflictosAgrupados[key]) {
                                conflictosAgrupados[key] = {
                                    asignatura: conflicto.asignatura,
                                    dia: conflicto.dia,
                                    tipo: conflicto.tipo,
                                    horas: []
                                };
                            }
                            conflictosAgrupados[key].horas.push({
                                inicio: conflicto.hora_inicio,
                                fin: conflicto.hora_fin
                            });
                        });
                        
                        html += '<div class="space-y-1">';
                        Object.values(conflictosAgrupados).forEach(function(grupo) {
                            const tipoLabel = grupo.tipo === 'Asignatura Regular' ? 'Asignatura Regular' : 'Asignatura Temporal';
                            const tipoColor = grupo.tipo === 'Asignatura Regular' ? 'bg-blue-100 text-blue-700' : 'bg-purple-100 text-purple-700';
                            
                            let horasText = grupo.horas.map(function(h) {
                                return h.inicio + ' - ' + h.fin;
                            }).join(', ');
                            
                            html += '<div class="text-xs p-1.5 bg-white border border-gray-200 rounded">';
                            html += '<p class="font-medium text-gray-800">' + grupo.asignatura + '</p>';
                            html += '<p class="text-gray-600 text-xs">' + grupo.dia + ' | ' + horasText + '</p>';
                            html += '<span class="inline-block px-2 py-0.5 rounded text-xs mt-1 ' + tipoColor + '">' + tipoLabel + '</span>';
                            html += '</div>';
                        });
                        html += '</div>';
                        html += '</div>';
                    }
                    
                    html += '</div>';
                });
                html += '</div>';
                content.innerHTML = html;
            }
            
            modal.style.display = 'block';
        };

        window.cerrarModalDescartadas = function() {
            document.getElementById('modal-salas-descartadas').style.display = 'none';
        };

        // Close modal on outside click
        document.getElementById('modal-salas-descartadas')?.addEventListener('click', function(e) {
            if (e.target === this) {
                cerrarModalDescartadas();
            }
        });

        window.selectSala = function(el, id) {
            document.querySelectorAll('.sala-card').forEach(function(c) {
                c.classList.remove('border-red-500', 'bg-red-50');
            });
            el.classList.add('border-red-500', 'bg-red-50');
            selectedSala = id;
            checkStep();
        };

        function validate(step) {
            if(step === 1) {
                if(!runSearchInput.value.trim()) { 
                    alert('Ingresa un RUN'); 
                    return false; 
                }
                if(!selectedProfesor && nuevoProfesorForm.classList.contains('hidden')) { 
                    alert('Selecciona o crea un profesor'); 
                    return false; 
                }
                if(!selectedProfesor && !nuevoProfesorForm.classList.contains('hidden')) {
                    if(!document.getElementById('nuevo_nombre').value.trim() || !document.getElementById('nuevo_email').value.trim()) {
                        alert('Completa todos los campos del nuevo profesor'); 
                        return false;
                    }
                }
            } else if(step === 2) {
                if(!document.getElementById('nombre_asignatura').value.trim() || !document.getElementById('fecha_inicio').value || !document.getElementById('fecha_termino').value || !document.getElementById('cantidad_inscritos').value) {
                    alert('Completa todos los campos'); 
                    return false;
                }
                if(parseInt(document.getElementById('cantidad_inscritos').value) < 1) {
                    alert('La cantidad de inscritos debe ser mayor a 0'); 
                    return false;
                }
                if(document.getElementById('fecha_inicio').value > document.getElementById('fecha_termino').value) {
                    alert('Fecha inicio debe ser menor a fecha término'); 
                    return false;
                }
            } else if(step === 3) {
                const selectedCells = document.querySelectorAll('.modulo-cell.selected').length;
                if(selectedCells === 0) { 
                    alert('Selecciona al menos un horario'); 
                    return false; 
                }
            } else if(step === 4) {
                if(!selectedSala) { 
                    alert('Selecciona una sala'); 
                    return false; 
                }
            }
            return true;
        }

        form.addEventListener('submit', function(e) {
            e.preventDefault();
            if(!validate(4)) return;
            const selectedCells = document.querySelectorAll('.modulo-cell.selected');
            const planificaciones = Array.from(selectedCells).map(function(cell) {
                return { id_modulo: cell.dataset.idModulo, id_espacio: selectedSala };
            });
            document.getElementById('planificaciones-input').value = JSON.stringify(planificaciones);
            form.submit();
        });

        showStep(1);
    });
