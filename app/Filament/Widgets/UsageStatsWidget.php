<?php

namespace App\Filament\Widgets;

use App\Services\Owner\DTO\MetricData;
use App\Services\Owner\Organization\OrganizationCacheService;
use App\Services\Owner\Organization\OrganizationMetricsService;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class UsageStatsWidget extends StatsOverviewWidget
{
    protected int|string|array $columnSpan = 'full';
    protected ?string $heading = 'Usage Analytics';
    protected ?string $pollingInterval = '30s';

    protected function getStats(): array
    {
        return collect(
            app(
                OrganizationCacheService::class
            )->usageMetrics()
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
