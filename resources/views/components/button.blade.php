@props([
    'variant' => 'primary',
    'iconOnly' => false,
    'srText' => '',
    'href' => false,
    'size' => 'base',
    'disabled' => false,
    'pill' => false,
    'squared' => false,
])
@php

    $baseClasses = 'inline-flex items-center justify-center font-medium transition-all duration-150 select-none shadow-sm disabled:opacity-50 
                disabled:cursor-not-allowed focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-white 
                dark:focus:ring-offset-dark-eval-2';

    switch ($variant) {
        case 'primary':
            $variantClasses = 'border border-transparent bg-blue-600 text-white hover:bg-blue-700 active:bg-blue-800 focus:ring-blue-500';
            break;
        case 'login':
            $variantClasses = 'border border-transparent bg-blue-600 text-white hover:bg-blue-700 active:bg-blue-800 focus:ring-blue-500';
            break;
        case 'secondary':
        case 'outline':
            $variantClasses = 'border border-gray-300 bg-white text-gray-700 hover:bg-gray-50 active:bg-gray-100 focus:ring-gray-300 dark:bg-dark-eval-1 dark:text-gray-200 dark:border-gray-600 dark:hover:bg-dark-eval-2';
            break;
        case 'success':
        case 'add':
            $variantClasses = 'border border-transparent bg-emerald-600 text-white hover:bg-emerald-700 active:bg-emerald-800 focus:ring-emerald-500';
            break;
        case 'danger':
            $variantClasses = 'border border-transparent bg-red-600 text-white hover:bg-red-700 active:bg-red-800 focus:ring-red-500';
            break;
        case 'warning':
            $variantClasses = 'border border-transparent bg-amber-600 text-white hover:bg-amber-700 active:bg-amber-800 focus:ring-amber-500';
            break;
        case 'info':
            $variantClasses = 'border border-transparent bg-sky-600 text-white hover:bg-sky-700 active:bg-sky-800 focus:ring-sky-500';
            break;
        case 'black':
            $variantClasses = 'border border-transparent bg-gray-900 text-white hover:bg-black focus:ring-gray-900';
            break;
        case 'view':
            $variantClasses = 'border border-transparent bg-blue-600 text-white hover:bg-blue-700 active:bg-blue-800 focus:ring-blue-500';
            break;
        default:
            $variantClasses = 'border border-gray-300 bg-white text-gray-700 hover:bg-gray-50 focus:ring-gray-300';
    }

    switch ($size) {
        case 'sm':
            $sizeClasses = $iconOnly ? 'p-1.5 text-xs' : 'px-3 py-1.5 text-xs gap-1.5';
            break;
        case 'base':
            $sizeClasses = $iconOnly ? 'p-2 text-sm' : 'px-4 py-2 text-sm gap-2';
            break;
        case 'lg':
        default:
            $sizeClasses = $iconOnly ? 'p-2.5 text-base' : 'px-5 py-2.5 text-base gap-2.5';
            break;
    }

    $classes = $baseClasses . ' ' . $sizeClasses . ' ' . $variantClasses;

    if (!$squared && !$pill) {
        $classes .= ' rounded-md';
    } elseif ($pill) {
        $classes .= ' rounded-full';
    }

@endphp
@if ($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>
        {{ $slot }}
        @if ($iconOnly)
            <span class="sr-only">{{ $srText ?? '' }}</span>
        @endif
    </a>
@else
    <button {{ $attributes->merge(['type' => 'submit', 'class' => $classes]) }}>
        {{ $slot }}
        @if ($iconOnly)
            <span class="sr-only">{{ $srText ?? '' }}</span>
        @endif
    </button>
@endif
