@props([
    'title' => '',
    'isActive' => false,
])

@php
    $baseClasses = 'flex items-center w-full pl-9 pr-3 py-2 text-sm rounded-lg transition-colors duration-150';
    $activeClasses = 'text-gray-900 bg-white font-semibold shadow-sm';
    $inactiveClasses = 'text-white/80 hover:text-white hover:bg-white/15 dark:hover:bg-white/10';
    $classes = "{$baseClasses} " . ($isActive ? $activeClasses : $inactiveClasses);
@endphp

<li>
    <a {{ $attributes->merge(['class' => $classes]) }}>
        {{ $title }}
    </a>
</li>
