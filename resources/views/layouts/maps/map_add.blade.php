<x-app-layout>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">

    <x-slot name="header">
        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <h2 class="text-xl font-semibold leading-tight" style="font-style: oblique;">
                {{ __('Editor de Mapas') }}
            </h2>
        </div>
    </x-slot>

    <div class="p-6 bg-white rounded-lg shadow-md dark:bg-gray-800">
        <!-- Selectores jerárquicos -->
        <div class="grid grid-cols-1 gap-4 mb-6 md:grid-cols-2">
            <div class="flex flex-col">
                <label for="universidad" class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    {{ __('Universidad') }}
                </label>
                <select id="universidad" class="w-full select2 h-10 text-sm">
                    <option value="">Seleccione universidad</option>
                    @foreach ($universidades as $universidad)
                        <option value="{{ $universidad->id_universidad }}">{{ $universidad->nombre_universidad }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="flex flex-col">
                <label for="facultad" class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    {{ __('Facultad') }}
                </label>
                <select id="facultad" disabled class="w-full select2 h-10 text-sm">
                    <option value="">Primero seleccione universidad</option>
                </select>
            </div>

            <div class="flex flex-col">
                <label for="piso" class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    {{ __('Piso') }}
                </label>
                <select id="piso" disabled class="w-full select2 h-10 text-sm">
                    <option value="">Primero seleccione facultad</option>
                </select>
            </div>

            <div class="flex flex-col">
                <label for="espacio" class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    {{ __('Espacio') }}
                </label>
                <select id="espacio" disabled class="w-full select2 h-10 text-sm">
                    <option value="">Primero seleccione piso</option>
                </select>
            </div>
        </div>

        <!-- Nombre del mapa -->
        <div class="mb-6">
            <div class="flex flex-col">
                <label for="nombre_mapa" class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    {{ __('Nombre del Mapa') }}
                </label>
                <input type="text" id="nombre_mapa" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 h-10 text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white">
            </div>
        </div>

        <!-- Controles del editor -->
        <div class="flex flex-wrap gap-4 mb-6">
            <x-button id="btnAddBlock" variant="success">
                <i class="mr-2 fas fa-plus"></i> Agregar Espacio
            </x-button>
            <x-button id="btnSaveMap" variant="success">
                <i class="mr-2 fas fa-save"></i> Guardar Mapa
            </x-button>
            <x-button id="btnClearCanvas" variant="danger">
                <i class="mr-2 fas fa-trash"></i> Limpiar Todo
            </x-button>
            <div class="ml-auto">
                <label for="mapImageUpload" class="cursor-pointer">
                    <x-button variant="secondary">
                        <i class="mr-2 fas fa-upload"></i> Cargar Plano
                    </x-button>
                </label>
                <input id="mapImageUpload" type="file" accept="image/*" class="hidden">
            </div>
        </div>

        <div class="flex flex-col md:flex-row gap-6">
            <div class="w-full md:w-1/2 border-2 border-dashed border-gray-300 rounded-lg p-4 bg-gray-50 dark:bg-gray-900">
                <div class="relative" style="padding-top: 75%;">
                    
                </div>
            </div>

            <div class="w-full md:w-1/2 border-2 border-dashed border-gray-300 rounded-lg p-4 bg-gray-50 dark:bg-gray-900">
                <div class="relative" style="padding-top: 75%;">
                    <canvas id="mapCanvas"
                        class="absolute top-0 left-0 w-full h-full bg-white dark:bg-gray-800"></canvas>
                </div>
            </div>
        </div>
    </div>

    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <style>
        .select2-container--default .select2-selection--single {
            height: 42px;
            padding-top: 8px;
            border-color: #d1d5db;
        }

        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 40px;
        }
    </style>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/fabric@5.3.1/dist/fabric.min.js"></script>
    <script>
        $(document).ready(function() {
            // Inicializar Select2
            $('.select2').select2({
                width: '100%',
                theme: 'classic'
            });

            // Configurar canvas con Fabric.js
            const canvas = new fabric.Canvas('mapCanvas', {
                preserveObjectStacking: true,
                selection: true
            });

            // Ajustar tamaño del canvas cuando cambie el contenedor
            function resizeCanvas() {
                const container = document.querySelector('#mapCanvas').parentElement;
                const width = container.offsetWidth;
                const height = container.offsetHeight;

                canvas.setWidth(width);
                canvas.setHeight(height);
                canvas.renderAll();
            }
            
            // Inicializar y ajustar al redimensionar
            resizeCanvas();
            window.addEventListener('resize', resizeCanvas);

            // Cargar imagen de fondo
            document.getElementById('mapImageUpload').addEventListener('change', function(e) {
                const file = e.target.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function(event) {
                        document.getElementById('mapImagePreview').src = event.target.result;

                        // Cargar como fondo del canvas
                        fabric.Image.fromURL(event.target.result, function(img) {
                            // Escalar la imagen para que se ajuste al canvas
                            const scale = Math.min(
                                canvas.width / img.width,
                                canvas.height / img.height
                            );
                            img.set({
                                scaleX: scale,
                                scaleY: scale,
                                originX: 'left',
                                originY: 'top',
                                selectable: false,
                                evented: false
                            });
                            canvas.setBackgroundImage(img, canvas.renderAll.bind(canvas));
                        });
                    };
                    reader.readAsDataURL(file);
                }
            });

            // Selector jerárquico
            $('#universidad').change(function() {
                const idUniversidad = $(this).val();
                $('#facultad').empty().append('<option value="">Cargando...</option>').prop('disabled', true);
                $('#piso').empty().append('<option value="">Seleccione facultad primero</option>').prop('disabled', true);
                $('#espacio').empty().append('<option value="">Seleccione piso primero</option>').prop('disabled', true);

                if (idUniversidad) {
                    $.get(`/api/facultades/${idUniversidad}`, function(data) {
                        $('#facultad').empty().append('<option value="">Seleccione facultad</option>');
                        data.forEach(facultad => {
                            $('#facultad').append(
                                `<option value="${facultad.id_facultad}">${facultad.nombre_facultad}</option>`
                            );
                        });
                        $('#facultad').prop('disabled', false);
                    }).fail(function() {
                        alert('Error al cargar facultades');
                    });
                }
            });

            $('#facultad').change(function() {
                const idFacultad = $(this).val();
                $('#piso').empty().append('<option value="">Cargando...</option>').prop('disabled', true);
                $('#espacio').empty().append('<option value="">Seleccione piso primero</option>').prop('disabled', true);

                if (idFacultad) {
                    $.get(`/api/pisos/${idFacultad}`, function(data) {
                        $('#piso').empty().append('<option value="">Seleccione piso</option>');
                        data.forEach(piso => {
                            $('#piso').append(
                                `<option value="${piso.id_piso}">Piso ${piso.numero_piso}</option>`
                            );
                        });
                        $('#piso').prop('disabled', false);
                    }).fail(function() {
                        alert('Error al cargar pisos');
                    });
                }
            });

            $('#piso').change(function() {
                const idPiso = $(this).val();
                $('#espacio').empty().append('<option value="">Cargando...</option>').prop('disabled', true);

                if (idPiso) {
                    $.get(`/api/espacios/${idPiso}`, function(data) {
                        $('#espacio').empty().append('<option value="">Seleccione espacio</option>');
                        data.forEach(espacio => {
                            $('#espacio').append(
                                `<option value="${espacio.id_espacio}">${espacio.tipo_espacio} - ${espacio.estado}</option>`
                            );
                        });
                        $('#espacio').prop('disabled', false);
                        
                        // Cargar mapa existente para este piso
                        loadExistingMap(idPiso);
                    }).fail(function() {
                        alert('Error al cargar espacios');
                    });
                }
            });

            // Función para cargar mapa existente
            function loadExistingMap(pisoId) {
                if (!pisoId) return;
                
                $.get(`/api/mapas/${pisoId}`, function(data) {
                    if (data) {
                        // Establecer nombre del mapa
                        $('#nombre_mapa').val(data.nombre_mapa);
                        
                        // Cargar imagen de fondo si existe
                        if (data.ruta_mapa) {
                            document.getElementById('mapImagePreview').src = `/storage/${data.ruta_mapa}`;
                            
                            fabric.Image.fromURL(`/storage/${data.ruta_mapa}`, function(img) {
                                const scale = Math.min(
                                    canvas.width / img.width,
                                    canvas.height / img.height
                                );
                                img.set({
                                    scaleX: scale,
                                    scaleY: scale,
                                    originX: 'left',
                                    originY: 'top',
                                    selectable: false,
                                    evented: false
                                });
                                canvas.setBackgroundImage(img, canvas.renderAll.bind(canvas));
                            });
                        }
                        
                        // Cargar objetos del mapa si existen
                        if (data.objetos && data.objetos.length > 0) {
                            canvas.clear();
                            
                            data.objetos.forEach(obj => {
                                const rect = new fabric.Rect({
                                    left: obj.x,
                                    top: obj.y,
                                    width: obj.width,
                                    height: obj.height,
                                    fill: obj.color || '#3b82f6',
                                    opacity: 0.7,
                                    stroke: '#1e40af',
                                    strokeWidth: 2,
                                    rx: 5,
                                    ry: 5,
                                    selectable: true,
                                    hasControls: true,
                                    lockRotation: true,
                                    data: {
                                        espacioId: obj.espacioId,
                                        espacioText: obj.espacioText
                                    }
                                });

                                const text = new fabric.Text(obj.espacioText || 'Espacio', {
                                    fontSize: 12,
                                    fill: 'white',
                                    left: obj.x + 5,
                                    top: obj.y + 20,
                                    selectable: false,
                                    evented: false
                                });

                                const group = new fabric.Group([rect, text], {
                                    left: obj.x,
                                    top: obj.y,
                                    selectable: true,
                                    hasControls: true
                                });

                                canvas.add(group);
                            });
                            canvas.renderAll();
                        }
                    }
                }).fail(function() {
                    console.log('No se encontró mapa existente para este piso');
                });
            }

            // Agregar bloque al canvas
            $('#btnAddBlock').click(function() {
                const espacioId = $('#espacio').val();
                const espacioText = $('#espacio option:selected').text();

                if (!espacioId) {
                    alert('Por favor seleccione un espacio primero');
                    return;
                }

                // Crear rectángulo con Fabric.js
                const rect = new fabric.Rect({
                    left: 50,
                    top: 50,
                    width: 100,
                    height: 60,
                    fill: '#3b82f6',
                    opacity: 0.7,
                    stroke: '#1e40af',
                    strokeWidth: 2,
                    rx: 5, // Bordes redondeados
                    ry: 5,
                    selectable: true,
                    hasControls: true,
                    lockRotation: true,
                    data: { // Metadatos
                        espacioId: espacioId,
                        espacioText: espacioText
                    }
                });

                // Agregar texto
                const text = new fabric.Text(espacioText, {
                    fontSize: 12,
                    fill: 'white',
                    left: rect.left + 5,
                    top: rect.top + 20,
                    selectable: false,
                    evented: false
                });

                // Grupo para mantener juntos el rectángulo y el texto
                const group = new fabric.Group([rect, text], {
                    left: rect.left,
                    top: rect.top,
                    selectable: true,
                    hasControls: true
                });

                canvas.add(group);
                canvas.setActiveObject(group);
                canvas.renderAll();
            });

            // Guardar mapa
            $('#btnSaveMap').click(function() {
                const pisoId = $('#piso').val();
                const nombreMapa = $('#nombre_mapa').val();
                
                if (!pisoId) {
                    alert('Por favor seleccione un piso primero');
                    return;
                }
                
                if (!nombreMapa) {
                    alert('Por favor ingrese un nombre para el mapa');
                    return;
                }

                const objetos = canvas.getObjects()
                    .filter(obj => obj.type !== 'image') // Excluir imagen de fondo
                    .map(obj => {
                        return {
                            x: obj.left,
                            y: obj.top,
                            width: obj.width,
                            height: obj.height,
                            espacioId: obj.data?.espacioId,
                            espacioText: obj.data?.espacioText,
                            color: obj.fill
                        };
                    });

                // Preparar datos del formulario
                const formData = new FormData();
                formData.append('piso_id', pisoId);
                formData.append('nombre_mapa', nombreMapa);
                formData.append('objetos', JSON.stringify(objetos));
                
                // Agregar la imagen si se ha cargado
                const imageFile = document.getElementById('mapImageUpload').files[0];
                if (imageFile) {
                    formData.append('mapa_imagen', imageFile);
                }

                // Enviar datos al servidor
                $.ajax({
                    url: '/api/mapas/guardar',
                    method: 'POST',
                    data: formData,
                    contentType: false,
                    processData: false,
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        alert('Mapa guardado correctamente');
                        // Actualizar vista con los datos guardados
                        loadExistingMap(pisoId);
                    },
                    error: function(xhr) {
                        alert('Error al guardar el mapa: ' + (xhr.responseJSON?.message || 'Error desconocido'));
                    }
                });
            });

            // Limpiar canvas
            $('#btnClearCanvas').click(function() {
                if (confirm('¿Está seguro que desea limpiar todo el canvas?')) {
                    // Mantener solo la imagen de fondo
                    const bg = canvas.backgroundImage;
                    canvas.clear();
                    if (bg) {
                        canvas.setBackgroundImage(bg, canvas.renderAll.bind(canvas));
                    }
                }
            });
        });
    </script>
</x-app-layout>