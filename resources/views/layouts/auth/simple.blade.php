<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    @include('partials.head', ['title' => $title ?? config('app.name', 'TaskForge')])
</head>
<body class="min-h-screen bg-zinc-50 antialiased dark:bg-zinc-950">
    <div class="flex min-h-svh flex-col items-center justify-center p-4 sm:p-6 md:p-10">
        <a href="{{ route('home') }}" class="mb-6 inline-flex items-center gap-2 text-sm text-zinc-500 hover:text-zinc-800 dark:text-zinc-400 dark:hover:text-zinc-200 transition" wire:navigate>
            <span>←</span> Back to home
        </a>

        <div class="flex w-full max-w-md flex-col gap-6 rounded-2xl border border-zinc-200 bg-white p-6 shadow-sm sm:p-8 dark:border-white/10 dark:bg-zinc-900">
            <a href="{{ route('home') }}" class="flex flex-col items-center gap-3" wire:navigate>
                <img src="/favicon.svg" alt="{{ config('app.name', 'TaskForge') }}" class="size-12" />
                <span class="text-lg font-semibold text-zinc-950 dark:text-white">{{ config('app.name', 'TaskForge') }}</span>
            </a>

            <div class="h-px w-full bg-zinc-100 dark:bg-white/10"></div>

            <div class="flex flex-col gap-6">
                {{ $slot }}
            </div>
        </div>
    </div>

    @persist('toast')
        <flux:toast.group>
            <flux:toast />
        </flux:toast.group>
    @endpersist

    @fluxScripts
</body>
</html>
