<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-white antialiased dark:bg-linear-to-b dark:from-neutral-950 dark:to-neutral-900">
        <div class="bg-background flex min-h-svh flex-col items-center justify-center gap-6 p-6 md:p-10">
            <div class="flex w-full max-w-sm flex-col gap-4">
                <a href="{{ route('home') }}" class="flex flex-col items-center gap-2 font-semibold text-zinc-950 dark:text-white" wire:navigate>
                    <span class="flex h-12 w-12 items-center justify-center rounded-2xl bg-zinc-950 text-white dark:bg-white dark:text-black">
                        <x-app-logo-icon class="size-6 fill-current" />
                    </span>
                    <span class="text-2xl">{{ config('app.name', 'TaskForge') }}</span>
                </a>
                <p class="text-center text-sm text-zinc-500 dark:text-zinc-400">Organize teams, tasks, and projects with clarity.</p>
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
