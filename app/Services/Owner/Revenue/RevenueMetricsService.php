<?php

namespace App\Services\Owner\Revenue;

class RevenueMetricsService
{
    public function metrics(): array
    {
        return [

            'mrr' => [
                'value' => $this->mrr(),
            ],

            'arr' => [
                'value' => $this->arr(),
            ],

            'payingOrganizations' => [
                'value' => $this->payingOrganizations(),
            ],

            'trialOrganizations' => [
                'value' => $this->trialOrganizations(),
            ],

            'conversionRate' => [
                'value' => $this->conversionRate(),
            ],

            'monthlyGrowth' => [
                'value' => $this->monthlyGrowth(),
            ],

        ];
    }
}
