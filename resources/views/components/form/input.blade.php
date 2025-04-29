@props([
    'disabled' => false,
    'withicon' => false,
    'icon' => null, // Añadir soporte para icono
    'wire' => null, // Añadir soporte para Livewire
])

@php
    $withiconClasses = $withicon ? 'pl-11 pr-4' : 'px-4';
@endphp

<div class="relative">
    @if($withicon && $icon)
        <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
            @if($icon === 'search')
                <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                </svg>
            @endif
        </div>
    @endif
    
    <input 
        {{ $disabled ? 'disabled' : '' }} 
        {!! $attributes->merge([
            'class' => $withiconClasses . ' py-2 border-gray-400 rounded-md focus:border-gray-400 focus:ring focus:ring-purple-500 focus:ring-offset-2 focus:ring-offset-white dark:border-gray-600 dark:bg-dark-eval-1 dark:text-gray-300 dark:focus:ring-offset-dark-eval-1',
            'wire:model.live' => $wire
        ]) !!}
    >
</div>