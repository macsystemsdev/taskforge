<?php

namespace App\Domain\Billing\Services;

use App\Exceptions\Billing\TrialUnavailableException;
use App\Models\Subscription;
use Illuminate\Support\Facades\DB;

class StartTrialService
{
    public function handle(Subscription $subscription): void
    {
        // Use database lock to prevent concurrent trials
        DB::transaction(function () use ($subscription) {
            // Lock the subscription row
            $lockedSubscription = Subscription::whereKey($subscription->id)
                ->lockForUpdate()
                ->firstOrFail();

            if (!$lockedSubscription->canStartTrial()) {
                throw new TrialUnavailableException();
            }

            $lockedSubscription->update([
                'has_used_trial' => true,
                'trial_starts_at' => now(),
                'trial_ends_at' => now()->addDays(14),
            ]);
        });
    }
}
