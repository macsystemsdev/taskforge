<?php

namespace App\Filament\Widgets;

use App\Services\Owner\DashboardMetricsCacheService;
use App\Services\Owner\DTO\MetricData;
use App\Services\Owner\Organization\OrganizationCacheService;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class OverviewStatsWidget extends StatsOverviewWidget
{
    protected ?string $heading = 'Platform Overview';

    protected int|string|array $columnSpan = 'full';

     protected  ?string $pollingInterval = '2m';
    protected static ?string $slug = 'metrics/overview-stats';

    protected function getStats(): array
    {
        return collect(
            app(
                OrganizationCacheService::class
            )->platformMetrics()
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
