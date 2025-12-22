{{-- Step 6: Digital Floor Plan - Full Maintainer --}}
<div class="flex flex-col h-full">
    <!-- Header Section -->
    <div class="p-4 border-b border-gray-200">
        <div class="text-center">
            <h2 class="text-2xl font-bold text-gray-800">Plano Digital y Espacios</h2>
            <p class="text-gray-600 mt-1">Configure el plano digital y los espacios de su sede</p>
        </div>
    </div>

    <!-- Main Content Area (Flexible) - ocupar todo el espacio restante excepto footer -->
    <div class="flex-grow overflow-hidden flex flex-col">
        <!-- Info Box -->
        <div class="bg-teal-50 border border-teal-200 rounded-lg p-3 mx-4 mt-4 mb-0 flex-shrink-0">
            <div class="flex items-start">
                <i class="fas fa-info-circle text-teal-500 mt-1 mr-3 flex-shrink-0"></i>
                <div>
                    <h4 class="font-semibold text-teal-800 text-sm">¿Cómo funciona?</h4>
                    <p class="text-xs text-teal-700 mt-1">
                        1. Seleccione un piso de la lista | 2. Suba una imagen del plano | 3. Seleccione un espacio de la lista | 4. Haga clic para ubicarlo | 5. Guarde el mapa
                    </p>
                </div>
            </div>
        </div>

        <!-- Main Container -->
        <div class="flex-grow overflow-hidden bg-white border border-gray-200 rounded-lg shadow-sm m-4 flex flex-col">
            <!-- Selectores de sede, facultad y piso -->
            <div class="grid grid-cols-1 gap-3 p-3 border-b border-gray-200 md:grid-cols-4 flex-shrink-0">
                <div>
                    <label for="init_sede" class="block text-xs font-medium text-gray-700 mb-1">Sede</label>
                    <input type="text" id="init_sede" name="init_sede"
                           value="{{ $sede->nombre_sede ?? 'Sin sede' }}"
                           class="block w-full px-2 py-1 text-xs border-gray-300 rounded-md shadow-sm bg-gray-100" readonly>
                    <input type="hidden" id="init_id_sede" value="{{ $sede->id_sede ?? '' }}">
                </div>

                <div>
                    <label for="init_facultad" class="block text-xs font-medium text-gray-700 mb-1">Facultad</label>
                    <select id="init_facultad" name="init_facultad"
                            class="block w-full px-2 py-1 text-xs border-gray-300 rounded-md shadow-sm focus:border-teal-500 focus:ring-teal-500">
                        <option value="">Seleccione</option>
                        @php
                            $facultades = \App\Models\Facultad::where('id_sede', $sede->id_sede ?? '')->get();
                        @endphp
                        @foreach($facultades as $facultad)
                            <option value="{{ $facultad->id_facultad }}">{{ $facultad->nombre_facultad }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="init_piso" class="block text-xs font-medium text-gray-700 mb-1">Piso</label>
                    <select id="init_piso" name="init_piso"
                            class="block w-full px-2 py-1 text-xs border-gray-300 rounded-md shadow-sm focus:border-teal-500 focus:ring-teal-500">
                        <option value="">Seleccione</option>
                    </select>
                </div>

                <div class="flex items-end">
                    <button type="button" id="btnAgregarPiso"
                            class="w-full px-2 py-1 bg-teal-600 text-white text-xs rounded-md hover:bg-teal-700 transition-colors whitespace-nowrap flex-shrink-0">
                        <i class="fas fa-plus"></i> Nuevo
                    </button>
                </div>
            </div>

            <!-- Contenedor principal con diseño de dos columnas - FULLSCREEN -->
            <div class="flex-grow flex flex-col gap-0 p-0 md:flex-row overflow-hidden">
                <!-- Columna izquierda - Lista de espacios -->
                <div class="w-full md:w-48 border-r border-gray-200 overflow-hidden flex flex-col flex-shrink-0">
                    <div class="p-2 border-b border-gray-200 flex-shrink-0">
                        <h3 class="text-xs font-semibold text-gray-900">Espacios</h3>
                    </div>
                    <div class="flex-grow overflow-y-auto">
                        <div id="initEspaciosList" class="space-y-1 p-2">
                            <div class="py-4 text-center text-gray-500 text-xs" id="initEmptySpacesMessage">
                                Seleccione un piso
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Columna derecha - Canvas y controles -->
                <div class="flex-1 flex flex-col overflow-hidden">
                    <!-- Panel de controles -->
                    <div class="p-2 border-b border-gray-200 space-y-2 flex-shrink-0">
                        <!-- Nombre del mapa -->
                        <div id="initNombreMapaContainer" class="hidden">
                            <label for="init_nombre_mapa" class="block text-xs font-medium text-gray-700 mb-1">Nombre del Mapa</label>
                            <input type="text" name="init_nombre_mapa" id="init_nombre_mapa"
                                   class="block w-full px-2 py-1 text-xs border-gray-300 rounded-md shadow-sm bg-gray-100" readonly>
                        </div>

                        <!-- Carga de imagen y controles en una fila -->
                        <div class="flex items-end gap-1">
                            <div class="flex-grow">
                                <label for="initMapImage" class="block text-xs font-medium text-gray-700 mb-1">
                                    <i class="fas fa-image mr-1"></i>Plano
                                </label>
                                <input type="file" id="initMapImage" accept="image/*"
                                       class="block w-full text-xs text-gray-500 file:mr-1 file:py-1 file:px-2 file:rounded file:border-0 file:text-xs file:font-semibold file:bg-teal-50 file:text-teal-700 hover:file:bg-teal-100">
                            </div>
                            <button id="initClearIndicatorsBtn" type="button"
                                    class="px-2 py-1 text-xs text-white bg-red-600 rounded hover:bg-red-700 flex-shrink-0">
                                <i class="fas fa-trash mr-1"></i>Limpiar
                            </button>
                            <button id="initSaveMapBtn" type="button"
                                    class="hidden px-2 py-1 text-xs text-white bg-green-600 rounded hover:bg-green-700 flex-shrink-0">
                                <i class="fas fa-save mr-1"></i>Guardar
                            </button>
                        </div>
                    </div>

                    <!-- Contenedor del canvas - CON SCROLL VERTICAL -->
                    <div class="flex-grow relative overflow-y-auto overflow-x-hidden bg-gray-50">
                        <div class="relative" id="initCanvasContainer">
                            <canvas id="initMapCanvas" class="block w-full bg-white"></canvas>
                            <canvas id="initIndicatorsCanvas" class="absolute top-0 left-0 w-full pointer-events-auto"></canvas>

                            <!-- Mensaje cuando no hay imagen -->
                            <div id="initNoImageMessage" class="absolute inset-0 flex items-center justify-center text-gray-400">
                                <div class="text-center">
                                    <i class="fas fa-image text-4xl mb-2"></i>
                                    <p class="text-sm">Suba una imagen del plano</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para agregar piso -->
    <div id="modalAgregarPiso" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-medium leading-6 text-gray-900">Agregar Nuevo Piso</h3>
                    <button type="button" id="btnCerrarModal" class="text-gray-400 hover:text-gray-500">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <form id="formAgregarPiso">
                    <div class="mb-4">
                        <label for="numero_piso" class="block text-sm font-medium text-gray-700 mb-1">
                            Número de Piso <span class="text-red-500">*</span>
                        </label>
                        <input type="number" id="numero_piso" name="numero_piso" required
                               class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:border-teal-500 focus:ring-teal-500"
                               placeholder="Ej: 1, 2, -1 (subterráneo)">
                    </div>
                    <div class="mb-4">
                        <label for="nombre_piso" class="block text-sm font-medium text-gray-700 mb-1">
                            Nombre del Piso <span class="text-red-500">*</span>
                        </label>
                        <input type="text" id="nombre_piso" name="nombre_piso" required
                               class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:border-teal-500 focus:ring-teal-500"
                               placeholder="Ej: Primer Piso, Planta Baja">
                    </div>
                    <div class="flex justify-end gap-2">
                        <button type="button" id="btnCancelarPiso"
                                class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 transition-colors">
                            Cancelar
                        </button>
                        <button type="submit"
                                class="px-4 py-2 bg-teal-600 text-white rounded-md hover:bg-teal-700 transition-colors">
                            <i class="fas fa-save mr-1"></i> Guardar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Formulario oculto para guardar -->
    <form id="initSaveMapForm" method="POST" action="{{ route('mapas.store') }}" enctype="multipart/form-data" class="hidden">
        @csrf
        <input type="text" name="nombre_mapa" id="init_nombre_mapa_form" value="">
        <input type="hidden" name="piso_id" id="init_piso_id">
        <input type="hidden" name="bloques" id="init_bloques">
        <input type="file" name="archivo" id="init_archivo">
        <input type="hidden" name="redirect_to_init" value="1">
    </form>

    <!-- Skip Option -->
    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mt-6">
        <div class="flex items-center">
            <i class="fas fa-exclamation-triangle text-yellow-500 mr-3"></i>
            <p class="text-sm text-yellow-800">
                Este paso es <strong>opcional</strong>. Puede configurar el plano digital posteriormente desde el menú <strong>Mapas</strong>.
            </p>
        </div>
    </div>

    <!-- Navigation Buttons - Fixed at bottom -->
    <div class="border-t border-gray-200 bg-white p-3 flex justify-between">
        <a href="{{ route('tenant.initialization.previous') }}"
           class="inline-flex items-center px-4 py-2 bg-gray-200 text-gray-700 font-semibold rounded hover:bg-gray-300 transition text-sm">
            <i class="fas fa-arrow-left mr-2"></i>
            Anterior
        </a>
        <form action="{{ route('tenant.initialization.skip-plan') }}" method="POST" class="inline">
            @csrf
            <button type="submit"
                    class="inline-flex items-center px-4 py-2 bg-blue-600 text-white font-semibold rounded hover:bg-blue-700 transition text-sm">
                Continuar
                <i class="fas fa-arrow-right ml-2"></i>
            </button>
        </form>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function() {
    // Variables globales
    const initState = {
        selectedSpace: null,
        mapImage: null,
        originalImageSize: null,
        currentMapId: null,
        indicators: [],
        isDragging: false,
        dragIndex: -1,
        currentPisoId: null
    };

    // Almacenar el estado de cada piso (imagen + indicadores)
    const pisoStates = {};

    // Elementos del DOM
    const initElements = {
        mapCanvas: document.getElementById('initMapCanvas'),
        mapCtx: document.getElementById('initMapCanvas').getContext('2d'),
        indicatorsCanvas: document.getElementById('initIndicatorsCanvas'),
        indicatorsCtx: document.getElementById('initIndicatorsCanvas').getContext('2d'),
        sedeId: document.getElementById('init_id_sede'),
        facultadSelect: document.getElementById('init_facultad'),
        pisoSelect: document.getElementById('init_piso'),
        nombreMapaInput: document.getElementById('init_nombre_mapa'),
        nombreMapaContainer: document.getElementById('initNombreMapaContainer'),
        mapImageInput: document.getElementById('initMapImage'),
        espaciosList: document.getElementById('initEspaciosList'),
        emptySpacesMessage: document.getElementById('initEmptySpacesMessage'),
        noImageMessage: document.getElementById('initNoImageMessage'),
        saveMapBtn: document.getElementById('initSaveMapBtn'),
        clearIndicatorsBtn: document.getElementById('initClearIndicatorsBtn'),
        saveMapForm: document.getElementById('initSaveMapForm'),
        // Modal de agregar piso
        btnAgregarPiso: document.getElementById('btnAgregarPiso'),
        modalAgregarPiso: document.getElementById('modalAgregarPiso'),
        btnCerrarModal: document.getElementById('btnCerrarModal'),
        btnCancelarPiso: document.getElementById('btnCancelarPiso'),
        formAgregarPiso: document.getElementById('formAgregarPiso')
    };

    // Funciones del modal de agregar piso
    function abrirModalPiso() {
        if (!initElements.facultadSelect.value) {
            alert('Por favor, seleccione una facultad primero');
            return;
        }
        initElements.modalAgregarPiso.classList.remove('hidden');
        initElements.formAgregarPiso.reset();
    }

    function cerrarModalPiso() {
        initElements.modalAgregarPiso.classList.add('hidden');
    }

    // Event listeners del modal
    initElements.btnAgregarPiso.addEventListener('click', abrirModalPiso);
    initElements.btnCerrarModal.addEventListener('click', cerrarModalPiso);
    initElements.btnCancelarPiso.addEventListener('click', cerrarModalPiso);

    // Cerrar modal al hacer clic fuera
    initElements.modalAgregarPiso.addEventListener('click', function(e) {
        if (e.target === this) {
            cerrarModalPiso();
        }
    });

    // Manejar submit del formulario de agregar piso
    initElements.formAgregarPiso.addEventListener('submit', function(e) {
        e.preventDefault();

        const numeroPiso = document.getElementById('numero_piso').value;
        const nombrePiso = document.getElementById('nombre_piso').value;
        const idFacultad = initElements.facultadSelect.value;

        if (!numeroPiso || !nombrePiso) {
            alert('Por favor, complete todos los campos');
            return;
        }

        // Hacer petición AJAX para crear el piso
        fetch('{{ route("tenant.initialization.pisos.store") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                numero_piso: numeroPiso,
                nombre_piso: nombrePiso,
                id_facultad: idFacultad
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Agregar el nuevo piso al select
                const option = document.createElement('option');
                option.value = data.piso.id;
                option.textContent = data.piso.nombre_piso;
                option.selected = true;
                initElements.pisoSelect.appendChild(option);

                // Disparar el evento change para cargar espacios
                initElements.pisoSelect.dispatchEvent(new Event('change'));

                cerrarModalPiso();

                // Mostrar mensaje de éxito
                alert('Piso creado exitosamente');
            } else {
                alert('Error al crear el piso: ' + (data.message || 'Error desconocido'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error al crear el piso. Por favor, intente nuevamente.');
        });
    });

    // Configuración
    const config = {
        indicatorSize: 35,
        indicatorColor: '#14B8A6',
        indicatorBorder: '#FFFFFF',
        indicatorActiveColor: '#10B981',
        indicatorTextColor: '#FFFFFF'
    };

    // Guardar el estado actual del piso
    function savePisoState() {
        if (!initState.currentPisoId) return;

        pisoStates[initState.currentPisoId] = {
            mapImage: initState.mapImage,
            originalImageSize: initState.originalImageSize,
            indicators: [...initState.indicators],
            imageFile: initElements.mapImageInput.files[0]
        };
    }

    // Cargar el estado de un piso
    function loadPisoState(pisoId) {
        // Guardar estado actual antes de cambiar
        savePisoState();

        initState.currentPisoId = pisoId;

        // Limpiar estado actual
        initState.mapImage = null;
        initState.originalImageSize = null;
        initState.indicators = [];
        initState.selectedSpace = null;
        initElements.mapImageInput.value = '';

        // Si hay estado guardado para este piso, cargarlo
        if (pisoStates[pisoId]) {
            const savedState = pisoStates[pisoId];
            initState.mapImage = savedState.mapImage;
            initState.originalImageSize = savedState.originalImageSize;
            initState.indicators = [...savedState.indicators];

            if (initState.mapImage) {
                initElements.noImageMessage.classList.add('hidden');
                initCanvases();
            } else {
                initElements.noImageMessage.classList.remove('hidden');
                // Limpiar canvas
                const container = document.getElementById('initCanvasContainer');
                initElements.mapCanvas.width = container.clientWidth;
                initElements.mapCanvas.height = 600;
                initElements.indicatorsCanvas.width = container.clientWidth;
                initElements.indicatorsCanvas.height = 600;
                initElements.mapCanvas.style.height = '600px';
                initElements.indicatorsCanvas.style.height = '600px';
                drawCanvas();
                drawIndicators();
            }
        } else {
            // No hay estado guardado, mostrar canvas vacío
            initElements.noImageMessage.classList.remove('hidden');
            const container = document.getElementById('initCanvasContainer');
            initElements.mapCanvas.width = container.clientWidth;
            initElements.mapCanvas.height = 600;
            initElements.indicatorsCanvas.width = container.clientWidth;
            initElements.indicatorsCanvas.height = 600;
            initElements.mapCanvas.style.height = '600px';
            initElements.indicatorsCanvas.style.height = '600px';
            drawCanvas();
            drawIndicators();
        }

        // Actualizar visibilidad del botón guardar
        if (initState.indicators.length > 0) {
            initElements.saveMapBtn.classList.remove('hidden');
        } else {
            initElements.saveMapBtn.classList.add('hidden');
        }
    }

    // Inicializar los canvases
    function initCanvases() {
        const container = document.getElementById('initCanvasContainer');
        const width = container.clientWidth;

        // Calcular altura basada en la proporción de la imagen
        let height = 600; // Altura mínima por defecto
        if (initState.mapImage) {
            const imageRatio = initState.mapImage.width / initState.mapImage.height;
            height = Math.round(width / imageRatio);
        }

        initElements.mapCanvas.width = width;
        initElements.mapCanvas.height = height;
        initElements.indicatorsCanvas.width = width;
        initElements.indicatorsCanvas.height = height;

        // Establecer la altura explícitamente en el estilo
        initElements.mapCanvas.style.height = height + 'px';
        initElements.indicatorsCanvas.style.height = height + 'px';

        drawCanvas();
        drawIndicators();
    }

    // Dibujar la imagen base
    function drawCanvas() {
        initElements.mapCtx.clearRect(0, 0, initElements.mapCanvas.width, initElements.mapCanvas.height);

        if (!initState.mapImage) return;

        // Dibujar la imagen ocupando todo el canvas (ancho y alto completo)
        initElements.mapCtx.drawImage(initState.mapImage, 0, 0, initElements.mapCanvas.width, initElements.mapCanvas.height);
    }

    // Dibujar todos los indicadores
    function drawIndicators() {
        initElements.indicatorsCtx.clearRect(0, 0, initElements.indicatorsCanvas.width, initElements.indicatorsCanvas.height);

        initState.indicators.forEach((indicator, index) => {
            drawIndicator(indicator, index === initState.dragIndex);
        });
    }

    // Dibujar un indicador individual
    function drawIndicator(indicator, isDragging = false) {
        const { x, y, id } = indicator;
        const size = config.indicatorSize;
        const color = isDragging ? config.indicatorActiveColor : config.indicatorColor;

        initElements.indicatorsCtx.fillStyle = color;
        initElements.indicatorsCtx.fillRect(x - size/2, y - size/2, size, size);
        initElements.indicatorsCtx.lineWidth = 2;
        initElements.indicatorsCtx.strokeStyle = config.indicatorBorder;
        initElements.indicatorsCtx.strokeRect(x - size/2, y - size/2, size, size);

        initElements.indicatorsCtx.font = `bold ${size/3}px Arial`;
        initElements.indicatorsCtx.fillStyle = config.indicatorTextColor;
        initElements.indicatorsCtx.textAlign = 'center';
        initElements.indicatorsCtx.textBaseline = 'middle';
        initElements.indicatorsCtx.fillText(id, x, y);
    }

    // Convertir coordenadas del mouse a coordenadas del canvas
    function getCanvasCoordinates(event) {
        const rect = initElements.indicatorsCanvas.getBoundingClientRect();
        return {
            x: event.clientX - rect.left,
            y: event.clientY - rect.top
        };
    }

    // Convertir coordenadas del canvas a coordenadas de la imagen original
    function canvasToImageCoordinates(canvasX, canvasY) {
        if (!initState.mapImage) return { x: 0, y: 0 };

        // La imagen ocupa todo el canvas, conversión directa
        const relativeX = canvasX / initElements.mapCanvas.width;
        const relativeY = canvasY / initElements.mapCanvas.height;

        return {
            x: Math.round(relativeX * initState.originalImageSize.width),
            y: Math.round(relativeY * initState.originalImageSize.height)
        };
    }

    // Verificar si el clic está sobre un indicador
    function isClickOnIndicator(x, y) {
        for (let i = initState.indicators.length - 1; i >= 0; i--) {
            const indicator = initState.indicators[i];
            const distance = Math.sqrt(Math.pow(x - indicator.x, 2) + Math.pow(y - indicator.y, 2));
            if (distance <= config.indicatorSize/2) {
                return i;
            }
        }
        return -1;
    }

    // Cargar imagen del mapa
    initElements.mapImageInput.addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (!file || !file.type.startsWith('image/')) return;

        const img = new Image();
        img.onload = function() {
            initState.mapImage = img;
            initState.originalImageSize = {
                width: img.naturalWidth,
                height: img.naturalHeight
            };
            initElements.noImageMessage.classList.add('hidden');
            initCanvases();

            // Guardar estado del piso actual
            savePisoState();
        };
        img.src = URL.createObjectURL(file);
    });

    // Limpiar todos los indicadores
    initElements.clearIndicatorsBtn.addEventListener('click', function() {
        initState.indicators = [];
        savePisoState(); // Guardar limpieza
        drawIndicators();
        initElements.saveMapBtn.classList.add('hidden');
    });

    // Evento cuando se selecciona una facultad
    initElements.facultadSelect.addEventListener('change', function() {
        const facultadId = this.value;
        initElements.pisoSelect.innerHTML = '<option value="">Seleccione un piso</option>';
        initElements.nombreMapaContainer.classList.add('hidden');

        if (facultadId) {
            // Construir URL con prefijo de tenant/initialization
            const url = `/tenant/initialization/pisos/${facultadId}`;
            fetch(url)
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    return response.json();
                })
                .then(data => {
                    console.log('Pisos cargados:', data);
                    if (Array.isArray(data) && data.length > 0) {
                        data.forEach(piso => {
                            const option = document.createElement('option');
                            option.value = piso.id;
                            option.textContent = piso.nombre_piso || `Piso ${piso.numero_piso}`;
                            initElements.pisoSelect.appendChild(option);
                        });
                    } else {
                        const option = document.createElement('option');
                        option.value = '';
                        option.textContent = 'No hay pisos disponibles';
                        option.disabled = true;
                        initElements.pisoSelect.appendChild(option);
                    }
                })
                .catch(error => {
                    console.error('Error al cargar pisos:', error);
                    alert('Error al cargar los pisos: ' + error.message);
                });
        }
    });

    // Evento cuando se selecciona un piso
    initElements.pisoSelect.addEventListener('change', function() {
        const pisoId = this.value;
        initElements.nombreMapaContainer.classList.add('hidden');

        if (pisoId) {
            // Cargar el estado guardado de este piso (o limpiar si no existe)
            loadPisoState(pisoId);

            // Construir URL con prefijo de tenant/initialization
            const url = `/tenant/initialization/espacios-por-piso/${pisoId}`;
            fetch(url)
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    return response.json();
                })
                .then(data => {
                    console.log('Espacios cargados:', data);
                    initElements.espaciosList.innerHTML = '';
                    initElements.emptySpacesMessage.classList.add('hidden');

                    if (data.length === 0) {
                        initElements.emptySpacesMessage.textContent = 'No hay espacios asignados a este piso';
                        initElements.emptySpacesMessage.classList.remove('hidden');
                        initElements.espaciosList.appendChild(initElements.emptySpacesMessage);
                        return;
                    }

                    data.forEach(espacio => {
                        const espacioItem = document.createElement('div');
                        espacioItem.className = 'p-2 bg-white rounded cursor-pointer hover:bg-teal-50 transition-colors flex items-center gap-2 border border-gray-200';
                        espacioItem.setAttribute('data-espacio-id', espacio.id);
                        espacioItem.innerHTML = `
                            <div class="flex items-center justify-center flex-shrink-0 w-10 h-10 font-bold text-white bg-teal-600 rounded text-xs">${espacio.id}</div>
                            <div class="text-sm font-medium truncate">${espacio.nombre}</div>
                        `;

                        espacioItem.addEventListener('click', function() {
                            document.querySelectorAll('#initEspaciosList > div[data-espacio-id]').forEach(item => {
                                item.classList.remove('bg-teal-100', 'border-teal-500');
                            });

                            this.classList.add('bg-teal-100', 'border-teal-500');
                            initState.selectedSpace = {
                                id: espacio.id,
                                nombre: espacio.nombre
                            };

                            initElements.indicatorsCanvas.style.cursor = 'crosshair';
                        });

                        initElements.espaciosList.appendChild(espacioItem);
                    });

                    updateNombreMapa();
                })
                .catch(error => {
                    console.error('Error al cargar espacios:', error);
                    initElements.emptySpacesMessage.textContent = 'Error al cargar espacios: ' + error.message;
                    initElements.emptySpacesMessage.classList.remove('hidden');
                    initElements.espaciosList.appendChild(initElements.emptySpacesMessage);
                });
        }
    });

    // Eventos del canvas de indicadores
    initElements.indicatorsCanvas.addEventListener('mousedown', function(e) {
        const { x, y } = getCanvasCoordinates(e);
        const index = isClickOnIndicator(x, y);

        if (index !== -1 && e.button === 0) {
            initState.isDragging = true;
            initState.dragIndex = index;
            drawIndicators();
            e.preventDefault();
        }

        if (e.button === 2 && index !== -1) {
            initState.indicators.splice(index, 1);
            drawIndicators();
            savePisoState(); // Guardar cambio
            if (initState.indicators.length === 0) {
                initElements.saveMapBtn.classList.add('hidden');
            }
            e.preventDefault();
        }
    });

    initElements.indicatorsCanvas.addEventListener('mousemove', function(e) {
        if (!initState.isDragging) return;

        const { x, y } = getCanvasCoordinates(e);
        if (initState.dragIndex !== -1) {
            initState.indicators[initState.dragIndex].x = x;
            initState.indicators[initState.dragIndex].y = y;

            const imgCoords = canvasToImageCoordinates(x, y);
            if (imgCoords) {
                initState.indicators[initState.dragIndex].originalX = imgCoords.x;
                initState.indicators[initState.dragIndex].originalY = imgCoords.y;
            }

            drawIndicators();
        }
    });

    initElements.indicatorsCanvas.addEventListener('mouseup', function(e) {
        if (initState.isDragging) {
            initState.isDragging = false;
            initState.dragIndex = -1;
            savePisoState(); // Guardar posición final
            drawIndicators();
        }
    });

    initElements.indicatorsCanvas.addEventListener('mouseleave', function() {
        if (initState.isDragging) {
            initState.isDragging = false;
            initState.dragIndex = -1;
            drawIndicators();
        }
    });

    initElements.indicatorsCanvas.addEventListener('contextmenu', function(e) {
        e.preventDefault();
    });

    // Colocar nuevo indicador
    initElements.indicatorsCanvas.addEventListener('click', function(e) {
        if (initState.isDragging) return;

        if (!initState.selectedSpace) {
            alert('Por favor, selecciona un espacio de la lista primero');
            return;
        }

        if (!initState.mapImage) {
            alert('Por favor, carga una imagen del plano primero');
            return;
        }

        const { x, y } = getCanvasCoordinates(e);
        const imgCoords = canvasToImageCoordinates(x, y);

        if (!imgCoords) {
            alert('Debes hacer clic dentro del área de la imagen');
            return;
        }

        initState.indicators.push({
            id: initState.selectedSpace.id,
            nombre: initState.selectedSpace.nombre,
            x: x,
            y: y,
            originalX: imgCoords.x,
            originalY: imgCoords.y
        });

        const espacioItem = document.querySelector(`#initEspaciosList div[data-espacio-id="${initState.selectedSpace.id}"]`);
        if (espacioItem) {
            espacioItem.remove();
        }

        if (initElements.espaciosList.querySelectorAll('[data-espacio-id]').length === 0) {
            const msg = document.createElement('div');
            msg.className = 'py-8 text-center text-gray-500 text-sm';
            msg.textContent = 'Todos los espacios han sido ubicados';
            initElements.espaciosList.appendChild(msg);
        }

        initState.selectedSpace = null;
        initElements.indicatorsCanvas.style.cursor = 'default';
        savePisoState(); // Guardar nuevo indicador
        drawIndicators();
        initElements.saveMapBtn.classList.remove('hidden');
    });

    // Guardar mapa
    initElements.saveMapBtn.addEventListener('click', function() {
        if (!initElements.pisoSelect.value) {
            alert('Debes seleccionar un piso');
            return;
        }

        if (!initElements.mapImageInput.files.length) {
            alert('Debes subir una imagen del plano');
            return;
        }

        if (initState.indicators.length === 0) {
            alert('Debes agregar al menos un indicador');
            return;
        }

        const bloques = initState.indicators.map(indicator => ({
            id_espacio: indicator.id,
            posicion_x: indicator.originalX,
            posicion_y: indicator.originalY,
            estado: true
        }));

        const nombreMapa = initElements.nombreMapaInput.value;
        if (!nombreMapa) {
            alert('El nombre del mapa es requerido');
            return;
        }

        document.getElementById('init_nombre_mapa_form').value = nombreMapa;
        document.getElementById('init_piso_id').value = initElements.pisoSelect.value;
        document.getElementById('init_bloques').value = JSON.stringify(bloques);

        const dataTransfer = new DataTransfer();
        dataTransfer.items.add(initElements.mapImageInput.files[0]);
        document.getElementById('init_archivo').files = dataTransfer.files;

        initElements.saveMapForm.submit();
    });

    // Función para actualizar el nombre del mapa
    function updateNombreMapa() {
        const sedeText = document.getElementById('init_sede').value;
        const facultadText = initElements.facultadSelect.options[initElements.facultadSelect.selectedIndex]?.text || '';
        const pisoText = initElements.pisoSelect.options[initElements.pisoSelect.selectedIndex]?.text || '';

        if (sedeText && facultadText && pisoText) {
            const nombreCompleto = `${pisoText}, ${facultadText} - ${sedeText}`;
            initElements.nombreMapaInput.value = nombreCompleto;
            document.getElementById('init_nombre_mapa_form').value = nombreCompleto;
            initElements.nombreMapaContainer.classList.remove('hidden');
        }
    }

    // Inicialización
    initCanvases();
    window.addEventListener('resize', initCanvases);
});
</script>

<style>
    #initIndicatorsCanvas {
        z-index: 10;
        cursor: default;
    }

    #initMapCanvas {
        z-index: 1;
    }

    #initEspaciosList {
        scrollbar-width: thin;
        scrollbar-color: #c1c1c1 #f1f1f1;
    }

    #initEspaciosList::-webkit-scrollbar {
        width: 6px;
    }

    #initEspaciosList::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 3px;
    }

    #initEspaciosList::-webkit-scrollbar-thumb {
        background: #c1c1c1;
        border-radius: 3px;
    }
</style>
