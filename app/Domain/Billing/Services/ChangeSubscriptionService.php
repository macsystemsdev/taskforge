<?php

namespace App\Domain\Billing\Services;

use App\Contracts\Billing\PaymentGateway;
use App\Domain\Billing\DataTranferObjects\ChangeSubscriptionData;
use App\Exceptions\Billing\SubscriptionAlreadyActiveException;
use App\Exceptions\Billing\SubscriptionPlanInactiveException;

class ChangeSubscriptionService
{
    public function __construct(
        protected PaymentGateway $paymentGateway,
    ) {}

    public function handle(
        ChangeSubscriptionData $data,
    ): string {
        $this->ensurePlanCanBeChanged($data);

        return $this->paymentGateway
            ->createCheckout(
                $data->organization,
                $data->plan
            );
    }

    protected function ensurePlanCanBeChanged(
        ChangeSubscriptionData $data,
    ): void {

        if (! $data->plan->is_active) {
            throw new SubscriptionPlanInactiveException();
        }

        if (
            $data->organization
            ->subscription
            ->subscription_plan_id === $data->plan->id
        ) {
            throw new SubscriptionAlreadyActiveException();
        }
    }
}
