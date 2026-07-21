<?php

namespace App\Filament\Widgets;

use App\Services\Owner\DTO\MetricData;
use App\Services\Owner\RevenueMetricsCacheService;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class RevenueOverview extends StatsOverviewWidget
{
    protected int|string|array $columnSpan = 'full';
    
    protected function getStats(): array
{
    $metrics =
        app(
            RevenueMetricsCacheService::class
        )->overview();

    return collect($metrics)

        ->map(
            fn (MetricData $metric) =>

            Stat::make(
                $metric->label,
                $metric->value
            )
                ->description(
                    $metric->description
                )
                ->icon(
                    $metric->icon
                )
                ->color(
                    $metric->color
                )
        )

        ->values()

        ->all();
}
}
