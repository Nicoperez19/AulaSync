<?php if (isset($component)) { $__componentOriginal49f213dd03064ea9fc6a2ba054f2b8a2 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal49f213dd03064ea9fc6a2ba054f2b8a2 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.sidebar.overlay','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('sidebar.overlay'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal49f213dd03064ea9fc6a2ba054f2b8a2)): ?>
<?php $attributes = $__attributesOriginal49f213dd03064ea9fc6a2ba054f2b8a2; ?>
<?php unset($__attributesOriginal49f213dd03064ea9fc6a2ba054f2b8a2); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal49f213dd03064ea9fc6a2ba054f2b8a2)): ?>
<?php $component = $__componentOriginal49f213dd03064ea9fc6a2ba054f2b8a2; ?>
<?php unset($__componentOriginal49f213dd03064ea9fc6a2ba054f2b8a2); ?>
<?php endif; ?>

<aside
    class="fixed inset-y-0 left-0 z-[90] mt-4 shadow-md bg-light-cloud-blue dark:bg-dark-eval-1 shadow-gray-500/30 transform transition-all duration-300 ease-in-out"
    :class="{
        'translate-x-0 opacity-100': isSidebarOpen || isSidebarHovered,
        '-translate-x-full opacity-0': !isSidebarOpen && !isSidebarHovered
    }"
    style="top: 2rem; height: calc(100vh - 2rem);" 
    x-on:mouseenter="handleSidebarHover(true)"
    x-on:mouseleave="handleSidebarHover(false)"
>
    <div class="flex flex-col h-full">
        <nav class="flex-1 overflow-y-auto custom-scrollbar">
            <div class="px-3 py-4">
                <?php if (isset($component)) { $__componentOriginale743b875282f4494cf3ddd8b43cf9412 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginale743b875282f4494cf3ddd8b43cf9412 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.sidebar.content','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('sidebar.content'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginale743b875282f4494cf3ddd8b43cf9412)): ?>
<?php $attributes = $__attributesOriginale743b875282f4494cf3ddd8b43cf9412; ?>
<?php unset($__attributesOriginale743b875282f4494cf3ddd8b43cf9412); ?>
<?php endif; ?>
<?php if (isset($__componentOriginale743b875282f4494cf3ddd8b43cf9412)): ?>
<?php $component = $__componentOriginale743b875282f4494cf3ddd8b43cf9412; ?>
<?php unset($__componentOriginale743b875282f4494cf3ddd8b43cf9412); ?>
<?php endif; ?>
            </div>
        </nav>
        <div class="flex-shrink-0">
            <?php if (isset($component)) { $__componentOriginal890b81d1fccff888f155ef507bc9e6b7 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal890b81d1fccff888f155ef507bc9e6b7 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.sidebar.footer','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('sidebar.footer'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal890b81d1fccff888f155ef507bc9e6b7)): ?>
<?php $attributes = $__attributesOriginal890b81d1fccff888f155ef507bc9e6b7; ?>
<?php unset($__attributesOriginal890b81d1fccff888f155ef507bc9e6b7); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal890b81d1fccff888f155ef507bc9e6b7)): ?>
<?php $component = $__componentOriginal890b81d1fccff888f155ef507bc9e6b7; ?>
<?php unset($__componentOriginal890b81d1fccff888f155ef507bc9e6b7); ?>
<?php endif; ?>
        </div>
    </div>
</aside>

<?php /**PATH D:\Dev\AulaSync\resources\views/components/sidebar/sidebar.blade.php ENDPATH**/ ?>