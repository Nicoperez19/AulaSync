@props([
    'title' => '',
    'isActive' => false,
])

@php
<<<<<<< HEAD
    $classes = 'transition-colors hover:text-light-cloud-blue dark:hover:text-white whitespace-normal';

    if ($isActive) {
        $classes .= ' text-black bg-white rounded-md py-2 px-2';
    } else {
        $classes .= ' text-white py-2 px-2';    }
@endphp

<li
    class="relative leading-8 m-0 pl-6 last:before:bg-white last:before:h-auto last:before:top-4 last:before:bottom-0
    dark:last:before:bg-white before:block before:w-4 before:h-0 before:absolute before:left-0 before:top-4
    before:border-t-2 before:border-t-white before:-mt-0.5 dark:before:border-t-white font-size:10px">
=======
    $baseClasses = 'transition-colors whitespace-normal text-[90%] pl-[2.2rem] py-[0.5rem] px-4';
    $hoverClasses = 'hover:text-gray-200 dark:hover:text-white';
    $activeClasses = 'text-black bg-white rounded-md';
    $inactiveClasses = 'text-white';
    $classes = "{$baseClasses} {$hoverClasses} " . ($isActive ? $activeClasses : $inactiveClasses);
@endphp

<li class="my-[1rem]">
>>>>>>> Nperez
    <a {{ $attributes->merge(['class' => $classes]) }}>
        {{ $title }}
    </a>
</li>
