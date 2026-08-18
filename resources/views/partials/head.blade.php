<meta charset="utf-8" />

<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<meta name="csrf-token" content="{{ csrf_token() }}">
<meta name="description"
    content="TaskForge helps your team manage projects, tasks, and workflows in one centralized application." />

<title>
    {{ filled($title ?? null) ? $title . ' - ' . config('app.name', 'TaskForge') : config('app.name', 'TaskForge') }}
</title>

<link rel="icon" href="/favicon.ico" sizes="any">

<link rel="icon" href="/favicon.svg" type="image/svg+xml">

<link rel="apple-touch-icon" href="/apple-touch-icon.png">

@fonts

@if (auth()->check())
    <script>
        window.TaskForge = {
            userId: @js(auth()->id()),
            userName: @js(auth()->user()?->name),
        };
    </script>
@endif

@vite(['resources/css/app.css', 'resources/js/app.js'])

@fluxAppearance
