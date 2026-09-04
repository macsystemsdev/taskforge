<?php

namespace App\Filament\Widgets;

use App\Services\Owner\Revenue\RevenueMetricsService;
use App\Services\Owner\RevenueMetricsCacheService;
use Filament\Widgets\ChartWidget;

class RevenueGrowthChart extends ChartWidget
{
    protected ?string $heading = 'Monthly Revenue Trend';
    protected int|string|array $columnSpan = 1;
    protected ?string $maxHeight = '320px';
    protected ?string $pollingInterval = '2m';
    protected static ?string $slug = 'metrics/revenue-growth';

    protected function getData(): array
    {
        $trendData = app(RevenueMetricsCacheService::class)->monthlyRevenueTrend();

        $revenueNumbers = [];
        $monthLabels = [];

        foreach ($trendData as $item) {
            $revenueNumbers[] = round($item['revenue'] / 100, 2);
            // Better month labels
            $monthLabels[] = date('M Y', mktime(0, 0, 0, $item['month'], 1, $item['year']));
        }

        // Ensure we have at least 12 months of data
        if (empty($revenueNumbers)) {
            return [
                'datasets' => [
                    [
                        'label' => 'Revenue',
                        'data' => [0],
                        'borderColor' => '#6366F1',
                        'backgroundColor' => 'rgba(99, 102, 241, 0.1)',
                        'fill' => true,
                        'tension' => 0.4,
                        'borderWidth' => 3,
                    ],
                ],
                'labels' => ['No Data'],
            ];
        }

        return [
            'datasets' => [
                [
                    'label' => 'Revenue',
                    'data' => $revenueNumbers,
                    'borderColor' => '#6366F1',
                    'backgroundColor' => 'rgba(99, 102, 241, 0.1)',
                    'fill' => true,
                    'tension' => 0.4,
                    'pointBackgroundColor' => '#4F46E5',
                    'pointBorderColor' => '#818CF8',
                    'pointRadius' => 4,
                    'pointHoverRadius' => 6,
                    'borderWidth' => 3,
                ],
            ],
            'labels' => $monthLabels,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => [
                    'display' => false,
                ],
                'tooltip' => [
                    'callbacks' => [
                        'label' => 'function(context) { 
                            return "Revenue: $" + context.parsed.y.toFixed(2);
                        }',
                    ],
                ],
            ],
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'grid' => [
                        'display' => true,
                        'color' => 'rgba(0, 0, 0, 0.05)',
                        'drawBorder' => false,
                    ],
                    'ticks' => [
                        'callback' => 'function(value) { return "$" + value.toFixed(0); }',
                    ],
                ],
                'x' => [
                    'grid' => [
                        'display' => false,
                    ],
                ],
            ],
            'elements' => [
                'line' => [
                    'borderJoinStyle' => 'round',
                ],
            ],
        ];
    }
}
