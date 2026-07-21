<?php

namespace App\Filament\Widgets;

use App\Services\Owner\Revenue\RevenueMetricsService;
use Filament\Widgets\ChartWidget;

class RevenueGrowthChart extends ChartWidget
{
    protected ?string $heading = 'Revenue Growth Chart';

    protected int|string|array $columnSpan = 1;
    
    protected function getData(): array
    {
        $data =
            app(
                RevenueMetricsService::class
            )->monthlyRevenueTrend();

        return [

            'datasets' => [

                [
                    'label' => 'Revenue',

                    'data' => collect(
                        $data
                    )->pluck(
                        'value'
                    ),
                ],
            ],

            'labels' => collect(
                $data
            )->pluck(
                'label'
            ),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
