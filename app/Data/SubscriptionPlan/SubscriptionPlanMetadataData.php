<?php

namespace App\Data\SubscriptionPlan;

use Spatie\LaravelData\Data;

class SubscriptionPlanMetadataData extends Data
{
    public function __construct(

        public readonly string $displayName,

    ) {
    }
}
