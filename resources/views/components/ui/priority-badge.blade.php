@props([
    'priority',
])

@php
    $value = $priority instanceof \BackedEnum ? $priority->value : (string) $priority;
    $label = str($value)->headline();

    $dots = [
        'low' => 'bg-zinc-400',
        'medium' => 'bg-zinc-700 dark:bg-zinc-300',
        'high' => 'bg-orange-500',
        'urgent' => 'bg-red-500',
    ];
@endphp

<span {{ $attributes->merge(['class' => 'inline-flex items-center gap-2 rounded-full border border-zinc-200 bg-white px-2.5 py-1 text-xs font-medium text-zinc-700 dark:border-white/10 dark:bg-white/5 dark:text-zinc-300']) }}>
    <span class="size-1.5 rounded-full {{ $dots[$value] ?? $dots['medium'] }}"></span>
    {{ $label }}
</span>
