<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
<<<<<<< HEAD
            <h2 class="text-xl font-semibold leading-tight" style="font-style: oblique;">
                {{ __('Ingreso de Mapas') }}
<<<<<<< HEAD
=======
            <h2 class="text-xl font-semibold leading-tight">
                {{ __('Crear Nuevo Mapa') }}
>>>>>>> Nperez
=======
>>>>>>> 6c05e560f5edb88b89bd0fe7d8d71ecb8386c841
            </h2>
        </div>
    </x-slot>

    <div class="p-6 bg-white rounded-lg shadow-md dark:bg-gray-800">
<<<<<<< HEAD
<<<<<<< HEAD
=======
>>>>>>> 6c05e560f5edb88b89bd0fe7d8d71ecb8386c841
        <!-- FORMULARIO -->
        <form method="POST" action="{{ route('mapas.store') }}" enctype="multipart/form-data" id="mapaForm">
            @csrf

            <!-- Selectores -->
            <input type="hidden" name="bloques_json" id="bloques_json">

            <div class="gap-4 mb-6 md:grid-cols-2">
                <div class="p-6 bg-white rounded-lg shadow-md dark:bg-gray-800">
                    <div class="flex flex-wrap gap-4 mb-6">
                        <!-- Universidad -->
                        <div class="w-full md:w-1/3">
                            <x-form.label for="id_universidad" :value="__('Universidad')" />
                            <select name="id_universidad" id="id_universidad"
                                class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                <option value="">Seleccione una universidad</option>
                                @foreach ($universidades as $uni)
                                    <option value="{{ $uni->id_universidad }}">{{ $uni->nombre_universidad }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Facultad -->
                        <div class="w-full md:w-1/3">
                            <x-form.label for="id_facultad" :value="__('Facultad')" />
                            <select name="id_facultad" id="id_facultad"
                                class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                                disabled>
                                <option value="">Seleccione una facultad</option>
                            </select>
                        </div>

                        <!-- Piso -->
                        <div class="w-full md:w-1/3">
                            <x-form.label for="piso_id" :value="__('Piso')" />
                            <select name="piso_id" id="piso_id"
                                class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                                disabled>
                                <option value="">Seleccione un piso</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Nombre del mapa + botones -->
            <div class="mb-6">
                <div class="flex justify-between items-end gap-4 flex-wrap">
                    <div class="flex flex-col w-full md:w-2/3">
                        <label for="nombre_mapa" class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            {{ __('Nombre del Mapa') }}
                        </label>
                        <input type="text" name="nombre_mapa" id="nombre_mapa"
                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 h-10 text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                            readonly required>
                    </div>

                    <div class="flex gap-2 w-full md:w-auto justify-end mt-4 md:mt-0">
                        <x-button id="btnClearCanvas" variant="danger" class="h-10 whitespace-nowrap" type="button">
                            <i class="mr-2 fas fa-trash"></i> Limpiar Todo
                        </x-button>
                        <x-button id="btnSaveMap" variant="success" class="h-10 whitespace-nowrap" type="submit">
                            <i class="mr-2 fas fa-save"></i> Guardar Mapa
                        </x-button>
                    </div>
                </div>
            </div>

            <!-- Botones de imagen y espacio -->
            <div class="flex justify-between items-center gap-4 mb-6 flex-wrap">
                <!-- Cargar Plano -->
                <div>
                    <x-button variant="secondary" class="h-10 whitespace-nowrap" type="button"
                        onclick="document.getElementById('mapImageUpload').click()">
                        <i class="mr-2 fas fa-upload"></i> Cargar Plano
                    </x-button>
                    <input id="mapImageUpload" name="imagen" type="file" accept="image/*" style="display: none;"
                        required>
                </div>

                <!-- Agregar Espacio -->
                <x-button id="btnAddBlock" variant="success" class="h-10 whitespace-nowrap" type="button" disabled>
                    <i class="mr-2 fas fa-plus"></i> Agregar Espacio
                </x-button>
                <div class="w-full md:w-1/3">
                    <x-form.label for="espacios_disponibles" :value="__('Espacios disponibles')" />
                    <input type="text" id="espacios_disponibles"
                        class="block w-full bg-gray-100 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-white rounded-md px-3 py-2"
                        readonly value="Seleccione un piso">
                </div>
            </div>

            <!-- Vista previa + canvas -->
            <div class="flex flex-col md:flex-row gap-6">
                <!-- Imagen cargada -->
                <div
                    class="w-full md:w-1/2 border-2 border-dashed border-gray-300 rounded-lg p-4 bg-gray-50 dark:bg-gray-900">
                    <div class="relative h-96">
                        <img id="previewImage" class="absolute top-0 left-0 w-full h-full object-contain rounded-md"
                            alt="Vista previa del mapa" style="display: none;">
                        <div id="noImageMessage"
                            class="absolute inset-0 flex items-center justify-center text-gray-500">
                            <span>No hay imagen cargada</span>
                        </div>
                    </div>
                </div>

                <!-- Canvas -->
                <div
                    class="w-full md:w-1/2 border-2 border-dashed border-gray-300 rounded-lg p-4 bg-gray-50 dark:bg-gray-900">
                    <div class="relative" style="padding-top: 75%;">
                        <canvas id="mapCanvas"
                            class="absolute top-0 left-0 w-full h-full bg-white dark:bg-gray-800"></canvas>
                    </div>
                </div>
            </div>
<<<<<<< HEAD
=======
        <!-- Selectores de sede, facultad y piso -->
        <div class="grid grid-cols-1 gap-4 mb-6 md:grid-cols-3">
            <div>
                <label for="sede" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Sede</label>
                <input type="text" id="sede" name="sede" value="Talcahuano" class="block w-full mt-1 border-gray-300 rounded-md shadow-sm bg-gray-100 dark:bg-gray-700 dark:border-gray-600 dark:text-white" readonly>
                <input type="hidden" id="id_sede" value="TH">
            </div>

            <div>
                <label for="facultad" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Facultad</label>
                <input type="text" id="facultad" name="facultad" value="Instituto Tecnológico" class="block w-full mt-1 border-gray-300 rounded-md shadow-sm bg-gray-100 dark:bg-gray-700 dark:border-gray-600 dark:text-white" readonly>
                <input type="hidden" id="id_facultad" value="IT_TH">
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
                    <div id="espaciosList" class="space-y-2 max-h-[500px] overflow-y-auto">
                        <!-- Los espacios se cargarán aquí -->
                        <div class="py-10 text-center text-gray-500 dark:text-gray-400" id="emptySpacesMessage">
                            Seleccione un piso para cargar los espacios
                        </div>
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
                    <button id="clearIndicatorsBtn" type="button" class="px-4 py-2 text-white bg-red-600 rounded hover:bg-red-700">Limpiar Todo</button>
                    <button id="saveMapBtn" type="button" class="hidden px-4 py-2 text-white bg-green-600 rounded hover:bg-green-700">Guardar Mapa</button>
                </div>
            </div>
        </div>

        <!-- Formulario oculto para guardar -->
        <form id="saveMapForm" method="POST" action="{{ route('mapas.store') }}" enctype="multipart/form-data" class="hidden">
            @csrf
            <input type="text" name="nombre_mapa" id="nombre_mapa_form" value="">
            <input type="hidden" name="id_espacio" id="id_espacio">
            <input type="hidden" name="bloques" id="bloques">
            <input type="file" name="archivo" id="archivo">
>>>>>>> Nperez
=======
>>>>>>> 6c05e560f5edb88b89bd0fe7d8d71ecb8386c841
        </form>
    </div>

    <script>
<<<<<<< HEAD
<<<<<<< HEAD
=======
>>>>>>> 6c05e560f5edb88b89bd0fe7d8d71ecb8386c841
        document.addEventListener('DOMContentLoaded', () => {
            // 1. Selectores de universidad, facultad y piso
            const universidadSelect = document.getElementById('id_universidad');
            const facultadSelect = document.getElementById('id_facultad');
            const pisoSelect = document.getElementById('piso_id');
            const nombreMapaInput = document.getElementById('nombre_mapa');
            const bloquesJsonInput = document.getElementById('bloques_json');

            // 2. Elementos para la imagen
            const inputImage = document.getElementById('mapImageUpload');
            const previewImage = document.getElementById('previewImage');
            const noImageMessage = document.getElementById('noImageMessage');
            const btnClearCanvas = document.getElementById('btnClearCanvas');
            const btnAddBlock = document.getElementById('btnAddBlock');
            const btnSaveMap = document.getElementById('btnSaveMap');
            const form = document.getElementById('mapaForm');

            // 3. Configuración del canvas
            const canvas = document.getElementById('mapCanvas');
            const ctx = canvas.getContext('2d');
            let squares = [];
            let selectedSquare = null;
            let offsetX, offsetY;
            let isDragging = false;
            let backgroundImage = null; // Para almacenar la imagen de fondo

            // 4. Manejo de los selectores
            universidadSelect.addEventListener('change', async () => {
                const id = universidadSelect.value;
                facultadSelect.innerHTML = '<option value="">Seleccione una facultad</option>';
                pisoSelect.innerHTML = '<option value="">Seleccione un piso</option>';
                facultadSelect.disabled = true;
                pisoSelect.disabled = true;
                nombreMapaInput.value = '';
                document.getElementById('espacios_disponibles').value = 'Seleccione un piso';
                btnAddBlock.disabled = true;

                if (id) {
                    try {
                        const res = await fetch(`/mapas/facultades/${id}`);
                        if (!res.ok) throw new Error('Error al cargar facultades');

                        const data = await res.json();
                        data.forEach(fac => {
                            facultadSelect.innerHTML +=
                                `<option value="${fac.id_facultad}">${fac.nombre_facultad}</option>`;
                        });
                        facultadSelect.disabled = false;
                    } catch (error) {
                        console.error('Error:', error);
                        alert('Error al cargar las facultades');
                    }
                }
            });

            facultadSelect.addEventListener('change', async () => {
                const id = facultadSelect.value;
                pisoSelect.innerHTML = '<option value="">Seleccione un piso</option>';
                pisoSelect.disabled = true;
                nombreMapaInput.value = '';
                document.getElementById('espacios_disponibles').value = 'Seleccione un piso';
                btnAddBlock.disabled = true;

                if (id) {
                    try {
                        const res = await fetch(`/mapas/pisos/${id}`);
                        if (!res.ok) throw new Error('Error al cargar pisos');

                        const data = await res.json();
                        data.forEach(piso => {
                            const nombre = piso.nombre ?? `Piso ${piso.numero_piso}`;
                            pisoSelect.innerHTML +=
                                `<option value="${piso.id}">${nombre}</option>`;
                        });
                        pisoSelect.disabled = false;
                    } catch (error) {
                        console.error('Error:', error);
                        alert('Error al cargar los pisos');
                    }
                }
            });

            pisoSelect.addEventListener('change', async () => {
                const nombrePiso = pisoSelect.options[pisoSelect.selectedIndex]?.text || '';
                const nombreFacultad = facultadSelect.options[facultadSelect.selectedIndex]?.text || '';
                const nombreUniversidad = universidadSelect.options[universidadSelect.selectedIndex]
                    ?.text || '';
                nombreMapaInput.value = `${nombrePiso}, ${nombreFacultad}, ${nombreUniversidad}`;

                const pisoId = pisoSelect.value;

                if (pisoId) {
                    try {
                        const res = await fetch(`/mapas/contar-espacios/${pisoId}`);
                        if (!res.ok) throw new Error('Error al contar espacios');

                        const data = await res.json();
                        const registrados = data.cantidad ?? 0;
                        const restantes = Math.max(1 - registrados, 0);

                        document.getElementById('espacios_disponibles').value =
                            `${restantes} espacios disponibles`;

                        btnAddBlock.disabled = (restantes <= 0);
                    } catch (error) {
                        console.error('Error:', error);
                        document.getElementById('espacios_disponibles').value = 'Error al obtener';
                        btnAddBlock.disabled = true;
                    }
                }
            });

            // 5. Configuración inicial del canvas
            function initCanvas() {
                const container = canvas.parentElement;
                const containerWidth = container.clientWidth;

                // Mantener relación de aspecto 4:3
                canvas.width = containerWidth;
                canvas.height = Math.floor(containerWidth * 0.75);

                drawCanvas();
            }
            initCanvas();
            window.addEventListener('resize', initCanvas);

            // 6. Manejo de la imagen
            inputImage.addEventListener('change', (e) => {
                const file = e.target.files[0];
                if (!file || !file.type.startsWith('image/')) {
                    alert('Por favor, selecciona un archivo de imagen válido (JPEG, PNG, etc.).');
                    return;
                }

                const reader = new FileReader();
                reader.onload = (event) => {
                    previewImage.src = event.target.result;
                    previewImage.style.display = 'block';
                    noImageMessage.style.display = 'none';

                    // Cargar imagen para usarla como fondo del canvas
                    const img = new Image();
                    img.onload = () => {
                        backgroundImage = null;
                        // Redimensionar canvas para que coincida con la imagen cargada
                        canvas.width = img.width;
                        canvas.height = img.height;
                        drawCanvas(); // Redibujar con la imagen de fondo
                    };
                    img.src = event.target.result;
                };
                reader.readAsDataURL(file);
            });

            // 7. Funciones para dibujar
            function drawCanvas() {
                ctx.clearRect(0, 0, canvas.width, canvas.height);

                // Dibujar la imagen de fondo si existe
                if (backgroundImage) {
                    ctx.drawImage(backgroundImage, 0, 0, canvas.width, canvas.height);
                } else {
                    // Si no hay imagen, dibujar un fondo blanco/gris
                    ctx.fillStyle = document.documentElement.classList.contains('dark') ? '#374151' : '#ffffff';
                    ctx.fillRect(0, 0, canvas.width, canvas.height);
                }

                drawSquares();
            }

            function drawSquares() {
                squares.forEach((square) => {
                    ctx.fillStyle = 'rgba(59, 130, 246, 0.3)'; // Azul con transparencia
                    ctx.strokeStyle = '#1e40af'; // Azul oscuro
                    ctx.lineWidth = 2;

                    if (ctx.roundRect) {
                        ctx.beginPath();
                        ctx.roundRect(square.x, square.y, square.width, square.height, 8);
                        ctx.fill();
                        ctx.stroke();
                    } else {
                        ctx.fillRect(square.x, square.y, square.width, square.height);
                        ctx.strokeRect(square.x, square.y, square.width, square.height);
                    }
                });
            }

            // 8. Interacción con el canvas
            canvas.addEventListener('mousedown', (e) => {
                const rect = canvas.getBoundingClientRect();
                const scaleX = canvas.width / rect.width;
                const scaleY = canvas.height / rect.height;
                const x = (e.clientX - rect.left) * scaleX;
                const y = (e.clientY - rect.top) * scaleY;

                selectedSquare = squares.find(square =>
                    x > square.x && x < square.x + square.width &&
                    y > square.y && y < square.y + square.height
                );

                if (selectedSquare) {
                    offsetX = x - selectedSquare.x;
                    offsetY = y - selectedSquare.y;
                    isDragging = true;
                }
            });

            canvas.addEventListener('mousemove', (e) => {
                if (!isDragging || !selectedSquare) return;

                const rect = canvas.getBoundingClientRect();
                const scaleX = canvas.width / rect.width;
                const scaleY = canvas.height / rect.height;
                const x = (e.clientX - rect.left) * scaleX;
                const y = (e.clientY - rect.top) * scaleY;

                selectedSquare.x = x - offsetX;
                selectedSquare.y = y - offsetY;

                // Limitar al área del canvas
                selectedSquare.x = Math.max(0, Math.min(canvas.width - selectedSquare.width, selectedSquare
                    .x));
                selectedSquare.y = Math.max(0, Math.min(canvas.height - selectedSquare.height,
                    selectedSquare.y));

                drawCanvas();
            });

            canvas.addEventListener('mouseup', () => {
                isDragging = false;
                selectedSquare = null;
            });

            canvas.addEventListener('mouseleave', () => {
                isDragging = false;
                selectedSquare = null;
            });

            // 9. Botones de acción
            btnAddBlock.addEventListener('click', () => {
                const newSquare = {
                    x: canvas.width / 2 - 50,
                    y: canvas.height / 2 - 50,
                    width: 100,
                    height: 100,
                    id: Date.now() // ID único para cada cuadrado
                };

                squares.push(newSquare);
                drawCanvas();

                // Actualizar contador de espacios disponibles
                updateEspaciosDisponibles();
            });

            btnClearCanvas.addEventListener('click', (e) => {
                e.preventDefault();

                squares = [];
                previewImage.src = '';
                previewImage.style.display = 'none';
                noImageMessage.style.display = 'flex';
                inputImage.value = '';
                backgroundImage = null; // Eliminar la imagen de fondo
                drawCanvas();

                // Restablecer contador
                if (pisoSelect.value) {
                    updateEspaciosDisponibles();
                } else {
                    document.getElementById('espacios_disponibles').value = 'Seleccione un piso';
                }
            });

            // 10. Función para actualizar espacios disponibles
            async function updateEspaciosDisponibles() {
                const pisoId = pisoSelect.value;
                if (!pisoId) return;

                try {
                    const res = await fetch(`/mapas/contar-espacios/${pisoId}`);
                    if (!res.ok) throw new Error('Error al contar espacios');

                    const data = await res.json();
                    const registrados = data.cantidad ?? 0;
                    const restantes = Math.max(1 - registrados - squares.length, 0);

                    document.getElementById('espacios_disponibles').value =
                        `${restantes} espacios disponibles`;

                    btnAddBlock.disabled = (restantes <= 0);
                } catch (error) {
                    console.error('Error:', error);
                    document.getElementById('espacios_disponibles').value = 'Error al obtener';
                    btnAddBlock.disabled = true;
                }
            }

            // 11. Envío del formulario
            form.addEventListener('submit', async (e) => {
                e.preventDefault();

                // Validaciones básicas
                if (!pisoSelect.value) {
                    alert('Por favor, seleccione un piso.');
                    return;
                }

                if (!inputImage.files[0] && !backgroundImage) {
                    alert('Por favor, cargue una imagen del mapa.');
                    return;
                }

                const nombreMapaFormatted = nombreMapaInput.value.replace(/,\s+/g, '_');

                // Deshabilitar botón para evitar múltiples envíos
                btnSaveMap.disabled = true;
                btnSaveMap.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Guardando...';

                // Guardar los bloques en el input hidden
                bloquesJsonInput.value = JSON.stringify(squares);

                try {
                    // Crear FormData
                    const formData = new FormData(form);

                    // Si hay imagen cargada, agregarla al FormData con el nuevo nombre
                    if (inputImage.files[0]) {
                        const imageFile = inputImage.files[0];
                        const newImageName =
                        `${nombreMapaFormatted}.${imageFile.name.split('.').pop()}`;
                        formData.append('imagen', imageFile, newImageName);
                    }

                    // Si hay imagen en el canvas (dibujada), agregarla al FormData con el nuevo nombre
                    if (squares.length > 0) {
                        const canvasBlob = await new Promise(resolve => {
                            canvas.toBlob(resolve, 'image/png');
                        });
                        formData.append('canvas_image', canvasBlob,
                        `${nombreMapaFormatted}_canvas.png`);
                    }

                    // Enviar datos
                    const response = await fetch(form.action, {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        }
                    });

                    const data = await response.json();

                    if (!response.ok) {
                        throw new Error(data.message || 'Error al guardar el mapa');
                    }

                    // Redireccionar si todo está bien
                    window.location.href = data.redirect || "{{ route('mapas.index') }}";
                } catch (error) {
                    console.error('Error:', error);
                    alert('Error al guardar el mapa: ' + error.message);
                    btnSaveMap.disabled = false;
                    btnSaveMap.innerHTML = '<i class="mr-2 fas fa-save"></i> Guardar Mapa';
                }
            });
        });
    </script>

</x-app-layout>
=======
        document.addEventListener("DOMContentLoaded", function() {
            // Variables globales
            const state = {
                selectedSpace: null,
                mapImage: null,
                originalImageSize: null,
                currentMapId: null,
                indicators: [],
                isDragging: false,
                dragIndex: -1
            };

            // Elementos del DOM
            const elements = {
                mapCanvas: document.getElementById('mapCanvas'),
                mapCtx: document.getElementById('mapCanvas').getContext('2d'),
                indicatorsCanvas: document.getElementById('indicatorsCanvas'),
                indicatorsCtx: document.getElementById('indicatorsCanvas').getContext('2d'),
                sedeId: document.getElementById('id_sede'),
                facultadId: document.getElementById('id_facultad'),
                pisoSelect: document.getElementById('piso'),
                nombreMapaInput: document.getElementById('nombre_mapa'),
                nombreMapaContainer: document.getElementById('nombreMapaContainer'),
                mapImageInput: document.getElementById('mapImage'),
                espaciosList: document.getElementById('espaciosList'),
                emptySpacesMessage: document.getElementById('emptySpacesMessage'),
                saveMapBtn: document.getElementById('saveMapBtn'),
                clearIndicatorsBtn: document.getElementById('clearIndicatorsBtn'),
                saveMapForm: document.getElementById('saveMapForm')
            };

            // Configuración
            const config = {
                indicatorSize: 40,
                indicatorColor: '#3B82F6',
                indicatorBorder: '#FFFFFF',
                indicatorActiveColor: '#10B981',
                indicatorTextColor: '#FFFFFF'
            };

            // Inicializar los canvases
            function initCanvases() {
                const container = elements.mapCanvas.parentElement;
                const width = container.clientWidth;
                const height = container.clientHeight;
                
                elements.mapCanvas.width = width;
                elements.mapCanvas.height = height;
                elements.indicatorsCanvas.width = width;
                elements.indicatorsCanvas.height = height;
                
                drawCanvas();
                drawIndicators();
            }

            // Dibujar la imagen base
            function drawCanvas() {
                elements.mapCtx.clearRect(0, 0, elements.mapCanvas.width, elements.mapCanvas.height);
                
                if (!state.mapImage) return;

                const canvasRatio = elements.mapCanvas.width / elements.mapCanvas.height;
                const imageRatio = state.mapImage.width / state.mapImage.height;
                let drawWidth, drawHeight, offsetX, offsetY;

                if (imageRatio > canvasRatio) {
                    drawWidth = elements.mapCanvas.width;
                    drawHeight = elements.mapCanvas.width / imageRatio;
                    offsetX = 0;
                    offsetY = (elements.mapCanvas.height - drawHeight) / 2;
                } else {
                    drawHeight = elements.mapCanvas.height;
                    drawWidth = elements.mapCanvas.height * imageRatio;
                    offsetX = (elements.mapCanvas.width - drawWidth) / 2;
                    offsetY = 0;
                }

                elements.mapCtx.drawImage(state.mapImage, offsetX, offsetY, drawWidth, drawHeight);
            }

            // Dibujar todos los indicadores
            function drawIndicators() {
                elements.indicatorsCtx.clearRect(0, 0, elements.indicatorsCanvas.width, elements.indicatorsCanvas.height);
                
                state.indicators.forEach((indicator, index) => {
                    drawIndicator(indicator, index === state.dragIndex);
                });
            }

            // Dibujar un indicador individual
            function drawIndicator(indicator, isDragging = false) {
                const { x, y, id } = indicator;
                const size = config.indicatorSize;
                const color = isDragging ? config.indicatorActiveColor : config.indicatorColor;
                
                // Dibujar cuadrado
                elements.indicatorsCtx.fillStyle = color;
                elements.indicatorsCtx.fillRect(x - size/2, y - size/2, size, size);
                elements.indicatorsCtx.lineWidth = 2;
                elements.indicatorsCtx.strokeStyle = config.indicatorBorder;
                elements.indicatorsCtx.strokeRect(x - size/2, y - size/2, size, size);
                
                // Dibujar texto
                elements.indicatorsCtx.font = `bold ${size/3}px Arial`;
                elements.indicatorsCtx.fillStyle = config.indicatorTextColor;
                elements.indicatorsCtx.textAlign = 'center';
                elements.indicatorsCtx.textBaseline = 'middle';
                elements.indicatorsCtx.fillText(id, x, y);
            }

            // Convertir coordenadas del mouse a coordenadas del canvas
            function getCanvasCoordinates(event) {
                const rect = elements.indicatorsCanvas.getBoundingClientRect();
                return {
                    x: event.clientX - rect.left,
                    y: event.clientY - rect.top
                };
            }

            // Convertir coordenadas del canvas a coordenadas de la imagen original
            function canvasToImageCoordinates(canvasX, canvasY) {
                if (!state.mapImage) return { x: 0, y: 0 };

                const canvasRatio = elements.mapCanvas.width / elements.mapCanvas.height;
                const imageRatio = state.mapImage.width / state.mapImage.height;
                let drawWidth, drawHeight, offsetX, offsetY;

                if (imageRatio > canvasRatio) {
                    drawWidth = elements.mapCanvas.width;
                    drawHeight = elements.mapCanvas.width / imageRatio;
                    offsetX = 0;
                    offsetY = (elements.mapCanvas.height - drawHeight) / 2;
                } else {
                    drawHeight = elements.mapCanvas.height;
                    drawWidth = elements.mapCanvas.height * imageRatio;
                    offsetX = (elements.mapCanvas.width - drawWidth) / 2;
                    offsetY = 0;
                }

                // Verificar si las coordenadas están dentro de la imagen
                if (canvasX < offsetX || canvasX > offsetX + drawWidth || canvasY < offsetY || canvasY > offsetY + drawHeight) {
                    return null;
                }

                const relativeX = (canvasX - offsetX) / drawWidth;
                const relativeY = (canvasY - offsetY) / drawHeight;

                return {
                    x: Math.round(relativeX * state.originalImageSize.width),
                    y: Math.round(relativeY * state.originalImageSize.height)
                };
            }

            // Verificar si el clic está sobre un indicador
            function isClickOnIndicator(x, y) {
                for (let i = state.indicators.length - 1; i >= 0; i--) {
                    const indicator = state.indicators[i];
                    const distance = Math.sqrt(Math.pow(x - indicator.x, 2) + Math.pow(y - indicator.y, 2));
                    if (distance <= config.indicatorSize/2) {
                        return i;
                    }
                }
                return -1;
            }

            // Cargar imagen del mapa
            elements.mapImageInput.addEventListener('change', function(e) {
                const file = e.target.files[0];
                if (!file || !file.type.startsWith('image/')) return;

                const img = new Image();
                img.onload = function() {
                    state.mapImage = img;
                    state.originalImageSize = {
                        width: img.naturalWidth,
                        height: img.naturalHeight
                    };
                    initCanvases();
                    
                    if (state.currentMapId) {
                        loadExistingBlocks(state.currentMapId);
                    }
                };
                img.src = URL.createObjectURL(file);
            });

            // Limpiar todos los indicadores
            elements.clearIndicatorsBtn.addEventListener('click', function() {
                state.indicators = [];
                drawIndicators();
                elements.saveMapBtn.classList.add('hidden');
            });

            // Evento para cuando se selecciona un piso
            elements.pisoSelect.addEventListener('change', function() {
                const pisoId = this.value;
                elements.nombreMapaContainer.classList.add('hidden');

                if (pisoId) {
                    // Cargar espacios del piso
                fetch(`/espacios-por-piso/${pisoId}`)
                    .then(response => response.json())
                    .then(data => {
                        elements.espaciosList.innerHTML = '';
                        elements.emptySpacesMessage.classList.add('hidden');

                        if (data.length === 0) {
                            elements.emptySpacesMessage.classList.remove('hidden');
                            return;
                        }

                        data.forEach(espacio => {
                            const espacioItem = document.createElement('div');
                            espacioItem.className = 'p-2 bg-gray-50 dark:bg-gray-700 rounded cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-600 transition-colors flex items-center gap-3';
                            espacioItem.setAttribute('data-espacio-id', espacio.id_espacio);
                            espacioItem.innerHTML = `
                                <div class="flex items-center justify-center flex-shrink-0 w-12 h-12 p-4 font-bold text-white bg-blue-600">${espacio.id_espacio}</div>
                                <div class="text-sm font-medium truncate">${espacio.nombre_espacio}</div>
                            `;

                            espacioItem.addEventListener('click', function() {
                                // Deseleccionar todos
                                document.querySelectorAll('#espaciosList > div').forEach(item => {
                                    item.classList.remove('bg-blue-100', 'dark:bg-blue-900');
                                });
                                
                                // Seleccionar este
                                this.classList.add('bg-blue-100', 'dark:bg-blue-900');
                                state.selectedSpace = {
                                    id: espacio.id_espacio,
                                    nombre: espacio.nombre_espacio
                                };
                                
                                // Cambiar cursor
                                elements.indicatorsCanvas.style.cursor = 'crosshair';
                            });

                            elements.espaciosList.appendChild(espacioItem);
                        });

                        // Actualizar nombre del mapa
                        updateNombreMapa();
                    });
                }
            });

            // Eventos del canvas de indicadores
            elements.indicatorsCanvas.addEventListener('mousedown', function(e) {
                const { x, y } = getCanvasCoordinates(e);
                const index = isClickOnIndicator(x, y);
                
                if (index !== -1 && e.button === 0) { // Clic izquierdo sobre un indicador
                    state.isDragging = true;
                    state.dragIndex = index;
                    drawIndicators(); // Redibujar con el indicador activo
                    e.preventDefault();
                }
                
                if (e.button === 2 && index !== -1) { // Clic derecho sobre un indicador
                    state.indicators.splice(index, 1);
                    drawIndicators();
                    if (state.indicators.length === 0) {
                        elements.saveMapBtn.classList.add('hidden');
                    }
                    e.preventDefault();
                }
            });

            elements.indicatorsCanvas.addEventListener('mousemove', function(e) {
                if (!state.isDragging) return;
                
                const { x, y } = getCanvasCoordinates(e);
                if (state.dragIndex !== -1) {
                    state.indicators[state.dragIndex].x = x;
                    state.indicators[state.dragIndex].y = y;
                    
                    // Actualizar coordenadas originales
                    const imgCoords = canvasToImageCoordinates(x, y);
                    if (imgCoords) {
                        state.indicators[state.dragIndex].originalX = imgCoords.x;
                        state.indicators[state.dragIndex].originalY = imgCoords.y;
                    }
                    
                    drawIndicators();
                }
            });

            elements.indicatorsCanvas.addEventListener('mouseup', function(e) {
                if (state.isDragging) {
                    state.isDragging = false;
                    state.dragIndex = -1;
                    drawIndicators();
                }
            });

            elements.indicatorsCanvas.addEventListener('mouseleave', function() {
                if (state.isDragging) {
                    state.isDragging = false;
                    state.dragIndex = -1;
                    drawIndicators();
                }
            });

            elements.indicatorsCanvas.addEventListener('contextmenu', function(e) {
                e.preventDefault(); // Evitar el menú contextual
            });

            // Colocar nuevo indicador
            elements.indicatorsCanvas.addEventListener('click', function(e) {
                if (state.isDragging) return; // Evitar colocar nuevo indicador mientras se arrastra
                
                if (!state.selectedSpace) {
                    alert('Por favor, selecciona un espacio de la lista primero');
                    return;
                }

                if (!state.mapImage) {
                    alert('Por favor, carga una imagen del mapa primero');
                    return;
                }

                const { x, y } = getCanvasCoordinates(e);
                const imgCoords = canvasToImageCoordinates(x, y);
                
                if (!imgCoords) {
                    alert('Debes hacer clic dentro del área de la imagen');
                    return;
                }

                // Agregar nuevo indicador
                state.indicators.push({
                    id: state.selectedSpace.id,
                    nombre: state.selectedSpace.nombre,
                    x: x,
                    y: y,
                    originalX: imgCoords.x,
                    originalY: imgCoords.y
                });

                // Eliminar el espacio de la lista
                const espacioItem = document.querySelector(`#espaciosList div[data-espacio-id="${state.selectedSpace.id}"]`);
                if (espacioItem) {
                    espacioItem.remove();
                }

                // Verificar si quedan espacios en la lista
                if (elements.espaciosList.children.length === 0) {
                    elements.emptySpacesMessage.textContent = 'No hay más espacios disponibles';
                    elements.emptySpacesMessage.classList.remove('hidden');
                }

                drawIndicators();
                elements.saveMapBtn.classList.remove('hidden');
            });

            // Guardar mapa
            elements.saveMapBtn.addEventListener('click', function() {
                if (!elements.pisoSelect.value) {
                    alert('Debes seleccionar un piso');
                    return;
                }

                if (!elements.mapImageInput.files.length) {
                    alert('Debes subir una imagen del mapa');
                    return;
                }

                if (state.indicators.length === 0) {
                    alert('Debes agregar al menos un indicador');
                    return;
                }

                // Preparar datos para enviar
                const bloques = state.indicators.map(indicator => ({
                    id_espacio: indicator.id,
                    posicion_x: indicator.originalX,
                    posicion_y: indicator.originalY,
                    estado: true
                }));

                // Obtener el nombre del mapa del input visible
                const nombreMapa = elements.nombreMapaInput.value;
                if (!nombreMapa) {
                    alert('El nombre del mapa es requerido');
                    return;
                }

                // Llenar formulario oculto
                const nombreMapaForm = document.getElementById('nombre_mapa_form');
                nombreMapaForm.value = nombreMapa;
                document.getElementById('id_espacio').value = elements.pisoSelect.value;
                document.getElementById('bloques').value = JSON.stringify(bloques);

                // Copiar archivo
                const dataTransfer = new DataTransfer();
                dataTransfer.items.add(elements.mapImageInput.files[0]);
                document.getElementById('archivo').files = dataTransfer.files;

                // Log para depuración
                console.log('Nombre del mapa a enviar:', nombreMapaForm.value);

                // Enviar formulario
                elements.saveMapForm.submit();
            });

            // Cargar bloques existentes
            function loadExistingBlocks(mapId) {
                fetch(`/bloques-por-mapa/${mapId}`)
                    .then(response => response.json())
                    .then(data => {
                        state.indicators = [];
                        
                        data.forEach(bloque => {
                            // Convertir coordenadas originales a coordenadas del canvas
                            const canvasRatio = elements.mapCanvas.width / elements.mapCanvas.height;
                            const imageRatio = state.mapImage.width / state.mapImage.height;
                            let drawWidth, drawHeight, offsetX, offsetY;

                            if (imageRatio > canvasRatio) {
                                drawWidth = elements.mapCanvas.width;
                                drawHeight = elements.mapCanvas.width / imageRatio;
                                offsetX = 0;
                                offsetY = (elements.mapCanvas.height - drawHeight) / 2;
                            } else {
                                drawHeight = elements.mapCanvas.height;
                                drawWidth = elements.mapCanvas.height * imageRatio;
                                offsetX = (elements.mapCanvas.width - drawWidth) / 2;
                                offsetY = 0;
                            }

                            const x = offsetX + (bloque.posicion_x / state.originalImageSize.width) * drawWidth;
                            const y = offsetY + (bloque.posicion_y / state.originalImageSize.height) * drawHeight;

                            state.indicators.push({
                                id: bloque.espacio.id_espacio,
                                nombre: bloque.espacio.nombre_espacio,
                                x: x,
                                y: y,
                                originalX: bloque.posicion_x,
                                originalY: bloque.posicion_y
                            });
                        });

                        drawIndicators();
                        if (state.indicators.length > 0) {
                            elements.saveMapBtn.classList.remove('hidden');
                        }
                    });
            }

            // Función para actualizar el nombre del mapa
            function updateNombreMapa() {
                const sedeText = document.getElementById('sede').value;
                const facultadText = document.getElementById('facultad').value;
                const pisoText = elements.pisoSelect.options[elements.pisoSelect.selectedIndex]?.text || '';

                if (sedeText && facultadText && pisoText) {
                    const nombreCompleto = `${pisoText}, ${facultadText} de ${sedeText}`;
                    elements.nombreMapaInput.value = nombreCompleto;
                    document.getElementById('nombre_mapa_form').value = nombreCompleto;
                    elements.nombreMapaContainer.classList.remove('hidden');
                }
            }

            // Inicialización
            initCanvases();
            window.addEventListener('resize', initCanvases);

            // Cargar pisos al iniciar
            const facultadId = elements.facultadId.value;
            if (facultadId) {
                fetch(`/pisos/${facultadId}`)
                    .then(response => response.json())
                    .then(data => {
                        elements.pisoSelect.innerHTML = '<option value="">Seleccione un piso</option>';
                        data.forEach(piso => {
                            const option = document.createElement('option');
                            option.value = piso.id;
                            option.textContent = `Piso ${piso.numero_piso}`;
                            elements.pisoSelect.appendChild(option);
                        });
                    })
                    .catch(error => {
                        console.error('Error al cargar los pisos:', error);
                    });
            }
        });
    </script>

    <style>
        #indicatorsCanvas {
            z-index: 10;
            cursor: default;
        }
        
        #mapCanvas {
            z-index: 1;
        }
        
        #espaciosList {
            scrollbar-width: thin;
            scrollbar-color: #c1c1c1 #f1f1f1;
        }
        
        #espaciosList::-webkit-scrollbar {
            width: 6px;
        }
        
        #espaciosList::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 3px;
        }
        
        #espaciosList::-webkit-scrollbar-thumb {
            background: #c1c1c1;
            border-radius: 3px;
        }
        
        #espaciosList::-webkit-scrollbar-thumb:hover {
            background: #a1a1a1;
        }
        
        .dark #espaciosList {
            scrollbar-color: #6b7280 #374151;
        }
        
        .dark #espaciosList::-webkit-scrollbar-track {
            background: #374151;
        }
        
        .dark #espaciosList::-webkit-scrollbar-thumb {
            background: #6b7280;
        }
        
        .dark #espaciosList::-webkit-scrollbar-thumb:hover {
            background: #9ca3af;
        }
    </style>
</x-app-layout>
>>>>>>> Nperez
