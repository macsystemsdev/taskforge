<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head', ['title' => $title ?? config('app.name', 'TaskForge')])
    </head>
    <body class="min-h-screen bg-[radial-gradient(circle_at_top_left,_rgba(99,102,241,0.16),_transparent_35%),linear-gradient(180deg,_#f8fafc_0%,_#f3f4f6_100%)] antialiased dark:bg-[radial-gradient(circle_at_top_left,_rgba(139,92,246,0.16),_transparent_35%),linear-gradient(180deg,_#09090b_0%,_#111827_100%)]">
        <div class="flex min-h-svh flex-col items-center justify-center gap-6 p-4 sm:p-6 md:p-10">
            <div class="flex w-full max-w-md flex-col gap-6 rounded-3xl border border-zinc-200 bg-white/80 p-6 shadow-xl shadow-zinc-950/5 backdrop-blur sm:p-8 dark:border-white/10 dark:bg-zinc-900/70">
                {{-- App Icon --}}
                <a href="{{ route('home') }}" class="flex flex-col items-center gap-3" wire:navigate>
                    <img src="/apple-touch-icon.png" alt="{{ config('app.name', 'TaskForge') }}" class="size-16 rounded-2xl shadow-lg" />
                    <span class="text-xl font-semibold text-zinc-950 dark:text-white">{{ config('app.name', 'TaskForge') }}</span>
                    <p class="text-sm text-zinc-500 dark:text-zinc-400">Organize teams, tasks, and projects with clarity.</p>
                </a>

                {{-- Divider --}}
                <div class="h-px w-full bg-zinc-200 dark:bg-white/10"></div>

                {{-- Content --}}
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
