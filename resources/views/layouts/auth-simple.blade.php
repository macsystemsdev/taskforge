<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head', ['title' => $title ?? config('app.name', 'TaskForge')])
    </head>
    <body class="min-h-screen bg-[radial-gradient(circle_at_top_left,_rgba(99,102,241,0.16),_transparent_35%),linear-gradient(180deg,_#f8fafc_0%,_#f3f4f6_100%)] antialiased dark:bg-[radial-gradient(circle_at_top_left,_rgba(139,92,246,0.16),_transparent_35%),linear-gradient(180deg,_#09090b_0%,_#111827_100%)]">
        <div class="flex min-h-svh flex-col items-center justify-center gap-6 p-4 sm:p-6 md:p-10">
            <div class="flex w-full max-w-md flex-col gap-4 rounded-3xl border border-zinc-200 bg-white/80 p-6 shadow-xl shadow-zinc-950/5 backdrop-blur sm:p-8 dark:border-white/10 dark:bg-zinc-900/70">
                <a href="{{ route('home') }}" class="flex flex-col items-center gap-2 font-semibold text-zinc-950 dark:text-white" wire:navigate>
                    <span class="flex h-12 w-12 items-center justify-center rounded-2xl bg-zinc-950 text-white dark:bg-white dark:text-black">
                        <x-app-logo-icon class="size-6 fill-current" />
                    </span>
                    <span class="text-2xl">{{ config('app.name', 'TaskForge') }}</span>
                </a>
                <p class="text-center text-sm text-zinc-500 dark:text-zinc-400">Organize teams, tasks, and projects with clarity.</p>
                <div class="flex flex-col gap-6">
                    @yield('content')
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
