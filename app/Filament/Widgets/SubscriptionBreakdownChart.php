<?php

namespace App\Filament\Widgets;

use App\Services\Owner\RevenueMetricsCacheService;
use Filament\Widgets\ChartWidget;

class SubscriptionBreakdownChart extends ChartWidget
{
    protected ?string $heading = 'Subscription Distribution';
    
    protected int|string|array $columnSpan = 1;
    protected ?string $maxHeight = '320px';
    protected  ?string $pollingInterval = '2m';
    protected static ?string $slug = 'metrics/subscription-breakdown';

    protected function getData(): array
    {
        // Get subscription breakdown data
        $breakdownData = app(RevenueMetricsCacheService::class)->subscriptionBreakdown();

        // Prepare the data
        $revenues = [];
        $subscriptionNames = [];

        foreach ($breakdownData as $item) {
            $revenues[] = $item['total_revenue'];
            $subscriptionNames[] = 'Sub #' . $item['subscription_plan_id'];
        }

        return [
            'datasets' => [
                [
                    'label' => 'Revenue by Subscription',
                    'data' => $revenues,
                ],
            ],
            'labels' => $subscriptionNames,
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
