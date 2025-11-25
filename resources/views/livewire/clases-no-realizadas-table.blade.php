<div class="p-6" wire:poll.60s>

    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Estadísticas de Clases No Realizadas</h1>
        
        <!-- Botones de Exportación -->
        <div class="flex gap-3">
            <button wire:click="exportarPDFSemanal" 
                    class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-md shadow-sm transition-colors duration-200">
                <i class="fas fa-file-pdf mr-2"></i>
                Exportar Semanal
            </button>
            <button wire:click="exportarPDFMensual" 
                    class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-md shadow-sm transition-colors duration-200">
                <i class="fas fa-file-pdf mr-2"></i>
                Exportar Mensual
            </button>
        </div>
    </div>

    <!-- Estadísticas Generales -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div class="stat-card bg-blue-50 border border-blue-200 rounded-lg p-4">
            <div class="text-blue-600 text-sm font-medium">Total</div>
            <div class="text-2xl font-bold text-blue-900">{{ $estadisticas['total'] }}</div>
        </div>
        <div class="stat-card bg-red-50 border border-red-200 rounded-lg p-4">
            <div class="text-red-600 text-sm font-medium">No Realizadas</div>
            <div class="text-2xl font-bold text-red-900">{{ $estadisticas['no_realizadas'] }}</div>
        </div>
        <div class="stat-card bg-yellow-50 border border-yellow-200 rounded-lg p-4">
            <div class="text-yellow-600 text-sm font-medium flex items-center gap-1">
                <i class="fas fa-clock text-xs"></i>
                Pendientes
            </div>
            <div class="text-2xl font-bold text-yellow-900">{{ $estadisticas['pendientes'] ?? 0 }}</div>
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
                               wire:model="search" 
                               id="search"
                               class="w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                               placeholder="Profesor, Asignatura...">
                        <i class="fas fa-search absolute left-3 top-3 text-gray-400"></i>
                    </div>
                </div>

                <!-- Estado -->
                <div class="mb-4">
                    <label for="estado" class="block text-sm font-medium text-gray-700 mb-2">Estado</label>
                    <select wire:model="estado" 
                            id="estado"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                        <option value="">Todos</option>
                        <option value="no_realizada">No realizada</option>
                        <option value="pendiente">Pendiente de recuperación</option>
                        <option value="justificado">Justificado</option>
                    </select>
                </div>

                <!-- Período -->
                <div class="mb-4">
                    <label for="periodo" class="block text-sm font-medium text-gray-700 mb-2">Período</label>
                    <input type="text" 
                           wire:model="periodo" 
                           id="periodo"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                           placeholder="2024-1">
                </div>

                <!-- Fecha Inicio -->
                <div class="mb-4">
                    <label for="fecha_inicio" class="block text-sm font-medium text-gray-700 mb-2">Fecha Inicio</label>
                    <input type="date" 
                           wire:model="fecha_inicio" 
                           id="fecha_inicio"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                </div>

                <!-- Fecha Fin -->
                <div class="mb-4">
                    <label for="fecha_fin" class="block text-sm font-medium text-gray-700 mb-2">Fecha Fin</label>
                    <input type="date" 
                           wire:model="fecha_fin" 
                           id="fecha_fin"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                </div>

                <!-- Botones de Acción -->
                <div class="flex gap-2 mt-6">
                    <button wire:click="aplicarFiltros" 
                            class="flex-1 px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        <i class="fas fa-filter mr-2"></i>Aplicar
                    </button>
                    <button wire:click="limpiarFiltros" 
                            class="flex-1 px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 border border-gray-300 rounded-md hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500">
                        <i class="fas fa-times mr-2"></i>Limpiar
                    </button>
                </div>
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
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Mód. Inicio</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Mód. Fin</th>
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
                                @php
                                    // Parsear módulos inicio y fin
                                    $modulos = explode(',', $clase->id_modulo);
                                    $moduloInicio = preg_replace('/^[A-Z]{2}\./', '', $modulos[0]);
                                    $moduloFin = count($modulos) > 1 ? preg_replace('/^[A-Z]{2}\./', '', end($modulos)) : $moduloInicio;
                                @endphp
                                <tr class="table-row hover:bg-gray-50 {{ $clase->estado === 'pendiente' ? 'bg-yellow-50' : '' }}">
                                    <td class="px-3 py-4 text-sm text-gray-900 w-24">
                                        <div class="flex items-center gap-1">
                                            {{ $clase->fecha_clase->format('d/m/Y') }}
                                            @if($clase->estado === 'pendiente')
                                                <i class="fas fa-clock text-yellow-600 text-xs cursor-help" 
                                                   title="Clase reagendada - Pendiente de recuperación"></i>
                                            @endif
                                        </div>
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
                                        {{ $moduloInicio }}
                                    </td>
                                    <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ $moduloFin }}
                                    </td>
                                    <td class="px-4 py-4 whitespace-nowrap">
                                        <div class="flex flex-col gap-1">
                                            @if($clase->estado === 'no_realizada')
                                                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">
                                                    No Realizada
                                                </span>
                                            @elseif($clase->estado === 'pendiente')
                                                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800 flex items-center gap-1">
                                                    <i class="fas fa-clock text-[10px]"></i>
                                                    Pendiente Recuperación
                                                </span>
                                            @elseif($clase->estado === 'justificado')
                                                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                                    Justificado
                                                </span>
                                            @endif
                                        </div>
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
                                            @if($clase->estado === 'pendiente')
                                                <div class="custom-tooltip">
                                                    <button wire:click="marcarComoRecuperada({{ $clase->id }})" 
                                                            class="p-1 bg-green-100 hover:bg-green-200 text-green-700 rounded transition-colors duration-200"
                                                            title="Marcar como recuperada">
                                                        <i class="fas fa-check-circle icon-animate text-xs"></i>
                                                    </button>
                                                    <span class="tooltip-text">Recuperada</span>
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
                            <option value="no_realizada" ${clase.estado === 'no_realizada' ? 'selected' : ''}>No realizada</option>
                            <option value="pendiente" ${clase.estado === 'pendiente' ? 'selected' : ''}>Pendiente de recuperación</option>
                            <option value="justificado" ${clase.estado === 'justificado' ? 'selected' : ''}>Justificado (sin recuperación)</option>
                        </select>
                        <p class="text-xs text-gray-500 mt-1">
                            <strong>Pendiente:</strong> Clase reagendada esperando recuperación<br>
                            <strong>Justificado:</strong> Clase justificada sin necesidad de recuperación
                        </p>
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
        
        console.log('Datos completos recibidos:', clase);
        
        // Variable global para almacenar módulos
        let modulosDisponibles = [];
        const fechaOriginal = clase.fecha_original; // formato: "dd/mm/yyyy"

        Swal.fire({
            title: '<strong><i class="fas fa-calendar-plus"></i> Reagendar Clase</strong>',
            html: `
                <div class="text-left space-y-4">
                    <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-4">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <i class="fas fa-exclamation-triangle text-yellow-400 text-xl"></i>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm text-yellow-700">
                                    <strong>Importante:</strong> Al reagendar esta clase, se marcará como <strong>justificada</strong> 
                                    pero quedará <strong>pendiente de recuperación</strong>. La clase permanecerá en el listado 
                                    hasta que se confirme su realización.
                                </p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-blue-50 p-4 rounded-lg mb-4">
                        <h4 class="font-semibold text-blue-900 mb-2">Clase Original</h4>
                        <p><strong>Profesor:</strong> ${clase.profesor}</p>
                        <p><strong>Asignatura:</strong> ${clase.asignatura}</p>
                        <p><strong>Fecha:</strong> ${clase.fecha_original}</p>
                        <p><strong>Espacio:</strong> ${clase.espacio_original}</p>
                        <p><strong>Módulo:</strong> ${clase.modulo_original}</p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Nueva Fecha <span class="text-red-500">*</span></label>
                            <input type="date" id="swal-nueva-fecha" class="w-full p-2 border border-gray-300 rounded-md" min="${new Date().toISOString().split('T')[0]}">
                        </div>
                        
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Cantidad de Módulos <span class="text-red-500">*</span></label>
                            <input type="number" id="swal-cantidad-modulos" class="w-full p-2 border border-gray-300 rounded-md" min="1" max="8" value="${clase.totalModulosProgramados}">
                        </div>
                        
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Módulo Inicio <span class="text-red-500">*</span></label>
                            <select id="swal-nuevo-modulo-inicio" class="w-full p-2 border border-gray-300 rounded-md">
                                <option value="">Cargando módulos...</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Módulo Final (Automático)</label>
                        <div class="p-3 bg-gray-50 border border-gray-300 rounded-md">
                            <span id="swal-modulo-final-display" class="text-gray-700">Selecciona un módulo inicial</span>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Nuevo Espacio <span class="text-red-500">*</span></label>
                        <select id="swal-nuevo-espacio" class="w-full p-2 border border-gray-300 rounded-md">
                            <option value="">Selecciona fecha y módulo primero</option>
                        </select>
                        <p id="espacios-disponibles-info" class="text-xs text-gray-500 mt-1"></p>
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
            didOpen: async () => {
                const dateInput = document.getElementById('swal-nueva-fecha');
                const hoy = new Date().toISOString().split('T')[0];
                dateInput.min = hoy;
                dateInput.focus();
                
                // Cargar módulos desde la API
                try {
                    const response = await fetch('/api/modulos');
                    modulosDisponibles = await response.json();
                    console.log('Módulos cargados:', modulosDisponibles);
                    
                    // Llenar select de módulos iniciales
                    const selectModuloInicio = document.getElementById('swal-nuevo-modulo-inicio');
                    selectModuloInicio.innerHTML = '<option value="">Seleccionar módulo</option>';
                    
                    modulosDisponibles.forEach(modulo => {
                        const option = document.createElement('option');
                        option.value = modulo.id_modulo;
                        option.textContent = `Módulo ${modulo.id_modulo} (${modulo.hora_inicio} - ${modulo.hora_termino})`;
                        selectModuloInicio.appendChild(option);
                    });
                } catch (error) {
                    console.error('Error cargando módulos:', error);
                }
                
                // Variables para controlar el cálculo
                const moduloInicio = document.getElementById('swal-nuevo-modulo-inicio');
                const moduloFinalDisplay = document.getElementById('swal-modulo-final-display');
                const cantidadModulos = document.getElementById('swal-cantidad-modulos');
                const dateInput2 = document.getElementById('swal-nueva-fecha');
                const selectEspacios = document.getElementById('swal-nuevo-espacio');
                const infoParagraph = document.getElementById('espacios-disponibles-info');
                
                // Función para cargar espacios disponibles
                const cargarEspaciosDisponibles = async () => {
                    const fecha = dateInput2.value;
                    const modulo = moduloInicio.value;
                    
                    if (!fecha || !modulo) {
                        selectEspacios.innerHTML = '<option value="">Selecciona fecha y módulo primero</option>';
                        infoParagraph.textContent = '';
                        return;
                    }

                    // Validar que no sea el mismo día
                    const [dia, mes, anio] = fechaOriginal.split('/');
                    const fechaOriginalFormato = anio + '-' + mes + '-' + dia;
                    if (fecha === fechaOriginalFormato) {
                        selectEspacios.innerHTML = '<option value="">No puedes reagendar para el mismo día</option>';
                        infoParagraph.textContent = 'El reagendamiento debe ser en una fecha diferente';
                        return;
                    }

                    // Calcular módulo final
                    const inicio = parseInt(modulo);
                    const cantidad = parseInt(cantidadModulos.value) || clase.totalModulosProgramados;
                    const fin = Math.min(inicio + cantidad - 1, modulosDisponibles.length);

                    try {
                        const url = '/api/espacios-disponibles/' + fecha + '/' + inicio + '/' + fin;
                        const response = await fetch(url);
                        const data = await response.json();
                        
                        if (data.error) {
                            selectEspacios.innerHTML = '<option value="">Error: ' + data.error + '</option>';
                            infoParagraph.textContent = '';
                            return;
                        }

                        console.log('Espacios disponibles:', data);

                        if (data.espacios.length === 0) {
                            selectEspacios.innerHTML = '<option value="">No hay espacios disponibles para esta fecha y módulos</option>';
                            infoParagraph.textContent = 'Intenta con otra fecha o módulo';
                            return;
                        }

                        selectEspacios.innerHTML = '<option value="">Seleccionar espacio</option>';
                        data.espacios.forEach(espacio => {
                            const option = document.createElement('option');
                            option.value = espacio.id_espacio;
                            option.textContent = espacio.display_name;
                            selectEspacios.appendChild(option);
                        });

                        infoParagraph.textContent = 'Disponibles: ' + data.total_disponibles + ' espacio(s) • Módulo: ' + data.hora_inicio + ' - ' + data.hora_fin;
                    } catch (error) {
                        console.error('Error cargando espacios:', error);
                        selectEspacios.innerHTML = '<option value="">Error al cargar espacios</option>';
                    }
                };
                
                // Función para recalcular el módulo final y mostrar horarios
                const recalcularModuloFinal = () => {
                    if (moduloInicio.value && modulosDisponibles.length > 0) {
                        const inicio = parseInt(moduloInicio.value);
                        const cantidad = parseInt(cantidadModulos.value) || clase.totalModulosProgramados;
                        const fin = Math.min(inicio + cantidad - 1, modulosDisponibles.length);
                        
                        // Buscar horarios de inicio y fin
                        const moduloInicial = modulosDisponibles.find(m => m.id_modulo == inicio);
                        const moduloFinal = modulosDisponibles.find(m => m.id_modulo == fin);
                        
                        if (moduloInicial && moduloFinal) {
                            const texto = 'Módulos ' + inicio + ' - ' + fin + ' (' + moduloInicial.hora_inicio + ' - ' + moduloFinal.hora_termino + ')';
                            moduloFinalDisplay.textContent = texto;
                        }
                        
                        // Cargar espacios disponibles cuando se selecciona módulo
                        cargarEspaciosDisponibles();
                    } else {
                        moduloFinalDisplay.textContent = 'Selecciona un módulo inicial';
                    }
                };
                
                dateInput2.addEventListener('change', cargarEspaciosDisponibles);
                moduloInicio.addEventListener('change', recalcularModuloFinal);
                cantidadModulos.addEventListener('change', recalcularModuloFinal);
                cantidadModulos.addEventListener('input', recalcularModuloFinal);
            },
            preConfirm: () => {
                const nuevaFecha = document.getElementById('swal-nueva-fecha').value;
                const nuevoEspacio = document.getElementById('swal-nuevo-espacio').value;
                const nuevoModulo = document.getElementById('swal-nuevo-modulo-inicio').value;
                const cantidadModulos = document.getElementById('swal-cantidad-modulos').value;
                const motivo = document.getElementById('swal-motivo-reagendamiento').value;
                
                if (!nuevaFecha) {
                    Swal.showValidationMessage('Por favor selecciona una fecha');
                    return false;
                }
                
                // Validar que no sea el mismo día
                const [dia, mes, anio] = fechaOriginal.split('/');
                const fechaOriginalFormato = anio + '-' + mes + '-' + dia;
                if (nuevaFecha === fechaOriginalFormato) {
                    Swal.showValidationMessage('No puedes reagendar para el mismo día');
                    return false;
                }
                
                if (!nuevoEspacio) {
                    Swal.showValidationMessage('Por favor selecciona un espacio');
                    return false;
                }
                
                if (!nuevoModulo) {
                    Swal.showValidationMessage('Por favor selecciona un módulo de inicio');
                    return false;
                }
                
                if (!cantidadModulos || parseInt(cantidadModulos) < 1 || parseInt(cantidadModulos) > 15) {
                    Swal.showValidationMessage('Por favor ingresa una cantidad válida de módulos (1-15)');
                    return false;
                }
                
                const fechaSeleccionada = new Date(nuevaFecha);
                const hoy = new Date();
                hoy.setHours(0, 0, 0, 0);
                
                if (fechaSeleccionada < hoy) {
                    Swal.showValidationMessage('La fecha no puede ser anterior a hoy');
                    return false;
                }
                
                return { nuevaFecha, nuevoEspacio, nuevoModulo, cantidadModulos, motivo };
            }
        }).then((result) => {
            if (result.isConfirmed) {
                const { nuevaFecha, nuevoEspacio, nuevoModulo, cantidadModulos, motivo } = result.value;
                window.Livewire.find('{{ $this->getId() }}').call('reagendarClase', clase.id, nuevaFecha, nuevoEspacio, nuevoModulo, cantidadModulos, motivo);
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
