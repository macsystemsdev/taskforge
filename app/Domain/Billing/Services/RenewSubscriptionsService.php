<?php

namespace App\Domain\Billing\Services;

use App\Contracts\Billing\PaymentGateway;
use App\Domain\Billing\Actions\CreatePaymentTransactionAction;
use App\Domain\Billing\DataTransferObjects\CheckoutData;
use App\Models\Subscription;
use Throwable;

class RenewSubscriptionsService
{
    public function __construct(
        protected PaymentGateway $gateway,
        protected CreatePaymentTransactionAction $createPayment,
        protected RenewSubscriptionService $renew,
    ) {}
    public function handle(): void
    {
        Subscription::query()
            ->active()
            ->with([
                'plan',
                'organization',
                'pendingPlan',
            ])
            ->lazyById()
            ->each(function (Subscription $subscription) {

                if (! $subscription->shouldRenew()) {
                    return;
                }

                try {

                    $this->renew
                        ->handle(
                            $subscription
                        );
                } catch (
                    Throwable $e
                ) {

                    report($e);
                }
            });
    }
}
