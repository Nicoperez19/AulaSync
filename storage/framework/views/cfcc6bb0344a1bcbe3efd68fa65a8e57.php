<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag; ?>
<?php foreach($attributes->onlyProps([
    'isActive' => false,
    'title' => '',
    'collapsible' => false
]) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
} ?>
<?php $attributes = $attributes->exceptProps([
    'isActive' => false,
    'title' => '',
    'collapsible' => false
]); ?>
<?php foreach (array_filter(([
    'isActive' => false,
    'title' => '',
    'collapsible' => false
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
} ?>
<?php $__defined_vars = get_defined_vars(); ?>
<?php foreach ($attributes as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
} ?>
<?php unset($__defined_vars); ?>

<?php
    $isActiveClasses =  $isActive ? 'text-black bg-white shadow-lg hover:bg-white' : 'text-black-500 hover:text-gray-700 hover:bg-gray-100 dark:hover:text-gray-300 dark:hover:bg-dark-eval-2';

    $classes = 'flex-shrink-0 flex items-center gap-2 p-2 transition-colors rounded-md overflow-hidden ' . $isActiveClasses;

    if($collapsible) $classes .= ' w-full';
?>

<?php if($collapsible): ?>
    <button type="button" <?php echo e($attributes->merge(['class' => $classes])); ?> >
        <?php if($icon ?? false): ?>
            <?php echo e($icon); ?>

        <?php else: ?>
            <?php if (isset($component)) { $__componentOriginal46edf475024f0befb3cd44ca68eeb4fd = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal46edf475024f0befb3cd44ca68eeb4fd = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.icons.empty-circle','data' => ['class' => 'flex-shrink-0 w-6 h-6','ariaHidden' => 'true']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('icons.empty-circle'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'flex-shrink-0 w-6 h-6','aria-hidden' => 'true']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal46edf475024f0befb3cd44ca68eeb4fd)): ?>
<?php $attributes = $__attributesOriginal46edf475024f0befb3cd44ca68eeb4fd; ?>
<?php unset($__attributesOriginal46edf475024f0befb3cd44ca68eeb4fd); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal46edf475024f0befb3cd44ca68eeb4fd)): ?>
<?php $component = $__componentOriginal46edf475024f0befb3cd44ca68eeb4fd; ?>
<?php unset($__componentOriginal46edf475024f0befb3cd44ca68eeb4fd); ?>
<?php endif; ?>
        <?php endif; ?>

        <span
            class="text-base font-medium whitespace-nowrap"
            x-show="isSidebarOpen || isSidebarHovered"
        >
            <?php echo e($title); ?>

        </span>

        <span
            x-show="isSidebarOpen || isSidebarHovered"
            aria-hidden="true"
            class="relative block w-6 h-6 ml-auto"
        >
            <span
                :class="open ? '-rotate-45' : 'rotate-45'"
                class="absolute right-[9px] bg-white mt-[-5px] h-2 w-[2px] top-1/2 transition-all duration-200"
            ></span>

            <span
                :class="open ? 'rotate-45' : '-rotate-45'"
                class="absolute left-[9px] bg-white mt-[-5px] h-2 w-[2px] top-1/2 transition-all duration-200"
            ></span>
        </span>
    </button>
<?php else: ?>
    <a <?php echo e($attributes->merge(['class' => $classes])); ?>>
        <?php if($icon ?? false): ?>
            <?php echo e($icon); ?>

        <?php else: ?>
            <?php if (isset($component)) { $__componentOriginal46edf475024f0befb3cd44ca68eeb4fd = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal46edf475024f0befb3cd44ca68eeb4fd = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.icons.empty-circle','data' => ['class' => 'flex-shrink-0 w-6 h-6','ariaHidden' => 'true']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('icons.empty-circle'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'flex-shrink-0 w-6 h-6','aria-hidden' => 'true']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal46edf475024f0befb3cd44ca68eeb4fd)): ?>
<?php $attributes = $__attributesOriginal46edf475024f0befb3cd44ca68eeb4fd; ?>
<?php unset($__attributesOriginal46edf475024f0befb3cd44ca68eeb4fd); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal46edf475024f0befb3cd44ca68eeb4fd)): ?>
<?php $component = $__componentOriginal46edf475024f0befb3cd44ca68eeb4fd; ?>
<?php unset($__componentOriginal46edf475024f0befb3cd44ca68eeb4fd); ?>
<?php endif; ?>
        <?php endif; ?>

        <span
            class="text-base font-medium"
            x-show="isSidebarOpen || isSidebarHovered"
        >
            <?php echo e($title); ?>

        </span>
    </a>
<?php endif; ?>
<?php /**PATH C:\Users\conym\OneDrive\Documentos\GitHub\AulaSync\resources\views/components/sidebar/link.blade.php ENDPATH**/ ?>