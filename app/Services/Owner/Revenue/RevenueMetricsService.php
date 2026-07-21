<?php

namespace App\Services\Owner\Revenue;

use App\Domain\Billing\BillingInterval;
use App\Domain\Billing\Enum\PaymentStatus;
use App\Models\Organization;
use App\Models\PaymentTransaction;
use App\Models\Subscription;
use App\Services\Owner\DTO\MetricData;

class RevenueMetricsService
{
    public function metrics(): array
    {
        return [

            'mrr' => new MetricData(
                label: 'MRR',
                value: $this->mrr(),
                description: 'Monthly recurring revenue',
                icon: 'heroicon-o-chart-bar',
                color: 'success',
            ),

            'arr' => new MetricData(
                label: 'ARR',
                value: $this->arr(),
                description: 'Annual recurring revenue',
                icon: 'heroicon-o-presentation-chart-line',
                color: 'success',
            ),

            'payingOrganizations' => new MetricData(
                label: 'Paying Organizations',
                value: $this->payingOrganizations(),
                description: 'Organizations with active subscriptions',
                icon: 'heroicon-o-banknotes',
                color: 'success',
            ),

            'trialOrganizations' => new MetricData(
                label: 'Trial Organizations',
                value: $this->trialOrganizations(),
                description: 'Organizations currently on trial',
                icon: 'heroicon-o-clock',
                color: 'warning',
            ),

            'conversionRate' => new MetricData(
                label: 'Conversion Rate',
                value: $this->conversionRate(),
                description: 'Trial to paid conversion rate',
                icon: 'heroicon-o-arrow-trending-up',
                color: 'info',
            ),

            'monthlyGrowth' => new MetricData(
                label: 'Monthly Growth',
                value: $this->monthlyGrowth(),
                description: 'Platform growth this month',
                icon: 'heroicon-o-arrow-trending-up',
                color: 'primary',
            ),

            'organizationsThisMonth' => new MetricData(
                label: 'Organizations This Month',
                value: $this->organizationsThisMonth(),
                description: 'New organizations this month',
                icon: 'heroicon-o-arrow-trending-up',
                color: 'primary',
            ),

            'freeOrganizations' => new MetricData(label: 'Free Organizations', value: $this->freeOrganizations(), description: 'Organizations on a free plan', color: 'warning', icon: 'heroicon-o-clock'),


        ];
    }

    private function organizationsThisMonth(): int
    {
        return Organization::whereMonth(
            'created_at',
            now()->month
        )
            ->whereYear(
                'created_at',
                now()->year
            )
            ->count();
    }

    private function organizationsLastMonth(): int
    {
        return Organization::whereMonth(
            'created_at',
            now()
                ->subMonth()
                ->month
        )
            ->whereYear(
                'created_at',
                now()
                    ->subMonth()
                    ->year
            )
            ->count();
    }

    private function mrr(): float
    {
        return Subscription::query()

            ->active()

            ->with('plan')

            ->get()

            ->sum(function ($subscription) {

                $plan = $subscription->plan;

                if (! $plan) {
                    return 0;
                }

                return match ($plan->billing_interval) {

                    BillingInterval::MONTHLY =>
                    $plan->price,

                    BillingInterval::YEARLY =>
                    $plan->price / 12,

                    default => 0,
                };
            });
    }

    private function arr(): float
    {
        return $this->mrr() * 12;
    }

    private function payingOrganizations(): int
    {
        return Organization::whereHas(
            'subscription',
            fn($query) => $query->active()
        )->count();
    }

    private function trialOrganizations(): int
    {
        return Organization::where(
            'trial_ends_at',
            '>',
            now()
        )->count();
    }

    private function freeOrganizations(): int
    {
        return Organization::whereHas(
            'subscription.plan',
            fn($query) =>
            $query->where('slug', 'free')
        )->count();
    }

    private function conversionRate(): float
    {
        $trials =
            $this->trialOrganizations();

        if ($trials === 0) {
            return 0;
        }

        return round(
            (
                $this->payingOrganizations()
                /
                $trials
            ) * 100,
            1
        );
    }

    private function monthlyGrowth(): float
    {
        $last =
            $this->organizationsLastMonth();

        $current =
            $this->organizationsThisMonth();

        if ($last === 0) {

            return $current > 0
                ? 100
                : 0;
        }

        return round(
            (
                ($current - $last)
                / $last
            ) * 100,
            1
        );
    }

    public function monthlyRevenueTrend(): array
    {
        return PaymentTransaction::query()

            ->where(
                'status',
                PaymentStatus::SUCCESSFUL
            )

            ->where(
                'paid_at',
                '>=',
                now()->subMonths(11)->startOfMonth()
            )

            ->selectRaw('
            YEAR(paid_at) as year,
            MONTH(paid_at) as month,
            SUM(amount) as revenue
        ')

            ->groupByRaw('
            YEAR(paid_at),
            MONTH(paid_at)
        ')

            ->orderByRaw('
            YEAR(paid_at),
            MONTH(paid_at)
        ')

            ->get();
    }
}
