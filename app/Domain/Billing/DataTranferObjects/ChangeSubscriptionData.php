<?php

namespace App\Domain\Billing\DataTranferObjects;

use App\Models\Organization;
use App\Models\SubscriptionPlan;

final readonly class ChangeSubscriptionData
{
  public function __construct(
        public Organization $organization,
        public SubscriptionPlan $plan,
    ) {}
}
