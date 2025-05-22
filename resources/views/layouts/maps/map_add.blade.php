<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <h2 class="text-xl font-semibold leading-tight">
                {{ __('Crear Nuevo Mapa') }}
            </h2>
        </div>
    </x-slot>

    <div class="p-6 bg-white rounded-lg shadow-md dark:bg-gray-800">
        <!-- Selectores de universidad, sede, facultad y piso -->
        <div class="grid grid-cols-1 gap-4 mb-6 md:grid-cols-4">
            <div>
                <label for="universidad" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Universidad</label>
                <select id="universidad" name="universidad" class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                    <option value="">Seleccione una universidad</option>
                    @foreach ($universidades as $universidad)
                        <option value="{{ $universidad->id_universidad }}">{{ $universidad->nombre_universidad }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="sede" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Sede</label>
                <select id="sede" name="sede" class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white" disabled>
                    <option value="">Seleccione una sede</option>
                </select>
            </div>

            <div>
                <label for="facultad" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Facultad</label>
                <select id="facultad" name="facultad" class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white" disabled>
                    <option value="">Seleccione una facultad</option>
                </select>
            </div>

            <div>
                <label for="piso" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Piso</label>
                <select id="piso" name="piso" class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white" disabled>
                    <option value="">Seleccione un piso</option>
                </select>
            </div>
        </div>

        <!-- Contenedor principal con diseño de dos columnas -->
        <div class="flex flex-col md:flex-row gap-6">
            <!-- Columna izquierda - Lista de espacios -->
            <div class="w-full md:w-1/4">
                <div class="bg-white dark:bg-gray-800 p-4 rounded-lg shadow h-full">
                    <h3 class="text-lg font-semibold mb-3 text-gray-900 dark:text-white">Espacios Disponibles</h3>
                    <div id="espaciosList" class="space-y-2 max-h-[500px] overflow-y-auto">
                        <!-- Los espacios se cargarán aquí -->
                        <div class="text-center py-10 text-gray-500 dark:text-gray-400" id="emptySpacesMessage">
                            Seleccione un piso para cargar los espacios
                        </div>
                    </div>
                </div>
            </div>

            <!-- Columna derecha - Canvas y controles -->
            <div class="w-full md:w-3/4">
                <!-- Nombre del mapa (solo visible cuando hay piso seleccionado) -->
                <div id="nombreMapaContainer" class="mb-4 hidden">
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
                <div class="mt-4 flex justify-end gap-4">
                    <button id="clearIndicatorsBtn" type="button" class="px-4 py-2 text-white bg-red-600 rounded hover:bg-red-700">Limpiar Todo</button>
                    <button id="saveMapBtn" type="button" class="px-4 py-2 text-white bg-green-600 rounded hover:bg-green-700 hidden">Guardar Mapa</button>
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
        </form>
    </div>

    <script>
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
                universidadSelect: document.getElementById('universidad'),
                sedeSelect: document.getElementById('sede'),
                facultadSelect: document.getElementById('facultad'),
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
                elements.indicatorsCtx.font = `bold ${size/2}px Arial`;
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

            // Eventos para los selectores
            elements.universidadSelect.addEventListener('change', function() {
                const universidadId = this.value;
                elements.sedeSelect.innerHTML = '<option value="">Cargando sedes...</option>';
                elements.facultadSelect.innerHTML = '<option value="">Seleccione una facultad</option>';
                elements.pisoSelect.innerHTML = '<option value="">Seleccione un piso</option>';
                elements.facultadSelect.disabled = true;
                elements.pisoSelect.disabled = true;
                elements.nombreMapaContainer.classList.add('hidden');

                if (universidadId) {
                    fetch(`/sedes/${universidadId}`)
                        .then(response => response.json())
                        .then(data => {
                            elements.sedeSelect.innerHTML = '<option value="">Seleccione una sede</option>';
                            data.forEach(sede => {
                                elements.sedeSelect.innerHTML += `<option value="${sede.id_sede}">${sede.nombre_sede}</option>`;
                            });
                            elements.sedeSelect.disabled = false;
                        });
                }
            });

            elements.sedeSelect.addEventListener('change', function() {
                const sedeId = this.value;
                elements.facultadSelect.innerHTML = '<option value="">Cargando facultades...</option>';
                elements.pisoSelect.innerHTML = '<option value="">Seleccione un piso</option>';
                elements.pisoSelect.disabled = true;
                elements.nombreMapaContainer.classList.add('hidden');

                if (sedeId) {
                    fetch(`/facultades-por-sede/${sedeId}`)
                        .then(response => response.json())
                        .then(data => {
                            elements.facultadSelect.innerHTML = '<option value="">Seleccione una facultad</option>';
                            data.forEach(facultad => {
                                elements.facultadSelect.innerHTML += `<option value="${facultad.id_facultad}">${facultad.nombre_facultad}</option>`;
                            });
                            elements.facultadSelect.disabled = false;
                        });
                }
            });

            elements.facultadSelect.addEventListener('change', function() {
                const facultadId = this.value;
                elements.pisoSelect.innerHTML = '<option value="">Cargando pisos...</option>';
                elements.nombreMapaContainer.classList.add('hidden');

                if (facultadId) {
                    fetch(`/pisos/${facultadId}`)
                        .then(response => response.json())
                        .then(data => {
                            elements.pisoSelect.innerHTML = '<option value="">Seleccione un piso</option>';
                            data.forEach(piso => {
                                elements.pisoSelect.innerHTML += `<option value="${piso.id}">Piso ${piso.numero_piso}</option>`;
                            });
                            elements.pisoSelect.disabled = false;
                        });
                }
            });

            // Actualizar nombre del mapa automáticamente
            function updateNombreMapa() {
                const sedeText = elements.sedeSelect.options[elements.sedeSelect.selectedIndex]?.text || '';
                const facultadText = elements.facultadSelect.options[elements.facultadSelect.selectedIndex]?.text || '';
                const pisoText = elements.pisoSelect.options[elements.pisoSelect.selectedIndex]?.text || '';

                if (sedeText && facultadText && pisoText) {
                    const nombreCompleto = `${pisoText}, ${facultadText} de ${sedeText}`;
                    elements.nombreMapaInput.value = nombreCompleto;
                    document.getElementById('nombre_mapa_form').value = nombreCompleto;
                    elements.nombreMapaContainer.classList.remove('hidden');
                    console.log('Nombre del mapa actualizado:', nombreCompleto);
                }
            }

            // Cargar espacios cuando se selecciona un piso
            elements.pisoSelect.addEventListener('change', function() {
                const pisoId = this.value;
                if (!pisoId) {
                    elements.espaciosList.innerHTML = '';
                    elements.emptySpacesMessage.classList.remove('hidden');
                    return;
                }

                fetch(`/espacios-por-piso/${pisoId}`)
                    .then(response => response.json())
                    .then(data => {
                        elements.espaciosList.innerHTML = '';
                        elements.emptySpacesMessage.classList.add('hidden');
                        state.indicators = [];
                        drawIndicators();
                        elements.saveMapBtn.classList.add('hidden');
                        state.selectedSpace = null;

                        if (data.length === 0) {
                            elements.emptySpacesMessage.textContent = 'No hay espacios disponibles para este piso';
                            elements.emptySpacesMessage.classList.remove('hidden');
                            return;
                        }

                        data.forEach(espacio => {
                            const espacioItem = document.createElement('div');
                            espacioItem.className = 'p-2 bg-gray-50 dark:bg-gray-700 rounded cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-600 transition-colors flex items-center gap-3';
                            espacioItem.setAttribute('data-espacio-id', espacio.id_espacio);
                            espacioItem.innerHTML = `
                                <div class="w-12 h-12 bg-blue-600 p-4 flex-shrink-0 flex items-center justify-center text-white font-bold">${espacio.id_espacio}</div>
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

            // Inicialización
            initCanvases();
            window.addEventListener('resize', initCanvases);
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