@props([
    'active' => false,
    'title' => ''
])

<div class="relative" x-data="{ open: @json($active) }">
    <x-sidebar.link
        collapsible
        :isActive="$active"
        title="{{ $title }}"
        x-on:click="open = !open"
        x-bind:class="{ 'bg-white text-black shadow-lg': open }"
    >
        @if ($icon ?? false)
            <x-slot name="icon">
                {{ $icon }}
            </x-slot>
        @endif
    </x-sidebar.link>

    <div
        x-show="open && (isSidebarOpen || isSidebarHovered)"
        x-collapse
    >
        <ul
           "
        >
            {{ $slot }}
        </ul>
    </div>
</div>
