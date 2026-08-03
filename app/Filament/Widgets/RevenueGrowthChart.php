<?php

namespace App\Filament\Widgets;

use App\Services\Owner\Revenue\RevenueMetricsService;
use App\Services\Owner\RevenueMetricsCacheService;
use Filament\Widgets\ChartWidget;

class RevenueGrowthChart extends ChartWidget
{
    protected ?string $heading = 'Revenue Trend';

    protected int|string|array $columnSpan = 1;
    protected  ?string $maxHeight = '320px';
    protected  ?string $pollingInterval = '2m';
    protected static ?string $slug = 'metrics/revenue-growth';

    protected function getData(): array
    {
        // Get the data
        $trendData = app(RevenueMetricsCacheService::class)->monthlyRevenueTrend();

        // Get just the revenue numbers for the chart
        $revenueNumbers = [];
        $monthLabels = [];

        foreach ($trendData as $item) {
            $revenueNumbers[] = $item['revenue']; // Add revenue to array
            $monthLabels[] = $item['month'] . '/' . $item['year']; // Add month/year label
        }

        return [
            'datasets' => [
                [
                    'label' => 'Revenue',
                    'data' => $revenueNumbers, // Example: [100, 200, 300, 400, 500]
                ],
            ],
            'labels' => $monthLabels, // Example: ['Jan 2024', 'Feb 2024', 'Mar 2024']
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
