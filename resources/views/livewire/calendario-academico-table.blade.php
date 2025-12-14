<div>
    <!-- Barra de herramientas principal -->
    <div class="p-4 bg-gray-50 border-b">
        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <!-- Toggle Vista Tabla/Calendario -->
            <div class="flex items-center gap-2">
                <span class="text-sm font-medium text-gray-700">Vista:</span>
                <div class="inline-flex rounded-lg border border-gray-300 bg-white p-1">
                    <button wire:click="cambiarVista('tabla')"
                        class="px-3 py-1.5 text-sm font-medium rounded-md transition-colors {{ $vistaActiva === 'tabla' ? 'bg-light-cloud-blue text-white' : 'text-gray-600 hover:bg-gray-100' }}">
                        <i class="fa-solid fa-table mr-1"></i> Tabla
                    </button>
                    <button wire:click="cambiarVista('calendario')"
                        class="px-3 py-1.5 text-sm font-medium rounded-md transition-colors {{ $vistaActiva === 'calendario' ? 'bg-light-cloud-blue text-white' : 'text-gray-600 hover:bg-gray-100' }}">
                        <i class="fa-solid fa-calendar mr-1"></i> Calendario
                    </button>
                </div>
            </div>

            <!-- Tabs Feriados/Períodos/Cursos de Verano (solo en vista tabla) -->
            @if($vistaActiva === 'tabla')
            <div class="flex items-center gap-2">
                <div class="inline-flex rounded-lg border border-gray-300 bg-white p-1">
                    <button wire:click="cambiarTab('feriados')"
                        class="px-3 py-1.5 text-sm font-medium rounded-md transition-colors {{ $tabActivo === 'feriados' ? 'bg-red-500 text-white' : 'text-gray-600 hover:bg-gray-100' }}">
                        <i class="fa-solid fa-calendar-xmark mr-1"></i> Feriados
                    </button>
                    <button wire:click="cambiarTab('periodos')"
                        class="px-3 py-1.5 text-sm font-medium rounded-md transition-colors {{ $tabActivo === 'periodos' ? 'bg-green-500 text-white' : 'text-gray-600 hover:bg-gray-100' }}">
                        <i class="fa-solid fa-graduation-cap mr-1"></i> Períodos Académicos
                    </button>
                    <button wire:click="cambiarTab('cursos_verano')"
                        class="px-3 py-1.5 text-sm font-medium rounded-md transition-colors {{ $tabActivo === 'cursos_verano' ? 'bg-orange-500 text-white' : 'text-gray-600 hover:bg-gray-100' }}">
                        <i class="fa-solid fa-sun mr-1"></i> Cursos de Verano
                    </button>
                </div>
            </div>
            @endif
        </div>
    </div>

    <!-- Período Académico Actual (Banner informativo) -->
    @if($periodoActual)
    <div class="mx-4 mt-4 p-4 bg-gradient-to-r from-green-50 to-blue-50 border border-green-200 rounded-lg">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="p-2 rounded-full bg-green-500 text-white">
                    <i class="fa-solid fa-calendar-check"></i>
                </div>
                <div>
                    <h3 class="font-semibold text-green-800">Período Académico Actual</h3>
                    <p class="text-sm text-green-600">{{ $periodoActual->nombre_completo }}</p>
                </div>
            </div>
            <div class="text-right text-sm text-gray-600">
                <p><span class="font-medium">Inicio:</span> {{ $periodoActual->fecha_inicio->format('d/m/Y') }}</p>
                <p><span class="font-medium">Fin:</span> {{ $periodoActual->fecha_fin->format('d/m/Y') }}</p>
            </div>
        </div>
    </div>
    @else
    <div class="mx-4 mt-4 p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
        <div class="flex items-center gap-3">
            <div class="p-2 rounded-full bg-yellow-500 text-white">
                <i class="fa-solid fa-exclamation-triangle"></i>
            </div>
            <div>
                <h3 class="font-semibold text-yellow-800">Sin Período Académico Activo</h3>
                <p class="text-sm text-yellow-600">No hay un período académico configurado para la fecha actual. Configure uno en la pestaña "Períodos Académicos".</p>
            </div>
        </div>
    </div>
    @endif

    <!-- VISTA CALENDARIO -->
    @if($vistaActiva === 'calendario')
    <div class="p-4">
        <!-- Navegación del calendario -->
        <div class="flex items-center justify-between mb-4">
            <button wire:click="mesAnterior" class="px-4 py-2 text-gray-600 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">
                <i class="fa-solid fa-chevron-left"></i>
            </button>
            <div class="flex items-center gap-4">
                <h2 class="text-xl font-bold text-gray-800">{{ $nombreMes }} {{ $anioCalendario }}</h2>
                <button wire:click="irAHoy" class="px-3 py-1 text-sm text-white bg-light-cloud-blue rounded-lg hover:bg-cloud-blue">
                    Hoy
                </button>
            </div>
            <button wire:click="mesSiguiente" class="px-4 py-2 text-gray-600 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">
                <i class="fa-solid fa-chevron-right"></i>
            </button>
        </div>

        <!-- Leyenda -->
        <div class="flex flex-wrap gap-4 mb-4 p-3 bg-gray-50 rounded-lg">
            <div class="flex items-center gap-2">
                <span class="w-3 h-3 rounded-full bg-blue-500"></span>
                <span class="text-sm text-gray-600">Feriado</span>
            </div>
            <div class="flex items-center gap-2">
                <span class="w-3 h-3 rounded-full bg-yellow-500"></span>
                <span class="text-sm text-gray-600">Semana Reajuste</span>
            </div>
            <div class="flex items-center gap-2">
                <span class="w-3 h-3 rounded-full bg-red-500"></span>
                <span class="text-sm text-gray-600">Suspensión/Fin Semestre</span>
            </div>
            <div class="flex items-center gap-2">
                <span class="w-3 h-3 rounded-full bg-green-500"></span>
                <span class="text-sm text-gray-600">Inicio Semestre</span>
            </div>
            <div class="flex items-center gap-2">
                <span class="w-3 h-3 rounded-full bg-orange-500"></span>
                <span class="text-sm text-gray-600">Cursos Verano</span>
            </div>
        </div>

        <!-- Calendario -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <!-- Encabezados días de la semana -->
            <div class="grid grid-cols-7 bg-gray-100 border-b">
                @foreach(['Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb', 'Dom'] as $dia)
                <div class="py-3 text-center text-sm font-semibold text-gray-600">{{ $dia }}</div>
                @endforeach
            </div>

            <!-- Días del calendario -->
            <div class="grid grid-cols-7">
                @foreach($diasCalendario as $dia)
                @php
                    $esHoy = $dia['fecha']->isToday();
                    $esFinDeSemana = $dia['fecha']->isWeekend();
                @endphp
                <div class="min-h-24 p-1 border-b border-r {{ !$dia['esMesActual'] ? 'bg-gray-50' : '' }} {{ $esFinDeSemana && $dia['esMesActual'] ? 'bg-blue-50' : '' }} {{ $esHoy ? 'bg-yellow-50 ring-2 ring-inset ring-yellow-400' : '' }}">
                    <div class="flex items-center justify-between mb-1">
                        <span class="text-sm font-medium {{ !$dia['esMesActual'] ? 'text-gray-400' : ($esHoy ? 'text-yellow-700 font-bold' : 'text-gray-700') }}">
                            {{ $dia['fecha']->day }}
                        </span>
                    </div>
                    <div class="space-y-1">
                        @foreach($dia['eventos'] as $evento)
                        <div class="text-xs px-1.5 py-0.5 rounded text-white truncate {{ $evento['color'] }}" title="{{ $evento['nombre'] }}">
                            {{ Str::limit($evento['nombre'], 12) }}
                        </div>
                        @endforeach
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
    @endif

    <!-- VISTA TABLA -->
    @if($vistaActiva === 'tabla')
    
        <!-- TAB FERIADOS -->
        @if($tabActivo === 'feriados')
        <div class="p-4 bg-gray-50">
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div class="flex flex-col gap-2 md:flex-row md:items-center md:gap-4">
                    <!-- Búsqueda -->
                    <div class="relative">
                        <input type="text" wire:model.live.debounce.500ms="search" placeholder="Buscar feriado..."
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg md:w-64 focus:ring-2 focus:ring-light-cloud-blue focus:border-transparent">
                        <i class="absolute text-gray-400 transform -translate-y-1/2 fa-solid fa-search right-3 top-1/2"></i>
                    </div>

                    <!-- Filtro de tipo -->
                    <select wire:model.live="tipo"
                        class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-light-cloud-blue focus:border-transparent">
                        <option value="">Todos los tipos</option>
                        <option value="feriado">Feriado</option>
                        <option value="semana_reajuste">Semana de Reajuste</option>
                        <option value="suspension_actividades">Suspensión de Actividades</option>
                    </select>
                </div>

                <!-- Botón agregar -->
                <button wire:click="openCreateModal"
                    class="px-4 py-2 text-white transition-colors rounded-lg bg-light-cloud-blue hover:bg-cloud-blue">
                    <i class="mr-2 fa-solid fa-plus"></i>
                    Agregar Feriado
                </button>
            </div>
        </div>

        <!-- Tabla de feriados -->
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="text-xs text-white uppercase bg-light-cloud-blue">
                    <tr>
                        <th class="px-6 py-3 cursor-pointer" wire:click="sortBy('nombre')">
                            Nombre
                            @if ($sortField === 'nombre')
                                <i class="fa-solid fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>
                            @endif
                        </th>
                        <th class="px-6 py-3 cursor-pointer" wire:click="sortBy('fecha_inicio')">
                            Fecha Inicio
                            @if ($sortField === 'fecha_inicio')
                                <i class="fa-solid fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>
                            @endif
                        </th>
                        <th class="px-6 py-3 cursor-pointer" wire:click="sortBy('fecha_fin')">
                            Fecha Fin
                            @if ($sortField === 'fecha_fin')
                                <i class="fa-solid fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>
                            @endif
                        </th>
                        <th class="px-6 py-3">Tipo</th>
                        <th class="px-6 py-3">Estado</th>
                        <th class="px-6 py-3 text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($feriados as $feriado)
                        <tr class="bg-white border-b hover:bg-gray-50">
                            <td class="px-6 py-4">
                                <div class="font-medium">{{ $feriado->nombre }}</div>
                                @if ($feriado->descripcion)
                                    <div class="text-xs text-gray-500">{{ Str::limit($feriado->descripcion, 50) }}</div>
                                @endif
                            </td>
                            <td class="px-6 py-4">{{ $feriado->fecha_inicio->format('d/m/Y') }}</td>
                            <td class="px-6 py-4">{{ $feriado->fecha_fin->format('d/m/Y') }}</td>
                            <td class="px-6 py-4">
                                @php
                                    $tipoLabels = [
                                        'feriado' => 'Feriado',
                                        'semana_reajuste' => 'Semana de Reajuste',
                                        'suspension_actividades' => 'Suspensión de Actividades',
                                    ];
                                    $tipoColors = [
                                        'feriado' => 'bg-blue-100 text-blue-800',
                                        'semana_reajuste' => 'bg-yellow-100 text-yellow-800',
                                        'suspension_actividades' => 'bg-red-100 text-red-800',
                                    ];
                                @endphp
                                <span class="px-2 py-1 text-xs font-semibold rounded {{ $tipoColors[$feriado->tipo] ?? 'bg-gray-100 text-gray-800' }}">
                                    {{ $tipoLabels[$feriado->tipo] ?? ucfirst($feriado->tipo) }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <button wire:click="toggleActivo({{ $feriado->id_feriado }})"
                                    class="px-2 py-1 text-xs font-semibold rounded {{ $feriado->activo ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                                    {{ $feriado->activo ? 'Activo' : 'Inactivo' }}
                                </button>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <div class="flex justify-center gap-2">
                                    <button wire:click="openEditModal({{ $feriado->id_feriado }})"
                                        class="text-blue-600 hover:text-blue-900" title="Editar">
                                        <i class="fa-solid fa-edit"></i>
                                    </button>
                                    <button wire:click="delete({{ $feriado->id_feriado }})"
                                        wire:confirm="¿Está seguro de eliminar este registro?"
                                        class="text-red-600 hover:text-red-900" title="Eliminar">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-4 text-center text-gray-500">
                                No se encontraron registros
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-4 bg-white border-t">
            {{ $feriados->links() }}
        </div>
        @endif

        <!-- TAB PERÍODOS ACADÉMICOS -->
        @if($tabActivo === 'periodos')
        <div class="p-4 bg-gray-50">
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div>
                    <h3 class="text-lg font-semibold text-gray-700">Configuración de Semestres</h3>
                    <p class="text-sm text-gray-500">Configure las fechas de inicio y fin de cada período académico</p>
                </div>

                <div class="flex gap-2">
                    <button wire:click="openCreateModalPeriodo"
                        class="px-4 py-2 text-white transition-colors rounded-lg bg-green-500 hover:bg-green-600">
                        <i class="mr-2 fa-solid fa-plus"></i>
                        Agregar Período
                    </button>
                </div>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="text-xs text-white uppercase bg-green-500">
                    <tr>
                        <th class="px-6 py-3">Período</th>
                        <th class="px-6 py-3">Inicio Actividades</th>
                        <th class="px-6 py-3">Fin Actividades</th>
                        <th class="px-6 py-3">Estado</th>
                        <th class="px-6 py-3 text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($periodos as $periodo)
                        <tr class="bg-white border-b hover:bg-gray-50 {{ $periodo->estaEnCurso() ? 'bg-green-50' : '' }}">
                            <td class="px-6 py-4">
                                <div class="font-semibold">{{ $periodo->nombre_completo }}</div>
                                @if($periodo->estaEnCurso())
                                    <span class="text-xs text-green-600"><i class="fa-solid fa-circle text-xs mr-1"></i>En curso</span>
                                @endif
                            </td>
                            <td class="px-6 py-4">{{ $periodo->fecha_inicio->format('d/m/Y') }}</td>
                            <td class="px-6 py-4">{{ $periodo->fecha_fin->format('d/m/Y') }}</td>
                            <td class="px-6 py-4">
                                <button wire:click="toggleActivoPeriodo({{ $periodo->id_periodo }})"
                                    class="px-2 py-1 text-xs font-semibold rounded {{ $periodo->activo ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                                    {{ $periodo->estado_texto }}
                                </button>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <button wire:click="openEditModalPeriodo({{ $periodo->id_periodo }})"
                                    class="text-blue-600 hover:text-blue-900 mr-2" title="Editar fechas">
                                    <i class="fa-solid fa-edit"></i>
                                </button>
                                <button wire:click="deletePeriodo({{ $periodo->id_periodo }})"
                                    wire:confirm="¿Está seguro de eliminar este período académico?"
                                    class="text-red-600 hover:text-red-900" title="Eliminar">
                                    <i class="fa-solid fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-4 text-center text-gray-500">
                                No hay períodos académicos configurados
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-4 bg-white border-t">
            {{ $periodos->links() }}
        </div>
        @endif

        <!-- TAB CURSOS DE VERANO -->
        @if($tabActivo === 'cursos_verano')
        <div class="p-4 bg-gray-50">
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div>
                    <h3 class="text-lg font-semibold text-gray-700">Cursos de Verano</h3>
                    <p class="text-sm text-gray-500">Gestione los períodos de cursos de verano de forma independiente</p>
                </div>

                <button wire:click="openModalAgregarVerano"
                    class="px-4 py-2 text-white transition-colors rounded-lg bg-orange-500 hover:bg-orange-600">
                    <i class="mr-2 fa-solid fa-plus"></i>
                    Agregar Cursos Verano
                </button>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="text-xs text-white uppercase bg-orange-500">
                    <tr>
                        <th class="px-6 py-3">Año</th>
                        <th class="px-6 py-3">Inicio</th>
                        <th class="px-6 py-3">Fin</th>
                        <th class="px-6 py-3">Duración</th>
                        <th class="px-6 py-3">Estado</th>
                        <th class="px-6 py-3 text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($cursosVerano as $curso)
                        <tr class="bg-white border-b hover:bg-gray-50">
                            <td class="px-6 py-4 font-semibold">{{ $curso->anio }}</td>
                            <td class="px-6 py-4">{{ $curso->fecha_inicio->format('d/m/Y') }}</td>
                            <td class="px-6 py-4">{{ $curso->fecha_fin->format('d/m/Y') }}</td>
                            <td class="px-6 py-4 text-sm text-gray-600">
                                {{ $curso->fecha_fin->diffInDays($curso->fecha_inicio) }} días
                            </td>
                            <td class="px-6 py-4">
                                <button wire:click="toggleActivoVerano({{ $curso->id_curso_verano }})"
                                    class="px-2 py-1 text-xs font-semibold rounded {{ $curso->activo ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                                    {{ $curso->activo ? 'Activo' : 'Inactivo' }}
                                </button>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <button wire:click="openModalEditarVerano({{ $curso->id_curso_verano }})"
                                    class="text-blue-600 hover:text-blue-900 mr-2" title="Editar">
                                    <i class="fa-solid fa-edit"></i>
                                </button>
                                <button wire:click="deleteVerano({{ $curso->id_curso_verano }})"
                                    wire:confirm="¿Está seguro de eliminar este curso de verano?"
                                    class="text-red-600 hover:text-red-900" title="Eliminar">
                                    <i class="fa-solid fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-4 text-center text-gray-500">
                                No hay cursos de verano configurados
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-4 bg-white border-t">
            {{ $cursosVerano->links() }}
        </div>
        @endif

    @endif

    <!-- Modal Crear/Editar Feriado -->
    @if ($showModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto bg-gray-900 bg-opacity-50">
            <div class="relative w-full max-w-2xl p-4 mx-auto bg-white rounded-lg shadow-xl">
                <div class="flex items-center justify-between p-4 border-b">
                    <h3 class="text-lg font-semibold">
                        {{ $editMode ? 'Editar Feriado' : 'Nuevo Feriado' }}
                    </h3>
                    <button wire:click="closeModal" class="text-gray-400 hover:text-gray-600">
                        <i class="fa-solid fa-times"></i>
                    </button>
                </div>

                <form wire:submit.prevent="save">
                    <div class="p-4 space-y-4">
                        <div>
                            <label class="block mb-2 text-sm font-medium text-gray-700">
                                Nombre <span class="text-red-500">*</span>
                            </label>
                            <input type="text" wire:model="nombre"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-light-cloud-blue focus:border-transparent"
                                placeholder="Ej: Día del Trabajador">
                            @error('nombre') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block mb-2 text-sm font-medium text-gray-700">
                                Tipo <span class="text-red-500">*</span>
                            </label>
                            <select wire:model="tipo_feriado"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-light-cloud-blue focus:border-transparent">
                                <option value="feriado">Feriado</option>
                                <option value="semana_reajuste">Semana de Reajuste</option>
                                <option value="suspension_actividades">Suspensión de Actividades</option>
                            </select>
                            @error('tipo_feriado') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block mb-2 text-sm font-medium text-gray-700">
                                    Fecha Inicio <span class="text-red-500">*</span>
                                </label>
                                <input type="date" wire:model="fecha_inicio"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-light-cloud-blue focus:border-transparent">
                                @error('fecha_inicio') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <label class="block mb-2 text-sm font-medium text-gray-700">
                                    Fecha Fin <span class="text-red-500">*</span>
                                </label>
                                <input type="date" wire:model="fecha_fin"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-light-cloud-blue focus:border-transparent">
                                @error('fecha_fin') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <div>
                            <label class="block mb-2 text-sm font-medium text-gray-700">Descripción</label>
                            <textarea wire:model="descripcion" rows="3"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-light-cloud-blue focus:border-transparent"
                                placeholder="Descripción adicional (opcional)"></textarea>
                            @error('descripcion') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                        </div>

                        <div class="flex items-center">
                            <input type="checkbox" wire:model="activo" id="activo"
                                class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-2 focus:ring-light-cloud-blue">
                            <label for="activo" class="ml-2 text-sm font-medium text-gray-700">Activo</label>
                        </div>
                    </div>

                    <div class="flex justify-end gap-2 p-4 border-t">
                        <button type="button" wire:click="closeModal"
                            class="px-4 py-2 text-gray-700 bg-gray-200 rounded-lg hover:bg-gray-300">
                            Cancelar
                        </button>
                        <button type="submit"
                            class="px-4 py-2 text-white rounded-lg bg-light-cloud-blue hover:bg-cloud-blue">
                            {{ $editMode ? 'Actualizar' : 'Guardar' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    <!-- Modal Crear/Editar Período Académico -->
    @if ($showModalPeriodo)
        <div class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto bg-gray-900 bg-opacity-50">
            <div class="relative w-full max-w-4xl p-4 mx-auto bg-white rounded-lg shadow-xl">
                <div class="flex items-center justify-between p-4 border-b bg-green-50">
                    <h3 class="text-lg font-semibold text-green-800">
                        <i class="fa-solid fa-graduation-cap mr-2"></i>
                        {{ $editModePeriodo ? 'Editar Períodos Académicos' : 'Nuevo Año Académico (Ambos Semestres)' }}
                    </h3>
                    <button wire:click="closeModalPeriodo" class="text-gray-400 hover:text-gray-600">
                        <i class="fa-solid fa-times"></i>
                    </button>
                </div>

                <form wire:submit.prevent="savePeriodo">
                    <div class="p-4 space-y-6">
                        <!-- Año -->
                        <div class="max-w-xs">
                            <label class="block mb-2 text-sm font-medium text-gray-700">
                                Año <span class="text-red-500">*</span>
                            </label>
                            <input type="number" wire:model="periodo_anio" min="2020" max="2100"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent">
                            @error('periodo_anio') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                        </div>

                        <div class="border-t pt-6">
                            <h4 class="text-lg font-semibold text-gray-800 mb-4">
                                <i class="fa-solid fa-calendar-days mr-2 text-blue-500"></i>
                                Primer Semestre
                            </h4>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block mb-2 text-sm font-medium text-gray-700">
                                        Inicio de Actividades <span class="text-red-500">*</span>
                                    </label>
                                    <input type="date" wire:model="periodo_1_fecha_inicio"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent">
                                    @error('periodo_1_fecha_inicio') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                                </div>
                                <div>
                                    <label class="block mb-2 text-sm font-medium text-gray-700">
                                        Cierre de Actividades <span class="text-red-500">*</span>
                                    </label>
                                    <input type="date" wire:model="periodo_1_fecha_fin"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent">
                                    @error('periodo_1_fecha_fin') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        </div>

                        <div class="border-t pt-6">
                            <h4 class="text-lg font-semibold text-gray-800 mb-4">
                                <i class="fa-solid fa-calendar-days mr-2 text-orange-500"></i>
                                Segundo Semestre
                            </h4>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block mb-2 text-sm font-medium text-gray-700">
                                        Inicio de Actividades <span class="text-red-500">*</span>
                                    </label>
                                    <input type="date" wire:model="periodo_2_fecha_inicio"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent">
                                    @error('periodo_2_fecha_inicio') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                                </div>
                                <div>
                                    <label class="block mb-2 text-sm font-medium text-gray-700">
                                        Cierre de Actividades <span class="text-red-500">*</span>
                                    </label>
                                    <input type="date" wire:model="periodo_2_fecha_fin"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent">
                                    @error('periodo_2_fecha_fin') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        </div>

                        <div class="border-t pt-6 flex items-center">
                            <input type="checkbox" wire:model="periodo_activo" id="periodo_activo"
                                class="w-4 h-4 text-green-600 border-gray-300 rounded focus:ring-2 focus:ring-green-500">
                            <label for="periodo_activo" class="ml-2 text-sm font-medium text-gray-700">Períodos Activos</label>
                        </div>
                    </div>

                    <div class="flex justify-end gap-2 p-4 border-t">
                        <button type="button" wire:click="closeModalPeriodo"
                            class="px-4 py-2 text-gray-700 bg-gray-200 rounded-lg hover:bg-gray-300">
                            Cancelar
                        </button>
                        <button type="submit"
                            class="px-4 py-2 text-white rounded-lg bg-green-500 hover:bg-green-600">
                            {{ $editModePeriodo ? 'Actualizar Semestres' : 'Guardar Ambos Semestres' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    <!-- Modal Cursos de Verano -->
    <!-- Modal Crear/Editar Cursos de Verano (Independiente) -->
    @if ($showModalVerano)
        <div class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto bg-gray-900 bg-opacity-50">
            <div class="relative w-full max-w-2xl p-4 mx-auto bg-white rounded-lg shadow-xl">
                <div class="flex items-center justify-between p-4 border-b bg-orange-50">
                    <h3 class="text-lg font-semibold text-orange-800">
                        <i class="fa-solid fa-sun mr-2"></i>
                        {{ $editModeVerano ? 'Editar Curso de Verano' : 'Nuevo Curso de Verano' }}
                    </h3>
                    <button wire:click="closeModalVerano" class="text-gray-400 hover:text-gray-600">
                        <i class="fa-solid fa-times"></i>
                    </button>
                </div>

                <form wire:submit.prevent="saveVerano">
                    <div class="p-6 space-y-4">
                        <p class="text-sm text-gray-600 bg-blue-50 border border-blue-200 rounded-lg p-3">
                            <i class="fa-solid fa-info-circle mr-2"></i>
                            Los cursos de verano son completamente independientes de los períodos académicos.
                        </p>

                        <div>
                            <label class="block mb-2 text-sm font-medium text-gray-700">
                                Año <span class="text-red-500">*</span>
                            </label>
                            <input type="number" wire:model="verano_anio"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent"
                                min="2000" :max="now()->year + 10">
                            @error('verano_anio') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block mb-2 text-sm font-medium text-gray-700">
                                    Fecha Inicio <span class="text-red-500">*</span>
                                </label>
                                <input type="date" wire:model="verano_inicio"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent">
                                @error('verano_inicio') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <label class="block mb-2 text-sm font-medium text-gray-700">
                                    Fecha Fin <span class="text-red-500">*</span>
                                </label>
                                <input type="date" wire:model="verano_fin"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent">
                                @error('verano_fin') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <div>
                            <label class="flex items-center gap-3">
                                <input type="checkbox" wire:model="verano_activo"
                                    class="w-4 h-4 rounded border-gray-300 text-orange-500 focus:ring-orange-500">
                                <span class="text-sm font-medium text-gray-700">Activar este curso de verano</span>
                            </label>
                        </div>
                    </div>

                    <div class="flex justify-between gap-2 p-4 border-t">
                        @if($editModeVerano)
                        <button type="button" wire:click="deleteVerano({{ $cursoVeranoId }})"
                            wire:confirm="¿Está seguro de eliminar este curso?"
                            class="px-4 py-2 text-red-600 bg-red-50 rounded-lg hover:bg-red-100">
                            <i class="fa-solid fa-trash mr-1"></i>Eliminar
                        </button>
                        @else
                        <div></div>
                        @endif
                        <div class="flex gap-2">
                            <button type="button" wire:click="closeModalVerano"
                                class="px-4 py-2 text-gray-700 bg-gray-200 rounded-lg hover:bg-gray-300">
                                Cancelar
                            </button>
                            <button type="submit"
                                class="px-4 py-2 text-white rounded-lg bg-orange-500 hover:bg-orange-600">
                                {{ $editModeVerano ? 'Actualizar' : 'Crear' }} Curso de Verano
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    @endif

</div>
