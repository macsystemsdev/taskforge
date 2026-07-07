<?php

namespace App\Models;

use App\Domain\Billing\BillingInterval;
use App\Domain\Billing\SubscriptionStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;


#[Table('subscriptions')]
#[Fillable([
    'organization_id',
    'subscription_plan_id',
    'status',
    'starts_at',
    'ends_at',
    'trial_ends_at',
    'pending_subscription_plan_id',
    'pending_payment_transaction_id',
    'pending_effective_at',
])]

class Subscription extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'status' => SubscriptionStatus::class,
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'trial_ends_at' => 'datetime',
            'pending_effective_at' => 'datetime',
        ];
    }

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    public function plan()
    {
        return $this->belongsTo(
            SubscriptionPlan::class,
            'subscription_plan_id'
        );
    }

    public function pendingPlan()
    {
        return $this->belongsTo(
            SubscriptionPlan::class,
            'pending_subscription_plan_id'
        );
    }

    public function pendingTransaction()
    {
        return $this->belongsTo(
            PaymentTransaction::class,
            'pending_payment_transaction_id'
        );
    }


    public function isActive(): bool
    {
        return $this->status === SubscriptionStatus::ACTIVE;
    }

    public function isFree(): bool
    {
        return $this->plan->billing_interval === BillingInterval::NONE;
    }

    public function isPaid(): bool
    {
        return ! $this->isFree();
    }

    public function hasExpired(): bool
    {
        return $this->ends_at !== null
            && $this->ends_at->isPast();
    }

    public function shouldExpire(): bool
    {
        return $this->isActive()
            && $this->hasExpired();
    }

    public function expiresAt(): ?Carbon
    {
        return $this->ends_at;
    }

    public function scopeActive($query)
    {
        return $query->where('status', SubscriptionStatus::ACTIVE);
    }

    public function scopeExpired($query)
    {
        return $query
            ->whereNotNull('ends_at')
            ->where('ends_at', '<=', now());
    }

    public function hasPendingPlan(): bool
    {
        return $this->pending_subscription_plan_id !== null;
    }

    public function shouldActivatePendingPlan(): bool
    {
        return $this->hasPendingPlan()
            && $this->pending_effective_at?->isPast();
    }

    public function shouldActivateImmediately(): bool
    {
        return $this->plan->isFree();
    }

    public function schedulePlanChange(
        SubscriptionPlan $plan,
        PaymentTransaction $transaction,
    ): void {

        if ($this->hasPendingPlan()) {
            return;
        }

        $this->update([
            'pending_subscription_plan_id' => $plan->id,
            'pending_payment_transaction_id' => $transaction->id,
            'pending_effective_at' => $this->ends_at,
        ]);
    }

    public function clearPendingPlan(): void
    {
        $this->update([
            'pending_subscription_plan_id' => null,
            'pending_payment_transaction_id' => null,
            'pending_effective_at' => null,
        ]);
    }

    public function activatePendingPlan(): void
    {
        if (! $this->shouldActivatePendingPlan()) {
            return;
        }

        $this->activatePlan(
            $this->pendingPlan()->firstOrFail()
        );
    }

    public function activatePlan(
        SubscriptionPlan $plan,
    ): void {

        $this->update([
            'subscription_plan_id' => $plan->id,
            'starts_at' => now(),
            'ends_at' => $plan->subscriptionEndsAt(),
        ]);

        $this->clearPendingPlan();
    }
}
