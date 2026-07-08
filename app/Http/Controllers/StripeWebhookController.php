<?php

namespace App\Http\Controllers;

use App\Domain\Billing\Enum\PaymentStatus;
use App\Domain\Billing\Services\CompletePaymentService;
use App\Domain\Billing\SubscriptionStatus;
use App\Models\PaymentTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Stripe\Exception\SignatureVerificationException;
use Stripe\Webhook;
use Stripe\StripeClient;

class StripeWebhookController extends Controller
{
    protected StripeClient $stripe;

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

        return $this->handleEvent($event);
    }

    protected function handleEvent($event)
    {
        return match ($event->type) {

            'checkout.session.completed' =>
            $this->handleCheckoutCompleted($event->data->object),

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
}
