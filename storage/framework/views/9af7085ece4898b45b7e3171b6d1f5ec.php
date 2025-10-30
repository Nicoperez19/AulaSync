<!DOCTYPE html>
<html lang="<?php echo e(str_replace('_', '-', app()->getLocale())); ?>">

<head class="h-full bg-gray-100">
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">

    <title><?php echo e(config('app.name', 'AulaSync')); ?></title>

    <!-- Estilos de Livewire -->
    <?php echo \Livewire\Mechanisms\FrontendAssets\FrontendAssets::styles(); ?>


    <!-- Fuentes -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@100;300;400;500;700;900&display=swap"
        rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Vite -->
    <?php echo app('Illuminate\Foundation\Vite')(['resources/css/app.css', 'resources/js/app.js']); ?>

    <!-- Estilos adicionales -->
    <style>
        [x-cloak] {
            display: none !important;
        }
        
    </style>
</head>

<!-- VISTA PARA TABLA DE ESPACIOS -->

<body class="font-sans antialiased bg-gray">
    <div x-data="mainState" x-on:resize.window="handleWindowResize" x-cloak>
        <div class="min-h-screen text-gray-900 bg-white">
            <header>
                <div class="py-2 sm:px-6 bg-gray-200 border-b border-gray-300">
                    <?php echo e($header); ?>

                </div>
            </header>
            <div class="flex flex-col transition-all duration-300 ease-in-out bg-white">
                <!-- Main content -->
                <main class="flex-1 px-4 overflow-x-auto transition-all duration-300 ease-in-out sm:px-6 bg-gray">
                    <?php echo e($slot); ?>

                </main>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <?php echo \Livewire\Mechanisms\FrontendAssets\FrontendAssets::scripts(); ?>


    <!-- SweetAlert Component -->
    <?php if (isset($component)) { $__componentOriginal9c8e73815168efeea949b2b9f7bded26 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal9c8e73815168efeea949b2b9f7bded26 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.sweet-alert','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('sweet-alert'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal9c8e73815168efeea949b2b9f7bded26)): ?>
<?php $attributes = $__attributesOriginal9c8e73815168efeea949b2b9f7bded26; ?>
<?php unset($__attributesOriginal9c8e73815168efeea949b2b9f7bded26); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal9c8e73815168efeea949b2b9f7bded26)): ?>
<?php $component = $__componentOriginal9c8e73815168efeea949b2b9f7bded26; ?>
<?php unset($__componentOriginal9c8e73815168efeea949b2b9f7bded26); ?>
<?php endif; ?>

    <!-- Alpine.js se carga a través de Vite en app.js -->
</body>

</html>
<?php /**PATH D:\Dev\AulaSync\resources\views/layouts/table-layout.blade.php ENDPATH**/ ?>