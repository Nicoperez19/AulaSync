<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <h2 class="text-xl font-semibold leading-tight" style="font-style: oblique;">
                {{ __('Ingreso de Mapas') }}
            </h2>
        </div>
    </x-slot>

    <div class="p-6 bg-white rounded-lg shadow-md dark:bg-gray-800">
        <!-- FORMULARIO -->
        <form method="POST" action="{{ route('mapas.store') }}" enctype="multipart/form-data">
            @csrf

            <!-- Selectores -->
            <div class="gap-4 mb-6 md:grid-cols-2">
                <div class="p-6 bg-white rounded-lg shadow-md dark:bg-gray-800">
                    <div class="flex flex-wrap gap-4 mb-6">
                        <!-- Universidad -->
                        <div class="w-full md:w-1/3">
                            <x-form.label for="id_universidad" :value="__('Universidad')" />
                            <select name="id_universidad" id="id_universidad" class="block w-full">
                                <option value="">Seleccione una universidad</option>
                                @foreach ($universidades as $uni)
                                    <option value="{{ $uni->id_universidad }}">{{ $uni->nombre_universidad }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Facultad -->
                        <div class="w-full md:w-1/3">
                            <x-form.label for="id_facultad" :value="__('Facultad')" />
                            <select name="id_facultad" id="id_facultad" class="block w-full" disabled>
                                <option value="">Seleccione una facultad</option>
                            </select>
                        </div>

                        <!-- Piso -->
                        <div class="w-full md:w-1/3">
                            <x-form.label for="piso_id" :value="__('Piso')" />
                            <select name="piso_id" id="piso_id" class="block w-full" disabled>
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
                        <x-button id="btnClearCanvas" variant="danger" class="h-10 whitespace-nowrap">
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
                    <x-button variant="secondary" class="h-10 whitespace-nowrap"
                        onclick="document.getElementById('mapImageUpload').click()">
                        <i class="mr-2 fas fa-upload"></i> Cargar Plano
                    </x-button>
                    <input id="mapImageUpload" name="imagen_plano" type="file" accept="image/*"
                        style="display: none;" required>
                </div>

                <!-- Agregar Espacio -->
                <x-button id="btnAddBlock" variant="success" class="h-10 whitespace-nowrap" type="button">
                    <i class="mr-2 fas fa-plus"></i> Agregar Espacio
                </x-button>
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
        </form>
    </div>

    <!-- Scripts -->
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            // 1. Selectores de universidad, facultad y piso
            const universidadSelect = document.getElementById('id_universidad');
            const facultadSelect = document.getElementById('id_facultad');
            const pisoSelect = document.getElementById('piso_id');
            const nombreMapaInput = document.getElementById('nombre_mapa');

            // 2. Elementos para la imagen
            const inputImage = document.getElementById('mapImageUpload');
            const previewImage = document.getElementById('previewImage');
            const noImageMessage = document.getElementById('noImageMessage');
            const btnClearCanvas = document.getElementById('btnClearCanvas');
            const btnAddBlock = document.getElementById('btnAddBlock');

            // 3. Configuración del canvas
            const canvas = document.getElementById('mapCanvas');
            const ctx = canvas.getContext('2d');
            let originalImage = null;
            let squares = [];
            let selectedSquare = null;
            let offsetX, offsetY;
            let isDragging = false;
            let isResizing = false;
            let resizeHandle = null;

            // 4. Manejo de los selectores
            universidadSelect.addEventListener('change', async () => {
                const id = universidadSelect.value;
                facultadSelect.innerHTML = '<option value="">Seleccione una facultad</option>';
                pisoSelect.innerHTML = '<option value="">Seleccione un piso</option>';
                facultadSelect.disabled = true;
                pisoSelect.disabled = true;

                if (id) {
                    const res = await fetch(`/mapas/facultades/${id}`);
                    const data = await res.json();

                    data.forEach(fac => {
                        facultadSelect.innerHTML +=
                            `<option value="${fac.id_facultad}">${fac.nombre_facultad}</option>`;
                    });

                    facultadSelect.disabled = false;
                }
            });

            facultadSelect.addEventListener('change', async () => {
                const id = facultadSelect.value;
                pisoSelect.innerHTML = '<option value="">Seleccione un piso</option>';
                pisoSelect.disabled = true;

                if (id) {
                    const res = await fetch(`/mapas/pisos/${id}`);
                    const data = await res.json();

                    data.forEach(piso => {
                        const nombre = piso.nombre ?? `Piso ${piso.numero_piso}`;
                        pisoSelect.innerHTML += `<option value="${piso.id}">${nombre}</option>`;
                    });

                    pisoSelect.disabled = false;
                }
            });

            pisoSelect.addEventListener('change', () => {
                const nombrePiso = pisoSelect.options[pisoSelect.selectedIndex]?.text || '';
                const nombreFacultad = facultadSelect.options[facultadSelect.selectedIndex]?.text || '';
                const nombreUniversidad = universidadSelect.options[universidadSelect.selectedIndex]
                    ?.text || '';

                nombreMapaInput.value = `${nombrePiso}, ${nombreFacultad}, ${nombreUniversidad}`;
            });

            // 5. Configuración inicial del canvas
            function initCanvas() {
                const container = canvas.parentElement;
                canvas.width = container.clientWidth;
                canvas.height = container.clientHeight;
            }
            initCanvas();
            window.addEventListener('resize', initCanvas);

            // 6. Manejo de la imagen
            inputImage.addEventListener('change', (e) => {
                const file = e.target.files[0];
                if (!file || !file.type.startsWith('image/')) {
                    alert('Por favor, selecciona un archivo de imagen válido.');
                    return;
                }
                const reader = new FileReader();
                reader.onload = (event) => {
                    previewImage.src = event.target.result;
                    previewImage.style.display = 'block';
                    noImageMessage.style.display = 'none';
                };
                reader.readAsDataURL(file);
            });

            // 7. Dibujar en el canvas
            function drawCanvas() {
                if (!canvas) return;

                ctx.clearRect(0, 0, canvas.width, canvas.height);

                // Solo dibujar los cuadrados
                drawSquares();
            }

            function drawSquares() {
                squares.forEach((square, index) => {
                    ctx.fillStyle = 'rgba(255, 255, 255, 0.5)';
                    ctx.strokeStyle = '#000';
                    ctx.lineWidth = 2;

                    // Bordes redondeados si está disponible
                    if (ctx.roundRect) {
                        ctx.beginPath();
                        ctx.roundRect(square.x, square.y, square.width, square.height, 10);
                        ctx.fill();
                        ctx.stroke();
                    } else {
                        // Fallback para navegadores antiguos
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
                drawCanvas();
            });

            canvas.addEventListener('mouseup', () => {
                isDragging = false;
                selectedSquare = null;
            });

            btnAddBlock.addEventListener('click', () => {
                const newSquare = {
                    x: canvas.width / 2 - 50,
                    y: canvas.height / 2 - 50,
                    width: 100,
                    height: 100
                };
                squares.push(newSquare);
                drawCanvas();
            });

            btnClearCanvas.addEventListener('click', (e) => {
                e.preventDefault();
                squares = [];
                previewImage.src = '';
                previewImage.style.display = 'none';
                noImageMessage.style.display = 'flex';
                inputImage.value = '';
                drawCanvas();
                ctx.clearRect(0, 0, canvas.width, canvas.height);
                selectedSquare = null;
            });

        });
    </script>
</x-app-layout>
