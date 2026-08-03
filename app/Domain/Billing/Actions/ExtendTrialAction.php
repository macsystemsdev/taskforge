<?php

namespace App\Domain\Billing\Actions;

use App\Models\Organization;

class ExtendTrialAction
{
    public function handle(
        Organization $organization,
        int $days,
    ): void {

        $subscription = $organization->subscription;

        if (! $subscription->isTrial()) {
            return;
        }

        $subscription->update([
            'trial_ends_at' =>
                $subscription->trial_ends_at
                    ->addDays($days),
        ]);
    }
}