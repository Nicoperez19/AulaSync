<div class="p-6" wire:poll.30s>

    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Estadísticas de Clases No Realizadas</h1>
    </div>

    <!-- Estadísticas Generales -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <div class="stat-card bg-blue-50 border border-blue-200 rounded-lg p-4">
            <div class="text-blue-600 text-sm font-medium">Total</div>
            <div class="text-2xl font-bold text-blue-900">{{ $estadisticas['total'] }}</div>
        </div>
        <div class="stat-card bg-red-50 border border-red-200 rounded-lg p-4">
            <div class="text-red-600 text-sm font-medium">Clases No Realizadas</div>
            <div class="text-2xl font-bold text-red-900">{{ $estadisticas['no_realizadas'] }}</div>
        </div>
        <div class="stat-card bg-green-50 border border-green-200 rounded-lg p-4">
            <div class="text-green-600 text-sm font-medium">Justificados</div>
            <div class="text-2xl font-bold text-green-900">{{ $estadisticas['justificados'] }}</div>
        </div>
    </div>

    <!-- Contenido Principal: Filtros a la izquierda, Tabla a la derecha -->
    <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
        
        <!-- Panel de Filtros (Izquierda) -->
        <div class="lg:col-span-1">
            <div class="filter-panel bg-white rounded-lg shadow p-4 sticky top-4">
                <h3 class="text-lg font-medium text-gray-900 mb-4">
                    <i class="fas fa-filter mr-2 icon-animate"></i>Filtros
                </h3>

                <!-- Búsqueda -->
                <div class="mb-4">
                    <label for="search" class="block text-sm font-medium text-gray-700 mb-2">Buscar</label>
                    <div class="relative">
                        <input type="text" 
                               wire:model.live="search" 
                               id="search"
                               class="w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                               placeholder="Profesor, Asignatura...">
                        <i class="fas fa-search absolute left-3 top-3 text-gray-400"></i>
                    </div>
                </div>

                <!-- Estado -->
                <div class="mb-4">
                    <label for="estado" class="block text-sm font-medium text-gray-700 mb-2">Estado</label>
                    <select wire:model.live="estado" 
                            id="estado"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                        <option value="">Todos</option>
                        <option value="no_realizada">Clase no realizada</option>
                        <option value="justificado">Justificado</option>
                    </select>
                </div>

                <!-- Período -->
                <div class="mb-4">
                    <label for="periodo" class="block text-sm font-medium text-gray-700 mb-2">Período</label>
                    <input type="text" 
                           wire:model.live="periodo" 
                           id="periodo"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                           placeholder="2024-1">
                </div>

                <!-- Fecha Inicio -->
                <div class="mb-4">
                    <label for="fecha_inicio" class="block text-sm font-medium text-gray-700 mb-2">Fecha Inicio</label>
                    <input type="date" 
                           wire:model.live="fecha_inicio" 
                           id="fecha_inicio"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                </div>

                <!-- Fecha Fin -->
                <div class="mb-4">
                    <label for="fecha_fin" class="block text-sm font-medium text-gray-700 mb-2">Fecha Fin</label>
                    <input type="date" 
                           wire:model.live="fecha_fin" 
                           id="fecha_fin"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                </div>

                <!-- Botón para limpiar filtros -->
                <button wire:click="limpiarFiltros" 
                        class="w-full mt-4 px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 border border-gray-300 rounded-md hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500">
                    <i class="fas fa-times mr-2"></i>Limpiar Filtros
                </button>
            </div>
        </div>

        <!-- Tabla (Derecha) -->
        <div class="lg:col-span-3">
            
            <!-- Mensaje Flash -->
            @if (session()->has('message'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                    {{ session('message') }}
                </div>
            @endif

            <div class="bg-white shadow rounded-lg overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer w-24" 
                                    wire:click="sortBy('fecha_clase')">
                                    Fecha
                                    @if($sortField === 'fecha_clase')
                                        <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>
                                    @endif
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-32">Profesor</th>
                                <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-40">Asignatura</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Espacio</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Módulo</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer"
                                    wire:click="sortBy('estado')">
                                    Estado
                                    @if($sortField === 'estado')
                                        <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>
                                    @endif
                                </th>
                                <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-28">Detección</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-32 sticky right-0 bg-gray-50">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($clasesNoRealizadas as $clase)
                                <tr class="table-row hover:bg-gray-50">
                                    <td class="px-3 py-4 text-sm text-gray-900 w-24">
                                        {{ $clase->fecha_clase->format('d/m/Y') }}
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-900 w-32">
                                        <div class="break-words">{{ $clase->profesor->name ?? 'N/A' }}</div>
                                    </td>
                                    <td class="px-3 py-4 text-sm text-gray-900 w-40">
                                        <div class="break-words">
                                            <div class="font-medium">{{ $clase->asignatura->nombre_asignatura ?? 'N/A' }}</div>
                                            <div class="text-xs text-gray-500">{{ $clase->asignatura->codigo_asignatura ?? '' }}</div>
                                        </div>
                                    </td>
                                    <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ $clase->id_espacio }}
                                    </td>
                                    <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ preg_replace('/^[A-Z]{2}\./', '', $clase->id_modulo) }}
                                    </td>
                                    <td class="px-4 py-4 whitespace-nowrap">
                                        <span class="px-2 py-1 text-xs font-semibold rounded-full
                                            {{ $clase->estado === 'no_realizada' ? 'estado-no-realizada' : 'estado-justificado' }}">
                                            {{ $clase->estado === 'no_realizada' ? 'Clase no realizada' : 'Justificado' }}
                                        </span>
                                    </td>
                                    <td class="px-3 py-4 text-sm text-gray-500 w-28">
                                        <div class="break-words">
                                            <div>{{ $clase->hora_deteccion->format('d/m/Y') }}</div>
                                            <div class="text-xs">{{ $clase->hora_deteccion->format('H:i') }}</div>
                                        </div>
                                    </td>
                                    <td class="px-4 py-4 text-sm font-medium w-32 sticky right-0 bg-white">
                                        <div class="flex space-x-1">
                                            @if($clase->estado === 'no_realizada')
                                                <div class="custom-tooltip">
                                                    <button wire:click="showReagendarModal({{ $clase->id }})" 
                                                            class="action-button reagendar p-1"
                                                            title="Reagendar Clase">
                                                        <i class="fas fa-calendar-plus icon-animate text-xs"></i>
                                                    </button>
                                                    <span class="tooltip-text">Reagendar</span>
                                                </div>
                                            @endif
                                            <div class="custom-tooltip">
                                                <button wire:click="showEditModal({{ $clase->id }})" 
                                                        class="action-button editar p-1"
                                                        title="Editar">
                                                    <i class="fas fa-edit icon-animate text-xs"></i>
                                                </button>
                                                <span class="tooltip-text">Editar</span>
                                            </div>
                                            <div class="custom-tooltip">
                                                <button wire:click="showDeleteModal({{ $clase->id }})" 
                                                        class="action-button eliminar p-1"
                                                        title="Eliminar">
                                                    <i class="fas fa-trash icon-animate text-xs"></i>
                                                </button>
                                                <span class="tooltip-text">Eliminar</span>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="px-6 py-12 text-center text-gray-500">
                                        <div class="flex flex-col items-center">
                                            <i class="fas fa-calendar-times text-4xl text-gray-300 mb-4"></i>
                                            <p class="text-lg font-medium">No se encontraron registros</p>
                                            <p class="text-sm">No hay clases no realizadas con los filtros aplicados.</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Paginación -->
                @if($clasesNoRealizadas->hasPages())
                    <div class="px-6 py-3 border-t border-gray-200">
                        {{ $clasesNoRealizadas->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>

</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    Livewire.on('show-edit-modal', (data) => {
        const clase = data[0];
        
        Swal.fire({
            title: '<strong>Editar Clase No Realizada</strong>',
            html: `
                <div class="text-left space-y-4">
                    <div class="bg-gray-50 p-3 rounded-lg mb-4">
                        <p><strong>Profesor:</strong> ${clase.profesor}</p>
                        <p><strong>Asignatura:</strong> ${clase.asignatura}</p>
                        <p><strong>Fecha:</strong> ${clase.fecha}</p>
                        <p><strong>Espacio:</strong> ${clase.espacio}</p>
                    </div>
                    
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Estado</label>
                        <select id="swal-estado" class="w-full p-2 border border-gray-300 rounded-md">
                            <option value="no_realizada" ${clase.estado === 'no_realizada' ? 'selected' : ''}>Clase no realizada</option>
                            <option value="justificado" ${clase.estado === 'justificado' ? 'selected' : ''}>Justificado</option>
                        </select>
                    </div>
                    
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Observaciones</label>
                        <textarea id="swal-observaciones" rows="4" class="w-full p-2 border border-gray-300 rounded-md resize-none" placeholder="Ingrese observaciones...">${clase.observaciones || ''}</textarea>
                    </div>
                </div>
            `,
            width: 600,
            showCancelButton: true,
            confirmButtonText: '<i class="fas fa-save"></i> Guardar',
            cancelButtonText: '<i class="fas fa-times"></i> Cancelar',
            confirmButtonColor: '#3B82F6',
            cancelButtonColor: '#6B7280',
            didOpen: () => {
                document.getElementById('swal-estado').focus();
            },
            preConfirm: () => {
                const estado = document.getElementById('swal-estado').value;
                const observaciones = document.getElementById('swal-observaciones').value;
                
                if (!estado) {
                    Swal.showValidationMessage('Por favor selecciona un estado');
                    return false;
                }
                
                if (observaciones.length > 1000) {
                    Swal.showValidationMessage('Las observaciones no pueden exceder 1000 caracteres');
                    return false;
                }
                
                return { estado, observaciones };
            }
        }).then((result) => {
            if (result.isConfirmed) {
                const { estado, observaciones } = result.value;
                window.Livewire.find('{{ $this->getId() }}').call('updateClase', clase.id, estado, observaciones);
            }
        });
    });

    Livewire.on('show-reagendar-modal', (data) => {
        const clase = data[0];
        
        // Debug: Verificar que los datos lleguen correctamente
        console.log('Datos completos recibidos:', clase);
        console.log('Espacios específicos:', clase.espacios);
        console.log('Tipo de espacios:', typeof clase.espacios);
        console.log('Es array?', Array.isArray(clase.espacios));
        
        // Crear las opciones de espacios de forma más robusta
        let espaciosOptions = '';
        if (clase.espacios && Array.isArray(clase.espacios) && clase.espacios.length > 0) {
            espaciosOptions = clase.espacios.map(espacio => {
                const id = espacio.id_espacio || 'Sin ID';
                const nombre = espacio.nombre_espacio || espacio.id_espacio || 'Sin nombre';
                const tipo = espacio.tipo_espacio ? ` - ${espacio.tipo_espacio}` : '';
                console.log('Procesando espacio:', { id, nombre, tipo });
                return `<option value="${id}">${nombre}${tipo} (${id})</option>`;
            }).join('');
            console.log('Opciones generadas:', espaciosOptions);
        } else {
            // Fallback con espacios de ejemplo
            console.warn('No se recibieron espacios válidos, usando fallback');
            espaciosOptions = `
                <option value="">Seleccionar espacio</option>
                <option value="A101">Aula A101 (A101)</option>
                <option value="A102">Aula A102 (A102)</option>
                <option value="B201">Aula B201 (B201)</option>
                <option value="LAB1">Laboratorio 1 (LAB1)</option>
            `;
        }

        Swal.fire({
            title: '<strong><i class="fas fa-calendar-plus"></i> Reagendar Clase</strong>',
            html: `
                <div class="text-left space-y-4">
                    <div class="bg-blue-50 p-4 rounded-lg mb-4">
                        <h4 class="font-semibold text-blue-900 mb-2">Clase Original</h4>
                        <p><strong>Profesor:</strong> ${clase.profesor}</p>
                        <p><strong>Asignatura:</strong> ${clase.asignatura}</p>
                        <p><strong>Fecha:</strong> ${clase.fecha_original}</p>
                        <p><strong>Espacio:</strong> ${clase.espacio_original}</p>
                        <p><strong>Módulo:</strong> ${clase.modulo_original}</p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Nueva Fecha <span class="text-red-500">*</span></label>
                            <input type="date" id="swal-nueva-fecha" class="w-full p-2 border border-gray-300 rounded-md" min="${new Date().toISOString().split('T')[0]}">
                        </div>
                        
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Nuevo Módulo <span class="text-red-500">*</span></label>
                            <select id="swal-nuevo-modulo" class="w-full p-2 border border-gray-300 rounded-md">
                                <option value="">Seleccionar módulo</option>
                                <option value="1">Módulo 1 (08:00-09:30)</option>
                                <option value="2">Módulo 2 (09:40-11:10)</option>
                                <option value="3">Módulo 3 (11:20-12:50)</option>
                                <option value="4">Módulo 4 (13:50-15:20)</option>
                                <option value="5">Módulo 5 (15:30-17:00)</option>
                                <option value="6">Módulo 6 (17:10-18:40)</option>
                                <option value="7">Módulo 7 (18:50-20:20)</option>
                                <option value="8">Módulo 8 (20:30-22:00)</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Nuevo Espacio <span class="text-red-500">*</span></label>
                        <select id="swal-nuevo-espacio" class="w-full p-2 border border-gray-300 rounded-md">
                            <option value="">Seleccionar espacio</option>
                            ${espaciosOptions}
                        </select>
                    </div>
                    
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Motivo del reagendamiento</label>
                        <textarea id="swal-motivo-reagendamiento" rows="3" class="w-full p-2 border border-gray-300 rounded-md resize-none" placeholder="Explique el motivo del reagendamiento..."></textarea>
                    </div>
                </div>
            `,
            width: 700,
            showCancelButton: true,
            confirmButtonText: '<i class="fas fa-calendar-check"></i> Reagendar',
            cancelButtonText: '<i class="fas fa-times"></i> Cancelar',
            confirmButtonColor: '#10B981',
            cancelButtonColor: '#6B7280',
            didOpen: () => {
                document.getElementById('swal-nueva-fecha').focus();
            },
            preConfirm: () => {
                const nuevaFecha = document.getElementById('swal-nueva-fecha').value;
                const nuevoEspacio = document.getElementById('swal-nuevo-espacio').value;
                const nuevoModulo = document.getElementById('swal-nuevo-modulo').value;
                const motivo = document.getElementById('swal-motivo-reagendamiento').value;
                
                if (!nuevaFecha) {
                    Swal.showValidationMessage('Por favor selecciona una fecha');
                    return false;
                }
                
                if (!nuevoEspacio) {
                    Swal.showValidationMessage('Por favor selecciona un espacio');
                    return false;
                }
                
                if (!nuevoModulo) {
                    Swal.showValidationMessage('Por favor selecciona un módulo');
                    return false;
                }
                
                const fechaSeleccionada = new Date(nuevaFecha);
                const hoy = new Date();
                hoy.setHours(0, 0, 0, 0);
                
                if (fechaSeleccionada < hoy) {
                    Swal.showValidationMessage('La fecha no puede ser anterior a hoy');
                    return false;
                }
                
                return { nuevaFecha, nuevoEspacio, nuevoModulo, motivo };
            }
        }).then((result) => {
            if (result.isConfirmed) {
                const { nuevaFecha, nuevoEspacio, nuevoModulo, motivo } = result.value;
                window.Livewire.find('{{ $this->getId() }}').call('reagendarClase', clase.id, nuevaFecha, nuevoEspacio, nuevoModulo, motivo);
            }
        });
    });

    Livewire.on('confirm-delete', (data) => {
        const clase = data[0];
        
        Swal.fire({
            title: '¿Eliminar registro?',
            html: `
                <div class="text-left">
                    <p class="mb-2"><strong>Profesor:</strong> ${clase.profesor}</p>
                    <p class="mb-2"><strong>Asignatura:</strong> ${clase.asignatura}</p>
                    <p class="mb-2"><strong>Fecha:</strong> ${clase.fecha}</p>
                    <br>
                    <p class="text-red-600"><strong>Esta acción no se puede deshacer</strong></p>
                </div>
            `,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: '<i class="fas fa-trash"></i> Sí, eliminar',
            cancelButtonText: '<i class="fas fa-times"></i> Cancelar',
            confirmButtonColor: '#DC2626',
            cancelButtonColor: '#6B7280',
        }).then((result) => {
            if (result.isConfirmed) {
                window.Livewire.find('{{ $this->getId() }}').call('confirmDelete', clase.id);
            }
        });
    });

    Livewire.on('show-success', (data) => {
        const message = data[0].message;
        
        Swal.fire({
            title: '¡Éxito!',
            text: message,
            icon: 'success',
            confirmButtonText: 'Entendido',
            confirmButtonColor: '#10B981',
            timer: 3000,
            timerProgressBar: true
        });
    });

    Livewire.on('show-info', (data) => {
        const message = data[0].message;
        
        // Toast notification para cambios automáticos
        Swal.fire({
            toast: true,
            position: 'top-end',
            icon: 'info',
            title: message,
            showConfirmButton: false,
            timer: 4000,
            timerProgressBar: true,
            didOpen: (toast) => {
                toast.addEventListener('mouseenter', Swal.stopTimer);
                toast.addEventListener('mouseleave', Swal.resumeTimer);
            }
        });
    });

    Livewire.on('show-error', (data) => {
        const message = data[0].message;
        
        Swal.fire({
            title: 'Error',
            text: message,
            icon: 'error',
            confirmButtonText: 'Entendido',
            confirmButtonColor: '#DC2626'
        });
    });
});
</script>
