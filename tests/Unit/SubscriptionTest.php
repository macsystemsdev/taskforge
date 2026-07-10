<?php

use App\Models\Subscription;
use App\Models\SubscriptionPlan;

uses(Tests\TestCase::class);

describe('subscription trial eligibility', function () {
    it('does not allow a free trial when the subscription has no plan', function () {
        $subscription = new Subscription();

        expect(fn () => $subscription->canStartTrial())->not->toThrow(Exception::class);
        expect($subscription->canStartTrial())->toBeFalse();
    });

    it('falls back gracefully when no dedicated trial plan exists', function () {
        expect(fn () => SubscriptionPlan::trialPlan())->not->toThrow(Exception::class);

        $plan = SubscriptionPlan::trialPlan();

        expect($plan->name)->toBe('Pro Trial');
        expect($plan->slug)->toBe('trial');
    });
});
