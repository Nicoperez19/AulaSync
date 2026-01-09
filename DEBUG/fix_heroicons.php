<?php

$replacements = [
    // Reemplazos para index.blade.php
    '<x-heroicon-s-plus class="w-8 h-8 text-green-600" />' => '<i class="fa-solid fa-plus text-3xl text-green-600"></i>',
    '<x-heroicon-s-plus class="w-4 h-4 mr-2" />' => '<i class="fa-solid fa-plus w-4 h-4 mr-2"></i>',
    '<x-heroicon-s-pencil class="w-4 h-4 mr-2" />' => '<i class="fa-solid fa-pencil w-4 h-4 mr-2"></i>',
    '<x-heroicon-s-building-office class="w-8 h-8 text-purple-600" />' => '<i class="fa-solid fa-building text-3xl text-purple-600"></i>',
    '<x-heroicon-s-cog-6-tooth class="w-4 h-4 mr-2" />' => '<i class="fa-solid fa-gear w-4 h-4 mr-2"></i>',
    '<x-heroicon-s-trash class="w-4 h-4 mr-2" />' => '<i class="fa-solid fa-trash w-4 h-4 mr-2"></i>',
    '<x-heroicon-s-arrow-path class="w-4 h-4 mr-2" />' => '<i class="fa-solid fa-rotate-right w-4 h-4 mr-2"></i>',
    '<x-heroicon-s-calendar-days class="w-8 h-8 text-blue-600" />' => '<i class="fa-solid fa-calendar text-3xl text-blue-600"></i>',
    '<x-heroicon-s-building-office class="w-8 h-8 text-green-600" />' => '<i class="fa-solid fa-building text-3xl text-green-600"></i>',
    '<x-heroicon-s-lock-closed class="w-8 h-8 text-red-600" />' => '<i class="fa-solid fa-lock text-3xl text-red-600"></i>',
    '<x-heroicon-s-clock class="w-8 h-8 text-yellow-600" />' => '<i class="fa-solid fa-clock text-3xl text-yellow-600"></i>',
    
    // Para crear-reserva.blade.php
    '<x-heroicon-s-magnifying-glass class="w-4 h-4 mr-2" />' => '<i class="fa-solid fa-search w-4 h-4 mr-2"></i>',
    '<x-heroicon-s-plus class="w-4 h-4 mr-2" />' => '<i class="fa-solid fa-plus w-4 h-4 mr-2"></i>',
    '<x-heroicon-s-arrow-left class="w-4 h-4 mr-2" />' => '<i class="fa-solid fa-arrow-left w-4 h-4 mr-2"></i>',
    
    // Para gestionar-reservas.blade.php
    '<x-heroicon-s-arrow-path class="w-4 h-4 mr-2 inline" />' => '<i class="fa-solid fa-rotate-right w-4 h-4 mr-2 inline"></i>',
    '<x-heroicon-s-check-circle class="w-8 h-8 text-green-600" />' => '<i class="fa-solid fa-circle-check text-3xl text-green-600"></i>',
    '<x-heroicon-s-x-circle class="w-8 h-8 text-red-600" />' => '<i class="fa-solid fa-circle-xmark text-3xl text-red-600"></i>',
    '<x-heroicon-s-clock class="w-8 h-8 text-yellow-600" />' => '<i class="fa-solid fa-clock text-3xl text-yellow-600"></i>',
    '<x-heroicon-s-calendar-days class="w-8 h-8 text-blue-600" />' => '<i class="fa-solid fa-calendar text-3xl text-blue-600"></i>',
    
    // Para gestionar-espacios.blade.php
    '<x-heroicon-s-check-circle class="w-8 h-8 text-green-600" />' => '<i class="fa-solid fa-circle-check text-3xl text-green-600"></i>',
    '<x-heroicon-s-lock-closed class="w-8 h-8 text-red-600" />' => '<i class="fa-solid fa-lock text-3xl text-red-600"></i>',
    '<x-heroicon-s-wrench-screwdriver class="w-8 h-8 text-yellow-600" />' => '<i class="fa-solid fa-wrench text-3xl text-yellow-600"></i>',
    '<x-heroicon-s-building-office class="w-8 h-8 text-blue-600" />' => '<i class="fa-solid fa-building text-3xl text-blue-600"></i>',
    '<x-heroicon-s-unlock class="w-3 h-3 mr-1" />' => '<i class="fa-solid fa-unlock w-3 h-3 mr-1"></i>',
    '<x-heroicon-s-wrench-screwdriver class="w-3 h-3 mr-1" />' => '<i class="fa-solid fa-wrench w-3 h-3 mr-1"></i>',
    '<x-heroicon-s-unlock class="w-4 h-4 mr-2" />' => '<i class="fa-solid fa-unlock w-4 h-4 mr-2"></i>',
    '<x-heroicon-s-wrench-screwdriver class="w-4 h-4 mr-2" />' => '<i class="fa-solid fa-wrench w-4 h-4 mr-2"></i>',
];

$files = [
    'd:\Dev\AulaSync\resources\views\layouts\quick_actions\index.blade.php',
    'd:\Dev\AulaSync\resources\views\layouts\quick_actions\crear-reserva.blade.php',
    'd:\Dev\AulaSync\resources\views\layouts\quick_actions\gestionar-reservas.blade.php',
    'd:\Dev\AulaSync\resources\views\layouts\quick_actions\gestionar-espacios.blade.php'
];

foreach ($files as $file) {
    if (file_exists($file)) {
        $content = file_get_contents($file);
        $content = str_replace(array_keys($replacements), array_values($replacements), $content);
        file_put_contents($file, $content);
        echo "Procesado: $file\n";
    }
}

echo "Reemplazos completados!\n";
