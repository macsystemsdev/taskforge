@props([
    'title',
    'description' => null,
])

<div {{ $attributes->merge(['class' => 'rounded-lg border border-dashed border-zinc-300 bg-zinc-50/70 px-6 py-10 text-center dark:border-white/15 dark:bg-white/[0.03]']) }}>
    <div class="mx-auto mb-4 flex size-10 items-center justify-center rounded-lg border border-zinc-200 bg-white text-zinc-500 dark:border-white/10 dark:bg-zinc-900 dark:text-zinc-400">
        <flux:icon name="inbox" class="size-5" />
    </div>

    <h3 class="text-sm font-semibold text-zinc-950 dark:text-white">
        {{ $title }}
    </h3>

    @if ($description)
        <p class="mx-auto mt-2 max-w-md text-sm leading-6 text-zinc-600 dark:text-zinc-400">
            {{ $description }}
        </p>
    @endif

    @isset($actions)
        <div class="mt-5 flex justify-center">
            {{ $actions }}
        </div>
    @endisset
</div>
