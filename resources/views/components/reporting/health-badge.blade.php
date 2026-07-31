@props([
    'status',
])

<x-filament::badge
    :color="match ($status) {

        \App\Domain\Projects\Enums\ProjectHealthStatus::HEALTHY => 'success',

        \App\Domain\Projects\Enums\ProjectHealthStatus::AT_RISK => 'warning',

        \App\Domain\Projects\Enums\ProjectHealthStatus::CRITICAL => 'danger',

    }"
>

    {{ str($status->value)->replace('_', ' ')->title() }}

</x-filament::badge>