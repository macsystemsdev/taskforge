<?php

namespace App\Data\SubscriptionPlan;

use App\Domain\Billing\BillingInterval;
use Spatie\LaravelData\Data;

class SubscriptionPlanData extends Data
{
        public function __construct(
        public string $name,
        public string $price,
        public string $currency,
        public BillingInterval $billing_interval,
        public ?int $max_workspaces,
        public ?int $max_projects,
        public ?int $max_members,
        public ?int $max_teams,
        public ?int $max_tasks,
        public ?int $max_storage_mb,
    ) {}
}
