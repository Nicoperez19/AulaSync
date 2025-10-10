<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test de Plantillas PDF - AulaSync</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .modal-backdrop {
            backdrop-filter: blur(4px);
        }
        .modal-content {
            max-height: 90vh;
            overflow-y: auto;
        }
    </style>
</head>
<body class="bg-gray-100">
    <div class="container mx-auto px-4 py-8">
        
        <!-- Header -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 mb-2">
                        <i class="fas fa-file-pdf text-indigo-600 mr-3"></i>
                        Test de Plantillas PDF
                    </h1>
                    <p class="text-gray-600">Visualiza y descarga PDFs de las plantillas de correo creadas</p>
                </div>
                @if($plantillas->count() > 0)
                    <a href="{{ route('test.plantillas.pdf.todos') }}" 
                       class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-indigo-600 to-purple-600 text-white rounded-lg hover:from-indigo-700 hover:to-purple-700 transition-all shadow-md">
                        <i class="fas fa-file-archive mr-2"></i>
                        Descargar Todos (ZIP)
                    </a>
                @endif
            </div>
        </div>

        <!-- Datos de Ejemplo -->
        <div class="bg-indigo-50 border-l-4 border-indigo-500 rounded-lg p-4 mb-6">
            <div class="flex items-start">
                <i class="fas fa-info-circle text-indigo-500 text-xl mr-3 mt-1"></i>
                <div>
                    <h3 class="font-semibold text-indigo-900 mb-2">Datos de Ejemplo Utilizados</h3>
                    <p class="text-sm text-indigo-800 mb-2">Los PDFs generados utilizan los siguientes datos de ejemplo para reemplazar las variables:</p>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-3 text-sm">
                        <div class="bg-white rounded p-2">
                            <span class="font-medium text-gray-700">Nombre:</span>
                            <span class="text-gray-600">Juan Pérez González</span>
                        </div>
                        <div class="bg-white rounded p-2">
                            <span class="font-medium text-gray-700">Email:</span>
                            <span class="text-gray-600">juan.perez@ejemplo.cl</span>
                        </div>
                        <div class="bg-white rounded p-2">
                            <span class="font-medium text-gray-700">Fecha:</span>
                            <span class="text-gray-600">{{ now()->format('d/m/Y') }}</span>
                        </div>
                        <div class="bg-white rounded p-2">
                            <span class="font-medium text-gray-700">Total Clases:</span>
                            <span class="text-gray-600">20</span>
                        </div>
                        <div class="bg-white rounded p-2">
                            <span class="font-medium text-gray-700">No Realizadas:</span>
                            <span class="text-gray-600">3</span>
                        </div>
                        <div class="bg-white rounded p-2">
                            <span class="font-medium text-gray-700">Porcentaje:</span>
                            <span class="text-gray-600">85%</span>
                        </div>
                        <div class="bg-white rounded p-2 col-span-2">
                            <span class="font-medium text-gray-700">Período:</span>
                            <span class="text-gray-600">Semana del {{ now()->startOfWeek()->format('d/m/Y') }} al {{ now()->endOfWeek()->format('d/m/Y') }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Lista de Plantillas -->
        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            <div class="p-6 bg-gradient-to-r from-indigo-500 to-purple-600">
                <h2 class="text-xl font-semibold text-white">
                    <i class="fas fa-list-ul mr-2"></i>
                    Plantillas Activas ({{ $plantillas->count() }})
                </h2>
            </div>

            @if($plantillas->isEmpty())
                <div class="p-12 text-center">
                    <i class="fas fa-inbox text-6xl text-gray-300 mb-4"></i>
                    <p class="text-gray-600 text-lg mb-2">No hay plantillas activas</p>
                    <p class="text-gray-500 text-sm">Crea plantillas en el panel de administración primero</p>
                </div>
            @else
                <div class="divide-y divide-gray-200">
                    @foreach($plantillas as $plantilla)
                        <div class="p-6 hover:bg-gray-50 transition-colors">
                            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                                
                                <!-- Información de la Plantilla -->
                                <div class="flex-1">
                                    <div class="flex items-start gap-3">
                                        <div class="flex-shrink-0">
                                            <button onclick="abrirVistaPrevia({{ $plantilla->id }}, '{{ addslashes($plantilla->nombre) }}')"
                                                    class="w-12 h-12 bg-indigo-100 rounded-lg flex items-center justify-center hover:bg-indigo-200 transition-colors group cursor-pointer"
                                                    title="Ver vista previa">
                                                <i class="fas fa-eye text-indigo-600 text-xl group-hover:scale-110 transition-transform"></i>
                                            </button>
                                        </div>
                                        <div class="flex-1">
                                            <h3 class="text-lg font-semibold text-gray-900 mb-1">
                                                {{ $plantilla->nombre }}
                                            </h3>
                                            <p class="text-sm text-gray-600 mb-2">
                                                <i class="fas fa-heading mr-1"></i>
                                                <strong>Asunto:</strong> {{ $plantilla->asunto }}
                                            </p>
                                            <div class="flex flex-wrap gap-2 items-center">
                                                @if($plantilla->tipoCorreo)
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                                                        <i class="fas fa-tag mr-1"></i>
                                                        {{ $plantilla->tipoCorreo->nombre }}
                                                    </span>
                                                @endif
                                                @if($plantilla->creador)
                                                    <span class="inline-flex items-center text-xs text-gray-500">
                                                        <i class="fas fa-user mr-1"></i>
                                                        Creada por: {{ $plantilla->creador->name }}
                                                    </span>
                                                @endif
                                                <span class="inline-flex items-center text-xs text-gray-500">
                                                    <i class="fas fa-calendar mr-1"></i>
                                                    {{ $plantilla->created_at->format('d/m/Y H:i') }}
                                                </span>
                                            </div>

                                            <!-- Variables Usadas -->
                                            @php
                                                $variablesUsadas = $plantilla->getVariablesUsadas();
                                            @endphp
                                            @if(count($variablesUsadas) > 0)
                                                <div class="mt-3">
                                                    <p class="text-xs text-gray-500 mb-1">
                                                        <i class="fas fa-code mr-1"></i>
                                                        Variables utilizadas:
                                                    </p>
                                                    <div class="flex flex-wrap gap-1">
                                                        @foreach($variablesUsadas as $variable)
                                                            <code class="px-2 py-1 bg-gray-100 text-gray-700 rounded text-xs font-mono">
                                                                @{{ '@{{' . trim($variable) . '@}}' }}
                                                            </code>
                                                        @endforeach
                                                    </div>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>

                                <!-- Acciones -->
                                <div class="flex flex-col gap-2 md:flex-row md:items-center">
                                    <button onclick="abrirVistaPrevia({{ $plantilla->id }}, '{{ addslashes($plantilla->nombre) }}')"
                                            class="inline-flex items-center justify-center px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 transition-colors text-sm font-medium">
                                        <i class="fas fa-eye mr-2"></i>
                                        Vista Previa
                                    </button>
                                    <a href="{{ route('test.plantillas.pdf.generar', $plantilla->id) }}" 
                                       target="_blank"
                                       class="inline-flex items-center justify-center px-4 py-2 bg-purple-600 text-white rounded-md hover:bg-purple-700 transition-colors text-sm font-medium">
                                        <i class="fas fa-download mr-2"></i>
                                        Descargar PDF
                                    </a>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        <!-- Footer con información adicional -->
        <div class="mt-6 bg-gray-800 rounded-lg p-6 text-white">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                    <h3 class="font-semibold mb-2 flex items-center">
                        <i class="fas fa-info-circle mr-2"></i>
                        Sobre esta Herramienta
                    </h3>
                    <p class="text-sm text-gray-300">
                        Esta herramienta te permite probar cómo se verán las plantillas de correo cuando se generen como PDF.
                    </p>
                </div>
                <div>
                    <h3 class="font-semibold mb-2 flex items-center">
                        <i class="fas fa-eye mr-2"></i>
                        Vista Previa HTML
                    </h3>
                    <p class="text-sm text-gray-300">
                        Abre el contenido renderizado directamente en el navegador para ver cómo se visualiza.
                    </p>
                </div>
                <div>
                    <h3 class="font-semibold mb-2 flex items-center">
                        <i class="fas fa-download mr-2"></i>
                        Generar PDF
                    </h3>
                    <p class="text-sm text-gray-300">
                        Descarga el PDF renderizado tal como se enviaría a los destinatarios.
                    </p>
                </div>
            </div>
        </div>

        <!-- Volver -->
        <div class="mt-6 flex justify-center gap-4">
            <a href="{{ route('correos-masivos.index') }}" 
               class="inline-flex items-center px-4 py-2 bg-white text-indigo-600 border-2 border-indigo-600 rounded-lg hover:bg-indigo-50 transition-colors font-medium">
                <i class="fas fa-arrow-left mr-2"></i>
                Volver a Correos Masivos
            </a>
            <a href="{{ url('/') }}" 
               class="inline-flex items-center px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-colors">
                <i class="fas fa-home mr-2"></i>
                Ir al Inicio
            </a>
        </div>
    </div>

    <!-- Modal de Vista Previa -->
    <div id="modalVistaPrevia" class="fixed inset-0 bg-gray-600 bg-opacity-75 modal-backdrop hidden z-50 overflow-y-auto" onclick="cerrarModal(event)">
        <div class="flex min-h-screen items-center justify-center p-4">
            <div class="relative bg-white rounded-lg shadow-xl w-full max-w-5xl modal-content" onclick="event.stopPropagation()">
                
                <!-- Header del Modal -->
                <div class="sticky top-0 bg-gradient-to-r from-indigo-600 to-purple-600 text-white px-6 py-4 rounded-t-lg z-10 flex justify-between items-center">
                    <div class="flex items-center gap-3">
                        <i class="fas fa-eye text-2xl"></i>
                        <div>
                            <h3 class="text-xl font-bold" id="modalTitulo">Vista Previa de Plantilla</h3>
                            <p class="text-sm opacity-90">Vista previa del correo con datos de ejemplo</p>
                        </div>
                    </div>
                    <div class="flex gap-2">
                        <a id="btnDescargarPdf" href="#" target="_blank"
                           class="inline-flex items-center px-4 py-2 bg-white text-indigo-600 rounded-md hover:bg-indigo-50 transition-colors text-sm font-medium">
                            <i class="fas fa-download mr-2"></i>
                            Descargar PDF
                        </a>
                        <button onclick="cerrarModal()" 
                                class="text-white hover:text-gray-200 transition-colors">
                            <i class="fas fa-times text-2xl"></i>
                        </button>
                    </div>
                </div>

                <!-- Contenido del Modal -->
                <div class="p-6">
                    <div id="contenidoVistaPrevia" class="bg-gray-50 rounded-lg p-4 min-h-[500px]">
                        <div class="flex items-center justify-center h-full">
                            <div class="text-center">
                                <i class="fas fa-spinner fa-spin text-4xl text-indigo-600 mb-3"></i>
                                <p class="text-gray-600">Cargando vista previa...</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Footer del Modal -->
                <div class="sticky bottom-0 bg-gray-50 px-6 py-4 rounded-b-lg border-t flex justify-between items-center">
                    <div class="text-sm text-gray-600">
                        <i class="fas fa-info-circle mr-1"></i>
                        Esta es una vista previa con datos de ejemplo
                    </div>
                    <button onclick="cerrarModal()" 
                            class="px-6 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 transition-colors font-medium">
                        <i class="fas fa-times mr-2"></i>
                        Cerrar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        function abrirVistaPrevia(plantillaId, nombrePlantilla) {
            const modal = document.getElementById('modalVistaPrevia');
            const titulo = document.getElementById('modalTitulo');
            const contenido = document.getElementById('contenidoVistaPrevia');
            const btnDescargar = document.getElementById('btnDescargarPdf');
            
            // Actualizar título
            titulo.textContent = nombrePlantilla;
            
            // Actualizar enlace de descarga
            btnDescargar.href = `/test/plantillas-pdf/generar/${plantillaId}`;
            
            // Mostrar modal con loading
            modal.classList.remove('hidden');
            contenido.innerHTML = `
                <div class="flex items-center justify-center min-h-[500px]">
                    <div class="text-center">
                        <i class="fas fa-spinner fa-spin text-4xl text-indigo-600 mb-3"></i>
                        <p class="text-gray-600 text-lg">Cargando vista previa...</p>
                    </div>
                </div>
            `;
            
            // Bloquear scroll del body
            document.body.style.overflow = 'hidden';
            
            // Cargar contenido via fetch
            fetch(`/test/plantillas-pdf/preview/${plantillaId}`)
                .then(response => response.text())
                .then(html => {
                    contenido.innerHTML = `
                        <div class="bg-white rounded shadow-inner">
                            <iframe srcdoc="${html.replace(/"/g, '&quot;')}" 
                                    class="w-full border-0 rounded" 
                                    style="height: 70vh; min-height: 500px;">
                            </iframe>
                        </div>
                    `;
                })
                .catch(error => {
                    contenido.innerHTML = `
                        <div class="flex items-center justify-center min-h-[500px]">
                            <div class="text-center">
                                <i class="fas fa-exclamation-circle text-4xl text-red-600 mb-3"></i>
                                <p class="text-red-600 text-lg font-semibold mb-2">Error al cargar la vista previa</p>
                                <p class="text-gray-600 text-sm">${error.message}</p>
                            </div>
                        </div>
                    `;
                });
        }
        
        function cerrarModal(event) {
            // Si se hizo click en el backdrop o en el botón cerrar
            if (!event || event.target.id === 'modalVistaPrevia' || event.type === 'click') {
                const modal = document.getElementById('modalVistaPrevia');
                modal.classList.add('hidden');
                // Restaurar scroll del body
                document.body.style.overflow = 'auto';
            }
        }
        
        // Cerrar modal con tecla ESC
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                cerrarModal();
            }
        });
    </script>
</body>
</html>
