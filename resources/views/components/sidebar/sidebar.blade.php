<x-sidebar.overlay />

<aside
    class="fixed inset-y-0 left-0 z-[90] mt-4 shadow-md bg-light-cloud-blue dark:bg-dark-eval-1 shadow-gray-500/30 transform transition-transform duration-300"
    :class="{
        'translate-x-0': isSidebarOpen || isSidebarHovered,
        '-translate-x-full': !isSidebarOpen && !isSidebarHovered
    }"
    style="top: 2rem; height: calc(100vh - 2rem);" 
    x-on:mouseenter="handleSidebarHover(true)"
    x-on:mouseleave="handleSidebarHover(false)"
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

