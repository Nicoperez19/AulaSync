<x-sidebar.overlay />

<aside
    class="fixed inset-y-0 left-0 z-[90] w-64 sm:w-72 bg-light-cloud-blue dark:bg-dark-eval-1 shadow-[4px_0_24px_rgba(0,0,0,0.18)] transform-gpu transition-transform duration-300 ease-[cubic-bezier(0.16,1,0.3,1)] select-none"
    :class="{
        'translate-x-0': isSidebarOpen || isSidebarHovered,
        '-translate-x-full': !isSidebarOpen && !isSidebarHovered
    }"
    style="top: 3.5rem; height: calc(100vh - 3.5rem); will-change: transform;"
    x-on:mouseenter="handleSidebarHover(true)"
    x-on:mouseleave="handleSidebarHover(false)"
    @keydown.escape.window="isSidebarOpen = false"
>
    <div class="flex flex-col h-full">
        <nav class="flex-1 overflow-y-auto custom-scrollbar">
            <div class="px-3 py-4">
                <x-sidebar.content />
            </div>
        </nav>
        <div class="flex-shrink-0">
            <x-sidebar.footer />
        </div>
    </div>
</aside>
