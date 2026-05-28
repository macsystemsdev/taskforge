@props([
    'name' => null,
    'initials' => null,
    'size' => 'md',
])

@php
    $displayInitials = $initials ?: collect(explode(' ', trim((string) $name)))
        ->filter()
        ->take(2)
        ->map(fn ($part) => mb_substr($part, 0, 1))
        ->implode('');

    $sizes = [
        'sm' => 'size-8 text-xs',
        'md' => 'size-9 text-sm',
        'lg' => 'size-10 text-sm',
    ];
@endphp

<span {{ $attributes->merge(['class' => ($sizes[$size] ?? $sizes['md']).' inline-flex shrink-0 items-center justify-center rounded-full border border-zinc-200 bg-zinc-100 font-semibold text-zinc-700 dark:border-white/10 dark:bg-white/10 dark:text-zinc-200']) }}>
    {{ strtoupper($displayInitials ?: '?') }}
</span>
