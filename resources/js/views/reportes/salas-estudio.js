        let vetoActual = {};
        let grupoActual = {};
        let vetoEditando = null;

        // Vetar individual
        function vetarIndividual(run, nombre, idReserva) {
            vetoActual = { run, nombre, idReserva };
            document.getElementById('vetoRun').textContent = run;
            document.getElementById('vetoNombre').textContent = nombre;
            document.getElementById('vetoObservacion').value = '';
            document.getElementById('modalVetarIndividual').classList.remove('hidden');
            document.getElementById('modalVetarIndividual').classList.add('flex');
        }

        function cerrarModalVetar() {
            document.getElementById('modalVetarIndividual').classList.add('hidden');
            document.getElementById('modalVetarIndividual').classList.remove('flex');
        }

        async function confirmarVetoIndividual() {
            const observacion = document.getElementById('vetoObservacion').value.trim();
            
            if (!observacion) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Campo requerido',
                    text: 'Debe ingresar el motivo del veto',
                    confirmButtonColor: '#D12420'
                });
                return;
            }

            try {
                const response = await fetch('/api/sala-estudio/vetar-individual', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        run: vetoActual.run,
                        observacion: observacion,
                        id_reserva: vetoActual.idReserva
                    })
                });

                const data = await response.json();

                if (data.success) {
                    await Swal.fire({
                        icon: 'success',
                        title: 'Veto aplicado',
                        text: 'Usuario vetado correctamente',
                        confirmButtonColor: '#10B981'
                    });
                    cerrarModalVetar();
                    location.reload();
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: data.message,
                        confirmButtonColor: '#D12420'
                    });
                }
            } catch (error) {
                console.error('Error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Error al aplicar el veto',
                    confirmButtonColor: '#D12420'
                });
            }
        }

        // Vetar grupo
        function vetarGrupo(idReserva, sala, grupoNum) {
            grupoActual = { idReserva, sala, grupoNum };
            document.getElementById('grupoInfo').textContent = `Grupo #${grupoNum} de ${sala}`;
            document.getElementById('vetoGrupoObservacion').value = '';
            document.getElementById('modalVetarGrupo').classList.remove('hidden');
            document.getElementById('modalVetarGrupo').classList.add('flex');
        }

        function cerrarModalVetarGrupo() {
            document.getElementById('modalVetarGrupo').classList.add('hidden');
            document.getElementById('modalVetarGrupo').classList.remove('flex');
        }

        async function confirmarVetoGrupo() {
            const observacion = document.getElementById('vetoGrupoObservacion').value.trim();
            
            if (!observacion) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Campo requerido',
                    text: 'Debe ingresar el motivo del veto grupal',
                    confirmButtonColor: '#D12420'
                });
                return;
            }

            try {
                const response = await fetch('/api/sala-estudio/vetar-grupo', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        id_reserva: grupoActual.idReserva,
                        observacion: observacion
                    })
                });

                const data = await response.json();

                if (data.success) {
                    const mensaje = `${data.vetados} usuarios vetados${data.ya_vetados > 0 ? '<br>' + data.ya_vetados + ' ya estaban vetados' : ''}`;
                    await Swal.fire({
                        icon: 'success',
                        title: 'Veto grupal aplicado',
                        html: mensaje,
                        confirmButtonColor: '#10B981'
                    });
                    cerrarModalVetarGrupo();
                    location.reload();
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: data.message,
                        confirmButtonColor: '#D12420'
                    });
                }
            } catch (error) {
                console.error('Error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Error al aplicar el veto grupal',
                    confirmButtonColor: '#D12420'
                });
            }
        }

        // Mostrar lista de vetados
        async function mostrarVetados() {
            cambiarTab('vetados');
        }

        function cerrarModalVetados() {
            // Ya no se usa - removido
        }

        async function filtrarVetados(estado) {
            // Actualizar botones activos
            document.querySelectorAll('.filter-button').forEach(btn => {
                btn.classList.remove('active', 'bg-red-600', 'text-white');
                btn.classList.add('bg-gray-200', 'text-gray-700', 'dark:bg-gray-700', 'dark:text-gray-300');
            });
            
            const activeButton = estado === 'activo' ? 'filter-activo' : (estado === 'liberado' ? 'filter-liberado' : 'filter-todos');
            const btn = document.getElementById(activeButton);
            btn.classList.add('active', 'bg-red-600', 'text-white');
            btn.classList.remove('bg-gray-200', 'text-gray-700', 'dark:bg-gray-700', 'dark:text-gray-300');
            
            await cargarVetados(estado, 'listaVetados');
        }

        async function cargarVetados(estado = '', containerId = 'listaVetados') {
            try {
                const url = estado ? `/api/sala-estudio/vetos?estado=${estado}` : '/api/sala-estudio/vetos';
                console.log('Cargando vetos desde:', url);
                
                const response = await fetch(url);
                console.log('Response status:', response.status);
                
                const data = await response.json();
                console.log('Data recibida:', data);

                const container = document.getElementById(containerId);
                
                if (data.success && data.vetos.length > 0) {
                    container.innerHTML = data.vetos.map(veto => `
                        <div class="p-4 border rounded-lg ${veto.estado === 'activo' ? 'bg-red-50 border-red-200' : 'bg-green-50 border-green-200'} dark:bg-gray-700">
                            <div class="flex items-start justify-between">
                                <div class="flex-1">
                                    <div class="flex items-center gap-2 mb-2">
                                        <span class="px-2 py-1 text-xs font-semibold rounded ${veto.estado === 'activo' ? 'bg-red-200 text-red-800' : 'bg-green-200 text-green-800'}">
                                            ${veto.estado === 'activo' ? 'ACTIVO' : 'LIBERADO'}
                                        </span>
                                        <span class="px-2 py-1 text-xs font-semibold rounded ${veto.tipo_veto === 'grupal' ? 'bg-orange-200 text-orange-800' : 'bg-blue-200 text-blue-800'}">
                                            ${veto.tipo_veto === 'grupal' ? 'Grupal' : 'Individual'}
                                        </span>
                                    </div>
                                    <p class="font-semibold text-gray-900 dark:text-white">
                                        ${veto.solicitante?.nombre || 'N/A'} (RUN: ${veto.run_vetado})
                                    </p>
                                    <p class="mt-2 text-sm text-gray-700 dark:text-gray-300">
                                        <strong>Motivo:</strong> ${veto.observacion}
                                    </p>
                                    <div class="mt-2 text-xs text-gray-600 dark:text-gray-400">
                                        <p>Vetado por: ${veto.vetado_por || 'Sistema'} - ${new Date(veto.fecha_veto).toLocaleString('es-CL')}</p>
                                        ${veto.estado === 'liberado' ? `<p>Liberado por: ${veto.liberado_por || 'Sistema'} - ${new Date(veto.fecha_liberacion).toLocaleString('es-CL')}</p>` : ''}
                                    </div>
                                </div>
                                ${veto.estado === 'activo' ? `
                                <div class="flex gap-1 ml-2">
                                    <button onclick="editarVeto(${veto.id}, '${veto.observacion}')" 
                                            class="px-2 py-1 text-xs text-white transition-colors bg-blue-600 rounded hover:bg-blue-700"
                                            title="Editar observación">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button onclick="liberarVeto(${veto.id})" 
                                            class="px-2 py-1 text-xs text-white transition-colors bg-green-600 rounded hover:bg-green-700"
                                            title="Liberar veto">
                                        <i class="fas fa-unlock"></i>
                                    </button>
                                </div>
                                ` : ''}
                            </div>
                        </div>
                    `).join('');
                } else {
                    container.innerHTML = `
                        <div class="p-8 text-center text-gray-500">
                            <i class="mb-2 text-4xl fas fa-inbox"></i>
                            <p>No hay vetos ${estado ? estado + 's' : 'registrados'}</p>
                        </div>
                    `;
                }
            } catch (error) {
                console.error('Error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Error al cargar la lista de vetados',
                    confirmButtonColor: '#D12420'
                });
            }
        }

        // Liberar veto
        async function liberarVeto(id) {
            const result = await Swal.fire({
                icon: 'question',
                title: '¿Liberar veto?',
                text: '¿Está seguro de liberar este veto?',
                showCancelButton: true,
                confirmButtonText: 'Sí, liberar',
                cancelButtonText: 'Cancelar',
                confirmButtonColor: '#10B981',
                cancelButtonColor: '#8D8D8D'
            });

            if (!result.isConfirmed) {
                return;
            }

            try {
                const response = await fetch(`/api/sala-estudio/veto/${id}/liberar`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                });

                const data = await response.json();

                if (data.success) {
                    await Swal.fire({
                        icon: 'success',
                        title: 'Veto liberado',
                        text: 'Veto liberado correctamente',
                        confirmButtonColor: '#10B981',
                        timer: 2000
                    });
                    await cargarVetados('activo', 'listaVetados');
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: data.message,
                        confirmButtonColor: '#D12420'
                    });
                }
            } catch (error) {
                console.error('Error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Error al liberar el veto',
                    confirmButtonColor: '#D12420'
                });
            }
        }

        // Editar veto
        function editarVeto(id, observacionActual) {
            vetoEditando = id;
            document.getElementById('editarObservacion').value = observacionActual;
            document.getElementById('modalEditarVeto').classList.remove('hidden');
            document.getElementById('modalEditarVeto').classList.add('flex');
        }

        function cerrarModalEditar() {
            document.getElementById('modalEditarVeto').classList.add('hidden');
            document.getElementById('modalEditarVeto').classList.remove('flex');
            vetoEditando = null;
        }

        async function confirmarEdicion() {
            const nuevaObservacion = document.getElementById('editarObservacion').value.trim();
            
            if (!nuevaObservacion) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Campo requerido',
                    text: 'Debe ingresar una observación',
                    confirmButtonColor: '#D12420'
                });
                return;
            }

            try {
                const response = await fetch(`/api/sala-estudio/veto/${vetoEditando}`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        observacion: nuevaObservacion
                    })
                });

                const data = await response.json();

                if (data.success) {
                    await Swal.fire({
                        icon: 'success',
                        title: 'Actualizado',
                        text: 'Observación actualizada correctamente',
                        confirmButtonColor: '#10B981',
                        timer: 2000
                    });
                    cerrarModalEditar();
                    await cargarVetados('activo', 'listaVetados');
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: data.message,
                        confirmButtonColor: '#D12420'
                    });
                }
            } catch (error) {
                console.error('Error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Error al actualizar la observación',
                    confirmButtonColor: '#D12420'
                });
            }
        }

        // Cambiar entre vistas
        function cambiarVista(vista) {
            // Ocultar todas las vistas
            document.querySelectorAll('.vista-content').forEach(v => {
                v.classList.add('hidden');
            });

            // Remover estado activo de todos los pills
            document.querySelectorAll('.pill-button').forEach(btn => {
                btn.classList.remove('active', 'bg-light-cloud-blue', 'text-white');
                btn.classList.add('bg-gray-200', 'text-gray-700', 'dark:bg-gray-700', 'dark:text-gray-300');
            });

            // Mostrar vista seleccionada
            document.getElementById(`vista-${vista}`).classList.remove('hidden');

            // Activar pill seleccionado
            const activeBtn = document.getElementById(`pill-${vista}`);
            activeBtn.classList.add('active', 'bg-light-cloud-blue', 'text-white');
            activeBtn.classList.remove('bg-gray-200', 'text-gray-700', 'dark:bg-gray-700', 'dark:text-gray-300');

            // Si cambia a vista de vetados, cargar datos
            if (vista === 'vetados') {
                filtrarVetados('activo');
            }
        }

        // Exportar vetados a Excel
        async function exportarVetados() {
            const estadoActual = document.querySelector('.filter-button.active').id.replace('filter-', '');
            
            try {
                const url = estadoActual ? `/api/sala-estudio/vetos/export?estado=${estadoActual}` : '/api/sala-estudio/vetos/export';
                
                const response = await fetch(url, {
                    method: 'GET',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                });

                if (response.ok) {
                    const blob = await response.blob();
                    const downloadUrl = window.URL.createObjectURL(blob);
                    const a = document.createElement('a');
                    a.href = downloadUrl;
                    a.download = `usuarios-vetados-${estadoActual || 'todos'}-${new Date().toISOString().split('T')[0]}.xlsx`;
                    document.body.appendChild(a);
                    a.click();
                    window.URL.revokeObjectURL(downloadUrl);
                    a.remove();

                    Swal.fire({
                        icon: 'success',
                        title: 'Exportado',
                        text: 'El archivo se ha descargado correctamente',
                        confirmButtonColor: '#10B981',
                        timer: 2000
                    });
                } else {
                    throw new Error('Error en la descarga');
                }
            } catch (error) {
                console.error('Error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Error al exportar el reporte',
                    confirmButtonColor: '#D12420'
                });
            }
        }

        // Cargar vetos activos al cargar la página
        document.addEventListener('DOMContentLoaded', function() {
            // No cargar automáticamente, solo cuando se acceda a la vista
        });

Object.assign(window, {
    cambiarVista,
    vetarIndividual,
    exportarVetados,
    filtrarVetados,
    cerrarModalVetar,
    confirmarVetoIndividual,
    cerrarModalVetarGrupo,
    confirmarVetoGrupo,
    cerrarModalEditar,
    confirmarEdicion,
    editarVeto,
    liberarVeto,
});
