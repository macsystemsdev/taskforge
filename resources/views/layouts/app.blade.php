<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">

<head>
    @include('partials.head')
</head>

<body class="min-h-screen bg-[radial-gradient(circle_at_top_left,_rgba(99,102,241,0.08),_transparent_35%),linear-gradient(180deg,_#f8fafc_0%,_#f3f4f6_100%)] text-zinc-900 antialiased dark:bg-[radial-gradient(circle_at_top_left,_rgba(139,92,246,0.16),_transparent_35%),linear-gradient(180deg,_#09090b_0%,_#111827_100%)] dark:text-zinc-100">

    <div class="flex min-h-screen">

        {{-- Sidebar --}}
        @include('layouts.app.sidebar')

        <div class="flex flex-1 flex-col">

            {{-- Header --}}
            @include('layouts.app.header')

            {{-- Main Content --}}
            <main class="flex-1 px-4 py-4 sm:px-6 sm:py-6 lg:px-8 lg:py-8">

                {{ $slot }}

            </main>

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
