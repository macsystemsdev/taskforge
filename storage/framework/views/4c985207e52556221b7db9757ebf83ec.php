<!DOCTYPE html>
<html
    lang="<?php echo e(str_replace('_', '-', app()->getLocale())); ?>"
    class="h-full"
>

<head>

    <meta charset="utf-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1"
    >

    <title>
        <?php echo e(config('app.name', 'TaskForge')); ?>

    </title>

    <?php echo app('Illuminate\Foundation\Vite')([
        'resources/css/app.css',
        'resources/js/app.js'
    ]); ?>

    <?php echo \Livewire\Mechanisms\FrontendAssets\FrontendAssets::styles(); ?>

    <?php echo app('flux')->fluxAppearance(); ?>


</head>

<body class="min-h-screen bg-zinc-100 dark:bg-zinc-900">

    <div class="flex min-h-screen">

        
        <?php echo $__env->make('layouts.app.sidebar', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

        <div class="flex-1 flex flex-col">

            
            <?php echo $__env->make('layouts.app.header', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

            
            <main class="flex-1 p-6 lg:p-10">

                <?php echo e($slot); ?>


            </main>

        </div>

    </div>

    <?php echo \Livewire\Mechanisms\FrontendAssets\FrontendAssets::scripts(); ?>

    <?php app('livewire')->forceAssetInjection(); ?>
<?php echo app('flux')->scripts(); ?>


</body>

</html>
<?php /**PATH D:\Code\taskforge\resources\views\layouts\app.blade.php ENDPATH**/ ?>