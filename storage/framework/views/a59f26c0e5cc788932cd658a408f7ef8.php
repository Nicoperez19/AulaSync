<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag; ?>
<?php foreach($attributes->onlyProps([
    'title' => '',
    'isActive' => false,
]) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
} ?>
<?php $attributes = $attributes->exceptProps([
    'title' => '',
    'isActive' => false,
]); ?>
<?php foreach (array_filter(([
    'title' => '',
    'isActive' => false,
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
} ?>
<?php $__defined_vars = get_defined_vars(); ?>
<?php foreach ($attributes as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
} ?>
<?php unset($__defined_vars); ?>

<?php
    $baseClasses = 'transition-colors whitespace-normal text-[90%] pl-[2.2rem] py-[0.5rem] px-4';
    $hoverClasses = 'hover:bg-white hover:text-gray-900 dark:hover:text-white';
    $activeClasses = 'text-black bg-white rounded-md';
    $inactiveClasses = 'text-white';
    $classes = "{$baseClasses} " . ($isActive ? $activeClasses : "{$inactiveClasses} {$hoverClasses}");
?>


<li class="my-[1rem]">
    <a <?php echo e($attributes->merge(['class' => $classes])); ?>>
        <?php echo e($title); ?>

    </a>
</li>
<?php /**PATH D:\Dev\AulaSync\resources\views/components/sidebar/sublink.blade.php ENDPATH**/ ?>