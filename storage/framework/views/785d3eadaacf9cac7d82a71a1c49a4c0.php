<!DOCTYPE html>
<html lang="<?php echo e(str_replace('_', '-', app()->getLocale())); ?>">
    <head>
        <?php echo $__env->make('partials.head', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    </head>
    <body class="bg-[#F8FAFC] text-[#111827] flex min-h-screen items-center justify-center p-6 lg:p-10">
        <div class="mx-auto flex w-full max-w-7xl flex-col gap-6 lg:flex-row lg:items-center lg:justify-between">
            <div class="max-w-2xl space-y-8 rounded-[2rem] border border-zinc-200 bg-white/90 p-10 shadow-[0_40px_120px_-50px_rgba(15,23,42,0.2)] backdrop-blur dark:border-zinc-800 dark:bg-zinc-950/85 dark:text-zinc-100">
                <div class="flex items-center gap-3">
                    <span class="inline-flex h-11 w-11 items-center justify-center rounded-2xl bg-zinc-950 text-white shadow-sm">
                        <?php if (isset($component)) { $__componentOriginal159d6670770cb479b1921cea6416c26c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal159d6670770cb479b1921cea6416c26c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.app-logo-icon','data' => ['class' => 'size-6 fill-current']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('app-logo-icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'size-6 fill-current']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal159d6670770cb479b1921cea6416c26c)): ?>
<?php $attributes = $__attributesOriginal159d6670770cb479b1921cea6416c26c; ?>
<?php unset($__attributesOriginal159d6670770cb479b1921cea6416c26c); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal159d6670770cb479b1921cea6416c26c)): ?>
<?php $component = $__componentOriginal159d6670770cb479b1921cea6416c26c; ?>
<?php unset($__componentOriginal159d6670770cb479b1921cea6416c26c); ?>
<?php endif; ?>
                    </span>
                    <div>
                        <p class="text-sm uppercase tracking-[0.32em] text-zinc-500 dark:text-zinc-400">TaskForge</p>
                        <p class="mt-2 text-sm font-semibold text-zinc-900 dark:text-white">Team task management for modern delivery.</p>
                    </div>
                </div>

                <div class="space-y-6">
                    <h1 class="text-4xl font-semibold tracking-tight text-zinc-950 dark:text-white sm:text-5xl">Plan, assign, and deliver work with one workflow hub.</h1>
                    <p class="max-w-xl text-base leading-7 text-zinc-600 dark:text-zinc-400">TaskForge gives your team a single place to manage projects, coordinate assignments, and stay aligned on progress from kickoff to completion.</p>
                </div>

                <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
                    <a href="<?php echo e(route('register')); ?>" class="inline-flex items-center justify-center rounded-full bg-zinc-950 px-6 py-3 text-sm font-semibold text-white transition hover:bg-zinc-800" wire:navigate>
                        Create account
                    </a>
                    <a href="<?php echo e(route('login')); ?>" class="inline-flex items-center justify-center rounded-full border border-zinc-200 bg-white px-6 py-3 text-sm font-semibold text-zinc-950 transition hover:border-zinc-300 dark:border-zinc-700 dark:bg-zinc-950 dark:text-white dark:hover:border-zinc-500" wire:navigate>
                        Log in
                    </a>
                </div>

                <div class="grid gap-3 sm:grid-cols-3">
                    <div class="rounded-3xl border border-zinc-200 bg-zinc-50 p-5 dark:border-zinc-800 dark:bg-zinc-900">
                        <p class="text-sm font-semibold text-zinc-950 dark:text-white">Project workspaces</p>
                        <p class="mt-2 text-sm text-zinc-600 dark:text-zinc-400">Group work by project and keep teams focused on outcomes.</p>
                    </div>
                    <div class="rounded-3xl border border-zinc-200 bg-zinc-50 p-5 dark:border-zinc-800 dark:bg-zinc-900">
                        <p class="text-sm font-semibold text-zinc-950 dark:text-white">Clear task assignments</p>
                        <p class="mt-2 text-sm text-zinc-600 dark:text-zinc-400">Assign work, set due dates, and review progress in one place.</p>
                    </div>
                    <div class="rounded-3xl border border-zinc-200 bg-zinc-50 p-5 dark:border-zinc-800 dark:bg-zinc-900">
                        <p class="text-sm font-semibold text-zinc-950 dark:text-white">Actionable notifications</p>
                        <p class="mt-2 text-sm text-zinc-600 dark:text-zinc-400">Stay on top of updates with clear alerts and task status signals.</p>
                    </div>
                </div>
            </div>

            <div class="relative overflow-hidden rounded-[2rem] bg-gradient-to-br from-[#111827] via-[#27272a] to-[#0f172a] p-10 shadow-[0_40px_120px_-50px_rgba(15,23,42,0.35)] lg:w-[38rem]">
                <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_right,_rgba(245,158,11,0.15),_transparent_35%)] pointer-events-none"></div>
                <div class="relative flex h-full flex-col justify-between gap-8 text-white">
                    <div class="space-y-4">
                        <div class="inline-flex items-center gap-3 rounded-3xl bg-white/10 px-4 py-3 text-sm font-medium text-white/90 backdrop-blur-sm">
                            <?php if (isset($component)) { $__componentOriginal159d6670770cb479b1921cea6416c26c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal159d6670770cb479b1921cea6416c26c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.app-logo-icon','data' => ['class' => 'size-5 fill-current text-white']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('app-logo-icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'size-5 fill-current text-white']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal159d6670770cb479b1921cea6416c26c)): ?>
<?php $attributes = $__attributesOriginal159d6670770cb479b1921cea6416c26c; ?>
<?php unset($__attributesOriginal159d6670770cb479b1921cea6416c26c); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal159d6670770cb479b1921cea6416c26c)): ?>
<?php $component = $__componentOriginal159d6670770cb479b1921cea6416c26c; ?>
<?php unset($__componentOriginal159d6670770cb479b1921cea6416c26c); ?>
<?php endif; ?>
                            <span>Built for teams who need focus and flow.</span>
                        </div>
                        <div class="rounded-[2rem] border border-white/10 bg-white/5 p-8 shadow-[0_25px_80px_-40px_rgba(255,255,255,0.2)]">
                            <p class="text-sm uppercase tracking-[0.32em] text-orange-300">Task board preview</p>
                            <h2 class="mt-4 text-3xl font-semibold">Organize every sprint, delivery, and update.</h2>
                            <p class="mt-3 max-w-xl text-sm text-white/70">TaskForge keeps work visible so your team can move faster without losing the details.</p>
                        </div>
                    </div>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div class="rounded-3xl border border-white/10 bg-white/5 p-5">
                            <p class="text-sm font-semibold text-white">Shared team context</p>
                            <p class="mt-2 text-sm text-white/60">Workspaces, teams, and assignments all connected.</p>
                        </div>
                        <div class="rounded-3xl border border-white/10 bg-white/5 p-5">
                            <p class="text-sm font-semibold text-white">Fast task handoff</p>
                            <p class="mt-2 text-sm text-white/60">Know what to do next with clear status and due dates.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </body>
</html>
<?php /**PATH D:\Code\taskforge\resources\views/welcome.blade.php ENDPATH**/ ?>