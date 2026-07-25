<?php

namespace App\Services\Owner\Organization;


use App\Models\Organization;
use App\Services\Owner\Enums\OrganizationHealth;

class OrganizationHealthCalculator
{
    public function calculate(Organization $organization): OrganizationHealth
    {
        /*
         * TODO (Phase 3)
         *
         * Extend calculation using:
         * - Storage usage
         * - Failed payments
         * - Grace period
         * - Queue failures
         * - Owner inactivity
         */

        if (
            $organization->subscription?->isInGracePeriod()
            || $organization->subscription?->hasExpired()
        ) {
            return OrganizationHealth::CRITICAL;
        }

        if (
            $organization->subscription?->trialEndsAt()?->diffInDays(now()) <= 3
        ) {
            return OrganizationHealth::WARNING;
        }

        return OrganizationHealth::HEALTHY;
    }
}