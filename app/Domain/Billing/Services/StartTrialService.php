<?php

namespace App\Domain\Billing\Services;

use App\Exceptions\Billing\TrialUnavailableException;
use App\Models\Subscription;

class StartTrialService
{

    public function handle(
        Subscription $subscription
    ): void {

        if (
            ! $subscription->canStartTrial()
        ) {
            throw new TrialUnavailableException();
        }

        $subscription->update([

            'has_used_trial' => true,

            'trial_starts_at' => now(),

            'trial_ends_at' =>
            now()->addDays(14),
        ]);
    }
}
