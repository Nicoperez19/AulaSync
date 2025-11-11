<!-- Modal para Agregar Reserva -->
<div id="modal-agregar-reserva" class="fixed inset-0 z-[9999] flex items-center justify-center bg-black bg-opacity-50 hidden">
    <div class="flex flex-col w-full max-w-4xl max-h-screen mx-2 overflow-hidden bg-white rounded-lg shadow-lg md:mx-8">
        <!-- Encabezado -->
        <div class="relative flex flex-col gap-6 p-6 bg-gradient-to-r bg-green-600 text-white">
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-bold">Agregar Nueva Reserva</h2>
                <button onclick="cerrarModalAgregarReserva()" class="text-white hover:text-gray-200">
                    <?php if (isset($component)) { $__componentOriginal643fe1b47aec0b76658e1a0200b34b2c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c = $attributes; } ?>
<?php $component = BladeUI\Icons\Components\Svg::resolve([] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('heroicon-s-x'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(BladeUI\Icons\Components\Svg::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'w-6 h-6']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c)): ?>
<?php $attributes = $__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c; ?>
<?php unset($__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal643fe1b47aec0b76658e1a0200b34b2c)): ?>
<?php $component = $__componentOriginal643fe1b47aec0b76658e1a0200b34b2c; ?>
<?php unset($__componentOriginal643fe1b47aec0b76658e1a0200b34b2c); ?>
<?php endif; ?>
                </button>
            </div>
        </div>

        <!-- Contenido del modal -->
        <div class="p-6 bg-gray-50 overflow-y-auto max-h-[70vh] flex-1">
            <!-- Token CSRF -->
            <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
            
            <form id="form-agregar-reserva" onsubmit="procesarAgregarReserva(event)">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    
                    <!-- Información del Responsable -->
                    <div class="bg-white p-4 rounded-lg border">
                        <h3 class="text-lg font-semibold text-gray-800 mb-4">Responsable</h3>
                        
                        <!-- Búsqueda por RUN -->
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Buscar por RUN
                            </label>
                            <div class="flex gap-2">
                                <input 
                                    type="text" 
                                    id="run-busqueda"
                                    placeholder="Ingrese RUN (sin puntos ni guión)"
                                    class="flex-1 px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500"
                                />
                                <button 
                                    type="button"
                                    onclick="buscarPorRun()"
                                    class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                                    Buscar
                                </button>
                            </div>
                            <div id="resultado-busqueda" class="mt-2 text-sm"></div>
                        </div>

                        <div class="border-t pt-4">
                            <p class="text-sm text-gray-600 mb-3">O agregar nuevo:</p>
                            
                            <div class="grid grid-cols-1 gap-3">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Nombre completo *</label>
                                    <input 
                                        type="text" 
                                        id="nombre-responsable"
                                        required
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500"
                                    />
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">RUN *</label>
                                    <input 
                                        type="text" 
                                        id="run-responsable"
                                        required
                                        placeholder="Sin puntos ni guión"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500"
                                    />
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Correo electrónico *</label>
                                    <input 
                                        type="email" 
                                        id="correo-responsable"
                                        required
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500"
                                    />
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Teléfono</label>
                                    <input 
                                        type="tel" 
                                        id="telefono-responsable"
                                        placeholder="9 dígitos"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500"
                                    />
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Tipo *</label>
                                    <select 
                                        id="tipo-responsable"
                                        required
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500">
                                        <option value="">Seleccione tipo</option>
                                        <option value="profesor">Profesor</option>
                                        <option value="solicitante">Solicitante externo</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Información de la Reserva -->
                    <div class="bg-white p-4 rounded-lg border">
                        <h3 class="text-lg font-semibold text-gray-800 mb-4">Detalles de la Reserva</h3>
                        
                        <div class="grid grid-cols-1 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Espacio *</label>
                                <select 
                                    id="espacio-reserva"
                                    required
                                    onchange="actualizarModulosDisponibles()"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500">
                                    <option value="">Cargando espacios...</option>
                                </select>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Fecha *</label>
                                <input 
                                    type="date" 
                                    id="fecha-reserva"
                                    required
                                    min="<?php echo e(date('Y-m-d')); ?>"
                                    value="<?php echo e(date('Y-m-d')); ?>"
                                    onchange="cargarModulosParaSeleccion()"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500"
                                />
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Módulo inicial *</label>
                                <select 
                                    id="modulo-inicial"
                                    required
                                    onchange="actualizarModulosFinales()"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500">
                                    <option value="">Seleccione módulo inicial</option>
                                    <!-- Se llenarán dinámicamente -->
                                </select>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Módulo final *</label>
                                <select 
                                    id="modulo-final"
                                    required
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500">
                                    <option value="">Seleccione módulo final</option>
                                    <!-- Se llenarán dinámicamente -->
                                </select>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Observaciones</label>
                                <textarea 
                                    id="observaciones-reserva"
                                    rows="3"
                                    placeholder="Observaciones adicionales..."
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500"></textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Botones de acción -->
                <div class="flex justify-end gap-3 mt-6 pt-4 border-t">
                    <button 
                        type="button"
                        onclick="cerrarModalAgregarReserva()"
                        class="px-6 py-2 text-sm font-medium text-gray-700 bg-gray-200 rounded-md hover:bg-gray-300">
                        Cancelar
                    </button>
                    <button 
                        type="submit"
                        class="px-6 py-2 text-sm font-medium text-white bg-green-600 rounded-md hover:bg-green-700">
                        Crear Reserva
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php /**PATH C:\Users\conym\OneDrive\Documentos\GitHub\AulaSync\resources\views/components/modal-agregar-reserva.blade.php ENDPATH**/ ?>