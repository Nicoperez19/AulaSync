<?php if (isset($component)) { $__componentOriginal9ac128a9029c0e4701924bd2d73d7f54 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal9ac128a9029c0e4701924bd2d73d7f54 = $attributes; } ?>
<?php $component = App\View\Components\AppLayout::resolve([] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('app-layout'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(App\View\Components\AppLayout::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
     <?php $__env->slot('header', null, []); ?> 
        <div class="flex flex-col gap-2 pr-6 md:flex-row md:items-center md:justify-between">
            <div class="flex items-center gap-3">
                <div class="p-2 rounded-xl bg-light-cloud-blue">
                    <i class="text-2xl text-white fa-solid fa-building"></i>
                </div>

                <div>
                    <h2 class="text-2xl font-bold leading-tight">Espacios</h2>
                    <p class="text-sm text-gray-500">Administra los espacios físicos disponibles en el sistema</p>
                </div>
            </div>

        </div>
     <?php $__env->endSlot(); ?>

    <div class="p-6 bg-white rounded-lg shadow-lg">
        <div class="flex items-center justify-between mt-4">
            <div class="w-2/3">
                <input type="text" id="searchInput" onkeyup="searchTable()" placeholder="Buscar por Nombre o ID"
                    class="w-full px-4 py-2 border rounded dark:bg-gray-700 dark:text-white">
            </div>
            <div class="flex gap-2">
                <?php if (isset($component)) { $__componentOriginald0f1fd2689e4bb7060122a5b91fe8561 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginald0f1fd2689e4bb7060122a5b91fe8561 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.button','data' => ['variant' => 'add','class' => 'justify-end max-w-xs gap-2','xOn:click.prevent' => '$dispatch(\'open-modal\', \'add-espacio\')']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['variant' => 'add','class' => 'justify-end max-w-xs gap-2','x-on:click.prevent' => '$dispatch(\'open-modal\', \'add-espacio\')']); ?>
                    <?php if (isset($component)) { $__componentOriginal167d49e5be30319451e9d24e17d8a630 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal167d49e5be30319451e9d24e17d8a630 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.icons.add','data' => ['class' => 'w-6 h-6','ariaHidden' => 'true']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('icons.add'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'w-6 h-6','aria-hidden' => 'true']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal167d49e5be30319451e9d24e17d8a630)): ?>
<?php $attributes = $__attributesOriginal167d49e5be30319451e9d24e17d8a630; ?>
<?php unset($__attributesOriginal167d49e5be30319451e9d24e17d8a630); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal167d49e5be30319451e9d24e17d8a630)): ?>
<?php $component = $__componentOriginal167d49e5be30319451e9d24e17d8a630; ?>
<?php unset($__componentOriginal167d49e5be30319451e9d24e17d8a630); ?>
<?php endif; ?>
                    Agregar Espacio
                 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginald0f1fd2689e4bb7060122a5b91fe8561)): ?>
<?php $attributes = $__attributesOriginald0f1fd2689e4bb7060122a5b91fe8561; ?>
<?php unset($__attributesOriginald0f1fd2689e4bb7060122a5b91fe8561); ?>
<?php endif; ?>
<?php if (isset($__componentOriginald0f1fd2689e4bb7060122a5b91fe8561)): ?>
<?php $component = $__componentOriginald0f1fd2689e4bb7060122a5b91fe8561; ?>
<?php unset($__componentOriginald0f1fd2689e4bb7060122a5b91fe8561); ?>
<?php endif; ?>
                <a href="<?php echo e(route('spaces.download-all-qr')); ?>"
                    class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-white bg-orange-400 border border-transparent rounded-md hover:bg-orange-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                        </path>
                    </svg>
                    Descargar QRs
                </a>
            </div>
        </div>
        <?php
$__split = function ($name, $params = []) {
    return [$name, $params];
};
[$__name, $__params] = $__split('spaces-table', []);

$__html = app('livewire')->mount($__name, $__params, 'lw-749365020-0', $__slots ?? [], get_defined_vars());

echo $__html;

unset($__html);
unset($__name);
unset($__params);
unset($__split);
if (isset($__slots)) unset($__slots);
?>
        <?php if (isset($component)) { $__componentOriginal9f64f32e90b9102968f2bc548315018c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal9f64f32e90b9102968f2bc548315018c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.modal','data' => ['name' => 'add-espacio','show' => $errors->any(),'focusable' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('modal'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'add-espacio','show' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($errors->any()),'focusable' => true]); ?>
            <?php $__env->slot('title'); ?>
            <div class="relative flex items-center justify-between p-2 bg-red-700">
                <div class="flex items-center gap-3">
                    <div class="p-4 bg-red-100 rounded-full">
                        <?php if (isset($component)) { $__componentOriginal1093bc2ccca6333f587cc054577deeb7 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal1093bc2ccca6333f587cc054577deeb7 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.icons.building-office','data' => ['class' => 'w-6 h-6 text-red-600','ariaHidden' => 'true','rt' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('icons.building-office'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'w-6 h-6 text-red-600','aria-hidden' => 'true','rt' => true]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal1093bc2ccca6333f587cc054577deeb7)): ?>
<?php $attributes = $__attributesOriginal1093bc2ccca6333f587cc054577deeb7; ?>
<?php unset($__attributesOriginal1093bc2ccca6333f587cc054577deeb7); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal1093bc2ccca6333f587cc054577deeb7)): ?>
<?php $component = $__componentOriginal1093bc2ccca6333f587cc054577deeb7; ?>
<?php unset($__componentOriginal1093bc2ccca6333f587cc054577deeb7); ?>
<?php endif; ?>

                    </div>
                    <h2 class="text-2xl font-bold text-white">
                        Agregar Espacio </h2>
                </div>
                <button @click="show = false"
                    class="ml-2 text-2xl font-bold text-white hover:text-gray-200">&times;</button>
                <!-- Círculos decorativos -->
                <span
                    class="absolute top-0 left-0 w-32 h-32 -translate-x-1/2 -translate-y-1/2 bg-white rounded-full pointer-events-none bg-opacity-10"></span>
                <span
                    class="absolute top-0 right-0 w-32 h-32 translate-x-1/2 -translate-y-1/2 bg-white rounded-full pointer-events-none bg-opacity-10"></span>
            </div>
            <?php $__env->endSlot(); ?>

            <form method="POST" action="<?php echo e(route('spaces.store')); ?>">
                <?php echo csrf_field(); ?>

                <!-- Campos hidden con valores por defecto -->
                <input type="hidden" name="estado" value="Disponible">

                <div class="p-6 space-y-6">
                    <!-- Información básica del espacio -->
                    <div class="space-y-4">
                        <h3 class="text-sm font-semibold tracking-wide text-gray-700 uppercase">Información del Espacio
                        </h3>

                        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                            <div class="space-y-2">
                                <?php if (isset($component)) { $__componentOriginal306f477fe089d4f950325a3d0a498c1c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal306f477fe089d4f950325a3d0a498c1c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.form.label','data' => ['for' => 'id_espacio','value' => __('ID del Espacio'),'class' => 'font-medium text-left text-gray-700']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('form.label'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['for' => 'id_espacio','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('ID del Espacio')),'class' => 'font-medium text-left text-gray-700']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal306f477fe089d4f950325a3d0a498c1c)): ?>
<?php $attributes = $__attributesOriginal306f477fe089d4f950325a3d0a498c1c; ?>
<?php unset($__attributesOriginal306f477fe089d4f950325a3d0a498c1c); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal306f477fe089d4f950325a3d0a498c1c)): ?>
<?php $component = $__componentOriginal306f477fe089d4f950325a3d0a498c1c; ?>
<?php unset($__componentOriginal306f477fe089d4f950325a3d0a498c1c); ?>
<?php endif; ?>
                                <?php if (isset($component)) { $__componentOriginald599939fa78ef9d91bfef4309fb3a81c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginald599939fa78ef9d91bfef4309fb3a81c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.form.input-with-icon-wrapper','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('form.input-with-icon-wrapper'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
                                     <?php $__env->slot('icon', null, []); ?> 
                                        <?php if (isset($component)) { $__componentOriginalb8c2af2c7c4a456e77f6ae42c74e5e35 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalb8c2af2c7c4a456e77f6ae42c74e5e35 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.icons.user','data' => ['class' => 'w-5 h-5 text-gray-400','ariaHidden' => 'true']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('icons.user'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'w-5 h-5 text-gray-400','aria-hidden' => 'true']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalb8c2af2c7c4a456e77f6ae42c74e5e35)): ?>
<?php $attributes = $__attributesOriginalb8c2af2c7c4a456e77f6ae42c74e5e35; ?>
<?php unset($__attributesOriginalb8c2af2c7c4a456e77f6ae42c74e5e35); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalb8c2af2c7c4a456e77f6ae42c74e5e35)): ?>
<?php $component = $__componentOriginalb8c2af2c7c4a456e77f6ae42c74e5e35; ?>
<?php unset($__componentOriginalb8c2af2c7c4a456e77f6ae42c74e5e35); ?>
<?php endif; ?>
                                     <?php $__env->endSlot(); ?>
                                    <?php if (isset($component)) { $__componentOriginal5c2a97ab476b69c1189ee85d1a95204b = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal5c2a97ab476b69c1189ee85d1a95204b = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.form.input','data' => ['id' => 'id_espacio','class' => 'block w-full','type' => 'text','name' => 'id_espacio','value' => old('id_espacio'),'placeholder' => ''.e(__('Ej: ESP-001')).'','required' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('form.input'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['id' => 'id_espacio','class' => 'block w-full','type' => 'text','name' => 'id_espacio','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(old('id_espacio')),'placeholder' => ''.e(__('Ej: ESP-001')).'','required' => true]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal5c2a97ab476b69c1189ee85d1a95204b)): ?>
<?php $attributes = $__attributesOriginal5c2a97ab476b69c1189ee85d1a95204b; ?>
<?php unset($__attributesOriginal5c2a97ab476b69c1189ee85d1a95204b); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal5c2a97ab476b69c1189ee85d1a95204b)): ?>
<?php $component = $__componentOriginal5c2a97ab476b69c1189ee85d1a95204b; ?>
<?php unset($__componentOriginal5c2a97ab476b69c1189ee85d1a95204b); ?>
<?php endif; ?>
                                 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginald599939fa78ef9d91bfef4309fb3a81c)): ?>
<?php $attributes = $__attributesOriginald599939fa78ef9d91bfef4309fb3a81c; ?>
<?php unset($__attributesOriginald599939fa78ef9d91bfef4309fb3a81c); ?>
<?php endif; ?>
<?php if (isset($__componentOriginald599939fa78ef9d91bfef4309fb3a81c)): ?>
<?php $component = $__componentOriginald599939fa78ef9d91bfef4309fb3a81c; ?>
<?php unset($__componentOriginald599939fa78ef9d91bfef4309fb3a81c); ?>
<?php endif; ?>
                            </div>

                            <div class="space-y-2">
                                <?php if (isset($component)) { $__componentOriginal306f477fe089d4f950325a3d0a498c1c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal306f477fe089d4f950325a3d0a498c1c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.form.label','data' => ['for' => 'nombre_espacio','value' => __('Nombre del Espacio'),'class' => 'font-medium text-left text-gray-700']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('form.label'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['for' => 'nombre_espacio','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Nombre del Espacio')),'class' => 'font-medium text-left text-gray-700']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal306f477fe089d4f950325a3d0a498c1c)): ?>
<?php $attributes = $__attributesOriginal306f477fe089d4f950325a3d0a498c1c; ?>
<?php unset($__attributesOriginal306f477fe089d4f950325a3d0a498c1c); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal306f477fe089d4f950325a3d0a498c1c)): ?>
<?php $component = $__componentOriginal306f477fe089d4f950325a3d0a498c1c; ?>
<?php unset($__componentOriginal306f477fe089d4f950325a3d0a498c1c); ?>
<?php endif; ?>
                                <?php if (isset($component)) { $__componentOriginald599939fa78ef9d91bfef4309fb3a81c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginald599939fa78ef9d91bfef4309fb3a81c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.form.input-with-icon-wrapper','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('form.input-with-icon-wrapper'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
                                     <?php $__env->slot('icon', null, []); ?> 
                                        <?php if (isset($component)) { $__componentOriginal1093bc2ccca6333f587cc054577deeb7 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal1093bc2ccca6333f587cc054577deeb7 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.icons.building-office','data' => ['class' => 'w-5 h-5 text-gray-400','ariaHidden' => 'true']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('icons.building-office'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'w-5 h-5 text-gray-400','aria-hidden' => 'true']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal1093bc2ccca6333f587cc054577deeb7)): ?>
<?php $attributes = $__attributesOriginal1093bc2ccca6333f587cc054577deeb7; ?>
<?php unset($__attributesOriginal1093bc2ccca6333f587cc054577deeb7); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal1093bc2ccca6333f587cc054577deeb7)): ?>
<?php $component = $__componentOriginal1093bc2ccca6333f587cc054577deeb7; ?>
<?php unset($__componentOriginal1093bc2ccca6333f587cc054577deeb7); ?>
<?php endif; ?>
                                     <?php $__env->endSlot(); ?>
                                    <?php if (isset($component)) { $__componentOriginal5c2a97ab476b69c1189ee85d1a95204b = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal5c2a97ab476b69c1189ee85d1a95204b = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.form.input','data' => ['id' => 'nombre_espacio','class' => 'block w-full','type' => 'text','name' => 'nombre_espacio','value' => old('nombre_espacio'),'placeholder' => ''.e(__('Ej: Laboratorio de Computación')).'','required' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('form.input'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['id' => 'nombre_espacio','class' => 'block w-full','type' => 'text','name' => 'nombre_espacio','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(old('nombre_espacio')),'placeholder' => ''.e(__('Ej: Laboratorio de Computación')).'','required' => true]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal5c2a97ab476b69c1189ee85d1a95204b)): ?>
<?php $attributes = $__attributesOriginal5c2a97ab476b69c1189ee85d1a95204b; ?>
<?php unset($__attributesOriginal5c2a97ab476b69c1189ee85d1a95204b); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal5c2a97ab476b69c1189ee85d1a95204b)): ?>
<?php $component = $__componentOriginal5c2a97ab476b69c1189ee85d1a95204b; ?>
<?php unset($__componentOriginal5c2a97ab476b69c1189ee85d1a95204b); ?>
<?php endif; ?>
                                 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginald599939fa78ef9d91bfef4309fb3a81c)): ?>
<?php $attributes = $__attributesOriginald599939fa78ef9d91bfef4309fb3a81c; ?>
<?php unset($__attributesOriginald599939fa78ef9d91bfef4309fb3a81c); ?>
<?php endif; ?>
<?php if (isset($__componentOriginald599939fa78ef9d91bfef4309fb3a81c)): ?>
<?php $component = $__componentOriginald599939fa78ef9d91bfef4309fb3a81c; ?>
<?php unset($__componentOriginald599939fa78ef9d91bfef4309fb3a81c); ?>
<?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Ubicación -->
                    <div class="space-y-4">
                        <h3 class="text-sm font-semibold tracking-wide text-gray-700 uppercase">Ubicación</h3>

                        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                            <div class="space-y-2">
                                <?php if (isset($component)) { $__componentOriginal306f477fe089d4f950325a3d0a498c1c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal306f477fe089d4f950325a3d0a498c1c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.form.label','data' => ['for' => 'universidad','value' => __('Universidad'),'class' => 'font-medium text-left text-gray-700']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('form.label'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['for' => 'universidad','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Universidad')),'class' => 'font-medium text-left text-gray-700']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal306f477fe089d4f950325a3d0a498c1c)): ?>
<?php $attributes = $__attributesOriginal306f477fe089d4f950325a3d0a498c1c; ?>
<?php unset($__attributesOriginal306f477fe089d4f950325a3d0a498c1c); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal306f477fe089d4f950325a3d0a498c1c)): ?>
<?php $component = $__componentOriginal306f477fe089d4f950325a3d0a498c1c; ?>
<?php unset($__componentOriginal306f477fe089d4f950325a3d0a498c1c); ?>
<?php endif; ?>
                                <select name="id_universidad" id="universidad"
                                    class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-red-500 focus:border-red-500 sm:text-sm"
                                    required onchange="loadSedes()">
                                    <option value="" disabled selected><?php echo e(__('Seleccionar Universidad')); ?></option>
                                    <?php $__currentLoopData = $universidades; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $universidad): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($universidad->id_universidad); ?>" <?php echo e(old('id_universidad') == $universidad->id_universidad ? 'selected' : ''); ?>>
                                            <?php echo e($universidad->nombre_universidad); ?>

                                        </option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                            </div>

                            <div class="space-y-2">
                                <?php if (isset($component)) { $__componentOriginal306f477fe089d4f950325a3d0a498c1c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal306f477fe089d4f950325a3d0a498c1c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.form.label','data' => ['for' => 'sede','value' => __('Sede'),'class' => 'font-medium text-left text-gray-700']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('form.label'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['for' => 'sede','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Sede')),'class' => 'font-medium text-left text-gray-700']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal306f477fe089d4f950325a3d0a498c1c)): ?>
<?php $attributes = $__attributesOriginal306f477fe089d4f950325a3d0a498c1c; ?>
<?php unset($__attributesOriginal306f477fe089d4f950325a3d0a498c1c); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal306f477fe089d4f950325a3d0a498c1c)): ?>
<?php $component = $__componentOriginal306f477fe089d4f950325a3d0a498c1c; ?>
<?php unset($__componentOriginal306f477fe089d4f950325a3d0a498c1c); ?>
<?php endif; ?>
                                <select name="id_sede" id="sede"
                                    class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-red-500 focus:border-red-500 sm:text-sm"
                                    required onchange="loadFacultades()" disabled>
                                    <option value="" disabled selected><?php echo e(__('Seleccionar Sede')); ?></option>
                                </select>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                            <div class="space-y-2">
                                <?php if (isset($component)) { $__componentOriginal306f477fe089d4f950325a3d0a498c1c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal306f477fe089d4f950325a3d0a498c1c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.form.label','data' => ['for' => 'facultad','value' => __('Facultad'),'class' => 'font-medium text-left text-gray-700']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('form.label'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['for' => 'facultad','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Facultad')),'class' => 'font-medium text-left text-gray-700']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal306f477fe089d4f950325a3d0a498c1c)): ?>
<?php $attributes = $__attributesOriginal306f477fe089d4f950325a3d0a498c1c; ?>
<?php unset($__attributesOriginal306f477fe089d4f950325a3d0a498c1c); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal306f477fe089d4f950325a3d0a498c1c)): ?>
<?php $component = $__componentOriginal306f477fe089d4f950325a3d0a498c1c; ?>
<?php unset($__componentOriginal306f477fe089d4f950325a3d0a498c1c); ?>
<?php endif; ?>
                                <select name="id_facultad" id="facultad"
                                    class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-red-500 focus:border-red-500 sm:text-sm"
                                    required onchange="loadPisos()" disabled>
                                    <option value="" disabled selected><?php echo e(__('Seleccionar Facultad')); ?></option>
                                </select>
                            </div>

                            <div class="space-y-2">
                                <?php if (isset($component)) { $__componentOriginal306f477fe089d4f950325a3d0a498c1c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal306f477fe089d4f950325a3d0a498c1c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.form.label','data' => ['for' => 'selectedPiso','value' => __('Piso'),'class' => 'font-medium text-left text-gray-700']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('form.label'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['for' => 'selectedPiso','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Piso')),'class' => 'font-medium text-left text-gray-700']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal306f477fe089d4f950325a3d0a498c1c)): ?>
<?php $attributes = $__attributesOriginal306f477fe089d4f950325a3d0a498c1c; ?>
<?php unset($__attributesOriginal306f477fe089d4f950325a3d0a498c1c); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal306f477fe089d4f950325a3d0a498c1c)): ?>
<?php $component = $__componentOriginal306f477fe089d4f950325a3d0a498c1c; ?>
<?php unset($__componentOriginal306f477fe089d4f950325a3d0a498c1c); ?>
<?php endif; ?>
                                <select name="piso_id" id="selectedPiso"
                                    class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-red-500 focus:border-red-500 sm:text-sm"
                                    required disabled>
                                    <option value="" disabled selected><?php echo e(__('Seleccionar Piso')); ?></option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Características del espacio -->
                    <div class="space-y-4">
                        <h3 class="text-sm font-semibold tracking-wide text-gray-700 uppercase">Características</h3>

                        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                            <div class="space-y-2">
                                <?php if (isset($component)) { $__componentOriginal306f477fe089d4f950325a3d0a498c1c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal306f477fe089d4f950325a3d0a498c1c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.form.label','data' => ['for' => 'tipo_espacio','value' => __('Tipo de Espacio'),'class' => 'font-medium text-left text-gray-700']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('form.label'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['for' => 'tipo_espacio','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Tipo de Espacio')),'class' => 'font-medium text-left text-gray-700']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal306f477fe089d4f950325a3d0a498c1c)): ?>
<?php $attributes = $__attributesOriginal306f477fe089d4f950325a3d0a498c1c; ?>
<?php unset($__attributesOriginal306f477fe089d4f950325a3d0a498c1c); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal306f477fe089d4f950325a3d0a498c1c)): ?>
<?php $component = $__componentOriginal306f477fe089d4f950325a3d0a498c1c; ?>
<?php unset($__componentOriginal306f477fe089d4f950325a3d0a498c1c); ?>
<?php endif; ?>
                                <select name="tipo_espacio" id="tipo_espacio"
                                    class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-red-500 focus:border-red-500 sm:text-sm"
                                    required>
                                    <option value="" disabled selected><?php echo e(__('Seleccionar Tipo de Espacio')); ?></option>
                                    <option value="Sala de Clases" <?php echo e(old('tipo_espacio') == 'Sala de Clases' ? 'selected' : ''); ?>>
                                        <?php echo e(__('Aula')); ?>

                                    </option>
                                    <option value="Laboratorio" <?php echo e(old('tipo_espacio') == 'Laboratorio' ? 'selected' : ''); ?>>
                                        <?php echo e(__('Laboratorio')); ?>

                                    </option>
                                    <option value="Biblioteca" <?php echo e(old('tipo_espacio') == 'Biblioteca' ? 'selected' : ''); ?>>
                                        <?php echo e(__('Biblioteca')); ?>

                                    </option>
                                    <option value="Sala de Reuniones" <?php echo e(old('tipo_espacio') == 'Sala de Reuniones' ? 'selected' : ''); ?>>
                                        <?php echo e(__('Sala de Reuniones')); ?>

                                    </option>
                                    <option value="Oficinas" <?php echo e(old('tipo_espacio') == 'Oficinas' ? 'selected' : ''); ?>>
                                        <?php echo e(__('Oficinas')); ?>

                                    </option>
                                </select>
                            </div>

                            <div class="space-y-2">
                                <?php if (isset($component)) { $__componentOriginal306f477fe089d4f950325a3d0a498c1c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal306f477fe089d4f950325a3d0a498c1c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.form.label','data' => ['for' => 'puestos_disponibles','value' => __('Puestos Disponibles'),'class' => 'font-medium text-left text-gray-700']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('form.label'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['for' => 'puestos_disponibles','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Puestos Disponibles')),'class' => 'font-medium text-left text-gray-700']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal306f477fe089d4f950325a3d0a498c1c)): ?>
<?php $attributes = $__attributesOriginal306f477fe089d4f950325a3d0a498c1c; ?>
<?php unset($__attributesOriginal306f477fe089d4f950325a3d0a498c1c); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal306f477fe089d4f950325a3d0a498c1c)): ?>
<?php $component = $__componentOriginal306f477fe089d4f950325a3d0a498c1c; ?>
<?php unset($__componentOriginal306f477fe089d4f950325a3d0a498c1c); ?>
<?php endif; ?>
                                <?php if (isset($component)) { $__componentOriginald599939fa78ef9d91bfef4309fb3a81c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginald599939fa78ef9d91bfef4309fb3a81c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.form.input-with-icon-wrapper','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('form.input-with-icon-wrapper'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
                                     <?php $__env->slot('icon', null, []); ?> 
                                        <?php if (isset($component)) { $__componentOriginalb8c2af2c7c4a456e77f6ae42c74e5e35 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalb8c2af2c7c4a456e77f6ae42c74e5e35 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.icons.user','data' => ['class' => 'w-5 h-5 text-gray-400','ariaHidden' => 'true']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('icons.user'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'w-5 h-5 text-gray-400','aria-hidden' => 'true']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalb8c2af2c7c4a456e77f6ae42c74e5e35)): ?>
<?php $attributes = $__attributesOriginalb8c2af2c7c4a456e77f6ae42c74e5e35; ?>
<?php unset($__attributesOriginalb8c2af2c7c4a456e77f6ae42c74e5e35); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalb8c2af2c7c4a456e77f6ae42c74e5e35)): ?>
<?php $component = $__componentOriginalb8c2af2c7c4a456e77f6ae42c74e5e35; ?>
<?php unset($__componentOriginalb8c2af2c7c4a456e77f6ae42c74e5e35); ?>
<?php endif; ?>
                                     <?php $__env->endSlot(); ?>
                                    <?php if (isset($component)) { $__componentOriginal5c2a97ab476b69c1189ee85d1a95204b = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal5c2a97ab476b69c1189ee85d1a95204b = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.form.input','data' => ['id' => 'puestos_disponibles','class' => 'block w-full','type' => 'number','name' => 'puestos_disponibles','value' => old('puestos_disponibles'),'placeholder' => ''.e(__('Puestos Disponibles')).'','min' => '1','step' => '1']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('form.input'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['id' => 'puestos_disponibles','class' => 'block w-full','type' => 'number','name' => 'puestos_disponibles','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(old('puestos_disponibles')),'placeholder' => ''.e(__('Puestos Disponibles')).'','min' => '1','step' => '1']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal5c2a97ab476b69c1189ee85d1a95204b)): ?>
<?php $attributes = $__attributesOriginal5c2a97ab476b69c1189ee85d1a95204b; ?>
<?php unset($__attributesOriginal5c2a97ab476b69c1189ee85d1a95204b); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal5c2a97ab476b69c1189ee85d1a95204b)): ?>
<?php $component = $__componentOriginal5c2a97ab476b69c1189ee85d1a95204b; ?>
<?php unset($__componentOriginal5c2a97ab476b69c1189ee85d1a95204b); ?>
<?php endif; ?>
                                 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginald599939fa78ef9d91bfef4309fb3a81c)): ?>
<?php $attributes = $__attributesOriginald599939fa78ef9d91bfef4309fb3a81c; ?>
<?php unset($__attributesOriginald599939fa78ef9d91bfef4309fb3a81c); ?>
<?php endif; ?>
<?php if (isset($__componentOriginald599939fa78ef9d91bfef4309fb3a81c)): ?>
<?php $component = $__componentOriginald599939fa78ef9d91bfef4309fb3a81c; ?>
<?php unset($__componentOriginald599939fa78ef9d91bfef4309fb3a81c); ?>
<?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Botón de acción -->
                    <div class="flex justify-end pt-6 border-t border-gray-200">
                        <?php if (isset($component)) { $__componentOriginald0f1fd2689e4bb7060122a5b91fe8561 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginald0f1fd2689e4bb7060122a5b91fe8561 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.button','data' => ['class' => 'gap-2 bg-red-600 hover:bg-red-700 focus:ring-red-500']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'gap-2 bg-red-600 hover:bg-red-700 focus:ring-red-500']); ?>
                            <?php if (isset($component)) { $__componentOriginal167d49e5be30319451e9d24e17d8a630 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal167d49e5be30319451e9d24e17d8a630 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.icons.add','data' => ['class' => 'w-5 h-5','ariaHidden' => 'true']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('icons.add'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'w-5 h-5','aria-hidden' => 'true']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal167d49e5be30319451e9d24e17d8a630)): ?>
<?php $attributes = $__attributesOriginal167d49e5be30319451e9d24e17d8a630; ?>
<?php unset($__attributesOriginal167d49e5be30319451e9d24e17d8a630); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal167d49e5be30319451e9d24e17d8a630)): ?>
<?php $component = $__componentOriginal167d49e5be30319451e9d24e17d8a630; ?>
<?php unset($__componentOriginal167d49e5be30319451e9d24e17d8a630); ?>
<?php endif; ?>
                            <span><?php echo e(__('Agregar Espacio')); ?></span>
                         <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginald0f1fd2689e4bb7060122a5b91fe8561)): ?>
<?php $attributes = $__attributesOriginald0f1fd2689e4bb7060122a5b91fe8561; ?>
<?php unset($__attributesOriginald0f1fd2689e4bb7060122a5b91fe8561); ?>
<?php endif; ?>
<?php if (isset($__componentOriginald0f1fd2689e4bb7060122a5b91fe8561)): ?>
<?php $component = $__componentOriginald0f1fd2689e4bb7060122a5b91fe8561; ?>
<?php unset($__componentOriginald0f1fd2689e4bb7060122a5b91fe8561); ?>
<?php endif; ?>
                    </div>
                </div>
            </form>
         <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal9f64f32e90b9102968f2bc548315018c)): ?>
<?php $attributes = $__attributesOriginal9f64f32e90b9102968f2bc548315018c; ?>
<?php unset($__attributesOriginal9f64f32e90b9102968f2bc548315018c); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal9f64f32e90b9102968f2bc548315018c)): ?>
<?php $component = $__componentOriginal9f64f32e90b9102968f2bc548315018c; ?>
<?php unset($__componentOriginal9f64f32e90b9102968f2bc548315018c); ?>
<?php endif; ?>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // No cargar pisos automáticamente, se cargarán cuando se seleccione facultad
        });
        function searchTable() {
            var input = document.getElementById("searchInput").value.toLowerCase();
            var table = document.getElementById("spaces-table");
            var rows = table.getElementsByTagName("tr");

            for (var i = 1; i < rows.length; i++) {
                var cells = rows[i].getElementsByTagName("td");
                var run = cells[0].textContent.toLowerCase();
                var name = cells[1].textContent.toLowerCase();
                var email = cells[2].textContent.toLowerCase();

                if (run.includes(input) || name.includes(input) || email.includes(input)) {
                    rows[i].style.display = "";
                } else {
                    rows[i].style.display = "none";
                }
            }
        }

        function loadSedes() {
            const universidadId = document.getElementById('universidad').value;
            const sedeSelect = document.getElementById('sede');
            const facultadSelect = document.getElementById('facultad');
            const pisoSelect = document.getElementById('selectedPiso');

            // Limpiar y deshabilitar selectores dependientes
            sedeSelect.innerHTML = "<option value=''>Seleccione una sede</option>";
            sedeSelect.disabled = true;
            facultadSelect.innerHTML = "<option value=''>Seleccione una facultad</option>";
            facultadSelect.disabled = true;
            pisoSelect.innerHTML = "<option value=''>Seleccione un piso</option>";
            pisoSelect.disabled = true;

            if (!universidadId) return;

            fetch(`/sedes/${universidadId}`)
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Error en la respuesta del servidor');
                    }
                    return response.json();
                })
                .then(data => {
                    sedeSelect.innerHTML = "<option value=''>Seleccione una sede</option>";

                    if (data && data.length > 0) {
                        data.forEach(sede => {
                            const option = document.createElement("option");
                            option.value = sede.id_sede;
                            option.textContent = sede.nombre_sede;
                            sedeSelect.appendChild(option);
                        });
                        sedeSelect.disabled = false;
                    } else {
                        sedeSelect.innerHTML = "<option value=''>No hay sedes disponibles</option>";
                        sedeSelect.disabled = true;
                    }
                })
                .catch(error => {
                    sedeSelect.innerHTML = "<option value=''>Error cargando sedes</option>";
                    sedeSelect.disabled = true;
                });
        }

        function loadFacultades() {
            const sedeId = document.getElementById('sede').value;
            const facultadSelect = document.getElementById('facultad');
            const pisoSelect = document.getElementById('selectedPiso');

            // Limpiar y deshabilitar selectores dependientes
            facultadSelect.innerHTML = "<option value=''>Seleccione una facultad</option>";
            facultadSelect.disabled = true;
            pisoSelect.innerHTML = "<option value=''>Seleccione un piso</option>";
            pisoSelect.disabled = true;

            if (!sedeId) return;

            fetch(`/facultades-por-sede/${sedeId}`)
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Error en la respuesta del servidor');
                    }
                    return response.json();
                })
                .then(data => {
                    facultadSelect.innerHTML = "<option value=''>Seleccione una facultad</option>";

                    if (data && data.length > 0) {
                        data.forEach(facultad => {
                            const option = document.createElement("option");
                            option.value = facultad.id_facultad;
                            option.textContent = facultad.nombre_facultad;
                            facultadSelect.appendChild(option);
                        });
                        facultadSelect.disabled = false;
                    } else {
                        facultadSelect.innerHTML = "<option value=''>No hay facultades disponibles</option>";
                        facultadSelect.disabled = true;
                    }
                })
                .catch(error => {
                    facultadSelect.innerHTML = "<option value=''>Error cargando facultades</option>";
                    facultadSelect.disabled = true;
                });
        }

        function loadPisos() {
            const facultadId = document.getElementById('facultad').value;
            const pisoSelect = document.getElementById('selectedPiso');

            // Limpiar selector de pisos
            pisoSelect.innerHTML = "<option value=''>Seleccione un piso</option>";
            pisoSelect.disabled = true;

            if (!facultadId) return;

            fetch(`/pisos/${facultadId}`)
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Error en la respuesta del servidor');
                    }
                    return response.json();
                })
                .then(data => {
                    pisoSelect.innerHTML = "<option value=''>Seleccione un piso</option>";

                    if (data && data.length > 0) {
                        data.forEach(piso => {
                            const option = document.createElement("option");
                            option.value = piso.id;
                            option.textContent = `Piso ${piso.numero_piso}`;
                            pisoSelect.appendChild(option);
                        });
                        pisoSelect.disabled = false;
                    } else {
                        pisoSelect.innerHTML = "<option value=''>No hay pisos disponibles</option>";
                        pisoSelect.disabled = true;
                    }
                })
                .catch(error => {
                    pisoSelect.innerHTML = "<option value=''>Error cargando pisos</option>";
                    pisoSelect.disabled = true;
                });
        }

        // Validación de puestos disponibles
        document.addEventListener('DOMContentLoaded', function () {
            const puestosInput = document.getElementById('puestos_disponibles');
            if (puestosInput) {
                puestosInput.addEventListener('input', function () {
                    const value = parseInt(this.value);
                    if (value < 1) {
                        this.value = 1;
                    }
                });

                puestosInput.addEventListener('blur', function () {
                    const value = parseInt(this.value);
                    if (value < 1 || isNaN(value)) {
                        this.value = 1;
                    }
                });
            }
        });

        // Recargar datos cuando se abre el modal
        document.addEventListener('livewire:load', function () {
            // Escuchar el evento de apertura del modal
            window.addEventListener('open-modal', function (event) {
                if (event.detail === 'add-espacio') {
                    // Resetear todos los selectores
                    const universidadSelect = document.getElementById('universidad');
                    const sedeSelect = document.getElementById('sede');
                    const facultadSelect = document.getElementById('facultad');
                    const pisoSelect = document.getElementById('selectedPiso');

                    universidadSelect.value = '';
                    sedeSelect.innerHTML = "<option value=''>Seleccione una sede</option>";
                    sedeSelect.disabled = true;
                    facultadSelect.innerHTML = "<option value=''>Seleccione una facultad</option>";
                    facultadSelect.disabled = true;
                    pisoSelect.innerHTML = "<option value=''>Seleccione un piso</option>";
                    pisoSelect.disabled = true;
                }
            });
        });

        // También resetear cuando se hace clic en el botón de agregar
        document.addEventListener('click', function (event) {
            if (event.target.closest('[x-on\\:click*="open-modal"]')) {
                setTimeout(() => {
                    // Resetear todos los selectores
                    const universidadSelect = document.getElementById('universidad');
                    const sedeSelect = document.getElementById('sede');
                    const facultadSelect = document.getElementById('facultad');
                    const pisoSelect = document.getElementById('selectedPiso');

                    universidadSelect.value = '';
                    sedeSelect.innerHTML = "<option value=''>Seleccione una sede</option>";
                    sedeSelect.disabled = true;
                    facultadSelect.innerHTML = "<option value=''>Seleccione una facultad</option>";
                    facultadSelect.disabled = true;
                    pisoSelect.innerHTML = "<option value=''>Seleccione un piso</option>";
                    pisoSelect.disabled = true;
                }, 100);
            }
        });
    </script>

 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal9ac128a9029c0e4701924bd2d73d7f54)): ?>
<?php $attributes = $__attributesOriginal9ac128a9029c0e4701924bd2d73d7f54; ?>
<?php unset($__attributesOriginal9ac128a9029c0e4701924bd2d73d7f54); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal9ac128a9029c0e4701924bd2d73d7f54)): ?>
<?php $component = $__componentOriginal9ac128a9029c0e4701924bd2d73d7f54; ?>
<?php unset($__componentOriginal9ac128a9029c0e4701924bd2d73d7f54); ?>
<?php endif; ?><?php /**PATH C:\Users\conym\OneDrive\Documentos\GitHub\AulaSync\resources\views/layouts/spaces/spaces_index.blade.php ENDPATH**/ ?>