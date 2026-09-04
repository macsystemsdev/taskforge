<?php

namespace App\Filament\Widgets;

use App\Services\Owner\DTO\MetricData;
use App\Services\Owner\Infrastructure\InfrastructureCacheService;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class InfrastructureWidget extends StatsOverviewWidget
{
    protected int|string|array $columnSpan = 'full';
    protected ?string $heading = 'Infrastructure Health';
    protected ?string $pollingInterval = '2m';
    protected static ?string $slug = 'metrics/infrastructure';

    protected function getStats(): array
    {
        return collect(
            app(
                InfrastructureCacheService::class
            )->healthMetrics()
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
