<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag; ?>
<?php foreach($attributes->onlyProps(['name', 'show' => false, 'maxWidth' => '2xl', 'title' => null]) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
} ?>
<?php $attributes = $attributes->exceptProps(['name', 'show' => false, 'maxWidth' => '2xl', 'title' => null]); ?>
<?php foreach (array_filter((['name', 'show' => false, 'maxWidth' => '2xl', 'title' => null]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
} ?>
<?php $__defined_vars = get_defined_vars(); ?>
<?php foreach ($attributes as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
} ?>
<?php unset($__defined_vars); ?>

<?php
    $maxWidthClass =
        [
            'sm' => 'sm:max-w-sm',
            'md' => 'sm:max-w-md',
            'lg' => 'sm:max-w-lg',
            'xl' => 'sm:max-w-xl',
            '2xl' => 'sm:max-w-2xl',
        ][$maxWidth] ?? 'sm:max-w-2xl';
?>

<div x-data="modalComponent({ show: <?php echo \Illuminate\Support\Js::from($show)->toHtml() ?>, focusable: <?php echo e($attributes->has('focusable') ? 'true' : 'false'); ?> })"
    x-init="init()" x-show="show" @open-modal.window="handleOpen($event, '<?php echo e($name); ?>')"
    @close-modal.window="handleClose($event, '<?php echo e($name); ?>')" @close.stop="show = false"
    @keydown.escape.window="show = false" @keydown.tab.prevent="navigateFocus($event)"
    class="fixed inset-0 z-[150] px-4 pt-8 overflow-y-auto sm:px-0" style="display: none;">
    <!-- Background overlay -->
    <div x-show="show" class="fixed inset-0 transition-opacity bg-gray-500 opacity-75 dark:bg-gray-900"
        @click="show = false" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200"
        x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"></div>

    <!-- Modal -->
    <div x-show="show"
        class="mb-6 bg-white dark:bg-gray-800 rounded-lg overflow-hidden shadow-xl transform transition-all sm:w-full <?php echo e($maxWidthClass); ?> sm:mx-auto"
        x-transition:enter="ease-out duration-300"
        x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
        x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200"
        x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
        x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95">

        <div class="p-2 text-lg font-semibold text-center text-white bg-red-700 dark:bg-dark-eval-1">
            <?php echo e($title ?? ($header ?? '')); ?>

        </div>

        <!-- Contenido del modal -->
        <div class="p-4">
            <?php echo e($slot); ?>

        </div>
    </div>


</div>

<script>
    function modalComponent({
        show = false,
        focusable = false
    }) {
        return {
            show,
            init() {
                this.$watch('show', value => {
                    document.body.classList.toggle('overflow-y-hidden', value);
                    if (value && focusable) {
                        setTimeout(() => this.firstFocusable()?.focus(), 100);
                    }
                });
            },
            handleOpen(event, name) {
                if (event.detail === name) this.show = true;
            },
            handleClose(event, name) {
                if (event.detail === name) this.show = false;
            },
            focusables() {
                return [...this.$el.querySelectorAll(
                    'a, button, input:not([type="hidden"]), textarea, select, details, [tabindex]:not([tabindex="-1"])'
                )].filter(el => !el.disabled);
            },
            firstFocusable() {
                return this.focusables()[0];
            },
            lastFocusable() {
                return this.focusables().at(-1);
            },
            navigateFocus(event) {
                const focusables = this.focusables();
                const index = focusables.indexOf(document.activeElement);
                const direction = event.shiftKey ? -1 : 1;
                const next = (index + direction + focusables.length) % focusables.length;
                focusables[next]?.focus();
            },
        };
    }
</script>
<?php /**PATH D:\Dev\AulaSync\resources\views/components/modal.blade.php ENDPATH**/ ?>