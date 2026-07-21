<?php

namespace App\Filament\Widgets;

use App\Services\Owner\RevenueMetricsCacheService;
use Filament\Widgets\ChartWidget;

class SubscriptionBreakdownChart extends ChartWidget
{
    protected ?string $heading = 'Subscription Breakdown Chart';

    protected int|string|array $columnSpan = 1;
    
    protected function getData(): array
    {
        $metrics =
            app(
                RevenueMetricsCacheService::class
            )->overview();

        return [

            'datasets' => [
                [
                    'label' => 'Organizations',

                    'data' => [

                        $metrics['freeOrganizations']->value,

                        $metrics['trialOrganizations']->value,

                        $metrics['payingOrganizations']->value,
                    ],
                ],
            ],

            'labels' => [
                'Free',
                'Trial',
                'Paid',
            ],
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
