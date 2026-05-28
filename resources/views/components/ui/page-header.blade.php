@props([
    'title',
    'description' => null,
    'eyebrow' => null,
])

<div {{ $attributes->merge(['class' => 'mb-6 flex flex-col gap-4 border-b border-zinc-200 pb-5 dark:border-white/10 sm:flex-row sm:items-end sm:justify-between']) }}>
    <div class="min-w-0">
        @if ($eyebrow)
            <p class="mb-2 text-xs font-semibold uppercase tracking-[0.16em] text-zinc-500 dark:text-zinc-400">
                {{ $eyebrow }}
            </p>
        @endif

        <h1 class="truncate text-2xl font-semibold tracking-tight text-zinc-950 dark:text-white">
            {{ $title }}
        </h1>

        @if ($description)
            <p class="mt-2 max-w-3xl text-sm leading-6 text-zinc-600 dark:text-zinc-400">
                {{ $description }}
            </p>
        @endif
    </div>

    @isset($actions)
        <div class="flex shrink-0 items-center gap-2">
            {{ $actions }}
        </div>
    @endisset
</div>
