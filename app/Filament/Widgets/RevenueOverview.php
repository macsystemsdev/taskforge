<?php

namespace App\Filament\Widgets;

use App\Services\Owner\DTO\MetricData;
use App\Services\Owner\RevenueMetricsCacheService;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class RevenueOverview extends StatsOverviewWidget
{
    protected int|string|array $columnSpan = 'full';
    protected ?string $heading = 'Revenue Overview';
    protected ?string $pollingInterval = '30s';

    protected function getStats(): array
    {
        return collect(
            app(
                RevenueMetricsCacheService::class
            )->overviewMetrics()
        )
            ->map(
                fn(MetricData $metric) =>

                Stat::make(
                    $metric->label,
                    $metric->value,
                )
                    ->description($metric->description)
                    ->icon($metric->icon)
                    ->color($metric->color)

            )
            ->toArray();
    }
}
