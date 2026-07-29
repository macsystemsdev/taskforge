<?php

namespace App\Data\SubscriptionPlan;
use Carbon\Carbon;
use Spatie\LaravelData\Data;

readonly class RetireSubscriptionPlanData
{
     public function __construct(
        public  Carbon $effectiveDate,
    ) {
    }

}
