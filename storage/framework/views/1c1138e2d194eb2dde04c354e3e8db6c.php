<div
    x-transition:enter="transition ease-out duration-300"
    x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100"
    x-transition:leave="transition ease-out duration-300"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0"
    x-show="isSidebarOpen"
    x-on:click="isSidebarOpen = false"
    class="fixed inset-0 z-[80] bg-white/30 backdrop-blur-sm transition-all duration-300 ease-in-out"
></div>
<?php /**PATH C:\Users\conym\OneDrive\Documentos\GitHub\AulaSync\resources\views/components/sidebar/overlay.blade.php ENDPATH**/ ?>