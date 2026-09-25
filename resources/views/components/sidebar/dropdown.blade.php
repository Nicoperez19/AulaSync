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
        x-bind:class="{ 'bg-white/20 text-white font-semibold': open && !@json($active) }"
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
