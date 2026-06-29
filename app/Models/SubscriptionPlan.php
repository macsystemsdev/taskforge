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
}
