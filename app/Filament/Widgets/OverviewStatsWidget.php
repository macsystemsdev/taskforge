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

     protected  ?string $pollingInterval = '30s';
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
                    ->icon($metric->icon)
                    ->color($metric->color)

            )
            ->toArray();
    }
}
