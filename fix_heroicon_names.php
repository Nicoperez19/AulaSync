<?php

// Script para actualizar nombres de iconos de Heroicons v2.x a v1.5
$replacements = [
    'heroicon-s-x-mark' => 'heroicon-s-x',
    'heroicon-o-x-mark' => 'heroicon-o-x',
    'heroicon-s-calendar-days' => 'heroicon-s-calendar',
    'heroicon-o-calendar-days' => 'heroicon-o-calendar',
    'heroicon-s-building-office' => 'heroicon-s-office-building',
    'heroicon-o-building-office' => 'heroicon-o-office-building',
    'heroicon-s-cog-6-tooth' => 'heroicon-s-cog',
    'heroicon-o-cog-6-tooth' => 'heroicon-o-cog',
    'heroicon-s-arrow-right-start-on-rectangle' => 'heroicon-s-logout',
    'heroicon-o-arrow-right-start-on-rectangle' => 'heroicon-o-logout',
    'heroicon-s-magnifying-glass' => 'heroicon-s-search',
    'heroicon-o-magnifying-glass' => 'heroicon-o-search',
    'heroicon-s-wrench-screwdriver' => 'heroicon-s-cog',
    'heroicon-o-wrench-screwdriver' => 'heroicon-o-cog',
    'heroicon-s-exclamation-triangle' => 'heroicon-s-exclamation',
    'heroicon-o-exclamation-triangle' => 'heroicon-o-exclamation',
    'heroicon-s-x-circle' => 'heroicon-s-x-circle',
    'heroicon-o-x-circle' => 'heroicon-o-x-circle',
    'heroicon-s-folder-open' => 'heroicon-s-folder-open',
    'heroicon-o-folder-open' => 'heroicon-o-folder-open',
    'heroicon-s-lock-closed' => 'heroicon-s-lock-closed',
    'heroicon-o-lock-closed' => 'heroicon-o-lock-closed',
    'heroicon-s-user-add' => 'heroicon-s-user-add',
    'heroicon-o-user-add' => 'heroicon-o-user-add',
    'heroicon-s-location-marker' => 'heroicon-s-location-marker',
    'heroicon-o-location-marker' => 'heroicon-o-location-marker',
    'heroicon-s-plus-circle' => 'heroicon-s-plus-circle',
    'heroicon-o-plus-circle' => 'heroicon-o-plus-circle',
];

// Buscar todos los archivos blade
$directory = new RecursiveDirectoryIterator('d:/Dev/AulaSync/resources/views');
$iterator = new RecursiveIteratorIterator($directory);
$files = new RegexIterator($iterator, '/^.+\.blade\.php$/i', RecursiveRegexIterator::GET_MATCH);

$totalReplacements = 0;

foreach ($files as $file) {
    $filePath = $file[0];
    $content = file_get_contents($filePath);
    $originalContent = $content;
    
    foreach ($replacements as $old => $new) {
        $content = str_replace('x-' . $old, 'x-' . $new, $content);
    }
    
    if ($content !== $originalContent) {
        file_put_contents($filePath, $content);
        echo "Actualizado: " . str_replace('d:/Dev/AulaSync/resources/views/', '', $filePath) . "\n";
        $totalReplacements++;
    }
}

echo "\nTotal de archivos actualizados: $totalReplacements\n";
echo "Actualización de nombres de iconos completada!\n";
