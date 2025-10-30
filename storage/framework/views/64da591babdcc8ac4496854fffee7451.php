<!DOCTYPE html>
<html lang="<?php echo e(str_replace('_', '-', app()->getLocale())); ?>">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">

    <title><?php echo e(config('app.name', 'Laravel')); ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <link rel="preload" href="https://fonts.googleapis.com/css2?family=Inter:wght@200..900&display=swap" as="style"
        onload="this.onload=null;this.rel='stylesheet'">
    <noscript>
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@200..900&display=swap" rel="stylesheet">
    </noscript>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@100;300;400;500;700;900&display=swap"
        rel="stylesheet">
    <?php echo \Livewire\Mechanisms\FrontendAssets\FrontendAssets::styles(); ?>

    <?php echo app('Illuminate\Foundation\Vite')(['resources/css/app.css', 'resources/js/app.js']); ?>
    <script src="https://cdn.jsdelivr.net/npm/livewire-v2.11.0/livewire.js"></script>

</head>

<body>
    <div x-data="mainState" class="font-sans antialiased "  x-cloak>
        <div class="flex flex-col min-h-screen text-black bg-gray-100 dark:bg-dark-eval-0 dark:text-gray-200">
            <?php echo e($slot); ?>

        </div>

        
    </div>
    <?php echo \Livewire\Mechanisms\FrontendAssets\FrontendAssets::scripts(); ?>



</body>

</html><?php /**PATH D:\Dev\AulaSync\resources\views/layouts/guest.blade.php ENDPATH**/ ?>