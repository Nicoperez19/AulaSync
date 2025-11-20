<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-2 pr-6 md:flex-row md:items-center md:justify-between">
            <div class="flex items-center gap-3">
                <div class="p-2 rounded-xl bg-light-cloud-blue">
                    <i class="text-2xl text-white fa-solid fa-user-clock"></i>
                </div>
                <div>
                    <h2 class="text-2xl font-bold leading-tight">
                        Editar Asignatura Temporal
                    </h2>
                    <p class="text-sm text-gray-500">Modifique profesor, datos básicos, horarios y sala</p>
                </div>
            </div>
            <a href="{{ route('clases-temporales.index') }}" 
               class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">
                <i class="mr-2 fa-solid fa-arrow-left"></i>Volver
            </a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
            <form method="POST" 
                  action="{{ route('clases-temporales.update', $profesorColaborador) }}"
                  id="formProfesorColaborador">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <!-- Columna izquierda: Formulario -->
                    <div class="lg:col-span-2">
                        <!-- Sección 1: Datos Básicos -->
                        <div class="bg-white rounded-lg shadow overflow-hidden mb-6">
                            <div class="px-6 py-4" style="background-color: #cd1627;">
                                <h3 class="text-lg font-semibold text-white">
                                    <i class="fa-solid fa-file-alt mr-2"></i>Datos Básicos
                                </h3>
                            </div>
                            <div class="p-6">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div class="md:col-span-2">
                                        <label class="block text-sm font-medium text-gray-700 mb-1">
                                            Nombre de la Asignatura <span class="text-red-500">*</span>
                                        </label>
                                        <input type="text" name="nombre_asignatura_temporal" required 
                                               id="nombre_asignatura"
                                               value="{{ old('nombre_asignatura_temporal', $profesorColaborador->nombre_asignatura_temporal) }}"
                                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-red-500 focus:border-red-500"
                                               placeholder="Ej: Taller de Programación Avanzada">
                                        @error('nombre_asignatura_temporal')
                                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <div class="md:col-span-2">
                                        <label class="block text-sm font-medium text-gray-700 mb-2">
                                            Asignatura del Catálogo (opcional)
                                        </label>
                                        <div class="relative">
                                            <input type="text" id="asignatura_search" 
                                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-red-500 focus:border-red-500"
                                                   placeholder="Buscar asignatura existente...">
                                            <input type="hidden" name="id_asignatura" id="id_asignatura" 
                                                   value="{{ old('id_asignatura', $profesorColaborador->id_asignatura) }}">
                                            <div id="asignatura-list" class="hidden absolute z-50 w-full bg-white border border-gray-300 rounded-md shadow-lg mt-1 max-h-48 overflow-y-auto">
                                            </div>
                                        </div>
                                        <p class="mt-2 text-xs text-gray-500">
                                            <i class="fa-solid fa-info-circle"></i>
                                            Selecciona una asignatura existente o déjalo vacío para usar la asignatura temporal
                                        </p>
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">
                                            Fecha Inicio <span class="text-red-500">*</span>
                                        </label>
                                        <input type="date" name="fecha_inicio" required 
                                               id="fecha_inicio"
                                               value="{{ old('fecha_inicio', $profesorColaborador->fecha_inicio->format('Y-m-d')) }}"
                                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-red-500 focus:border-red-500">
                                        @error('fecha_inicio')
                                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">
                                            Fecha Término <span class="text-red-500">*</span>
                                        </label>
                                        <input type="date" name="fecha_termino" required 
                                               id="fecha_termino"
                                               value="{{ old('fecha_termino', $profesorColaborador->fecha_termino->format('Y-m-d')) }}"
                                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-red-500 focus:border-red-500">
                                        @error('fecha_termino')
                                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">
                                            Cantidad de Inscritos <span class="text-red-500">*</span>
                                        </label>
                                        <input type="number" name="cantidad_inscritos" required 
                                               id="cantidad_inscritos" 
                                               min="1"
                                               value="{{ old('cantidad_inscritos', $profesorColaborador->cantidad_inscritos) }}"
                                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-red-500 focus:border-red-500"
                                               placeholder="Número de estudiantes">
                                        @error('cantidad_inscritos')
                                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">
                                            Estado <span class="text-red-500">*</span>
                                        </label>
                                        <select name="estado" required id="estado"
                                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-red-500 focus:border-red-500">
                                            <option value="activo" {{ old('estado', $profesorColaborador->estado) === 'activo' ? 'selected' : '' }}>Activo</option>
                                            <option value="inactivo" {{ old('estado', $profesorColaborador->estado) === 'inactivo' ? 'selected' : '' }}>Inactivo</option>
                                        </select>
                                        @error('estado')
                                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">
                                            Tipo de Clase <span class="text-red-500">*</span>
                                        </label>
                                        <select name="tipo_clase" required id="tipo_clase"
                                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-red-500 focus:border-red-500">
                                            <option value="temporal" {{ old('tipo_clase', $profesorColaborador->tipo_clase ?? 'temporal') === 'temporal' ? 'selected' : '' }}>Temporal</option>
                                            <option value="reforzamiento" {{ old('tipo_clase', $profesorColaborador->tipo_clase ?? 'temporal') === 'reforzamiento' ? 'selected' : '' }}>Reforzamiento</option>
                                            <option value="recuperacion" {{ old('tipo_clase', $profesorColaborador->tipo_clase ?? 'temporal') === 'recuperacion' ? 'selected' : '' }}>Recuperación</option>
                                        </select>
                                        <p class="mt-1 text-xs text-gray-500">
                                            <span class="inline-block px-2 py-0.5 bg-purple-200 text-purple-700 rounded mr-2">Temporal</span>
                                            <span class="inline-block px-2 py-0.5 bg-orange-200 text-orange-700 rounded mr-2">Reforzamiento</span>
                                            <span class="inline-block px-2 py-0.5 bg-green-200 text-green-700 rounded">Recuperación</span>
                                        </p>
                                        @error('tipo_clase')
                                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <div class="md:col-span-2">
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Descripción (opcional)</label>
                                        <textarea name="descripcion" rows="3" id="descripcion"
                                                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-red-500 focus:border-red-500"
                                                  placeholder="Breve descripción de la asignatura...">{{ old('descripcion', $profesorColaborador->descripcion) }}</textarea>
                                        @error('descripcion')
                                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Sección 2: Profesor -->
                        <div class="bg-white rounded-lg shadow overflow-hidden mb-6">
                            <div class="px-6 py-4" style="background-color: #cd1627;">
                                <h3 class="text-lg font-semibold text-white">
                                    <i class="fa-solid fa-user mr-2"></i>Profesor
                                </h3>
                            </div>
                            <div class="p-6">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div class="md:col-span-2">
                                        <label class="block text-sm font-medium text-gray-700 mb-2">
                                            Seleccionar Profesor <span class="text-red-500">*</span>
                                        </label>
                                        <select name="run_profesor_colaborador" required id="run_profesor_colaborador"
                                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-red-500 focus:border-red-500">
                                            <option value="">-- Seleccione un profesor --</option>
                                            @foreach($profesores as $profesor)
                                                <option value="{{ $profesor->run_profesor }}"
                                                    {{ old('run_profesor_colaborador', $profesorColaborador->run_profesor_colaborador) === $profesor->run_profesor ? 'selected' : '' }}>
                                                    {{ $profesor->name }} ({{ $profesor->run_profesor }})
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('run_profesor_colaborador')
                                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Sección 3: Calendario de Horarios -->
                        <div class="bg-white rounded-lg shadow overflow-hidden mb-6">
                            <div class="px-6 py-4" style="background-color: #cd1627;">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <h3 class="text-lg font-semibold text-white">
                                            <i class="fa-solid fa-calendar-alt mr-2"></i>Horarios
                                        </h3>
                                    </div>
                                    <div class="relative group cursor-help">
                                        <div class="flex items-center gap-1 text-white hover:text-gray-200 transition">
                                            <i class="fa-solid fa-circle-question text-lg"></i>
                                            <span class="text-sm font-medium">¿Cómo seleccionar?</span>
                                        </div>
                                        <div class="hidden group-hover:block absolute right-0 mt-2 w-72 bg-gray-900 text-white rounded-lg shadow-xl p-4 text-xs z-50 border border-gray-700">
                                            <p class="font-bold mb-3 text-white text-sm">📋 Cómo seleccionar horarios:</p>
                                            <ul class="space-y-2 text-gray-200">
                                                <li class="flex items-start gap-2"><span class="text-red-400 font-bold">1.</span> Haz clic en un módulo</li>
                                                <li class="flex items-start gap-2"><span class="text-red-400 font-bold">2.</span> Arrastra hasta el final</li>
                                                <li class="flex items-start gap-2"><span class="text-red-400 font-bold">3.</span> Se marcarán en <span class="font-bold text-red-400">rojo</span></li>
                                                <li class="flex items-start gap-2"><span class="text-red-400 font-bold">4.</span> Selecciona varios días</li>
                                                <li class="flex items-start gap-2"><span class="text-red-400 font-bold">5.</span> Click en <span class="font-bold text-red-400">X</span> para eliminar</li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="p-6">
                                <div class="overflow-x-auto">
                                    <table class="w-full border-collapse" id="horario-grid">
                                        <thead>
                                            <tr>
                                                <th class="border border-gray-300 p-2 bg-gray-100 w-32">Módulo</th>
                                                <th class="border border-gray-300 p-2 bg-gray-100">Lunes</th>
                                                <th class="border border-gray-300 p-2 bg-gray-100">Martes</th>
                                                <th class="border border-gray-300 p-2 bg-gray-100">Miércoles</th>
                                                <th class="border border-gray-300 p-2 bg-gray-100">Jueves</th>
                                                <th class="border border-gray-300 p-2 bg-gray-100">Viernes</th>
                                                <th class="border border-gray-300 p-2 bg-gray-100">Sábado</th>
                                            </tr>
                                        </thead>
                                        <tbody id="horario-tbody">
                                            @php
                                                $modulos = [
                                                    1 => '08:10 - 09:00', 2 => '09:10 - 10:00', 3 => '10:10 - 11:00',
                                                    4 => '11:10 - 12:00', 5 => '12:10 - 13:00', 6 => '13:10 - 14:00',
                                                    7 => '14:10 - 15:00', 8 => '15:10 - 16:00', 9 => '16:10 - 17:00',
                                                    10 => '17:10 - 18:00', 11 => '18:10 - 19:00', 12 => '19:10 - 20:00',
                                                    13 => '20:10 - 21:00', 14 => '21:10 - 22:00', 15 => '22:10 - 23:00',
                                                ];
                                                $dias = ['lunes' => 'LU', 'martes' => 'MA', 'miercoles' => 'MI', 'jueves' => 'JU', 'viernes' => 'VI', 'sabado' => 'SA'];
                                                $sabado_modulos = [1, 2, 3, 4, 5];
                                                $modulos_diurnos = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11];
                                                $modulos_vespertinos = [12, 13, 14, 15];

                                                // Obtener módulos actuales
                                                $modulosActuales = $profesorColaborador->planificaciones->pluck('id_modulo')->toArray();
                                            @endphp
                                            @foreach($modulos as $numModulo => $horario)
                                                <tr>
                                                    <td class="border border-gray-300 p-2 bg-gray-50 text-center text-xs font-medium whitespace-nowrap">
                                                        <div>{{ $numModulo }}</div>
                                                        <div class="text-gray-500 text-xs">{{ $horario }}</div>
                                                    </td>
                                                    @foreach($dias as $dia => $prefijo)
                                                        @if($dia === 'sabado' && !in_array($numModulo, $sabado_modulos))
                                                            <td class="border border-gray-300 p-0 bg-gray-400"></td>
                                                        @elseif($dia === 'sabado' && $numModulo > 5)
                                                            <td class="border border-gray-300 p-0 bg-gray-600">
                                                                <div class="modulo-cell h-8 cursor-not-allowed" data-dia="{{ $dia }}" data-modulo="{{ $numModulo }}" data-id-modulo="{{ $prefijo }}.{{ $numModulo }}" data-disabled="true"></div>
                                                            </td>
                                                        @else
                                                            @php
                                                                $idModulo = $prefijo . '.' . $numModulo;
                                                                $isSelected = in_array($idModulo, $modulosActuales);
                                                            @endphp
                                                            <td class="border border-gray-300 p-0">
                                                                <div class="modulo-cell h-8 cursor-pointer transition {{ $isSelected ? 'bg-red-600' : 'bg-white hover:bg-gray-100' }}" 
                                                                     data-dia="{{ $dia }}" 
                                                                     data-modulo="{{ $numModulo }}" 
                                                                     data-id-modulo="{{ $idModulo }}"
                                                                     data-selected="{{ $isSelected ? 'true' : 'false' }}">
                                                                    @if($isSelected)
                                                                        <div class="h-full flex items-center justify-center">
                                                                            <i class="fa-solid fa-check text-white text-xs"></i>
                                                                        </div>
                                                                    @endif
                                                                </div>
                                                            </td>
                                                        @endif
                                                    @endforeach
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>

                                <!-- Clases seleccionadas -->
                                <div class="mt-6 p-4 bg-gray-50 rounded-lg border border-gray-200">
                                    <h4 class="font-semibold text-gray-700 mb-3">Clases Temporales Seleccionadas</h4>
                                    <div id="selected-classes" class="space-y-2">
                                        @forelse($profesorColaborador->planificaciones as $planificacion)
                                            <div class="flex items-center justify-between bg-white p-3 rounded-lg border border-gray-200 selected-class-item" data-id-modulo="{{ $planificacion->id_modulo }}">
                                                <div class="flex items-center gap-3">
                                                    <div class="w-3 h-3 bg-red-600 rounded-full"></div>
                                                    <span class="text-sm font-medium text-gray-700">
                                                        {{ $planificacion->modulo->dia }} - Módulo {{ explode('.', $planificacion->id_modulo)[1] }} ({{ $planificacion->modulo->hora_inicio }})
                                                    </span>
                                                    <span class="text-xs text-gray-500">
                                                        {{ $planificacion->espacio->nombre_espacio }}
                                                    </span>
                                                </div>
                                                <button type="button" 
                                                        class="text-red-600 hover:text-red-800 delete-class-btn"
                                                        data-id-modulo="{{ $planificacion->id_modulo }}">
                                                    <i class="fa-solid fa-times"></i>
                                                </button>
                                            </div>
                                        @empty
                                            <p class="text-sm text-gray-500 italic">No hay clases seleccionadas aún</p>
                                        @endforelse
                                    </div>
                                </div>

                                <!-- Input oculto para planificaciones -->
                                <input type="hidden" name="planificaciones" id="planificaciones-input" value="">

                                @error('planificaciones')
                                    <p class="text-red-500 text-sm mt-2">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Columna derecha: Sala -->
                    <div class="lg:col-span-1">
                        <!-- Sección 4: Sala -->
                        <div class="bg-white rounded-lg shadow overflow-hidden sticky top-6">
                            <div class="px-6 py-4" style="background-color: #cd1627;">
                                <h3 class="text-lg font-semibold text-white">
                                    <i class="fa-solid fa-door-open mr-2"></i>Sala
                                </h3>
                            </div>
                            <div class="p-6">
                                <div class="mb-6">
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Salas Disponibles <span class="text-red-500">*</span>
                                    </label>
                                    <select id="salas-select" 
                                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-red-500 focus:border-red-500">
                                        <option value="">-- Seleccione una sala --</option>
                                    </select>
                                    <p class="text-xs text-gray-500 mt-2">
                                        <i class="fa-solid fa-info-circle"></i>
                                        Solo se muestran salas sin conflictos
                                    </p>
                                </div>

                                <!-- Detalles de la sala seleccionada -->
                                <div id="sala-details" class="hidden p-4 bg-gray-50 rounded-lg border border-gray-200">
                                    <h4 class="font-semibold text-gray-700 mb-3">Detalles de la Sala</h4>
                                    <div class="space-y-2 text-sm">
                                        <div>
                                            <span class="text-gray-600">Nombre:</span>
                                            <span id="sala-nombre" class="font-medium text-gray-800"></span>
                                        </div>
                                        <div>
                                            <span class="text-gray-600">Piso:</span>
                                            <span id="sala-piso" class="font-medium text-gray-800"></span>
                                        </div>
                                        <div>
                                            <span class="text-gray-600">Tipo:</span>
                                            <span id="sala-tipo" class="font-medium text-gray-800"></span>
                                        </div>
                                        <div>
                                            <span class="text-gray-600">Capacidad:</span>
                                            <span id="sala-capacidad" class="font-medium text-gray-800"></span>
                                        </div>
                                        <div>
                                            <span class="text-gray-600">Ocupación (Planificación):</span>
                                            <div class="flex items-center gap-2 mt-1">
                                                <div class="flex-1 bg-gray-300 rounded-full h-2">
                                                    <div id="sala-ocupacion-bar" class="bg-red-600 h-2 rounded-full" style="width: 0%"></div>
                                                </div>
                                                <span id="sala-ocupacion-pct" class="font-medium text-gray-800 min-w-[40px]">0%</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Hidden input para almacenar la sala seleccionada -->
                                <input type="hidden" id="selected-sala-id" value="">
                            </div>
                        </div>

                        <!-- Botones de acción -->
                        <div class="mt-6 space-y-3">
                            <button type="submit" 
                                    class="w-full px-4 py-2 text-sm font-medium text-white rounded-lg"
                                    style="background-color: #cd1627;"
                                    id="btn-guardar">
                                <i class="fa-solid fa-save mr-2"></i>Guardar Cambios
                            </button>
                            <button type="button" 
                                    class="w-full px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50"
                                    onclick="confirmDelete()">
                                <i class="fa-solid fa-trash mr-2"></i>Eliminar
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal de confirmación de eliminación -->
    <div id="delete-modal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white rounded-lg p-6 max-w-sm mx-auto">
            <h3 class="text-lg font-bold text-gray-800 mb-2">¿Eliminar profesor colaborador?</h3>
            <p class="text-gray-600 mb-6">Esta acción no se puede deshacer. Se eliminarán todos los datos asociados.</p>
            <div class="flex gap-3 justify-end">
                <button type="button" 
                        onclick="closeDeleteModal()"
                        class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200">
                    Cancelar
                </button>
                <form method="POST" action="{{ route('clases-temporales.destroy', $profesorColaborador) }}" style="display: inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" 
                            class="px-4 py-2 text-sm font-medium text-white rounded-lg"
                            style="background-color: #cd1627;">
                        Eliminar
                    </button>
                </form>
            </div>
        </div>
    </div>

    <script>
        const profesorColaborador = @json($profesorColaborador);
        const espacios = @json($espacios);
        const modulos = @json($modulos);
        const asignaturas = @json($asignaturas);
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
                const response = await fetch('{{ route("clases-temporales.getSalasDisponibles") }}', {
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
                option.textContent = `${sala.nombre_espacio} (${sala.piso_nombre}) - Ocupación: ${sala.porcentaje_planificacion}%`;
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
    </script>
</x-app-layout>
