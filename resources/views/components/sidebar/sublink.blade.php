@props([
    'title' => '',
    'isActive' => false,
])

@php
    $baseClasses = 'transition-colors whitespace-normal text-[90%] pl-[2.2rem] py-[0.5rem] px-4';
    $hoverClasses = 'hover:bg-white hover:text-gray-900 dark:hover:text-white';
    $activeClasses = 'text-black bg-white rounded-md';
    $inactiveClasses = 'text-white';
    $classes = "{$baseClasses} " . ($isActive ? $activeClasses : "{$inactiveClasses} {$hoverClasses}");
@endphp


<li class="my-[1rem]">
    <a {{ $attributes->merge(['class' => $classes]) }}>
        {{ $title }}
    </a>
</li>
