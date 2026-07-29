{{-- 
|--------------------------------------------------------------------------
| Subscription Plan Preview
|--------------------------------------------------------------------------
|
| Simplified administrator preview.
|
| TODO
| - Replace with production pricing card component.
| - Support accent colors.
| - Render commercial features.
| - Add responsive layouts.
|
--}}

<x-filament::section heading="Preview">

    <div class="space-y-4">

        <div class="text-center">

            <h2 class="text-2xl font-bold">
                {{ $metadata['display_name'] ?? '' }}
            </h2>

            <p class="text-gray-500">
                {{ $metadata['subtitle'] ?? '' }}
            </p>

        </div>

        @if (!empty($metadata['badge']))
            <x-filament::badge>
                {{ $metadata['badge'] }}
            </x-filament::badge>
        @endif

        <div class="text-center">

            <div class="text-4xl font-bold">
                ${{ number_format($plan['price'], 2) }}
            </div>

            <div>
                /{{ $plan['billing_interval'] }}
            </div>

        </div>

        @if (!empty($metadata['description']))
            <p class="text-gray-600 text-center">
                {{ $metadata['description'] }}
            </p>
        @endif

        <x-filament::button disabled>

            {{ $metadata['button_text'] ?? 'Get Started' }}

        </x-filament::button>

        @if (!empty($metadata['marketing_copy']))
            <p class="text-sm text-gray-500 text-center">
                {{ $metadata['marketing_copy'] }}
            </p>
        @endif

    </div>

</x-filament::section>
