@props([
    'sidebar' => false,
])

@if($sidebar)
    <flux:sidebar.brand name="TaskForge" {{ $attributes }}>
        <x-slot name="logo" class="flex aspect-square size-8 items-center justify-center">
            <img src="/favicon.svg" alt="TaskForge" class="size-6" />
        </x-slot>
    </flux:sidebar.brand>
@else
    <flux:brand name="TaskForge" {{ $attributes }}>
        <x-slot name="logo" class="flex aspect-square size-8 items-center justify-center">
            <img src="/favicon.svg" alt="TaskForge" class="size-6" />
        </x-slot>
    </flux:brand>
@endif
