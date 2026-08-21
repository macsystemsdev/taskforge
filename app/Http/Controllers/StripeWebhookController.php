<?php

namespace App\Http\Controllers;

use App\Domain\Billing\Enum\PaymentProvider;
use App\Domain\Billing\Services\CompletePaymentService;
use App\Models\PaymentTransaction;
use App\Models\WebhookEvent;
use Illuminate\Http\Request;
use Stripe\Exception\SignatureVerificationException;
use Stripe\Webhook;
use Stripe\StripeClient;


class StripeWebhookController
{
    /**
     * Stripe API client.
     *
     * This is used when webhook data alone is not sufficient and we need
     * to retrieve additional information directly from Stripe.
     *
     * Example:
     * A checkout.session.completed event gives us the Checkout Session,
     * including the Payment Intent ID. We retrieve the Payment Intent to
     * obtain the payment_method ID that will later be reused for renewals.
     */
    protected StripeClient $stripe;

    /**
     * Check whether this exact Stripe event has already been processed.
     *
     * Webhooks must be treated as potentially duplicated.
     *
     * Stripe can retry delivery, and the same event can arrive more than
     * once. We therefore use Stripe's unique event ID as an idempotency key.
     *
     * Without this protection, the same successful payment could potentially
     * trigger subscription/payment completion logic multiple times.
     */
    protected function alreadyProcessed($event): bool
    {
        return WebhookEvent::query()
            ->where(
                'event_id',
                $event->id
            )
            ->exists();
    }

    /**
     * Record that a Stripe webhook event has been handled.
     *
     * The event ID is stored so that future deliveries of the same event
     * can be identified as duplicates and ignored.
     *
     * provider:
     *     Identifies which payment provider produced the event. This matters
     *     because TaskForge is designed to support multiple payment providers,
     *     not only Stripe.
     *
     * event_id:
     *     Stripe's unique identifier for this specific webhook event.
     *
     * event_type:
     *     The Stripe event that was handled, for example:
     *     - checkout.session.completed
     *     - payment_intent.succeeded
     *     - payment_intent.payment_failed
     *
     * processed_at:
     *     Records when TaskForge considers this event processed.
     */
    protected function storeWebhookEvent($event): void
    {
        WebhookEvent::create([
            'provider' => PaymentProvider::STRIPE,
            'event_id' => $event->id,
            'event_type' => $event->type,
            'processed_at' => now(),
        ]);
    }

    /**
     * Inject the application service responsible for completing a payment.
     *
     * The webhook controller should not contain the full subscription
     * activation/renewal business logic.
     *
     * Its responsibility is mainly:
     *
     * 1. Verify that the request genuinely came from Stripe.
     * 2. Identify the Stripe event.
     * 3. Prevent duplicate processing.
     * 4. Route the event to the appropriate application workflow.
     *
     * CompletePaymentService owns the actual payment completion logic.
     */
    public function __construct(
        protected CompletePaymentService $service,
    ) {
        /**
         * Stripe's secret API key is used for server-to-server communication
         * with Stripe.
         *
         * This is different from the webhook signing secret.
         *
         * Secret API key:
         *     Allows TaskForge's backend to call the Stripe API.
         *
         * Webhook signing secret:
         *     Allows TaskForge to verify that an incoming webhook payload
         *     was genuinely signed by Stripe.
         */
        $this->stripe = new StripeClient(
            config('services.stripe.secret')
        );
    }

    /**
     * Receive and process an incoming Stripe webhook.
     *
     * IMPORTANT:
     * A webhook request is not trusted simply because it reaches this route.
     *
     * Stripe signs webhook requests. We verify the payload using the
     * Stripe-Signature header and the configured webhook signing secret
     * before doing anything with the event.
     */
    public function __invoke(Request $request)
    {
        /**
         * Get the raw request body exactly as Stripe sent it.
         *
         * Signature verification depends on the original payload, so we use
         * getContent() rather than transforming the request into an array
         * before verification.
         */
        $payload = $request->getContent();

        /**
         * Stripe includes a signature in this request header.
         */
        $signature = $request->header('Stripe-Signature');

        /**
         * This secret belongs specifically to the configured Stripe webhook
         * endpoint and is used to verify incoming webhook signatures.
         */
        $secret = config('services.stripe.webhook_secret');

        try {
            /**
             * Verify the webhook signature and construct a Stripe Event object.
             *
             * If verification fails, the event must not be trusted or processed.
             */
            $event = Webhook::constructEvent(
                $payload,
                $signature,
                $secret
            );
        } catch (SignatureVerificationException $e) {
            /**
             * The request either did not come from Stripe or the payload/signature
             * combination failed verification.
             */
            return response('Invalid signature', 400);
        }

        /**
         * Webhooks are at-least-once delivery systems.
         *
         * Stripe may send the same event again, so do not run payment logic twice
         * for an event we have already processed.
         */
        if ($this->alreadyProcessed($event)) {
            return response()->json([
                'duplicate' => true,
            ]);
        }

        /**
         * Route the event to the correct handler based on its Stripe event type.
         */
        $response = $this->handleEvent($event);

        /**
         * Only record the event after it has been handled.
         *
         * This means a failure before this point does not incorrectly mark
         * the event as successfully processed.
         */
        $this->storeWebhookEvent($event);

        return $response;
    }

    /**
     * Route supported Stripe events to their corresponding application flow.
     *
     * Events not relevant to TaskForge are deliberately acknowledged but ignored.
     *
     * This prevents the controller from attempting to handle every Stripe event
     * Stripe may send.
     */
    protected function handleEvent($event)
    {
        return match ($event->type) {

            /**
             * Initial subscription/payment checkout completed successfully.
             */
            'checkout.session.completed' =>

            $this->handleCheckoutCompleted(
                $event->data->object
            ),

            /**
             * A Payment Intent used for a subscription renewal succeeded.
             */
            'payment_intent.succeeded'

            => $this->handleRenewalCompleted(

                $event->data->object
            ),

            /**
             * A renewal Payment Intent failed.
             */
            'payment_intent.payment_failed'
            =>
            $this->handleRenewalFailed(
                $event->data->object
            ),

            /**
             * Stripe events that TaskForge does not currently use are
             * acknowledged without triggering application logic.
             */
            default => response()->json([
                'ignored' => true,
            ]),
        };
    }

    /**
     * Handle a completed Stripe Checkout Session.
     *
     * This is used for the initial successful payment flow.
     */
    protected function handleCheckoutCompleted($session)
    {
        /**
         * The internal TaskForge payment transaction ID was attached to Stripe
         * metadata when the checkout/payment flow was created.
         *
         * Metadata gives us a reliable way to connect Stripe's external payment
         * object back to the correct internal PaymentTransaction.
         */
        $transactionId =
            $session->metadata->payment_transaction_id
            ?? null;

        /**
         * Only continue with a transaction that is still in the expected
         * processing state.
         *
         * This provides another layer of protection against accidentally
         * completing a transaction that has already been finalized or is
         * otherwise no longer eligible for completion.
         */
        $transaction =
            PaymentTransaction::processing(
                $transactionId
            );

        if (! $transaction) {
            return response()->json([
                'not_found',
            ], 404);
        }

        /**
         * A Checkout Session references the Payment Intent created for the payment.
         *
         * We retrieve the Payment Intent because it contains the payment method
         * that successfully funded this transaction.
         *
         * The Payment Intent is Stripe's object representing the lifecycle of
         * an attempt to collect payment.
         *
         * In simplified terms:
         *
         * Checkout Session
         *      ↓
         * Customer completes checkout
         *      ↓
         * Stripe creates/completes Payment Intent
         *      ↓
         * Payment Intent records payment outcome and payment method
         *
         * We store the successful payment method ID on the organization so
         * TaskForge can later use that saved payment method for subscription
         * renewal attempts.
         */
        $paymentIntent = $this->stripe
            ->paymentIntents
            ->retrieve(
                $session->payment_intent
            );

        /**
         * Save the Stripe payment method that successfully completed checkout.
         *
         * This does NOT store card details in TaskForge.
         *
         * TaskForge stores Stripe's payment method identifier, while sensitive
         * payment details remain managed by Stripe.
         */
        $transaction
            ->organization
            ->update([
                'stripe_payment_method_id' =>
                    $paymentIntent->payment_method,
            ]);

        /**
         * Hand the internal transaction to the payment completion service.
         *
         * This is where TaskForge performs the actual application-side work
         * associated with a successful payment, rather than putting that
         * business logic inside the webhook controller.
         */
        $this->service->handle(
            $transactionId
        );

        return response()->json([
            'ok' => true,
        ]);
    }

    /**
     * Handle a successful renewal payment.
     *
     * Renewals use Payment Intents directly rather than the initial
     * Checkout Session flow.
     */
    protected function handleRenewalCompleted(
        $paymentIntent
    ) {
        /**
         * Defensively confirm that the Payment Intent actually succeeded.
         *
         * The webhook event name already indicates success, but checking the
         * object state avoids blindly trusting assumptions about the payload.
         */
        if (
            $paymentIntent->status
            !== 'succeeded'
        ) {
            return;
        }

        /**
         * Retrieve the internal TaskForge transaction ID from Stripe metadata.
         *
         * This links the external Stripe payment back to the internal
         * PaymentTransaction that TaskForge created for this renewal attempt.
         */
        $transactionId =
            $paymentIntent
            ->metadata
            ->payment_transaction_id
            ?? null;

        /**
         * Find the renewal transaction only while it is still processing.
         */
        $transaction =
            PaymentTransaction::processing(
                $transactionId
            );

        if (! $transaction) {
            return response()->json([
                'missing_transaction',
            ]);
        }

        /**
         * Complete the internal payment workflow.
         */
        $this->service->handle(
            $transaction->id
        );

        return response()->json([
            'ok' => true,
        ]);
    }

    /**
     * Handle a failed renewal payment.
     *
     * A failed Payment Intent means Stripe was unable to collect the renewal
     * payment using the attempted payment method.
     */
    protected function handleRenewalFailed(
        $paymentIntent
    ) {
        /**
         * Link the Stripe Payment Intent back to TaskForge's internal
         * PaymentTransaction.
         */
        $transactionId =
            $paymentIntent
            ->metadata
            ->payment_transaction_id
            ?? null;

        /**
         * Only mark a transaction as failed if it is still processing.
         */
        $transaction =
            PaymentTransaction::processing(
                $transactionId
            );

        if (! $transaction) {
            return;
        }

        /**
         * Record the failure on the internal transaction.
         *
         * Stripe may provide a human-readable explanation through
         * last_payment_error.
         */
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