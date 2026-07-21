<?php

namespace App\Filament\Widgets;

use App\Services\Owner\DashboardMetricsCacheService;
use App\Services\Owner\DTO\MetricData;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class OverviewStatsWidget extends StatsOverviewWidget
{
    protected int|string|array $columnSpan = 'full';
    
    protected function getStats(): array
    {
        $metrics =
            app(
                DashboardMetricsCacheService::class
            )->overview();

        return collect($metrics)
            ->map(
                fn(MetricData $metric) =>

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
            ->toArray();
    }
}
