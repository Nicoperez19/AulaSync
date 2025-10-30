<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag; ?>
<?php foreach($attributes->onlyProps([
    'disabled' => false,
    'withicon' => false,
    'icon' => null, // Añadir soporte para icono
    'wire' => null, // Añadir soporte para Livewire
]) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
} ?>
<?php $attributes = $attributes->exceptProps([
    'disabled' => false,
    'withicon' => false,
    'icon' => null, // Añadir soporte para icono
    'wire' => null, // Añadir soporte para Livewire
]); ?>
<?php foreach (array_filter(([
    'disabled' => false,
    'withicon' => false,
    'icon' => null, // Añadir soporte para icono
    'wire' => null, // Añadir soporte para Livewire
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
} ?>
<?php $__defined_vars = get_defined_vars(); ?>
<?php foreach ($attributes as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
} ?>
<?php unset($__defined_vars); ?>

<?php
    $withiconClasses = $withicon ? 'pl-4 pr-4' : 'px-4';
?>

<div class="relative">
    <?php if($withicon && $icon): ?>
        <div class="absolute inset-y-0 left-0 flex items-center pointer-events-none">
            <?php if($icon === 'search'): ?>
                <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                </svg>
            <?php endif; ?>
        </div>
    <?php endif; ?>
    
    <input 
        <?php echo e($disabled ? 'disabled' : ''); ?> 
        <?php echo $attributes->merge([
            'class' => $withiconClasses . ' py-2 border-gray-400 rounded-md focus:border-gray-400 focus:ring focus:ring-light-cloud-blue focus:ring-offset-2 focus:ring-offset-white dark:border-gray-600 dark:bg-dark-eval-1 dark:text-gray-300 dark:focus:ring-offset-dark-eval-1',
            'wire:model.live' => $wire
        ]); ?>

    >
</div><?php /**PATH D:\Dev\AulaSync\resources\views/components/form/input.blade.php ENDPATH**/ ?>