<x-sidebar.overlay />

<aside
<<<<<<< HEAD
<<<<<<< HEAD
=======
>>>>>>> 6c05e560f5edb88b89bd0fe7d8d71ecb8386c841
    class="fixed inset-y-0 left-0 z-10 mt-4 shadow-md bg-light-cloud-blue dark:bg-dark-eval-1 shadow-gray-500/30"
    :class="{
        'translate-x-0 w-64': isSidebarOpen || isSidebarHovered,
        'w-16': !isSidebarOpen && !isSidebarHovered
    }"
    style="transition-property: width, transform; transition-duration: 150ms; top: 2rem; height: calc(100vh - 2rem);" 
<<<<<<< HEAD
=======
    class="fixed inset-y-0 left-0 z-[90] mt-4 shadow-md bg-light-cloud-blue dark:bg-dark-eval-1 shadow-gray-500/30 transform transition-all duration-300 ease-in-out"
    :class="{
        'translate-x-0 opacity-100': isSidebarOpen || isSidebarHovered,
        '-translate-x-full opacity-0': !isSidebarOpen && !isSidebarHovered
    }"
    style="top: 2rem; height: calc(100vh - 2rem);" 
>>>>>>> Nperez
=======
>>>>>>> 6c05e560f5edb88b89bd0fe7d8d71ecb8386c841
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

