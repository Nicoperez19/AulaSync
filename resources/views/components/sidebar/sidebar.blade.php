<x-sidebar.overlay />

<aside
    class="fixed inset-y-0 left-0 z-10 flex flex-col mt-4 space-y-6 shadow-md bg-light-cloud-blue dark:bg-dark-eval-1 shadow-gray-500/30"
    :class="{
        'translate-x-0 w-64': isSidebarOpen || isSidebarHovered,
        'w-16': !isSidebarOpen && !isSidebarHovered
    }"
    style="transition-property: width, transform; transition-duration: 150ms; top: 2rem; height: calc(100vh - 2rem);" 
    x-on:mouseenter="handleSidebarHover(true)"
    x-on:mouseleave="handleSidebarHover(false)"
>
    <div class="flex-1 overflow-y-auto">
        <x-sidebar.content />
    </div>
    <div class="flex-shrink-0">
        <x-sidebar.footer />
    </div>
</aside>

