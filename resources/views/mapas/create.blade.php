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

    <div class="p-6 bg-white rounded-lg shadow-md dark:bg-gray-800">
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
                <select id="piso" name="piso" class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                    <option value="">Seleccione un piso</option>
                </select>
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
                    <button id="saveMapBtn" type="button" class="hidden px-4 py-2 text-white bg-green-600 rounded hover:bg-green-700">Guardar Mapa</button>
                </div>
            </div>
        </div>

        <!-- Formulario oculto para guardar -->
        <form id="saveMapForm" method="POST" action="{{ route('mapas.store') }}" enctype="multipart/form-data" class="hidden">
            @csrf
            <input type="text" name="nombre_mapa" id="nombre_mapa_form" value="">
            <input type="hidden" name="piso_id" id="piso_id">
            <input type="hidden" name="bloques" id="bloques">
            <input type="file" name="archivo" id="archivo">
        </form>
    </div>

    @vite(['resources/js/views/mapas/create.js'])
    @vite(['resources/css/views/mapas/create.css'])
</x-app-layout>
