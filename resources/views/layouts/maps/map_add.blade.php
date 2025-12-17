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
            <input type="hidden" name="piso_id" id="piso_id">
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
                                    item.classList.remove('bg-blue-100', 'dark:bg-blue-900', 'bg-green-200', 'dark:bg-green-900');
                                });

                                // Seleccionar este
                                this.classList.add('bg-green-200', 'dark:bg-green-900');
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
                const pisoId = elements.pisoSelect.value;
                document.getElementById('piso_id').value = pisoId;
                document.getElementById('bloques').value = JSON.stringify(bloques);

                // Copiar archivo
                const dataTransfer = new DataTransfer();
                dataTransfer.items.add(elements.mapImageInput.files[0]);
                document.getElementById('archivo').files = dataTransfer.files;

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
                const sedeId = document.getElementById('id_sede').value;
                const esTalcahuano = sedeId === 'TH';
                const sedeText = document.getElementById('sede').value;
                const facultadText = document.getElementById('facultad').value;
                const pisoText = elements.pisoSelect.options[elements.pisoSelect.selectedIndex]?.text || '';

                // Para sedes que NO son Talcahuano, no requerir piso
                if (!esTalcahuano && sedeText && facultadText) {
                    const nombreCompleto = `${facultadText} de ${sedeText}`;
                    elements.nombreMapaInput.value = nombreCompleto;
                    document.getElementById('nombre_mapa_form').value = nombreCompleto;
                    elements.nombreMapaContainer.classList.remove('hidden');
                } else if (sedeText && facultadText && pisoText) {
                    // Para Talcahuano, mantener comportamiento con piso
                    const nombreCompleto = `${pisoText}, ${facultadText} de ${sedeText}`;
                    elements.nombreMapaInput.value = nombreCompleto;
                    document.getElementById('nombre_mapa_form').value = nombreCompleto;
                    elements.nombreMapaContainer.classList.remove('hidden');
                }
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

            // Detectar si es Talcahuano (TH) o no
            const sedeId = document.getElementById('id_sede').value;
            const esTalcahuano = sedeId === 'TH';
            const facultadId = elements.facultadId.value;

            if (facultadId) {
                // Cargar pisos siempre
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
                        console.error('Error al cargar pisos:', error);
                    });

                // Para sedes que NO son Talcahuano, cargar TODOS los espacios de la facultad
                if (!esTalcahuano) {
                    fetch(`/espacios-por-facultad/${facultadId}`)
                        .then(response => response.json())
                        .then(data => {
                            elements.espaciosList.innerHTML = '';
                            elements.emptySpacesMessage.classList.add('hidden');

                            if (data.length === 0) {
                                elements.emptySpacesMessage.textContent = 'No hay espacios disponibles';
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
                                        item.classList.remove('bg-blue-100', 'dark:bg-blue-900', 'bg-green-200', 'dark:bg-green-900');
                                    });

                                    // Seleccionar este
                                    this.classList.add('bg-blue-100', 'dark:bg-blue-900');
                                    state.selectedSpace = {
                                        id: espacio.id_espacio,
                                        nombre: espacio.nombre_espacio
                                    };
                                });

                                elements.espaciosList.appendChild(espacioItem);
                            });

                            elements.emptySpacesMessage.classList.add('hidden');
                        })
                        .catch(error => {
                            console.error('Error al cargar espacios:', error);
                            elements.emptySpacesMessage.textContent = 'Error al cargar los espacios';
                            elements.emptySpacesMessage.classList.remove('hidden');
                        });
                }
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
