@props([
    'active' => false,
    'title' => ''
])

<div class="relative" x-data="{ open: {{ $active ? 'true' : 'false' }}, isActive: {{ $active ? 'true' : 'false' }} }">
    <x-sidebar.link
        collapsible
        :isActive="$active"
        title="{{ $title }}"
        x-on:click="open = !open"
        x-bind:class="{ 'bg-white/20 text-white font-semibold': open && !isActive }"
    >
        @if ($icon ?? false)
            <x-slot name="icon">
                {{ $icon }}
            </x-slot>
        @endif
    </x-sidebar.link>

    <div
        x-show="open"
        x-collapse
        class="mt-1"
    >
        <ul class="space-y-1">
            {{ $slot }}
        </ul>
    </div>
</div>
