<meta charset="utf-8" />

<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
<meta name="description"
    content="TaskForge helps your team manage projects, tasks, and workflows in one centralized application." />

<title>
    <?php echo e(filled($title ?? null) ? $title . ' - ' . config('app.name', 'TaskForge') : config('app.name', 'TaskForge')); ?>

</title>

<link rel="icon" href="/favicon.ico" sizes="any">

<link rel="icon" href="/favicon.svg" type="image/svg+xml">

<link rel="apple-touch-icon" href="/apple-touch-icon.png">

<?php echo app('Illuminate\Foundation\Vite')->fonts(); ?>

<?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(auth()->check()): ?>
    <script>
        window.TaskForge = {
            userId: <?php echo \Illuminate\Support\Js::from(auth()->id())->toHtml() ?>,
            userName: <?php echo \Illuminate\Support\Js::from(auth()->user()?->name)->toHtml() ?>,
        };
    </script>
<?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

<?php echo app('Illuminate\Foundation\Vite')(['resources/css/app.css', 'resources/js/app.js']); ?>

<?php echo app('flux')->fluxAppearance(); ?>

<?php /**PATH /var/www/html/resources/views/partials/head.blade.php ENDPATH**/ ?>