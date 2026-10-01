<div
    x-cloak
    x-show="isSidebarOpen"
    x-transition:enter="transition-opacity ease-out duration-300"
    x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100"
    x-transition:leave="transition-opacity ease-in duration-200"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0"
    x-on:click="isSidebarOpen = false"
    class="fixed inset-0 z-[80] bg-black/30 backdrop-blur-[2px]"
></div>
