<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag; ?>
<?php foreach($attributes->onlyProps([
    'active' => false,
    'title' => ''
]) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
} ?>
<?php $attributes = $attributes->exceptProps([
    'active' => false,
    'title' => ''
]); ?>
<?php foreach (array_filter(([
    'active' => false,
    'title' => ''
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
} ?>
<?php $__defined_vars = get_defined_vars(); ?>
<?php foreach ($attributes as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
} ?>
<?php unset($__defined_vars); ?>

<div class="relative" x-data="{ open: <?php echo json_encode($active, 15, 512) ?> }">
   <?php if (isset($component)) { $__componentOriginal1e62ea70552e2303ad88fc0b4cc5a488 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal1e62ea70552e2303ad88fc0b4cc5a488 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.sidebar.link','data' => ['collapsible' => true,'isActive' => $active,'title' => ''.e($title).'','xOn:click' => 'open = !open','class' => 'transition hover:bg-white hover:text-black','xBind:class' => '{ \' bg-white text-black shadow-lg\': open }']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('sidebar.link'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['collapsible' => true,'isActive' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($active),'title' => ''.e($title).'','x-on:click' => 'open = !open','class' => 'transition hover:bg-white hover:text-black','x-bind:class' => '{ \' bg-white text-black shadow-lg\': open }']); ?>

        <?php if($icon ?? false): ?>
             <?php $__env->slot('icon', null, []); ?> 
                <?php echo e($icon); ?>

             <?php $__env->endSlot(); ?>
        <?php endif; ?>
     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal1e62ea70552e2303ad88fc0b4cc5a488)): ?>
<?php $attributes = $__attributesOriginal1e62ea70552e2303ad88fc0b4cc5a488; ?>
<?php unset($__attributesOriginal1e62ea70552e2303ad88fc0b4cc5a488); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal1e62ea70552e2303ad88fc0b4cc5a488)): ?>
<?php $component = $__componentOriginal1e62ea70552e2303ad88fc0b4cc5a488; ?>
<?php unset($__componentOriginal1e62ea70552e2303ad88fc0b4cc5a488); ?>
<?php endif; ?>

    <div
        x-show="open && (isSidebarOpen || isSidebarHovered)"
        x-collapse
    >
        <ul>
            <?php echo e($slot); ?>

        </ul>
    </div>
</div>
<?php /**PATH D:\Dev\AulaSync\resources\views/components/sidebar/dropdown.blade.php ENDPATH**/ ?>