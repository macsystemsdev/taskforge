<?php

namespace App\Models;

use App\Domain\Billing\Enum\PaymentProvider;
use App\Domain\Billing\Enum\PaymentStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Model;

#[Table('payment_transactions')]

#[Fillable(['organization_id', 'subscription_plan_id', 'amount', 'currency', 'status', 'provider', 'provider_reference', 'metadata', 'paid_at'])]

class PaymentTransaction extends Model
{
    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    public function plan()
    {
        return $this->belongsTo(SubscriptionPlan::class, 'subscription_plan_id');
    }

    protected function casts(): array
    {
        return [

            'provider' => PaymentProvider::class,

            'status' => PaymentStatus::class,

            'metadata' => 'array',

            'paid_at' => 'datetime',
        ];
    }

    public function isPending(): bool
    {
        return $this->status === PaymentStatus::PENDING;
    }

    public function isSuccessful(): bool
    {
        return $this->status === PaymentStatus::SUCCESSFUL;
    }

    public function isFailed(): bool
    {
        return $this->status === PaymentStatus::FAILED;
    }

    public function isCancelled(): bool
    {
        return $this->status === PaymentStatus::CANCELLED;
    }

    public static function processing(int $id): ?self
    {
        return static::query()
            ->whereKey($id)
            ->where('status', PaymentStatus::PROCESSING)
            ->first();
    }
}
