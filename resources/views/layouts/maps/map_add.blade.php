<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <h2 class="text-xl font-semibold leading-tight">
                {{ __('Crear Nuevo Mapa') }}
            </h2>
        </div>
    </x-slot>

    <div class="p-6 bg-white rounded-lg shadow-md dark:bg-gray-800">
       <div class="grid grid-cols-1 gap-4 mb-6 md:grid-cols-4">
            <!-- Universidad -->
            <div>
                <label for="universidad"
                    class="block text-sm font-medium text-gray-700 dark:text-gray-300">Universidad</label>
                <select id="universidad" name="universidad"
                    class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                    <option value="">Seleccione una universidad</option>
                    @foreach ($universidades as $universidad)
                        <option value="{{ $universidad->id_universidad }}">{{ $universidad->nombre_universidad }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Sede -->
            <div>
                <label for="sede" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Sede</label>
                <select id="sede" name="sede"
                    class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                    disabled>
                    <option value="">Seleccione una sede</option>
                </select>
            </div>

            <!-- Facultad -->
            <div>
                <label for="facultad"
                    class="block text-sm font-medium text-gray-700 dark:text-gray-300">Facultad</label>
                <select id="facultad" name="facultad"
                    class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                    disabled>
                    <option value="">Seleccione una facultad</option>
                </select>
            </div>

            <!-- Piso -->
            <div>
                <label for="piso" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Piso</label>
                <select id="piso" name="piso"
                    class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                    disabled>
                    <option value="">Seleccione un piso</option>
                </select>
            </div>
        </div>

        <div class="w-full mb-4 md:w-2/3">
            <x-form.label for="nombre_mapa" :value="__('Nombre del Mapa')" />
            <input type="text" name="nombre_mapa" id="nombre_mapa"
                class="block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                readonly required>
        </div>

        <!-- Input para subir imagen o video -->
        <div class="mb-4">
            <label for="mapImage" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Subir Imagen del Mapa</label>
            <input type="file" id="mapImage" accept="image/*,video/*" class="block w-full mt-1 text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 dark:file:bg-gray-700 dark:file:text-gray-300 dark:hover:file:bg-gray-600">
        </div>

        <!-- Botones para agregar indicadores -->
        <div class="mb-4 space-x-4">
            <button id="addIndicatorBtn" type="button" 
                class="px-4 py-2 text-white bg-blue-600 rounded hover:bg-blue-700">Agregar Indicador</button>
            <button id="clearIndicatorsBtn" type="button"
                class="px-4 py-2 text-white bg-red-600 rounded hover:bg-red-700">Limpiar Indicadores</button>
        </div>

        <!-- Canvas con contenedor relativo para indicadores -->
        <div class="relative p-4 border-2 border-gray-300 border-dashed rounded-lg bg-gray-50 dark:bg-gray-900" style="padding-top: 75%;">

            <!-- Canvas para dibujo -->
            <canvas id="mapCanvas" 
                class="absolute top-0 left-0 w-full h-full bg-white dark:bg-gray-800"></canvas>

            <!-- Contenedor para indicadores (posicionados absolutamente) -->
            <div id="indicatorsContainer" class="absolute top-0 left-0 w-full h-full pointer-events-none"></div>

        </div>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const universidadSelect = document.getElementById('universidad');
            const sedeSelect = document.getElementById('sede');
            const facultadSelect = document.getElementById('facultad');
            const pisoSelect = document.getElementById('piso');
            const nombreMapaInput = document.getElementById('nombre_mapa');
            const mapImageInput = document.getElementById('mapImage');
            const canvas = document.getElementById('mapCanvas');
            const ctx = canvas.getContext('2d');
            const indicatorsContainer = document.getElementById('indicatorsContainer');
            const addIndicatorBtn = document.getElementById('addIndicatorBtn');
            const clearIndicatorsBtn = document.getElementById('clearIndicatorsBtn');

            // Variables para la imagen/video cargada
            let mapImage = null;
            let isAddingIndicator = false;

            // Ajustar tamaño del canvas al contenedor (mantener aspecto)
            function resizeCanvas() {
                const container = canvas.parentElement;
                canvas.width = container.clientWidth;
                canvas.height = container.clientHeight;
                drawCanvas();
            }
            window.addEventListener('resize', resizeCanvas);

            // Dibuja la imagen en el canvas
            function drawCanvas() {
                if (!mapImage) {
                    ctx.clearRect(0, 0, canvas.width, canvas.height);
                    return;
                }

                // Ajustar la imagen al canvas manteniendo la proporción
                const canvasRatio = canvas.width / canvas.height;
                const imageRatio = mapImage.width / mapImage.height;
                let drawWidth, drawHeight, offsetX, offsetY;

                if (imageRatio > canvasRatio) {
                    drawWidth = canvas.width;
                    drawHeight = canvas.width / imageRatio;
                    offsetX = 0;
                    offsetY = (canvas.height - drawHeight) / 2;
                } else {
                    drawHeight = canvas.height;
                    drawWidth = canvas.height * imageRatio;
                    offsetX = (canvas.width - drawWidth) / 2;
                    offsetY = 0;
                }

                ctx.clearRect(0, 0, canvas.width, canvas.height);
                ctx.drawImage(mapImage, offsetX, offsetY, drawWidth, drawHeight);
            }

            // Manejar carga de imagen o video
            mapImageInput.addEventListener('change', function(e) {
                const file = this.files[0];
                if (!file) return;

                if (file.type.startsWith('image/')) {
                    const img = new Image();
                    img.onload = () => {
                        mapImage = img;
                        resizeCanvas();
                    };
                    img.src = URL.createObjectURL(file);
                } else if (file.type.startsWith('video/')) {
                    // Si quieres mostrar video en canvas, se necesita usar <video>
                    alert('Visualización de video aún no implementada.');
                    // Aquí podrías añadir el código para video si quieres.
                }
            });

            // Agregar indicador al hacer click en canvas solo cuando está activo el modo "Agregar indicador"
            addIndicatorBtn.addEventListener('click', () => {
                isAddingIndicator = true;
                addIndicatorBtn.disabled = true;
                addIndicatorBtn.textContent = "Haz clic en el mapa para agregar un indicador";
            });

            clearIndicatorsBtn.addEventListener('click', () => {
                indicatorsContainer.innerHTML = '';
            });

            canvas.addEventListener('click', function(event) {
                if (!isAddingIndicator) return;

                // Calcular posición relativa en % para que el indicador sea responsive
                const rect = canvas.getBoundingClientRect();
                const xPercent = ((event.clientX - rect.left) / rect.width) * 100;
                const yPercent = ((event.clientY - rect.top) / rect.height) * 100;

                // Crear el indicador
                const indicator = document.createElement('div');
                indicator.className = 'indicator bg-red-600 rounded-full w-6 h-6 border-2 border-white shadow-lg cursor-pointer';
                indicator.style.position = 'absolute';
                indicator.style.left = xPercent + '%';
                indicator.style.top = yPercent + '%';
                indicator.style.transform = 'translate(-50%, -50%)';
                indicator.title = `Indicador (${xPercent.toFixed(1)}%, ${yPercent.toFixed(1)}%)`;

                // Opcional: permitir eliminar indicador con click derecho
                indicator.addEventListener('contextmenu', e => {
                    e.preventDefault();
                    indicator.remove();
                });

                indicatorsContainer.appendChild(indicator);

                // Desactivar modo agregar indicador
                isAddingIndicator = false;
                addIndicatorBtn.disabled = false;
                addIndicatorBtn.textContent = "Agregar Indicador";
            });

            // Carga dinámica selects universidad, sede, facultad, piso (tu código original)
            universidadSelect.addEventListener('change', function() {
                const universidadId = this.value;
                sedeSelect.innerHTML = '<option value="">Cargando sedes...</option>';
                facultadSelect.innerHTML = '<option value="">Seleccione una facultad</option>';
                pisoSelect.innerHTML = '<option value="">Seleccione un piso</option>';
                facultadSelect.disabled = true;
                pisoSelect.disabled = true;

                if (universidadId) {
                    fetch(`/sedes/${universidadId}`)
                        .then(response => response.json())
                        .then(data => {
                            sedeSelect.innerHTML = '<option value="">Seleccione una sede</option>';
                            data.forEach(sede => {
                                sedeSelect.innerHTML +=
                                    `<option value="${sede.id_sede}">${sede.nombre_sede}</option>`;
                            });
                            sedeSelect.disabled = false;
                        });
                } else {
                    sedeSelect.innerHTML = '<option value="">Seleccione una sede</option>';
                    sedeSelect.disabled = true;
                }
            });

            sedeSelect.addEventListener('change', function() {
                const sedeId = this.value;
                facultadSelect.innerHTML = '<option value="">Cargando facultades...</option>';
                pisoSelect.innerHTML = '<option value="">Seleccione un piso</option>';
                pisoSelect.disabled = true;

                if (sedeId) {
                    fetch(`/facultades-por-sede/${sedeId}`)
                        .then(response => response.json())
                        .then(data => {
                            facultadSelect.innerHTML =
                                '<option value="">Seleccione una facultad</option>';
                            data.forEach(facultad => {
                                facultadSelect.innerHTML +=
                                    `<option value="${facultad.id_facultad}">${facultad.nombre_facultad}</option>`;
                            });
                            facultadSelect.disabled = false;
                        });
                } else {
                    facultadSelect.innerHTML = '<option value="">Seleccione una facultad</option>';
                    facultadSelect.disabled = true;
                }
            });

            facultadSelect.addEventListener('change', function() {
                const facultadId = this.value;
                pisoSelect.innerHTML = '<option value="">Cargando pisos...</option>';

                if (facultadId !== "") {
                    fetch(`/pisos/${facultadId}`)
                        .then(response => response.json())
                        .then(data => {
                            pisoSelect.innerHTML = '<option value="">Seleccione un piso</option>';
                            data.forEach(piso => {
                                pisoSelect.innerHTML +=
                                    `<option value="${piso.id}">Piso ${piso.numero_piso}</option>`;
                            });
                            pisoSelect.disabled = false;
                        });
                } else {
                    pisoSelect.innerHTML = '<option value="">Seleccione un piso</option>';
                    pisoSelect.disabled = true;
                }
            });

            // Inicializa canvas tamaño correcto
            resizeCanvas();
        });
    </script>

    <style>
        /* Indicadores estilos */
        .indicator {
            transition: transform 0.2s ease;
        }
        .indicator:hover {
            transform: translate(-50%, -50%) scale(1.3);
            z-index: 10;
        }
    </style>
</x-app-layout>
