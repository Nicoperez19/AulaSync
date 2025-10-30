<!-- Modal para Editar Espacios -->
<div id="modal-editar-espacios" class="fixed inset-0 z-[9999] flex items-center justify-center bg-black bg-opacity-50 hidden">
    <div class="flex flex-col w-full max-w-6xl max-h-screen mx-2 overflow-hidden bg-white rounded-lg shadow-lg md:mx-8">
        <!-- Encabezado -->
        <div class="relative flex items-center justify-between p-4 bg-green-600 text-white">
            <h2 class="text-lg font-bold">Editar Estados de Espacios</h2>
            <button onclick="cerrarModalEditarEspacios()" class="text-white hover:text-gray-200">
                <?php if (isset($component)) { $__componentOriginal643fe1b47aec0b76658e1a0200b34b2c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c = $attributes; } ?>
<?php $component = BladeUI\Icons\Components\Svg::resolve([] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('heroicon-s-x'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(BladeUI\Icons\Components\Svg::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'w-5 h-5']); ?>
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

        <!-- Contenido -->
        <div class="p-6 bg-gray-50 overflow-y-auto flex-1">
            <!-- Filtros -->
            <div class="mb-4 flex gap-4 items-center">
                <select 
                    id="filtro-estado-espacio"
                    onchange="filtrarEspacios()"
                    class="px-3 py-2 border border-gray-300 rounded-md">
                    <option value="">Todos los estados</option>
                    <option value="Disponible">Disponibles</option>
                    <option value="Ocupado">Ocupados</option>
                    <option value="Mantenimiento">En mantenimiento</option>
                </select>
                
                <select 
                    id="filtro-piso-espacio"
                    onchange="filtrarEspacios()"
                    class="px-3 py-2 border border-gray-300 rounded-md">
                    <option value="">Todos los pisos</option>
                    <option value="1">Piso 1</option>
                    <option value="2">Piso 2</option>
                    <option value="3">Piso 3</option>
                </select>
                
                <button 
                    onclick="cargarEspacios()"
                    class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700">
                    Actualizar
                </button>
            </div>

            <!-- Tabla de espacios -->
            <div class="bg-white rounded-lg border overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Código</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nombre</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tipo</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Piso</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Capacidad</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estado Actual</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="tabla-espacios-body" class="bg-white divide-y divide-gray-200">
                        <tr>
                            <td colspan="7" class="px-6 py-4 text-center text-gray-500">
                                Cargando espacios...
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?php /**PATH D:\Dev\AulaSync\resources\views/components/modal-editar-espacios.blade.php ENDPATH**/ ?>