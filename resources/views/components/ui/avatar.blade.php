@props([
    'name' => null,
    'initials' => null,
    'size' => 'md',
    'user' => null,
    'clickable' => false,
])

@php
    $displayInitials = $initials ?: collect(explode(' ', trim((string) $name)))
        ->filter()
        ->take(2)
        ->map(fn ($part) => mb_substr($part, 0, 1))
        ->implode('');

    $avatarPath = $user?->avatar_path ?? null;

    $sizes = [
        'sm' => 'size-8 text-xs',
        'md' => 'size-10 text-sm',
        'lg' => 'size-12 text-sm',
    ];
@endphp

@if ($avatarPath && $user && $user->id)
    @if ($clickable)
        <button type="button"
            wire:click="$dispatch('open-avatar-modal', { avatarPath: '{{ $avatarPath }}', name: '{{ addslashes($name) }}', email: '{{ $user?->email }}', userId: '{{ $user->id }}' })"
            class="cursor-pointer transition hover:opacity-80">
            <img src="{{ route('users.avatar', ['user' => $user->id, 'v' => $user->updated_at?->timestamp ?? $user->id]) }}"
                 alt="{{ $name }}"
                 class="shrink-0 rounded-full {{ $sizes[$size] ?? $sizes['md'] }} aspect-square object-cover" />
        </button>
    @else
        <img src="{{ route('users.avatar', ['user' => $user->id, 'v' => $user->updated_at?->timestamp ?? $user->id]) }}"
             alt="{{ $name }}"
             class="shrink-0 rounded-full {{ $sizes[$size] ?? $sizes['md'] }} aspect-square object-cover" />
    @endif
@else
    <span class="shrink-0 rounded-full {{ $sizes[$size] ?? $sizes['md'] }} inline-flex items-center justify-center bg-gradient-to-br from-blue-500 to-indigo-600 font-semibold text-white">
        {{ strtoupper($displayInitials ?: '?') }}
    </span>
@endif
