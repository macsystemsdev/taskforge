# TaskForge Billing & Testing — Complete Documentation

**Last Updated:** 2026-08-29

---

## Table of Contents

1. Current Test Status
2. Skipped Tests
3. Stripe Setup
4. Stripe CLI Commands
5. Common Issues & Fixes
6. Payment Flow
7. Key Architectural Decisions
8. Known Issues
9. Testing Commands
10. Billing Test Suite
11. Files Involved
12. Test Payment Card

---

## Current Test Status

| Metric | Count |
|--------|-------|
| Passing | 117 |
| Skipped | 8 |
| Failing | 0 |
| Assertions | 230 |

---

## Skipped Tests

### Password Reset Notification Queue (3 tests)

File: tests/Feature/Auth/PasswordResetTest.php

- reset password link can be requested
- reset password screen can be rendered
- password can be reset with valid token

Reason: The ResetPassword notification is queued (implements ShouldQueue), but Notification::fake() does not intercept queued notifications in the current test setup.

Fix when revisiting: Either disable queueing for notifications in tests, use Queue::fake() and run the queue worker, or use a different notification assertion strategy.

---

### Team Creation / Deletion (2 tests)

File: tests/Feature/Teams/TeamTest.php

- teams can be created
- deleting current team switches to alphabetically first remaining team

Reason: The create-team Livewire component expects workspace->organization to exist. The UserFactory creates an Organization + Workspace, but these specific tests create teams without the proper workspace/organization context.

Fix when revisiting: Update tests to use user->currentTeam->workspace as the workspace context.

---

### Two-Factor Authentication (3 tests)

File: tests/Feature/Auth/TwoFactorChallengeTest.php
File: tests/Feature/Auth/AuthenticationTest.php

- two factor challenge redirects to login when not authenticated
- two factor challenge can be rendered
- users with two factor enabled are redirected to two factor challenge

Reason: Two-factor authentication requires additional test setup that simulates the 2FA flow.

Fix when revisiting: Set up proper 2FA test data using UserFactory::withTwoFactor() and ensure Fortify 2FA features are enabled.

---

## Stripe Setup

### Prerequisites

- Stripe account (Test Mode)
- Stripe CLI via Docker
- Docker Desktop running
- TaskForge app running in Docker

### Environment Variables

STRIPE_PUBLISHABLE_KEY=pk_test_...
STRIPE_SECRET_KEY=sk_test_...
STRIPE_WEBHOOK_SECRET=whsec_...

All keys are TEST MODE.

### Where to find API Keys

1. Go to https://dashboard.stripe.com/test/apikeys
2. Copy Publishable key (pk_test_...)
3. Copy Secret key (sk_test_...)
4. Webhook secret comes from Stripe CLI (whsec_...)

---

## Stripe CLI Commands

### 1. Create volume for Stripe credentials (once)

docker volume create stripe-config

### 2. Login to Stripe CLI (once per session)

docker run --rm -it \
  -v stripe-config:/root/.config/stripe \
  stripe/stripe-cli:latest login

This opens a browser URL. Authorize the Stripe account.
Choose the SAME sandbox that has your sk_test_ API keys.

### 3. Forward webhooks to local Docker app

docker run --rm -it \
  -v stripe-config:/root/.config/stripe \
  --network taskforge_default \
  stripe/stripe-cli:latest listen \
  --api-key sk_test_YOUR_SECRET_KEY_HERE \
  --forward-to http://taskforge-app:8000/stripe/webhook

Key points:
- --network taskforge_default — Same Docker network as the app
- --forward-to http://taskforge-app:8000 — Container name, NOT localhost
- --api-key sk_test_... — SAME secret key as .env

### 4. Get webhook signing secret

The terminal outputs:
> Ready! Your webhook signing secret is whsec_xxxxxxxxxxxxx

Copy whsec_... to .env:
STRIPE_WEBHOOK_SECRET=whsec_xxxxxxxxxxxxx

Then:
docker compose exec app php artisan config:clear

---

## Common Issues & Fixes

### Issue 1: 404 on /livewire-XXXX/update

Symptom: Browser returns 404 on Livewire update endpoint.

Root cause: Stale Livewire hash in browser cache or stale compiled views.

Fix:
rm -rf storage/framework/views/*.php
docker compose exec app php artisan optimize:clear
docker compose restart app
docker compose exec redis redis-cli FLUSHALL

Then in browser:
1. Clear site data (DevTools → Application → Clear storage)
2. Use Incognito/Private window
3. Log in fresh

---

### Issue 2: Webhook returns 400 (Invalid signature)

Symptom: Stripe CLI shows webhook forwarded but app returns 400.

Root cause: Webhook secret in .env does not match Stripe CLI secret.

Fix:
# Copy whsec_ from Stripe CLI terminal
# Update .env
STRIPE_WEBHOOK_SECRET=whsec_YOUR_SECRET_HERE
docker compose exec app php artisan config:clear

---

### Issue 3: Webhook returns 500 on payment_intent.succeeded

Symptom: Stripe CLI shows payment_intent.succeeded webhook returns 500.

Root cause: The payment_intent.succeeded event was being treated as a renewal, but it's part of the initial checkout flow. The metadata doesn't have payment_transaction_id.

Fix: Updated StripeWebhookController::handleRenewalCompleted() to check if payment_transaction_id exists in metadata. If not, return ignored.

---

### Issue 4: Webhooks not reaching app

Symptom: Stripe CLI running but no webhooks received.

Root cause: Stripe CLI using a DIFFERENT sandbox than the app's API keys.

Fix: Use --api-key with the SAME secret key as .env.

---

### Issue 5: Empty error toast when purchasing while plan scheduled

Symptom: Error toast appears but with no text.

Root cause: SubscriptionChangeAlreadyScheduledException had no message.

Fix: Added default messages to all billing exceptions.

---

## Payment Flow

User clicks Continue to Payment
  -> CreateCheckoutService
  -> ensurePlanCanBeChanged()
  -> isFree() -> CannotPurchaseFreePlanException
  -> !isPurchasable() -> SubscriptionPlanInactiveException
  -> isSubscribedTo() -> SubscriptionAlreadyActiveException
  -> hasPendingPlan() -> SubscriptionChangeAlreadyScheduledException
  -> DB::transaction()
  -> lockForUpdate() on subscription
  -> UUID idempotency key
  -> CreatePaymentTransactionAction
  -> PaymentGatewayResolver->resolve(STRIPE)
  -> StripePaymentGateway->createCheckout()
  -> Redirect to Stripe Checkout page
  -> User pays with test card 4242 4242 4242 4242
  -> Stripe sends webhook -> Stripe CLI -> /stripe/webhook
  -> StripeWebhookController
  -> Verify signature (Webhook::constructEvent)
  -> Check event_id (idempotency)
  -> Route event type
  -> checkout.session.completed -> handleCheckoutCompleted()
  -> Find transaction (PaymentTransaction::processing())
  -> Retrieve PaymentIntent
  -> Save stripe_payment_method_id
  -> CompletePaymentService->handle()
  -> PaymentTransaction: PROCESSING -> SUCCESSFUL
  -> paid_at = now()
  -> Subscription activation
  -> clearTrial()
  -> activatePlan() or schedulePlanChange()
  -> Activity log dispatched (queued)

---

## Key Architectural Decisions

### 1. Payment Gateway Resolver Pattern

PaymentGatewayResolver
    STRIPE -> StripePaymentGateway
    MTN -> (future) MtnMomoPaymentGateway
    ORANGE -> (future) OrangeMomoPaymentGateway

Adding new providers requires only resolver changes. CreateCheckoutService stays untouched.

### 2. Concurrency Protection (Three Layers)

1. Database Row Lock — lockForUpdate() on subscription
2. UUID Idempotency Key — Unique per checkout attempt
3. Unique Database Constraint — idempotency_key UNIQUE

### 3. Payment Amount Invariant

- Database: Major units (19.99)
- Stripe: Minor units (1999)
- Both checkout and off-session charge use same invariant

### 4. Webhook Authority

- Stripe webhook is authoritative, NOT success URL redirect
- Success page only displays state, does not mutate
- Webhook idempotency via WebhookEvent.event_id UNIQUE

### 5. Authorization Boundaries

- HTTP route: Gate::authorize('update', $organization)
- Livewire actions: Same check in mutating methods
- Policy: OrganizationPolicy::update() -> OWNER only

### 6. Payment Transaction State Machine

PROCESSING -> SUCCESSFUL (via webhook)
PROCESSING -> FAILED (via webhook or gateway exception)

- Gateway failure inside DB::transaction() rolls back transaction creation
- No orphaned PROCESSING transactions
- markFailed() stores failure_reason and failed_at

### 7. Billing Validation Order

1. isFree() -> CannotPurchaseFreePlanException (specific)
2. !isPurchasable() -> SubscriptionPlanInactiveException (generic)
3. isSubscribedTo() -> SubscriptionAlreadyActiveException
4. hasPendingPlan() -> SubscriptionChangeAlreadyScheduledException

---

## Known Issues

1. Password reset notification tests — Queue timing in test environment
2. Team creation tests — Workspace/organization context in test data
3. 2FA tests — Need proper test setup
4. Valid Stripe signature positive test — Test completeness (not blocker)
5. Concurrent Stripe customer creation — Race condition possible (add lock)
6. Production hardening — Observability, deployment, failure recovery

---

## Testing Commands

# Run all tests
docker compose exec app php artisan test

# Run billing tests only
./scripts/test-billing.sh

# Clear caches before tests
docker compose exec app php artisan optimize:clear

# Start Stripe CLI webhook forwarding
docker run --rm -it \
  -v stripe-config:/root/.config/stripe \
  --network taskforge_default \
  stripe/stripe-cli:latest listen \
  --api-key sk_test_YOUR_SECRET_KEY_HERE \
  --forward-to http://taskforge-app:8000/stripe/webhook

# Check webhook log
docker compose exec app tail -20 storage/logs/laravel.log

---

## Billing Test Suite (62 tests — ALL PASSING)

| Category | Tests | Status |
|----------|-------|--------|
| Authorization | 7 | PASS |
| Organization Isolation | 3 | PASS |
| Plans | 6 | PASS |
| Trial | 4 | PASS |
| Payment | 4 | PASS |
| Payment State Machine | 4 | PASS |
| Amounts | 2 | PASS |
| Checkout Failure | 4 | PASS |
| Checkout Failure Recovery | 2 | PASS |
| Webhook | 3 | PASS |
| Webhook Signature | 4 | PASS |
| Stripe Customer | 3 | PASS |
| Stripe Customer Isolation | 2 | PASS |
| Livewire Authorization | 4 | PASS |
| Success/Cancel Pages | 3 | PASS |
| Subscription Lifecycle | 5 | PASS |
| Feature Limits | 3 | PASS |

---

## Files Involved

| File | Purpose |
|------|---------|
| app/Infrastructure/Billing/StripePaymentGateway.php | Stripe checkout + customer + charge |
| app/Http/Controllers/StripeWebhookController.php | Webhook verification + routing |
| app/Domain/Billing/Services/CompletePaymentService.php | Payment completion + subscription |
| app/Domain/Billing/Services/CreateCheckoutService.php | Checkout orchestration |
| app/Domain/Billing/Services/PaymentGatewayResolver.php | Provider to Gateway |
| app/Domain/Billing/Actions/CreatePaymentTransactionAction.php | Transaction + idempotency |
| app/Models/PaymentTransaction.php | Transaction model |
| app/Models/WebhookEvent.php | Webhook idempotency |
| app/Models/Subscription.php | Subscription lifecycle |
| app/Models/SubscriptionPlan.php | Plan model |
| app/Domain/Billing/Enum/PaymentProvider.php | Provider enum |
| app/Domain/Billing/Enum/PaymentStatus.php | Status enum |
| app/Livewire/Billing/BillingDashboard.php | Multi-org billing UI |

---

## Test Payment Card

- Card number: 4242 4242 4242 4242
- Expiry: Any future date (e.g., 12/34)
- CVC: Any 3 digits (e.g., 123)
- ZIP: Any (e.g., 12345)

---

## Stripe Events Handled

| Event | Handler | Action |
|-------|---------|--------|
| checkout.session.completed | handleCheckoutCompleted | Initial payment completion |
| payment_intent.succeeded | handleRenewalCompleted | Renewal success (if metadata has transaction_id) |
| payment_intent.payment_failed | handleRenewalFailed | Renewal failure |

Other events are acknowledged and ignored.

---

## Verified End-to-End

- Real Stripe payment with test card 4242 4242 4242 4242
- Stripe CLI webhook forwarding to local Docker
- Transaction marked successful
- Subscription activated (Enterprise plan active)
- Payment Gateway Resolver architecture
- Concurrency protection (row locking + UUID idempotency)
