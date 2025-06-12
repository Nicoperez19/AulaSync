@props(['name', 'show' => false, 'maxWidth' => '2xl', 'title' => null])

@php
    $maxWidthClass =
        [
            'sm' => 'sm:max-w-sm',
            'md' => 'sm:max-w-md',
            'lg' => 'sm:max-w-lg',
            'xl' => 'sm:max-w-xl',
            '2xl' => 'sm:max-w-2xl',
        ][$maxWidth] ?? 'sm:max-w-2xl';
@endphp

<div x-data="modalComponent({ show: @js($show), focusable: {{ $attributes->has('focusable') ? 'true' : 'false' }} })" x-init="init()" x-show="show"
    @open-modal.window="handleOpen($event, '{{ $name }}')" @close.stop="show = false"
    @keydown.escape.window="show = false" @keydown.tab.prevent="navigateFocus($event)"
    class="fixed inset-0 z-50 px-4 pt-32 overflow-y-auto sm:px-0" style="display: none;">
    <!-- Background overlay -->
    <div x-show="show" class="fixed inset-0 transition-opacity bg-gray-500 opacity-75 dark:bg-gray-900"
        @click="show = false" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200"
        x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"></div>

    <!-- Modal -->
    <div x-show="show"
        class="mb-6 bg-white dark:bg-gray-800 rounded-lg overflow-hidden shadow-xl transform transition-all sm:w-full {{ $maxWidthClass }} sm:mx-auto"
        x-transition:enter="ease-out duration-300"
        x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
        x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200"
        x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
        x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95">

        <div class="p-2 text-lg font-semibold text-center text-white bg-light-cloud-blue dark:bg-dark-eval-1">
            {{ $title ?? ($header ?? '') }}
        </div>

        <!-- Contenido del modal -->
        <div class="p-4">
            {{ $slot }}
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
