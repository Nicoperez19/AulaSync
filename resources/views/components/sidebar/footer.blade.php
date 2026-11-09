<div class="px-3 flex-shrink-0 lg:hidden">
    <button
        type="button"
        aria-label="Toggle sidebar"
        title="Alternar menú lateral"
        x-show="!isSidebarOpen"
        x-on:click="isSidebarOpen = !isSidebarOpen"
        class="inline-flex items-center justify-center p-2 text-white hover:bg-white/10 active:bg-white/20 rounded-md focus:outline-none transition-colors"
    >
        <x-icons.menu-fold-left
            x-show="isSidebarOpen"
            class="w-6 h-6"
        />

        <x-icons.menu-fold-right
            x-show="!isSidebarOpen"
            class="w-6 h-6"
        />
    </button>
</div>
