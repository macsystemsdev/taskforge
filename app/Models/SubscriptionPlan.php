<?php

namespace App\Models;

use App\Domain\Billing\BillingInterval;
use App\Domain\Billing\SubscriptionPlanStatus;
use App\Domain\Billing\SubscriptionStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Model;
use Carbon\CarbonInterface;

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
    'max_teams',
    'max_tasks',
    'max_storage_mb',
    'status',
    'activated_at',
    'retired_at',
    'retirement_effective_at',
    'archived_at',
])]

class SubscriptionPlan extends Model
{

    protected function casts(): array
    {
        return [

            'status' => SubscriptionPlanStatus::class,
            'activated_at' => 'datetime',
            'retired_at' => 'datetime',
            'retirement_effective_at' => 'datetime',
            'archived_at' => 'datetime',
            'price' => 'decimal:2',
            'billing_interval' => BillingInterval::class,
        ];
    }

    public function subscriptions()
    {
        return $this->hasMany(Subscription::class);
    }

    public function metadata()
    {
        return $this->hasOne(
            SubscriptionPlanMetadata::class
        );
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

    public function teamLimitLabel(): string
    {
        return $this->formatLimit(
            $this->max_teams
        );
    }

    public function storageLimitLabel(): string
    {
        if (is_null($this->max_storage_mb)) {
            return 'Unlimited';
        }

        return "{$this->max_storage_mb} MB";
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

    public function paymentTransactions()
    {
        return $this->hasMany(PaymentTransaction::class);
    }

    public function featureHighlights(): array
    {
        return [
            [
                'label' => 'Workspaces',
                'value' => $this->workspaceLimitLabel(),
            ],
            [
                'label' => 'Projects',
                'value' => $this->projectLimitLabel(),
            ],
            [
                'label' => 'Members',
                'value' => $this->memberLimitLabel(),
            ],
        ];
    }

    public function subscriptionEndsAt(): ?CarbonInterface
    {
        return match ($this->billing_interval) {

            BillingInterval::NONE => null,

            BillingInterval::MONTHLY => now()->addMonth(),

            BillingInterval::YEARLY => now()->addYear(),
        };
    }

    public function scopePurchasable($query)
    {
        return $query
            ->where('status', '=', SubscriptionPlanStatus::ACTIVE)
            ->where('billing_interval', '!=', BillingInterval::NONE);
    }


    public static function trialPlan(): self
    {
        try {
            $plan = static::query()
                ->where('slug', 'pro-monthly')
                ->first();

            if ($plan) {
                return $plan;
            }
        } catch (\Throwable) {
            // Fall back to an in-memory trial plan when the database lookup is unavailable.
        }

        return new static([
            'name' => 'Pro Trial',
            'slug' => 'trial',
            'price' => 0.0,
            'currency' => 'usd',
            'billing_interval' => BillingInterval::MONTHLY,
            'max_workspaces' => null,
            'max_projects' => null,
            'max_members' => null,
            'status' => SubscriptionPlanStatus::ACTIVE,
        ]);
    }

    public function canBeActivated(): bool
    {
        return $this->status->isDraft();
    }

    public function canBeRetired(): bool
    {
        return $this->status->isActive();
    }

    public function canBeArchived(): bool
    {
        return $this->status->isRetired();
    }

    public function isPurchasable(): bool
    {
        return $this->status->isPurchasable() && ! $this->isFree();
    }

    public function isVisible(): bool
    {
        return $this->status->isVisible();
    }

    public function acceptsRenewals(): bool
    {
        return $this->status->acceptsRenewals();
    }
}
