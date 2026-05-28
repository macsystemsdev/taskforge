@props([
    'padding' => 'p-5 sm:p-6',
])

<section {{ $attributes->merge(['class' => "rounded-lg border border-zinc-200 bg-white {$padding} shadow-sm dark:border-white/10 dark:bg-zinc-900/70"]) }}>
    {{ $slot }}
</section>
