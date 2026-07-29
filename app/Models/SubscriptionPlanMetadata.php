<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Model;

#[Table('subscription_plan_metadata')]
#[Fillable(
    'subscription_plan_id',
    'display_name',
    'subtitle',
    'description',
    'badge',
    'popular',
    'recommended',
    'accent_color',
    'card_order',
    'button_text',
    'marketing_copy',
)]
class SubscriptionPlanMetadata extends Model
{
    protected function casts(): array
    {
        return [
            'popular' => 'boolean',
            'recommended' => 'boolean',
        ];
    }

    public function subscriptionPlan()
    {
        return $this->belongsTo(
            SubscriptionPlan::class
        );
    }
}
