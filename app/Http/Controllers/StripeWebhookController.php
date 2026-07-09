<?php

namespace App\Http\Controllers;

use App\Domain\Billing\Enum\PaymentProvider;
use App\Domain\Billing\Enum\PaymentStatus;
use App\Domain\Billing\Services\CompletePaymentService;
use App\Domain\Billing\SubscriptionStatus;
use App\Models\PaymentTransaction;
use App\Models\WebhookEvent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Stripe\Exception\SignatureVerificationException;
use Stripe\Webhook;
use Stripe\StripeClient;

class StripeWebhookController extends Controller
{
    protected StripeClient $stripe;

    protected function alreadyProcessed($event): bool
    {
        return WebhookEvent::query()
            ->where(
                'event_id',
                $event->id
            )
            ->exists();
    }

    protected function storeWebhookEvent($event): void
    {
        WebhookEvent::create([
            'provider' => PaymentProvider::STRIPE,
            'event_id' => $event->id,
            'event_type' => $event->type,
            'processed_at' => now(),
        ]);
    }

    public function __construct(
        protected CompletePaymentService $service,
    ) {
        $this->stripe = new StripeClient(
            config('services.stripe.secret')
        );
    }

    public function __invoke(Request $request)
    {
        $payload = $request->getContent();
        $signature = $request->header('Stripe-Signature');
        $secret = config('services.stripe.webhook_secret');

        try {
            $event = Webhook::constructEvent(
                $payload,
                $signature,
                $secret
            );
        } catch (SignatureVerificationException $e) {
            return response('Invalid signature', 400);
        }


        if ($this->alreadyProcessed($event)) {

            return response()->json([
                'duplicate' => true,
            ]);
        }

        $response = $this->handleEvent($event);

        $this->storeWebhookEvent($event);

        return $response;
    }

    protected function handleEvent($event)
    {
        return match ($event->type) {

            'checkout.session.completed' =>

            $this->handleCheckoutCompleted($event->data->object),

            'payment_intent.succeeded'

            => $this->handleRenewalCompleted(

                $event->data->object
            ),

            'payment_intent.payment_failed'
            =>
            $this->handleRenewalFailed(
                $event->data->object
            ),

            default => response()->json(['ignored' => true]),
        };
    }

    protected function handleCheckoutCompleted($session)
    {
        $transactionId = $session->metadata->payment_transaction_id ?? null;

        $transaction = PaymentTransaction::processing($transactionId);

        if (! $transaction) {
            return response()->json(['not_found'], 404);
        }

        $paymentIntent = $this->stripe
            ->paymentIntents
            ->retrieve($session->payment_intent);

        $transaction->organization->update([
            'stripe_payment_method_id' => $paymentIntent->payment_method,
        ]);


        $this->service->handle($transactionId);

        return response()->json(['ok' => true]);
    }

    protected function handleRenewalCompleted(
        $paymentIntent
    ) {

        if (
            $paymentIntent->status
            !== 'succeeded'
        ) {
            return;
        }

        $transactionId =
            $paymentIntent
            ->metadata
            ->payment_transaction_id
            ?? null;

        $transaction =
            PaymentTransaction::processing(
                $transactionId
            );

        if (! $transaction) {
            return response()->json([
                'missing_transaction'
            ]);
        }

        $this->service->handle(
            $transaction->id
        );

        return response()->json([
            'ok' => true,
        ]);
    }

    protected function handleRenewalFailed($paymentIntent) 
    {
        $transactionId =
            $paymentIntent
            ->metadata
            ->payment_transaction_id
            ?? null;

        $transaction =
            PaymentTransaction::processing(
                $transactionId
            );

        if (! $transaction) {
            return;
        }

        $transaction->markFailed(
            $paymentIntent
                ->last_payment_error
                ?->message
        );

        return response()->json([
            'failed' => true,
        ]);
    }
}
