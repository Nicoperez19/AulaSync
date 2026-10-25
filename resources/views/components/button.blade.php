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
        // Acciones Positivas / Éxito (Verde Institucional #10B981)
        case 'success':
        case 'save':
        case 'add':
        case 'create':
        case 'confirm':
        case 'accept':
        case 'apply':
        case 'continue':
        case 'next':
        case 'submit':
        case 'publish':
            $variantClasses = 'border border-transparent bg-[#10B981] text-white hover:bg-[#059669] active:bg-[#047857] focus:ring-[#10B981] shadow-sm hover:shadow active:scale-[0.98]';
            break;

        // Acciones Primarias / Institucionales de Marca (Rojo Institucional #D12420)
        case 'primary':
        case 'brand':
        case 'ucsc':
        case 'login':
        case 'register':
        case 'process':
        case 'execute':
            $variantClasses = 'border border-transparent bg-[#D12420] text-white hover:bg-[#950101] active:bg-[#7B0101] focus:ring-[#D12420] shadow-sm hover:shadow active:scale-[0.98]';
            break;

        // Acciones Destructivas / Peligro (Rojo Alerta #D12420)
        case 'danger':
        case 'delete':
        case 'remove':
        case 'logout':
            $variantClasses = 'border border-transparent bg-[#D12420] text-white hover:bg-[#950101] active:bg-[#7B0101] focus:ring-[#D12420] shadow-sm hover:shadow active:scale-[0.98]';
            break;

        // Exportaciones Específicas
        case 'excel':
            $variantClasses = 'border border-transparent bg-[#107C41] text-white hover:bg-[#0E6B37] active:bg-[#0B562C] focus:ring-[#107C41] shadow-sm hover:shadow active:scale-[0.98]';
            break;
        case 'pdf':
            $variantClasses = 'border border-transparent bg-[#D12420] text-white hover:bg-[#950101] active:bg-[#7B0101] focus:ring-[#D12420] shadow-sm hover:shadow active:scale-[0.98]';
            break;

        // Utilidades Oscuras / Filtrado / Búsqueda / Refrescar
        case 'dark':
        case 'black':
        case 'filter':
        case 'search':
        case 'refresh':
        case 'edit':
            $variantClasses = 'border border-transparent bg-[#191919] text-white hover:bg-[#950101] active:bg-black focus:ring-[#191919] shadow-sm hover:shadow active:scale-[0.98] dark:bg-gray-800 dark:hover:bg-[#950101]';
            break;

        // Acciones Secundarias Sólidas (Gris Neutro #8D8D8D)
        case 'secondary':
        case 'cancel':
            $variantClasses = 'border border-transparent bg-[#8D8D8D] text-white hover:bg-[#737373] active:bg-[#5A5A5A] focus:ring-[#8D8D8D] shadow-sm hover:shadow active:scale-[0.98]';
            break;

        // Acciones Outline / Volver / Cerrar / Limpiar / Deshacer / Copiar
        case 'outline':
        case 'back':
        case 'close':
        case 'clear':
        case 'reset':
        case 'undo':
        case 'redo':
        case 'copy':
        case 'duplicate':
            $variantClasses = 'border border-[#C0C0C0] bg-white text-[#191919] hover:bg-[#F1EFF2] hover:border-[#8D8D8D] active:bg-gray-200 focus:ring-gray-300 dark:bg-dark-eval-1 dark:text-gray-200 dark:border-gray-600 dark:hover:bg-dark-eval-2 shadow-sm active:scale-[0.98]';
            break;

        case 'warning':
            $variantClasses = 'border border-transparent bg-amber-600 text-white hover:bg-amber-700 active:bg-amber-800 focus:ring-amber-500 shadow-sm';
            break;
        case 'info':
        case 'view':
            $variantClasses = 'border border-transparent bg-sky-600 text-white hover:bg-sky-700 active:bg-sky-800 focus:ring-sky-500 shadow-sm';
            break;
        default:
            $variantClasses = 'border border-[#C0C0C0] bg-white text-[#191919] hover:bg-gray-50 focus:ring-gray-300';
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
        $classes .= ' rounded-lg';
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
