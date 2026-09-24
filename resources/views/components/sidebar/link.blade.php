@props([
    'isActive' => false,
    'title' => '',
    'collapsible' => false
])

@php
    $isActiveClasses = $isActive
        ? 'text-gray-900 bg-white font-semibold shadow-md'
        : 'text-white/90 hover:text-white hover:bg-white/15 dark:hover:text-white dark:hover:bg-white/10';

    $classes = 'flex-shrink-0 flex items-center gap-3 px-3 py-2.5 transition-colors duration-150 rounded-lg overflow-hidden ' . $isActiveClasses;

    if($collapsible) $classes .= ' w-full text-left';
@endphp

@if ($collapsible)
    <button type="button" {{ $attributes->merge(['class' => $classes]) }}>
        @if ($icon ?? false)
            <div class="flex-shrink-0 w-6 h-6 flex items-center justify-center">
                {{ $icon }}
            </div>
        @else
            <x-icons.empty-circle class="flex-shrink-0 w-6 h-6" aria-hidden="true" />
        @endif

        <span class="flex-1 text-sm font-medium truncate">
            {{ $title }}
        </span>

        <span aria-hidden="true" class="flex-shrink-0 ml-auto transition-transform duration-200 ease-out" :class="{ 'rotate-180': open }">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7" />
            </svg>
        </span>
    </button>
@else
    <a {{ $attributes->merge(['class' => $classes]) }}>
        @if ($icon ?? false)
            <div class="flex-shrink-0 w-6 h-6 flex items-center justify-center">
                {{ $icon }}
            </div>
        @else
            <x-icons.empty-circle class="flex-shrink-0 w-6 h-6" aria-hidden="true" />
        @endif

        <span class="flex-1 text-sm font-medium truncate">
            {{ $title }}
        </span>
    </a>
@endif
