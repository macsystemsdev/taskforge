<?php

namespace App\Domain\Billing\Services;

use App\Actions\ActivityLogs\CreateActivityLogAction;
use App\Domain\Billing\Enum\PaymentStatus;
use App\Domain\Billing\SubscriptionStatus;
use App\Models\PaymentTransaction;
use Illuminate\Support\Facades\DB;

class CompletePaymentService
{
    public function __construct(protected CreateActivityLogAction $activity) {}
    public function handle(int $transactionId): void
    {
        $transaction = PaymentTransaction::processing($transactionId);

        if (! $transaction) {
            return;
        }

        DB::transaction(function () use ($transaction) {

            $transaction->update([
                'status' => PaymentStatus::SUCCESSFUL,
                'paid_at' => now(),
            ]);

            $subscription = $transaction->organization->subscription;

            if ($subscription->shouldActivateImmediately()) {

                $subscription->activatePlan(
                    $transaction->plan,
                );
            } else {

                $subscription->schedulePlanChange(
                    plan: $transaction->plan,
                    transaction: $transaction,
                );
            }


            // log the successful payment
            $this->activity->handle(
                event: 'payment_successful',
                properties: [
                    'transaction_id' => $transaction->id,
                    'amount' => $transaction->amount,
                    'currency' => $transaction->currency,
                    'subscription_plan_id' => $transaction->subscription_plan_id,
                ],
                subject: $transaction->organization

            );
        });
    }
}
