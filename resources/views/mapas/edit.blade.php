<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <div class="flex items-center gap-3">
                <div class="p-2 rounded-xl bg-light-cloud-blue">
                    <i class="fa-solid fa-map text-white text-2xl"></i>
                </div>
                <div>
                    <h2 class="text-2xl font-bold leading-tight">Gestión de Mapas</h2>
                    <p class="text-gray-500 text-sm">Agrega, edita y administra la ubicación de los espacios en el mapa institucional</p>
                </div>
            </div>
        </div>
    </x-slot>

    <a href="http://127.0.0.1:8000/dashboard" class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-white bg-gray-600 rounded-lg hover:bg-gray-700 transition-colors duration-200 shadow-md mb-4">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="size-5">
            <path d="M11.47 3.841a.75.75 0 0 1 1.06 0l8.69 8.69a.75.75 0 1 0 1.06-1.061l-8.689-8.69a2.25 2.25 0 0 0-3.182 0l-8.69 8.69a.75.75 0 1 0 1.061 1.06l8.69-8.689Z"></path>
            <path d="m12 5.432 8.159 8.159c.03.03.06.058.091.086v6.198c0 1.035-.84 1.875-1.875 1.875H15a.75.75 0 0 1-.75-.75v-4.5a.75.75 0 0 0-.75-.75h-3a.75.75 0 0 0-.75.75V21a.75.75 0 0 1-.75.75H5.625a1.875 1.875 0 0 1-1.875-1.875v-6.198a2.29 2.29 0 0 0 .091-.086L12 5.432Z"></path>
        </svg>
        Volver
    </a>
    <div class="p-6 bg-white rounded-lg shadow-md dark:bg-gray-800">
        <div class="p-3 rounded-lg bg-gray-50 mb-6">
            <h4 class="mb-2 text-base font-semibold text-gray-800">Información del Mapa</h4>
            <div class="grid grid-cols-1 gap-2 text-sm md:grid-cols-2">
                <div>
                    <span class="font-medium text-gray-600">Nombre:</span>
                    <span class="ml-2 text-gray-800">{{ $mapa->nombre_mapa ?? 'N/A' }}</span>
                </div>
                @if($mapa->piso)
                <div>
                    <span class="font-medium text-gray-600">Piso:</span>
                    <span class="ml-2 text-gray-800">{{ $mapa->piso->nombre_piso ?? 'Piso ' . $mapa->piso->numero_piso }}</span>
                </div>
                @endif
                @if($mapa->piso && $mapa->piso->facultad)
                <div>
                    <span class="font-medium text-gray-600">Facultad:</span>
                    <span class="ml-2 text-gray-800">{{ $mapa->piso->facultad->nombre_facultad ?? 'N/A' }}</span>
                </div>
                @endif
                <div>
                    <span class="font-medium text-gray-600">Fecha de creación:</span>
                    <span class="ml-2 text-gray-800">{{ $mapa->created_at ? $mapa->created_at->format('d/m/Y H:i') : 'N/A' }}</span>
                </div>
            </div>
        </div>
        <!-- Selectores de sede, facultad y piso -->
        <div class="grid grid-cols-1 gap-4 mb-6 md:grid-cols-3">
            <div>
                <label for="sede" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Sede</label>
                <input type="text" id="sede" name="sede" value="{{ $sede->nombre_sede ?? 'Sin sede' }}" class="block w-full mt-1 border-gray-300 rounded-md shadow-sm bg-gray-100 dark:bg-gray-700 dark:border-gray-600 dark:text-white" readonly>
                <input type="hidden" id="id_sede" value="{{ $sede->id_sede ?? '' }}">
            </div>

            <div>
                <label for="facultad" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Facultad</label>
                <input type="text" id="facultad" name="facultad" value="{{ $facultad->nombre_facultad ?? 'Sin facultad' }}" class="block w-full mt-1 border-gray-300 rounded-md shadow-sm bg-gray-100 dark:bg-gray-700 dark:border-gray-600 dark:text-white" readonly>
                <input type="hidden" id="id_facultad" value="{{ $facultad->id_facultad ?? '' }}">
            </div>

            <div>
                <label for="piso" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Piso</label>
                <div class="flex gap-2">
                    <select id="piso" name="piso" class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                        <option value="">Seleccione un piso</option>
                    </select>
                    <button id="selectPisoBtn" type="button" class="px-3 py-1 mt-1 text-xs font-semibold text-white bg-blue-600 rounded hover:bg-blue-700" style="display:none;">Seleccionar</button>
                </div>
            </div>


        </div>

        <!-- Contenedor principal con diseño de dos columnas -->
        <div class="flex flex-col gap-6 md:flex-row">
            <!-- Columna izquierda - Lista de espacios -->
            <div class="w-full md:w-1/4">
                <div class="h-full p-4 bg-white rounded-lg shadow dark:bg-gray-800">
                    <h3 class="mb-3 text-lg font-semibold text-gray-900 dark:text-white">Espacios Disponibles</h3>
                    <div class="py-10 text-center text-gray-500 dark:text-gray-400" id="emptySpacesMessage">
                        Seleccione un piso para cargar los espacios
                    </div>
                    <div id="espaciosList" class="space-y-2 max-h-[500px] overflow-y-auto">
                    </div>
                </div>
            </div>

            <!-- Columna derecha - Canvas y controles -->
            <div class="w-full md:w-3/4">
                <!-- Nombre del mapa (solo visible cuando hay piso seleccionado) -->
                <div id="nombreMapaContainer" class="hidden mb-4">
                    <x-form.label for="nombre_mapa" :value="__('Nombre del Mapa')" />
                    <input type="text" name="nombre_mapa" id="nombre_mapa" class="block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white" readonly>
                </div>

                <!-- Carga de imagen -->
                <div class="mb-4">
                    <label for="mapImage" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Subir Imagen del Mapa</label>
                    <input type="file" id="mapImage" accept="image/*" class="block w-full mt-1 text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 dark:file:bg-gray-700 dark:file:text-gray-300 dark:hover:file:bg-gray-600">
                    @if($mapa->ruta_mapa)
                        <div class="mt-2">
                            <span class="text-xs text-gray-500">Imagen actual:</span><br>
                            <img id="currentMapImage" src="{{ asset('storage/' . $mapa->ruta_mapa) }}" alt="Mapa actual" class="max-w-xs max-h-40 border rounded shadow">
                        </div>
                    @endif
                </div>



                <!-- Contenedor del mapa -->
                <div class="relative p-4 border-2 border-gray-300 border-dashed rounded-lg bg-gray-50 dark:bg-gray-900" style="padding-top: 75%;">
                    <!-- Canvas para la imagen base -->
                    <canvas id="mapCanvas" class="absolute top-0 left-0 w-full h-full bg-white dark:bg-gray-800"></canvas>

                    <!-- Canvas para los indicadores (transparente) -->
                    <canvas id="indicatorsCanvas" class="absolute top-0 left-0 w-full h-full pointer-events-auto"></canvas>
                </div>

                <!-- Botones de acción -->
                <div class="flex justify-end gap-4 mt-4">
                    <button id="clearIndicatorsBtn" type="button" class="btn btn-clear px-4 py-2 text-sm">Limpiar Todo</button>
                    <button id="saveMapBtn" type="button" class="hidden px-4 py-2 text-white bg-green-600 rounded hover:bg-green-700">Guardar Edición</button>
                </div>
            </div>
        </div>

        <!-- Formulario oculto para guardar -->
        <form id="saveMapForm" method="POST" action="{{ route('mapas.update', $mapa->id_mapa) }}" enctype="multipart/form-data" class="hidden">
            @csrf
            @method('PUT')
            <input type="text" name="nombre_mapa" id="nombre_mapa_form" value="{{ $mapa->nombre_mapa ?? '' }}">
            <input type="hidden" name="piso_id" id="piso_id" value="{{ $mapa->piso_id ?? '' }}">
            <input type="hidden" name="bloques" id="bloques">
            <input type="file" name="archivo" id="archivo">
        </form>
    </div>

    <div data-page-config="MapasEditConfig" data-config="{{ json_encode(['rutaMapaUrl' => $mapa->ruta_mapa ? asset('storage/' . $mapa->ruta_mapa) : null, 'pisoId' => $mapa->piso_id ?? '']) }}" class="hidden"></div>
    @vite(['resources/js/views/mapas/edit.js'])
    @vite(['resources/css/views/mapas/edit.css'])
</x-app-layout>
