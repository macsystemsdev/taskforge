<!DOCTYPE html>
<html
    lang="{{ str_replace('_', '-', app()->getLocale()) }}"
    class="h-full"
>

<head>

    <meta charset="utf-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1"
    >

    <title>
        {{ config('app.name', 'TaskForge') }}
    </title>

    @vite([
        'resources/css/app.css',
        'resources/js/app.js'
    ])

    @livewireStyles

</head>

<body class="min-h-screen bg-zinc-100 dark:bg-zinc-900">

    <div class="flex min-h-screen">

        {{-- Sidebar --}}
        @include('layouts.app.sidebar')

        <div class="flex-1 flex flex-col">

            {{-- Header --}}
            @include('layouts.app.header')

            {{-- Main Content --}}
            <main class="flex-1 p-6 lg:p-10">

                {{ $slot }}

            </main>

        </div>

    </div>

    @livewireScripts

</body>

</html>