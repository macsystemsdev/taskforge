@props([
    'size' => '7xl',
])

@php
    $maxWidth = [
        '3xl' => 'max-w-3xl',
        '4xl' => 'max-w-4xl',
        '5xl' => 'max-w-5xl',
        '6xl' => 'max-w-6xl',
        '7xl' => 'max-w-7xl',
    ][$size] ?? 'max-w-7xl';
@endphp

<div {{ $attributes->merge(['class' => "mx-auto w-full {$maxWidth} px-4 py-6 sm:px-6 lg:px-8 lg:py-8"]) }}>
    {{ $slot }}
</div>
