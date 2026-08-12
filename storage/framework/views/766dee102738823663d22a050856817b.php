<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<meta name="description" content="TaskForge helps your team manage projects, tasks, and workflows in one centralized application." />

<title>
    <?php echo e(filled($title ?? null) ? $title.' - '.config('app.name', 'TaskForge') : config('app.name', 'TaskForge')); ?>

</title>

<link rel="icon" href="/favicon.ico" sizes="any">
<link rel="icon" href="/favicon.svg" type="image/svg+xml">
<link rel="apple-touch-icon" href="/apple-touch-icon.png">

<?php echo app('Illuminate\Foundation\Vite')->fonts(); ?>

<?php echo app('Illuminate\Foundation\Vite')(['resources/css/app.css', 'resources/js/app.js']); ?>
<?php echo app('flux')->fluxAppearance(); ?>

<?php /**PATH /var/www/html/resources/views/partials/head.blade.php ENDPATH**/ ?>