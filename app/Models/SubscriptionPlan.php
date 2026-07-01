<?php

namespace App\Models;

use App\Domain\Billing\BillingInterval;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Model;


#[Table('subscription_plans')]

#[Fillable([
    'name',
    'slug',
    'price',
    'currency',
    'billing_interval',
    'max_workspaces',
    'max_projects',
    'max_members',
    'is_active',
])]

class SubscriptionPlan extends Model
{

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'is_active' => 'boolean',
            'billing_interval' => BillingInterval::class,
        ];
    }

    public function subscriptions()
    {
        return $this->hasMany(Subscription::class);
    }

    private function formatLimit(?int $limit): string
    {
        return $limit === null
            ? 'Unlimited'
            : (string) $limit;
    }

    public function workspaceLimitLabel(): string
    {
        return $this->formatLimit($this->max_workspaces);
    }

    public function projectLimitLabel(): string
    {
        return $this->formatLimit($this->max_projects);
    }

    public function memberLimitLabel(): string
    {
        return $this->formatLimit($this->max_members);
    }

    public function billingIntervalLabel(): string
    {
        return match ($this->billing_interval) {

            BillingInterval::NONE => 'Free',

            BillingInterval::MONTHLY => 'Monthly',

            BillingInterval::YEARLY => 'Yearly',
        };
    }

    public function billingLabel(): string
    {
        return match ($this->billing_interval) {

            BillingInterval::NONE => '',

            BillingInterval::MONTHLY => 'month',

            BillingInterval::YEARLY => 'year',
        };
    }

    public function formattedPrice(): string
    {
        if ($this->price === 0.0) {
            return 'Free';
        }

        return '$' . number_format($this->price, 2);
    }

    public function isFree(): bool
{
    return $this->billing_interval === BillingInterval::NONE;
}
}
