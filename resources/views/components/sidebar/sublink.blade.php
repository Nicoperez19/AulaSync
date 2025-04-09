@props([
    'title' => '',
    'active' => false,
])

@php
    $classes = 'transition-colors hover:text-white dark:hover:text-white whitespace-normal';
    $active ? ($classes .= ' text-white') : ($classes .= ' text-white');
@endphp

<li
    class="relative leading-8 m-0 pl-6 last:before:bg-white last:before:h-auto last:before:top-4 last:before:bottom-0
    dark:last:before:bg-white before:block before:w-4 before:h-0 before:absolute before:left-0 before:top-4
    before:border-t-2 before:border-t-white before:-mt-0.5 dark:before:border-t-white font-size:10px">
    <a {{ $attributes->merge(['class' => $classes]) }}>
        {{ $title }}
    </a>
</li>