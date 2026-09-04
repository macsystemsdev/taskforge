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
    protected ?string $pollingInterval = '2m';
    protected static ?string $slug = 'metrics/revenue-overview';

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
                    ->descriptionIcon(
                        $metric->trend === 'up' 
                            ? 'heroicon-m-arrow-trending-up' 
                            : ($metric->trend === 'down' 
                                ? 'heroicon-m-arrow-trending-down' 
                                : null)
                    )
                    ->descriptionColor(
                        $metric->trend === 'up' 
                            ? 'success' 
                            : ($metric->trend === 'down' 
                                ? 'danger' 
                                : null)
                    )
                    ->icon($metric->icon)
                    ->color($metric->color)

            )
            ->toArray();
    }
}
